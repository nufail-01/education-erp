@extends('layouts/layoutMaster')

@section('title', 'Teachers')

@section('content')
  @include('content.institutes._flash')

  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Teachers</h5>
      @can('create', App\Models\Teacher::class)
        <a href="{{ route('teachers.create') }}" class="btn btn-primary">Add Teacher</a>
      @endcan
    </div>

    <div class="card-body">
      <form method="GET" action="{{ route('teachers.index') }}" class="row g-3 mb-4">
        <div class="col-md-6">
          <input type="text" name="search" class="form-control" placeholder="Search name, email or employee code"
            value="{{ $filters['search'] ?? '' }}">
        </div>
        <div class="col-md-3">
          <select name="status" class="form-select">
            <option value="">All statuses</option>
            <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
            <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
          </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
          <button class="btn btn-primary" type="submit">Filter</button>
          <a href="{{ route('teachers.index') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
      </form>

      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>Employee Code</th><th>Name</th><th>Email</th><th>Qualification</th>
              <th>Joining Date</th><th>Institute</th><th>Status</th><th></th>
            </tr>
          </thead>
          <tbody>
            @forelse ($teachers as $teacher)
              <tr>
                <td>{{ $teacher->employee_code }}</td>
                <td>{{ $teacher->user->name }}</td>
                <td>{{ $teacher->user->email }}</td>
                <td>{{ $teacher->qualification ?? '-' }}</td>
                <td>{{ $teacher->joining_date?->format('d M Y') ?? '-' }}</td>
                <td>{{ $teacher->institute->code }}</td>
                <td>
                  <span class="badge {{ $teacher->isActive() ? 'bg-label-success' : 'bg-label-secondary' }}">
                    {{ ucfirst($teacher->status) }}
                  </span>
                </td>
                <td class="text-end text-nowrap">
                  @can('update', $teacher)
                    <a href="{{ route('teachers.edit', $teacher) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                    <form method="POST" action="{{ route('teachers.toggle-status', $teacher) }}" class="d-inline">
                      @csrf
                      @method('PATCH')
                      <button type="submit" class="btn btn-sm {{ $teacher->isActive() ? 'btn-danger' : 'btn-success' }}">
                        {{ $teacher->isActive() ? 'Deactivate' : 'Activate' }}
                      </button>
                    </form>
                  @endcan
                  @can('delete', $teacher)
                    <form method="POST" action="{{ route('teachers.destroy', $teacher) }}" class="d-inline"
                      onsubmit="return confirm('Delete this teacher account permanently?')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                  @endcan
                </td>
              </tr>
            @empty
              <tr><td colspan="8" class="text-center text-muted">No teachers found.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="mt-4">{{ $teachers->links() }}</div>
    </div>
  </div>
@endsection