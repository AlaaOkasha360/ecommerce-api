@extends('layouts.app')

@section('title', 'My Profile - E-Commerce Store')

@section('content')
<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Home</a></li>
            <li class="breadcrumb-item active">Profile</li>
        </ol>
    </nav>

    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="bi bi-person-circle display-1 text-muted"></i>
                    </div>
                    <h5 id="profileName">Loading...</h5>
                    <p class="text-muted" id="profileEmail">Loading...</p>
                </div>
                <div class="list-group list-group-flush">
                    <a href="/profile" class="list-group-item list-group-item-action active">
                        <i class="bi bi-person me-2"></i> Profile
                    </a>
                    <a href="/orders" class="list-group-item list-group-item-action">
                        <i class="bi bi-box me-2"></i> Orders
                    </a>
                    <a href="/addresses" class="list-group-item list-group-item-action">
                        <i class="bi bi-geo-alt me-2"></i> Addresses
                    </a>
                    <a href="#" class="list-group-item list-group-item-action text-danger" id="sidebarLogout">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-person"></i> Profile Information</h5>
                </div>
                <div class="card-body">
                    <form id="profileForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" readonly>
                            <small class="text-muted">Email cannot be changed</small>
                        </div>

                        <div class="mb-3">
                            <label for="phone_number" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone_number">
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Save Changes
                        </button>
                    </form>
                </div>
            </div>

            <!-- Change Password Section -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-lock"></i> Change Password</h5>
                </div>
                <div class="card-body">
                    <form id="passwordForm">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" required minlength="8">
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" required>
                        </div>

                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-key"></i> Change Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (!requireAuth()) return;

    loadProfile();

    document.getElementById('profileForm').addEventListener('submit', updateProfile);
    document.getElementById('passwordForm').addEventListener('submit', changePassword);
    document.getElementById('sidebarLogout').addEventListener('click', handleLogout);
});

async function loadProfile() {
    try {
        const response = await axios.get('/users/profile');
        const user = response.data.data || response.data;

        document.getElementById('profileName').textContent = `${user.first_name} ${user.last_name}`;
        document.getElementById('profileEmail').textContent = user.email;

        document.getElementById('first_name').value = user.first_name || '';
        document.getElementById('last_name').value = user.last_name || '';
        document.getElementById('email').value = user.email || '';
        document.getElementById('phone_number').value = user.phone_number || '';

    } catch (error) {
        console.error('Error loading profile:', error);
        showAlert('Failed to load profile', 'danger');
    }
}

async function updateProfile(e) {
    e.preventDefault();

    const data = {
        first_name: document.getElementById('first_name').value,
        last_name: document.getElementById('last_name').value,
        phone_number: document.getElementById('phone_number').value
    };

    try {
        await axios.put('/users/profile', data);

        // Update local storage
        const user = JSON.parse(localStorage.getItem('user') || '{}');
        user.first_name = data.first_name;
        user.last_name = data.last_name;
        localStorage.setItem('user', JSON.stringify(user));

        showAlert('Profile updated successfully', 'success');
        loadProfile();
    } catch (error) {
        console.error('Error updating profile:', error);
        showAlert('Failed to update profile', 'danger');
    }
}

async function changePassword(e) {
    e.preventDefault();

    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    if (newPassword !== confirmPassword) {
        showAlert('Passwords do not match', 'danger');
        return;
    }

    const data = {
        current_password: document.getElementById('current_password').value,
        password: newPassword,
        password_confirmation: confirmPassword
    };

    try {
        await axios.put('/users/profile', data);
        showAlert('Password changed successfully', 'success');
        document.getElementById('passwordForm').reset();
    } catch (error) {
        console.error('Error changing password:', error);
        showAlert('Failed to change password. Make sure your current password is correct.', 'danger');
    }
}
</script>
@endsection
