@extends('layouts/layoutMaster')

@section('title', 'Dashboard')

@section('content')
  <h4 class="mb-4">Super Admin Dashboard</h4>

  <div class="row g-4 mb-4">
    <div class="col-md-4">
      <div class="card"><div class="card-body">
        <p class="text-muted mb-1">Total Institutes</p><h3 class="mb-0">{{ $totalInstitutes }}</h3>
      </div></div>
    </div>
    <div class="col-md-4">
      <div class="card"><div class="card-body">
        <p class="text-muted mb-1">Active Institutes</p><h3 class="mb-0">{{ $activeInstitutes }}</h3>
      </div></div>
    </div>
    <div class="col-md-4">
      <div class="card"><div class="card-body">
        <p class="text-muted mb-1">Total Users</p><h3 class="mb-0">{{ $totalUsers }}</h3>
      </div></div>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h5 class="mb-0">Recent Institutes</h5></div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table">
          <thead><tr><th>Name</th><th>Code</th><th>Status</th><th>Created</th></tr></thead>
          <tbody>
            @forelse ($recentInstitutes as $institute)
              <tr>
                <td><a href="{{ route('institutes.show', $institute) }}">{{ $institute->name }}</a></td>
                <td>{{ $institute->code }}</td>
                <td>{{ ucfirst($institute->status) }}</td>
                <td>{{ $institute->created_at->format('d M Y') }}</td>
              </tr>
            @empty
              <tr><td colspan="4" class="text-center text-muted">No institutes yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
@endsection