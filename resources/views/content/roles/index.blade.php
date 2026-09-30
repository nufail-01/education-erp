@extends('layouts/layoutMaster')

@section('title', 'Roles')

@section('content')
  @include('content.institutes._flash')

  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Roles</h5>
      @if (! auth()->user()->isGlobalUser())
        @can('create', Spatie\Permission\Models\Role::class)
          <a href="{{ route('roles.create') }}" class="btn btn-primary">Add Role</a>
        @endcan
      @endif
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr><th>Name</th><th>Scope</th><th>Permissions</th><th>Type</th><th></th></tr>
          </thead>
          <tbody>
            @forelse ($roles as $role)
              <tr>
                <td>{{ $role->name }}</td>
                <td>{{ $role->institute_id ? ($instituteCodes[$role->institute_id] ?? '-') : 'Global' }}</td>
                <td>{{ $role->permissions_count }}</td>
                <td>
                  <span class="badge {{ $role->is_protected ? 'bg-label-warning' : 'bg-label-primary' }}">
                    {{ $role->is_protected ? 'Protected' : 'Custom' }}
                  </span>
                </td>
                <td class="text-end text-nowrap">
                  @can('update', $role)
                    <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                  @endcan
                  @can('delete', $role)
                    <form method="POST" action="{{ route('roles.destroy', $role) }}" class="d-inline"
                      onsubmit="return confirm('Delete this role?')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                  @endcan
                </td>
              </tr>
            @empty
              <tr><td colspan="5" class="text-center text-muted">No roles found.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="mt-4">{{ $roles->links() }}</div>
    </div>
  </div>
@endsection