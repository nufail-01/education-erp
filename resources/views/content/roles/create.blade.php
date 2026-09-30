@extends('layouts/layoutMaster')

@section('title', 'Add Role')

@section('content')
  <div class="card">
    <div class="card-header"><h5 class="mb-0">Add Role</h5></div>
    <div class="card-body">
      <form method="POST" action="{{ route('roles.store') }}">
        @csrf
        @include('content.roles._form')
        <div class="mt-4 d-flex gap-2">
          <button type="submit" class="btn btn-primary">Save</button>
          <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">Back</a>
        </div>
      </form>
    </div>
  </div>
@endsection