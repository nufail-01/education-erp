@extends('layouts/layoutMaster')

@section('title', 'Edit Role')

@section('content')
  <div class="card">
    <div class="card-header"><h5 class="mb-0">Edit Role</h5></div>
    <div class="card-body">
      <form method="POST" action="{{ route('roles.update', $role) }}">
        @csrf
        @method('PUT')
        @include('content.roles._form')
        <div class="mt-4 d-flex gap-2">
          <button type="submit" class="btn btn-primary">Update</button>
          <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">Back</a>
        </div>
      </form>
    </div>
  </div>
@endsection