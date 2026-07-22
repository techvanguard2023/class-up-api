<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\School;
use App\Models\SchoolType;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_registration_assigns_the_free_plan_subscription(): void
    {
        $type = SchoolType::create(['name' => 'Idiomas', 'description' => 'x']);
        $free = Plan::create([
            'name' => 'Gratuito',
            'price' => 0,
            'billing_cycle' => 'monthly',
            'active' => true,
        ]);

        $response = $this->postJson('/api/v1/register', [
            'name' => 'Ana',
            'last_name' => 'Souza',
            'email' => 'ana@escola.test',
            'phone' => '11999999999',
            'role' => 'admin',
            'password' => 'Senha@123',
            'password_confirmation' => 'Senha@123',
            'school_name' => 'Escola Nova',
            'school_type_id' => $type->id,
        ]);

        $response->assertCreated();

        $user = User::where('email', 'ana@escola.test')->first();
        $this->assertNotNull($user->subscription);
        $this->assertSame($free->id, $user->subscription->plan_id);
        $this->assertTrue($user->subscription->isActive());
    }

    public function test_new_admin_can_immediately_access_subscription_gated_routes(): void
    {
        SchoolType::create(['name' => 'Idiomas', 'description' => 'x']);
        Plan::create(['name' => 'Gratuito', 'price' => 0, 'billing_cycle' => 'monthly', 'active' => true]);

        $token = $this->postJson('/api/v1/register', [
            'name' => 'Ana',
            'last_name' => 'Souza',
            'email' => 'ana@escola.test',
            'phone' => '11999999999',
            'role' => 'admin',
            'password' => 'Senha@123',
            'password_confirmation' => 'Senha@123',
            'school_name' => 'Escola Nova',
            'school_type_id' => SchoolType::first()->id,
        ])->json('token');

        // instructors is behind the validate.subscription middleware.
        $this->withToken($token)
            ->getJson('/api/v1/instructors')
            ->assertOk();
    }
}
