@extends('layouts.app')
@section('content')

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <x-breadcrumb 
        title="Edit User" 
        subtitle="Update user information and settings" 
        icon="fa-user-edit">
        <li class="breadcrumb-item">
            <a href="{{ route('users.index') }}" class="text-decoration-none">User Management</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Edit {{ $user->f_name ?? 'User' }}</li>
        
        <x-slot name="actions">
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary me-2">
                <i class="fa fa-arrow-left me-2"></i>Back to Users
            </a>
            <button type="submit" form="updateUserForm" class="btn btn-primary">
                <i class="fa fa-save me-2"></i>Update User
            </button>
        </x-slot>
    </x-breadcrumb>
    @include('partials.message')

    <!-- Form Section -->
    <div class="card shadow-sm">
        <div class="card-body p-4" style="background-color:#F5F5F6;">
            <form id="updateUserForm" action="{{ route('users.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')
                <!-- Basic Information -->
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">First Name</label>
                            <input type="text"
                                class="form-control form-control-lg @error('f_name') is-invalid @enderror" id="f_name"
                                name="f_name" value="{{ $user->f_name }}" placeholder="Enter first name" required>
                            @error('f_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">Last Name</label>
                            <input type="text"
                                class="form-control form-control-lg @error('l_name') is-invalid @enderror" id="l_name"
                                name="l_name" value="{{ $user->l_name }}" placeholder="Enter last name" required>
                            @error('l_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">Email Address</label>
                            <input type="email"
                                class="form-control form-control-lg @error('email') is-invalid @enderror" id="email"
                                name="email" value="{{ $user->email }}" placeholder="Enter email address" readonly
                                required>
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">Phone Number</label>
                            <input type="text" class="form-control form-control-lg @error('phone') is-invalid @enderror"
                                id="phone" name="phone" value="{{ $user->phone }}" placeholder="Enter phone number">
                            @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">Password</label>
                            <div class="input-group input-group-lg">
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                    id="password" name="password" placeholder="Leave blank to keep current password">
                                <button class="btn btn-outline-secondary" type="button" id="generatePassword" title="Generate Password">
                                    <i class="fa fa-random"></i>
                                </button>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword" title="Show/Hide Password">
                                    <i class="fa fa-eye"></i>
                                </button>
                            </div>
                            <div class="form-text small mt-1">
                                <small class="text-muted">Leave blank to keep current password, or generate a new secure password.</small>
                            </div>
                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">Role</label>
                            <select class="form-control custom-select" name="role" required>
                                <option value="">Select Role</option>
                                @foreach($roles as $role)
                                <option value="{{ $role->id }}" @if(old('role')) {{ old('role')==$role->id ? 'selected'
                                    : '' }}
                                    @else
                                    {{ $user->roles->contains('id', $role->id) ? 'selected' : '' }}
                                    @endif
                                    >
                                    {{ $role->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">Company</label>
                            <input type="text" class="form-control form-control-lg" value="{{ $user->company ? $user->company->name : '' }}" readonly>
                            <input type="hidden" name="company_id" value="{{ $user->company_id }}">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-bold mb-1">Status</label>
                            <select class="form-control custom-select" name="is_active">
                                <option value="1" {{ old('is_active', $user->is_active) == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('is_active', $user->is_active) == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>

                <div class="col-12">
                    <div class="form-group">
                        <label class="form-label fw-bold mb-1">Address</label>
                        <textarea class="form-control form-control-lg @error('address') is-invalid @enderror"
                            id="address" name="address" rows="3"
                            placeholder="Enter full address">{{ $user->address }}</textarea>
                        @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
        </div>

        <!-- Form Actions -->
        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
            <!-- <a href="{{ route('users.index') }}" class="btn btn-light btn-lg px-4">
                        <i class="fa fa-times me-2"></i>Cancel
                    </a> -->
            <button type="submit" class="btn btn-primary btn-lg px-4">
                <i class="fa fa-save me-2"></i> <span> Update User</span>
            </button>
        </div>
        </form>
    </div>
</div>
</div>


<script>
    document.addEventListener("DOMContentLoaded", function () {
        const form = document.getElementById("updateUserForm");
        const passwordInput = document.getElementById("password");
        const togglePassword = document.getElementById("togglePassword");
        const generatePassword = document.getElementById("generatePassword");
        const emailInput = document.getElementById("email");
        const phoneInput = document.getElementById("phone");

        // Generate secure password
        generatePassword.addEventListener("click", () => {
            const chars = {
                uppercase: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
                lowercase: 'abcdefghijklmnopqrstuvwxyz',
                numbers: '0123456789',
                special: '!@#$%^&*()_+-=[]{}|;:,.<>?'
            };
            
            let password = '';
            // Ensure at least one character from each category
            password += chars.uppercase.charAt(Math.floor(Math.random() * chars.uppercase.length));
            password += chars.lowercase.charAt(Math.floor(Math.random() * chars.lowercase.length));
            password += chars.numbers.charAt(Math.floor(Math.random() * chars.numbers.length));
            password += chars.special.charAt(Math.floor(Math.random() * chars.special.length));
            
            // Fill the rest with random characters
            const allChars = chars.uppercase + chars.lowercase + chars.numbers + chars.special;
            for (let i = 4; i < 12; i++) {
                password += allChars.charAt(Math.floor(Math.random() * allChars.length));
            }
            
            // Shuffle the password
            password = password.split('').sort(() => Math.random() - 0.5).join('');
            
            passwordInput.value = password;
            passwordInput.type = 'text'; // Show generated password
            togglePassword.innerHTML = '<i class="fa fa-eye-slash"></i>';
            
            // Update password requirements display
            passwordInput.dispatchEvent(new Event('input'));
            
            // Show success message
            const generateBtn = document.getElementById("generatePassword");
            const originalIcon = generateBtn.innerHTML;
            generateBtn.innerHTML = '<i class="fa fa-check text-success"></i>';
            generateBtn.classList.add('btn-success');
            generateBtn.classList.remove('btn-outline-secondary');
            
            setTimeout(() => {
                generateBtn.innerHTML = originalIcon;
                generateBtn.classList.remove('btn-success');
                generateBtn.classList.add('btn-outline-secondary');
            }, 2000);
        });

        // Show/hide password
        togglePassword.addEventListener("click", () => {
            const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
            passwordInput.setAttribute("type", type);
            togglePassword.innerHTML = type === "password" ? '<i class="fa fa-eye"></i>' : '<i class="fa fa-eye-slash"></i>';
        });

        // Password requirement check (only if password is entered)
        const passwordRequirements = {
            length: { regex: /.{8,}/, message: "At least 8 characters" },
            uppercase: { regex: /[A-Z]/, message: "At least one uppercase letter" },
            lowercase: { regex: /[a-z]/, message: "At least one lowercase letter" },
            digit: { regex: /[0-9]/, message: "At least one number" },
            special: { regex: /[\W_]/, message: "At least one special character" }
        };

        // Create feedback container just below the input group
        const inputGroup = passwordInput.closest(".input-group");
        const feedbackContainer = document.createElement("div");
        feedbackContainer.className = "password-feedback mt-2";
        inputGroup.parentNode.insertBefore(feedbackContainer, inputGroup.nextSibling.nextSibling);

        passwordInput.addEventListener("input", () => {
            if (passwordInput.value.length === 0) {
                feedbackContainer.innerHTML = '';
                return;
            }
            
            let feedback = '';
            Object.keys(passwordRequirements).forEach(key => {
                const requirement = passwordRequirements[key];
                const passed = requirement.regex.test(passwordInput.value);
                feedback += `<div class="small ${passed ? 'text-success' : 'text-danger'}"><i class="fa ${passed ? 'fa-check' : 'fa-times'} me-1"></i>${requirement.message}</div>`;
            });
            feedbackContainer.innerHTML = feedback;
        });

        form.addEventListener("submit", function (e) {
            // Only validate password if it's not empty (for edit form)
            if (passwordInput.value.length > 0) {
                let passwordValid = Object.values(passwordRequirements).every(req => req.regex.test(passwordInput.value));
                if (!passwordValid) {
                    e.preventDefault();
                    alert("Password does not meet all requirements.");
                    passwordInput.focus();
                    return;
                }
            }
        });
    });
</script>


@endsection