@extends('layouts/layoutMaster')

@section('title', $institute->name)

@section('content')
  @include('content.institutes._flash')

  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">{{ $institute->name }}</h5>
      <div class="d-flex gap-2">
        <a href="{{ route('institutes.index') }}" class="btn btn-outline-secondary">Back</a>
        @can('update', $institute)
          <a href="{{ route('institutes.edit', $institute) }}" class="btn btn-outline-secondary">Edit</a>
        @endcan
        @can('toggleStatus', $institute)
          <form method="POST" action="{{ route('institutes.toggle-status', $institute) }}">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn {{ $institute->isActive() ? 'btn-danger' : 'btn-success' }}">
              {{ $institute->isActive() ? 'Deactivate' : 'Activate' }}
            </button>
          </form>
        @endcan
      </div>
    </div>
    <div class="card-body">
      @if ($institute->logo)
        <img src="{{ asset('storage/' . $institute->logo) }}" alt="logo" class="mb-4" style="height: 64px">
      @endif
      <dl class="row mb-0">
        <dt class="col-sm-3">Code</dt><dd class="col-sm-9">{{ $institute->code }}</dd>
        <dt class="col-sm-3">Email</dt><dd class="col-sm-9">{{ $institute->email ?? '-' }}</dd>
        <dt class="col-sm-3">Phone</dt><dd class="col-sm-9">{{ $institute->phone ?? '-' }}</dd>
        <dt class="col-sm-3">Address</dt><dd class="col-sm-9">{{ $institute->address ?? '-' }}</dd>
        <dt class="col-sm-3">Status</dt><dd class="col-sm-9">{{ ucfirst($institute->status) }}</dd>
        <dt class="col-sm-3">Users</dt><dd class="col-sm-9">{{ $institute->users_count }}</dd>
        <dt class="col-sm-3">Teachers</dt><dd class="col-sm-9">{{ $institute->teachers_count }}</dd>
        <dt class="col-sm-3">Created</dt><dd class="col-sm-9">{{ $institute->created_at->format('d M Y') }}</dd>
      </dl>
    </div>
  </div>
@endsection