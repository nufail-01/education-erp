@extends('layouts/layoutMaster')

@section('title', 'My Profile')

@section('content')
  @include('content.institutes._flash')

  <div class="row g-4">
    <div class="col-lg-6">
      <div class="card">
        <div class="card-header"><h5 class="mb-0">Profile</h5></div>
        <div class="card-body">
          <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PUT')
            <div class="mb-4">
              <label class="form-label" for="name">Name *</label>
              <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                class="form-control @error('name') is-invalid @enderror">
              @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-4">
              <label class="form-label" for="email">Email *</label>
              <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                class="form-control @error('email') is-invalid @enderror">
              @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-4">
              <label class="form-label" for="mobile_no">Mobile</label>
              <input type="text" id="mobile_no" name="mobile_no" value="{{ old('mobile_no', $user->mobile_no) }}"
                class="form-control @error('mobile_no') is-invalid @enderror">
              @error('mobile_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-4">
              <label class="form-label">Institute</label>
              <input type="text" class="form-control" value="{{ $user->institute?->name ?? 'Global' }}" disabled>
            </div>
            @if ($user->teacher)
              <div class="mb-4">
                <label class="form-label">Employee Code</label>
                <input type="text" class="form-control" value="{{ $user->teacher->employee_code }}" disabled>
              </div>
            @endif
            <button type="submit" class="btn btn-primary">Update Profile</button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card">
        <div class="card-header"><h5 class="mb-0">Change Password</h5></div>
        <div class="card-body">
          <form method="POST" action="{{ route('profile.password') }}">
            @csrf
            @method('PUT')
            <div class="mb-4">
              <label class="form-label" for="current_password">Current Password *</label>
              <input type="password" id="current_password" name="current_password" required
                class="form-control @error('current_password') is-invalid @enderror">
              @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-4">
              <label class="form-label" for="password">New Password * (min 8)</label>
              <input type="password" id="password" name="password" required
                class="form-control @error('password') is-invalid @enderror">
              @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-4">
              <label class="form-label" for="password_confirmation">Confirm New Password *</label>
              <input type="password" id="password_confirmation" name="password_confirmation" required class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Change Password</button>
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection