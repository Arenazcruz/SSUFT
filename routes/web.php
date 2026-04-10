<?php

use App\Http\Controllers\Admin\ReunionController as AdminReunionController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\ReunionController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\Teacher\ReunionController as TeacherReunionController;
use App\Http\Controllers\TeacherDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingController::class)->name('landing');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/reuniones/{reunion}', [ReunionController::class, 'show'])->name('reuniones.show');

    Route::middleware('role:estudiante')->group(function (): void {
        Route::get('/estudiante/dashboard', [StudentDashboardController::class, 'index'])->name('student.dashboard');
    });

    Route::middleware('role:docente')->group(function (): void {
        Route::get('/docente/dashboard', [TeacherDashboardController::class, 'index'])->name('teacher.dashboard');
        Route::post('/docente/reuniones', [TeacherReunionController::class, 'store'])->name('teacher.reuniones.store');
        Route::patch('/docente/reuniones/{reunion}', [TeacherReunionController::class, 'update'])->name('teacher.reuniones.update');
        Route::delete('/docente/reuniones/{reunion}', [TeacherReunionController::class, 'destroy'])->name('teacher.reuniones.destroy');
    });

    Route::middleware('role:administrador')->group(function (): void {
        Route::get('/administrador/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::post('/administrador/usuarios', [AdminUserController::class, 'store'])->name('admin.users.store');
        Route::patch('/administrador/usuarios/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
        Route::delete('/administrador/usuarios/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
        Route::patch('/administrador/reuniones/{reunion}', [AdminReunionController::class, 'update'])->name('admin.reuniones.update');
        Route::delete('/administrador/reuniones/{reunion}', [AdminReunionController::class, 'destroy'])->name('admin.reuniones.destroy');
    });
});
