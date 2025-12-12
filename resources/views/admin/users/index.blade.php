@extends('admin.layouts.app')

@section('title', 'Users Management')
@section('page-title', 'Users Management')

@section('content')
<div class="card data-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bi bi-people me-2"></i>Users</h6>
        <input type="search" class="form-control" style="max-width: 200px;" placeholder="Search users..." id="userSearch" oninput="searchUsers()">
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="usersTable">
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <div class="spinner-border spinner-border-sm" role="status"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- User Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalTitle">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="userForm">
                <div class="modal-body">
                    <input type="hidden" id="userId">

                    <div class="mb-3">
                        <label for="userFirstName" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="userFirstName" required>
                    </div>

                    <div class="mb-3">
                        <label for="userLastName" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="userLastName" required>
                    </div>

                    <div class="mb-3">
                        <label for="userEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="userEmail" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="userPhone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="userPhone">
                    </div>

                    <div class="mb-3">
                        <label for="userRole" class="form-label">Role</label>
                        <select class="form-select" id="userRole">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <button type="button" class="btn btn-danger" id="deleteUserBtn">Delete User</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let userModal;
let currentUserId = null;

document.addEventListener('DOMContentLoaded', function() {
    userModal = new bootstrap.Modal(document.getElementById('userModal'));
    loadUsers();
    document.getElementById('userForm').addEventListener('submit', saveUser);
});

async function loadUsers() {
    try {
        const response = await axios.get('/admin/users');
        const users = response.data.data || response.data;
        displayUsers(users);
    } catch (error) {
        console.error('Error loading users:', error);
        document.getElementById('usersTable').innerHTML =
            '<tr><td colspan="6" class="text-center text-danger">Error loading users</td></tr>';
    }
}

function displayUsers(users) {
    const tbody = document.getElementById('usersTable');

    if (!users || users.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No users found</td></tr>';
        return;
    }

    tbody.innerHTML = users.map(user => `
        <tr>
            <td><strong>${user.first_name} ${user.last_name}</strong></td>
            <td>${user.email}</td>
            <td>${user.phone_number || '-'}</td>
            <td><span class="badge bg-${user.role === 'admin' ? 'danger' : 'secondary'}">${user.role || 'user'}</span></td>
            <td><span class="badge bg-success">Active</span></td>
            <td>
                <button class="btn btn-sm btn-outline-primary" onclick="editUser('${JSON.stringify(user).replace(/"/g, '&quot;')}')">
                    <i class="bi bi-pencil"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

function editUser(userJson) {
    const user = JSON.parse(userJson.replace(/&quot;/g, '"'));
    currentUserId = user.id;

    document.getElementById('userId').value = user.id;
    document.getElementById('userFirstName').value = user.first_name || '';
    document.getElementById('userLastName').value = user.last_name || '';
    document.getElementById('userEmail').value = user.email;
    document.getElementById('userPhone').value = user.phone_number || '';
    document.getElementById('userRole').value = user.role || 'user';

    userModal.show();
}

async function saveUser(e) {
    e.preventDefault();

    const userId = document.getElementById('userId').value;
    const data = {
        first_name: document.getElementById('userFirstName').value,
        last_name: document.getElementById('userLastName').value,
        phone_number: document.getElementById('userPhone').value,
        role: document.getElementById('userRole').value
    };

    try {
        await axios.put(`/admin/users/${userId}`, data);
        showAlert('User updated successfully', 'success');
        userModal.hide();
        loadUsers();
    } catch (error) {
        console.error('Error updating user:', error);
        showAlert('Failed to update user', 'danger');
    }
}

async function deleteUser() {
    if (!confirm('Are you sure you want to delete this user?')) return;

    try {
        await axios.delete(`/admin/users/${currentUserId}`);
        showAlert('User deleted successfully', 'success');
        userModal.hide();
        loadUsers();
    } catch (error) {
        console.error('Error deleting user:', error);
        showAlert('Failed to delete user', 'danger');
    }
}

function searchUsers() {
    const query = document.getElementById('userSearch').value.toLowerCase();
    const rows = document.querySelectorAll('#usersTable tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
    });
}

document.getElementById('deleteUserBtn')?.addEventListener('click', deleteUser);
</script>
@endsection
