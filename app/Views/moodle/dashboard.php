<?= $this->extend('layouts/app') ?>

<?= $this->section('title') ?><?= $title ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><?= $title ?></h1>
    <?php if ($sso_enabled): ?>
        <a href="/moodle/sso-login" class="btn btn-primary" target="_blank">
            <i class="bi bi-box-arrow-up-right"></i> Open Moodle
        </a>
    <?php endif; ?>
</div>

<?php if (!$sso_enabled): ?>
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i>
        Moodle SSO is not enabled. Please contact your administrator to configure Moodle integration.
    </div>
<?php endif; ?>

<div class="row">
    <!-- SSO Status -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-shield-check"></i> SSO Status
                </h5>
            </div>
            <div class="card-body text-center">
                <?php if ($sso_enabled): ?>
                    <div class="mb-3">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                    </div>
                    <h6 class="text-success">SSO Enabled</h6>
                    <p class="text-muted small">Single Sign-On is active</p>
                    <?php if ($user['moodle_id']): ?>
                        <span class="badge bg-success">Linked (ID: <?= $user['moodle_id'] ?>)</span>
                    <?php else: ?>
                        <span class="badge bg-warning">Not Linked</span>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="mb-3">
                        <i class="bi bi-x-circle-fill text-danger" style="font-size: 3rem;"></i>
                    </div>
                    <h6 class="text-danger">SSO Disabled</h6>
                    <p class="text-muted small">Contact administrator</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-lightning"></i> Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <?php if ($sso_enabled): ?>
                        <a href="/moodle/sso-login" class="btn btn-outline-primary" target="_blank">
                            <i class="bi bi-box-arrow-up-right"></i> Access Moodle
                        </a>
                        <?php if (in_array($user['role'], ['admin', 'instructor'])): ?>
                            <button class="btn btn-outline-info" onclick="loadMoodleCourses()">
                                <i class="bi bi-arrow-repeat"></i> Sync Courses
                            </button>
                            <button class="btn btn-outline-success" onclick="showCreateCourseModal()">
                                <i class="bi bi-plus-circle"></i> Create Course
                            </button>
                        <?php endif; ?>
                    <?php else: ?>
                        <button class="btn btn-outline-secondary" disabled>
                            <i class="bi bi-x-circle"></i> SSO Not Available
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- User Stats -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-person-badge"></i> Your Stats
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <h4 class="text-primary mb-1"><?= count($moodle_courses) ?></h4>
                            <small class="text-muted">Moodle Courses</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h4 class="text-success mb-1"><?= count($user_badges) ?></h4>
                        <small class="text-muted">Earned Badges</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Moodle Courses -->
<?php if (!empty($moodle_courses) || in_array($user['role'], ['admin', 'instructor'])): ?>
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="bi bi-mortarboard"></i> Moodle Courses
            <?php if (in_array($user['role'], ['admin', 'instructor'])): ?>
                <span class="badge bg-primary ms-2"><?= $user['role'] === 'admin' ? 'Admin' : 'Instructor' ?> View</span>
            <?php endif; ?>
        </h5>
        <?php if (in_array($user['role'], ['admin', 'instructor'])): ?>
            <button class="btn btn-sm btn-outline-primary" onclick="loadMoodleCourses()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div id="moodle-courses-container">
            <?php if (empty($moodle_courses)): ?>
                <div class="text-center py-4">
                    <i class="bi bi-mortarboard display-4 text-muted"></i>
                    <p class="text-muted mt-2">No Moodle courses found.</p>
                    <?php if (in_array($user['role'], ['admin', 'instructor'])): ?>
                        <button class="btn btn-primary" onclick="loadMoodleCourses()">
                            <i class="bi bi-search"></i> Load Courses
                        </button>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($moodle_courses as $course): ?>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="card-title"><?= esc($course['title']) ?></h6>
                                    <p class="card-text small text-muted">
                                        <?= esc(substr($course['summary'], 0, 100)) ?>...
                                    </p>
                                    <div class="mb-2">
                                        <span class="badge bg-<?= $course['visible'] ? 'success' : 'secondary' ?>">
                                            <?= $course['visible'] ? 'Visible' : 'Hidden' ?>
                                        </span>
                                        <?php if ($course['cite_id']): ?>
                                            <span class="badge bg-info">Synced</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex gap-1">
                                        <?php if ($course['sso_url']): ?>
                                            <a href="<?= $course['sso_url'] ?>" 
                                               class="btn btn-sm btn-primary" 
                                               target="_blank" 
                                               title="Open in Moodle">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($course['can_manage']): ?>
                                            <button class="btn btn-sm btn-outline-secondary" 
                                                    onclick="manageCourse(<?= $course['id'] ?>)" 
                                                    title="Manage">
                                                <i class="bi bi-gear"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php if (!$course['cite_id'] && in_array($user['role'], ['admin', 'instructor'])): ?>
                                            <button class="btn btn-sm btn-outline-success" 
                                                    onclick="syncCourse(<?= $course['id'] ?>)" 
                                                    title="Sync to CITE">
                                                <i class="bi bi-arrow-down-circle"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- User Badges -->
<?php if (!empty($user_badges)): ?>
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-award"></i> Your Badges
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <?php foreach (array_slice($user_badges, 0, 6) as $badge): ?>
                <div class="col-md-4 col-lg-2 mb-3">
                    <div class="card text-center">
                        <div class="card-body p-3">
                            <?php if (isset($badge['imageurl']) && $badge['imageurl']): ?>
                                <img src="<?= $badge['imageurl'] ?>" 
                                     class="mb-2" 
                                     style="width: 48px; height: 48px;" 
                                     alt="Badge">
                            <?php else: ?>
                                <i class="bi bi-award-fill text-warning mb-2" style="font-size: 48px;"></i>
                            <?php endif; ?>
                            <h6 class="card-title small"><?= esc($badge['name']) ?></h6>
                            <p class="card-text small text-muted"><?= $badge['coursename'] ?? 'Site Badge' ?></p>
                            <small class="text-muted">
                                <?= date('M j, Y', $badge['dateissued']) ?>
                            </small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if (count($user_badges) > 6): ?>
            <div class="text-center">
                <button class="btn btn-outline-primary" onclick="showAllBadges()">
                    View All <?= count($user_badges) ?> Badges
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- Course Creation Modal -->
<div class="modal fade" id="createCourseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Course in Moodle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createCourseForm">
                    <div class="mb-3">
                        <label class="form-label">Course Title</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Instructor</label>
                                <select class="form-select" name="instructor_id" required>
                                    <option value="<?= $user['id'] ?>"><?= esc($user['name']) ?> (You)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="create_in_moodle" value="1" checked>
                        <label class="form-check-label">Create in Moodle</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="createCourse()">Create Course</button>
            </div>
        </div>
    </div>
</div>

<script>
// Load Moodle courses via AJAX
async function loadMoodleCourses() {
    try {
        const response = await fetch('/moodle/get-moodle-courses', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.courses) {
            updateCoursesDisplay(data.courses);
        }
    } catch (error) {
        console.error('Failed to load courses:', error);
        alert('Failed to load Moodle courses');
    }
}

// Update courses display
function updateCoursesDisplay(courses) {
    const container = document.getElementById('moodle-courses-container');
    if (courses.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4">
                <i class="bi bi-mortarboard display-4 text-muted"></i>
                <p class="text-muted mt-2">No Moodle courses found.</p>
            </div>
        `;
        return;
    }
    
    let html = '<div class="row">';
    courses.forEach(course => {
        html += `
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-title">${escapeHtml(course.fullname)}</h6>
                        <p class="card-text small text-muted">
                            ${escapeHtml(course.summary.substring(0, 100))}...
                        </p>
                        <div class="mb-2">
                            <span class="badge bg-${course.visible ? 'success' : 'secondary'}">
                                ${course.visible ? 'Visible' : 'Hidden'}
                            </span>
                            ${course.cite_id ? '<span class="badge bg-info">Synced</span>' : ''}
                        </div>
                        <div class="d-flex gap-1">
                            ${course.sso_url ? `<a href="${course.sso_url}" class="btn btn-sm btn-primary" target="_blank" title="Open in Moodle"><i class="bi bi-eye"></i></a>` : ''}
                            ${course.can_manage ? `<button class="btn btn-sm btn-outline-secondary" onclick="manageCourse(${course.id})" title="Manage"><i class="bi bi-gear"></i></button>` : ''}
                            ${!course.cite_id ? `<button class="btn btn-sm btn-outline-success" onclick="syncCourse(${course.id})" title="Sync to CITE"><i class="bi bi-arrow-down-circle"></i></button>` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    html += '</div>';
    container.innerHTML = html;
}

// Sync course to CITE
async function syncCourse(moodleCourseId) {
    if (!confirm('Sync this course to CITE?')) return;
    
    try {
        const formData = new FormData();
        formData.append('moodle_course_id', moodleCourseId);
        
        const response = await fetch('/moodle/sync-course', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Course synced successfully!');
            loadMoodleCourses(); // Refresh the list
        } else {
            alert('Failed to sync course: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Sync failed:', error);
        alert('Failed to sync course');
    }
}

// Show create course modal
function showCreateCourseModal() {
    new bootstrap.Modal(document.getElementById('createCourseModal')).show();
}

// Create course
async function createCourse() {
    const form = document.getElementById('createCourseForm');
    const formData = new FormData(form);
    
    try {
        const response = await fetch('/moodle/create-course', {
            method: 'POST',
            body: formData
        });
        
        if (response.ok) {
            alert('Course created successfully!');
            bootstrap.Modal.getInstance(document.getElementById('createCourseModal')).hide();
            form.reset();
            loadMoodleCourses();
        } else {
            alert('Failed to create course');
        }
    } catch (error) {
        console.error('Course creation failed:', error);
        alert('Failed to create course');
    }
}

// Manage course (placeholder)
function manageCourse(courseId) {
    // This could open a management modal or redirect to course management page
    alert('Course management features coming soon!');
}

// Show all badges (placeholder)
function showAllBadges() {
    alert('Badge gallery coming soon!');
}

// Utility function to escape HTML
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

// Load courses on page load if user is admin/instructor
document.addEventListener('DOMContentLoaded', function() {
    <?php if (in_array($user['role'], ['admin', 'instructor']) && $sso_enabled && $user['moodle_id']): ?>
        loadMoodleCourses();
    <?php endif; ?>
});
</script>
<?= $this->endSection() ?>