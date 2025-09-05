<?= $this->extend('layouts/app') ?>

<?= $this->section('title') ?><?= $title ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><?= $title ?></h1>
        <p class="text-muted mb-0">
            <?php if ($badgeCount > 0): ?>
                You have earned <?= $badgeCount ?> badge<?= $badgeCount != 1 ? 's' : '' ?> across your courses
            <?php else: ?>
                You haven't earned any badges yet. Complete courses to earn your first badge!
            <?php endif; ?>
        </p>
    </div>
</div>

<?php if (empty($badges)): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-award text-muted" style="font-size: 4rem;"></i>
            <h4 class="mt-3">No Badges Yet</h4>
            <p class="text-muted">Complete your courses to earn badges and showcase your achievements!</p>
            <a href="/student/courses" class="btn btn-primary">
                <i class="bi bi-book"></i> View My Courses
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="row">
        <?php foreach ($badges as $badge): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 border-success">
                    <div class="card-header bg-success text-white text-center">
                        <i class="bi bi-check-circle"></i> Earned
                    </div>
                    <div class="card-body text-center">
                        <?php if (!empty($badge['image_url'])): ?>
                            <img src="<?= base_url($badge['image_url']) ?>" 
                                 alt="Badge Image" 
                                 class="rounded-circle mb-3" 
                                 style="width: 100px; height: 100px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" 
                                 style="width: 100px; height: 100px;">
                                <i class="bi bi-award" style="font-size: 2.5rem;"></i>
                            </div>
                        <?php endif; ?>
                        
                        <h5 class="card-title"><?= esc($badge['badge_name']) ?></h5>
                        <p class="card-text small text-muted">
                            <i class="bi bi-book"></i> <?= esc($badge['course_title']) ?>
                        </p>
                        <p class="card-text"><?= character_limiter(strip_tags($badge['description']), 100) ?></p>
                        
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="bi bi-calendar"></i> 
                                Earned on <?= date('M j, Y', strtotime($badge['awarded_at'])) ?>
                            </small>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="d-grid gap-2">
                            <a href="/student/badges/<?= $badge['badge_id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> View Details
                            </a>
                            <a href="/student/badges/certificate/<?= $badge['id'] ?>" class="btn btn-sm btn-success">
                                <i class="bi bi-download"></i> Certificate
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Badge Statistics</h5>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-3">
                    <div class="display-4 text-primary"><?= $badgeCount ?></div>
                    <p class="text-muted">Total Badges</p>
                </div>
                <div class="col-md-3">
                    <div class="display-4 text-success">
                        <?= count(array_unique(array_column($badges, 'course_title'))) ?>
                    </div>
                    <p class="text-muted">Courses</p>
                </div>
                <div class="col-md-3">
                    <div class="display-4 text-info">
                        <?php
                        $thisMonth = count(array_filter($badges, function($badge) {
                            return date('Y-m', strtotime($badge['awarded_at'])) === date('Y-m');
                        }));
                        echo $thisMonth;
                        ?>
                    </div>
                    <p class="text-muted">This Month</p>
                </div>
                <div class="col-md-3">
                    <div class="display-4 text-warning">
                        <?php
                        $recent = count(array_filter($badges, function($badge) {
                            return strtotime($badge['awarded_at']) > strtotime('-30 days');
                        }));
                        echo $recent;
                        ?>
                    </div>
                    <p class="text-muted">Recent</p>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>