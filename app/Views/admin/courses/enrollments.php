<?= $this->extend('layouts/app') ?>

<?= $this->section('title') ?><?= $title ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><?= $title ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/admin/courses">Courses</a></li>
                <li class="breadcrumb-item"><a href="/admin/courses/<?= $course['id'] ?>"><?= esc($course['title']) ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Enrollments</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="/admin/courses/<?= $course['id'] ?>" class="btn btn-outline-primary">
            <i class="bi bi-eye"></i> View Course
        </a>
        <a href="/admin/courses" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Courses
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Enrolled Students -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Enrolled Students (<?= count($enrolledStudents) ?>)</h5>
            </div>
            <div class="card-body">
                <?php if (empty($enrolledStudents)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-people display-4 text-muted"></i>
                        <p class="text-muted mt-2">No students enrolled in this course yet.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Email</th>
                                    <th>Enrolled Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($enrolledStudents as $student): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                                    <?= strtoupper(substr($student['name'], 0, 1)) ?>
                                                </div>
                                                <?= esc($student['name']) ?>
                                            </div>
                                        </td>
                                        <td><?= esc($student['email']) ?></td>
                                        <td><?= date('M j, Y', strtotime($student['enrollment_date'])) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $student['enrollment_status'] === 'active' ? 'success' : 'secondary' ?>">
                                                <?= ucfirst($student['enrollment_status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="/admin/users/<?= $student['id'] ?>" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   title="View Profile">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="/admin/courses/<?= $course['id'] ?>/unenroll/<?= $student['id'] ?>" 
                                                   class="btn btn-sm btn-outline-danger" 
                                                   title="Unenroll"
                                                   onclick="return confirm('Are you sure you want to unenroll <?= esc($student['name']) ?> from this course?')">
                                                    <i class="bi bi-person-dash"></i>
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
    </div>
    
    <div class="col-md-4">
        <!-- Course Info -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Course Information</h5>
            </div>
            <div class="card-body">
                <h6 class="mb-2"><?= esc($course['title']) ?></h6>
                <p class="text-muted small mb-2"><?= esc(substr($course['description'], 0, 100)) ?>...</p>
                <div class="mb-2">
                    <strong>Instructor:</strong> <?= esc($course['instructor_name']) ?>
                </div>
                <div class="mb-2">
                    <strong>Status:</strong>
                    <span class="badge bg-<?= $course['status'] === 'active' ? 'success' : ($course['status'] === 'archived' ? 'secondary' : 'warning') ?> ms-1">
                        <?= ucfirst($course['status']) ?>
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Enroll New Student -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Enroll New Student</h5>
            </div>
            <div class="card-body">
                <?php if (empty($availableStudents)): ?>
                    <p class="text-muted">All active students are already enrolled in this course.</p>
                <?php else: ?>
                    <?= form_open('/admin/courses/' . $course['id'] . '/enroll') ?>
                    <div class="mb-3">
                        <label for="student_id" class="form-label">Select Student</label>
                        <select class="form-select" id="student_id" name="student_id" required>
                            <option value="">Choose a student...</option>
                            <?php foreach ($availableStudents as $student): ?>
                                <option value="<?= $student['id'] ?>">
                                    <?= esc($student['name']) ?> (<?= esc($student['email']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-person-plus"></i> Enroll Student
                        </button>
                    </div>
                    <?= form_close() ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Enrollment Statistics -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Statistics</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <h4 class="text-success mb-1"><?= count($enrolledStudents) ?></h4>
                            <small class="text-muted">Enrolled</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h4 class="text-info mb-1"><?= count($availableStudents) ?></h4>
                        <small class="text-muted">Available</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>