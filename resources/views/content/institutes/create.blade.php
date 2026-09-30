@extends('layouts/layoutMaster')

@section('title', 'Add Institute')

@section('content')
  <div class="card">
    <div class="card-header"><h5 class="mb-0">Add Institute</h5></div>
    <div class="card-body">
      <form method="POST" action="{{ route('institutes.store') }}" enctype="multipart/form-data">
        @csrf
        @include('content.institutes._form')
        <div class="mt-4 d-flex gap-2">
          <button type="submit" class="btn btn-primary">Save</button>
          <a href="{{ route('institutes.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
@endsection