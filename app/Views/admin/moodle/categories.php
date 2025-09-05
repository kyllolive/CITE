<?= $this->extend('layouts/app') ?>

<?= $this->section('title') ?><?= $title ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><?= $title ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/admin/moodle">Moodle Management</a></li>
                <li class="breadcrumb-item active">Categories</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-primary" onclick="showCreateCategoryModal()">
            <i class="bi bi-plus-circle"></i> Create Category
        </button>
        <a href="/admin/moodle" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle"></i> <?= $error ?>
    </div>
<?php endif; ?>

<!-- Categories Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-folder"></i> Course Categories
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($categories)): ?>
            <div class="text-center py-5">
                <i class="bi bi-folder display-1 text-muted"></i>
                <h4 class="text-muted mt-3">No Categories Found</h4>
                <p class="text-muted">Create your first category to organize courses.</p>
                <button class="btn btn-primary" onclick="showCreateCategoryModal()">
                    <i class="bi bi-plus-circle"></i> Create First Category
                </button>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Parent</th>
                            <th>Course Count</th>
                            <th>Visible</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?= $category['id'] ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if ($category['parent'] != 0): ?>
                                            <span class="text-muted me-2">└</span>
                                        <?php endif; ?>
                                        <i class="bi bi-folder<?= $category['visible'] ? '' : '-fill text-muted' ?> me-2"></i>
                                        <strong><?= esc($category['name']) ?></strong>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($category['description'])): ?>
                                        <span title="<?= esc($category['description']) ?>">
                                            <?= esc(substr($category['description'], 0, 50)) ?>
                                            <?= strlen($category['description']) > 50 ? '...' : '' ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">No description</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($category['parent'] != 0): ?>
                                        <span class="badge bg-secondary">Parent: <?= $category['parent'] ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-primary">Root</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?= $category['coursecount'] ?? 0 ?> courses</span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $category['visible'] ? 'success' : 'warning' ?>">
                                        <?= $category['visible'] ? 'Visible' : 'Hidden' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-outline-primary" 
                                                onclick="viewCategoryDetails(<?= $category['id'] ?>)" 
                                                title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-info" 
                                                onclick="viewCategoryCourses(<?= $category['id'] ?>)" 
                                                title="View Courses">
                                            <i class="bi bi-mortarboard"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-success" 
                                                onclick="createSubcategory(<?= $category['id'] ?>)" 
                                                title="Create Subcategory">
                                            <i class="bi bi-plus-circle"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Create Category Modal -->
<div class="modal fade" id="createCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Course Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createCategoryForm">
                    <div class="mb-3">
                        <label for="categoryName" class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="categoryName" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="categoryDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="categoryDescription" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="parentCategory" class="form-label">Parent Category</label>
                        <select class="form-select" id="parentCategory" name="parent">
                            <option value="0">Root Category</option>
                            <?php if (!empty($categories)): ?>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>">
                                        <?= esc($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="createCategory()">
                    <i class="bi bi-plus-circle"></i> Create Category
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Category Details Modal -->
<div class="modal fade" id="categoryDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Category Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="categoryDetailsContent">
                <!-- Content loaded via JavaScript -->
            </div>
        </div>
    </div>
</div>

<script>
function showCreateCategoryModal() {
    document.getElementById('createCategoryForm').reset();
    new bootstrap.Modal(document.getElementById('createCategoryModal')).show();
}

async function createCategory() {
    const form = document.getElementById('createCategoryForm');
    const formData = new FormData(form);
    
    // Basic validation
    const name = formData.get('name');
    if (!name || name.trim().length < 3) {
        alert('Category name must be at least 3 characters long');
        return;
    }
    
    try {
        const response = await fetch('/admin/moodle/create-category', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Category created successfully!');
            bootstrap.Modal.getInstance(document.getElementById('createCategoryModal')).hide();
            window.location.reload();
        } else {
            alert('Failed to create category: ' + (data.message || 'Unknown error'));
        }
        
    } catch (error) {
        console.error('Category creation failed:', error);
        alert('Failed to create category: Network error');
    }
}

function viewCategoryDetails(categoryId) {
    // Find category data from the table
    const rows = document.querySelectorAll('tbody tr');
    let categoryData = null;
    
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells[0].textContent == categoryId) {
            categoryData = {
                id: categoryId,
                name: cells[1].querySelector('strong').textContent,
                description: cells[2].textContent,
                parent: cells[3].textContent,
                courseCount: cells[4].textContent,
                visible: cells[5].textContent
            };
        }
    });
    
    if (categoryData) {
        document.getElementById('categoryDetailsContent').innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h6>Basic Information</h6>
                    <table class="table table-sm">
                        <tr><td><strong>ID:</strong></td><td>${categoryData.id}</td></tr>
                        <tr><td><strong>Name:</strong></td><td>${categoryData.name}</td></tr>
                        <tr><td><strong>Parent:</strong></td><td>${categoryData.parent}</td></tr>
                        <tr><td><strong>Visibility:</strong></td><td>${categoryData.visible}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6>Statistics</h6>
                    <table class="table table-sm">
                        <tr><td><strong>Courses:</strong></td><td>${categoryData.courseCount}</td></tr>
                    </table>
                    
                    <h6>Actions</h6>
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-info btn-sm" onclick="viewCategoryCourses(${categoryData.id})">
                            <i class="bi bi-mortarboard"></i> View Courses
                        </button>
                        <button class="btn btn-outline-success btn-sm" onclick="createSubcategory(${categoryData.id})">
                            <i class="bi bi-plus-circle"></i> Create Subcategory
                        </button>
                    </div>
                </div>
            </div>
            
            ${categoryData.description && categoryData.description !== 'No description' ? 
                `<div class="mt-3">
                    <h6>Description</h6>
                    <p class="text-muted">${categoryData.description}</p>
                </div>` : ''
            }
        `;
        
        new bootstrap.Modal(document.getElementById('categoryDetailsModal')).show();
    }
}

function viewCategoryCourses(categoryId) {
    // Redirect to courses page with category filter
    window.location.href = `/admin/moodle/courses?category=${categoryId}`;
}

function createSubcategory(parentId) {
    document.getElementById('createCategoryForm').reset();
    document.getElementById('parentCategory').value = parentId;
    new bootstrap.Modal(document.getElementById('createCategoryModal')).show();
}
</script>
<?= $this->endSection() ?>