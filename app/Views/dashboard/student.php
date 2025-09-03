<?= $this->extend('layouts/app') ?>

<?= $this->section('title') ?>Student Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <h1 class="mb-4">Welcome, <?= session()->get('name') ?>!</h1>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">My Enrolled Courses</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($enrolledCourses)): ?>
                        <div class="row">
                            <?php foreach ($enrolledCourses as $course): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title"><?= esc($course['title']) ?></h6>
                                            <p class="card-text text-muted small"><?= esc($course['description']) ?></p>
                                            <a href="/courses/<?= $course['course_id'] ?>" class="btn btn-sm btn-primary">View Course</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">You are not enrolled in any courses yet.</p>
                        <a href="/courses" class="btn btn-primary">Browse Courses</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Available Courses</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($availableCourses)): ?>
                        <div class="list-group">
                            <?php foreach ($availableCourses as $course): ?>
                                <a href="/courses/<?= $course['id'] ?>" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?= esc($course['title']) ?></h6>
                                        <small>View Details</small>
                                    </div>
                                    <p class="mb-1 text-muted small"><?= esc(substr($course['description'], 0, 100)) ?>...</p>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No courses available at the moment.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">My Profile</h5>
                </div>
                <div class="card-body">
                    <p><strong>Name:</strong> <?= session()->get('name') ?></p>
                    <p><strong>Email:</strong> <?= session()->get('email') ?></p>
                    <p><strong>Role:</strong> Student</p>
                    <a href="/profile" class="btn btn-sm btn-outline-primary">Edit Profile</a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Stats</h5>
                </div>
                <div class="card-body">
                    <p><strong>Enrolled Courses:</strong> <?= count($enrolledCourses ?? []) ?></p>
                    <p><strong>Completed Courses:</strong> 0</p>
                    <p><strong>Average Grade:</strong> N/A</p>
                </div>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>