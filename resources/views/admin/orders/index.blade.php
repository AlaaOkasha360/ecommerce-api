@extends('admin.layouts.app')

@section('title', 'Orders Management')
@section('page-title', 'Orders Management')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <input type="search" class="form-control" placeholder="Search orders..." id="orderSearch" oninput="searchOrders()" style="max-width: 300px;">
            </div>
            <select class="form-select" id="statusFilter" onchange="loadOrders()" style="max-width: 200px;">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
                <option value="shipped">Shipped</option>
                <option value="delivered">Delivered</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
    </div>
</div>

<div class="card data-card">
    <div class="card-header">
        <h6 class="mb-0"><i class="bi bi-receipt me-2"></i>Orders</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="ordersTable">
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <div class="spinner-border spinner-border-sm" role="status"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Order Detail Modal -->
<div class="modal fade" id="orderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderModalTitle">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="orderModalContent">
                <!-- Order details loaded dynamically -->
            </div>
            <div class="modal-footer">
                <div id="statusUpdateSection">
                    <select class="form-select" id="newStatus" style="max-width: 150px;">
                        <option value="">Update Status</option>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="updateOrderStatusBtn">Update Status</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let orderModal;
let currentOrderId = null;

document.addEventListener('DOMContentLoaded', function() {
    orderModal = new bootstrap.Modal(document.getElementById('orderModal'));
    loadOrders();
    document.getElementById('updateOrderStatusBtn').addEventListener('click', updateOrderStatus);
});

async function loadOrders() {
    try {
        const response = await axios.get('/admin/orders');
        let orders = response.data.data?.orders || response.data.orders || [];

        // Filter by status if selected
        const statusFilter = document.getElementById('statusFilter').value;
        if (statusFilter) {
            orders = orders.filter(order => order.status === statusFilter);
        }

        displayOrders(orders);
    } catch (error) {
        console.error('Error loading orders:', error);
        document.getElementById('ordersTable').innerHTML =
            '<tr><td colspan="7" class="text-center text-danger">Error loading orders</td></tr>';
    }
}

function displayOrders(orders) {
    const tbody = document.getElementById('ordersTable');

    if (!orders || orders.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">No orders found</td></tr>';
        return;
    }

    const statusColors = {
        pending: 'warning',
        processing: 'info',
        shipped: 'primary',
        delivered: 'success',
        cancelled: 'danger'
    };

    tbody.innerHTML = orders.map(order => `
        <tr>
            <td><strong>#${order.order_number || order.id}</strong></td>
            <td>${order.user?.first_name || 'Customer'} ${order.user?.last_name || ''}</td>
            <td>${order.items_count || (order.order_items?.length || 0)} items</td>
            <td>${formatCurrency(order.total_amount || 0)}</td>
            <td><span class="badge bg-${statusColors[order.status] || 'secondary'} text-capitalize">${order.status || 'pending'}</span></td>
            <td>${formatDate(order.created_at)}</td>
            <td>
                <button class="btn btn-sm btn-outline-primary" onclick="viewOrder(${order.id})">
                    <i class="bi bi-eye"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

async function viewOrder(orderId) {
    currentOrderId = orderId;
    const content = document.getElementById('orderModalContent');

    content.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border spinner-border-sm" role="status"></div>
        </div>
    `;

    try {
        const response = await axios.get(`/admin/orders/${orderId}`);
        const order = response.data.data || response.data;

        document.getElementById('orderModalTitle').textContent = `Order #${order.order_number || order.id}`;

        const statusColors = {
            pending: 'warning',
            processing: 'info',
            shipped: 'primary',
            delivered: 'success',
            cancelled: 'danger'
        };

        content.innerHTML = `
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6>Order Information</h6>
                    <p class="mb-1"><strong>Order ID:</strong> #${order.id}</p>
                    <p class="mb-1"><strong>Date:</strong> ${formatDate(order.created_at)}</p>
                    <p class="mb-1"><strong>Status:</strong> <span class="badge bg-${statusColors[order.status] || 'secondary'}">${order.status || 'pending'}</span></p>
                    <p class="mb-1"><strong>Payment Method:</strong> ${order.payment_method || 'N/A'}</p>
                </div>
                <div class="col-md-6">
                    <h6>Customer Information</h6>
                    <p class="mb-1"><strong>Name:</strong> ${order.user?.first_name} ${order.user?.last_name}</p>
                    <p class="mb-1"><strong>Email:</strong> ${order.user?.email}</p>
                    <p class="mb-1"><strong>Phone:</strong> ${order.user?.phone_number || 'N/A'}</p>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <h6>Shipping Address</h6>
                    <p class="text-muted mb-0">${order.shipping_address?.street_address || 'N/A'}<br>${order.shipping_address?.city || ''}, ${order.shipping_address?.state || ''} ${order.shipping_address?.postal_code || ''}<br>${order.shipping_address?.country || ''}</p>
                </div>
                <div class="col-md-6">
                    <h6>Billing Address</h6>
                    <p class="text-muted mb-0">${order.billing_address?.street_address || 'N/A'}<br>${order.billing_address?.city || ''}, ${order.billing_address?.state || ''} ${order.billing_address?.postal_code || ''}<br>${order.billing_address?.country || ''}</p>
                </div>
            </div>

            <h6>Order Items</h6>
            <div class="table-responsive mb-4">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${(order.items || []).map(item => `
                            <tr>
                                <td>${item.product_name}</td>
                                <td>${formatCurrency(item.price)}</td>
                                <td>${item.quantity}</td>
                                <td>${formatCurrency(item.subtotal)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>

            <div class="row">
                <div class="col-md-6"></div>
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Subtotal:</strong></td>
                            <td class="text-end">${formatCurrency(order.subtotal || 0)}</td>
                        </tr>
                        <tr>
                            <td><strong>Shipping:</strong></td>
                            <td class="text-end">${formatCurrency(order.shipping_cost || 0)}</td>
                        </tr>
                        <tr>
                            <td><strong>Tax:</strong></td>
                            <td class="text-end">${formatCurrency(order.tax || 0)}</td>
                        </tr>
                        <tr class="table-active">
                            <td><strong>Total:</strong></td>
                            <td class="text-end"><strong>${formatCurrency(order.total_amount || 0)}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        `;

        // Set current status
        document.getElementById('newStatus').value = '';
        orderModal.show();

    } catch (error) {
        console.error('Error loading order:', error);
        content.innerHTML = '<div class="alert alert-danger">Failed to load order details.</div>';
    }
}

async function updateOrderStatus() {
    const newStatus = document.getElementById('newStatus').value;

    if (!newStatus) {
        showAlert('Please select a status', 'warning');
        return;
    }

    try {
        await axios.put(`/admin/orders/${currentOrderId}/status`, { status: newStatus });
        showAlert('Order status updated successfully', 'success');
        orderModal.hide();
        loadOrders();
    } catch (error) {
        console.error('Error updating order:', error);
        showAlert('Failed to update order status', 'danger');
    }
}

function searchOrders() {
    const query = document.getElementById('orderSearch').value.toLowerCase();
    const rows = document.querySelectorAll('#ordersTable tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
    });
}
</script>
@endsection
