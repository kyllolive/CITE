<?= $this->extend('layouts/auth') ?>

<?= $this->section('title') ?>Login<?= $this->endSection() ?>
<?= $this->section('auth-title') ?>Sign in to your account<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <form action="/auth/login" method="POST">
        <?= csrf_field() ?>
        
        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" 
                   class="form-control <?= session('errors.email') ? 'is-invalid' : '' ?>" 
                   id="email" 
                   name="email" 
                   value="<?= old('email') ?>" 
                   required 
                   autofocus>
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
            <?php if (session('errors.password')): ?>
                <div class="invalid-feedback">
                    <?= session('errors.password') ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="remember" name="remember">
            <label class="form-check-label" for="remember">
                Remember me
            </label>
        </div>
        
        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary">Sign In</button>
        </div>
        
        <div class="mt-3">
            <div class="d-grid gap-2">
                <a href="/auth/moodle" class="btn btn-outline-success">
                    <i class="bi bi-mortarboard"></i> Login with Moodle
                </a>
            </div>
        </div>
        
        <div class="mt-3 text-center">
            <p class="mb-1">
                <a href="/auth/forgot-password" class="text-decoration-none">Forgot your password?</a>
            </p>
            <p>
                Don't have an account? 
                <a href="/auth/register" class="text-decoration-none">Register here</a>
            </p>
        </div>
    </form>
    
    <hr class="my-4">
    
    <div class="text-center text-muted">
        <small>
            <strong>Demo Credentials:</strong><br>
            Admin: admin@cite.local / Admin@123<br>
            Instructor: instructor@cite.local / Instructor@123<br>
            Student: student@cite.local / Student@123
        </small>
    </div>
<?= $this->endSection() ?>