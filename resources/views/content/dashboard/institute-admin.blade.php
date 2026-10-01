@extends('layouts/layoutMaster')

@section('title', 'Dashboard')

@section('content')
  <h4 class="mb-4">{{ $institute->name }} Dashboard</h4>

  <div class="row g-4 mb-4">
    <div class="col-md-6">
      <div class="card"><div class="card-body">
        <p class="text-muted mb-1">Teachers</p><h3 class="mb-0">{{ $teacherCount }}</h3>
      </div></div>
    </div>
    <div class="col-md-6">
      <div class="card"><div class="card-body">
        <p class="text-muted mb-1">Users</p><h3 class="mb-0">{{ $userCount }}</h3>
      </div></div>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h5 class="mb-0">Recent Activity</h5></div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table">
          <thead><tr><th>When</th><th>User</th><th>Activity</th></tr></thead>
          <tbody>
            @forelse ($recentActivity as $log)
              <tr>
                <td>{{ $log->created_at->format('d M Y H:i') }}</td>
                <td>{{ $log->user?->name ?? '-' }}</td>
                <td>{{ $log->description }}</td>
              </tr>
            @empty
              <tr><td colspan="3" class="text-center text-muted">No activity yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
@endsection