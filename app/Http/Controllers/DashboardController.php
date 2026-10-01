<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Institute;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isGlobalUser()) {
            return view('content.dashboard.super-admin', [
                'totalInstitutes' => Institute::count(),
                'activeInstitutes' => Institute::active()->count(),
                'totalUsers' => User::count(),
                'recentInstitutes' => Institute::latest()->take(5)->get(),
            ]);
        }

        if ($user->hasRole(User::ROLE_INSTITUTE_ADMIN)) {
            // Teacher aur ActivityLog par institute global scope already laga hai
            return view('content.dashboard.institute-admin', [
                'institute' => $user->institute,
                'teacherCount' => Teacher::count(),
                'userCount' => User::where('institute_id', $user->institute_id)->count(),
                'recentActivity' => ActivityLog::with('user')
                    ->where('institute_id', $user->institute_id)
                    ->latest()->take(10)->get(),
            ]);
        }

        return view('content.dashboard.teacher', [
            'user' => $user->load('institute', 'teacher'),
            'permissions' => $user->getAllPermissions()->pluck('name')->sort()->values(),
        ]);
    }
}