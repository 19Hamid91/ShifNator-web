<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ScheduleController extends Controller
{
    protected function generateShiftSchedule($year, $month, array $employees, int $maxShift, array $unavailableList): array
    {
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;

        $result = [];
        $shiftSlots = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::createFromDate($year, $month, $day)->toDateString();
            $result[$date] = [
                'morning' => null,
                'night' => null,
            ];

            $shiftSlots[] = [$date, 'morning'];
            $shiftSlots[] = [$date, 'night'];
        }

        shuffle($employees);
        $employeeShiftCount = array_fill_keys($employees, 0);
        $employeeIndex = 0;

        foreach ($shiftSlots as [$date, $shiftType]) {
            $dayName = strtolower(Carbon::parse($date)->locale('en_US')->isoFormat('dddd'));
            $assigned = false;
            $attempts = 0;

            while ($attempts < count($employees)) {
                $empId = $employees[$employeeIndex];
                $isUnavailable = $unavailableList[$empId][$dayName][$shiftType] ?? false;

                if (!$isUnavailable && $employeeShiftCount[$empId] < $maxShift) {
                    $result[$date][$shiftType] = $empId;
                    $employeeShiftCount[$empId]++;
                    $employeeIndex = ($employeeIndex + 1) % count($employees);
                    $assigned = true;
                    break;
                }

                $employeeIndex = ($employeeIndex + 1) % count($employees);
                $attempts++;
            }

            if (!$assigned) {
                throw new \Exception("Tidak bisa mengisi shift pada tanggal $date ($shiftType). Pegawai tidak mencukupi atau sudah mencapai batas shift.");
            }
        }

        return $result;
    }


    public function index()
    {
        return Inertia::render('Schedule/Index');
    }

    public function table(Request $request)
    {
        $query = Schedule::query();

        if ($search = $request->search) {
            $monthMap = [
                'january' => 1,
                'february' => 2,
                'march' => 3,
                'april' => 4,
                'may' => 5,
                'june' => 6,
                'july' => 7,
                'august' => 8,
                'september' => 9,
                'october' => 10,
                'november' => 11,
                'december' => 12,
            ];

            $searchLower = strtolower($search);
            $monthNumber = $monthMap[$searchLower] ?? null;

            $query->where(function ($q) use ($search, $monthNumber) {
                $q->where('year', 'LIKE', "%{$search}%")
                    ->orWhere('max_shift_per_employee', 'LIKE', "%{$search}%")
                    ->orWhere('status', 'LIKE', "%{$search}%");

                if ($monthNumber) {
                    $q->orWhere('month', $monthNumber);
                }
            });
        }


        $perPage = (int) $request->input('per_page', 10);
        $sortBy = $request->input('sort_by', 'year');
        $order = $request->input('order', 'desc');

        $data = $query->orderBy($sortBy, $order)
            ->paginate($perPage);

        $data->setCollection(
            $data->getCollection()->map(function ($item) {
                $item->periode = Carbon::create()->month($item->month)->format('F') .  ' - ' . $item->year;
                return $item;
            })
        );

        return response()->json([
            'data' => $data,
        ]);
    }

    public function create()
    {
        $employees = Employee::where('status', 'active')->select(['id', 'name', 'unavailable_shift'])->get()->map(function ($item) {
            $item->unavailable_shift = $item->unavailable_shift ? json_decode($item->unavailable_shift, true) : null;
            return $item;
        });
        return Inertia::render('Schedule/Create', ['employees' => $employees]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|digits:4|integer|min:1900|min:' . (date('Y')),
            'month' => 'required|in:1,2,3,4,5,6,7,8,9,10,11,12',
            'max_shift_per_employee' => 'required|numeric|min:1',
            'status' => 'required|in:active,inactive',
            'selected_employees' => 'required|array',
            'selected_employees.*' => 'exists:employees,id',
        ]);

        DB::beginTransaction();

        try {
            $year = $validated['year'];
            $month = $validated['month'];
            $employees = $validated['selected_employees'];
            $maxShift = $validated['max_shift_per_employee'];

            // data unavailable list
            $unavailableListRaw = Employee::whereIn('id', $validated['selected_employees'])
                ->pluck('unavailable_shift', 'id'); // key = empId
            $unavailableList = $unavailableListRaw->map(function ($v) {
                $decoded = json_decode($v, true);
                return is_array($decoded) ? $decoded : []; // fallback ke array kosong
            })->toArray();

            // generate jadwa
            $result = $this->generateShiftSchedule($year, $month, $employees, $maxShift, $unavailableList);

            // Simpan ke database
            $schedule = Schedule::create([
                'year' => $year,
                'month' => $month,
                'status' => $validated['status'],
                'max_shift_per_employee' => $maxShift,
                'selected_employees' => !empty($employees) ? json_encode($employees) : null,
                'result' => !empty($result) ? json_encode($result) : null,
            ]);

            DB::commit();

            return redirect()->route('schedule.result', $schedule->id)
                ->with('success', 'Schedule generated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to generate schedule: ' . $e->getMessage());
            $msg = $e->getMessage() ?? 'There is an error when generating schedule';
            return back()->with('error', $msg);
        }
    }

    public function result($id)
    {
        $employees = Employee::where('status', 'active')->select(['id', 'name'])->get()->keyBy('id');

        $schedule = Schedule::find($id);
        $periode = Carbon::create()->month($schedule->month)->format('F') .  ' - ' . $schedule->year;
        $result = $schedule->result ? json_decode($schedule->result) : [];

        $shiftColor = [
            'morning' => 'blue',
            'night' => 'deep-purple',
        ];

        $shift = [];

        foreach ($result as $date => $shifts) {
            foreach ($shifts as $shiftType => $employeeId) {
                if (!$employeeId) continue; // skip if null

                $employeeName = $employees[$employeeId]->name ?? '-';

                $shift[] = [
                    'title' => $employeeName,
                    'start' => $date,
                    'end' => $date,
                    'color' => $shiftColor[$shiftType] ?? 'grey',
                ];
            }
        }

        return Inertia('Schedule/Result', [
            'shift' => $shift,
            'schedule' => $schedule,
            'periode' => $periode
        ]);
    }

    public function change(Schedule $schedule)
    {
        try {
            $schedule->status = $schedule->status === 'inactive' ? 'active' : 'inactive';
            $schedule->save();

            return redirect()->route('schedule.index')
                ->with('success', 'Status changed successfully');
        } catch (\Exception $e) {
            Log::error('Failed to change schedule status: ' . $e->getMessage());

            return back()->with('error', 'Failed to change status');
        }
    }
}
