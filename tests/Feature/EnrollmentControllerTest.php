<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTenants;
use Tests\TestCase;

class EnrollmentControllerTest extends TestCase
{
    use RefreshDatabase, CreatesTenants;

    private function createStudent($school): Student
    {
        return Student::create([
            'school_id' => $school->id,
            'name' => fake()->name(),
            'modality' => 'presencial',
            'level' => 'Básico',
            'status' => 'active',
        ]);
    }

    private function createClassroom($school, int $capacity): Classroom
    {
        return Classroom::create([
            'school_id' => $school->id,
            'name' => 'Turma ' . fake()->randomLetter(),
            'capacity' => $capacity,
            'year' => (int) now()->year,
        ]);
    }

    public function test_it_enrolls_a_student_and_increments_the_classroom_counter(): void
    {
        $user = $this->actingAsSubscribedUser();
        $student = $this->createStudent($user->school);
        $classroom = $this->createClassroom($user->school, 5);

        $this->postJson('/api/v1/enrollments', [
            'student_id' => $student->id,
            'classroom_id' => $classroom->id,
            'year' => (int) now()->year,
        ])->assertCreated();

        $this->assertSame(1, $classroom->fresh()->enrolled);
    }

    public function test_it_rejects_enrolling_beyond_classroom_capacity(): void
    {
        $user = $this->actingAsSubscribedUser();
        $classroom = $this->createClassroom($user->school, 1);

        $first = $this->createStudent($user->school);
        $this->postJson('/api/v1/enrollments', [
            'student_id' => $first->id,
            'classroom_id' => $classroom->id,
            'year' => (int) now()->year,
        ])->assertCreated();

        $second = $this->createStudent($user->school);
        $this->postJson('/api/v1/enrollments', [
            'student_id' => $second->id,
            'classroom_id' => $classroom->id,
            'year' => (int) now()->year,
        ])->assertUnprocessable()
          ->assertJsonValidationErrors('classroom_id');

        $this->assertSame(1, $classroom->fresh()->enrolled);
    }

    public function test_it_rejects_enrolling_the_same_student_twice(): void
    {
        $user = $this->actingAsSubscribedUser();
        $classroom = $this->createClassroom($user->school, 5);
        $student = $this->createStudent($user->school);

        $payload = [
            'student_id' => $student->id,
            'classroom_id' => $classroom->id,
            'year' => (int) now()->year,
        ];

        $this->postJson('/api/v1/enrollments', $payload)->assertCreated();
        $this->postJson('/api/v1/enrollments', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('student_id');

        $this->assertSame(1, Enrollment::where('classroom_id', $classroom->id)->count());
    }

    public function test_it_rejects_enrolling_a_student_from_another_school(): void
    {
        $user = $this->actingAsSubscribedUser();
        $classroom = $this->createClassroom($user->school, 5);

        $otherSchool = $this->createSchool();
        $otherStudent = $this->createStudent($otherSchool);

        $this->postJson('/api/v1/enrollments', [
            'student_id' => $otherStudent->id,
            'classroom_id' => $classroom->id,
            'year' => (int) now()->year,
        ])->assertUnprocessable();
    }
}
