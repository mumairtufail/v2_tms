{{-- This file has been moved to dashboard/profile/show.blade.php --}}
{{-- Please use the route('profile.show') instead --}}
@extends('layouts.app')
@section('content')
<div class="container-fluid py-4">
    <div class="alert alert-info">
        <h5>Profile Page Moved</h5>
        <p>The profile functionality has been moved to a dedicated profile module.</p>
        <a href="{{ route('profile.show') }}" class="btn btn-primary">Go to Profile</a>
    </div>
</div>
@endsection