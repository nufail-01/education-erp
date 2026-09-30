@extends('layouts/layoutMaster')

@section('title', 'Edit Institute Admin')

@section('content')
  <div class="card">
    <div class="card-header"><h5 class="mb-0">Edit Institute Admin</h5></div>
    <div class="card-body">
      <form method="POST" action="{{ route('institute-admins.update', $admin) }}">
        @csrf
        @method('PUT')
        <div class="row g-4">
          <div class="col-md-6">
            <label class="form-label">Institute</label>
            <input type="text" class="form-control" value="{{ $admin->institute?->name }}" disabled>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="name">Name *</label>
            <input type="text" id="name" name="name" value="{{ old('name', $admin->name) }}" required
              class="form-control @error('name') is-invalid @enderror">
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="col-md-6">
            <label class="form-label" for="email">Email *</label>
            <input type="email" id="email" name="email" value="{{ old('email', $admin->email) }}" required
              class="form-control @error('email') is-invalid @enderror">
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="col-md-6">
            <label class="form-label" for="mobile_no">Mobile</label>
            <input type="text" id="mobile_no" name="mobile_no" value="{{ old('mobile_no', $admin->mobile_no) }}"
              class="form-control @error('mobile_no') is-invalid @enderror">
            @error('mobile_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="col-md-6">
            <label class="form-label" for="password">New Password (khali chhodo to nahi badlega)</label>
            <input type="password" id="password" name="password"
              class="form-control @error('password') is-invalid @enderror">
            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="col-md-6">
            <label class="form-label" for="password_confirmation">Confirm New Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control">
          </div>
        </div>
        <div class="mt-4 d-flex gap-2">
          <button type="submit" class="btn btn-primary">Update</button>
          <a href="{{ route('institute-admins.index') }}" class="btn btn-outline-secondary">Back</a>
        </div>
      </form>
    </div>
  </div>
@endsection