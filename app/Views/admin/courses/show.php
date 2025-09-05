<?= $this->extend('layouts/app') ?>

<?= $this->section('title') ?><?= $title ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><?= $title ?></h1>
    <div class="d-flex gap-2">
        <a href="/admin/courses/<?= $course['id'] ?>/edit" class="btn btn-outline-primary">
            <i class="bi bi-pencil"></i> Edit Course
        </a>
        <a href="/admin/courses/<?= $course['id'] ?>/enrollments" class="btn btn-outline-info">
            <i class="bi bi-people"></i> Manage Enrollments
        </a>
        <?php if ($course['moodle_id']): ?>
            <a href="/moodle/sso-login/<?= $course['id'] ?>" class="btn btn-outline-success" target="_blank">
                <i class="bi bi-box-arrow-up-right"></i> Open in Moodle
            </a>
        <?php endif; ?>
        <a href="/admin/courses" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Courses
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Course Details</h5>
            </div>
            <div class="card-body">
                <h3 class="mb-3">
                    <?= esc($course['title']) ?>
                    <?php if ($course['moodle_id']): ?>
                        <span class="badge bg-info ms-2" title="Linked to Moodle">Moodle ID: <?= $course['moodle_id'] ?></span>
                    <?php endif; ?>
                </h3>
                
                <div class="mb-3">
                    <strong>Status:</strong>
                    <span class="badge bg-<?= $course['status'] === 'active' ? 'success' : ($course['status'] === 'archived' ? 'secondary' : 'warning') ?> ms-2">
                        <?= ucfirst($course['status']) ?>
                    </span>
                </div>
                
                <div class="mb-3">
                    <strong>Description:</strong>
                    <p class="mt-2"><?= nl2br(esc($course['description'])) ?></p>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <strong>Start Date:</strong>
                            <div><?= $course['start_date'] ? date('F j, Y', strtotime($course['start_date'])) : 'Not set' ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <strong>End Date:</strong>
                            <div><?= $course['end_date'] ? date('F j, Y', strtotime($course['end_date'])) : 'Not set' ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <strong>Created:</strong>
                    <div><?= date('F j, Y \a\t g:i A', strtotime($course['created_at'])) ?></div>
                </div>
                
                <div class="mb-3">
                    <strong>Last Updated:</strong>
                    <div><?= date('F j, Y \a\t g:i A', strtotime($course['updated_at'])) ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Instructor Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Instructor</h5>
            </div>
            <div class="card-body">
                <?php if ($course['instructor_name']): ?>
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar bg-warning text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                            <?= strtoupper(substr($course['instructor_name'], 0, 1)) ?>
                        </div>
                        <div>
                            <h6 class="mb-0"><?= esc($course['instructor_name']) ?></h6>
                            <small class="text-muted"><?= esc($course['instructor_email']) ?></small>
                        </div>
                    </div>
                    <a href="/admin/users/<?= $course['instructor_id'] ?>" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-person"></i> View Profile
                    </a>
                <?php else: ?>
                    <p class="text-muted">No instructor assigned</p>
                    <a href="/admin/courses/<?= $course['id'] ?>/edit" class="btn btn-sm btn-primary">
                        <i class="bi bi-person-plus"></i> Assign Instructor
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Enrollment Statistics -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Enrollment Stats</h5>
            </div>
            <div class="card-body text-center">
                <h2 class="text-primary mb-2"><?= $enrollmentCount ?></h2>
                <p class="text-muted mb-3">Total Enrolled Students</p>
                <a href="/admin/courses/<?= $course['id'] ?>/enrollments" class="btn btn-outline-info btn-sm">
                    <i class="bi bi-people"></i> View All Students
                </a>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <?php if ($course['status'] === 'active'): ?>
                        <a href="/admin/courses/<?= $course['id'] ?>/archive" 
                           class="btn btn-outline-warning btn-sm"
                           onclick="return confirm('Are you sure you want to archive this course?')">
                            <i class="bi bi-archive"></i> Archive Course
                        </a>
                    <?php elseif ($course['status'] === 'inactive' || $course['status'] === 'archived'): ?>
                        <a href="/admin/courses/<?= $course['id'] ?>/activate" 
                           class="btn btn-outline-success btn-sm">
                            <i class="bi bi-play-circle"></i> Activate Course
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($course['moodle_id']): ?>
                        <button type="button" class="btn btn-outline-info btn-sm" onclick="syncWithMoodle(<?= $course['id'] ?>)">
                            <i class="bi bi-arrow-repeat"></i> Sync with Moodle
                        </button>
                    <?php endif; ?>
                    
                    <a href="/admin/courses/<?= $course['id'] ?>/delete" 
                       class="btn btn-outline-danger btn-sm"
                       onclick="return confirm('Are you sure you want to delete this course? This action cannot be undone.')">
                        <i class="bi bi-trash"></i> Delete Course
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recently Enrolled Students -->
<?php if (!empty($enrolledStudents)): ?>
<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Recent Enrollments</h5>
        <a href="/admin/courses/<?= $course['id'] ?>/enrollments" class="btn btn-sm btn-outline-primary">
            View All <?= $enrollmentCount ?> Students
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Email</th>
                        <th>Enrolled</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $recentStudents = array_slice($enrolledStudents, 0, 5);
                    foreach ($recentStudents as $student): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 24px; height: 24px; font-size: 12px;">
                                        <?= strtoupper(substr($student['name'], 0, 1)) ?>
                                    </div>
                                    <?= esc($student['name']) ?>
                                </div>
                            </td>
                            <td><?= esc($student['email']) ?></td>
                            <td><?= date('M j, Y', strtotime($student['enrollment_date'])) ?></td>
                            <td>
                                <span class="badge bg-<?= $student['enrollment_status'] === 'active' ? 'success' : 'secondary' ?> text-xs">
                                    <?= ucfirst($student['enrollment_status']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function syncWithMoodle(courseId) {
    // This would make an AJAX call to sync with Moodle
    // For now, just show a placeholder message
    alert('Moodle sync functionality would be implemented here');
}
</script>
<?= $this->endSection() ?>