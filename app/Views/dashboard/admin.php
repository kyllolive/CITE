<?= $this->extend('layouts/app') ?>

<?= $this->section('title') ?>Admin Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <h1 class="mb-4">Admin Dashboard</h1>
    
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Total Users</h5>
                    <h3 class="card-text"><?= $totalUsers ?? 0 ?></h3>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Total Courses</h5>
                    <h3 class="card-text"><?= $totalCourses ?? 0 ?></h3>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">Active Enrollments</h5>
                    <h3 class="card-text"><?= $totalEnrollments ?? 0 ?></h3>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">Instructors</h5>
                    <h3 class="card-text"><?= $totalInstructors ?? 0 ?></h3>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/admin/users" class="btn btn-outline-primary">Manage Users</a>
                        <a href="/admin/courses" class="btn btn-outline-success">Manage Courses</a>
                        <a href="/admin/enrollments" class="btn btn-outline-info">Manage Enrollments</a>
                        <a href="/admin/settings" class="btn btn-outline-warning">System Settings</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Activities</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">No recent activities to display.</p>
                </div>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>