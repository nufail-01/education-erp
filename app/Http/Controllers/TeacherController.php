<?php

namespace App\Http\Controllers;

use App\Http\Requests\Teacher\StoreTeacherRequest;
use App\Http\Requests\Teacher\UpdateTeacherRequest;
use App\Models\Teacher;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class TeacherController extends Controller
{
    public function __construct(private ActivityLogger $logger)
    {
    }

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Teacher::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in([Teacher::STATUS_ACTIVE, Teacher::STATUS_INACTIVE])],
        ]);

        $teachers = Teacher::query()
            ->with(['user', 'institute'])
            ->when($filters['search'] ?? null, function ($q, $term) {
                $q->where(function ($q) use ($term) {
                    $q->where('employee_code', 'like', "%{$term}%")
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$term}%")
                            ->orWhere('email', 'like', "%{$term}%"));
                });
            })
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('content.teachers.index', compact('teachers', 'filters'));
    }

    public function create()
    {
        Gate::authorize('create', Teacher::class);

        return view('content.teachers.create');
    }

    public function store(StoreTeacherRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $instituteId = $request->user()->institute_id;

        $teacher = DB::transaction(function () use ($data, $instituteId) {
            $user = new User(collect($data)->only(['name', 'email', 'mobile_no', 'password'])->all());
            $user->institute_id = $instituteId;
            $user->status = User::STATUS_ACTIVE;
            $user->save();

            $teacher = new Teacher(collect($data)->only(['employee_code', 'qualification', 'joining_date'])->all());
            $teacher->institute_id = $instituteId;
            $teacher->user_id = $user->id;
            $teacher->status = Teacher::STATUS_ACTIVE;
            $teacher->save();

           
            $registrar = app(PermissionRegistrar::class);
            $previous = $registrar->getPermissionsTeamId();
            $registrar->setPermissionsTeamId($instituteId);

            $role = Role::firstOrCreate(
                ['name' => 'Teacher', 'guard_name' => 'web', 'institute_id' => $instituteId],
                ['is_protected' => false]
            );
            if ($role->wasRecentlyCreated) {
                $role->syncPermissions(['teachers.view']);
            }
            $user->assignRole($role);

            $registrar->setPermissionsTeamId($previous);

            $this->logger->log('teacher.created', "Teacher {$teacher->employee_code} created", $teacher, $instituteId);

            return $teacher;
        });

        return redirect()->route('teachers.index')->with('success', "Teacher {$teacher->employee_code} created.");
    }

    public function edit(Teacher $teacher)
    {
        Gate::authorize('update', $teacher);

        $teacher->load('user');

        return view('content.teachers.edit', compact('teacher'));
    }

    public function update(UpdateTeacherRequest $request, Teacher $teacher): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $teacher) {
            $userData = collect($data)->only(['name', 'email', 'mobile_no', 'password'])->filter(
                fn ($v, $k) => $k !== 'password' || ! empty($v)
            )->all();

            $teacher->user->fill($userData)->save();
            $teacher->fill(collect($data)->only(['employee_code', 'qualification', 'joining_date'])->all())->save();

            $this->logger->log('teacher.updated', "Teacher {$teacher->employee_code} updated", $teacher, $teacher->institute_id);
        });

        return redirect()->route('teachers.index')->with('success', 'Teacher updated.');
    }

    public function toggleStatus(Teacher $teacher): RedirectResponse
    {
        Gate::authorize('update', $teacher);

        $new = $teacher->isActive() ? Teacher::STATUS_INACTIVE : Teacher::STATUS_ACTIVE;

        DB::transaction(function () use ($teacher, $new) {
            $teacher->status = $new;
            $teacher->save();

       
            $teacher->user->status = $new;
            $teacher->user->save();

            $this->logger->log('teacher.status-changed', "Teacher {$teacher->employee_code} set to {$new}", $teacher, $teacher->institute_id);
        });

        return back()->with($new === Teacher::STATUS_INACTIVE ? 'danger' : 'success', "Teacher is now {$new}.");
    }

    public function destroy(Teacher $teacher): RedirectResponse
    {
        Gate::authorize('delete', $teacher);

        DB::transaction(function () use ($teacher) {
            $user = $teacher->user;
            $code = $teacher->employee_code;
            $instituteId = $teacher->institute_id;

            $registrar = app(PermissionRegistrar::class);
            $previous = $registrar->getPermissionsTeamId();
            $registrar->setPermissionsTeamId($instituteId);
            $user->syncRoles([]);
            $registrar->setPermissionsTeamId($previous);

            $teacher->delete();
            $user->delete();

            $this->logger->log('teacher.deleted', "Teacher {$code} deleted", null, $instituteId);
        });

        return redirect()->route('teachers.index')->with('danger', 'Teacher deleted.');
    }
}