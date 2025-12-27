@extends('layouts.app')
@section('content')

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <x-breadcrumb 
        title="Create New Carrier" 
        subtitle="Add new carrier to your system" 
        icon="fa-truck-moving">
        <li class="breadcrumb-item"><a href="{{ route('carriers.index') }}">Carriers</a></li>
        <li class="breadcrumb-item active" aria-current="page">Create</li>
    </x-breadcrumb>

    <!-- Form Section -->
    <div class="card shadow-sm">
        <div class="card-body p-4" style="background-color:#F5F5F6;">
            <form action="{{ route('carriers.store') }}" method="POST">
                @csrf

                <div class="row g-4">
                    <!-- Basic Information -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">Carrier Name</label>
                            <input type="text" class="form-control form-control-lg @error('carrier_name') is-invalid @enderror"
                                name="carrier_name" value="{{ old('carrier_name') }}" placeholder="Enter carrier name" required>
                         
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">DOT ID</label>
                            <input type="text" class="form-control form-control-lg @error('dot_id') is-invalid @enderror"
                                name="dot_id" value="{{ old('dot_id') }}" placeholder="Enter DOT ID">
                          
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">Docket Number</label>
                            <input type="text" class="form-control form-control-lg @error('docket_number') is-invalid @enderror"
                                name="docket_number" value="{{ old('docket_number') }}" placeholder="Enter docket number">
                         
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">Address</label>
                            <input type="text" class="form-control form-control-lg @error('address_1') is-invalid @enderror"
                                name="address_1" value="{{ old('address_1') }}" placeholder="Enter address">
                            
                        </div>
                    </div>

                    <input type="hidden" name="source" value="{{ request()->get('source') }}">
                    <input type="hidden" name="manifest_id" value="{{ request()->get('manifest_id') }}">

                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">City</label>
                            <input type="text" class="form-control form-control-lg @error('city') is-invalid @enderror"
                                name="city" value="{{ old('city') }}" placeholder="Enter city">
                        
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">State</label>
                            <input type="text" class="form-control form-control-lg @error('state') is-invalid @enderror"
                                name="state" value="{{ old('state') }}" placeholder="Enter state">
                           
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">Country</label>
                            <select class="form-select custom-select @error('country') is-invalid @enderror"
                                name="country">
                                <option value="">Select Country</option>
                                <option value="US" {{ old('country') == 'US' ? 'selected' : '' }}>United States</option>
                                <option value="CA" {{ old('country') == 'CA' ? 'selected' : '' }}>Canada</option>
                                <option value="MX" {{ old('country') == 'MX' ? 'selected' : '' }}>Mexico</option>
                                <!-- Add more countries as needed -->
                            </select>
                         
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">Postal Code</label>
                            <input type="text" class="form-control form-control-lg @error('post_code') is-invalid @enderror"
                                name="post_code" value="{{ old('post_code') }}" placeholder="Enter postal code">
                        
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">Currency</label>
                            <select class="form-select custom-select @error('currency') is-invalid @enderror"
                                name="currency">
                                <option value="">Select Currency</option>
                                <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD</option>
                                <option value="CAD" {{ old('currency') == 'CAD' ? 'selected' : '' }}>CAD</option>
                                <option value="MXN" {{ old('currency') == 'MXN' ? 'selected' : '' }}>MXN</option>
                                <!-- Add more currencies as needed -->
                            </select>
                           
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1 d-block">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                    {{ old('is_active') ? 'checked' : '' }} style="width: 3em; height: 1.5em; margin-right: 1em;">
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                            @error('is_active')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('carriers.index') }}" class="btn btn-light btn-lg px-4">
                        <i class="fa fa-times me-2"></i> <span>Cancel</span>
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg px-4">
                        <i class="fa fa-save me-2"></i> <span>Create Carrier</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection