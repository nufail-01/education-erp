<?php

namespace App\Http\Controllers;

use App\Http\Requests\Institute\StoreInstituteRequest;
use App\Http\Requests\Institute\UpdateInstituteRequest;
use App\Models\Institute;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class InstituteController extends Controller
{
    public function __construct(private ActivityLogger $logger)
    {
    }

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Institute::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in([Institute::STATUS_ACTIVE, Institute::STATUS_INACTIVE])],
        ]);

        $institutes = Institute::query()
            ->withCount(['users', 'teachers'])
            ->search($filters['search'] ?? null)
            ->status($filters['status'] ?? null)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('content.institutes.index', compact('institutes', 'filters'));
    }

    public function create()
    {
        Gate::authorize('create', Institute::class);

        return view('content.institutes.create');
    }

    public function store(StoreInstituteRequest $request): RedirectResponse
    {
        $data = $request->validated();
        unset($data['logo']);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('institutes', 'public');
        }

        $institute = DB::transaction(function () use ($data) {
            $institute = Institute::create($data + ['status' => Institute::STATUS_ACTIVE]);

            $this->logger->log('institute.created', "Institute {$institute->code} created", $institute, $institute->id);

            return $institute;
        });

        return redirect()->route('institutes.show', $institute)->with('success', 'Institute created.');
    }

    public function show(Institute $institute)
    {
        Gate::authorize('view', $institute);

        $institute->loadCount(['users', 'teachers']);

        return view('content.institutes.show', compact('institute'));
    }

    public function edit(Institute $institute)
    {
        Gate::authorize('update', $institute);

        return view('content.institutes.edit', compact('institute'));
    }

    public function update(UpdateInstituteRequest $request, Institute $institute): RedirectResponse
    {
        $data = $request->validated();
        unset($data['logo']);

        $oldLogo = $institute->logo;

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('institutes', 'public');
        }

        DB::transaction(function () use ($institute, $data) {
            $institute->update($data);

            $this->logger->log('institute.updated', "Institute {$institute->code} updated", $institute, $institute->id);
        });

        if ($request->hasFile('logo') && $oldLogo) {
            Storage::disk('public')->delete($oldLogo);
        }

        return redirect()->route('institutes.show', $institute)->with('success', 'Institute updated.');
    }

    public function toggleStatus(Institute $institute): RedirectResponse
    {
        Gate::authorize('toggleStatus', $institute);

        $newStatus = $institute->isActive() ? Institute::STATUS_INACTIVE : Institute::STATUS_ACTIVE;

        DB::transaction(function () use ($institute, $newStatus) {
            $institute->update(['status' => $newStatus]);

            $this->logger->log(
                'institute.status-changed',
                "Institute {$institute->code} set to {$newStatus}",
                $institute,
                $institute->id
            );
        });

               $flashType = $newStatus === Institute::STATUS_INACTIVE ? 'danger' : 'success';

        return back()->with($flashType, "Institute is now {$newStatus}.");
    }
}