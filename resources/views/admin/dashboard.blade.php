@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card primary h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs text-uppercase text-muted mb-1">Total Revenue</div>
                        <div class="h4 mb-0 fw-bold" id="totalRevenue">$0.00</div>
                    </div>
                    <i class="bi bi-currency-dollar stat-icon text-primary"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card success h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs text-uppercase text-muted mb-1">Total Orders</div>
                        <div class="h4 mb-0 fw-bold" id="totalOrders">0</div>
                    </div>
                    <i class="bi bi-cart-check stat-icon text-success"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card info h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs text-uppercase text-muted mb-1">Total Products</div>
                        <div class="h4 mb-0 fw-bold" id="totalProducts">0</div>
                    </div>
                    <i class="bi bi-box-seam stat-icon text-info"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card warning h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs text-uppercase text-muted mb-1">Total Users</div>
                        <div class="h4 mb-0 fw-bold" id="totalUsers">0</div>
                    </div>
                    <i class="bi bi-people stat-icon text-warning"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Orders -->
    <div class="col-lg-8">
        <div class="card data-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-receipt me-2"></i>Recent Orders</h6>
                <a href="/admin/orders" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody id="recentOrdersTable">
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <div class="spinner-border spinner-border-sm" role="status"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="col-lg-4">
        <div class="card data-card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Order Status</h6>
            </div>
            <div class="card-body" id="orderStatusStats">
                <div class="text-center py-4">
                    <div class="spinner-border spinner-border-sm" role="status"></div>
                </div>
            </div>
        </div>

        <div class="card data-card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-lightning me-2"></i>Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="/admin/products?action=new" class="btn btn-outline-primary">
                        <i class="bi bi-plus-lg me-2"></i>Add New Product
                    </a>
                    <a href="/admin/categories?action=new" class="btn btn-outline-secondary">
                        <i class="bi bi-plus-lg me-2"></i>Add New Category
                    </a>
                    <a href="/admin/orders" class="btn btn-outline-info">
                        <i class="bi bi-list-check me-2"></i>Manage Orders
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Products -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card data-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-box-seam me-2"></i>Recent Products</h6>
                <a href="/admin/products" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="recentProductsTable">
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <div class="spinner-border spinner-border-sm" role="status"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardStats();
    loadRecentOrders();
    loadRecentProducts();
});

async function loadDashboardStats() {
    try {
        // Load products count
        const productsRes = await axios.get('/products');
        const products = productsRes.data.data || productsRes.data;
        document.getElementById('totalProducts').textContent = Array.isArray(products) ? products.length : (productsRes.data.meta?.total || 0);

        // Load users count
        const usersRes = await axios.get('/admin/users');
        const users = usersRes.data.data || usersRes.data;
        document.getElementById('totalUsers').textContent = Array.isArray(users) ? users.length : 0;

        // Load orders
        const ordersRes = await axios.get('/orders');
        const orders = ordersRes.data.data || ordersRes.data;
        document.getElementById('totalOrders').textContent = Array.isArray(orders) ? orders.length : 0;

        // Calculate revenue
        let revenue = 0;
        if (Array.isArray(orders)) {
            revenue = orders.reduce((sum, order) => sum + parseFloat(order.total_amount || 0), 0);
        }
        document.getElementById('totalRevenue').textContent = formatCurrency(revenue);

        // Order status breakdown
        displayOrderStatus(orders);

    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

function displayOrderStatus(orders) {
    const container = document.getElementById('orderStatusStats');

    if (!Array.isArray(orders) || orders.length === 0) {
        container.innerHTML = '<p class="text-muted text-center mb-0">No orders yet</p>';
        return;
    }

    const statusCounts = {};
    orders.forEach(order => {
        const status = order.status || 'pending';
        statusCounts[status] = (statusCounts[status] || 0) + 1;
    });

    const statusColors = {
        pending: 'warning',
        processing: 'info',
        shipped: 'primary',
        delivered: 'success',
        cancelled: 'danger'
    };

    container.innerHTML = Object.entries(statusCounts).map(([status, count]) => `
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-capitalize">${status}</span>
            <span class="badge bg-${statusColors[status] || 'secondary'}">${count}</span>
        </div>
    `).join('');
}

async function loadRecentOrders() {
    try {
        const response = await axios.get('/orders');
        const orders = (response.data.data || response.data).slice(0, 5);

        const tbody = document.getElementById('recentOrdersTable');

        if (!orders || orders.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No orders yet</td></tr>';
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
                <td>${formatCurrency(order.total_amount || 0)}</td>
                <td><span class="badge bg-${statusColors[order.status] || 'secondary'} text-capitalize">${order.status || 'pending'}</span></td>
                <td>${formatDate(order.created_at)}</td>
            </tr>
        `).join('');
    } catch (error) {
        console.error('Error loading orders:', error);
        document.getElementById('recentOrdersTable').innerHTML =
            '<tr><td colspan="5" class="text-center text-danger">Error loading orders</td></tr>';
    }
}

async function loadRecentProducts() {
    try {
        const response = await axios.get('/products');
        const products = (response.data.data || response.data).slice(0, 5);

        const tbody = document.getElementById('recentProductsTable');

        if (!products || products.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No products yet</td></tr>';
            return;
        }

        tbody.innerHTML = products.map(product => `
            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        <img src="${product.image || 'https://via.placeholder.com/40'}" class="rounded me-2" width="40" height="40" style="object-fit:cover;">
                        <span>${product.name}</span>
                    </div>
                </td>
                <td><span class="badge bg-secondary">${product.category || 'N/A'}</span></td>
                <td>${formatCurrency(product.price)}</td>
                <td>${product.stock || product.quantity || 0}</td>
                <td>
                    <a href="/admin/products?edit=${product.id}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil"></i>
                    </a>
                </td>
            </tr>
        `).join('');
    } catch (error) {
        console.error('Error loading products:', error);
        document.getElementById('recentProductsTable').innerHTML =
            '<tr><td colspan="5" class="text-center text-danger">Error loading products</td></tr>';
    }
}
</script>
@endsection
