<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTenants;
use Tests\TestCase;

class SubscriptionControllerTest extends TestCase
{
    use RefreshDatabase, CreatesTenants;

    public function test_status_reports_no_active_subscription_when_none_exists(): void
    {
        $school = $this->createSchool();
        $user = $this->createUserForSchool($school);
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/v1/subscription/status');

        $response->assertOk();
        $response->assertJson([
            'active' => false,
            'message' => 'No active subscription',
        ]);
    }

    public function test_status_reports_active_subscription_details(): void
    {
        $user = $this->actingAsSubscribedUser();

        $response = $this->getJson('/api/v1/subscription/status');

        $response->assertOk();
        $response->assertJson([
            'active' => true,
            'status' => 'active',
        ]);
    }

    public function test_checkout_requires_an_existing_plan(): void
    {
        $this->actingAsSubscribedUser();

        $this->postJson('/api/v1/subscription/checkout', ['plan_id' => 999999])
            ->assertUnprocessable();
    }

    public function test_checkout_rejects_a_plan_without_a_stripe_price(): void
    {
        $this->actingAsSubscribedUser();
        $plan = $this->createPlan(['stripe_price_id' => null]);

        $response = $this->postJson('/api/v1/subscription/checkout', ['plan_id' => $plan->id]);

        $response->assertStatus(400);
        $response->assertJson(['code' => 'PLAN_NOT_AVAILABLE']);
    }

    public function test_cancel_returns_404_when_there_is_no_subscription(): void
    {
        $school = $this->createSchool();
        $user = $this->createUserForSchool($school);
        $this->actingAs($user, 'sanctum');

        $this->postJson('/api/v1/subscription/cancel')
            ->assertNotFound()
            ->assertJson(['code' => 'SUBSCRIPTION_NOT_FOUND']);
    }

    public function test_resume_returns_400_when_subscription_is_not_scheduled_for_cancellation(): void
    {
        $this->actingAsSubscribedUser();

        $this->postJson('/api/v1/subscription/resume')
            ->assertStatus(400)
            ->assertJson(['code' => 'SUBSCRIPTION_NOT_CANCELABLE']);
    }
}
