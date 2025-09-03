<?= $this->extend('layouts/auth') ?>

<?= $this->section('title') ?>Register<?= $this->endSection() ?>
<?= $this->section('auth-title') ?>Create a new account<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <form action="/auth/register" method="POST">
        <?= csrf_field() ?>
        
        <div class="mb-3">
            <label for="name" class="form-label">Full Name</label>
            <input type="text" 
                   class="form-control <?= session('errors.name') ? 'is-invalid' : '' ?>" 
                   id="name" 
                   name="name" 
                   value="<?= old('name') ?>" 
                   required 
                   autofocus>
            <?php if (session('errors.name')): ?>
                <div class="invalid-feedback">
                    <?= session('errors.name') ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" 
                   class="form-control <?= session('errors.email') ? 'is-invalid' : '' ?>" 
                   id="email" 
                   name="email" 
                   value="<?= old('email') ?>" 
                   required>
            <?php if (session('errors.email')): ?>
                <div class="invalid-feedback">
                    <?= session('errors.email') ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" 
                   class="form-control <?= session('errors.password') ? 'is-invalid' : '' ?>" 
                   id="password" 
                   name="password" 
                   required>
            <small class="form-text text-muted">
                Password must be at least 8 characters long.
            </small>
            <?php if (session('errors.password')): ?>
                <div class="invalid-feedback">
                    <?= session('errors.password') ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="mb-3">
            <label for="password_confirm" class="form-label">Confirm Password</label>
            <input type="password" 
                   class="form-control <?= session('errors.password_confirm') ? 'is-invalid' : '' ?>" 
                   id="password_confirm" 
                   name="password_confirm" 
                   required>
            <?php if (session('errors.password_confirm')): ?>
                <div class="invalid-feedback">
                    <?= session('errors.password_confirm') ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
            <label class="form-check-label" for="terms">
                I agree to the <a href="/terms" target="_blank">Terms and Conditions</a>
            </label>
        </div>
        
        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary">Create Account</button>
        </div>
        
        <div class="mt-3 text-center">
            <p>
                Already have an account? 
                <a href="/auth/login" class="text-decoration-none">Sign in here</a>
            </p>
        </div>
    </form>
<?= $this->endSection() ?>