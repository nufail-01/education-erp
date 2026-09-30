@extends('layouts/layoutMaster')

@section('title', 'Add Teacher')

@section('content')
  <div class="card">
    <div class="card-header"><h5 class="mb-0">Add Teacher</h5></div>
    <div class="card-body">
      <form method="POST" action="{{ route('teachers.store') }}">
        @csrf
        @include('content.teachers._form')
        <div class="mt-4 d-flex gap-2">
          <button type="submit" class="btn btn-primary">Save</button>
          <a href="{{ route('teachers.index') }}" class="btn btn-outline-secondary">Back</a>
        </div>
      </form>
    </div>
  </div>
@endsection