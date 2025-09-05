<?= $this->extend('layouts/app') ?>

<?= $this->section('title') ?><?= $title ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><?= $title ?></h4>
                <a href="/admin/users" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Users
                </a>
            </div>
            <div class="card-body">
                <?= form_open('/admin/users') ?>
                
                <div class="mb-3">
                    <label for="name" class="form-label">Full Name *</label>
                    <input type="text" 
                           class="form-control <?= $validation->hasError('name') ? 'is-invalid' : '' ?>" 
                           id="name" 
                           name="name" 
                           value="<?= old('name') ?>" 
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
                           value="<?= old('email') ?>" 
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
                            <label for="password" class="form-label">Password *</label>
                            <input type="password" 
                                   class="form-control <?= $validation->hasError('password') ? 'is-invalid' : '' ?>" 
                                   id="password" 
                                   name="password" 
                                   required>
                            <?php if ($validation->hasError('password')): ?>
                                <div class="invalid-feedback">
                                    <?= $validation->getError('password') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="password_confirm" class="form-label">Confirm Password *</label>
                            <input type="password" 
                                   class="form-control <?= $validation->hasError('password_confirm') ? 'is-invalid' : '' ?>" 
                                   id="password_confirm" 
                                   name="password_confirm" 
                                   required>
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
                        <option value="admin" <?= old('role') === 'admin' ? 'selected' : '' ?>>Administrator</option>
                        <option value="instructor" <?= old('role') === 'instructor' ? 'selected' : '' ?>>Instructor</option>
                        <option value="student" <?= old('role') === 'student' ? 'selected' : '' ?>>Student</option>
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
                               <?= old('is_active') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">
                            Account is active
                        </label>
                    </div>
                    <small class="form-text text-muted">
                        Active users can log in and access the system.
                    </small>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="/admin/users" class="btn btn-secondary me-md-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Create User
                    </button>
                </div>

                <?= form_close() ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>