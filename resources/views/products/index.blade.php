@extends('layouts.app')

@section('title', 'Products - E-Commerce Store')

@section('styles')
<style>
    .filter-sidebar {
        position: sticky;
        top: 80px;
    }

    .price-range {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .pagination-container {
        display: flex;
        justify-content: center;
        margin-top: 30px;
    }
</style>
@endsection

@section('content')
<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Home</a></li>
            <li class="breadcrumb-item active">Products</li>
        </ol>
    </nav>

    <div class="row">
        <!-- Filters Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="filter-sidebar">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-funnel"></i> Filters
                    </div>
                    <div class="card-body">
                        <!-- Categories Filter -->
                        <h6 class="fw-bold mb-3">Categories</h6>
                        <div id="categoryFilters" class="mb-4">
                            <div class="spinner-border spinner-border-sm" role="status"></div>
                        </div>

                        <!-- Price Range -->
                        <h6 class="fw-bold mb-3">Price Range</h6>
                        <div class="price-range mb-4">
                            <input type="number" class="form-control form-control-sm" id="minPrice" placeholder="Min">
                            <span>-</span>
                            <input type="number" class="form-control form-control-sm" id="maxPrice" placeholder="Max">
                        </div>

                        <!-- Sort By -->
                        <h6 class="fw-bold mb-3">Sort By</h6>
                        <select class="form-select mb-4" id="sortBy">
                            <option value="">Default</option>
                            <option value="price_asc">Price: Low to High</option>
                            <option value="price_desc">Price: High to Low</option>
                            <option value="name_asc">Name: A-Z</option>
                            <option value="name_desc">Name: Z-A</option>
                        </select>

                        <button class="btn btn-primary w-100" onclick="applyFilters()">
                            <i class="bi bi-filter"></i> Apply Filters
                        </button>
                        <button class="btn btn-outline-secondary w-100 mt-2" onclick="clearFilters()">
                            Clear All
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="col-lg-9">
            <!-- Results Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-0">Products</h4>
                    <small class="text-muted" id="resultsCount">Loading...</small>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary btn-sm" onclick="setView('grid')" id="gridViewBtn">
                        <i class="bi bi-grid-3x3-gap"></i>
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" onclick="setView('list')" id="listViewBtn">
                        <i class="bi bi-list"></i>
                    </button>
                </div>
            </div>

            <!-- Search Result Info -->
            <div id="searchInfo" class="alert alert-info" style="display: none;">
                Showing results for: <strong id="searchTerm"></strong>
                <button class="btn-close float-end" onclick="clearSearch()"></button>
            </div>

            <!-- Products Container -->
            <div class="row g-4" id="productsContainer">
                <div class="col-12 text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="pagination-container" id="paginationContainer">
                <!-- Pagination loaded dynamically -->
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let currentPage = 1;
let currentView = 'grid';
let currentCategory = null;

document.addEventListener('DOMContentLoaded', function() {
    // Check for URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const search = urlParams.get('q');
    const category = urlParams.get('category');

    if (search) {
        document.getElementById('searchInput').value = search;
        document.getElementById('searchInfo').style.display = 'block';
        document.getElementById('searchTerm').textContent = search;
    }

    if (category) {
        currentCategory = category;
    }

    loadCategoryFilters();
    loadProducts();
});

async function loadCategoryFilters() {
    try {
        const response = await axios.get('/categories');
        const categories = response.data.data || response.data;
        const container = document.getElementById('categoryFilters');

        container.innerHTML = `
            <div class="form-check">
                <input class="form-check-input category-filter" type="radio" name="category" value="" id="cat-all" checked>
                <label class="form-check-label" for="cat-all">All Categories</label>
            </div>
            ${categories.map(cat => `
                <div class="form-check">
                    <input class="form-check-input category-filter" type="radio" name="category"
                           value="${cat.slug}" id="cat-${cat.id}" ${currentCategory === cat.slug ? 'checked' : ''}>
                    <label class="form-check-label" for="cat-${cat.id}">${cat.name}</label>
                </div>
            `).join('')}
        `;
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

async function loadProducts(page = 1) {
    currentPage = page;
    const container = document.getElementById('productsContainer');

    container.innerHTML = `
        <div class="col-12 text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;

    try {
        const urlParams = new URLSearchParams(window.location.search);
        const search = urlParams.get('q');
        const category = document.querySelector('.category-filter:checked')?.value || currentCategory;

        let url = '/products';
        let params = { page };

        if (search) {
            url = '/products/search';
            params.q = search;
        } else if (category) {
            url = `/products/category/${category}`;
        }

        const response = await axios.get(url, { params });
        let products, pagination;

        if (response.data.data && response.data.data.products) {
            // Category response
            products = response.data.data.products;
            pagination = null;
        } else {
            products = response.data.data || response.data;
            pagination = response.data.meta || response.data;
        }

        displayProducts(products);

        if (pagination && pagination.last_page > 1) {
            displayPagination(pagination);
        } else {
            document.getElementById('paginationContainer').innerHTML = '';
        }

        document.getElementById('resultsCount').textContent =
            `Showing ${products.length} product${products.length !== 1 ? 's' : ''}`;

    } catch (error) {
        console.error('Error loading products:', error);
        container.innerHTML = `
            <div class="col-12 text-center py-5">
                <i class="bi bi-exclamation-circle text-danger fs-1"></i>
                <p class="text-muted mt-3">Error loading products. Please try again.</p>
                <button class="btn btn-primary" onclick="loadProducts()">Retry</button>
            </div>
        `;
    }
}

function displayProducts(products) {
    const container = document.getElementById('productsContainer');

    if (products.length === 0) {
        container.innerHTML = `
            <div class="col-12 text-center py-5">
                <i class="bi bi-search fs-1 text-muted"></i>
                <h5 class="mt-3">No products found</h5>
                <p class="text-muted">Try adjusting your search or filters</p>
            </div>
        `;
        return;
    }

    if (currentView === 'grid') {
        container.innerHTML = products.map(product => `
            <div class="col-lg-4 col-md-6">
                <div class="card product-card h-100">
                    <img src="${product.image || 'https://via.placeholder.com/300x200?text=No+Image'}"
                         class="card-img-top product-img" alt="${product.name}">
                    <div class="card-body d-flex flex-column">
                        <span class="badge bg-secondary mb-2">${product.category || 'General'}</span>
                        <h5 class="card-title">${product.name}</h5>
                        <div class="rating mb-2">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-half"></i>
                            <small class="text-muted">(4.5)</small>
                        </div>
                        <p class="text-primary fw-bold fs-5 mt-auto">$${parseFloat(product.price).toFixed(2)}</p>
                        <div class="d-flex gap-2">
                            <a href="/products/${product.id}" class="btn btn-outline-primary flex-grow-1">
                                View Details
                            </a>
                            <button class="btn btn-primary btn-add-cart" onclick="addToCart(${product.id})">
                                <i class="bi bi-cart-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    } else {
        container.innerHTML = products.map(product => `
            <div class="col-12">
                <div class="card product-card mb-3">
                    <div class="row g-0">
                        <div class="col-md-3">
                            <img src="${product.image || 'https://via.placeholder.com/300x200?text=No+Image'}"
                                 class="img-fluid rounded-start h-100" style="object-fit: cover;" alt="${product.name}">
                        </div>
                        <div class="col-md-9">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <span class="badge bg-secondary mb-2">${product.category || 'General'}</span>
                                        <h5 class="card-title">${product.name}</h5>
                                        <div class="rating mb-2">
                                            <i class="bi bi-star-fill"></i>
                                            <i class="bi bi-star-fill"></i>
                                            <i class="bi bi-star-fill"></i>
                                            <i class="bi bi-star-fill"></i>
                                            <i class="bi bi-star-half"></i>
                                            <small class="text-muted">(4.5)</small>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <p class="text-primary fw-bold fs-4 mb-0">$${parseFloat(product.price).toFixed(2)}</p>
                                    </div>
                                </div>
                                <p class="card-text text-muted">${product.description || 'No description available'}</p>
                                <div class="d-flex gap-2">
                                    <a href="/products/${product.id}" class="btn btn-outline-primary">View Details</a>
                                    <button class="btn btn-primary btn-add-cart" onclick="addToCart(${product.id})">
                                        <i class="bi bi-cart-plus"></i> Add to Cart
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    }
}

function displayPagination(pagination) {
    const container = document.getElementById('paginationContainer');
    const currentPage = pagination.current_page;
    const lastPage = pagination.last_page;

    let html = '<nav><ul class="pagination">';

    // Previous button
    html += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadProducts(${currentPage - 1}); return false;">Previous</a>
        </li>
    `;

    // Page numbers
    for (let i = 1; i <= lastPage; i++) {
        if (i === 1 || i === lastPage || (i >= currentPage - 2 && i <= currentPage + 2)) {
            html += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="loadProducts(${i}); return false;">${i}</a>
                </li>
            `;
        } else if (i === currentPage - 3 || i === currentPage + 3) {
            html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }

    // Next button
    html += `
        <li class="page-item ${currentPage === lastPage ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadProducts(${currentPage + 1}); return false;">Next</a>
        </li>
    `;

    html += '</ul></nav>';
    container.innerHTML = html;
}

function setView(view) {
    currentView = view;
    document.getElementById('gridViewBtn').classList.toggle('active', view === 'grid');
    document.getElementById('listViewBtn').classList.toggle('active', view === 'list');
    loadProducts(currentPage);
}

function applyFilters() {
    const category = document.querySelector('.category-filter:checked')?.value;
    if (category) {
        currentCategory = category;
    } else {
        currentCategory = null;
    }
    loadProducts(1);
}

function clearFilters() {
    document.querySelector('.category-filter[value=""]').checked = true;
    document.getElementById('minPrice').value = '';
    document.getElementById('maxPrice').value = '';
    document.getElementById('sortBy').value = '';
    currentCategory = null;
    window.history.pushState({}, '', '/products');
    loadProducts(1);
}

function clearSearch() {
    document.getElementById('searchInput').value = '';
    document.getElementById('searchInfo').style.display = 'none';
    window.history.pushState({}, '', '/products');
    loadProducts(1);
}
</script>
@endsection
