<?= $this->extend('layouts/app') ?>

<?= $this->section('title') ?><?= $title ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><?= $title ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/admin/moodle">Moodle Management</a></li>
                <li class="breadcrumb-item active"><?= $title ?></li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-success" onclick="bulkSyncSelected()">
            <i class="bi bi-arrow-down-circle"></i> Bulk Sync Selected
        </button>
        <button class="btn btn-outline-primary" onclick="refreshCourses()">
            <i class="bi bi-arrow-clockwise"></i> Refresh
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

<!-- Statistics -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-primary mb-1"><?= $total_moodle_courses ?? 0 ?></h3>
                <small class="text-muted">Total Moodle Courses</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-success mb-1"><?= $synced_courses ?? 0 ?></h3>
                <small class="text-muted">Already Synced</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-warning mb-1"><?= $unsynced_courses ?? 0 ?></h3>
                <small class="text-muted">Available to Sync</small>
            </div>
        </div>
    </div>
</div>

<!-- Filters and Search -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" id="courseSearch" placeholder="Search courses...">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="statusFilter">
                    <option value="">All Courses</option>
                    <option value="synced">Synced Only</option>
                    <option value="unsynced">Unsynced Only</option>
                    <option value="visible">Visible Only</option>
                    <option value="hidden">Hidden Only</option>
                </select>
            </div>
            <div class="col-md-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="selectAll">
                    <label class="form-check-label" for="selectAll">
                        Select All
                    </label>
                </div>
            </div>
            <div class="col-md-2">
                <span class="text-muted small" id="selectedCount">0 selected</span>
            </div>
        </div>
    </div>
</div>

<!-- Courses Table -->
<div class="card">
    <div class="card-body">
        <?php if (empty($courses)): ?>
            <div class="text-center py-5">
                <i class="bi bi-mortarboard display-1 text-muted"></i>
                <h4 class="text-muted mt-3">No Moodle Courses Found</h4>
                <p class="text-muted">Check your Moodle connection or ensure courses exist in Moodle.</p>
                <button class="btn btn-primary" onclick="refreshCourses()">
                    <i class="bi bi-arrow-clockwise"></i> Try Again
                </button>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover" id="coursesTable">
                    <thead>
                        <tr>
                            <th width="50">
                                <input type="checkbox" id="selectAllTable" class="form-check-input">
                            </th>
                            <th>Course</th>
                            <th>Shortname</th>
                            <th>Category</th>
                            <th>Visibility</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $course): ?>
                            <tr class="course-row" 
                                data-sync-status="<?= $course['is_synced'] ? 'synced' : 'unsynced' ?>"
                                data-visibility="<?= $course['visible'] ? 'visible' : 'hidden' ?>">
                                <td>
                                    <?php if (!$course['is_synced']): ?>
                                        <input type="checkbox" class="form-check-input course-checkbox" 
                                               value="<?= $course['moodle_id'] ?>">
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div>
                                        <strong><?= esc($course['fullname']) ?></strong>
                                        <div class="text-muted small">
                                            <?= esc(substr($course['summary'], 0, 100)) ?>
                                            <?= strlen($course['summary']) > 100 ? '...' : '' ?>
                                        </div>
                                        <?php if ($course['startdate']): ?>
                                            <div class="text-muted small">
                                                <i class="bi bi-calendar"></i> 
                                                <?= date('M j, Y', $course['startdate']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <code><?= esc($course['shortname']) ?></code>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">Cat <?= $course['categoryid'] ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $course['visible'] ? 'success' : 'warning' ?>">
                                        <?= $course['visible'] ? 'Visible' : 'Hidden' ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($course['is_synced']): ?>
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Synced
                                        </span>
                                        <?php if ($course['cite_course']): ?>
                                            <div class="small text-muted mt-1">
                                                CITE ID: <?= $course['cite_course']['id'] ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-warning">
                                            <i class="bi bi-exclamation-circle"></i> Not Synced
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <?php if ($course['is_synced'] && $course['cite_course']): ?>
                                            <a href="/admin/courses/<?= $course['cite_course']['id'] ?>" 
                                               class="btn btn-sm btn-outline-primary" 
                                               title="View in CITE">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-success sync-course-btn" 
                                                    data-moodle-id="<?= $course['moodle_id'] ?>"
                                                    title="Sync to CITE">
                                                <i class="bi bi-arrow-down-circle"></i>
                                            </button>
                                        <?php endif; ?>
                                        
                                        <button class="btn btn-sm btn-outline-info" 
                                                onclick="viewCourseUsers(<?= $course['moodle_id'] ?>)" 
                                                title="View Users">
                                            <i class="bi bi-people"></i>
                                        </button>
                                        
                                        <button class="btn btn-sm btn-outline-secondary" 
                                                onclick="viewCourseDetails(<?= $course['moodle_id'] ?>)" 
                                                title="Details">
                                            <i class="bi bi-info-circle"></i>
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

<!-- Course Details Modal -->
<div class="modal fade" id="courseDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Course Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="courseDetailsContent">
                <!-- Content loaded via JavaScript -->
            </div>
        </div>
    </div>
</div>

<script>
let selectedCourses = new Set();

// Initialize page functionality
document.addEventListener('DOMContentLoaded', function() {
    initializeEventListeners();
    updateSelectedCount();
});

function initializeEventListeners() {
    // Search functionality
    document.getElementById('courseSearch').addEventListener('input', filterCourses);
    
    // Status filter
    document.getElementById('statusFilter').addEventListener('change', filterCourses);
    
    // Select all functionality
    document.getElementById('selectAllTable').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.course-checkbox:not([disabled])');
        const isChecked = this.checked;
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = isChecked;
            if (isChecked) {
                selectedCourses.add(checkbox.value);
            } else {
                selectedCourses.delete(checkbox.value);
            }
        });
        
        updateSelectedCount();
    });
    
    // Individual course selection
    document.querySelectorAll('.course-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                selectedCourses.add(this.value);
            } else {
                selectedCourses.delete(this.value);
            }
            updateSelectedCount();
        });
    });
    
    // Individual sync buttons
    document.querySelectorAll('.sync-course-btn').forEach(button => {
        button.addEventListener('click', function() {
            const moodleId = this.dataset.moodleId;
            syncSingleCourse(moodleId, this);
        });
    });
}

function filterCourses() {
    const searchTerm = document.getElementById('courseSearch').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const rows = document.querySelectorAll('.course-row');
    
    rows.forEach(row => {
        const courseName = row.querySelector('strong').textContent.toLowerCase();
        const shortname = row.querySelector('code').textContent.toLowerCase();
        const syncStatus = row.dataset.syncStatus;
        const visibility = row.dataset.visibility;
        
        let showRow = true;
        
        // Search filter
        if (searchTerm && !courseName.includes(searchTerm) && !shortname.includes(searchTerm)) {
            showRow = false;
        }
        
        // Status filter
        if (statusFilter) {
            switch (statusFilter) {
                case 'synced':
                    if (syncStatus !== 'synced') showRow = false;
                    break;
                case 'unsynced':
                    if (syncStatus !== 'unsynced') showRow = false;
                    break;
                case 'visible':
                    if (visibility !== 'visible') showRow = false;
                    break;
                case 'hidden':
                    if (visibility !== 'hidden') showRow = false;
                    break;
            }
        }
        
        row.style.display = showRow ? '' : 'none';
    });
}

async function syncSingleCourse(moodleId, button) {
    const originalContent = button.innerHTML;
    button.innerHTML = '<i class="bi bi-arrow-clockwise spinner-border spinner-border-sm"></i>';
    button.disabled = true;
    
    try {
        const formData = new FormData();
        formData.append('moodle_course_id', moodleId);
        
        const response = await fetch('/admin/moodle/sync-course', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Update row to show synced status
            const row = button.closest('tr');
            const statusCell = row.querySelector('td:nth-child(6)');
            statusCell.innerHTML = '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Synced</span>';
            
            // Update actions
            const actionsCell = row.querySelector('td:nth-child(7)');
            actionsCell.innerHTML = `
                <div class="btn-group" role="group">
                    <a href="/admin/courses/${data.cite_course_id}" class="btn btn-sm btn-outline-primary" title="View in CITE">
                        <i class="bi bi-eye"></i>
                    </a>
                    <button class="btn btn-sm btn-outline-info" onclick="viewCourseUsers(${moodleId})" title="View Users">
                        <i class="bi bi-people"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" onclick="viewCourseDetails(${moodleId})" title="Details">
                        <i class="bi bi-info-circle"></i>
                    </button>
                </div>
            `;
            
            // Remove checkbox
            const checkbox = row.querySelector('.course-checkbox');
            if (checkbox) {
                checkbox.remove();
            }
            
            alert('Course synced successfully!');
        } else {
            alert('Sync failed: ' + data.message);
            button.innerHTML = originalContent;
            button.disabled = false;
        }
        
    } catch (error) {
        console.error('Sync failed:', error);
        alert('Sync failed: Network error');
        button.innerHTML = originalContent;
        button.disabled = false;
    }
}

async function bulkSyncSelected() {
    if (selectedCourses.size === 0) {
        alert('Please select courses to sync');
        return;
    }
    
    if (!confirm(`Sync ${selectedCourses.size} selected courses?`)) {
        return;
    }
    
    try {
        const formData = new FormData();
        selectedCourses.forEach(courseId => {
            formData.append('course_ids[]', courseId);
        });
        
        const response = await fetch('/admin/moodle/bulk-sync-courses', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(data.message);
            refreshCourses();
        } else {
            alert('Bulk sync failed: ' + data.message);
        }
        
    } catch (error) {
        console.error('Bulk sync failed:', error);
        alert('Bulk sync failed: Network error');
    }
}

function updateSelectedCount() {
    document.getElementById('selectedCount').textContent = `${selectedCourses.size} selected`;
}

function refreshCourses() {
    window.location.reload();
}

function viewCourseUsers(moodleId) {
    window.location.href = `/admin/moodle/course-users/${moodleId}`;
}

function viewCourseDetails(moodleId) {
    // Find course data from the table
    const row = document.querySelector(`[data-moodle-id="${moodleId}"]`).closest('tr');
    const courseName = row.querySelector('strong').textContent;
    const shortname = row.querySelector('code').textContent;
    
    document.getElementById('courseDetailsContent').innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <h6>Basic Information</h6>
                <table class="table table-sm">
                    <tr><td><strong>Moodle ID:</strong></td><td>${moodleId}</td></tr>
                    <tr><td><strong>Full Name:</strong></td><td>${courseName}</td></tr>
                    <tr><td><strong>Short Name:</strong></td><td>${shortname}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>Quick Actions</h6>
                <div class="d-grid gap-2">
                    <button class="btn btn-success btn-sm" onclick="syncSingleCourse(${moodleId}, this)">
                        <i class="bi bi-arrow-down-circle"></i> Sync to CITE
                    </button>
                    <button class="btn btn-outline-info btn-sm" onclick="viewCourseUsers(${moodleId})">
                        <i class="bi bi-people"></i> View Users
                    </button>
                </div>
            </div>
        </div>
    `;
    
    new bootstrap.Modal(document.getElementById('courseDetailsModal')).show();
}
</script>
<?= $this->endSection() ?>