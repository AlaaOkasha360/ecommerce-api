@extends('layouts.app')

@section('title', 'Shopping Cart - E-Commerce Store')

@section('styles')
<style>
    .cart-item-img {
        width: 100px;
        height: 100px;
        object-fit: cover;
    }

    .quantity-input {
        width: 60px;
        text-align: center;
    }

    .cart-summary {
        position: sticky;
        top: 100px;
    }

    .empty-cart {
        min-height: 400px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
</style>
@endsection

@section('content')
<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Home</a></li>
            <li class="breadcrumb-item active">Shopping Cart</li>
        </ol>
    </nav>

    <h2 class="mb-4"><i class="bi bi-cart3"></i> Shopping Cart</h2>

    <!-- Login Required Message -->
    <div id="loginRequired" class="text-center py-5" style="display: none;">
        <i class="bi bi-person-lock fs-1 text-muted"></i>
        <h4 class="mt-3">Please login to view your cart</h4>
        <p class="text-muted">You need to be logged in to access your shopping cart.</p>
        <a href="/login" class="btn btn-primary">
            <i class="bi bi-box-arrow-in-right"></i> Login
        </a>
        <a href="/register" class="btn btn-outline-primary ms-2">
            Create Account
        </a>
    </div>

    <!-- Cart Content -->
    <div id="cartContent" style="display: none;">
        <div class="row">
            <!-- Cart Items -->
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><strong id="itemCount">0</strong> items in your cart</span>
                        <button class="btn btn-outline-danger btn-sm" onclick="clearCart()" id="clearCartBtn">
                            <i class="bi bi-trash"></i> Clear Cart
                        </button>
                    </div>
                    <div class="card-body" id="cartItems">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cart Summary -->
            <div class="col-lg-4">
                <div class="card cart-summary">
                    <div class="card-header">
                        <strong>Order Summary</strong>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <span id="subtotal">$0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping</span>
                            <span id="shipping">Free</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax (10%)</span>
                            <span id="tax">$0.00</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Total</strong>
                            <strong class="text-primary fs-5" id="total">$0.00</strong>
                        </div>

                        <!-- Promo Code -->
                        <div class="mb-3">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Promo code" id="promoCode">
                                <button class="btn btn-outline-secondary" type="button" onclick="applyPromo()">Apply</button>
                            </div>
                        </div>

                        <button class="btn btn-primary w-100 btn-lg mb-2" onclick="proceedToCheckout()" id="checkoutBtn">
                            <i class="bi bi-credit-card"></i> Proceed to Checkout
                        </button>
                        <a href="/products" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-arrow-left"></i> Continue Shopping
                        </a>
                    </div>
                </div>

                <!-- Security Badge -->
                <div class="card mt-3">
                    <div class="card-body text-center">
                        <i class="bi bi-shield-check text-success fs-4"></i>
                        <p class="small text-muted mb-0">Secure Checkout - SSL Encrypted</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Empty Cart -->
    <div id="emptyCart" class="empty-cart" style="display: none;">
        <i class="bi bi-cart-x fs-1 text-muted"></i>
        <h4 class="mt-3">Your cart is empty</h4>
        <p class="text-muted">Looks like you haven't added any items to your cart yet.</p>
        <a href="/products" class="btn btn-primary">
            <i class="bi bi-bag"></i> Start Shopping
        </a>
    </div>
</div>

<!-- Checkout Modal -->
<div class="modal fade" id="checkoutModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Checkout - Select Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="addressLoadingSpinner" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>

                <div id="noAddressesMessage" style="display: none;" class="text-center py-4">
                    <i class="bi bi-geo-alt fs-1 text-muted"></i>
                    <p class="text-muted mt-3">You don't have any saved addresses.</p>
                    <a href="/addresses" class="btn btn-primary">Add Address</a>
                </div>

                <form id="checkoutForm" style="display: none;">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Shipping Address</label>
                            <select class="form-select" id="shippingAddressSelect" required>
                                <option value="">Select shipping address</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Billing Address</label>
                            <select class="form-select" id="billingAddressSelect" required>
                                <option value="">Select billing address</option>
                            </select>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="sameAsShipping" checked onchange="toggleBillingAddress()">
                                <label class="form-check-label" for="sameAsShipping">Same as shipping address</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Payment Method</label>
                        <select class="form-select" id="paymentMethod" required>
                            <option value="stripe">Credit/Debit Card (Stripe)</option>
                            <option value="paypal">PayPal</option>
                            <option value="cod">Cash on Delivery</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Order Notes (Optional)</label>
                        <textarea class="form-control" id="orderNotes" rows="2" placeholder="Any special instructions..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="placeOrderBtn" onclick="placeOrder()">
                    <i class="bi bi-credit-card"></i> Place Order & Pay
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let cartData = null;

document.addEventListener('DOMContentLoaded', function() {
    if (!localStorage.getItem('token')) {
        document.getElementById('loginRequired').style.display = 'block';
    } else {
        document.getElementById('cartContent').style.display = 'block';
        loadCart();
    }
});

async function loadCart() {
    try {
        const response = await axios.get('/cart');
        cartData = response.data.data || response.data;
        displayCart(cartData);
    } catch (error) {
        console.error('Error loading cart:', error);
        if (error.response && error.response.status === 401) {
            document.getElementById('cartContent').style.display = 'none';
            document.getElementById('loginRequired').style.display = 'block';
        }
    }
}

function displayCart(cart) {
    const items = cart.items || [];
    const container = document.getElementById('cartItems');

    if (items.length === 0) {
        document.getElementById('cartContent').style.display = 'none';
        document.getElementById('emptyCart').style.display = 'flex';
        updateCartBadge(0);
        return;
    }

    document.getElementById('itemCount').textContent = items.length;

    container.innerHTML = items.map(item => `
        <div class="cart-item border-bottom py-3" id="cartItem-${item.id}">
            <div class="row align-items-center">
                <div class="col-auto">
                    <img src="https://via.placeholder.com/100x100?text=Product"
                         class="cart-item-img rounded" alt="${item.product_name}">
                </div>
                <div class="col">
                    <h6 class="mb-1">${item.product_name}</h6>
                    <p class="text-muted small mb-1">Product ID: ${item.product_id}</p>
                    <p class="text-primary fw-bold mb-0">$${parseFloat(item.price).toFixed(2)}</p>
                </div>
                <div class="col-auto">
                    <div class="input-group input-group-sm" style="width: 120px;">
                        <button class="btn btn-outline-secondary" type="button" onclick="updateQuantity(${item.id}, ${item.quantity - 1})">
                            <i class="bi bi-dash"></i>
                        </button>
                        <input type="text" class="form-control text-center" value="${item.quantity}" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="updateQuantity(${item.id}, ${item.quantity + 1})">
                            <i class="bi bi-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="col-auto text-end" style="min-width: 100px;">
                    <p class="fw-bold mb-1">$${parseFloat(item.subtotal).toFixed(2)}</p>
                    <button class="btn btn-outline-danger btn-sm" onclick="removeItem(${item.id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `).join('');

    updateSummary(cart.total);
    updateCartBadge(items.length);
}

function updateSummary(subtotal) {
    const subtotalVal = parseFloat(subtotal) || 0;
    const tax = subtotalVal * 0.1;
    const shipping = subtotalVal > 50 ? 0 : 5.99;
    const total = subtotalVal + tax + shipping;

    document.getElementById('subtotal').textContent = `$${subtotalVal.toFixed(2)}`;
    document.getElementById('tax').textContent = `$${tax.toFixed(2)}`;
    document.getElementById('shipping').textContent = shipping === 0 ? 'Free' : `$${shipping.toFixed(2)}`;
    document.getElementById('total').textContent = `$${total.toFixed(2)}`;
}

async function updateQuantity(itemId, newQuantity) {
    if (newQuantity < 1) {
        removeItem(itemId);
        return;
    }

    try {
        await axios.put(`/cart/items/${itemId}`, { quantity: newQuantity });
        loadCart();
        showAlert('Cart updated', 'success');
    } catch (error) {
        console.error('Error updating quantity:', error);
        showAlert('Failed to update cart', 'danger');
    }
}

async function removeItem(itemId) {
    try {
        await axios.delete(`/cart/items/${itemId}`);
        loadCart();
        showAlert('Item removed from cart', 'success');
    } catch (error) {
        console.error('Error removing item:', error);
        showAlert('Failed to remove item', 'danger');
    }
}

async function clearCart() {
    if (!confirm('Are you sure you want to clear your cart?')) return;

    try {
        await axios.delete('/cart');
        loadCart();
        showAlert('Cart cleared', 'success');
    } catch (error) {
        console.error('Error clearing cart:', error);
        showAlert('Failed to clear cart', 'danger');
    }
}

function applyPromo() {
    const code = document.getElementById('promoCode').value;
    if (!code) {
        showAlert('Please enter a promo code', 'warning');
        return;
    }
    // For demo purposes
    showAlert('Invalid promo code', 'danger');
}

let checkoutModal;
let userAddresses = [];

async function proceedToCheckout() {
    // Initialize modal if not exists
    if (!checkoutModal) {
        checkoutModal = new bootstrap.Modal(document.getElementById('checkoutModal'));
    }

    // Show modal with loading state
    document.getElementById('addressLoadingSpinner').style.display = 'block';
    document.getElementById('noAddressesMessage').style.display = 'none';
    document.getElementById('checkoutForm').style.display = 'none';
    checkoutModal.show();

    try {
        // Load user addresses
        const response = await axios.get('/users/addresses');
        userAddresses = response.data.data || response.data;

        document.getElementById('addressLoadingSpinner').style.display = 'none';

        if (!userAddresses || userAddresses.length === 0) {
            document.getElementById('noAddressesMessage').style.display = 'block';
            return;
        }

        // Populate address dropdowns
        const shippingSelect = document.getElementById('shippingAddressSelect');
        const billingSelect = document.getElementById('billingAddressSelect');

        const addressOptions = userAddresses.map(addr =>
            `<option value="${addr.id}" ${addr.is_default ? 'selected' : ''}>
                ${addr.street_address}, ${addr.city}, ${addr.state} ${addr.postal_code}
            </option>`
        ).join('');

        shippingSelect.innerHTML = '<option value="">Select shipping address</option>' + addressOptions;
        billingSelect.innerHTML = '<option value="">Select billing address</option>' + addressOptions;

        // Set default selections
        const defaultAddr = userAddresses.find(a => a.is_default) || userAddresses[0];
        if (defaultAddr) {
            shippingSelect.value = defaultAddr.id;
            billingSelect.value = defaultAddr.id;
        }

        document.getElementById('checkoutForm').style.display = 'block';
        toggleBillingAddress();

    } catch (error) {
        console.error('Error loading addresses:', error);
        document.getElementById('addressLoadingSpinner').style.display = 'none';
        showAlert('Failed to load addresses. Please try again.', 'danger');
        checkoutModal.hide();
    }
}

function toggleBillingAddress() {
    const sameAsShipping = document.getElementById('sameAsShipping').checked;
    const billingSelect = document.getElementById('billingAddressSelect');
    const shippingSelect = document.getElementById('shippingAddressSelect');

    if (sameAsShipping) {
        billingSelect.value = shippingSelect.value;
        billingSelect.disabled = true;
    } else {
        billingSelect.disabled = false;
    }
}

// Update billing when shipping changes if "same as shipping" is checked
document.addEventListener('DOMContentLoaded', function() {
    const shippingSelect = document.getElementById('shippingAddressSelect');
    if (shippingSelect) {
        shippingSelect.addEventListener('change', function() {
            if (document.getElementById('sameAsShipping').checked) {
                document.getElementById('billingAddressSelect').value = this.value;
            }
        });
    }
});

async function placeOrder() {
    const shippingAddressId = document.getElementById('shippingAddressSelect').value;
    const billingAddressId = document.getElementById('billingAddressSelect').value;
    const paymentMethod = document.getElementById('paymentMethod').value;
    const notes = document.getElementById('orderNotes').value;

    if (!shippingAddressId || !billingAddressId) {
        showAlert('Please select shipping and billing addresses', 'warning');
        return;
    }

    const placeOrderBtn = document.getElementById('placeOrderBtn');
    placeOrderBtn.disabled = true;
    placeOrderBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

    try {
        // Step 1: Create the order
        const orderResponse = await axios.post('/orders', {
            shipping_address_id: parseInt(shippingAddressId),
            billing_address_id: parseInt(billingAddressId),
            payment_method: paymentMethod,
            notes: notes
        });

        const orderData = orderResponse.data.data || orderResponse.data;
        const orderId = orderData.order?.id || orderData.id;

        if (!orderId) {
            throw new Error('Order ID not returned');
        }

        // Step 2: Create checkout session
        const checkoutResponse = await axios.post('/payments/create-checkout', {
            order_id: orderId
        });

        const checkoutData = checkoutResponse.data.data || checkoutResponse.data;
        const checkoutUrl = checkoutData.checkout_url || checkoutData.url;

        if (checkoutUrl) {
            showAlert('Redirecting to payment...', 'success');
            window.location.href = checkoutUrl;
        } else {
            showAlert('Order created! Redirecting to orders...', 'success');
            setTimeout(() => window.location.href = '/orders', 1500);
        }

    } catch (error) {
        console.error('Error during checkout:', error);

        let message = 'Failed to complete checkout. Please try again.';
        if (error.response?.data?.message) {
            message = error.response.data.message;
        }

        showAlert(message, 'danger');

        placeOrderBtn.disabled = false;
        placeOrderBtn.innerHTML = '<i class="bi bi-credit-card"></i> Place Order & Pay';
    }
}
</script>
@endsection
