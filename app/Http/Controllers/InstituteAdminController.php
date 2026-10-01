<?php

namespace App\Http\Controllers;

use App\Http\Requests\InstituteAdmin\StoreInstituteAdminRequest;
use App\Http\Requests\InstituteAdmin\UpdateInstituteAdminRequest;
use App\Models\Institute;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class InstituteAdminController extends Controller
{
    public function __construct(private ActivityLogger $logger)
    {
    }

    public function index()
    {
        Gate::authorize('institute-admins.manage');

        $admins = User::query()
            ->with('institute')
            ->whereNotNull('institute_id')
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('model_has_roles as mhr')
                    ->join('roles', 'roles.id', '=', 'mhr.role_id')
                    ->whereColumn('mhr.model_id', 'users.id')
                    ->where('mhr.model_type', (new User)->getMorphClass())
                    ->where('roles.name', User::ROLE_INSTITUTE_ADMIN)
                    ->whereNull('roles.institute_id');
            })
            ->latest()
            ->paginate(10);

        return view('content.institute-admins.index', compact('admins'));
    }

    public function create()
    {
        Gate::authorize('institute-admins.manage');

        $institutes = Institute::active()->orderBy('name')->get(['id', 'name', 'code']);

        return view('content.institute-admins.create', compact('institutes'));
    }

    public function store(StoreInstituteAdminRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $institute = Institute::findOrFail($data['institute_id']);

        $admin = DB::transaction(function () use ($data, $institute) {
            $admin = new User(collect($data)->only(['name', 'email', 'mobile_no', 'password'])->all());
            $admin->institute_id = $institute->id;
            $admin->status = User::STATUS_ACTIVE;
            $admin->save();

            $role = Role::where('name', User::ROLE_INSTITUTE_ADMIN)->whereNull('institute_id')->firstOrFail();

            $registrar = app(PermissionRegistrar::class);
            $previous = $registrar->getPermissionsTeamId();
            $registrar->setPermissionsTeamId($institute->id);
            $admin->assignRole($role);
            $registrar->setPermissionsTeamId($previous);

            $this->logger->log('institute-admin.created', "Institute Admin {$admin->email} created", $admin, $institute->id);

            return $admin;
        });

        return redirect()->route('institute-admins.index')->with('success', "Institute Admin {$admin->email} created.");
    }

    public function edit(User $admin)
    {
        $this->authorizeTarget($admin);

        return view('content.institute-admins.edit', compact('admin'));
    }

    public function update(UpdateInstituteAdminRequest $request, User $admin): RedirectResponse
    {
        $this->authorizeTarget($admin);

        $data = $request->validated();
        if (empty($data['password'])) {
            unset($data['password']);
        }

        DB::transaction(function () use ($admin, $data) {
            $admin->fill($data)->save();
            $this->logger->log('institute-admin.updated', "Institute Admin {$admin->email} updated", $admin, $admin->institute_id);
        });

        return redirect()->route('institute-admins.index')->with('success', 'Institute Admin updated.');
    }

    public function toggleStatus(User $admin): RedirectResponse
    {
        $this->authorizeTarget($admin);

        $new = $admin->isActive() ? User::STATUS_INACTIVE : User::STATUS_ACTIVE;

        DB::transaction(function () use ($admin, $new) {
            $admin->status = $new;
            $admin->save();
            $this->logger->log('institute-admin.status-changed', "Institute Admin {$admin->email} set to {$new}", $admin, $admin->institute_id);
        });

        return back()->with($new === User::STATUS_INACTIVE ? 'danger' : 'success', "Admin is now {$new}.");
    }

    
    private function authorizeTarget(User $admin): void
    {
        Gate::authorize('institute-admins.manage');

        $registrar = app(PermissionRegistrar::class);
        $previous = $registrar->getPermissionsTeamId();
        $registrar->setPermissionsTeamId($admin->institute_id ?? 0);
        $isAdmin = $admin->institute_id !== null && $admin->hasRole(User::ROLE_INSTITUTE_ADMIN);
        $registrar->setPermissionsTeamId($previous);

        abort_unless($isAdmin, 404);
    }
}