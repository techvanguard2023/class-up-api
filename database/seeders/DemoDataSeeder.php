<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\ClassSession;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Instructor;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Plan;
use App\Models\School;
use App\Models\SchoolPaymentPlan;
use App\Models\SchoolType;
use App\Models\Student;
use App\Models\StudentPaymentPlan;
use App\Models\Subject;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Seeds one fully-populated demo school so every module has realistic data.
 *
 * Run with:  php artisan db:seed --class=DemoDataSeeder
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Idempotent: wipe any previous demo run first.
        $existing = School::where('name', 'Escola Demo ClassUp')->get();
        foreach ($existing as $old) {
            Subscription::where('school_id', $old->id)->forceDelete();
            $old->forceDelete();
        }
        User::where('email', 'demo.admin@classup.test')->forceDelete();

        $schoolType = SchoolType::first() ?? SchoolType::create([
            'name' => 'Escola de Idiomas',
            'description' => 'Ensino de idiomas',
        ]);

        // ── School + admin ────────────────────────────────────────────────
        $admin = User::create([
            'name' => 'Ana',
            'last_name' => 'Souza',
            'email' => 'demo.admin@classup.test',
            'password' => Hash::make('Senha@123'),
            'role' => 'admin',
        ]);

        $school = School::create([
            'name' => 'Escola Demo ClassUp',
            'slug' => 'escola-demo-' . Str::lower(Str::random(5)),
            'school_type_id' => $schoolType->id,
            'owner_id' => $admin->id,
            'phone' => '(11) 90000-0000',
            'invite_code' => strtoupper(Str::random(6)),
            'active' => true,
        ]);

        $admin->update(['school_id' => $school->id]);

        // ── Active subscription (Ouro = unlimited) ────────────────────────
        $plan = Plan::where('name', 'Ouro')->first() ?? Plan::first();
        if ($plan) {
            Subscription::create([
                'school_id' => $school->id,
                'user_id' => $admin->id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'starts_at' => now()->subMonth(),
                'ends_at' => now()->addMonth(),
                'payment_method' => 'stripe',
            ]);
        }

        // ── Instructors ───────────────────────────────────────────────────
        $instructors = collect([
            ['name' => 'Carlos Mendes', 'email' => 'carlos@demo.test', 'qualification' => 'Inglês C2'],
            ['name' => 'Beatriz Lima', 'email' => 'beatriz@demo.test', 'qualification' => 'Espanhol C1'],
        ])->map(fn ($data) => Instructor::create(array_merge($data, [
            'school_id' => $school->id,
            'phone' => '(11) 9' . fake()->numerify('####-####'),
            'active' => true,
        ])));

        // ── Subjects ──────────────────────────────────────────────────────
        $subjects = collect(['Inglês', 'Espanhol', 'Conversação'])
            ->map(fn ($name) => Subject::create([
                'school_id' => $school->id,
                'name' => $name,
                'description' => "Disciplina de {$name}",
            ]));

        // ── Classrooms (note the small capacity to exercise limits) ───────
        // NOTE: classrooms table has no instructor_id column (model/schema
        // mismatch), so instructor is assigned at the class_session level below.
        $turmaA = Classroom::create([
            'school_id' => $school->id,
            'name' => 'Turma A - Inglês Básico',
            'capacity' => 5,
            'year' => (int) now()->year,
            'shift' => 'Manhã',
            'level' => 'Básico',
        ]);

        $turmaB = Classroom::create([
            'school_id' => $school->id,
            'name' => 'Turma B - Espanhol Intermediário',
            'capacity' => 8,
            'year' => (int) now()->year,
            'shift' => 'Noite',
            'level' => 'Intermediário',
        ]);

        // ── Students ──────────────────────────────────────────────────────
        $students = collect(range(1, 10))->map(fn ($i) => Student::create([
            'school_id' => $school->id,
            'name' => fake()->name(),
            'modality' => 'presencial',
            'level' => 'Básico',
            'status' => 'active',
            'birth_date' => fake()->dateTimeBetween('-25 years', '-10 years')->format('Y-m-d'),
        ]));

        // ── Enrollments ───────────────────────────────────────────────────
        // 7 students into Turma A (capacity 5!) to show capacity is not enforced.
        $students->take(7)->each(fn ($student) => Enrollment::create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'classroom_id' => $turmaA->id,
            'status' => 'active',
            'year' => (int) now()->year,
        ]));

        $students->slice(7)->each(fn ($student) => Enrollment::create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'classroom_id' => $turmaB->id,
            'status' => 'active',
            'year' => (int) now()->year,
        ]));

        // ── Class session + attendances + grades for Turma A ──────────────
        $session = ClassSession::create([
            'school_id' => $school->id,
            'name' => 'Inglês Básico - Seg/Qua',
            'instructor_id' => $instructors[0]->id,
            'classroom_id' => $turmaA->id,
            'subject_id' => $subjects[0]->id,
            'start_time' => '08:00',
            'end_time' => '09:30',
            'days' => ['Monday', 'Wednesday'],
            'modality' => 'presencial',
            'color' => '#4f46e5',
        ]);

        // 4 class dates, random present/absent — so attendance_rate COULD be computed.
        $turmaAStudents = $students->take(7);
        foreach (range(0, 3) as $d) {
            $date = now()->subWeeks(3 - $d)->format('Y-m-d');
            foreach ($turmaAStudents as $student) {
                Attendance::create([
                    'school_id' => $school->id,
                    'class_session_id' => $session->id,
                    'student_id' => $student->id,
                    'date' => $date,
                    'status' => fake()->randomElement(['present', 'present', 'present', 'absent']),
                ]);
            }
        }

        // Grades for the enrollments in Turma A
        Enrollment::where('classroom_id', $turmaA->id)->get()->each(function ($enrollment) use ($subjects) {
            Grade::create([
                'school_id' => $enrollment->school_id,
                'enrollment_id' => $enrollment->id,
                'subject_id' => $subjects[0]->id,
                'period' => '1º Bimestre',
                'value' => fake()->randomFloat(2, 5, 10),
                'weight' => 1,
            ]);
        });

        // ── Finance: payment plan, method, monthly payments ───────────────
        $paymentPlan = SchoolPaymentPlan::create([
            'school_id' => $school->id,
            'name' => 'Mensalidade Padrão',
            'description' => 'Plano mensal de idiomas',
            'price' => 250.00,
            'due_day' => 10,
            'active' => true,
        ]);

        $pix = PaymentMethod::create([
            'school_id' => $school->id,
            'name' => 'PIX',
            'active' => true,
        ]);

        $students->each(function ($student) use ($paymentPlan, $pix) {
            StudentPaymentPlan::create([
                'student_id' => $student->id,
                'school_payment_plan_id' => $paymentPlan->id,
                'due_day' => 10,
                'start_date' => now()->startOfYear()->format('Y-m-d'),
                'active' => true,
            ]);

            // 3 months: 1 paid, 1 pending, 1 overdue
            Payment::create([
                'student_id' => $student->id,
                'school_payment_plan_id' => $paymentPlan->id,
                'payment_method_id' => $pix->id,
                'amount' => 250.00,
                'due_date' => now()->subMonth()->day(10),
                'paid_date' => now()->subMonth()->day(9),
                'status' => 'paid',
            ]);
            Payment::create([
                'student_id' => $student->id,
                'school_payment_plan_id' => $paymentPlan->id,
                'amount' => 250.00,
                'due_date' => now()->day(10),
                'status' => 'pending',
            ]);
            Payment::create([
                'student_id' => $student->id,
                'school_payment_plan_id' => $paymentPlan->id,
                'amount' => 250.00,
                'due_date' => now()->subMonths(2)->day(10),
                'status' => 'late',
            ]);
        });

        $this->command->info("Demo school created: {$school->name} (id={$school->id})");
        $this->command->info("Login: demo.admin@classup.test / Senha@123");
    }
}
