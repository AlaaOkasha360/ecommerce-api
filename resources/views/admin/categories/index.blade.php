@extends('admin.layouts.app')

@section('title', 'Categories Management')
@section('page-title', 'Categories Management')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <input type="search" class="form-control" placeholder="Search categories..." id="categorySearch" oninput="searchCategories()" style="max-width: 300px;">
            </div>
            <button class="btn btn-primary" onclick="openCategoryModal()">
                <i class="bi bi-plus-lg me-2"></i>Add Category
            </button>
        </div>
    </div>
</div>

<div class="card data-card">
    <div class="card-header">
        <h6 class="mb-0"><i class="bi bi-tags me-2"></i>Categories</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Products</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="categoriesTable">
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

<!-- Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryModalTitle">Add Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="categoryForm">
                <div class="modal-body">
                    <input type="hidden" id="categoryId">

                    <div class="mb-3">
                        <label for="categoryName" class="form-label">Category Name</label>
                        <input type="text" class="form-control" id="categoryName" required>
                    </div>

                    <div class="mb-3">
                        <label for="categorySlug" class="form-label">Slug</label>
                        <input type="text" class="form-control" id="categorySlug" required>
                        <small class="text-muted">URL-friendly name (auto-generated from name)</small>
                    </div>

                    <div class="mb-3">
                        <label for="categoryDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="categoryDescription" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="categoryParent" class="form-label">Parent Category (Optional)</label>
                        <select class="form-select" id="categoryParent">
                            <option value="">None</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Category</button>
                    <button type="button" class="btn btn-danger" id="deleteCategoryBtn" style="display: none;">Delete Category</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let categoryModal;
let currentCategoryId = null;

document.addEventListener('DOMContentLoaded', function() {
    categoryModal = new bootstrap.Modal(document.getElementById('categoryModal'));
    loadCategories();
    document.getElementById('categoryForm').addEventListener('submit', saveCategory);
    document.getElementById('categoryName').addEventListener('input', generateSlug);
});

async function loadCategories() {
    try {
        const response = await axios.get('/categories');
        const categories = response.data.data || response.data;
        displayCategories(categories);
        loadParentCategories(categories);
    } catch (error) {
        console.error('Error loading categories:', error);
        document.getElementById('categoriesTable').innerHTML =
            '<tr><td colspan="5" class="text-center text-danger">Error loading categories</td></tr>';
    }
}

function displayCategories(categories) {
    const tbody = document.getElementById('categoriesTable');

    if (!categories || categories.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No categories found</td></tr>';
        return;
    }

    tbody.innerHTML = categories.map(category => `
        <tr>
            <td><strong>${category.name}</strong></td>
            <td><code>${category.slug}</code></td>
            <td><span class="badge bg-info">${category.products?.length || 0}</span></td>
            <td><small class="text-muted">${(category.description || '').substring(0, 50)}${(category.description || '').length > 50 ? '...' : ''}</small></td>
            <td>
                <button class="btn btn-sm btn-outline-primary" onclick="editCategory('${JSON.stringify(category).replace(/"/g, '&quot;')}')">
                    <i class="bi bi-pencil"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

function loadParentCategories(categories) {
    const select = document.getElementById('categoryParent');
    select.innerHTML = '<option value="">None</option>' +
        categories.filter(cat => !cat.parent_id).map(cat =>
            `<option value="${cat.id}">${cat.name}</option>`
        ).join('');
}

function generateSlug() {
    const name = document.getElementById('categoryName').value;
    const slug = name
        .toLowerCase()
        .trim()
        .replace(/[^\w\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-');

    document.getElementById('categorySlug').value = slug;
}

function openCategoryModal() {
    currentCategoryId = null;
    document.getElementById('categoryModalTitle').textContent = 'Add Category';
    document.getElementById('categoryForm').reset();
    document.getElementById('deleteCategoryBtn').style.display = 'none';
    categoryModal.show();
}

function editCategory(categoryJson) {
    const category = JSON.parse(categoryJson.replace(/&quot;/g, '"'));
    currentCategoryId = category.id;

    document.getElementById('categoryModalTitle').textContent = 'Edit Category';
    document.getElementById('categoryId').value = category.id;
    document.getElementById('categoryName').value = category.name || '';
    document.getElementById('categorySlug').value = category.slug || '';
    document.getElementById('categoryDescription').value = category.description || '';
    document.getElementById('categoryParent').value = category.parent_id || '';

    document.getElementById('deleteCategoryBtn').style.display = 'block';
    categoryModal.show();
}

async function saveCategory(e) {
    e.preventDefault();

    const categoryId = document.getElementById('categoryId').value;
    const data = {
        name: document.getElementById('categoryName').value,
        slug: document.getElementById('categorySlug').value,
        description: document.getElementById('categoryDescription').value,
        parent_id: document.getElementById('categoryParent').value || null
    };

    try {
        if (categoryId) {
            await axios.put(`/categories/${categoryId}`, data);
            showAlert('Category updated successfully', 'success');
        } else {
            await axios.post('/categories', data);
            showAlert('Category created successfully', 'success');
        }
        categoryModal.hide();
        loadCategories();
    } catch (error) {
        console.error('Error saving category:', error);
        showAlert('Failed to save category', 'danger');
    }
}

async function deleteCategory() {
    if (!confirm('Are you sure you want to delete this category?')) return;

    try {
        await axios.delete(`/categories/${currentCategoryId}`);
        showAlert('Category deleted successfully', 'success');
        categoryModal.hide();
        loadCategories();
    } catch (error) {
        console.error('Error deleting category:', error);
        showAlert('Failed to delete category', 'danger');
    }
}

function searchCategories() {
    const query = document.getElementById('categorySearch').value.toLowerCase();
    const rows = document.querySelectorAll('#categoriesTable tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
    });
}

document.getElementById('deleteCategoryBtn')?.addEventListener('click', deleteCategory);
</script>
@endsection
