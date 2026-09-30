@extends('layouts/layoutMaster')

@section('title', 'Edit Institute')

@section('content')
  <div class="card">
    <div class="card-header"><h5 class="mb-0">Edit Institute</h5></div>
    <div class="card-body">
      <form method="POST" action="{{ route('institutes.update', $institute) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('content.institutes._form')
        <div class="mt-4 d-flex gap-2">
          <button type="submit" class="btn btn-primary">Update</button>
          <a href="{{ route('institutes.show', $institute) }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
@endsection