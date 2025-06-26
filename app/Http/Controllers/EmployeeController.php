<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class EmployeeController extends Controller
{
    public function index()
    {
        return Inertia::render('Employee/Index');
    }

    public function table(Request $request)
    {
        $query = Employee::query();

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('status', 'LIKE', "%{$search}%");
            });
        }

        $perPage = (int) $request->input('per_page', 10);
        $sortBy = $request->input('sort_by', 'name');
        $order = $request->input('order', 'desc');

        $data = $query->orderBy($sortBy, $order)
            ->paginate($perPage);

        $data->setCollection(
            $data->getCollection()->map(function ($item) {
                $shifts = json_decode($item->unavailable_shift, true);
                $display = [];

                foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
                    $dayShifts = collect($shifts[$day] ?? [])
                        ->filter(fn($v) => $v === true)
                        ->keys();

                    if ($dayShifts->isNotEmpty()) {
                        $display[] = ucfirst($day) . ' (' . $dayShifts->implode(', ') . ')';
                    }
                }

                $item->unavailable_shift_display = $display;
                return $item;
            })
        );

        return response()->json([
            'data' => $data,
        ]);
    }

    public function create()
    {
        return Inertia::render('Employee/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('employees', 'name')],
            'status' => ['required', 'in:active,inactive'],
            'unavailable_shift' => ['nullable', 'array'],
        ]);

        try {
            Employee::create([
                'name' => $validated['name'],
                'status' => $validated['status'],
                'unavailable_shift' => !empty($validated['unavailable_shift'])
                    ? json_encode($validated['unavailable_shift'])
                    : null,

            ]);

            return back()->with('success', 'Employee created successfully');
        } catch (\Exception $e) {
            Log::error('Failed to update employee: ' . $e->getMessage());

            return back()->with('error', 'There is an error when creating employee');
        }
    }

    public function edit(Employee $employee)
    {
        return Inertia::render('Employee/Edit', [
            'id' => $employee->id,
            'name' => $employee->name,
            'status' => $employee->status,
            'unavailable_shift' => $employee->unavailable_shift ? json_decode($employee->unavailable_shift, true) : null,
        ]);
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('employees', 'name')->ignore($employee->id)],
            'status' => ['required', 'in:active,inactive'],
            'unavailable_shift' => ['nullable', 'array'],
        ]);

        try {
            $employee->update([
                'name' => $validated['name'],
                'status' => $validated['status'],
                'unavailable_shift' => !empty($validated['unavailable_shift'])
                    ? json_encode($validated['unavailable_shift'])
                    : null,
            ]);

            return redirect()->route('employee.edit', $employee->id)
                ->with('success', 'Employee updated successfully');
        } catch (\Exception $e) {
            Log::error('Failed to update employee: ' . $e->getMessage());

            return back()->with('error', 'There is an error when updating employee');
        }
    }
}
