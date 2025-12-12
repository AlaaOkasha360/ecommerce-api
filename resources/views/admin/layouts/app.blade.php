<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - ShopAPI</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --sidebar-width: 250px;
            --header-height: 60px;
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --sidebar-bg: #212529;
        }

        body {
            min-height: 100vh;
            background-color: #f8f9fc;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, #212529 10%, #1a1d20 100%);
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .sidebar-brand {
            height: var(--header-height);
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            background: rgba(0,0,0,0.1);
        }

        .sidebar-brand a {
            color: #fff;
            font-size: 1.25rem;
            font-weight: 700;
            text-decoration: none;
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .nav-section {
            padding: 0.5rem 1.5rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            color: rgba(255,255,255,0.4);
            letter-spacing: 0.05rem;
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.2s;
        }

        .sidebar .nav-link:hover {
            color: #fff;
            background: rgba(255,255,255,0.1);
        }

        .sidebar .nav-link.active {
            color: #fff;
            background: var(--primary-color);
        }

        .sidebar .nav-link i {
            font-size: 1rem;
            width: 1.25rem;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        /* Top Header */
        .top-header {
            height: var(--header-height);
            background: #fff;
            border-bottom: 1px solid #e3e6f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        /* Content Area */
        .content-wrapper {
            padding: 1.5rem;
        }

        /* Cards */
        .stat-card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            border-left: 4px solid;
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
        }

        .stat-card.primary { border-left-color: #4e73df; }
        .stat-card.success { border-left-color: #1cc88a; }
        .stat-card.info { border-left-color: #36b9cc; }
        .stat-card.warning { border-left-color: #f6c23e; }
        .stat-card.danger { border-left-color: #e74a3b; }

        .stat-card .stat-icon {
            font-size: 2rem;
            opacity: 0.3;
        }

        /* Data Tables */
        .data-card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        .data-card .card-header {
            background: #fff;
            border-bottom: 1px solid #e3e6f0;
            padding: 1rem 1.25rem;
        }

        .table th {
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            color: var(--secondary-color);
        }

        /* Status Badges */
        .badge-status {
            padding: 0.4rem 0.8rem;
            font-weight: 500;
        }

        /* Alert Container */
        .alert-floating {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 1050;
            min-width: 300px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }
        }

        @yield('styles')
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <a href="/admin">
                <i class="bi bi-speedometer2"></i> Admin Panel
            </a>
        </div>

        <div class="sidebar-nav">
            <div class="nav-section">Main</div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('admin') ? 'active' : '' }}" href="/admin">
                        <i class="bi bi-grid-1x2"></i> Dashboard
                    </a>
                </li>
            </ul>

            <div class="nav-section mt-3">Management</div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('admin/products*') ? 'active' : '' }}" href="/admin/products">
                        <i class="bi bi-box-seam"></i> Products
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('admin/categories*') ? 'active' : '' }}" href="/admin/categories">
                        <i class="bi bi-tags"></i> Categories
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('admin/orders*') ? 'active' : '' }}" href="/admin/orders">
                        <i class="bi bi-receipt"></i> Orders
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('admin/users*') ? 'active' : '' }}" href="/admin/users">
                        <i class="bi bi-people"></i> Users
                    </a>
                </li>
            </ul>

            <div class="nav-section mt-3">Other</div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="/" target="_blank">
                        <i class="bi bi-shop"></i> View Store
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-danger" href="#" id="adminLogout">
                        <i class="bi bi-box-arrow-left"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Header -->
        <header class="top-header">
            <div class="d-flex align-items-center">
                <button class="btn btn-link text-dark d-md-none me-3" id="sidebarToggle">
                    <i class="bi bi-list fs-4"></i>
                </button>
                <h5 class="mb-0">@yield('page-title', 'Dashboard')</h5>
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle fs-4 me-2"></i>
                        <span id="adminName">Admin</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="/profile"><i class="bi bi-person me-2"></i>Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="#" id="headerLogout"><i class="bi bi-box-arrow-left me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Alert Container -->
        <div id="alertContainer"></div>

        <!-- Content -->
        <div class="content-wrapper">
            @yield('content')
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Axios -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <script>
        const API_URL = '/api';

        // Configure Axios
        axios.defaults.baseURL = API_URL;
        axios.defaults.headers.common['Content-Type'] = 'application/json';
        axios.defaults.headers.common['Accept'] = 'application/json';

        const token = localStorage.getItem('token');
        if (token) {
            axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        }

        // Response interceptor
        axios.interceptors.response.use(
            response => response,
            error => {
                if (error.response && (error.response.status === 401 || error.response.status === 403)) {
                    localStorage.removeItem('token');
                    localStorage.removeItem('user');
                    window.location.href = '/login';
                }
                return Promise.reject(error);
            }
        );

        // Check admin auth
        document.addEventListener('DOMContentLoaded', function() {
            const user = JSON.parse(localStorage.getItem('user') || 'null');
            if (!localStorage.getItem('token') || !user) {
                window.location.href = '/login';
                return;
            }

            // Check if user is admin
            if (user.role !== 'admin') {
                showAlert('Access denied. Admin privileges required.', 'danger');
                setTimeout(() => window.location.href = '/', 2000);
                return;
            }

            document.getElementById('adminName').textContent = user.first_name || 'Admin';

            // Logout handlers
            document.getElementById('adminLogout')?.addEventListener('click', handleLogout);
            document.getElementById('headerLogout')?.addEventListener('click', handleLogout);

            // Sidebar toggle
            document.getElementById('sidebarToggle')?.addEventListener('click', () => {
                document.getElementById('sidebar').classList.toggle('show');
            });
        });

        async function handleLogout(e) {
            e.preventDefault();
            try {
                await axios.post('/auth/logout');
            } catch (error) {}
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            window.location.href = '/login';
        }

        function showAlert(message, type = 'info', duration = 4000) {
            const container = document.getElementById('alertContainer');
            if (!container) return;

            const alertId = 'alert-' + Date.now();
            container.insertAdjacentHTML('beforeend', `
                <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show alert-floating" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `);

            setTimeout(() => {
                const alert = document.getElementById(alertId);
                if (alert) alert.remove();
            }, duration);
        }

        function formatCurrency(amount) {
            return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount);
        }

        function formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('en-US', {
                year: 'numeric', month: 'short', day: 'numeric'
            });
        }
    </script>

    @yield('scripts')
</body>
</html>
