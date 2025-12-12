@extends('layouts.app')

@section('title', 'Home - E-Commerce Store')

@section('content')
<!-- Hero Section -->
<section class="hero-section text-center">
    <div class="container">
        <h1 class="display-4 fw-bold mb-4">Welcome to ShopAPI</h1>
        <p class="lead mb-4">Discover amazing products at unbeatable prices</p>
        <a href="/products" class="btn btn-light btn-lg px-5">
            <i class="bi bi-bag"></i> Shop Now
        </a>
    </div>
</section>

<!-- Featured Categories -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-4">Shop by Category</h2>
        <div class="row g-4" id="categoriesContainer">
            <!-- Categories loaded dynamically -->
            <div class="col-12 text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Featured Products</h2>
            <a href="/products" class="btn btn-outline-primary">View All <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="row g-4" id="featuredProducts">
            <!-- Products loaded dynamically -->
            <div class="col-12 text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-md-4">
                <div class="p-4">
                    <i class="bi bi-truck fs-1 text-primary mb-3"></i>
                    <h5>Free Shipping</h5>
                    <p class="text-muted">On orders over $50</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4">
                    <i class="bi bi-shield-check fs-1 text-primary mb-3"></i>
                    <h5>Secure Payment</h5>
                    <p class="text-muted">100% secure transactions</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4">
                    <i class="bi bi-arrow-repeat fs-1 text-primary mb-3"></i>
                    <h5>Easy Returns</h5>
                    <p class="text-muted">30-day return policy</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    loadCategories();
    loadFeaturedProducts();
});

async function loadCategories() {
    try {
        const response = await axios.get('/categories');
        const categories = response.data.data || response.data;
        const container = document.getElementById('categoriesContainer');

        if (categories.length === 0) {
            container.innerHTML = '<p class="text-muted text-center">No categories available</p>';
            return;
        }

        container.innerHTML = categories.slice(0, 4).map(category => `
            <div class="col-md-3 col-6">
                <a href="/products?category=${category.slug}" class="text-decoration-none">
                    <div class="card category-card h-100 text-center p-4">
                        <i class="bi bi-grid fs-1 text-primary mb-3"></i>
                        <h5 class="card-title">${category.name}</h5>
                        <p class="text-muted small">${category.description || 'Browse products'}</p>
                    </div>
                </a>
            </div>
        `).join('');
    } catch (error) {
        console.error('Error loading categories:', error);
        document.getElementById('categoriesContainer').innerHTML =
            '<p class="text-danger text-center">Error loading categories</p>';
    }
}

async function loadFeaturedProducts() {
    try {
        const response = await axios.get('/products');
        const products = response.data.data || response.data;
        const container = document.getElementById('featuredProducts');

        if (products.length === 0) {
            container.innerHTML = '<p class="text-muted text-center">No products available</p>';
            return;
        }

        container.innerHTML = products.slice(0, 8).map(product => `
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="card product-card h-100">
                    <img src="${product.image || 'https://via.placeholder.com/300x200?text=No+Image'}"
                         class="card-img-top product-img" alt="${product.name}">
                    <div class="card-body d-flex flex-column">
                        <span class="badge bg-secondary mb-2">${product.category || 'General'}</span>
                        <h6 class="card-title">${product.name}</h6>
                        <p class="text-primary fw-bold mt-auto">$${parseFloat(product.price).toFixed(2)}</p>
                        <div class="d-flex gap-2">
                            <a href="/products/${product.id}" class="btn btn-outline-primary btn-sm flex-grow-1">
                                <i class="bi bi-eye"></i> View
                            </a>
                            <button class="btn btn-primary btn-sm btn-add-cart" onclick="addToCart(${product.id})">
                                <i class="bi bi-cart-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    } catch (error) {
        console.error('Error loading products:', error);
        document.getElementById('featuredProducts').innerHTML =
            '<p class="text-danger text-center">Error loading products</p>';
    }
}
</script>
@endsection
