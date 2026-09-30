@php
  $role = $role ?? null;
@endphp

<div class="row g-4">
  <div class="col-md-6">
    <label class="form-label" for="name">Role Name *</label>
    <input type="text" id="name" name="name" value="{{ old('name', $role?->name) }}" required
      class="form-control @error('name') is-invalid @enderror">
    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

  <div class="col-12">
    <label class="form-label d-block">Permissions</label>
    @error('permissions.*') <div class="text-danger mb-2">{{ $message }}</div> @enderror
    <div class="row">
      @foreach ($permissions as $permission)
        <div class="col-md-4 mb-2">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission }}"
              id="perm-{{ $loop->index }}"
              @checked(in_array($permission, old('permissions', $selected), true))>
            <label class="form-check-label" for="perm-{{ $loop->index }}">{{ $permission }}</label>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</div>