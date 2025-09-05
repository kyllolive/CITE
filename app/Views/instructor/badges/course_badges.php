<?= $this->extend('layouts/app') ?>

<?= $this->section('title') ?><?= $title ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><?= $title ?></h1>
        <p class="text-muted mb-0">Award badges to students who have completed your course</p>
    </div>
    <a href="/instructor/badges" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to All Badges
    </a>
</div>

<?php if (empty($badges)): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-award text-muted" style="font-size: 4rem;"></i>
            <h4 class="mt-3">No Badges Available</h4>
            <p class="text-muted">There are no active badges for this course yet. Contact your administrator to create badges for this course.</p>
        </div>
    </div>
<?php else: ?>
    <div class="row">
        <?php foreach ($badges as $badge): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span class="badge bg-<?= $badge['is_active'] ? 'success' : 'secondary' ?>">
                            <?= $badge['is_active'] ? 'Active' : 'Inactive' ?>
                        </span>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                Actions
                            </button>
                            <ul class="dropdown-menu">
                                <?php if ($badge['is_active']): ?>
                                    <li><a class="dropdown-item" href="/instructor/badges/course/<?= $course['id'] ?>/badge/<?= $badge['id'] ?>/award">
                                        <i class="bi bi-award"></i> Award Badge</a></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="/instructor/badges/course/<?= $course['id'] ?>/badge/<?= $badge['id'] ?>/recipients">
                                    <i class="bi bi-people"></i> View Recipients</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <?php if (!empty($badge['image_url'])): ?>
                                <img src="<?= base_url($badge['image_url']) ?>" 
                                     alt="Badge Image" 
                                     class="rounded-circle" 
                                     style="width: 80px; height: 80px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" 
                                     style="width: 80px; height: 80px;">
                                    <i class="bi bi-award" style="font-size: 2rem;"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <h5 class="card-title text-center"><?= esc($badge['name']) ?></h5>
                        <p class="card-text"><?= character_limiter(strip_tags($badge['description']), 100) ?></p>
                        
                        <?php if (!empty($badge['criteria'])): ?>
                            <div class="mb-2">
                                <small class="text-muted"><strong>Criteria:</strong></small>
                                <p class="small"><?= character_limiter(strip_tags($badge['criteria']), 80) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="row text-center">
                            <div class="col">
                                <small class="text-muted">Awards</small>
                                <div class="fw-bold">
                                    <?php
                                    $awardModel = new \App\Models\BadgeAward();
                                    echo $awardModel->where('badge_id', $badge['id'])->countAllResults();
                                    ?>
                                </div>
                            </div>
                            <div class="col">
                                <small class="text-muted">Students</small>
                                <div class="fw-bold"><?= count($students) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Enrolled Students</h5>
        </div>
        <div class="card-body">
            <?php if (empty($students)): ?>
                <p class="text-muted">No students enrolled in this course.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Email</th>
                                <th>Enrolled</th>
                                <th>Badges Earned</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                                 style="width: 32px; height: 32px;">
                                                <?= strtoupper(substr($student['name'], 0, 1)) ?>
                                            </div>
                                            <?= esc($student['name']) ?>
                                        </div>
                                    </td>
                                    <td><?= esc($student['email']) ?></td>
                                    <td><?= date('M j, Y', strtotime($student['enrollment_date'])) ?></td>
                                    <td>
                                        <?php
                                        $badgeAwardModel = new \App\Models\BadgeAward();
                                        $studentCourseAwards = $badgeAwardModel
                                            ->select('badge_awards.*')
                                            ->join('badges', 'badges.id = badge_awards.badge_id')
                                            ->where('badge_awards.user_id', $student['id'])
                                            ->where('badges.course_id', $course['id'])
                                            ->countAllResults();
                                        echo $studentCourseAwards;
                                        ?>
                                    </td>
                                    <td>
                                        <a href="/instructor/badges/course/<?= $course['id'] ?>/student/<?= $student['id'] ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            View Badges
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>