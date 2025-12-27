@extends('layouts.app')
@section('content')
    <div class="container-fluid py-4">
        <!-- Breadcrumb -->
        <x-breadcrumb 
            title="Create New User" 
            subtitle="Add a new user to the system" 
            icon="fa-user-plus">
            <li class="breadcrumb-item">
                <a href="{{ route('users.index') }}" class="text-decoration-none">User Management</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Create User</li>
            
            <x-slot name="actions">
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                    <i class="fa fa-arrow-left me-2"></i>Back to Users
                </a>
            </x-slot>
        </x-breadcrumb>

        @include('partials.message')


        <!-- Form Section -->
        <div class="card shadow-sm">
            <div class="card-body p-4" style="background-color:#F5F5F6;">
                <form id="createUserForm" action="{{ route('users.store') }}" method="POST">
                    @csrf

                    <!-- Basic Information -->
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label fw-bold mb-1">First Name</label>
                                <input type="text"
                                    class="form-control form-control-lg @error('f_name') is-invalid @enderror"
                                    id="f_name" name="f_name" value="{{ old('f_name') }}" placeholder="Enter first name"
                                    required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label fw-bold mb-1">Last Name</label>
                                <input type="text"
                                    class="form-control form-control-lg @error('l_name') is-invalid @enderror"
                                    id="l_name" name="l_name" value="{{ old('l_name') }}" placeholder="Enter last name"
                                    required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label fw-bold mb-1">Email Address</label>
                                <input type="email"
                                    class="form-control form-control-lg @error('email') is-invalid @enderror" id="email"
                                    name="email" value="{{ old('email') }}" placeholder="Enter email address" required>
                            </div>
                        </div>
                        <input type="hidden" name="source" value="{{ request()->get('source') }}">
                        <input type="hidden" name="manifest_id" value="{{ request()->get('manifest_id') }}">

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label fw-bold mb-1">Phone Number</label>
                                <input type="text"
                                    class="form-control form-control-lg @error('phone') is-invalid @enderror" id="phone"
                                    name="phone" value="{{ old('phone') }}" placeholder="Enter phone number">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label fw-bold mb-1">Password</label>
                                <div class="input-group input-group-lg">
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                        id="password" name="password" placeholder="Enter password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="generatePassword" title="Generate Password">
                                        <i class="fa fa-random"></i>
                                    </button>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword" title="Show/Hide Password">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text small mt-1">
                                    <small class="text-muted">Password must be at least 8 characters with uppercase, lowercase, number, and special character.</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label fw-bold mb-1">Role</label>
                                <select class="form-control custom-select" name="role" required>
                                    <option value="">Select Role</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}" {{ old('role') == $role->id ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label fw-bold mb-1">Select Company</label>
                                <select class="form-control custom-select" name="company_id" required>
                                    {{-- <option value="">Select Company</option> --}}
                                    @foreach ($companies as $company)
                                        <option value="{{ $company->id }}"
                                            {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                            {{ $company->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label fw-bold mb-1">Status</label>
                                <select class="form-control custom-select" name="is_active">
                                    <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-label fw-bold mb-1">Address</label>
                                <textarea class="form-control form-control-lg @error('address') is-invalid @enderror" id="address" name="address"
                                    rows="3" placeholder="Enter full address">{{ old('address') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-primary btn-lg px-4">
                            <i class="fa fa-save me-2"></i><span> Create User</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>



    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.getElementById("createUserForm");
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
                togglePassword.innerHTML = type === "password" ? '<i class="fa fa-eye"></i>' :
                    '<i class="fa fa-eye-slash"></i>';
            });

            // Password requirement check
            const passwordRequirements = {
                length: {
                    regex: /.{8,}/,
                    message: "At least 8 characters"
                },
                uppercase: {
                    regex: /[A-Z]/,
                    message: "At least one uppercase letter"
                },
                lowercase: {
                    regex: /[a-z]/,
                    message: "At least one lowercase letter"
                },
                digit: {
                    regex: /[0-9]/,
                    message: "At least one number"
                },
                special: {
                    regex: /[\W_]/,
                    message: "At least one special character"
                }
            };

            // Create feedback container just below the input group
            const inputGroup = passwordInput.closest(".input-group");
            const feedbackContainer = document.createElement("div");
            feedbackContainer.className = "form-text small mt-1 ms-1";
            inputGroup.parentNode.insertBefore(feedbackContainer, inputGroup.nextSibling);


            passwordInput.addEventListener("input", () => {
                let feedback = '';
                Object.keys(passwordRequirements).forEach(key => {
                    const requirement = passwordRequirements[key];
                    const passed = requirement.regex.test(passwordInput.value);
                    feedback +=
                        `<div class="${passed ? 'text-success' : 'text-danger'}">${requirement.message}</div>`;
                });
                feedbackContainer.innerHTML = feedback;
            });

            form.addEventListener("submit", function(e) {
                // Debug: Check role and company values before submission
                const roleSelect = form.querySelector('select[name="role"]');
                const companySelect = form.querySelector('select[name="company_id"]');
                
                if (roleSelect) {
                    console.log('Role selected:', roleSelect.value);
                    if (!roleSelect.value || roleSelect.value === '') {
                        e.preventDefault();
                        alert('Please select a role before submitting the form.');
                        roleSelect.focus();
                        return;
                    }
                }
                
                if (companySelect) {
                    console.log('Company selected:', companySelect.value);
                    if (!companySelect.value || companySelect.value === '') {
                        e.preventDefault();
                        alert('Please select a company before submitting the form.');
                        companySelect.focus();
                        return;
                    }
                }

                let passwordValid = Object.values(passwordRequirements).every(req => req.regex.test(
                    passwordInput.value));
                if (!passwordValid) {
                    e.preventDefault();
                    alert("Password does not meet all requirements.");
                    passwordInput.focus();
                    return;
                }

                // Email is already validated by HTML5, but double-check:
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(emailInput.value)) {
                    e.preventDefault();
                    alert("Please enter a valid email address.");
                    emailInput.focus();
                    return;
                }
            });
        });
    </script>
@endsection
