<?= $this->extend('layouts/app') ?>

<?= $this->section('title') ?><?= $title ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><?= $title ?></h4>
                <div class="btn-group">
                    <a href="/admin/users/<?= $user['id'] ?>/edit" class="btn btn-outline-primary">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <a href="/admin/users" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Users
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center mb-4">
                        <div class="avatar-lg bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 100px; height: 100px; font-size: 2rem;">
                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                        </div>
                        <h5 class="mb-1"><?= esc($user['name']) ?></h5>
                        <span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'instructor' ? 'warning' : 'info') ?> mb-2">
                            <?= ucfirst($user['role']) ?>
                        </span>
                        <div>
                            <?php if ($user['is_active']): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="col-md-9">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted">Basic Information</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td class="fw-bold">User ID:</td>
                                        <td><?= $user['id'] ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Email:</td>
                                        <td><?= esc($user['email']) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Role:</td>
                                        <td><?= ucfirst($user['role']) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Status:</td>
                                        <td>
                                            <?php if ($user['is_active']): ?>
                                                <span class="text-success"><i class="bi bi-check-circle"></i> Active</span>
                                            <?php else: ?>
                                                <span class="text-secondary"><i class="bi bi-pause-circle"></i> Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Email Verified:</td>
                                        <td>
                                            <?php if ($user['email_verified_at']): ?>
                                                <span class="text-success">
                                                    <i class="bi bi-check-circle"></i> 
                                                    <?= date('M j, Y', strtotime($user['email_verified_at'])) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-warning"><i class="bi bi-exclamation-triangle"></i> Not verified</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            
                            <div class="col-md-6">
                                <h6 class="text-muted">Account Details</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td class="fw-bold">Created:</td>
                                        <td><?= date('M j, Y g:i A', strtotime($user['created_at'])) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Last Updated:</td>
                                        <td><?= date('M j, Y g:i A', strtotime($user['updated_at'])) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Remember Token:</td>
                                        <td>
                                            <?php if ($user['remember_token']): ?>
                                                <span class="text-success"><i class="bi bi-check-circle"></i> Active</span>
                                            <?php else: ?>
                                                <span class="text-muted">None</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <?php if ($user['moodle_id'] || $user['moodle_username'] || $user['last_moodle_sync']): ?>
                            <div class="mt-4">
                                <h6 class="text-muted">Moodle Integration</h6>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <strong>Moodle ID:</strong><br>
                                                <?= $user['moodle_id'] ?? '<span class="text-muted">Not set</span>' ?>
                                            </div>
                                            <div class="col-md-4">
                                                <strong>Moodle Username:</strong><br>
                                                <?= $user['moodle_username'] ?? '<span class="text-muted">Not set</span>' ?>
                                            </div>
                                            <div class="col-md-4">
                                                <strong>Last Sync:</strong><br>
                                                <?php if ($user['last_moodle_sync']): ?>
                                                    <?= date('M j, Y g:i A', strtotime($user['last_moodle_sync'])) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Never</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="mt-4 pt-4 border-top">
                    <div class="d-flex justify-content-between">
                        <div class="btn-group">
                            <?php if ($user['is_active']): ?>
                                <a href="/admin/users/<?= $user['id'] ?>/deactivate" 
                                   class="btn btn-warning"
                                   onclick="return confirm('Are you sure you want to deactivate this user?')">
                                    <i class="bi bi-pause-circle"></i> Deactivate
                                </a>
                            <?php else: ?>
                                <a href="/admin/users/<?= $user['id'] ?>/activate" class="btn btn-success">
                                    <i class="bi bi-play-circle"></i> Activate
                                </a>
                            <?php endif; ?>
                        </div>
                        
                        <a href="/admin/users/<?= $user['id'] ?>/delete" 
                           class="btn btn-danger"
                           onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                            <i class="bi bi-trash"></i> Delete User
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>