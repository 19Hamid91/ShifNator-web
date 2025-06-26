<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ScheduleController extends Controller
{
    public function index()
    {
        return Inertia::render('Schedule/Index');
    }

    public function table(Request $request)
    {
        $query = Schedule::query();

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('start_date', 'LIKE', "%{$search}%")
                    ->orWhere('end_date', 'LIKE', "%{$search}%")
                    ->orWhere('max_shift_per_employee', 'LIKE', "%{$search}%")
                    ->orWhere('status', 'LIKE', "%{$search}%");
            });
        }

        $perPage = (int) $request->input('per_page', 10);
        $sortBy = $request->input('sort_by', 'start_date');
        $order = $request->input('order', 'desc');

        $data = $query->orderBy($sortBy, $order)
            ->paginate($perPage);

        $data->setCollection(
            $data->getCollection()->map(function ($item) {
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
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:active,inactive',
            'selected_employees' => 'required|array',
            'selected_employees.*' => 'exists:employees,id',
        ]);

        // try {
        return redirect()->route('schedule.result')
            ->with('success', 'Schedule generated successfully');
        // } catch (\Exception $e) {
        //     Log::error('Failed to update employee: ' . $e->getMessage());

        //     return back()->with('error', 'There is an error when generating schedule');
        // }
    }

    public function result(Request $request)
    {
        $employees = Employee::where('status', 'active')->select(['id', 'name', 'unavailable_shift'])->get()->map(function ($item) {
            $item->unavailable_shift = $item->unavailable_shift ? json_decode($item->unavailable_shift, true) : null;
            return $item;
        });

        return Inertia('Schedule/Result', [
            'employees' => $employees
        ]);
    }
}
