<?= $this->extend('layouts/app') ?>

<?= $this->section('title') ?><?= $title ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><?= $title ?></h1>
    <a href="/admin/users/new" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Add New User
    </a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($users)): ?>
            <div class="text-center py-4">
                <p class="text-muted">No users found.</p>
                <a href="/admin/users/new" class="btn btn-primary">Create First User</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= $user['id'] ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                        </div>
                                        <?= esc($user['name']) ?>
                                    </div>
                                </td>
                                <td><?= esc($user['email']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'instructor' ? 'warning' : 'info') ?>">
                                        <?= ucfirst($user['role']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($user['is_active']): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="/admin/users/<?= $user['id'] ?>" class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="/admin/users/<?= $user['id'] ?>/edit" class="btn btn-sm btn-outline-secondary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <?php if ($user['is_active']): ?>
                                            <a href="/admin/users/<?= $user['id'] ?>/deactivate" 
                                               class="btn btn-sm btn-outline-warning" 
                                               title="Deactivate"
                                               onclick="return confirm('Are you sure you want to deactivate this user?')">
                                                <i class="bi bi-pause-circle"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="/admin/users/<?= $user['id'] ?>/activate" 
                                               class="btn btn-sm btn-outline-success" 
                                               title="Activate">
                                                <i class="bi bi-play-circle"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="/admin/users/<?= $user['id'] ?>/delete" 
                                           class="btn btn-sm btn-outline-danger" 
                                           title="Delete"
                                           onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
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
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-primary">Total Users</h5>
                <h2 class="text-primary"><?= count($users) ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-success">Active Users</h5>
                <h2 class="text-success"><?= count(array_filter($users, fn($u) => $u['is_active'])) ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-warning">Inactive Users</h5>
                <h2 class="text-warning"><?= count(array_filter($users, fn($u) => !$u['is_active'])) ?></h2>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>