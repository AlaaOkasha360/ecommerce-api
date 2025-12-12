@extends('layouts.app')

@section('title', 'My Orders - E-Commerce Store')

@section('styles')
<style>
    .order-card {
        transition: all 0.3s;
    }

    .order-card:hover {
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .status-pending { background-color: #ffc107; color: #000; }
    .status-processing { background-color: #17a2b8; color: #fff; }
    .status-shipped { background-color: #007bff; color: #fff; }
    .status-delivered { background-color: #28a745; color: #fff; }
    .status-cancelled { background-color: #dc3545; color: #fff; }
</style>
@endsection

@section('content')
<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Home</a></li>
            <li class="breadcrumb-item active">My Orders</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-box-seam"></i> My Orders</h2>
    </div>

    <!-- Orders List -->
    <div id="ordersContainer">
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>
</div>

<!-- Order Detail Modal -->
<div class="modal fade" id="orderDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="orderDetailContent">
                <!-- Order details loaded dynamically -->
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (!requireAuth()) return;
    loadOrders();
});

async function loadOrders() {
    try {
        const response = await axios.get('/users/orders');
        const orders = response.data.data || response.data;
        displayOrders(orders);
    } catch (error) {
        console.error('Error loading orders:', error);
        document.getElementById('ordersContainer').innerHTML = `
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-circle"></i> Failed to load orders. Please try again.
            </div>
        `;
    }
}

function displayOrders(orders) {
    const container = document.getElementById('ordersContainer');

    if (!orders || orders.length === 0) {
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="bi bi-box fs-1 text-muted"></i>
                <h4 class="mt-3">No orders yet</h4>
                <p class="text-muted">You haven't placed any orders yet.</p>
                <a href="/products" class="btn btn-primary">Start Shopping</a>
            </div>
        `;
        return;
    }

    container.innerHTML = orders.map(order => `
        <div class="card order-card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <strong>Order #${order.id}</strong>
                    <span class="text-muted ms-3">${formatDate(order.created_at)}</span>
                </div>
                <span class="badge status-${order.status?.toLowerCase() || 'pending'}">${order.status || 'Pending'}</span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <p class="mb-1"><strong>Items:</strong> ${order.items_count || order.order_items?.length || 'N/A'}</p>
                        <p class="mb-0"><strong>Shipping:</strong> ${order.shipping_address || 'N/A'}</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <p class="fs-5 fw-bold text-primary mb-2">$${parseFloat(order.total_amount || 0).toFixed(2)}</p>
                        <button class="btn btn-outline-primary btn-sm" onclick="viewOrderDetail(${order.id})">
                            <i class="bi bi-eye"></i> View Details
                        </button>
                        ${order.status === 'pending' ? `
                            <button class="btn btn-outline-danger btn-sm ms-2" onclick="cancelOrder(${order.id})">
                                <i class="bi bi-x-circle"></i> Cancel
                            </button>
                        ` : ''}
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

async function viewOrderDetail(orderId) {
    const modal = new bootstrap.Modal(document.getElementById('orderDetailModal'));
    const content = document.getElementById('orderDetailContent');

    content.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status"></div>
        </div>
    `;

    modal.show();

    try {
        const response = await axios.get(`/users/orders/${orderId}`);
        const order = response.data.data || response.data;

        content.innerHTML = `
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6>Order Information</h6>
                    <p class="mb-1"><strong>Order ID:</strong> #${order.id}</p>
                    <p class="mb-1"><strong>Date:</strong> ${formatDate(order.created_at)}</p>
                    <p class="mb-1"><strong>Status:</strong> <span class="badge status-${order.status?.toLowerCase()}">${order.status}</span></p>
                </div>
                <div class="col-md-6">
                    <h6>Shipping Address</h6>
                    <p class="text-muted">${order.shipping_address || 'N/A'}</p>
                </div>
            </div>

            <h6>Order Items</h6>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${(order.order_items || []).map(item => `
                            <tr>
                                <td>${item.product?.name || 'Product'}</td>
                                <td>$${parseFloat(item.price).toFixed(2)}</td>
                                <td>${item.quantity}</td>
                                <td>$${(parseFloat(item.price) * item.quantity).toFixed(2)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                            <td><strong class="text-primary">$${parseFloat(order.total_amount || 0).toFixed(2)}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        `;
    } catch (error) {
        console.error('Error loading order details:', error);
        content.innerHTML = '<div class="alert alert-danger">Failed to load order details.</div>';
    }
}

async function cancelOrder(orderId) {
    if (!confirm('Are you sure you want to cancel this order?')) return;

    try {
        await axios.put(`/orders/${orderId}/cancel`);
        showAlert('Order cancelled successfully', 'success');
        loadOrders();
    } catch (error) {
        console.error('Error cancelling order:', error);
        showAlert('Failed to cancel order', 'danger');
    }
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}
</script>
@endsection
