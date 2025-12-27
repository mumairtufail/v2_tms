@extends('layouts.app')
@section('content')

<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div class="avatar-lg bg-light rounded-circle me-3 d-flex align-items-center justify-content-center">
                    <i class="fa fa-user-edit text-primary" style="font-size: 1.5rem;"></i>
                </div>
                <div>
                    <h4 class="mb-1">Edit Profile</h4>
                    <p class="text-muted mb-0">Update your personal information and settings</p>
                </div>
            </div>
        </div>
    </div>

    @include('partials.message')

    <div class="row">
        <!-- Profile Form -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fa fa-user text-primary me-2"></i>Personal Information
                    </h5>
                </div>
                <div class="card-body p-4" style="background-color:#F5F5F6;">
                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-4">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label fw-bold mb-1">First Name</label>
                                    <input type="text" 
                                           class="form-control form-control-lg @error('f_name') is-invalid @enderror" 
                                           name="f_name" 
                                           value="{{ old('f_name', $user->f_name) }}" 
                                           placeholder="Enter first name" 
                                           required>
                                    @error('f_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label fw-bold mb-1">Last Name</label>
                                    <input type="text" 
                                           class="form-control form-control-lg @error('l_name') is-invalid @enderror" 
                                           name="l_name" 
                                           value="{{ old('l_name', $user->l_name) }}" 
                                           placeholder="Enter last name" 
                                           required>
                                    @error('l_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label fw-bold mb-1">Email Address</label>
                                    <input type="email" 
                                           class="form-control form-control-lg @error('email') is-invalid @enderror" 
                                           name="email" 
                                           value="{{ old('email', $user->email) }}" 
                                           placeholder="Enter email address" 
                                           required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label fw-bold mb-1">Phone Number</label>
                                    <input type="text" 
                                           class="form-control form-control-lg @error('phone') is-invalid @enderror" 
                                           name="phone" 
                                           value="{{ old('phone', $user->phone) }}" 
                                           placeholder="Enter phone number">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-group">
                                    <label class="form-label fw-bold mb-1">Address</label>
                                    <textarea class="form-control form-control-lg @error('address') is-invalid @enderror" 
                                              name="address" 
                                              rows="3" 
                                              placeholder="Enter your address">{{ old('address', $user->address) }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Password Change Section -->
                            <div class="col-12">
                                <hr class="my-4">
                                <h6 class="text-primary mb-3">
                                    <i class="fa fa-lock me-2"></i>Change Password (Optional)
                                </h6>
                                <p class="text-muted mb-3">Leave blank if you don't want to change your password</p>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label fw-bold mb-1">Current Password</label>
                                    <div class="input-group input-group-lg">
                                        <input type="password" 
                                               class="form-control @error('current_password') is-invalid @enderror" 
                                               name="current_password" 
                                               placeholder="Current password">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('current_password')">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    </div>
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label fw-bold mb-1">New Password</label>
                                    <div class="input-group input-group-lg">
                                        <input type="password" 
                                               class="form-control @error('password') is-invalid @enderror" 
                                               name="password" 
                                               placeholder="New password">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    </div>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label fw-bold mb-1">Confirm New Password</label>
                                    <div class="input-group input-group-lg">
                                        <input type="password" 
                                               class="form-control" 
                                               name="password_confirmation" 
                                               placeholder="Confirm new password">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmation')">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="{{ route('profile.show') }}" class="btn btn-light btn-lg px-4">
                                <i class="fa fa-times me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg px-4">
                                <i class="fa fa-save me-2"></i>Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Profile Picture & Company Info -->
        <div class="col-lg-4">
            <!-- Profile Picture -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fa fa-camera text-primary me-2"></i>Profile Picture
                    </h5>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        @if($user->profile_image)
                            <img src="{{ asset('storage/avatars/' . $user->profile_image) }}" 
                                 alt="Profile Picture" 
                                 class="rounded-circle"
                                 style="width: 120px; height: 120px; object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white mx-auto"
                                 style="width: 120px; height: 120px; font-size: 3rem; font-weight: bold;">
                                {{ strtoupper(substr($user->f_name, 0, 1)) }}{{ strtoupper(substr($user->l_name, 0, 1)) }}
                            </div>
                        @endif
                    </div>

                    <form action="{{ route('profile.avatar') }}" method="POST" enctype="multipart/form-data" id="avatarForm">
                        @csrf
                        <div class="mb-3">
                            <input type="file" 
                                   class="form-control @error('avatar') is-invalid @enderror" 
                                   name="avatar" 
                                   accept="image/*"
                                   onchange="previewImage(this)">
                            @error('avatar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-outline-primary btn-sm">
                            <i class="fa fa-upload me-2"></i>Upload New Picture
                        </button>
                    </form>
                    
                    <div class="mt-3">
                        <small class="text-muted">
                            Supported formats: JPG, PNG, GIF<br>
                            Maximum size: 2MB
                        </small>
                    </div>
                </div>
            </div>

            <!-- Company Information -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fa fa-building text-primary me-2"></i>Company Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="info-item mb-3">
                        <label class="info-label">Company Name</label>
                        <p class="info-value">{{ $user->company->name ?? 'No company assigned' }}</p>
                    </div>
                    
                    <div class="info-item mb-3">
                        <label class="info-label">User Type</label>
                        <p class="info-value">
                            @if($user->is_super_admin)
                                <span class="badge badge-warning">Super Administrator</span>
                            @else
                                <span class="badge badge-info">Company User</span>
                            @endif
                        </p>
                    </div>
                    
                    <div class="info-item">
                        <label class="info-label">Account Status</label>
                        <p class="info-value">
                            <span class="badge badge-{{ $user->is_active ? 'success' : 'danger' }}">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.info-item {
    margin-bottom: 1rem;
}

.info-label {
    display: block;
    font-weight: 600;
    color: #495057;
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
}

.info-value {
    margin-bottom: 0;
    color: #212529;
    font-size: 1rem;
}

.badge {
    font-size: 0.75rem;
    padding: 0.375em 0.75em;
    font-weight: 500;
}

.badge-success {
    background-color: #28a745;
    color: white;
}

.badge-danger {
    background-color: #dc3545;
    color: white;
}

.badge-warning {
    background-color: #ffc107;
    color: #212529;
}

.badge-info {
    background-color: #17a2b8;
    color: white;
}

.input-group .btn {
    border-left: none;
}

.form-control:focus + .btn {
    border-color: #80bdff;
}

@media (max-width: 768px) {
    .col-lg-4 {
        margin-top: 2rem;
    }
}
</style>

<script>
function togglePassword(fieldName) {
    const field = document.querySelector(`input[name="${fieldName}"]`);
    const button = field.nextElementSibling;
    const icon = button.querySelector('i');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const preview = document.querySelector('.card-body img, .card-body .rounded-circle');
            if (preview.tagName === 'IMG') {
                preview.src = e.target.result;
            } else {
                // Replace the placeholder with an actual image
                const img = document.createElement('img');
                img.src = e.target.result;
                img.alt = 'Profile Picture Preview';
                img.className = 'rounded-circle';
                img.style.cssText = 'width: 120px; height: 120px; object-fit: cover;';
                preview.parentNode.replaceChild(img, preview);
            }
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[action*="profile"]');
    const passwordField = document.querySelector('input[name="password"]');
    const currentPasswordField = document.querySelector('input[name="current_password"]');
    
    passwordField.addEventListener('input', function() {
        if (this.value && !currentPasswordField.value) {
            currentPasswordField.setAttribute('required', 'required');
        } else if (!this.value) {
            currentPasswordField.removeAttribute('required');
        }
    });
});
</script>

@endsection
