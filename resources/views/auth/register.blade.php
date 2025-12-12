@extends('layouts.app')

@section('title', 'Register - E-Commerce Store')

@section('styles')
<style>
    .auth-container {
        min-height: calc(100vh - 200px);
        display: flex;
        align-items: center;
    }

    .auth-card {
        max-width: 500px;
        margin: 0 auto;
    }

    .password-strength {
        height: 4px;
        border-radius: 2px;
        margin-top: 5px;
        transition: all 0.3s;
    }

    .strength-weak { background: #dc3545; width: 33%; }
    .strength-medium { background: #ffc107; width: 66%; }
    .strength-strong { background: #28a745; width: 100%; }
</style>
@endsection

@section('content')
<div class="auth-container py-5">
    <div class="container">
        <div class="auth-card">
            <div class="text-center mb-4">
                <i class="bi bi-shop fs-1 text-primary"></i>
                <h2 class="mt-2">Create Account</h2>
                <p class="text-muted">Join us and start shopping</p>
            </div>

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form id="registerForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" placeholder="John" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" placeholder="Doe" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" id="email" placeholder="you@example.com" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="phone_number" class="form-label">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-phone"></i></span>
                                <input type="tel" class="form-control" id="phone_number" placeholder="+1 234 567 890" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="password" placeholder="Create a strong password" required minlength="8" oninput="checkPasswordStrength()">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                    <i class="bi bi-eye" id="toggleIcon1"></i>
                                </button>
                            </div>
                            <div class="password-strength" id="passwordStrength"></div>
                            <small class="text-muted">At least 8 characters</small>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" class="form-control" id="password_confirmation" placeholder="Confirm your password" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmation')">
                                    <i class="bi bi-eye" id="toggleIcon2"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="#" class="text-decoration-none">Terms of Service</a> and <a href="#" class="text-decoration-none">Privacy Policy</a>
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 btn-lg" id="registerBtn">
                            <span id="registerText">Create Account</span>
                            <span class="spinner-border spinner-border-sm d-none" id="registerSpinner" role="status"></span>
                        </button>
                    </form>
                </div>
            </div>

            <p class="text-center mt-4">
                Already have an account? <a href="/login" class="text-decoration-none fw-bold">Sign in</a>
            </p>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Redirect if already logged in
    if (localStorage.getItem('token')) {
        window.location.href = '/';
    }

    document.getElementById('registerForm').addEventListener('submit', handleRegister);
});

async function handleRegister(e) {
    e.preventDefault();

    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('password_confirmation').value;

    if (password !== confirmPassword) {
        showAlert('Passwords do not match', 'danger');
        return;
    }

    const btn = document.getElementById('registerBtn');
    const text = document.getElementById('registerText');
    const spinner = document.getElementById('registerSpinner');

    // Disable button and show loading
    btn.disabled = true;
    text.textContent = 'Creating account...';
    spinner.classList.remove('d-none');

    const data = {
        first_name: document.getElementById('first_name').value,
        last_name: document.getElementById('last_name').value,
        email: document.getElementById('email').value,
        phone_number: document.getElementById('phone_number').value,
        password: password,
        password_confirmation: confirmPassword
    };

    try {
        const response = await axios.post('/auth/register', data);
        const result = response.data.data || response.data;

        // Store token and user
        if (result.access_token) {
            localStorage.setItem('token', result.access_token);
            localStorage.setItem('user', JSON.stringify(result.user));
            axios.defaults.headers.common['Authorization'] = `Bearer ${result.access_token}`;
        }

        showAlert('Account created successfully! Please check your email to verify your account.', 'success');

        // Redirect after delay
        setTimeout(() => {
            window.location.href = '/login';
        }, 2000);

    } catch (error) {
        console.error('Registration error:', error);

        let message = 'Registration failed. Please try again.';
        if (error.response?.data?.message) {
            message = error.response.data.message;
        } else if (error.response?.data?.errors) {
            const errors = error.response.data.errors;
            message = Object.values(errors).flat().join('<br>');
        }

        showAlert(message, 'danger');

        // Reset button
        btn.disabled = false;
        text.textContent = 'Create Account';
        spinner.classList.add('d-none');
    }
}

function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = input.parentElement.querySelector('i');

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

function checkPasswordStrength() {
    const password = document.getElementById('password').value;
    const strengthBar = document.getElementById('passwordStrength');

    strengthBar.className = 'password-strength';

    if (password.length === 0) {
        strengthBar.style.width = '0';
        return;
    }

    let strength = 0;
    if (password.length >= 8) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;

    if (strength <= 1) {
        strengthBar.classList.add('strength-weak');
    } else if (strength <= 2) {
        strengthBar.classList.add('strength-medium');
    } else {
        strengthBar.classList.add('strength-strong');
    }
}
</script>
@endsection
