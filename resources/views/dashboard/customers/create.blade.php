@extends('layouts.app')
@section('content')

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <x-breadcrumb 
        title="Create New Customer" 
        subtitle="Add a new customer to your organization" 
        icon="fa-building">
        <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Customers</a></li>
        <li class="breadcrumb-item active" aria-current="page">Create</li>
    </x-breadcrumb>
    @include('partials.message')

    <!-- Form Section -->
    <div class="card shadow-sm">
        <div class="card-body p-4" style="background-color:#F5F5F6;">
            <form action="{{ route('customers.store') }}" method="POST">
                @csrf

                <!-- Basic Information -->
                <div class="row g-4">
                    <!-- Name and Short Code -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">Customer Name</label>
                            <input type="text" class="form-control form-control-lg @error('name') is-invalid @enderror"
                                name="name" value="{{ old('name') }}" placeholder="Enter customer name" required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">Short Code</label>
                            <input type="text"
                                class="form-control form-control-lg @error('short_code') is-invalid @enderror"
                                name="short_code" value="{{ old('short_code') }}" placeholder="Enter short code">
                        </div>
                    </div>

                    <!-- Customer Type and Billing Option -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">Customer Type</label>
                            <select class="form-select custom-select @error('customer_type') is-invalid @enderror"
                                name="customer_type" required>
                                <option value="">Select Type</option>
                                <option value="shipper" {{ old('customer_type')=='shipper' ? 'selected' : '' }}>Shipper
                                </option>
                                <option value="broker" {{ old('customer_type')=='broker' ? 'selected' : '' }}>Broker
                                </option>
                                <option value="carrier" {{ old('customer_type')=='carrier' ? 'selected' : '' }}>Carrier
                                </option>
                                <option value="other" {{ old('customer_type')=='other' ? 'selected' : '' }}>Other
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">Default Billing Option</label>
                            <select
                                class="form-select custom-select @error('default_billing_option') is-invalid @enderror"
                                name="default_billing_option" required>
                                <option value="">Select Billing Option</option>
                                <option value="third_party" {{ old('default_billing_option')=='third_party' ? 'selected'
                                    : '' }}>Third Party</option>
                                <option value="consignee" {{ old('default_billing_option')=='consignee' ? 'selected'
                                    : '' }}>Consignee</option>
                                <option value="shipper" {{ old('default_billing_option')=='shipper' ? 'selected' : ''
                                    }}>Shipper</option>
                            </select>
                        </div>
                    </div>

                    <!-- Location Information -->
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">Address</label>
                            <textarea class="form-control form-control-lg @error('address') is-invalid @enderror"
                                name="address" rows="3" placeholder="Enter full address">{{ old('address') }}</textarea>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">City</label>
                            <input type="text" class="form-control form-control-lg @error('city') is-invalid @enderror"
                                name="city" value="{{ old('city') }}" placeholder="Enter city">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">State/Province</label>
                            <input type="text" class="form-control form-control-lg @error('state') is-invalid @enderror"
                                name="state" value="{{ old('state') }}" placeholder="Enter state">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">Postal Code/Zip</label>
                            <input type="text"
                                class="form-control form-control-lg @error('postal_code') is-invalid @enderror"
                                name="postal_code" value="{{ old('postal_code') }}" placeholder="Enter postal code">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">Country</label>
                            <input type="text"
                                class="form-control form-control-lg @error('country') is-invalid @enderror"
                                name="country" value="{{ old('country') }}" placeholder="Enter country">
                        </div>
                    </div>

                    <!-- Additional Settings -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">Location Sharing</label>
                            <select class="form-select custom-select" name="location_sharing" required>
                                <option value="Do not share" {{ old('location_sharing')=='Do not share' ? 'selected'
                                    : '' }}>Do not share</option>
                                <option value="approximate" {{ old('location_sharing')=='approximate' ? 'selected' : ''
                                    }}>Approximate</option>
                                <option value="exact live location" {{ old('location_sharing')=='exact live location'
                                    ? 'selected' : '' }}>Exact live location</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">Currency</label>
                            <input type="text"
                                class="form-control form-control-lg @error('currency') is-invalid @enderror"
                                name="currency" value="{{ old('currency') }}" placeholder="Enter currency">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">Customer Email</label>
                            <input type="email" class="form-control form-control-lg " name="customer_email"
                                placeholder="Enter Email" required>
                        </div>
                    </div>

                    <!-- Checkboxes -->
                    <div class="col-md-4">
                        <label class="form-label fw-bold mb-1">Additional Options</label>
                        <div class="form-group bg-white p-3 rounded">
                            <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input" name="is_active" value="1" {{
                                    old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label">Active Status</label>
                            </div>

                            <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input" name="portal" value="1" {{ old('portal')
                                    ? 'checked' : '' }}>
                                <label class="form-check-label">Portal Access</label>
                            </div>

                            <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input" name="network_customer" value="1" {{
                                    old('network_customer') ? 'checked' : '' }}>
                                <label class="form-check-label">Network Customer</label>
                            </div>

                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="quote_required" value="1" {{
                                    old('quote_required') ? 'checked' : '' }}>
                                <label class="form-check-label">Quote Required</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('customers.index') }}" class="btn btn-light btn-lg px-4">
                        <i class="fa fa-times me-2"></i> <span> Cancel</span>
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg px-4">
                        <i class="fa fa-save me-2"></i> <span> Create Customer</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>



@endsection