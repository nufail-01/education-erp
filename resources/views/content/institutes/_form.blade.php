@php $institute = $institute ?? null; @endphp

<div class="row g-4">
  <div class="col-md-6">
    <label class="form-label" for="name">Name *</label>
    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
      value="{{ old('name', $institute?->name) }}" required>
    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>
  <div class="col-md-6">
    <label class="form-label" for="code">Code * (unique)</label>
    <input type="text" id="code" name="code" class="form-control @error('code') is-invalid @enderror"
      value="{{ old('code', $institute?->code) }}" required>
    @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>
  <div class="col-md-6">
    <label class="form-label" for="email">Email</label>
    <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
      value="{{ old('email', $institute?->email) }}">
    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>
  <div class="col-md-6">
    <label class="form-label" for="phone">Phone</label>
    <input type="text" id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror"
      value="{{ old('phone', $institute?->phone) }}">
    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>
  <div class="col-12">
    <label class="form-label" for="address">Address</label>
    <textarea id="address" name="address" rows="3" class="form-control @error('address') is-invalid @enderror">{{ old('address', $institute?->address) }}</textarea>
    @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>
  <div class="col-md-6">
    <label class="form-label" for="logo">Logo (jpg, png, webp, max 2MB)</label>
    <input type="file" id="logo" name="logo" accept="image/*"
      class="form-control @error('logo') is-invalid @enderror">
    @error('logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
    @if ($institute?->logo)
      <img src="{{ asset('storage/' . $institute->logo) }}" alt="logo" class="mt-2" style="height: 48px">
    @endif
  </div>
</div>