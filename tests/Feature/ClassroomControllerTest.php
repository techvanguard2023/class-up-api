<?php

namespace Tests\Feature;

use App\Models\Instructor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTenants;
use Tests\TestCase;

class ClassroomControllerTest extends TestCase
{
    use RefreshDatabase, CreatesTenants;

    private function makeInstructor($school): Instructor
    {
        return Instructor::create([
            'school_id' => $school->id,
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'active' => true,
        ]);
    }

    public function test_it_creates_a_classroom_scoped_to_the_callers_school(): void
    {
        $user = $this->actingAsSubscribedUser();

        $response = $this->postJson('/api/v1/classrooms', [
            'name' => 'Turma A',
            'capacity' => 20,
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('classrooms', [
            'name' => 'Turma A',
            'school_id' => $user->school_id,
        ]);
    }

    public function test_it_ignores_a_spoofed_school_id_in_the_request(): void
    {
        $user = $this->actingAsSubscribedUser();
        $otherSchool = $this->createSchool();

        $this->postJson('/api/v1/classrooms', [
            'name' => 'Turma Falsa',
            'capacity' => 10,
            'school_id' => $otherSchool->id,
        ])->assertCreated();

        // Must land in the caller's school, never the spoofed one.
        $this->assertDatabaseHas('classrooms', [
            'name' => 'Turma Falsa',
            'school_id' => $user->school_id,
        ]);
        $this->assertDatabaseMissing('classrooms', [
            'name' => 'Turma Falsa',
            'school_id' => $otherSchool->id,
        ]);
    }

    public function test_it_assigns_an_instructor_from_the_same_school(): void
    {
        $user = $this->actingAsSubscribedUser();
        $instructor = $this->makeInstructor($user->school);

        $this->postJson('/api/v1/classrooms', [
            'name' => 'Turma A',
            'capacity' => 20,
            'instructor_id' => $instructor->id,
        ])->assertCreated()
          ->assertJsonFragment(['instructor_id' => $instructor->id]);
    }

    public function test_it_rejects_an_instructor_from_another_school(): void
    {
        $this->actingAsSubscribedUser();
        $otherSchool = $this->createSchool();
        $otherInstructor = $this->makeInstructor($otherSchool);

        $this->postJson('/api/v1/classrooms', [
            'name' => 'Turma A',
            'capacity' => 20,
            'instructor_id' => $otherInstructor->id,
        ])->assertUnprocessable()
          ->assertJsonValidationErrors('instructor_id');
    }
}
