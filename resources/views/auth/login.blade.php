@extends('layouts.app')

@section('title', 'Login - E-Commerce Store')

@section('styles')
<style>
    .auth-container {
        min-height: calc(100vh - 200px);
        display: flex;
        align-items: center;
    }

    .auth-card {
        max-width: 450px;
        margin: 0 auto;
    }

    .divider {
        display: flex;
        align-items: center;
        text-align: center;
        color: #6c757d;
    }

    .divider::before,
    .divider::after {
        content: '';
        flex: 1;
        border-bottom: 1px solid #dee2e6;
    }

    .divider::before {
        margin-right: 1rem;
    }

    .divider::after {
        margin-left: 1rem;
    }
</style>
@endsection

@section('content')
<div class="auth-container py-5">
    <div class="container">
        <div class="auth-card">
            <div class="text-center mb-4">
                <i class="bi bi-shop fs-1 text-primary"></i>
                <h2 class="mt-2">Welcome Back</h2>
                <p class="text-muted">Sign in to your account</p>
            </div>

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form id="loginForm">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" id="email" placeholder="you@example.com" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="password" placeholder="Enter your password" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                    <i class="bi bi-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember">
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>
                            <a href="/forgot-password" class="text-decoration-none">Forgot password?</a>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 btn-lg" id="loginBtn">
                            <span id="loginText">Sign In</span>
                            <span class="spinner-border spinner-border-sm d-none" id="loginSpinner" role="status"></span>
                        </button>
                    </form>

                    <div class="divider my-4">or</div>

                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-dark">
                            <i class="bi bi-google"></i> Continue with Google
                        </button>
                    </div>
                </div>
            </div>

            <p class="text-center mt-4">
                Don't have an account? <a href="/register" class="text-decoration-none fw-bold">Sign up</a>
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

    document.getElementById('loginForm').addEventListener('submit', handleLogin);
});

async function handleLogin(e) {
    e.preventDefault();

    const btn = document.getElementById('loginBtn');
    const text = document.getElementById('loginText');
    const spinner = document.getElementById('loginSpinner');

    // Disable button and show loading
    btn.disabled = true;
    text.textContent = 'Signing in...';
    spinner.classList.remove('d-none');

    const data = {
        email: document.getElementById('email').value,
        password: document.getElementById('password').value
    };

    try {
        const response = await axios.post('/auth/login', data);
        const result = response.data.data || response.data;

        // Store token and user
        localStorage.setItem('token', result.access_token);
        localStorage.setItem('user', JSON.stringify(result.user));

        // Update axios header
        axios.defaults.headers.common['Authorization'] = `Bearer ${result.access_token}`;

        showAlert('Login successful! Redirecting...', 'success');

        // Redirect after short delay
        setTimeout(() => {
            const redirect = new URLSearchParams(window.location.search).get('redirect') || '/';
            window.location.href = redirect;
        }, 1000);

    } catch (error) {
        console.error('Login error:', error);

        let message = 'Login failed. Please try again.';
        if (error.response) {
            if (error.response.status === 401) {
                message = 'Invalid email or password.';
            } else if (error.response.status === 403) {
                message = 'Please verify your email before logging in.';
            } else if (error.response.data?.message) {
                message = error.response.data.message;
            }
        }

        showAlert(message, 'danger');

        // Reset button
        btn.disabled = false;
        text.textContent = 'Sign In';
        spinner.classList.add('d-none');
    }
}

function togglePassword() {
    const input = document.getElementById('password');
    const icon = document.getElementById('toggleIcon');

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
</script>
@endsection
