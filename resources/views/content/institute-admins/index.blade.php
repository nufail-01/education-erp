@extends('layouts/layoutMaster')

@section('title', 'Institute Admins')

@section('content')
  @include('content.institutes._flash')

  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Institute Admins</h5>
      <a href="{{ route('institute-admins.create') }}" class="btn btn-primary">Add Institute Admin</a>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr><th>Name</th><th>Email</th><th>Institute</th><th>Status</th><th></th></tr>
          </thead>
          <tbody>
            @forelse ($admins as $admin)
              <tr>
                <td>{{ $admin->name }}</td>
                <td>{{ $admin->email }}</td>
                <td>{{ $admin->institute?->name }} ({{ $admin->institute?->code }})</td>
                <td>
                  <span class="badge {{ $admin->isActive() ? 'bg-label-success' : 'bg-label-secondary' }}">
                    {{ ucfirst($admin->status) }}
                  </span>
                </td>
                <td class="text-end">
                  <a href="{{ route('institute-admins.edit', $admin) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                  <form method="POST" action="{{ route('institute-admins.toggle-status', $admin) }}" class="d-inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-sm {{ $admin->isActive() ? 'btn-danger' : 'btn-success' }}">
                      {{ $admin->isActive() ? 'Deactivate' : 'Activate' }}
                    </button>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td colspan="5" class="text-center text-muted">No institute admins found.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="mt-4">{{ $admins->links() }}</div>
    </div>
  </div>
@endsection