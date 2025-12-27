@extends('layouts.app')
@section('content')

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <x-breadcrumb 
        title="Edit Equipment" 
        subtitle="Update equipment details" 
        icon="fa-truck">
        <li class="breadcrumb-item"><a href="{{ route('equipment.index') }}">Equipment</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit {{ $equipment->name ?? 'Equipment' }}</li>
    </x-breadcrumb>
    @include('partials.message')

    <!-- Form Section -->
    <div class="card shadow-sm">
        <div class="card-body p-4" style="background-color:#F5F5F6;">
            <form action="{{ route('equipment.update', $equipment->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">Equipment Name</label>
                            <input type="text" class="form-control form-control-lg @error('name') is-invalid @enderror"
                                name="name" value="{{ old('name', $equipment->name) }}" required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">Type</label>
                            <select class="form-select custom-select @error('type') is-invalid @enderror"
                                name="type" required>
                                <option value="">Select Type</option>
                                <option value="trailer" {{ $equipment->type == 'trailer' ? 'selected' : '' }}>Trailer</option>
                                <option value="truck" {{ $equipment->type == 'truck' ? 'selected' : '' }}>Truck</option>
                                <option value="container" {{ $equipment->type == 'container' ? 'selected' : '' }}>Container</option>
                                <option value="chassis" {{ $equipment->type == 'chassis' ? 'selected' : '' }}>Chassis</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">Sub Type</label>
                            <input type="text" class="form-control form-control-lg @error('sub_type') is-invalid @enderror"
                                name="sub_type" value="{{ old('sub_type', $equipment->sub_type) }}">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">Status</label>
                            <select class="form-select custom-select @error('status') is-invalid @enderror"
                                name="status">
                                <option value="">Select Status</option>
                                <option value="active" {{ $equipment->status == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ $equipment->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="in_use" {{ $equipment->status == 'in_use' ? 'selected' : '' }}>In Use</option>
                                <option value="available" {{ $equipment->status == 'available' ? 'selected' : '' }}>Available</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">Description</label>
                            <textarea class="form-control form-control-lg @error('desc') is-invalid @enderror"
                                name="desc" rows="3">{{ old('desc', $equipment->desc) }}</textarea>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">Last Seen</label>
                            <input type="datetime-local" class="form-control form-control-lg @error('last_seen') is-invalid @enderror"
                                name="last_seen" value="{{ old('last_seen', $equipment->last_seen) }}">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">Last Location</label>
                            <input type="text" class="form-control form-control-lg @error('last_location') is-invalid @enderror"
                                name="last_location" value="{{ old('last_location', $equipment->last_location) }}">
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('equipment.index') }}" class="btn btn-light btn-lg px-4">
                        <i class="fa fa-times me-2"></i> <span>Cancel</span>
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg px-4">
                        <i class="fa fa-save me-2"></i> <span>Update Equipment</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
