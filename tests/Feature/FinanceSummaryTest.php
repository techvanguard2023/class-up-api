<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\SchoolPaymentPlan;
use App\Models\Student;
use App\Models\StudentPaymentPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTenants;
use Tests\TestCase;

class FinanceSummaryTest extends TestCase
{
    use RefreshDatabase, CreatesTenants;

    public function test_conversion_rate_is_consistent_with_the_monthly_series(): void
    {
        $user = $this->actingAsSubscribedUser();

        $plan = SchoolPaymentPlan::create([
            'school_id' => $user->school_id,
            'name' => 'Mensalidade',
            'price' => 100,
            'due_day' => 10,
            'active' => true,
        ]);

        $student = Student::create([
            'school_id' => $user->school_id,
            'name' => 'Aluno',
            'modality' => 'presencial',
            'level' => 'Básico',
            'status' => 'active',
        ]);

        // Active plan for the whole window (expected 100/month).
        StudentPaymentPlan::create([
            'student_id' => $student->id,
            'school_payment_plan_id' => $plan->id,
            'due_day' => 10,
            'start_date' => now()->subMonths(11)->startOfMonth(),
            'active' => true,
        ]);

        // Only ONE month actually collected.
        Payment::create([
            'student_id' => $student->id,
            'school_payment_plan_id' => $plan->id,
            'amount' => 100,
            'due_date' => now()->subMonth()->day(10),
            'paid_date' => now()->subMonth()->day(9),
            'status' => 'paid',
        ]);

        $data = $this->getJson('/api/v1/finance/summary?months=12')->assertOk()->json();

        $monthlyExpected = array_sum(array_column($data['monthly'], 'subscriptions'));
        $monthlyRevenue  = array_sum(array_column($data['monthly'], 'revenue'));

        // Headline must match the aggregated monthly series.
        $this->assertEqualsWithDelta($monthlyExpected, $data['period_expected'], 0.01);

        $expectedRate = round(($monthlyRevenue / $monthlyExpected) * 100, 2);
        $this->assertEqualsWithDelta($expectedRate, $data['conversion_rate'], 0.01);

        // With only 1 of ~12 months collected, it must be far below 100%.
        $this->assertLessThan(100, $data['conversion_rate']);
    }
}
