<?= $this->extend('layouts/app') ?>

<?= $this->section('title') ?><?= $title ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><?= $title ?></h1>
    <div class="d-flex gap-2">
        <a href="/admin/moodle" class="btn btn-outline-info">
            <i class="bi bi-mortarboard"></i> Manage Moodle
        </a>
        <a href="/admin/courses/new" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add New Course
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($courses)): ?>
            <div class="text-center py-4">
                <p class="text-muted">No courses found.</p>
                <a href="/admin/courses/new" class="btn btn-primary">Create First Course</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Instructor</th>
                            <th>Status</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Enrollments</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $course): ?>
                            <tr>
                                <td><?= $course['id'] ?></td>
                                <td>
                                    <div>
                                        <strong><?= esc($course['title']) ?></strong>
                                        <?php if ($course['moodle_id']): ?>
                                            <span class="badge bg-info ms-1" title="Linked to Moodle">M</span>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted"><?= esc(substr($course['description'], 0, 100)) ?>...</small>
                                </td>
                                <td>
                                    <?php if ($course['instructor_name']): ?>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-warning text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                                <?= strtoupper(substr($course['instructor_name'], 0, 1)) ?>
                                            </div>
                                            <?= esc($course['instructor_name']) ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">No instructor assigned</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $course['status'] === 'active' ? 'success' : ($course['status'] === 'archived' ? 'secondary' : 'warning') ?>">
                                        <?= ucfirst($course['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?= $course['start_date'] ? date('M j, Y', strtotime($course['start_date'])) : '-' ?>
                                </td>
                                <td>
                                    <?= $course['end_date'] ? date('M j, Y', strtotime($course['end_date'])) : '-' ?>
                                </td>
                                <td>
                                    <a href="/admin/courses/<?= $course['id'] ?>/enrollments" class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-people"></i> View
                                    </a>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="/admin/courses/<?= $course['id'] ?>" class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="/admin/courses/<?= $course['id'] ?>/edit" class="btn btn-sm btn-outline-secondary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="/admin/courses/<?= $course['id'] ?>/enrollments" class="btn btn-sm btn-outline-info" title="Manage Enrollments">
                                            <i class="bi bi-people"></i>
                                        </a>
                                        <?php if ($course['status'] === 'active'): ?>
                                            <a href="/admin/courses/<?= $course['id'] ?>/archive" 
                                               class="btn btn-sm btn-outline-warning" 
                                               title="Archive"
                                               onclick="return confirm('Are you sure you want to archive this course?')">
                                                <i class="bi bi-archive"></i>
                                            </a>
                                        <?php elseif ($course['status'] === 'inactive' || $course['status'] === 'archived'): ?>
                                            <a href="/admin/courses/<?= $course['id'] ?>/activate" 
                                               class="btn btn-sm btn-outline-success" 
                                               title="Activate">
                                                <i class="bi bi-play-circle"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="/admin/courses/<?= $course['id'] ?>/delete" 
                                           class="btn btn-sm btn-outline-danger" 
                                           title="Delete"
                                           onclick="return confirm('Are you sure you want to delete this course? This action cannot be undone.')">
                                            <i class="bi bi-trash"></i>
                                        </a>
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

<div class="row mt-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-primary">Total Courses</h5>
                <h2 class="text-primary"><?= count($courses) ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-success">Active</h5>
                <h2 class="text-success"><?= count(array_filter($courses, fn($c) => $c['status'] === 'active')) ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-warning">Inactive</h5>
                <h2 class="text-warning"><?= count(array_filter($courses, fn($c) => $c['status'] === 'inactive')) ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-secondary">Archived</h5>
                <h2 class="text-secondary"><?= count(array_filter($courses, fn($c) => $c['status'] === 'archived')) ?></h2>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>