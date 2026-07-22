<?php

namespace Tests\Feature;

use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Testing\TestResponse;
use Tests\Concerns\CreatesTenants;
use Tests\TestCase;

class StripeWebhookControllerTest extends TestCase
{
    use RefreshDatabase, CreatesTenants;

    private const WEBHOOK_SECRET = 'whsec_test_secret';

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.stripe.webhook_secret', self::WEBHOOK_SECRET);
    }

    private function postWebhook(array $payload): TestResponse
    {
        $body = json_encode($payload);
        $timestamp = time();
        $signature = hash_hmac('sha256', "{$timestamp}.{$body}", self::WEBHOOK_SECRET);

        return $this->call(
            'POST',
            '/api/v1/webhooks/stripe',
            [],
            [],
            [],
            [
                'HTTP_Stripe-Signature' => "t={$timestamp},v1={$signature}",
                'CONTENT_TYPE' => 'application/json',
            ],
            $body
        );
    }

    public function test_it_rejects_a_webhook_with_an_invalid_signature(): void
    {
        $response = $this->postJson('/api/v1/webhooks/stripe', [
            'type' => 'customer.subscription.created',
        ], ['Stripe-Signature' => 't=1,v1=invalid']);

        $response->assertStatus(400);
        $response->assertJson(['error' => 'Invalid signature']);
    }

    public function test_it_creates_a_subscription_on_customer_subscription_created(): void
    {
        $school = $this->createSchool();
        $user = $this->createUserForSchool($school);
        $plan = $this->createPlan();

        $payload = [
            'id' => 'evt_1',
            'type' => 'customer.subscription.created',
            'data' => [
                'object' => [
                    'id' => 'sub_123',
                    'customer' => 'cus_123',
                    'status' => 'active',
                    'current_period_end' => now()->addMonth()->timestamp,
                    'metadata' => [
                        'user_id' => (string) $user->id,
                        'plan_id' => (string) $plan->id,
                    ],
                    'items' => [
                        'data' => [
                            ['price' => ['id' => 'price_123']],
                        ],
                    ],
                ],
            ],
        ];

        $this->postWebhook($payload)->assertOk();

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'school_id' => $school->id,
            'plan_id' => $plan->id,
            'stripe_subscription_id' => 'sub_123',
            'stripe_price_id' => 'price_123',
            'status' => 'active',
        ]);
    }

    public function test_it_updates_subscription_status_on_customer_subscription_updated(): void
    {
        $school = $this->createSchool();
        $user = $this->createUserForSchool($school);
        $plan = $this->createPlan();
        $subscription = Subscription::create([
            'school_id' => $school->id,
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'stripe_subscription_id' => 'sub_456',
        ]);

        $payload = [
            'id' => 'evt_2',
            'type' => 'customer.subscription.updated',
            'data' => [
                'object' => [
                    'id' => 'sub_456',
                    'status' => 'past_due',
                    'cancel_at_period_end' => true,
                    'current_period_end' => now()->addMonth()->timestamp,
                ],
            ],
        ];

        $this->postWebhook($payload)->assertOk();

        $subscription->refresh();
        $this->assertSame('past_due', $subscription->status);
        $this->assertTrue((bool) $subscription->cancel_at_period_end);
    }

    public function test_it_cancels_subscription_on_customer_subscription_deleted(): void
    {
        $school = $this->createSchool();
        $user = $this->createUserForSchool($school);
        $plan = $this->createPlan();
        $subscription = Subscription::create([
            'school_id' => $school->id,
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'stripe_subscription_id' => 'sub_789',
        ]);

        $payload = [
            'id' => 'evt_3',
            'type' => 'customer.subscription.deleted',
            'data' => ['object' => ['id' => 'sub_789']],
        ];

        $this->postWebhook($payload)->assertOk();

        $subscription->refresh();
        $this->assertSame('canceled', $subscription->status);
    }

    public function test_it_marks_subscription_active_on_invoice_payment_succeeded(): void
    {
        $school = $this->createSchool();
        $user = $this->createUserForSchool($school);
        $plan = $this->createPlan();
        $subscription = Subscription::create([
            'school_id' => $school->id,
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'past_due',
            'stripe_subscription_id' => 'sub_321',
        ]);

        $payload = [
            'id' => 'evt_4',
            'type' => 'invoice.payment_succeeded',
            'data' => ['object' => ['subscription' => 'sub_321']],
        ];

        $this->postWebhook($payload)->assertOk();

        $subscription->refresh();
        $this->assertSame('active', $subscription->status);
    }

    public function test_it_marks_subscription_past_due_on_invoice_payment_failed(): void
    {
        $school = $this->createSchool();
        $user = $this->createUserForSchool($school);
        $plan = $this->createPlan();
        $subscription = Subscription::create([
            'school_id' => $school->id,
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'stripe_subscription_id' => 'sub_654',
        ]);

        $payload = [
            'id' => 'evt_5',
            'type' => 'invoice.payment_failed',
            'data' => ['object' => ['subscription' => 'sub_654']],
        ];

        $this->postWebhook($payload)->assertOk();

        $subscription->refresh();
        $this->assertSame('past_due', $subscription->status);
    }
}
