<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InstituteAdminController;
use App\Http\Controllers\InstituteController;
use App\Http\Controllers\language\LanguageController;
use App\Http\Controllers\pages\MiscError;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\UserRoleController;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

// Authenticated routes
Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Dashboard  
    Route::get('/', [DashboardController::class, 'index'])->name('pages-home');

    // Institutes (Super Admin)
    Route::resource('institutes', InstituteController::class)->except(['destroy']);
    Route::patch('institutes/{institute}/status', [InstituteController::class, 'toggleStatus'])
        ->name('institutes.toggle-status');

    // Institute Admins (Super Admin)
    Route::resource('institute-admins', InstituteAdminController::class)
        ->parameters(['institute-admins' => 'admin'])
        ->only(['index', 'create', 'store', 'edit', 'update']);
    Route::patch('institute-admins/{admin}/status', [InstituteAdminController::class, 'toggleStatus'])
        ->name('institute-admins.toggle-status');

    // Teachers (institute scoped)
    Route::resource('teachers', TeacherController::class)->except(['show']);
    Route::patch('teachers/{teacher}/status', [TeacherController::class, 'toggleStatus'])
        ->name('teachers.toggle-status');

    // Roles (institute scoped)
    Route::resource('roles', RoleController::class)->except(['show']);

    // Users  
    Route::get('users', [UserRoleController::class, 'index'])->name('users.index');
    Route::get('users/{user}/roles', [UserRoleController::class, 'edit'])->name('users.roles.edit');
    Route::put('users/{user}/roles', [UserRoleController::class, 'update'])->name('users.roles.update');
});

// locale
Route::get('/lang/{locale}', [LanguageController::class, 'swap']);
Route::get('/pages/misc-error', [MiscError::class, 'index'])->name('pages-misc-error');