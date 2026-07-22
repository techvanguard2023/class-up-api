<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToSchool;

class Attendance extends Model
{
    use SoftDeletes, BelongsToSchool;

    protected $fillable = ['school_id', 'class_session_id', 'student_id', 'date', 'status'];

    protected $casts = [
        'date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        $recalculate = fn ($attendance) => $attendance->recalculateStudentAttendanceRate();

        static::saved($recalculate);
        static::deleted($recalculate);
        static::restored($recalculate);
    }

    /**
     * Recompute and persist the owning student's attendance_rate (% present).
     */
    public function recalculateStudentAttendanceRate(): void
    {
        // Drop only the tenant scope; keep the soft-delete scope so deleted
        // attendance records are excluded from the calculation.
        $student = Student::withoutGlobalScope('school')->find($this->student_id);

        if (!$student) {
            return;
        }

        $total = static::withoutGlobalScope('school')
            ->where('student_id', $student->id)
            ->count();

        $present = static::withoutGlobalScope('school')
            ->where('student_id', $student->id)
            ->where('status', 'present')
            ->count();

        $rate = $total > 0 ? round(($present / $total) * 100, 2) : 0;

        $student->forceFill(['attendance_rate' => $rate])->saveQuietly();
    }

    public function classSession()
    {
        return $this->belongsTo(ClassSession::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
