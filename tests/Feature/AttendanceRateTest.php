<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\ClassSession;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesTenants;
use Tests\TestCase;

class AttendanceRateTest extends TestCase
{
    use RefreshDatabase, CreatesTenants;

    private function makeSession($school): ClassSession
    {
        return ClassSession::create([
            'school_id' => $school->id,
            'name' => 'Aula',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'days' => ['Monday'],
            'modality' => 'presencial',
        ]);
    }

    private function record(ClassSession $session, Student $student, string $status, string $date): Attendance
    {
        return Attendance::create([
            'school_id' => $session->school_id,
            'class_session_id' => $session->id,
            'student_id' => $student->id,
            'date' => $date,
            'status' => $status,
        ]);
    }

    public function test_attendance_rate_is_recalculated_when_records_are_created(): void
    {
        $user = $this->actingAsSubscribedUser();
        $student = Student::create([
            'school_id' => $user->school_id,
            'name' => 'Aluno Teste',
            'modality' => 'presencial',
            'level' => 'Básico',
            'status' => 'active',
        ]);
        $session = $this->makeSession($user->school);

        $this->record($session, $student, 'present', '2026-07-01');
        $this->record($session, $student, 'present', '2026-07-08');
        $this->record($session, $student, 'present', '2026-07-15');
        $this->record($session, $student, 'absent', '2026-07-22');

        // 3 of 4 present = 75%
        $this->assertEquals(75.0, $student->fresh()->attendance_rate);
    }

    public function test_attendance_rate_updates_when_a_record_changes(): void
    {
        $user = $this->actingAsSubscribedUser();
        $student = Student::create([
            'school_id' => $user->school_id,
            'name' => 'Aluno Teste',
            'modality' => 'presencial',
            'level' => 'Básico',
            'status' => 'active',
        ]);
        $session = $this->makeSession($user->school);

        $a = $this->record($session, $student, 'absent', '2026-07-01');
        $this->record($session, $student, 'present', '2026-07-08');

        $this->assertEquals(50.0, $student->fresh()->attendance_rate);

        $a->update(['status' => 'present']);

        $this->assertEquals(100.0, $student->fresh()->attendance_rate);
    }

    public function test_attendance_rate_updates_when_a_record_is_deleted(): void
    {
        $user = $this->actingAsSubscribedUser();
        $student = Student::create([
            'school_id' => $user->school_id,
            'name' => 'Aluno Teste',
            'modality' => 'presencial',
            'level' => 'Básico',
            'status' => 'active',
        ]);
        $session = $this->makeSession($user->school);

        $absent = $this->record($session, $student, 'absent', '2026-07-01');
        $this->record($session, $student, 'present', '2026-07-08');
        $this->assertEquals(50.0, $student->fresh()->attendance_rate);

        $absent->delete();

        // Only the present record remains = 100%
        $this->assertEquals(100.0, $student->fresh()->attendance_rate);
    }
}
