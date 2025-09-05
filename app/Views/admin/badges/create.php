<?= $this->extend('layouts/app') ?>

<?= $this->section('title') ?><?= $title ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><?= $title ?></h4>
                <a href="/admin/badges" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Badges
                </a>
            </div>
            <div class="card-body">
                <?= form_open_multipart('/admin/badges') ?>
                
                <div class="mb-3">
                    <label for="name" class="form-label">Badge Name *</label>
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
                    <label for="description" class="form-label">Description *</label>
                    <textarea class="form-control <?= $validation->hasError('description') ? 'is-invalid' : '' ?>" 
                              id="description" 
                              name="description" 
                              rows="4" 
                              required><?= old('description') ?></textarea>
                    <?php if ($validation->hasError('description')): ?>
                        <div class="invalid-feedback">
                            <?= $validation->getError('description') ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="course_id" class="form-label">Course *</label>
                    <select class="form-select <?= $validation->hasError('course_id') ? 'is-invalid' : '' ?>" 
                            id="course_id" 
                            name="course_id" 
                            required>
                        <option value="">Select Course...</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?= $course['id'] ?>" <?= old('course_id') == $course['id'] ? 'selected' : '' ?>>
                                <?= esc($course['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($validation->hasError('course_id')): ?>
                        <div class="invalid-feedback">
                            <?= $validation->getError('course_id') ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">Badge Image</label>
                    <input type="file" 
                           class="form-control" 
                           id="image" 
                           name="image" 
                           accept="image/*">
                    <small class="form-text text-muted">
                        Upload an image for the badge. Recommended size: 256x256 pixels. Supports PNG, JPG, GIF.
                    </small>
                </div>

                <div class="mb-3">
                    <label for="criteria" class="form-label">Award Criteria</label>
                    <textarea class="form-control" 
                              id="criteria" 
                              name="criteria" 
                              rows="3" 
                              placeholder="Describe the criteria for earning this badge..."><?= old('criteria') ?></textarea>
                    <small class="form-text text-muted">
                        Optional: Describe what students must do to earn this badge.
                    </small>
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
                            Badge is active
                        </label>
                    </div>
                    <small class="form-text text-muted">
                        Active badges can be awarded to students. Inactive badges are hidden from instructors.
                    </small>
                </div>

                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    <strong>Moodle Integration:</strong> If Moodle sync is enabled, this badge will be automatically created in Moodle and linked to the corresponding course.
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="/admin/badges" class="btn btn-secondary me-md-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Create Badge
                    </button>
                </div>

                <?= form_close() ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>