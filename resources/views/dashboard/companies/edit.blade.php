@extends('layouts.app')
@section('content')

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <x-breadcrumb 
        title="Update Company" 
        subtitle="Edit company information" 
        icon="fa-building">
        <li class="breadcrumb-item"><a href="{{ route('companies.index') }}">Companies</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit {{ $company->name ?? 'Company' }}</li>
    </x-breadcrumb>
    @include('partials.message')

    <!-- Form Section -->
    <div class="card shadow-sm">
        <div class="card-body p-4" style="background-color:#F5F5F6;">
            <form id="editCompanyForm" action="{{ route('companies.update', $company->id) }}" method="POST">
                @csrf
                @method('PUT')
                <!-- Basic Information -->
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">Company Name <span class="text-danger">*</span></label>
                            <input type="text"
                                class="form-control form-control-lg @error('name') is-invalid @enderror" 
                                id="name"
                                name="name" 
                                value="{{ old('name', $company->name) }}" 
                                placeholder="Enter company name" 
                                required
                                maxlength="255">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">Phone Number</label>
                            <input type="tel" 
                                class="form-control form-control-lg @error('phone') is-invalid @enderror"
                                id="phone" 
                                name="phone" 
                                value="{{ old('phone', $company->phone) }}" 
                                placeholder="Enter phone number"
                                maxlength="20">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">Address</label>
                            <textarea class="form-control form-control-lg @error('address') is-invalid @enderror"
                                id="address" 
                                name="address" 
                                rows="3"
                                placeholder="Enter company address"
                                maxlength="500">{{ old('address', $company->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1"
                                       {{ old('is_active', $company->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="is_active">
                                    Active Status
                                </label>
                                <div class="form-text">Toggle to activate/deactivate the company</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Company Statistics -->
                <div class="row g-4 mt-3">
                    <div class="col-12">
                        <div class="alert alert-info">
                            <h6 class="mb-2"><i class="fa fa-info-circle me-2"></i>Company Statistics</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <small class="text-muted">Total Users:</small>
                                    <div class="fw-bold">{{ $company->users->count() }}</div>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">Active Users:</small>
                                    <div class="fw-bold">{{ $company->activeUsers->count() }}</div>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">Created:</small>
                                    <div class="fw-bold">{{ $company->created_at->format('M d, Y') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('companies.index') }}" class="btn btn-light btn-lg px-4">
                        <i class="fa fa-times me-2"></i>Cancel
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg px-4">
                        <i class="fa fa-save me-2"></i> <span>Update Company</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('editCompanyForm');
    const nameInput = document.getElementById('name');
    const phoneInput = document.getElementById('phone');
    
    // Real-time validation
    nameInput.addEventListener('input', function() {
        const value = this.value.trim();
        if (value.length === 0) {
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-invalid');
        }
    });

    // Phone number validation (basic)
    phoneInput.addEventListener('input', function() {
        const value = this.value.trim();
        const phoneRegex = /^[\+]?[0-9\s\-\(\)]*$/;
        
        if (value.length > 0 && !phoneRegex.test(value)) {
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-invalid');
        }
    });

    // Form submission validation
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Validate company name
        if (nameInput.value.trim().length === 0) {
            nameInput.classList.add('is-invalid');
            isValid = false;
        }

        // Validate phone if provided
        if (phoneInput.value.trim().length > 0) {
            const phoneRegex = /^[\+]?[0-9\s\-\(\)]*$/;
            if (!phoneRegex.test(phoneInput.value.trim())) {
                phoneInput.classList.add('is-invalid');
                isValid = false;
            }
        }

        if (!isValid) {
            e.preventDefault();
            alert('Please correct the errors in the form before submitting.');
        }
    });
});
</script>

@endsection