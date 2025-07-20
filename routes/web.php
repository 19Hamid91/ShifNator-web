<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScheduleController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::redirect('/', 'login');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('employee')->group(function () {
        Route::get('/', [EmployeeController::class, 'index'])->name('employee.index');
        Route::get('/table', [EmployeeController::class, 'table'])->name('employee.table');
        Route::get('/create', [EmployeeController::class, 'create'])->name('employee.create');
        Route::post('/store', [EmployeeController::class, 'store'])->name('employee.store');
        Route::get('/edit/{employee}', [EmployeeController::class, 'edit'])->name('employee.edit');
        Route::patch('/update/{employee}', [EmployeeController::class, 'update'])->name('employee.update');
    });

    Route::prefix('schedule')->group(function () {
        Route::get('/', [ScheduleController::class, 'index'])->name('schedule.index');
        Route::get('/table', [ScheduleController::class, 'table'])->name('schedule.table');
        Route::get('/create', [ScheduleController::class, 'create'])->name('schedule.create');
        Route::get('/result/{id}', [ScheduleController::class, 'result'])->name('schedule.result');
        Route::post('/store', [ScheduleController::class, 'store'])->name('schedule.store');
        Route::patch('/update/{schedule}', [ScheduleController::class, 'update'])->name('schedule.update');
        Route::patch('/change/{schedule}', [ScheduleController::class, 'change'])->name('schedule.change');
    });
});

require __DIR__ . '/auth.php';
