<?= $this->extend('layouts/app') ?>

<?= $this->section('title') ?>Unauthorized<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-exclamation-triangle text-warning" style="font-size: 4rem;"></i>
                    <h1 class="mt-3">403 - Unauthorized</h1>
                    <p class="text-muted">You do not have permission to access this resource.</p>
                    <div class="mt-4">
                        <a href="/dashboard" class="btn btn-primary">Go to Dashboard</a>
                        <a href="/" class="btn btn-outline-secondary">Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>