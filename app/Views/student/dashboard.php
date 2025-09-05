<?= $this->extend('layouts/app') ?>

<?= $this->section('title') ?><?= $title ?><?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    :root {
        --primary-color: #1e3a8a;
        --secondary-color: #f59e0b;
        --accent-color: #10b981;
        --light-bg: #f8fafc;
        --card-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        --card-hover-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    body {
        background-color: var(--light-bg);
    }

    .dashboard-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, #3730a3 100%);
        color: white;
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .profile-notification {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        border-radius: 12px;
        padding: 1rem 1.5rem;
        margin-bottom: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .activity-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: var(--card-shadow);
        transition: all 0.3s ease;
        margin-bottom: 1.5rem;
        border: 1px solid #e2e8f0;
    }

    .activity-card:hover {
        box-shadow: var(--card-hover-shadow);
        transform: translateY(-2px);
    }

    .activity-tag {
        background: var(--secondary-color);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .activity-tag.forum {
        background: var(--primary-color);
    }

    .stats-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        text-align: center;
        box-shadow: var(--card-shadow);
        transition: all 0.3s ease;
        border: 1px solid #e2e8f0;
    }

    .stats-card:hover {
        box-shadow: var(--card-hover-shadow);
        transform: translateY(-2px);
    }

    .stats-number {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--primary-color);
        margin-bottom: 0.5rem;
    }

    .stats-label {
        color: #64748b;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .course-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: var(--card-shadow);
        transition: all 0.3s ease;
        border: 1px solid #e2e8f0;
    }

    .course-card:hover {
        box-shadow: var(--card-hover-shadow);
        transform: translateY(-2px);
    }

    .course-image {
        height: 200px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        position: relative;
    }

    .course-image.alt-1 {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .course-image.alt-2 {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }

    .course-image.alt-3 {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    }

    .course-tags {
        position: absolute;
        top: 1rem;
        left: 1rem;
    }

    .course-tag {
        background: rgba(255, 255, 255, 0.9);
        color: var(--primary-color);
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
        display: inline-block;
    }

    .course-content {
        padding: 1.5rem;
    }

    .course-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.5rem;
    }

    .course-subtitle {
        color: #64748b;
        font-size: 0.875rem;
        margin-bottom: 1rem;
    }

    .course-stats {
        display: flex;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .course-stat {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        color: #64748b;
        font-size: 0.75rem;
    }

    .btn-view-discussion {
        background: var(--primary-color);
        color: white;
        border: none;
        border-radius: 8px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        transition: all 0.3s ease;
        width: 100%;
    }

    .btn-view-discussion:hover {
        background: #1e40af;
        color: white;
        transform: translateY(-1px);
    }

    .btn-complete-profile {
        background: var(--primary-color);
        color: white;
        border: none;
        border-radius: 8px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .btn-complete-profile:hover {
        background: #1e40af;
        color: white;
        transform: translateY(-1px);
    }

    .section-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary-color);
        margin-bottom: 1.5rem;
    }

    .badge-item {
        display: flex;
        align-items: center;
        padding: 1rem;
        background: white;
        border-radius: 12px;
        box-shadow: var(--card-shadow);
        margin-bottom: 1rem;
        transition: all 0.3s ease;
        border: 1px solid #e2e8f0;
    }

    .badge-item:hover {
        box-shadow: var(--card-hover-shadow);
        transform: translateY(-1px);
    }

    .badge-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--secondary-color) 0%, #d97706 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        margin-right: 1rem;
    }

    .badge-info h6 {
        margin-bottom: 0.25rem;
        font-weight: 600;
        color: #1e293b;
    }

    .badge-info small {
        color: #64748b;
    }

    .carousel-item {
        padding: 0 0.5rem;
    }

    .recommended-activities .carousel-control-prev,
    .recommended-activities .carousel-control-next {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(30, 58, 138, 0.1);
        border: none;
        top: 50%;
        transform: translateY(-50%);
    }

    .recommended-activities .carousel-control-prev {
        left: -50px;
    }

    .recommended-activities .carousel-control-next {
        right: -50px;
    }

    .nav-tabs-custom {
        border: none;
        margin-bottom: 2rem;
    }

    .nav-tabs-custom .nav-link {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        margin-right: 1rem;
        padding: 0.75rem 1.5rem;
        color: #64748b;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .nav-tabs-custom .nav-link.active {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }

    @media (max-width: 768px) {
        .dashboard-header {
            padding: 1.5rem;
        }
        
        .course-stats {
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .recommended-activities .carousel-control-prev,
        .recommended-activities .carousel-control-next {
            display: none;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Profile Completion Alert -->
<div class="profile-notification">
    <div>
        <h6 class="mb-1">Complete your profile information</h6>
        <small>Your profile information is mandatory and should be completed within 28 days</small>
    </div>
    <a href="/profile" class="btn-complete-profile">Complete Profile Information</a>
</div>

<!-- Dashboard Header -->
<div class="dashboard-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h1 class="mb-2">Welcome back, <?= esc($user['name']) ?>!</h1>
            <p class="mb-0 opacity-75">Continue your learning journey and track your progress</p>
        </div>
        <div class="col-md-4 text-md-end">
            <div class="d-flex justify-content-md-end gap-3">
                <div class="text-center">
                    <div class="fs-2 fw-bold"><?= $totalBadges ?></div>
                    <small class="opacity-75">Badges Earned</small>
                </div>
                <div class="text-center">
                    <div class="fs-2 fw-bold"><?= $totalCourses ?></div>
                    <small class="opacity-75">Courses Enrolled</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="stats-card">
            <div class="stats-number"><?= $totalBadges ?></div>
            <div class="stats-label">Total Badges</div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stats-card">
            <div class="stats-number"><?= $totalCourses ?></div>
            <div class="stats-label">Enrolled Courses</div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stats-card">
            <div class="stats-number"><?= $completedCourses ?></div>
            <div class="stats-label">Completed Courses</div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stats-card">
            <div class="stats-number"><?= count(array_keys($badgesByCourse)) ?></div>
            <div class="stats-label">Courses with Badges</div>
        </div>
    </div>
</div>

<!-- Recommended Activities Carousel -->
<div class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="section-title mb-0">Recommended Activities</h2>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-primary" type="button" data-bs-target="#activitiesCarousel" data-bs-slide="prev">
                <i class="bi bi-chevron-left"></i>
            </button>
            <button class="btn btn-sm btn-outline-primary" type="button" data-bs-target="#activitiesCarousel" data-bs-slide="next">
                <i class="bi bi-chevron-right"></i>
            </button>
        </div>
    </div>
    
    <div id="activitiesCarousel" class="carousel slide recommended-activities" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="activity-card">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="activity-tag">Recommended Activities</span>
                                <span class="activity-tag forum">FORUM</span>
                            </div>
                            <h5 class="mb-3">Networking in 1 Line: How Do You Introduce Yourself?</h5>
                            <button class="btn-view-discussion">View Discussion</button>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="activity-card">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="activity-tag">Recommended Activities</span>
                                <span class="activity-tag forum">FORUM</span>
                            </div>
                            <h5 class="mb-3">My First Job Interview Experience – Share Your Lessons</h5>
                            <button class="btn-view-discussion">View Discussion</button>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="activity-card">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="activity-tag">Recommended Activities</span>
                                <span class="activity-tag forum">FORUM</span>
                            </div>
                            <h5 class="mb-3">AI or Human? Who Should Review Your Resume First?</h5>
                            <button class="btn-view-discussion">View Discussion</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Navigation Tabs -->
<ul class="nav nav-tabs nav-tabs-custom" id="mainTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="courses-tab" data-bs-toggle="tab" data-bs-target="#courses-content" type="button" role="tab">
            <i class="bi bi-book"></i> Courses and Programs
            <small class="d-block">Courses and curated learning paths</small>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="forum-tab" data-bs-toggle="tab" data-bs-target="#forum-content" type="button" role="tab">
            <i class="bi bi-people"></i> Forum
            <small class="d-block">Connect with communities & share views</small>
        </button>
    </li>
</ul>

<!-- Tab Content -->
<div class="tab-content" id="mainTabContent">
    <!-- Courses Tab -->
    <div class="tab-pane fade show active" id="courses-content" role="tabpanel">
        <div class="mb-5">
            <h3 class="section-title">Offered by Wadhwani Foundation</h3>
            <p class="text-muted mb-4">Programs</p>
            <p class="mb-4">Curated collection of courses to help you gain skills to break into a new career or advance your current career.</p>
            
            <div class="row">
                <?php if (!empty($enrolledCourses)): ?>
                    <?php foreach (array_slice($enrolledCourses, 0, 2) as $index => $course): ?>
                    <div class="col-lg-6 mb-4">
                        <div class="course-card">
                            <div class="course-image <?= 'alt-' . ($index % 4) ?>">
                                <div class="course-tags">
                                    <span class="course-tag">Communication</span>
                                    <span class="course-tag">+<?= rand(6, 12) ?> more</span>
                                </div>
                            </div>
                            <div class="course-content">
                                <h5 class="course-title"><?= esc($course['title']) ?></h5>
                                <p class="course-subtitle">Employability Skills - JobReady</p>
                                
                                <div class="course-stats">
                                    <div class="course-stat">
                                        <i class="bi bi-book"></i>
                                        <span><?= rand(10, 20) ?> Courses</span>
                                    </div>
                                    <div class="course-stat">
                                        <i class="bi bi-award"></i>
                                        <span>1 Certificate</span>
                                    </div>
                                    <div class="course-stat">
                                        <i class="bi bi-patch-check"></i>
                                        <span><?= rand(10, 20) ?> Microcertificates</span>
                                    </div>
                                </div>
                                
                                <div class="course-stat mb-3">
                                    <i class="bi bi-bar-chart"></i>
                                    <span>Skill Scorecard</span>
                                </div>
                                
                                <a href="/student/courses" class="btn-view-discussion">Continue Learning</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <?php if (!empty($availableCourses)): ?>
                    <?php foreach (array_slice($availableCourses, 0, 2 - count($enrolledCourses)) as $index => $course): ?>
                    <div class="col-lg-6 mb-4">
                        <div class="course-card">
                            <div class="course-image <?= 'alt-' . (($index + count($enrolledCourses)) % 4) ?>">
                                <div class="course-tags">
                                    <span class="course-tag">Communication</span>
                                    <span class="course-tag">+<?= rand(6, 12) ?> more</span>
                                </div>
                            </div>
                            <div class="course-content">
                                <h5 class="course-title"><?= esc($course['title']) ?></h5>
                                <p class="course-subtitle">Employability Skills - Available</p>
                                
                                <div class="course-stats">
                                    <div class="course-stat">
                                        <i class="bi bi-book"></i>
                                        <span><?= rand(10, 20) ?> Courses</span>
                                    </div>
                                    <div class="course-stat">
                                        <i class="bi bi-award"></i>
                                        <span>1 Certificate</span>
                                    </div>
                                    <div class="course-stat">
                                        <i class="bi bi-patch-check"></i>
                                        <span><?= rand(10, 20) ?> Microcertificates</span>
                                    </div>
                                </div>
                                
                                <div class="course-stat mb-3">
                                    <i class="bi bi-bar-chart"></i>
                                    <span>Skill Scorecard</span>
                                </div>
                                
                                <a href="/courses" class="btn-view-discussion">Enroll Now</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <?php if (count($enrolledCourses) + count($availableCourses) > 2): ?>
            <div class="text-center mt-4">
                <button class="btn btn-outline-primary">
                    <i class="bi bi-chevron-right"></i>
                    View More Courses
                </button>
            </div>
            <?php endif; ?>
        </div>

        <!-- Individual Courses Section -->
        <div class="mb-5">
            <h3 class="section-title">Courses</h3>
            <p class="mb-4">Designed to provide specific skills and knowledge, helping you develop expertise in a particular area.</p>
            
            <div class="course-card">
                <div class="course-image">
                    <!-- Course image would go here -->
                </div>
                <div class="course-content">
                    <h5 class="course-title">Individual Course Example</h5>
                    <p class="course-subtitle">Learn specific skills in focused modules</p>
                    <button class="btn-view-discussion">View Course</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Forum Tab -->
    <div class="tab-pane fade" id="forum-content" role="tabpanel">
        <div class="mb-5">
            <h3 class="section-title">Recommended Forums</h3>
            
            <div class="activity-card">
                <div class="row">
                    <div class="col-md-4">
                        <div class="course-image" style="height: 150px; border-radius: 12px;">
                            <!-- Forum image -->
                        </div>
                    </div>
                    <div class="col-md-8">
                        <h5 class="mb-3">Career Bridge - Learn, Grow and Collaborate</h5>
                        <p class="mb-3">A space for students, alumni and teachers to connect, exchange ideas, and foster professional development.</p>
                        
                        <div class="mb-3">
                            <h6 class="mb-2"><i class="bi bi-trending-up"></i> Trending Topics</h6>
                            <span class="badge bg-warning text-dark me-2">⭐ Turning Passion into Profession ⭐</span>
                        </div>
                        
                        <button class="btn btn-primary">View Forum</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Batch Enrollment -->
        <div class="mb-5">
            <div class="activity-card" style="background: #fef3cd;">
                <h5 class="mb-3">Join a batch</h5>
                <p class="mb-3">You can enroll in the program by entering your unique batch code given to you by our partner organization.</p>
                
                <div class="row">
                    <div class="col-md-8">
                        <input type="text" class="form-control" placeholder="Enter code here" style="border-radius: 8px;">
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-primary w-100">Enroll</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Badges -->
<?php if (!empty($recentBadges)): ?>
<div class="mb-5">
    <h3 class="section-title">Recent Achievements</h3>
    <?php foreach ($recentBadges as $badge): ?>
    <div class="badge-item">
        <div class="badge-icon">
            <i class="bi bi-award"></i>
        </div>
        <div class="badge-info flex-grow-1">
            <h6><?= esc($badge['badge_name']) ?></h6>
            <small class="text-muted">Earned in <?= esc($badge['course_title']) ?> • <?= date('M j, Y', strtotime($badge['created_at'])) ?></small>
        </div>
        <a href="/student/badges/<?= $badge['badge_id'] ?>" class="btn btn-sm btn-outline-primary">View Badge</a>
    </div>
    <?php endforeach; ?>
    
    <div class="text-center mt-4">
        <a href="/student/badges" class="btn btn-outline-primary">View All Badges</a>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tabs
    var triggerTabList = [].slice.call(document.querySelectorAll('#mainTabs button'))
    triggerTabList.forEach(function (triggerEl) {
        var tabTrigger = new bootstrap.Tab(triggerEl)
        
        triggerEl.addEventListener('click', function (event) {
            event.preventDefault()
            tabTrigger.show()
        })
    })
    
    // Initialize carousel
    var carousel = new bootstrap.Carousel(document.getElementById('activitiesCarousel'), {
        interval: false
    })
});
</script>
<?= $this->endSection() ?>