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
        Route::get('/store', [EmployeeController::class, 'store'])->name('employee.store');
        Route::get('/edit', [EmployeeController::class, 'edit'])->name('employee.edit');
        Route::get('/update', [EmployeeController::class, 'update'])->name('employee.edit');
    });

    Route::prefix('schedule')->group(function () {
        Route::get('/', [ScheduleController::class, 'index'])->name('schedule.index');
    });
});

require __DIR__ . '/auth.php';
