@php
  $configData = Helper::appClasses();
  $customizerHidden = 'customizer-hide';
  $pageConfigs = ['myLayout' => 'blank'];
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Access Denied')

@section('page-style')
  @vite(['resources/assets/vendor/scss/pages/page-misc.scss'])
@endsection

@section('content')
  <div class="container-xxl container-p-y">
    <div class="misc-wrapper text-center">
      <h1 class="mb-2 mx-2" style="font-size: 6rem; line-height: 6rem">403</h1>
      <h4 class="mb-2">Access Denied</h4>
      <p class="mb-6 mx-2">You do not have permission to view this page.</p>
      @auth
        <a href="{{ url('/') }}" class="btn btn-primary">Back to Home</a>
      @else
        <a href="{{ route('login') }}" class="btn btn-primary">Go to Login</a>
      @endauth
    </div>
  </div>
@endsection