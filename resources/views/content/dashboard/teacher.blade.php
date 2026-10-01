@extends('layouts/layoutMaster')

@section('title', 'Dashboard')

@section('content')
  <h4 class="mb-4">My Dashboard</h4>

  <div class="row g-4">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header"><h5 class="mb-0">Profile</h5></div>
        <div class="card-body">
          <dl class="row mb-0">
            <dt class="col-sm-5">Name</dt><dd class="col-sm-7">{{ $user->name }}</dd>
            <dt class="col-sm-5">Email</dt><dd class="col-sm-7">{{ $user->email }}</dd>
            <dt class="col-sm-5">Mobile</dt><dd class="col-sm-7">{{ $user->mobile_no ?? '-' }}</dd>
            <dt class="col-sm-5">Institute</dt><dd class="col-sm-7">{{ $user->institute?->name ?? '-' }}</dd>
            <dt class="col-sm-5">Employee Code</dt><dd class="col-sm-7">{{ $user->teacher?->employee_code ?? '-' }}</dd>
            <dt class="col-sm-5">Qualification</dt><dd class="col-sm-7">{{ $user->teacher?->qualification ?? '-' }}</dd>
            <dt class="col-sm-5">Joining Date</dt><dd class="col-sm-7">{{ $user->teacher?->joining_date?->format('d M Y') ?? '-' }}</dd>
          </dl>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card">
        <div class="card-header"><h5 class="mb-0">Permitted Actions</h5></div>
        <div class="card-body">
          @forelse ($permissions as $permission)
            <span class="badge bg-label-primary me-1 mb-1">{{ $permission }}</span>
          @empty
            <p class="text-muted mb-0">No permissions assigned.</p>
          @endforelse
        </div>
      </div>
    </div>
  </div>
@endsection