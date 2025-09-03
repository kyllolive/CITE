<?= $this->extend('layouts/app') ?>

<?= $this->section('title') ?>Instructor Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <h1 class="mb-4">Instructor Dashboard</h1>
    
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">My Courses</h5>
                    <h3 class="card-text"><?= count($myCourses ?? []) ?></h3>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Total Students</h5>
                    <h3 class="card-text"><?= $totalStudents ?? 0 ?></h3>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">Active Courses</h5>
                    <h3 class="card-text"><?= count(array_filter($myCourses ?? [], function($c) { return $c['status'] === 'active'; })) ?></h3>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">Pending Grades</h5>
                    <h3 class="card-text">0</h3>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">My Courses</h5>
            <a href="/courses/create" class="btn btn-sm btn-primary">Create New Course</a>
        </div>
        <div class="card-body">
            <?php if (!empty($myCourses)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Course Title</th>
                                <th>Status</th>
                                <th>Students</th>
                                <th>Start Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($myCourses as $course): ?>
                                <tr>
                                    <td><?= esc($course['title']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $course['status'] === 'active' ? 'success' : 'secondary' ?>">
                                            <?= ucfirst($course['status']) ?>
                                        </span>
                                    </td>
                                    <td>N/A</td>
                                    <td><?= $course['start_date'] ?? 'Not set' ?></td>
                                    <td>
                                        <a href="/courses/<?= $course['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                                        <a href="/courses/<?= $course['id'] ?>/edit" class="btn btn-sm btn-outline-secondary">Edit</a>
                                        <a href="/courses/<?= $course['id'] ?>/students" class="btn btn-sm btn-outline-info">Students</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">You haven't created any courses yet.</p>
                <a href="/courses/create" class="btn btn-primary">Create Your First Course</a>
            <?php endif; ?>
        </div>
    </div>
<?= $this->endSection() ?>