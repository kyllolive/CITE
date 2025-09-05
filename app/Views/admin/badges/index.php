<?= $this->extend('layouts/app') ?>

<?= $this->section('title') ?><?= $title ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><?= $title ?></h1>
    <a href="/admin/badges/new" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Create New Badge
    </a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($badges)): ?>
            <div class="text-center py-4">
                <i class="bi bi-award text-muted" style="font-size: 4rem;"></i>
                <p class="text-muted mt-3">No badges found.</p>
                <a href="/admin/badges/new" class="btn btn-primary">Create First Badge</a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($badges as $badge): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-<?= $badge['is_active'] ? 'success' : 'secondary' ?>">
                                        <?= $badge['is_active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="/admin/badges/<?= $badge['id'] ?>"><i class="bi bi-eye"></i> View</a></li>
                                        <li><a class="dropdown-item" href="/admin/badges/<?= $badge['id'] ?>/edit"><i class="bi bi-pencil"></i> Edit</a></li>
                                        <li><a class="dropdown-item" href="/admin/badges/<?= $badge['id'] ?>/awards"><i class="bi bi-award"></i> Awards</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <?php if ($badge['is_active']): ?>
                                            <li><a class="dropdown-item text-warning" href="/admin/badges/<?= $badge['id'] ?>/deactivate" 
                                                   onclick="return confirm('Deactivate this badge?')">
                                                   <i class="bi bi-pause-circle"></i> Deactivate</a></li>
                                        <?php else: ?>
                                            <li><a class="dropdown-item text-success" href="/admin/badges/<?= $badge['id'] ?>/activate">
                                                   <i class="bi bi-play-circle"></i> Activate</a></li>
                                        <?php endif; ?>
                                        <li><a class="dropdown-item text-danger" href="/admin/badges/<?= $badge['id'] ?>/delete" 
                                               onclick="return confirm('Delete this badge? This action cannot be undone.')">
                                               <i class="bi bi-trash"></i> Delete</a></li>
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
                                <p class="card-text text-muted small">
                                    <i class="bi bi-book"></i> <?= esc($badge['course_title']) ?>
                                </p>
                                <p class="card-text"><?= character_limiter(strip_tags($badge['description']), 100) ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="bi bi-person"></i> <?= esc($badge['creator_name']) ?>
                                    </small>
                                    <small class="text-muted">
                                        <i class="bi bi-calendar"></i> <?= date('M j, Y', strtotime($badge['created_at'])) ?>
                                    </small>
                                </div>
                            </div>
                            <div class="card-footer bg-light">
                                <div class="row text-center">
                                    <div class="col">
                                        <small class="text-muted">Awards</small>
                                        <div class="fw-bold"><?= $badge['award_count'] ?? 0 ?></div>
                                    </div>
                                    <div class="col">
                                        <small class="text-muted">Moodle</small>
                                        <div class="fw-bold">
                                            <?php if ($badge['moodle_badge_id']): ?>
                                                <i class="bi bi-check-circle text-success"></i>
                                            <?php else: ?>
                                                <i class="bi bi-x-circle text-muted"></i>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-primary">Total Badges</h5>
                <h2 class="text-primary"><?= count($badges) ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-success">Active Badges</h5>
                <h2 class="text-success"><?= count(array_filter($badges, fn($b) => $b['is_active'])) ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-warning">Inactive Badges</h5>
                <h2 class="text-warning"><?= count(array_filter($badges, fn($b) => !$b['is_active'])) ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-info">Synced with Moodle</h5>
                <h2 class="text-info"><?= count(array_filter($badges, fn($b) => !empty($b['moodle_badge_id']))) ?></h2>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>