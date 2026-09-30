@extends('layouts/layoutMaster')

@section('title', 'Institutes')

@section('content')
  @include('content.institutes._flash')

  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Institutes</h5>
      @can('create', App\Models\Institute::class)
        <a href="{{ route('institutes.create') }}" class="btn btn-primary">Add Institute</a>
      @endcan
    </div>

    <div class="card-body">
      <form method="GET" action="{{ route('institutes.index') }}" class="row g-3 mb-4">
        <div class="col-md-6">
          <input type="text" name="search" class="form-control" placeholder="Search name, code or email"
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
          <a href="{{ route('institutes.index') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
      </form>

      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>Name</th><th>Code</th><th>Email</th><th>Users</th><th>Teachers</th><th>Status</th><th></th>
            </tr>
          </thead>
          <tbody>
            @forelse ($institutes as $institute)
              <tr>
                <td>{{ $institute->name }}</td>
                <td>{{ $institute->code }}</td>
                <td>{{ $institute->email }}</td>
                <td>{{ $institute->users_count }}</td>
                <td>{{ $institute->teachers_count }}</td>
                <td>
                  <span class="badge {{ $institute->isActive() ? 'bg-label-success' : 'bg-label-secondary' }}">
                    {{ ucfirst($institute->status) }}
                  </span>
                </td>
                <td class="text-end">
                  <a href="{{ route('institutes.show', $institute) }}" class="btn btn-sm btn-outline-primary">View</a>
                  @can('update', $institute)
                    <a href="{{ route('institutes.edit', $institute) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                  @endcan
                </td>
              </tr>
            @empty
              <tr><td colspan="7" class="text-center text-muted">No institutes found.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="mt-4">{{ $institutes->links() }}</div>
    </div>
  </div>
@endsection