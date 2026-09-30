@extends('layouts/layoutMaster')

@section('title', 'Assign Roles')

@section('content')
  <div class="card">
    <div class="card-header">
      <h5 class="mb-0">Roles for {{ $user->name }} ({{ $user->email }})</h5>
    </div>
    <div class="card-body">
      <form method="POST" action="{{ route('users.roles.update', $user) }}">
        @csrf
        @method('PUT')

        @error('roles.*') <div class="text-danger mb-2">{{ $message }}</div> @enderror

        @forelse ($roles as $role)
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->id }}"
              id="role-{{ $role->id }}" @checked(in_array($role->id, old('roles', $assignedIds)))>
            <label class="form-check-label" for="role-{{ $role->id }}">{{ $role->name }}</label>
          </div>
        @empty
          <p class="text-muted">No custom roles yet. Create a role first.</p>
        @endforelse

        <div class="mt-4 d-flex gap-2">
          <button type="submit" class="btn btn-primary">Save</button>
          <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Back</a>
        </div>
      </form>
    </div>
  </div>
@endsection