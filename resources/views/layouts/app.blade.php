<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'E-Commerce Store')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        main {
            flex: 1;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }

        .product-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .product-img {
            height: 200px;
            object-fit: cover;
        }

        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
        }

        .category-card {
            transition: transform 0.2s;
            cursor: pointer;
        }

        .category-card:hover {
            transform: scale(1.05);
        }

        .cart-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            font-size: 0.7rem;
        }

        .rating {
            color: #ffc107;
        }

        .btn-add-cart {
            transition: all 0.3s;
        }

        .btn-add-cart:hover {
            transform: scale(1.05);
        }

        .footer {
            background-color: #212529;
            color: #fff;
            padding: 40px 0;
            margin-top: auto;
        }

        .loading-spinner {
            display: none;
        }

        .loading-spinner.active {
            display: inline-block;
        }

        .alert-floating {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 1050;
            min-width: 300px;
        }

        @yield('styles')
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="bi bi-shop"></i> ShopAPI
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/products">Products</a>
                    </li>
                    <li class="nav-item dropdown" id="categoriesDropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            Categories
                        </a>
                        <ul class="dropdown-menu" id="categoriesMenu">
                            <!-- Categories loaded dynamically -->
                        </ul>
                    </li>
                </ul>

                <!-- Search Form -->
                <form class="d-flex me-3" id="searchForm" action="/products" method="GET">
                    <input class="form-control me-2" type="search" name="q" placeholder="Search products..." id="searchInput">
                    <button class="btn btn-outline-light" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </form>

                <!-- User Menu -->
                <ul class="navbar-nav">
                    <!-- Cart -->
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="/cart" id="cartLink">
                            <i class="bi bi-cart3 fs-5"></i>
                            <span class="badge bg-danger cart-badge" id="cartCount" style="display: none;">0</span>
                        </a>
                    </li>

                    <!-- Guest Menu -->
                    <li class="nav-item" id="guestMenu">
                        <a class="nav-link" href="/login">
                            <i class="bi bi-person"></i> Login
                        </a>
                    </li>

                    <!-- User Menu (when logged in) -->
                    <li class="nav-item dropdown" id="userMenu" style="display: none;">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <span id="userName">Account</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/profile"><i class="bi bi-person"></i> Profile</a></li>
                            <li><a class="dropdown-item" href="/orders"><i class="bi bi-box"></i> My Orders</a></li>
                            <li><a class="dropdown-item" href="/addresses"><i class="bi bi-geo-alt"></i> Addresses</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="#" id="logoutBtn"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Alert Container -->
    <div id="alertContainer"></div>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5><i class="bi bi-shop"></i> ShopAPI</h5>
                    <p class="text-muted">Your one-stop shop for everything you need. Quality products, great prices.</p>
                </div>
                <div class="col-md-2 mb-4">
                    <h6>Shop</h6>
                    <ul class="list-unstyled">
                        <li><a href="/products" class="text-muted text-decoration-none">All Products</a></li>
                        <li><a href="/categories" class="text-muted text-decoration-none">Categories</a></li>
                    </ul>
                </div>
                <div class="col-md-2 mb-4">
                    <h6>Account</h6>
                    <ul class="list-unstyled">
                        <li><a href="/profile" class="text-muted text-decoration-none">My Account</a></li>
                        <li><a href="/orders" class="text-muted text-decoration-none">Orders</a></li>
                        <li><a href="/cart" class="text-muted text-decoration-none">Cart</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h6>Contact</h6>
                    <ul class="list-unstyled text-muted">
                        <li><i class="bi bi-envelope"></i> support@shopapi.com</li>
                        <li><i class="bi bi-phone"></i> +1 234 567 890</li>
                        <li><i class="bi bi-geo-alt"></i> 123 Shop Street, City</li>
                    </ul>
                </div>
            </div>
            <hr class="bg-secondary">
            <div class="text-center text-muted">
                <small>&copy; {{ date('Y') }} ShopAPI. All rights reserved.</small>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Axios -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <!-- API Configuration -->
    <script>
        const API_URL = '/api';

        // Configure Axios
        axios.defaults.baseURL = API_URL;
        axios.defaults.headers.common['Content-Type'] = 'application/json';
        axios.defaults.headers.common['Accept'] = 'application/json';

        // Add token to requests if available
        const token = localStorage.getItem('token');
        if (token) {
            axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        }

        // Response interceptor for handling auth errors
        axios.interceptors.response.use(
            response => response,
            error => {
                if (error.response && error.response.status === 401) {
                    localStorage.removeItem('token');
                    localStorage.removeItem('user');
                    if (!window.location.pathname.includes('/login')) {
                        window.location.href = '/login';
                    }
                }
                return Promise.reject(error);
            }
        );
    </script>

    <!-- Global JS Functions -->
    <script src="{{ asset('js/app.js') }}"></script>

    @yield('scripts')
</body>
</html>
