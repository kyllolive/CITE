<?= $this->extend('layouts/app') ?>

<?= $this->section('title') ?><?= $title ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><?= $title ?></h4>
                <a href="/instructor/badges/course/<?= $course['id'] ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Course
                </a>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-3 text-center">
                        <?php if (!empty($badge['image_url'])): ?>
                            <img src="<?= base_url($badge['image_url']) ?>" 
                                 alt="Badge Image" 
                                 class="rounded-circle mb-3" 
                                 style="width: 120px; height: 120px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" 
                                 style="width: 120px; height: 120px;">
                                <i class="bi bi-award" style="font-size: 3rem;"></i>
                            </div>
                        <?php endif; ?>
                        <h5><?= esc($badge['name']) ?></h5>
                    </div>
                    <div class="col-md-9">
                        <h6>Badge Description</h6>
                        <p><?= nl2br(esc($badge['description'])) ?></p>
                        
                        <?php if (!empty($badge['criteria'])): ?>
                            <h6>Award Criteria</h6>
                            <p><?= nl2br(esc($badge['criteria'])) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <?= form_open("/instructor/badges/course/{$course['id']}/badge/{$badge['id']}/award") ?>
                
                <div class="mb-3">
                    <label for="notes" class="form-label">Award Notes (Optional)</label>
                    <textarea class="form-control" 
                              id="notes" 
                              name="notes" 
                              rows="3" 
                              placeholder="Add notes about why these students earned this badge..."></textarea>
                </div>

                <h6>Select Students to Award Badge</h6>
                <p class="text-muted">Choose the students who have earned this badge:</p>

                <?php if (empty($students)): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> No students enrolled in this course.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="50">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="selectAll">
                                            <label class="form-check-label" for="selectAll">All</label>
                                        </div>
                                    </th>
                                    <th>Student</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): ?>
                                    <tr class="<?= in_array($student['id'], $awardedUserIds) ? 'table-warning' : '' ?>">
                                        <td>
                                            <?php if (in_array($student['id'], $awardedUserIds)): ?>
                                                <i class="bi bi-check-circle text-success" title="Already awarded"></i>
                                            <?php else: ?>
                                                <div class="form-check">
                                                    <input type="checkbox" 
                                                           class="form-check-input student-checkbox" 
                                                           name="students[]" 
                                                           value="<?= $student['id'] ?>" 
                                                           id="student_<?= $student['id'] ?>">
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                                     style="width: 32px; height: 32px;">
                                                    <?= strtoupper(substr($student['name'], 0, 1)) ?>
                                                </div>
                                                <label for="student_<?= $student['id'] ?>" class="form-check-label mb-0">
                                                    <?= esc($student['name']) ?>
                                                </label>
                                            </div>
                                        </td>
                                        <td><?= esc($student['email']) ?></td>
                                        <td>
                                            <?php if (in_array($student['id'], $awardedUserIds)): ?>
                                                <span class="badge bg-success">Already Awarded</span>
                                            <?php else: ?>
                                                <span class="badge bg-primary">Eligible</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <a href="/instructor/badges/course/<?= $course['id'] ?>" class="btn btn-secondary me-md-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-award"></i> Award Badge to Selected Students
                        </button>
                    </div>
                <?php endif; ?>

                <?= form_close() ?>
            </div>
        </div>
    </div>
</div>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const studentCheckboxes = document.querySelectorAll('.student-checkbox');
    
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            studentCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
        
        studentCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const checkedCount = document.querySelectorAll('.student-checkbox:checked').length;
                selectAllCheckbox.checked = checkedCount === studentCheckboxes.length;
                selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < studentCheckboxes.length;
            });
        });
    }
});
</script>
<?= $this->endSection() ?>
<?= $this->endSection() ?>