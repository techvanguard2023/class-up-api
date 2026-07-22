<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTenants;
use Tests\TestCase;

class PaymentControllerTest extends TestCase
{
    use RefreshDatabase, CreatesTenants;

    private function createStudentForSchool($school, array $attributes = []): Student
    {
        return Student::create(array_merge([
            'school_id' => $school->id,
            'name' => 'Student ' . fake()->firstName(),
            'modality' => 'online',
            'level' => 'beginner',
            'status' => 'active',
        ], $attributes));
    }

    private function createPaymentForStudent(Student $student, array $attributes = []): Payment
    {
        return Payment::create(array_merge([
            'student_id' => $student->id,
            'amount' => 100,
            'due_date' => now()->addWeek(),
            'status' => 'pending',
        ], $attributes));
    }

    public function test_it_lists_only_payments_belonging_to_the_authenticated_school(): void
    {
        $user = $this->actingAsSubscribedUser();
        $student = $this->createStudentForSchool($user->school);
        $payment = $this->createPaymentForStudent($student);

        $otherSchool = $this->createSchool();
        $otherStudent = $this->createStudentForSchool($otherSchool);
        $this->createPaymentForStudent($otherStudent);

        $response = $this->getJson('/api/v1/payments');

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['id' => $payment->id]);
    }

    public function test_it_prevents_viewing_a_payment_from_another_school(): void
    {
        $this->actingAsSubscribedUser();

        $otherSchool = $this->createSchool();
        $otherStudent = $this->createStudentForSchool($otherSchool);
        $otherPayment = $this->createPaymentForStudent($otherStudent);

        $this->getJson("/api/v1/payments/{$otherPayment->id}")->assertNotFound();
    }

    public function test_it_prevents_updating_a_payment_from_another_school(): void
    {
        $this->actingAsSubscribedUser();

        $otherSchool = $this->createSchool();
        $otherStudent = $this->createStudentForSchool($otherSchool);
        $otherPayment = $this->createPaymentForStudent($otherStudent);

        $this->putJson("/api/v1/payments/{$otherPayment->id}", ['amount' => 500])
            ->assertNotFound();

        $this->assertDatabaseHas('payments', ['id' => $otherPayment->id, 'amount' => 100.00]);
    }

    public function test_it_prevents_deleting_a_payment_from_another_school(): void
    {
        $this->actingAsSubscribedUser();

        $otherSchool = $this->createSchool();
        $otherStudent = $this->createStudentForSchool($otherSchool);
        $otherPayment = $this->createPaymentForStudent($otherStudent);

        $this->deleteJson("/api/v1/payments/{$otherPayment->id}")->assertNotFound();

        $this->assertDatabaseHas('payments', ['id' => $otherPayment->id]);
    }

    public function test_it_creates_a_payment_for_a_student_in_the_same_school(): void
    {
        $user = $this->actingAsSubscribedUser();
        $student = $this->createStudentForSchool($user->school);

        $response = $this->postJson('/api/v1/payments', [
            'student_id' => $student->id,
            'amount' => 150.00,
            'due_date' => now()->addWeek()->toDateString(),
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('payments', ['student_id' => $student->id, 'amount' => 150.00]);
    }

    public function test_it_rejects_creating_a_payment_for_a_student_in_another_school(): void
    {
        $this->actingAsSubscribedUser();

        $otherSchool = $this->createSchool();
        $otherStudent = $this->createStudentForSchool($otherSchool);

        $this->postJson('/api/v1/payments', [
            'student_id' => $otherStudent->id,
            'amount' => 150.00,
            'due_date' => now()->addWeek()->toDateString(),
        ])->assertNotFound();
    }

    public function test_it_cannot_edit_a_paid_payment(): void
    {
        $user = $this->actingAsSubscribedUser();
        $student = $this->createStudentForSchool($user->school);
        $payment = $this->createPaymentForStudent($student, ['status' => 'paid']);

        $this->putJson("/api/v1/payments/{$payment->id}", ['amount' => 999])
            ->assertUnprocessable();
    }

    public function test_mark_as_paid_updates_status_and_paid_date(): void
    {
        $user = $this->actingAsSubscribedUser();
        $student = $this->createStudentForSchool($user->school);
        $payment = $this->createPaymentForStudent($student);

        $this->postJson("/api/v1/payments/{$payment->id}/mark-as-paid")
            ->assertOk()
            ->assertJsonFragment(['status' => 'paid']);

        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'paid']);
    }
}
