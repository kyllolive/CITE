<?= $this->extend('layouts/app') ?>

<?= $this->section('title') ?><?= $title ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><?= $title ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/admin/moodle">Moodle Management</a></li>
                <li class="breadcrumb-item"><a href="/admin/moodle/courses">Courses</a></li>
                <li class="breadcrumb-item active">Users</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="/admin/moodle/courses" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Courses
        </a>
    </div>
</div>

<!-- Course Info -->
<?php if (isset($course)): ?>
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <h5 class="mb-3"><?= esc($course['fullname']) ?></h5>
                <div class="row">
                    <div class="col-sm-6">
                        <strong>Short Name:</strong> <code><?= esc($course['shortname']) ?></code>
                    </div>
                    <div class="col-sm-6">
                        <strong>Moodle ID:</strong> <?= $moodle_course_id ?>
                    </div>
                </div>
                <?php if (!empty($course['summary'])): ?>
                    <div class="mt-2">
                        <strong>Description:</strong>
                        <p class="text-muted"><?= esc(substr($course['summary'], 0, 200)) ?>...</p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-md-4 text-end">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h3 class="text-primary mb-1"><?= count($users) ?></h3>
                        <small class="text-muted">Enrolled Users</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Users Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="bi bi-people"></i> Enrolled Users
        </h5>
        <div class="d-flex gap-2">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control" id="userSearch" placeholder="Search users...">
            </div>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($users)): ?>
            <div class="text-center py-5">
                <i class="bi bi-people display-1 text-muted"></i>
                <h4 class="text-muted mt-3">No Users Enrolled</h4>
                <p class="text-muted">This course has no enrolled users.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover" id="usersTable">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Roles</th>
                            <th>First Access</th>
                            <th>Last Access</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr class="user-row">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                            <?= strtoupper(substr($user['firstname'] ?? 'U', 0, 1)) ?>
                                        </div>
                                        <div>
                                            <strong><?= esc(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? '')) ?></strong>
                                            <div class="text-muted small">ID: <?= $user['id'] ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?= esc($user['email'] ?? 'N/A') ?>
                                    <?php if (isset($user['username'])): ?>
                                        <div class="text-muted small">@<?= esc($user['username']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (isset($user['roles']) && is_array($user['roles'])): ?>
                                        <?php foreach ($user['roles'] as $role): ?>
                                            <?php 
                                            $roleColors = [
                                                'manager' => 'danger',
                                                'coursecreator' => 'warning', 
                                                'editingteacher' => 'info',
                                                'teacher' => 'info',
                                                'student' => 'success',
                                                'guest' => 'secondary'
                                            ];
                                            $badgeColor = $roleColors[$role['shortname']] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?= $badgeColor ?> me-1">
                                                <?= esc($role['name'] ?? $role['shortname']) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Student</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (isset($user['firstaccess']) && $user['firstaccess'] > 0): ?>
                                        <?= date('M j, Y', $user['firstaccess']) ?>
                                    <?php else: ?>
                                        <span class="text-muted">Never</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (isset($user['lastaccess']) && $user['lastaccess'] > 0): ?>
                                        <span title="<?= date('Y-m-d H:i:s', $user['lastaccess']) ?>">
                                            <?= date('M j, Y', $user['lastaccess']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">Never</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    $isActive = !isset($user['suspended']) || !$user['suspended'];
                                    ?>
                                    <span class="badge bg-<?= $isActive ? 'success' : 'warning' ?>">
                                        <?= $isActive ? 'Active' : 'Suspended' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-outline-primary" 
                                                onclick="viewUserProfile(<?= $user['id'] ?>)" 
                                                title="View Profile">
                                            <i class="bi bi-person"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-info" 
                                                onclick="viewUserGrades(<?= $user['id'] ?>)" 
                                                title="View Grades">
                                            <i class="bi bi-bar-chart"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary" 
                                                onclick="viewUserActivity(<?= $user['id'] ?>)" 
                                                title="View Activity">
                                            <i class="bi bi-activity"></i>
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

<!-- User Details Modal -->
<div class="modal fade" id="userDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="userDetailsContent">
                <!-- Content loaded via JavaScript -->
            </div>
        </div>
    </div>
</div>

<script>
// Initialize search functionality
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('userSearch').addEventListener('input', filterUsers);
});

function filterUsers() {
    const searchTerm = document.getElementById('userSearch').value.toLowerCase();
    const rows = document.querySelectorAll('.user-row');
    
    rows.forEach(row => {
        const userName = row.querySelector('strong').textContent.toLowerCase();
        const email = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
        
        const matches = userName.includes(searchTerm) || email.includes(searchTerm);
        row.style.display = matches ? '' : 'none';
    });
}

function viewUserProfile(userId) {
    document.getElementById('userDetailsContent').innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    
    new bootstrap.Modal(document.getElementById('userDetailsModal')).show();
    
    // Simulate loading user data
    setTimeout(() => {
        document.getElementById('userDetailsContent').innerHTML = `
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                User profile details would be loaded here from Moodle API.
                <br><strong>User ID:</strong> ${userId}
            </div>
            <div class="row">
                <div class="col-md-6">
                    <h6>Profile Information</h6>
                    <p class="text-muted">Profile data from Moodle would appear here</p>
                </div>
                <div class="col-md-6">
                    <h6>Course Progress</h6>
                    <p class="text-muted">Progress and completion data would appear here</p>
                </div>
            </div>
        `;
    }, 1000);
}

function viewUserGrades(userId) {
    alert(`View grades for user ${userId} - This would integrate with Moodle gradebook API`);
}

function viewUserActivity(userId) {
    alert(`View activity for user ${userId} - This would show recent course activity`);
}
</script>


<?= $this->endSection() ?>