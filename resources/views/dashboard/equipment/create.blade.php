@extends('layouts.app')
@section('content')

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <x-breadcrumb 
        title="Create New Equipment" 
        subtitle="Add new equipment to your fleet" 
        icon="fa-truck">
        <li class="breadcrumb-item"><a href="{{ route('equipment.index') }}">Equipment</a></li>
        <li class="breadcrumb-item active" aria-current="page">Create</li>
    </x-breadcrumb>
    @include('partials.message')

    <!-- Form Section -->
    <div class="card shadow-sm">
        <div class="card-body p-4" style="background-color:#F5F5F6;">
            <form action="{{ route('equipment.store') }}" method="POST">
                @csrf

                <div class="row g-4">
                    <!-- Basic Information -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">Equipment Name</label>
                            <input type="text" class="form-control form-control-lg @error('name') is-invalid @enderror"
                                name="name" value="{{ old('name') }}" placeholder="Enter equipment name" required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">Type</label>
                            <select class="form-select custom-select @error('type') is-invalid @enderror"
                                name="type" required>
                                <option value="">Select Type</option>
                                <option value="trailer">Trailer</option>
                                <option value="truck">Truck</option>
                                <option value="container">Container</option>
                                <option value="chassis">Chassis</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">Sub Type</label>
                            <input type="text" class="form-control form-control-lg @error('sub_type') is-invalid @enderror"
                                name="sub_type" value="{{ old('sub_type') }}" placeholder="Enter sub type">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">Status</label>
                            <select class="form-select custom-select @error('status') is-invalid @enderror"
                                name="status">
                                <option value="">Select Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="in_use">In Use</option>
                                <option value="available">Available</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">Description</label>
                            <textarea class="form-control form-control-lg @error('desc') is-invalid @enderror"
                                name="desc" rows="3" placeholder="Enter description">{{ old('desc') }}</textarea>
                        </div>
                    </div>
                    <input type="hidden" name="source" value="{{ request()->get('source') }}">
                    <input type="hidden" name="manifest_id" value="{{ request()->get('manifest_id') }}">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">Last Seen</label>
                            <input type="datetime-local" class="form-control form-control-lg @error('last_seen') is-invalid @enderror"
                                name="last_seen" value="{{ old('last_seen') }}">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">Last Location</label>
                            <input type="text" class="form-control form-control-lg @error('last_location') is-invalid @enderror"
                                name="last_location" value="{{ old('last_location') }}" placeholder="Enter last known location">
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('equipment.index') }}" class="btn btn-light btn-lg px-4">
                        <i class="fa fa-times me-2"></i> <span>Cancel</span>
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg px-4">
                        <i class="fa fa-save me-2"></i> <span>Create Equipment</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection