<?php

namespace Tests\Concerns;

use App\Models\Plan;
use App\Models\School;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

trait CreatesTenants
{
    protected function createSchool(array $attributes = []): School
    {
        return School::create(array_merge([
            'name' => 'School ' . Str::random(6),
            'slug' => Str::slug('school-' . Str::random(8)),
            'invite_code' => strtoupper(Str::random(8)),
            'active' => true,
        ], $attributes));
    }

    protected function createUserForSchool(School $school, array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'school_id' => $school->id,
        ], $attributes));
    }

    protected function createPlan(array $attributes = []): Plan
    {
        return Plan::create(array_merge([
            'name' => 'Basic',
            'price' => 49.90,
            'billing_cycle' => 'monthly',
            'active' => true,
        ], $attributes));
    }

    protected function createActiveSubscription(User $user, ?Plan $plan = null): Subscription
    {
        $plan ??= $this->createPlan();

        return Subscription::create([
            'school_id' => $user->school_id,
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
        ]);
    }

    /**
     * Create a school + user with an active subscription and authenticate as them.
     */
    protected function actingAsSubscribedUser(array $userAttributes = []): User
    {
        $school = $this->createSchool();
        $user = $this->createUserForSchool($school, $userAttributes);
        $this->createActiveSubscription($user);

        Sanctum::actingAs($user);

        return $user;
    }
}
