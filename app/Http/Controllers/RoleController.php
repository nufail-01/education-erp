<?php

namespace App\Http\Controllers;

use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Models\Institute;
use App\Services\ActivityLogger;
use App\Services\PrivilegeGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(private ActivityLogger $logger, private PrivilegeGuard $guard)
    {
    }

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Role::class);

        $actor = $request->user();

        $roles = Role::query()
            ->when(! $actor->isGlobalUser(), fn ($q) => $q->where('institute_id', $actor->institute_id))
            ->withCount('permissions')
            ->orderBy('institute_id')
            ->orderBy('name')
            ->paginate(10);

        $instituteCodes = Institute::pluck('code', 'id');

        return view('content.roles.index', compact('roles', 'instituteCodes'));
    }

    public function create(Request $request)
    {
        abort_if($request->user()->isGlobalUser(), 403);
        Gate::authorize('create', Role::class);

        $permissions = $this->guard->grantablePermissionNames($request->user());
        $selected = [];

        return view('content.roles.create', compact('permissions', 'selected'));
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $actor = $request->user();
        $data = $request->validated();
        $names = $data['permissions'] ?? [];

       
        abort_unless($this->guard->canGrantPermissions($actor, $names), 403);

        $role = DB::transaction(function () use ($actor, $data, $names) {
            $role = Role::create([
                'name' => $data['name'],
                'guard_name' => 'web',
                'institute_id' => $actor->institute_id,
            ]);
            $role->syncPermissions($names);

            $this->logger->log('role.created', "Role {$role->name} created", $role, $actor->institute_id);

            return $role;
        });

        return redirect()->route('roles.index')->with('success', "Role {$role->name} created.");
    }

    public function edit(Request $request, Role $role)
    {
        $this->scoped($request, $role);
        Gate::authorize('update', $role);

        $permissions = $this->guard->grantablePermissionNames($request->user());
        $selected = $role->permissions->pluck('name')->all();

        return view('content.roles.edit', compact('role', 'permissions', 'selected'));
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $actor = $request->user();
        $data = $request->validated();
        $names = $data['permissions'] ?? [];

        abort_unless($this->guard->canGrantPermissions($actor, $names), 403);

        DB::transaction(function () use ($actor, $role, $data, $names) {
           
            $grantable = $this->guard->grantablePermissionNames($actor);
            $keep = $role->permissions->pluck('name')->diff($grantable)->all();

            $role->update(['name' => $data['name']]);
            $role->syncPermissions(array_merge($keep, $names));

            $this->logger->log('role.updated', "Role {$role->name} updated", $role, $role->institute_id);
        });

        return redirect()->route('roles.index')->with('success', 'Role updated.');
    }

    public function destroy(Request $request, Role $role): RedirectResponse
    {
        $this->scoped($request, $role);
        Gate::authorize('delete', $role);

        $inUse = DB::table(config('permission.table_names.model_has_roles'))
            ->where('role_id', $role->id)
            ->exists();

        if ($inUse) {
            return back()->with('danger', 'This role is assigned to users. Remove it from them first.');
        }

        DB::transaction(function () use ($role) {
            $name = $role->name;
            $instituteId = $role->institute_id;
            $role->delete();

            $this->logger->log('role.deleted', "Role {$name} deleted", null, $instituteId);
        });

        return redirect()->route('roles.index')->with('danger', 'Role deleted.');
    }

     
    private function scoped(Request $request, Role $role): void
    {
        $actor = $request->user();

        if (! $actor->isGlobalUser() && $role->institute_id !== $actor->institute_id) {
            abort(404);
        }
    }
}