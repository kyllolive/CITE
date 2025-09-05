<?= $this->extend('layouts/app') ?>

<?= $this->section('title') ?><?= $title ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><?= $title ?></h1>
    <div class="d-flex gap-2">
        <a href="/admin/moodle/courses" class="btn btn-primary">
            <i class="bi bi-mortarboard"></i> View Moodle Courses
        </a>
        <?php if ($sso_enabled): ?>
            <a href="<?= $moodle_url ?>" class="btn btn-outline-success" target="_blank">
                <i class="bi bi-box-arrow-up-right"></i> Open Moodle
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Connection Status -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-wifi"></i> Connection Status
                </h5>
            </div>
            <div class="card-body text-center">
                <div id="connection-status" class="mb-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Testing connection...</span>
                    </div>
                </div>
                <button class="btn btn-outline-primary btn-sm" onclick="testConnection()">
                    <i class="bi bi-arrow-clockwise"></i> Test Connection
                </button>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-shield-check"></i> SSO Status
                </h5>
            </div>
            <div class="card-body text-center">
                <?php if ($sso_enabled): ?>
                    <i class="bi bi-check-circle-fill text-success mb-2" style="font-size: 3rem;"></i>
                    <h6 class="text-success">SSO Enabled</h6>
                    <small class="text-muted">Single Sign-On is active</small>
                <?php else: ?>
                    <i class="bi bi-x-circle-fill text-danger mb-2" style="font-size: 3rem;"></i>
                    <h6 class="text-danger">SSO Disabled</h6>
                    <small class="text-muted">Check configuration</small>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-arrow-repeat"></i> Sync Status
                </h5>
            </div>
            <div class="card-body text-center">
                <?php if ($sync_enabled): ?>
                    <i class="bi bi-check-circle-fill text-success mb-2" style="font-size: 3rem;"></i>
                    <h6 class="text-success">Sync Enabled</h6>
                    <small class="text-muted">Data synchronization active</small>
                <?php else: ?>
                    <i class="bi bi-x-circle-fill text-warning mb-2" style="font-size: 3rem;"></i>
                    <h6 class="text-warning">Sync Disabled</h6>
                    <small class="text-muted">Manual sync only</small>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-primary mb-1"><?= $stats['total_cite_courses'] ?></h3>
                <small class="text-muted">CITE Courses</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-info mb-1" id="moodle-courses-count"><?= $stats['total_moodle_courses'] ?></h3>
                <small class="text-muted">Moodle Courses</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-success mb-1"><?= $stats['synced_courses'] ?></h3>
                <small class="text-muted">Synced Courses</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-warning mb-1"><?= $stats['users_with_moodle_id'] ?></h3>
                <small class="text-muted">Linked Users</small>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-lightning-charge"></i> Quick Actions
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 col-lg-3 mb-3">
                <div class="d-grid">
                    <a href="/admin/moodle/courses" class="btn btn-outline-primary">
                        <i class="bi bi-mortarboard"></i>
                        <div>View Moodle Courses</div>
                        <small class="text-muted">Browse and sync courses</small>
                    </a>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3 mb-3">
                <div class="d-grid">
                    <a href="/admin/moodle/categories" class="btn btn-outline-info">
                        <i class="bi bi-folder"></i>
                        <div>Course Categories</div>
                        <small class="text-muted">Manage course categories</small>
                    </a>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3 mb-3">
                <div class="d-grid">
                    <button class="btn btn-outline-success" onclick="showBulkSyncModal()">
                        <i class="bi bi-arrow-down-circle"></i>
                        <div>Bulk Sync Courses</div>
                        <small class="text-muted">Sync multiple courses</small>
                    </button>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3 mb-3">
                <div class="d-grid">
                    <button class="btn btn-outline-warning" onclick="showConfigModal()">
                        <i class="bi bi-gear"></i>
                        <div>Configuration</div>
                        <small class="text-muted">View settings</small>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-clock-history"></i> Integration Overview
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6>Moodle Connection</h6>
                <ul class="list-unstyled">
                    <li><strong>URL:</strong> <?= $moodle_url ?: 'Not configured' ?></li>
                    <li><strong>SSO:</strong> <span class="badge bg-<?= $sso_enabled ? 'success' : 'danger' ?>"><?= $sso_enabled ? 'Enabled' : 'Disabled' ?></span></li>
                    <li><strong>Sync:</strong> <span class="badge bg-<?= $sync_enabled ? 'success' : 'warning' ?>"><?= $sync_enabled ? 'Enabled' : 'Disabled' ?></span></li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6>Synchronization Status</h6>
                <div class="progress mb-2">
                    <?php 
                    $syncPercentage = $stats['total_moodle_courses'] > 0 ? 
                        round(($stats['synced_courses'] / $stats['total_moodle_courses']) * 100) : 0;
                    ?>
                    <div class="progress-bar" style="width: <?= $syncPercentage ?>%"></div>
                </div>
                <small class="text-muted"><?= $stats['synced_courses'] ?> of <?= $stats['total_moodle_courses'] ?> courses synced (<?= $syncPercentage ?>%)</small>
            </div>
        </div>
    </div>
</div>

<!-- Setup Guide Modal -->
<div class="modal fade" id="setupGuideModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Moodle Integration Setup Guide</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <strong>⚠️ Setup Required:</strong> Moodle web services need to be configured before the integration can work properly.
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <h6>1. Access Moodle Admin</h6>
                        <ul>
                            <li>Open <a href="http://localhost:8081" target="_blank">http://localhost:8081</a></li>
                            <li>Login: <code>admin</code> / <code>Admin@123</code></li>
                        </ul>
                        
                        <h6>2. Enable Web Services</h6>
                        <ul>
                            <li>Site Administration → Advanced Features</li>
                            <li>Check "Enable web services" ✅</li>
                            <li>Save changes</li>
                        </ul>
                        
                        <h6>3. Enable REST Protocol</h6>
                        <ul>
                            <li>Site Administration → Server → Web services → Manage protocols</li>
                            <li>Enable REST protocol ✅</li>
                        </ul>
                    </div>
                    
                    <div class="col-md-6">
                        <h6>4. Create API Service</h6>
                        <ul>
                            <li>Web services → External services → Add</li>
                            <li>Name: "CITE Integration Service"</li>
                            <li>Enabled: ✅, Authorized users only: ✅</li>
                        </ul>
                        
                        <h6>5. Add Required Functions</h6>
                        <div class="bg-light p-2 small">
                            <code>core_webservice_get_site_info</code><br>
                            <code>core_course_get_courses</code><br>
                            <code>core_user_get_users</code><br>
                            <code>core_user_create_users</code><br>
                            <code>enrol_manual_enrol_users</code>
                        </div>
                        
                        <h6>6. Generate API Token</h6>
                        <ul>
                            <li>Web services → Manage tokens → Create token</li>
                            <li>Select your service and a user</li>
                            <li>Copy the token</li>
                        </ul>
                    </div>
                </div>
                
                <div class="alert alert-success mt-3">
                    <strong>🔑 Final Step:</strong> Update your <code>.env</code> file with the token:
                    <br><code>MOODLE_TOKEN = your_generated_token_here</code>
                </div>
                
                <div class="alert alert-info">
                    <strong>🔧 Common Issue Fix:</strong> If you get redirect errors, update Moodle's site URL:
                    <br><code>Site Administration → Server → HTTP → Force redirect to secure login: No</code>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="testConnection()">Test Connection</button>
            </div>
        </div>
    </div>
</div>

<!-- Configuration Modal -->
<div class="modal fade" id="configModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Moodle Integration Configuration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table">
                        <tbody>
                            <tr>
                                <td><strong>Moodle URL</strong></td>
                                <td><?= $moodle_url ?: 'Not configured' ?></td>
                            </tr>
                            <tr>
                                <td><strong>SSO Enabled</strong></td>
                                <td><span class="badge bg-<?= $sso_enabled ? 'success' : 'danger' ?>"><?= $sso_enabled ? 'Yes' : 'No' ?></span></td>
                            </tr>
                            <tr>
                                <td><strong>Sync Enabled</strong></td>
                                <td><span class="badge bg-<?= $sync_enabled ? 'success' : 'warning' ?>"><?= $sync_enabled ? 'Yes' : 'No' ?></span></td>
                            </tr>
                            <tr>
                                <td><strong>SSO Secret</strong></td>
                                <td><?= getenv('MOODLE_SSO_SECRET') ? '****** (configured)' : 'Not configured' ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="alert alert-info">
                    <h6>Configuration Instructions:</h6>
                    <ol class="mb-0">
                        <li>Access Moodle at <strong>http://localhost:8081</strong> (admin/Admin@123)</li>
                        <li>Enable web services in Site Administration → Advanced Features</li>
                        <li>Enable REST protocol in Site Administration → Server → Web services</li>
                        <li>Create API token and update .env MOODLE_TOKEN</li>
                        <li>Configure wwwroot to prevent redirects</li>
                    </ol>
                    <div class="mt-2">
                        <strong>📋 Detailed Setup Guide:</strong> 
                        <a href="#" onclick="showSetupGuide()" class="btn btn-sm btn-outline-primary">View Setup Instructions</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Test Moodle connection
async function testConnection() {
    const statusDiv = document.getElementById('connection-status');
    const coursesCount = document.getElementById('moodle-courses-count');
    
    statusDiv.innerHTML = `
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Testing...</span>
        </div>
    `;
    
    try {
        const response = await fetch('/admin/moodle/test-connection', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        let statusHtml = '';
        if (data.tests.connection) {
            statusHtml = `
                <i class="bi bi-check-circle-fill text-success mb-2" style="font-size: 3rem;"></i>
                <h6 class="text-success">Connected</h6>
                <small class="text-muted">${data.results.site_info?.sitename || 'Moodle Site'}</small>
            `;
            
            if (data.results.courses_count !== undefined) {
                coursesCount.textContent = data.results.courses_count;
            }
        } else {
            statusHtml = `
                <i class="bi bi-x-circle-fill text-danger mb-2" style="font-size: 3rem;"></i>
                <h6 class="text-danger">Connection Failed</h6>
                <small class="text-muted">${data.results.error || 'Unknown error'}</small>
            `;
        }
        
        statusDiv.innerHTML = statusHtml;
        
    } catch (error) {
        statusDiv.innerHTML = `
            <i class="bi bi-x-circle-fill text-danger mb-2" style="font-size: 3rem;"></i>
            <h6 class="text-danger">Test Failed</h6>
            <small class="text-muted">Connection error</small>
        `;
    }
}

// Show setup guide modal
function showSetupGuide() {
    new bootstrap.Modal(document.getElementById('setupGuideModal')).show();
}

// Show configuration modal
function showConfigModal() {
    new bootstrap.Modal(document.getElementById('configModal')).show();
}

// Show bulk sync modal (placeholder)
function showBulkSyncModal() {
    // Redirect to courses page for bulk sync
    window.location.href = '/admin/moodle/courses';
}

// Test connection on page load
document.addEventListener('DOMContentLoaded', function() {
    testConnection();
});
</script>
<?= $this->endSection() ?>