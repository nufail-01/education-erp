<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(private ActivityLogger $logger)
    {
    }

    public function show(Request $request)
    {
        $user = $request->user()->load('institute', 'teacher');

        return view('content.profile.show', compact('user'));
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->only(['name', 'email', 'mobile_no']))->save();

        $this->logger->log('profile.updated', "Profile updated for {$user->email}", $user, $user->institute_id);

        return redirect()->route('profile.show')->with('success', 'Profile updated.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->password = $request->input('password');
        $user->save();

        $request->session()->regenerate();

        $this->logger->log('profile.password-changed', "Password changed for {$user->email}", $user, $user->institute_id);

        return redirect()->route('profile.show')->with('success', 'Password updated.');
    }
}