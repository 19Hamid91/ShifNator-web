<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Schedule;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $employeeCount = Employee::where('status', 'active')->count();
        $scheduleCount = Schedule::count();

        $schedule = Schedule::latest()->first();

        $shift = [];
        $periode = null;

        if ($schedule) {
            $employees = Employee::where('status', 'active')
                ->select(['id', 'name'])
                ->get()
                ->keyBy('id');

            $periode = Carbon::create()
                ->month($schedule->month)
                ->format('F') . ' - ' . $schedule->year ?? '-';

            $result = $schedule->result ? json_decode($schedule->result) : [];

            $shiftColor = [
                'morning' => 'blue',
                'night' => 'deep-purple',
            ];

            foreach ($result as $date => $shifts) {
                foreach ($shifts as $shiftType => $employeeId) {
                    if (!$employeeId) continue;

                    $employeeName = $employees[$employeeId]->name ?? '-';

                    $shift[] = [
                        'title' => $employeeName,
                        'start' => $date,
                        'end' => $date,
                        'color' => $shiftColor[$shiftType] ?? 'grey',
                    ];
                }
            }
        }

        return Inertia::render('Dashboard', [
            'employeeCount' => $employeeCount,
            'scheduleCount' => $scheduleCount,
            'shift' => $shift,
            'schedule' => $schedule,
            'periode' => $periode,
        ]);
    }
}
