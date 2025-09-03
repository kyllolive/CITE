# CITE Architecture Guide - Lean MVC Approach

This document outlines the recommended architecture for the CITE project using a lean MVC pattern optimized for development speed while maintaining scalability.

## Core Philosophy

**Start Simple, Add Complexity When Needed**

We prioritize development speed and simplicity over premature optimization. This architecture allows for rapid development while maintaining clear paths for scaling when requirements demand it.

## Directory Structure

```
/app/
├── Controllers/
│   ├── Api/                    # API controllers (add when 5+ API controllers)
│   │   ├── AuthController.php
│   │   ├── CourseController.php
│   │   └── MoodleController.php
│   ├── Auth/                   # Web authentication controllers
│   ├── Admin/                  # Admin panel controllers
│   └── BaseController.php      # Common controller functionality
├── Models/
│   ├── UserModel.php
│   ├── CourseModel.php
│   ├── EnrollmentModel.php
│   └── BaseModel.php           # Common model methods
├── Views/
│   ├── layouts/
│   │   ├── app.php            # Main layout template
│   │   └── auth.php           # Authentication layout
│   ├── auth/
│   ├── courses/
│   ├── admin/
│   └── components/            # Reusable view components
├── Libraries/                  # Business logic & external services
│   ├── MoodleApi.php          # Moodle integration
│   ├── AuthService.php        # Authentication logic
│   └── CourseService.php      # Complex course operations
├── Helpers/
│   └── moodle_helper.php     # Utility functions
└── Database/
    ├── Migrations/
    └── Seeds/
```

## MVC Pattern Implementation

### Models (Fat Models Approach)

Models handle data operations AND basic business logic. This keeps controllers thin while avoiding over-abstraction.

```php
<?php
namespace App\Models;

use CodeIgniter\Model;

class CourseModel extends Model
{
    protected $table = 'courses';
    protected $primaryKey = 'id';
    protected $allowedFields = ['title', 'description', 'moodle_id', 'instructor_id', 'status'];
    protected $useTimestamps = true;
    
    // Validation rules embedded in model
    protected $validationRules = [
        'title' => 'required|min_length[3]|max_length[255]',
        'description' => 'required',
        'instructor_id' => 'required|is_natural_no_zero'
    ];
    
    // Business logic stays in model
    public function enrollStudent($courseId, $userId)
    {
        $enrollment = [
            'course_id' => $courseId,
            'user_id' => $userId,
            'enrolled_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->db->table('enrollments')->insert($enrollment);
    }
    
    public function getWithInstructor($courseId)
    {
        return $this->select('courses.*, users.name as instructor_name')
                    ->join('users', 'users.id = courses.instructor_id')
                    ->find($courseId);
    }
    
    public function syncWithMoodle($courseId)
    {
        $moodleApi = new \App\Libraries\MoodleApi();
        $moodleData = $moodleApi->getCourse($this->find($courseId)['moodle_id']);
        
        return $this->update($courseId, [
            'title' => $moodleData['fullname'],
            'description' => $moodleData['summary']
        ]);
    }
}
```

### Controllers (Thin Controllers)

Controllers handle HTTP requests/responses only. Business logic goes to Models or Libraries.

```php
<?php
namespace App\Controllers;

class CourseController extends BaseController
{
    protected $courseModel;
    
    public function __construct()
    {
        $this->courseModel = new \App\Models\CourseModel();
    }
    
    public function index()
    {
        $data = [
            'title' => 'Courses',
            'courses' => $this->courseModel->paginate(10),
            'pager' => $this->courseModel->pager
        ];
        
        return view('courses/index', $data);
    }
    
    public function show($id)
    {
        $course = $this->courseModel->getWithInstructor($id);
        
        if (!$course) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }
        
        return view('courses/show', ['course' => $course]);
    }
    
    public function create()
    {
        if ($this->request->getMethod() === 'post') {
            if ($this->courseModel->save($this->request->getPost())) {
                return redirect()->to('/courses')
                    ->with('success', 'Course created successfully');
            }
            
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->courseModel->errors());
        }
        
        return view('courses/create');
    }
}
```

### Libraries (Complex Business Logic)

Use Libraries only when logic becomes complex or needs to be shared across controllers.

```php
<?php
namespace App\Libraries;

class MoodleApi
{
    private $baseUrl;
    private $token;
    private $client;
    
    public function __construct()
    {
        $this->baseUrl = env('MOODLE_URL');
        $this->token = env('MOODLE_TOKEN');
        $this->client = \Config\Services::curlrequest();
    }
    
    public function getCourse($moodleCourseId)
    {
        $response = $this->client->get($this->baseUrl . '/webservice/rest/server.php', [
            'query' => [
                'wstoken' => $this->token,
                'wsfunction' => 'core_course_get_courses',
                'moodlewsrestformat' => 'json',
                'options[ids][0]' => $moodleCourseId
            ]
        ]);
        
        return json_decode($response->getBody(), true)[0] ?? null;
    }
    
    public function enrollUser($moodleCourseId, $moodleUserId)
    {
        // Enrollment logic
    }
}
```

## Naming Conventions

### Files

| Type | Convention | Example |
|------|------------|---------|
| Controllers | PascalCase + Controller | `CourseController.php` |
| Models | PascalCase + Model | `CourseModel.php` |
| Libraries | PascalCase | `MoodleApi.php` |
| Helpers | snake_case | `moodle_helper.php` |
| Views | snake_case | `course_list.php` |
| Migrations | timestamp_description | `2024_01_15_000001_create_courses.php` |

### Database

| Element | Convention | Example |
|---------|------------|---------|
| Tables | snake_case, plural | `courses`, `user_enrollments` |
| Columns | snake_case | `course_id`, `created_at` |
| Foreign Keys | singular_id | `user_id`, `course_id` |
| Indexes | idx_table_column | `idx_courses_status` |

### Code

| Element | Convention | Example |
|---------|------------|---------|
| Classes | PascalCase | `CourseModel` |
| Methods | camelCase | `enrollStudent()` |
| Variables | camelCase | `$courseData` |
| Constants | UPPER_SNAKE_CASE | `MAX_ENROLLMENTS` |
| Routes | kebab-case | `/api/course-enrollment` |

## Routes Organization

Keep routes simple and RESTful:

```php
// app/Config/Routes.php

// API Routes (when needed)
$routes->group('api', function($routes) {
    $routes->resource('courses');
    $routes->post('auth/login', 'Api\AuthController::login');
    $routes->get('moodle/sync', 'Api\MoodleController::sync');
});

// Admin Routes
$routes->group('admin', ['filter' => 'auth'], function($routes) {
    $routes->get('dashboard', 'Admin\DashboardController::index');
    $routes->resource('users', ['controller' => 'Admin\UserController']);
});

// Web Routes
$routes->get('/', 'Home::index');
$routes->resource('courses');
```

## View Structure

### Layout Template

```php
<!-- app/Views/layouts/app.php -->
<!DOCTYPE html>
<html>
<head>
    <title><?= $this->renderSection('title') ?> - CITE</title>
    <?= $this->renderSection('styles') ?>
</head>
<body>
    <?= $this->include('components/_header') ?>
    
    <main>
        <?= $this->renderSection('content') ?>
    </main>
    
    <?= $this->include('components/_footer') ?>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
```

### View File

```php
<!-- app/Views/courses/index.php -->
<?= $this->extend('layouts/app') ?>

<?= $this->section('title') ?>Courses<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <h1>Courses</h1>
    
    <?php foreach($courses as $course): ?>
        <?= $this->include('courses/_course_card', ['course' => $course]) ?>
    <?php endforeach; ?>
    
    <?= $pager->links() ?>
<?= $this->endSection() ?>
```

## Development Workflow

### Phase 1: Start Simple
1. Create model with validation rules
2. Create controller with basic CRUD
3. Create views for UI
4. Test functionality

### Phase 2: Add Complexity When Needed
- **Add Api/ folder** when you have 5+ API controllers
- **Create Libraries** when business logic exceeds 50 lines
- **Extract view components** when code is repeated 3+ times
- **Add service classes** only when truly needed
- **Create helpers** for utility functions used in multiple places

### Quick Start Commands

```bash
# Generate new components
php spark make:model CourseModel
php spark make:controller CourseController
php spark make:migration create_courses_table

# Database operations
php spark migrate
php spark db:seed CourseSeeder

# Development
php spark serve
```

## Moodle Integration Strategy

### Single Library Approach

Keep all Moodle operations in one library until complexity demands splitting:

```php
// app/Libraries/MoodleApi.php
class MoodleApi
{
    public function authenticate() {}
    public function getCourses() {}
    public function getUsers() {}
    public function enrollStudent() {}
    public function syncCourse() {}
    public function syncGrades() {}
}
```

### When to Split
- When MoodleApi.php exceeds 500 lines
- When you need different authentication methods
- When you have 10+ distinct Moodle operations

## Testing Structure

### Keep It Simple

```
/tests/
├── unit/
│   ├── CourseModelTest.php
│   └── MoodleApiTest.php
└── feature/
    ├── CourseCreationTest.php
    └── EnrollmentTest.php
```

### Basic Test Example

```php
class CourseModelTest extends \CodeIgniter\Test\DatabaseTestCase
{
    public function testCreateCourse()
    {
        $model = new \App\Models\CourseModel();
        
        $data = [
            'title' => 'Test Course',
            'description' => 'Test Description',
            'instructor_id' => 1
        ];
        
        $courseId = $model->insert($data);
        $this->assertIsNumeric($courseId);
        
        $course = $model->find($courseId);
        $this->assertEquals('Test Course', $course['title']);
    }
}
```

## When to Scale Up

### Signs You Need More Structure

1. **Controllers > 200 lines** → Extract to Libraries
2. **Models > 300 lines** → Create service classes
3. **Duplicate code in 3+ places** → Create helpers or traits
4. **5+ related controllers** → Group in subdirectories
5. **Complex business workflows** → Implement service layer
6. **Multiple external integrations** → Create Infrastructure layer
7. **10+ API endpoints** → Version your API

### Migration Path

When you need to scale:

1. **From Fat Models** → Service Layer
   ```
   Before: CourseModel does everything
   After: CourseModel (data) + CourseService (logic)
   ```

2. **From Single Library** → Multiple Services
   ```
   Before: MoodleApi.php (500+ lines)
   After: MoodleAuth.php, MoodleCourse.php, MoodleUser.php
   ```

3. **From Flat Structure** → Modular
   ```
   Before: /Controllers/CourseController.php
   After: /Modules/Course/Controllers/CourseController.php
   ```

## Environment Configuration

### Essential .env Variables

```env
# Application
CI_ENVIRONMENT = development
app.baseURL = 'http://localhost:8080'

# Database
database.default.hostname = mysql
database.default.database = cite_db
database.default.username = cite_user
database.default.password = cite_password

# Moodle Integration
MOODLE_URL = http://moodle:8081
MOODLE_TOKEN = your_token_here
MOODLE_SYNC_ENABLED = true

# Cache (add when needed)
CACHE_HANDLER = file

# Session
app.sessionDriver = 'CodeIgniter\Session\Handlers\DatabaseHandler'
```

## Best Practices Summary

### Do's
- ✅ Keep controllers thin (< 100 lines per method)
- ✅ Put business logic in models first
- ✅ Use Libraries for complex/shared logic
- ✅ Follow CI4 conventions
- ✅ Write tests for critical paths
- ✅ Use database transactions for multi-table operations
- ✅ Validate input in models
- ✅ Use CI4's built-in features (pagination, validation, etc.)

### Don'ts
- ❌ Don't create abstractions you don't need yet
- ❌ Don't put business logic in controllers
- ❌ Don't bypass CI4's model features
- ❌ Don't create services/repositories until necessary
- ❌ Don't optimize prematurely
- ❌ Don't create interfaces for single implementations
- ❌ Don't split code until it's actually complex

## Quick Reference

### Common Patterns

**1. CRUD Controller Method**
```php
public function update($id)
{
    $model = new \App\Models\CourseModel();
    
    if ($this->request->getMethod() === 'post') {
        if ($model->update($id, $this->request->getPost())) {
            return redirect()->to('/courses')->with('success', 'Updated');
        }
        return redirect()->back()->withInput()->with('errors', $model->errors());
    }
    
    return view('courses/edit', ['course' => $model->find($id)]);
}
```

**2. Model with Relationships**
```php
public function getWithRelations($id)
{
    return $this->select('courses.*, users.name as instructor')
                ->join('users', 'users.id = courses.instructor_id')
                ->where('courses.id', $id)
                ->first();
}
```

**3. API Response**
```php
return $this->response->setJSON([
    'status' => 'success',
    'data' => $courses,
    'message' => 'Courses retrieved'
]);
```

## Conclusion

This lean MVC approach prioritizes:
1. **Speed of development** over premature optimization
2. **Simplicity** over complex abstractions
3. **CI4 conventions** over custom patterns
4. **Progressive enhancement** - add complexity only when needed

Start simple, ship fast, refactor when necessary.