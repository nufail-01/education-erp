@extends('layouts/layoutMaster')

@section('title', 'Add Institute Admin')

@section('content')
  <div class="card">
    <div class="card-header"><h5 class="mb-0">Add Institute Admin</h5></div>
    <div class="card-body">
      <form method="POST" action="{{ route('institute-admins.store') }}">
        @csrf
        <div class="row g-4">
          <div class="col-md-6">
            <label class="form-label" for="institute_id">Institute *</label>
            <select id="institute_id" name="institute_id" class="form-select @error('institute_id') is-invalid @enderror" required>
              <option value="">Select institute</option>
              @foreach ($institutes as $institute)
                <option value="{{ $institute->id }}" @selected(old('institute_id') == $institute->id)>
                  {{ $institute->name }} ({{ $institute->code }})
                </option>
              @endforeach
            </select>
            @error('institute_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="col-md-6">
            <label class="form-label" for="name">Name *</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required
              class="form-control @error('name') is-invalid @enderror">
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="col-md-6">
            <label class="form-label" for="email">Email *</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required
              class="form-control @error('email') is-invalid @enderror">
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="col-md-6">
            <label class="form-label" for="mobile_no">Mobile</label>
            <input type="text" id="mobile_no" name="mobile_no" value="{{ old('mobile_no') }}"
              class="form-control @error('mobile_no') is-invalid @enderror">
            @error('mobile_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="col-md-6">
            <label class="form-label" for="password">Password * (min 8)</label>
            <input type="password" id="password" name="password" required
              class="form-control @error('password') is-invalid @enderror">
            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="col-md-6">
            <label class="form-label" for="password_confirmation">Confirm Password *</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required class="form-control">
          </div>
        </div>
        <div class="mt-4 d-flex gap-2">
          <button type="submit" class="btn btn-primary">Save</button>
          <a href="{{ route('institute-admins.index') }}" class="btn btn-outline-secondary">Back</a>
        </div>
      </form>
    </div>
  </div>
@endsection