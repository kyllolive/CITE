<?= $this->extend('layouts/app') ?>

<?= $this->section('title') ?><?= $title ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><?= $title ?></h1>
    <a href="/admin/courses" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Courses
    </a>
</div>

<div class="card">
    <div class="card-body">
        <?= form_open('/admin/courses', ['class' => 'needs-validation', 'novalidate' => '']) ?>
        
        <div class="row">
            <div class="col-md-8">
                <div class="mb-3">
                    <label for="title" class="form-label">Course Title <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control <?= $validation->hasError('title') ? 'is-invalid' : '' ?>" 
                           id="title" 
                           name="title" 
                           value="<?= old('title') ?>" 
                           required>
                    <?php if ($validation->hasError('title')): ?>
                        <div class="invalid-feedback">
                            <?= $validation->getError('title') ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                    <select class="form-select <?= $validation->hasError('status') ? 'is-invalid' : '' ?>" 
                            id="status" 
                            name="status" 
                            required>
                        <option value="">Select Status</option>
                        <option value="active" <?= old('status') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= old('status') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        <option value="archived" <?= old('status') === 'archived' ? 'selected' : '' ?>>Archived</option>
                    </select>
                    <?php if ($validation->hasError('status')): ?>
                        <div class="invalid-feedback">
                            <?= $validation->getError('status') ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
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

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="instructor_id" class="form-label">Instructor <span class="text-danger">*</span></label>
                    <select class="form-select <?= $validation->hasError('instructor_id') ? 'is-invalid' : '' ?>" 
                            id="instructor_id" 
                            name="instructor_id" 
                            required>
                        <option value="">Select Instructor</option>
                        <?php foreach ($instructors as $instructor): ?>
                            <option value="<?= $instructor['id'] ?>" <?= old('instructor_id') == $instructor['id'] ? 'selected' : '' ?>>
                                <?= esc($instructor['name']) ?> (<?= esc($instructor['email']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($validation->hasError('instructor_id')): ?>
                        <div class="invalid-feedback">
                            <?= $validation->getError('instructor_id') ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="moodle_id" class="form-label">Moodle Course ID</label>
                    <input type="text" 
                           class="form-control" 
                           id="moodle_id" 
                           name="moodle_id" 
                           value="<?= old('moodle_id') ?>" 
                           placeholder="Optional - Link to Moodle course">
                    <div class="form-text">Link this course to a Moodle course by entering the Moodle course ID.</div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" 
                           class="form-control <?= $validation->hasError('start_date') ? 'is-invalid' : '' ?>" 
                           id="start_date" 
                           name="start_date" 
                           value="<?= old('start_date') ?>">
                    <?php if ($validation->hasError('start_date')): ?>
                        <div class="invalid-feedback">
                            <?= $validation->getError('start_date') ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" 
                           class="form-control <?= $validation->hasError('end_date') ? 'is-invalid' : '' ?>" 
                           id="end_date" 
                           name="end_date" 
                           value="<?= old('end_date') ?>">
                    <?php if ($validation->hasError('end_date')): ?>
                        <div class="invalid-feedback">
                            <?= $validation->getError('end_date') ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Create Course
            </button>
            <a href="/admin/courses" class="btn btn-outline-secondary">Cancel</a>
        </div>

        <?= form_close() ?>
    </div>
</div>

<script>
// Bootstrap form validation
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();

// Date validation - ensure end date is after start date
document.addEventListener('DOMContentLoaded', function() {
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');
    
    function validateDates() {
        if (startDate.value && endDate.value) {
            if (new Date(endDate.value) <= new Date(startDate.value)) {
                endDate.setCustomValidity('End date must be after start date');
            } else {
                endDate.setCustomValidity('');
            }
        } else {
            endDate.setCustomValidity('');
        }
    }
    
    startDate.addEventListener('change', validateDates);
    endDate.addEventListener('change', validateDates);
});
</script>
<?= $this->endSection() ?>