@extends('layouts.app')

@section('title', 'Product Details - E-Commerce Store')

@section('styles')
<style>
    .product-main-image {
        max-height: 500px;
        object-fit: contain;
    }

    .product-thumbnails img {
        width: 80px;
        height: 80px;
        object-fit: cover;
        cursor: pointer;
        border: 2px solid transparent;
        transition: border-color 0.3s;
    }

    .product-thumbnails img:hover,
    .product-thumbnails img.active {
        border-color: var(--primary-color);
    }

    .quantity-selector {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .quantity-selector button {
        width: 40px;
        height: 40px;
    }

    .quantity-selector input {
        width: 60px;
        text-align: center;
    }

    .review-card {
        border-left: 4px solid var(--primary-color);
    }

    .star-rating {
        color: #ffc107;
    }

    .star-rating-input {
        direction: rtl;
        display: inline-flex;
    }

    .star-rating-input input {
        display: none;
    }

    .star-rating-input label {
        cursor: pointer;
        font-size: 1.5rem;
        color: #ddd;
        transition: color 0.2s;
    }

    .star-rating-input label:hover,
    .star-rating-input label:hover ~ label,
    .star-rating-input input:checked ~ label {
        color: #ffc107;
    }
</style>
@endsection

@section('content')
<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Home</a></li>
            <li class="breadcrumb-item"><a href="/products">Products</a></li>
            <li class="breadcrumb-item active" id="productBreadcrumb">Loading...</li>
        </ol>
    </nav>

    <!-- Product Details -->
    <div class="row g-5" id="productContainer">
        <div class="col-12 text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>

    <!-- Reviews Section -->
    <section class="mt-5">
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" id="reviewTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="reviews-tab" data-bs-toggle="tab" href="#reviews" role="tab">
                            <i class="bi bi-chat-left-text"></i> Reviews <span id="reviewCount" class="badge bg-primary">0</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="write-review-tab" data-bs-toggle="tab" href="#write-review" role="tab">
                            <i class="bi bi-pencil"></i> Write a Review
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="reviewTabContent">
                    <!-- Reviews List -->
                    <div class="tab-pane fade show active" id="reviews" role="tabpanel">
                        <div id="reviewsContainer">
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading reviews...</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Write Review Form -->
                    <div class="tab-pane fade" id="write-review" role="tabpanel">
                        <div id="reviewFormContainer">
                            <div class="alert alert-info" id="loginToReview">
                                Please <a href="/login">login</a> to write a review.
                            </div>
                            <form id="reviewForm" style="display: none;">
                                <div class="mb-3">
                                    <label class="form-label">Rating</label>
                                    <div class="star-rating-input">
                                        <input type="radio" name="rating" value="5" id="star5">
                                        <label for="star5"><i class="bi bi-star-fill"></i></label>
                                        <input type="radio" name="rating" value="4" id="star4">
                                        <label for="star4"><i class="bi bi-star-fill"></i></label>
                                        <input type="radio" name="rating" value="3" id="star3">
                                        <label for="star3"><i class="bi bi-star-fill"></i></label>
                                        <input type="radio" name="rating" value="2" id="star2">
                                        <label for="star2"><i class="bi bi-star-fill"></i></label>
                                        <input type="radio" name="rating" value="1" id="star1">
                                        <label for="star1"><i class="bi bi-star-fill"></i></label>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="reviewTitle" class="form-label">Review Title</label>
                                    <input type="text" class="form-control" id="reviewTitle" required>
                                </div>
                                <div class="mb-3">
                                    <label for="reviewComment" class="form-label">Your Review</label>
                                    <textarea class="form-control" id="reviewComment" rows="4" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send"></i> Submit Review
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Related Products -->
    <section class="mt-5">
        <h4 class="mb-4">Related Products</h4>
        <div class="row g-4" id="relatedProducts">
            <div class="col-12 text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('scripts')
<script>
let productId = null;
let quantity = 1;

document.addEventListener('DOMContentLoaded', function() {
    // Get product ID from URL
    const pathParts = window.location.pathname.split('/');
    productId = pathParts[pathParts.length - 1];

    loadProduct();
    loadReviews();
    loadRelatedProducts();

    // Check if user is logged in for review form
    if (localStorage.getItem('token')) {
        document.getElementById('loginToReview').style.display = 'none';
        document.getElementById('reviewForm').style.display = 'block';
    }

    // Review form submission
    document.getElementById('reviewForm').addEventListener('submit', submitReview);
});

async function loadProduct() {
    try {
        const response = await axios.get(`/products/${productId}`);
        const product = response.data.data || response.data;

        document.getElementById('productBreadcrumb').textContent = product.name;
        document.title = `${product.name} - E-Commerce Store`;

        document.getElementById('productContainer').innerHTML = `
            <div class="col-lg-6">
                <div class="text-center">
                    <img src="${product.image || 'https://via.placeholder.com/500x500?text=No+Image'}"
                         class="img-fluid product-main-image rounded" alt="${product.name}" id="mainImage">
                </div>
            </div>
            <div class="col-lg-6">
                <span class="badge bg-primary mb-3">${product.category || 'General'}</span>
                <h1 class="mb-3">${product.name}</h1>

                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="star-rating">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-half"></i>
                    </div>
                    <span class="text-muted">(4.5 stars)</span>
                    <a href="#reviews" class="text-decoration-none">See reviews</a>
                </div>

                <div class="mb-4">
                    <span class="fs-2 fw-bold text-primary">$${parseFloat(product.price).toFixed(2)}</span>
                    ${product.compare_price ? `<span class="text-muted text-decoration-line-through ms-2">$${parseFloat(product.compare_price).toFixed(2)}</span>` : ''}
                </div>

                <p class="text-muted mb-4">${product.description || 'No description available for this product.'}</p>

                <div class="mb-4">
                    <span class="badge ${product.stock > 0 || product.quantity > 0 ? 'bg-success' : 'bg-danger'}">
                        ${product.stock > 0 || product.quantity > 0 ? 'In Stock' : 'Out of Stock'}
                    </span>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Quantity</label>
                    <div class="quantity-selector">
                        <button class="btn btn-outline-secondary" onclick="changeQuantity(-1)">
                            <i class="bi bi-dash"></i>
                        </button>
                        <input type="number" class="form-control" id="quantityInput" value="1" min="1" max="99">
                        <button class="btn btn-outline-secondary" onclick="changeQuantity(1)">
                            <i class="bi bi-plus"></i>
                        </button>
                    </div>
                </div>

                <div class="d-flex gap-3 mb-4">
                    <button class="btn btn-primary btn-lg flex-grow-1" onclick="addToCartWithQuantity()">
                        <i class="bi bi-cart-plus"></i> Add to Cart
                    </button>
                    <button class="btn btn-outline-danger btn-lg">
                        <i class="bi bi-heart"></i>
                    </button>
                </div>

                <div class="border-top pt-4">
                    <div class="row text-muted small">
                        <div class="col-6 mb-2">
                            <i class="bi bi-truck"></i> Free shipping over $50
                        </div>
                        <div class="col-6 mb-2">
                            <i class="bi bi-arrow-repeat"></i> 30-day returns
                        </div>
                        <div class="col-6 mb-2">
                            <i class="bi bi-shield-check"></i> Secure checkout
                        </div>
                        <div class="col-6 mb-2">
                            <i class="bi bi-headset"></i> 24/7 support
                        </div>
                    </div>
                </div>
            </div>
        `;
    } catch (error) {
        console.error('Error loading product:', error);
        document.getElementById('productContainer').innerHTML = `
            <div class="col-12 text-center py-5">
                <i class="bi bi-exclamation-circle text-danger fs-1"></i>
                <h4 class="mt-3">Product not found</h4>
                <p class="text-muted">The product you're looking for doesn't exist or has been removed.</p>
                <a href="/products" class="btn btn-primary">Browse Products</a>
            </div>
        `;
    }
}

async function loadReviews() {
    try {
        const response = await axios.get(`/products/${productId}/reviews`);
        const reviews = response.data.data || response.data;

        document.getElementById('reviewCount').textContent = reviews.length;

        const container = document.getElementById('reviewsContainer');

        if (reviews.length === 0) {
            container.innerHTML = `
                <div class="text-center py-4">
                    <i class="bi bi-chat-left-text fs-1 text-muted"></i>
                    <p class="text-muted mt-3">No reviews yet. Be the first to review this product!</p>
                </div>
            `;
            return;
        }

        container.innerHTML = reviews.map(review => `
            <div class="card review-card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1">${review.title}</h6>
                            <div class="star-rating mb-2">
                                ${generateStars(review.rating)}
                            </div>
                        </div>
                        ${review.is_verified_purchase ? '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Verified Purchase</span>' : ''}
                    </div>
                    <p class="text-muted mb-0">${review.comment}</p>
                </div>
            </div>
        `).join('');
    } catch (error) {
        console.error('Error loading reviews:', error);
        document.getElementById('reviewsContainer').innerHTML =
            '<p class="text-muted">Unable to load reviews.</p>';
    }
}

function generateStars(rating) {
    let stars = '';
    for (let i = 1; i <= 5; i++) {
        if (i <= rating) {
            stars += '<i class="bi bi-star-fill"></i>';
        } else {
            stars += '<i class="bi bi-star"></i>';
        }
    }
    return stars;
}

async function loadRelatedProducts() {
    try {
        const response = await axios.get('/products');
        const products = (response.data.data || response.data).filter(p => p.id != productId).slice(0, 4);

        const container = document.getElementById('relatedProducts');

        if (products.length === 0) {
            container.innerHTML = '<p class="text-muted">No related products found.</p>';
            return;
        }

        container.innerHTML = products.map(product => `
            <div class="col-lg-3 col-md-6">
                <div class="card product-card h-100">
                    <img src="${product.image || 'https://via.placeholder.com/300x200?text=No+Image'}"
                         class="card-img-top product-img" alt="${product.name}">
                    <div class="card-body">
                        <h6 class="card-title">${product.name}</h6>
                        <p class="text-primary fw-bold">$${parseFloat(product.price).toFixed(2)}</p>
                        <a href="/products/${product.id}" class="btn btn-outline-primary btn-sm w-100">View Details</a>
                    </div>
                </div>
            </div>
        `).join('');
    } catch (error) {
        console.error('Error loading related products:', error);
    }
}

function changeQuantity(delta) {
    const input = document.getElementById('quantityInput');
    let value = parseInt(input.value) + delta;
    if (value < 1) value = 1;
    if (value > 99) value = 99;
    input.value = value;
    quantity = value;
}

function addToCartWithQuantity() {
    const qty = parseInt(document.getElementById('quantityInput').value);
    addToCart(productId, qty);
}

async function submitReview(e) {
    e.preventDefault();

    const rating = document.querySelector('input[name="rating"]:checked');
    if (!rating) {
        showAlert('Please select a rating', 'warning');
        return;
    }

    const data = {
        rating: parseInt(rating.value),
        title: document.getElementById('reviewTitle').value,
        comment: document.getElementById('reviewComment').value
    };

    try {
        await axios.post(`/products/${productId}/reviews`, data);
        showAlert('Review submitted successfully!', 'success');
        document.getElementById('reviewForm').reset();
        loadReviews();

        // Switch to reviews tab
        const reviewsTab = new bootstrap.Tab(document.getElementById('reviews-tab'));
        reviewsTab.show();
    } catch (error) {
        console.error('Error submitting review:', error);
        showAlert('Failed to submit review. Please try again.', 'danger');
    }
}
</script>
@endsection
