/**
 * E-Commerce Frontend JavaScript
 * Global functions and utilities
 */

// DOM Ready
document.addEventListener('DOMContentLoaded', function () {
    initializeApp();
});

/**
 * Initialize the application
 */
function initializeApp() {
    checkAuthStatus();
    loadCategoriesNav();
    updateCartBadge();
}

/**
 * Check authentication status and update UI
 */
function checkAuthStatus() {
    const token = localStorage.getItem('token');
    const user = JSON.parse(localStorage.getItem('user') || 'null');

    const guestMenu = document.getElementById('guestMenu');
    const userMenu = document.getElementById('userMenu');
    const userName = document.getElementById('userName');

    if (token && user) {
        if (guestMenu) guestMenu.style.display = 'none';
        if (userMenu) userMenu.style.display = 'block';
        if (userName) userName.textContent = user.first_name || 'Account';

        // Setup logout button
        const logoutBtn = document.getElementById('logoutBtn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', handleLogout);
        }
    } else {
        if (guestMenu) guestMenu.style.display = 'block';
        if (userMenu) userMenu.style.display = 'none';
    }
}

/**
 * Handle user logout
 */
async function handleLogout(e) {
    e.preventDefault();

    try {
        await axios.post('/auth/logout');
    } catch (error) {
        console.error('Logout error:', error);
    }

    // Clear local storage
    localStorage.removeItem('token');
    localStorage.removeItem('user');

    // Clear axios header
    delete axios.defaults.headers.common['Authorization'];

    showAlert('Logged out successfully', 'success');

    // Redirect to home
    setTimeout(() => {
        window.location.href = '/';
    }, 500);
}

/**
 * Load categories for navigation dropdown
 */
async function loadCategoriesNav() {
    try {
        const response = await axios.get('/categories');
        const categories = response.data.data || response.data;
        const menu = document.getElementById('categoriesMenu');

        if (menu && categories.length > 0) {
            menu.innerHTML = categories.map(cat => `
                <li><a class="dropdown-item" href="/products?category=${cat.slug}">${cat.name}</a></li>
            `).join('') + `
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="/products">All Products</a></li>
            `;
        }
    } catch (error) {
        console.error('Error loading categories for nav:', error);
    }
}

/**
 * Update cart badge count
 */
async function updateCartBadge(count) {
    const badge = document.getElementById('cartCount');
    if (!badge) return;

    if (count !== undefined) {
        badge.textContent = count;
        badge.style.display = count > 0 ? 'block' : 'none';
        return;
    }

    // Only fetch if logged in
    if (!localStorage.getItem('token')) {
        badge.style.display = 'none';
        return;
    }

    try {
        const response = await axios.get('/cart');
        const cart = response.data.data || response.data;
        const items = cart.items || [];

        badge.textContent = items.length;
        badge.style.display = items.length > 0 ? 'block' : 'none';
    } catch (error) {
        console.error('Error updating cart badge:', error);
        badge.style.display = 'none';
    }
}

/**
 * Add product to cart
 */
async function addToCart(productId, quantity = 1) {
    // Check if logged in
    if (!localStorage.getItem('token')) {
        showAlert('Please login to add items to cart', 'warning');
        setTimeout(() => {
            window.location.href = `/login?redirect=${encodeURIComponent(window.location.pathname)}`;
        }, 1500);
        return;
    }

    try {
        await axios.post('/cart/items', {
            product_id: productId,
            quantity: quantity
        });

        showAlert('Item added to cart!', 'success');
        updateCartBadge();
    } catch (error) {
        console.error('Error adding to cart:', error);

        let message = 'Failed to add item to cart';
        if (error.response?.data?.message) {
            message = error.response.data.message;
        }

        showAlert(message, 'danger');
    }
}

/**
 * Show alert notification
 */
function showAlert(message, type = 'info', duration = 4000) {
    const container = document.getElementById('alertContainer');
    if (!container) {
        console.log('Alert:', message);
        return;
    }

    const alertId = 'alert-' + Date.now();
    const alertHtml = `
        <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show alert-floating" role="alert">
            <i class="bi ${getAlertIcon(type)} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', alertHtml);

    // Auto remove after duration
    setTimeout(() => {
        const alert = document.getElementById(alertId);
        if (alert) {
            alert.classList.remove('show');
            setTimeout(() => alert.remove(), 150);
        }
    }, duration);
}

/**
 * Get icon for alert type
 */
function getAlertIcon(type) {
    const icons = {
        'success': 'bi-check-circle-fill',
        'danger': 'bi-exclamation-triangle-fill',
        'warning': 'bi-exclamation-circle-fill',
        'info': 'bi-info-circle-fill'
    };
    return icons[type] || icons.info;
}

/**
 * Format currency
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

/**
 * Format date
 */
function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

/**
 * Debounce function for search
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Check if user is authenticated
 */
function isAuthenticated() {
    return !!localStorage.getItem('token');
}

/**
 * Get current user
 */
function getCurrentUser() {
    const user = localStorage.getItem('user');
    return user ? JSON.parse(user) : null;
}

/**
 * Require authentication - redirect to login if not authenticated
 */
function requireAuth() {
    if (!isAuthenticated()) {
        window.location.href = `/login?redirect=${encodeURIComponent(window.location.pathname)}`;
        return false;
    }
    return true;
}
