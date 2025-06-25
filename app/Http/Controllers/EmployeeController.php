<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
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

        // $data->setCollection(
        //     $data->getCollection()->map(function ($item) {
        //         $item->rngIp = $item->rngIp ?? '-';
        //         return $item;
        //     })
        // );

        return response()->json([
            'data' => $data,
        ]);
    }
}
