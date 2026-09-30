@php
  $teacher = $teacher ?? null;
  $isEdit = $teacher !== null;
@endphp

<div class="row g-4">
  <div class="col-md-6">
    <label class="form-label" for="name">Name *</label>
    <input type="text" id="name" name="name" value="{{ old('name', $teacher?->user?->name) }}" required
      class="form-control @error('name') is-invalid @enderror">
    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>
  <div class="col-md-6">
    <label class="form-label" for="email">Email *</label>
    <input type="email" id="email" name="email" value="{{ old('email', $teacher?->user?->email) }}" required
      class="form-control @error('email') is-invalid @enderror">
    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>
  <div class="col-md-6">
    <label class="form-label" for="mobile_no">Mobile</label>
    <input type="text" id="mobile_no" name="mobile_no" value="{{ old('mobile_no', $teacher?->user?->mobile_no) }}"
      class="form-control @error('mobile_no') is-invalid @enderror">
    @error('mobile_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>
  <div class="col-md-6">
    <label class="form-label" for="employee_code">Employee Code *</label>
    <input type="text" id="employee_code" name="employee_code"
      value="{{ old('employee_code', $teacher?->employee_code) }}" required
      class="form-control @error('employee_code') is-invalid @enderror">
    @error('employee_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>
  <div class="col-md-6">
    <label class="form-label" for="qualification">Qualification</label>
    <input type="text" id="qualification" name="qualification"
      value="{{ old('qualification', $teacher?->qualification) }}"
      class="form-control @error('qualification') is-invalid @enderror">
    @error('qualification') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>
  <div class="col-md-6">
    <label class="form-label" for="joining_date">Joining Date</label>
    <input type="date" id="joining_date" name="joining_date"
      value="{{ old('joining_date', $teacher?->joining_date?->format('Y-m-d')) }}"
      class="form-control @error('joining_date') is-invalid @enderror">
    @error('joining_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>
  <div class="col-md-6">
    <label class="form-label" for="password">
      {{ $isEdit ? 'New Password (khali chhodo to nahi badlega)' : 'Password * (min 8)' }}
    </label>
    <input type="password" id="password" name="password" {{ $isEdit ? '' : 'required' }}
      class="form-control @error('password') is-invalid @enderror">
    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>
  <div class="col-md-6">
    <label class="form-label" for="password_confirmation">Confirm Password</label>
    <input type="password" id="password_confirmation" name="password_confirmation" {{ $isEdit ? '' : 'required' }}
      class="form-control">
  </div>
</div>