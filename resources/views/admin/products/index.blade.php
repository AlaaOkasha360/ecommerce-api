@extends('admin.layouts.app')

@section('title', 'Products Management')
@section('page-title', 'Products Management')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <input type="search" class="form-control" placeholder="Search products..." id="productSearch" oninput="searchProducts()" style="max-width: 300px;">
            </div>
            <button class="btn btn-primary" onclick="openProductModal()">
                <i class="bi bi-plus-lg me-2"></i>Add Product
            </button>
        </div>
    </div>
</div>

<div class="card data-card">
    <div class="card-header">
        <h6 class="mb-0"><i class="bi bi-box-seam me-2"></i>Products</h6>
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
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="productsTable">
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <div class="spinner-border spinner-border-sm" role="status"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1" size="lg">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productModalTitle">Add Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="productForm">
                <div class="modal-body">
                    <input type="hidden" id="productId">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="productName" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="productName" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="productCategory" class="form-label">Category</label>
                            <select class="form-select" id="productCategory" required>
                                <option value="">Select Category</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="productDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="productDescription" rows="3"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="productPrice" class="form-label">Price</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="productPrice" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="productComparePrice" class="form-label">Compare Price</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="productComparePrice" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="productQuantity" class="form-label">Stock</label>
                            <input type="number" class="form-control" id="productQuantity" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="productSku" class="form-label">SKU</label>
                            <input type="text" class="form-control" id="productSku">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="productImage" class="form-label">Image URL</label>
                            <input type="url" class="form-control" id="productImage">
                        </div>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="productActive" checked>
                        <label class="form-check-label" for="productActive">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Product</button>
                    <button type="button" class="btn btn-danger" id="deleteProductBtn" style="display: none;">Delete Product</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let productModal;
let currentProductId = null;

document.addEventListener('DOMContentLoaded', function() {
    productModal = new bootstrap.Modal(document.getElementById('productModal'));
    loadCategories();
    loadProducts();
    document.getElementById('productForm').addEventListener('submit', saveProduct);
});

async function loadCategories() {
    try {
        const response = await axios.get('/categories');
        const categories = response.data.data || response.data;
        const select = document.getElementById('productCategory');

        select.innerHTML = '<option value="">Select Category</option>' +
            categories.map(cat => `<option value="${cat.id}">${cat.name}</option>`).join('');
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

async function loadProducts() {
    try {
        const response = await axios.get('/products');
        let products = response.data.data || response.data;

        // Handle paginated response
        if (response.data.data && response.data.meta) {
            products = response.data.data;
        }

        displayProducts(products);
    } catch (error) {
        console.error('Error loading products:', error);
        document.getElementById('productsTable').innerHTML =
            '<tr><td colspan="6" class="text-center text-danger">Error loading products</td></tr>';
    }
}

function displayProducts(products) {
    const tbody = document.getElementById('productsTable');

    if (!products || products.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No products found</td></tr>';
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
            <td>${formatCurrency(product.price || 0)}</td>
            <td><span class="badge bg-${(product.stock || product.quantity || 0) > 0 ? 'success' : 'danger'}">${product.stock || product.quantity || 0}</span></td>
            <td><span class="badge bg-${product.is_active !== false ? 'success' : 'secondary'}">${product.is_active !== false ? 'Active' : 'Inactive'}</span></td>
            <td>
                <button class="btn btn-sm btn-outline-primary" onclick="editProduct('${JSON.stringify(product).replace(/"/g, '&quot;')}')">
                    <i class="bi bi-pencil"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

function openProductModal() {
    currentProductId = null;
    document.getElementById('productModalTitle').textContent = 'Add Product';
    document.getElementById('productForm').reset();
    document.getElementById('deleteProductBtn').style.display = 'none';
    productModal.show();
}

function editProduct(productJson) {
    const product = JSON.parse(productJson.replace(/&quot;/g, '"'));
    currentProductId = product.id;

    document.getElementById('productModalTitle').textContent = 'Edit Product';
    document.getElementById('productId').value = product.id;
    document.getElementById('productName').value = product.name || '';
    document.getElementById('productCategory').value = product.category_id || '';
    document.getElementById('productDescription').value = product.description || '';
    document.getElementById('productPrice').value = product.price || '';
    document.getElementById('productComparePrice').value = product.compare_price || '';
    document.getElementById('productQuantity').value = product.stock || product.quantity || '';
    document.getElementById('productSku').value = product.sku || '';
    document.getElementById('productImage').value = product.image || '';
    document.getElementById('productActive').checked = product.is_active !== false;

    document.getElementById('deleteProductBtn').style.display = 'block';
    productModal.show();
}

async function saveProduct(e) {
    e.preventDefault();

    const productId = document.getElementById('productId').value;
    const data = {
        name: document.getElementById('productName').value,
        category_id: document.getElementById('productCategory').value,
        description: document.getElementById('productDescription').value,
        price: document.getElementById('productPrice').value,
        compare_price: document.getElementById('productComparePrice').value || null,
        quantity: document.getElementById('productQuantity').value,
        sku: document.getElementById('productSku').value,
        image: document.getElementById('productImage').value,
        is_active: document.getElementById('productActive').checked
    };

    try {
        if (productId) {
            await axios.put(`/products/${productId}`, data);
            showAlert('Product updated successfully', 'success');
        } else {
            await axios.post('/products', data);
            showAlert('Product created successfully', 'success');
        }
        productModal.hide();
        loadProducts();
    } catch (error) {
        console.error('Error saving product:', error);
        showAlert('Failed to save product', 'danger');
    }
}

async function deleteProduct() {
    if (!confirm('Are you sure you want to delete this product?')) return;

    try {
        await axios.delete(`/products/${currentProductId}`);
        showAlert('Product deleted successfully', 'success');
        productModal.hide();
        loadProducts();
    } catch (error) {
        console.error('Error deleting product:', error);
        showAlert('Failed to delete product', 'danger');
    }
}

function searchProducts() {
    const query = document.getElementById('productSearch').value.toLowerCase();
    const rows = document.querySelectorAll('#productsTable tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
    });
}

document.getElementById('deleteProductBtn')?.addEventListener('click', deleteProduct);
</script>
@endsection
