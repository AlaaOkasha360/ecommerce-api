@extends('layouts.app')

@section('title', 'My Addresses - E-Commerce Store')

@section('content')
<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Home</a></li>
            <li class="breadcrumb-item active">Addresses</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-geo-alt"></i> My Addresses</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addressModal" onclick="openAddModal()">
            <i class="bi bi-plus-lg"></i> Add Address
        </button>
    </div>

    <!-- Addresses List -->
    <div class="row" id="addressesContainer">
        <div class="col-12 text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>
</div>

<!-- Address Modal -->
<div class="modal fade" id="addressModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addressModalTitle">Add Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addressForm">
                <div class="modal-body">
                    <input type="hidden" id="address_id">

                    <div class="mb-3">
                        <label for="address_type" class="form-label">Address Type</label>
                        <select class="form-select" id="address_type" required>
                            <option value="shipping">Shipping</option>
                            <option value="billing">Billing</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="address_line_1" class="form-label">Address Line 1</label>
                        <input type="text" class="form-control" id="address_line_1" placeholder="Street address" required>
                    </div>

                    <div class="mb-3">
                        <label for="address_line_2" class="form-label">Address Line 2 (Optional)</label>
                        <input type="text" class="form-control" id="address_line_2" placeholder="Apt, suite, unit, etc.">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="state" class="form-label">State/Province</label>
                            <input type="text" class="form-control" id="state" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="postal_code" class="form-label">Postal Code</label>
                            <input type="text" class="form-control" id="postal_code" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="country" class="form-label">Country</label>
                            <input type="text" class="form-control" id="country" required>
                        </div>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_default">
                        <label class="form-check-label" for="is_default">
                            Set as default address
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveAddressBtn">Save Address</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let addressModal;
let editingId = null;

document.addEventListener('DOMContentLoaded', function() {
    if (!requireAuth()) return;

    addressModal = new bootstrap.Modal(document.getElementById('addressModal'));
    loadAddresses();

    document.getElementById('addressForm').addEventListener('submit', saveAddress);
});

async function loadAddresses() {
    try {
        const response = await axios.get('/users/addresses');
        const addresses = response.data.data || response.data;
        displayAddresses(addresses);
    } catch (error) {
        console.error('Error loading addresses:', error);
        document.getElementById('addressesContainer').innerHTML = `
            <div class="col-12">
                <div class="alert alert-danger">Failed to load addresses</div>
            </div>
        `;
    }
}

function displayAddresses(addresses) {
    const container = document.getElementById('addressesContainer');

    if (!addresses || addresses.length === 0) {
        container.innerHTML = `
            <div class="col-12 text-center py-5">
                <i class="bi bi-geo-alt fs-1 text-muted"></i>
                <h4 class="mt-3">No addresses saved</h4>
                <p class="text-muted">Add an address to make checkout faster.</p>
            </div>
        `;
        return;
    }

    container.innerHTML = addresses.map(address => `
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="badge bg-${address.address_type === 'shipping' ? 'primary' : 'secondary'}">
                        ${address.address_type || 'Address'}
                    </span>
                    ${address.is_default ? '<span class="badge bg-success">Default</span>' : ''}
                </div>
                <div class="card-body">
                    <p class="mb-1">${address.street_address}</p>
                    ${address.address_line_2 ? `<p class="mb-1">${address.address_line_2}</p>` : ''}
                    <p class="mb-1">${address.city}, ${address.state} ${address.postal_code}</p>
                    <p class="mb-0">${address.country}</p>
                </div>
                <div class="card-footer">
                    <button class="btn btn-outline-primary btn-sm" onclick="editAddress(${JSON.stringify(address).replace(/"/g, '&quot;')})">
                        <i class="bi bi-pencil"></i> Edit
                    </button>
                    <button class="btn btn-outline-danger btn-sm" onclick="deleteAddress(${address.id})">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

function openAddModal() {
    editingId = null;
    document.getElementById('addressModalTitle').textContent = 'Add Address';
    document.getElementById('addressForm').reset();
}

function editAddress(address) {
    editingId = address.id;
    document.getElementById('addressModalTitle').textContent = 'Edit Address';
    document.getElementById('address_id').value = address.id;
    document.getElementById('address_type').value = address.address_type || 'shipping';
    document.getElementById('address_line_1').value = address.street_address || '';
    document.getElementById('address_line_2').value = address.address_line_2 || '';
    document.getElementById('city').value = address.city || '';
    document.getElementById('state').value = address.state || '';
    document.getElementById('postal_code').value = address.postal_code || '';
    document.getElementById('country').value = address.country || '';
    document.getElementById('is_default').checked = address.is_default || false;

    addressModal.show();
}

async function saveAddress(e) {
    e.preventDefault();

    const data = {
        // address_type: document.getElementById('address_type').value,
        street_address: document.getElementById('address_line_1').value,
        // address_line_2: document.getElementById('address_line_2').value,
        city: document.getElementById('city').value,
        state: document.getElementById('state').value,
        postal_code: document.getElementById('postal_code').value,
        country: document.getElementById('country').value,
        is_default: document.getElementById('is_default').checked
    };

    try {
        if (editingId) {
            await axios.put(`/users/addresses/${editingId}`, data);
            showAlert('Address updated successfully', 'success');
        } else {
            await axios.post('/users/addresses', data);
            showAlert('Address added successfully', 'success');
        }

        addressModal.hide();
        loadAddresses();
    } catch (error) {
        console.error('Error saving address:', error);
        showAlert('Failed to save address', 'danger');
    }
}

async function deleteAddress(id) {
    if (!confirm('Are you sure you want to delete this address?')) return;

    try {
        await axios.delete(`/users/addresses/${id}`);
        showAlert('Address deleted', 'success');
        loadAddresses();
    } catch (error) {
        console.error('Error deleting address:', error);
        showAlert('Failed to delete address', 'danger');
    }
}
</script>
@endsection
