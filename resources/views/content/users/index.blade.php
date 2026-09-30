@extends('layouts/layoutMaster')

@section('title', 'Users')

@section('content')
  @include('content.institutes._flash')

  <div class="card">
    <div class="card-header"><h5 class="mb-0">Users</h5></div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>Name</th><th>Email</th><th>Institute</th>
              @if ($canAssign) <th>Roles</th> @endif
              <th>Status</th><th></th>
            </tr>
          </thead>
          <tbody>
            @forelse ($users as $user)
              <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->institute?->code ?? 'Global' }}</td>
                @if ($canAssign)
                  <td>{{ $user->roles->pluck('name')->join(', ') ?: '-' }}</td>
                @endif
                <td>
                  <span class="badge {{ $user->isActive() ? 'bg-label-success' : 'bg-label-secondary' }}">
                    {{ ucfirst($user->status) }}
                  </span>
                </td>
                <td class="text-end">
                  @if ($canAssign)
                    <a href="{{ route('users.roles.edit', $user) }}" class="btn btn-sm btn-outline-primary">Roles</a>
                  @endif
                </td>
              </tr>
            @empty
              <tr><td colspan="6" class="text-center text-muted">No users found.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="mt-4">{{ $users->links() }}</div>
    </div>
  </div>
@endsection