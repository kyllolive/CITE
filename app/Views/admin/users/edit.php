<?= $this->extend('layouts/app') ?>

<?= $this->section('title') ?><?= $title ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><?= $title ?></h4>
                <div class="btn-group">
                    <a href="/admin/users/<?= $user['id'] ?>" class="btn btn-outline-info">
                        <i class="bi bi-eye"></i> View
                    </a>
                    <a href="/admin/users" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Users
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?= form_open('/admin/users/' . $user['id']) ?>
                
                <div class="mb-3">
                    <label for="name" class="form-label">Full Name *</label>
                    <input type="text" 
                           class="form-control <?= $validation->hasError('name') ? 'is-invalid' : '' ?>" 
                           id="name" 
                           name="name" 
                           value="<?= old('name', $user['name']) ?>" 
                           required>
                    <?php if ($validation->hasError('name')): ?>
                        <div class="invalid-feedback">
                            <?= $validation->getError('name') ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address *</label>
                    <input type="email" 
                           class="form-control <?= $validation->hasError('email') ? 'is-invalid' : '' ?>" 
                           id="email" 
                           name="email" 
                           value="<?= old('email', $user['email']) ?>" 
                           required>
                    <?php if ($validation->hasError('email')): ?>
                        <div class="invalid-feedback">
                            <?= $validation->getError('email') ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" 
                                   class="form-control <?= $validation->hasError('password') ? 'is-invalid' : '' ?>" 
                                   id="password" 
                                   name="password">
                            <small class="form-text text-muted">Leave blank to keep current password</small>
                            <?php if ($validation->hasError('password')): ?>
                                <div class="invalid-feedback">
                                    <?= $validation->getError('password') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="password_confirm" class="form-label">Confirm New Password</label>
                            <input type="password" 
                                   class="form-control <?= $validation->hasError('password_confirm') ? 'is-invalid' : '' ?>" 
                                   id="password_confirm" 
                                   name="password_confirm">
                            <?php if ($validation->hasError('password_confirm')): ?>
                                <div class="invalid-feedback">
                                    <?= $validation->getError('password_confirm') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="role" class="form-label">Role *</label>
                    <select class="form-select <?= $validation->hasError('role') ? 'is-invalid' : '' ?>" 
                            id="role" 
                            name="role" 
                            required>
                        <option value="">Select Role...</option>
                        <option value="admin" <?= old('role', $user['role']) === 'admin' ? 'selected' : '' ?>>Administrator</option>
                        <option value="instructor" <?= old('role', $user['role']) === 'instructor' ? 'selected' : '' ?>>Instructor</option>
                        <option value="student" <?= old('role', $user['role']) === 'student' ? 'selected' : '' ?>>Student</option>
                    </select>
                    <?php if ($validation->hasError('role')): ?>
                        <div class="invalid-feedback">
                            <?= $validation->getError('role') ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" 
                               class="form-check-input" 
                               id="is_active" 
                               name="is_active" 
                               value="1" 
                               <?= old('is_active', $user['is_active']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">
                            Account is active
                        </label>
                    </div>
                    <small class="form-text text-muted">
                        Active users can log in and access the system.
                    </small>
                </div>

                <?php if ($user['moodle_id']): ?>
                    <div class="mb-3">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">Moodle Integration</h6>
                                <p class="card-text mb-1">
                                    <strong>Moodle ID:</strong> <?= $user['moodle_id'] ?>
                                </p>
                                <p class="card-text mb-1">
                                    <strong>Moodle Username:</strong> <?= $user['moodle_username'] ?? 'Not set' ?>
                                </p>
                                <p class="card-text mb-0">
                                    <strong>Last Sync:</strong> <?= $user['last_moodle_sync'] ? date('M j, Y g:i A', strtotime($user['last_moodle_sync'])) : 'Never' ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <input type="hidden" name="_method" value="PUT">
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="/admin/users" class="btn btn-secondary me-md-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Update User
                    </button>
                </div>

                <?= form_close() ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>