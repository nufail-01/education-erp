<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserRoleController extends Controller
{
    public function __construct(private ActivityLogger $logger)
    {
    }

    public function index(Request $request)
    {
        $actor = $request->user();
        abort_unless($actor->can('institute-users.view'), 403);

        $canAssign = ! $actor->isGlobalUser() && $actor->can('roles.assign');

        $users = User::query()
            ->with('institute')
            ->when($canAssign, fn ($q) => $q->with('roles'))
            ->when(! $actor->isGlobalUser(), fn ($q) => $q->where('institute_id', $actor->institute_id))
            ->orderBy('name')
            ->paginate(10);

        return view('content.users.index', compact('users', 'canAssign'));
    }

    public function edit(Request $request, User $user)
    {
        $this->authorizeTarget($request, $user);

        $roles = Role::where('institute_id', $user->institute_id)->orderBy('name')->get();
        $assignedIds = $roles->filter(fn ($role) => $user->hasRole($role))->pluck('id')->all();

        return view('content.users.roles', compact('user', 'roles', 'assignedIds'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $actor = $request->user();
        $this->authorizeTarget($request, $user);

        $candidates = Role::where('institute_id', $user->institute_id)->get();

        $request->validate([
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', Rule::in($candidates->pluck('id')->all())],
        ]);

        $wanted = array_map('intval', $request->input('roles', []));

        DB::transaction(function () use ($actor, $user, $candidates, $wanted) {
            foreach ($candidates as $role) {
                $has = $user->hasRole($role);
                $should = in_array($role->id, $wanted, true);

                if ($should && ! $has) {
                    abort_unless($actor->can('assign', [$role, $user]), 403);
                    $user->assignRole($role);
                } elseif (! $should && $has) {
                    abort_unless($actor->can('assign', [$role, $user]), 403);
                    $user->removeRole($role);
                }
            }

            $this->logger->log('user.roles-updated', "Roles updated for {$user->email}", $user, $user->institute_id);
        });

        return redirect()->route('users.index')->with('success', "Roles updated for {$user->email}.");
    }

    private function authorizeTarget(Request $request, User $user): void
    {
        $actor = $request->user();

        abort_unless($actor->can('roles.assign'), 403);
        abort_if($actor->isGlobalUser(), 403);
        abort_unless($user->institute_id === $actor->institute_id, 404);
    }
}