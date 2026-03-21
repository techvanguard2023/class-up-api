<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    /**
     * Return student count grouped by month for the given period.
     *
     * Query params:
     *   - months (int, default 6): how many past months to include
     */
    public function studentGrowth(Request $request)
    {
        $months = (int) $request->query('months', 6);
        $months = min(max($months, 1), 24);

        $from = now()->subMonths($months - 1)->startOfMonth();

        $rows = Student::where('created_at', '>=', $from)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        // Fill in months with 0 so the frontend always gets a complete series
        $series = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $series[] = [
                'month' => $key,
                'total' => $rows->has($key) ? (int) $rows[$key]->total : 0,
            ];
        }

        $totalActive = Student::where('status', 'active')->count();
        $totalAll    = Student::count();

        return response()->json([
            'growth'        => $series,
            'total_active'  => $totalActive,
            'total_students' => $totalAll,
        ]);
    }
}
