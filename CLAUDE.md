# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview
This is a CodeIgniter 4 PHP web application following the MVC pattern. The framework requires PHP 8.1+ and uses Composer for dependency management. The development environment includes Docker containers for CodeIgniter 4, MySQL, Moodle LMS, and phpMyAdmin.

## Essential Commands

### Docker Setup & Development
```bash
# First time setup
cp env .env                      # Create environment file
docker-compose up -d              # Start all services (CI4, MySQL, Moodle, phpMyAdmin)
docker-compose exec ci4-app composer install  # Install PHP dependencies

# Daily development
docker-compose up -d              # Start all services
docker-compose down               # Stop all services
docker-compose logs -f ci4-app   # View CI4 app logs
docker-compose exec ci4-app bash # Access CI4 container shell

# Service URLs
# CodeIgniter 4: http://localhost:8080
# Moodle LMS: http://localhost:8081 (admin/Admin@123)
# phpMyAdmin: http://localhost:8082 (root/root_password)
```

### Local Development (without Docker)
```bash
composer install          # Install dependencies (run first if vendor/ doesn't exist)
cp env .env              # Create environment file
php spark key:generate   # Generate encryption key
php spark serve          # Start development server (default: http://localhost:8080)
php spark serve --port 3000  # Specify custom port
```

### Testing
```bash
# In Docker
docker-compose exec ci4-app ./vendor/bin/phpunit                    # Run all tests
docker-compose exec ci4-app ./vendor/bin/phpunit tests/unit         # Run unit tests only
docker-compose exec ci4-app ./vendor/bin/phpunit --filter TestName  # Run specific test

# Local
./vendor/bin/phpunit                    # Run all tests
./vendor/bin/phpunit tests/unit         # Run unit tests only
./vendor/bin/phpunit --filter TestName  # Run specific test
```

### Database
```bash
# In Docker
docker-compose exec ci4-app php spark migrate                 # Run database migrations
docker-compose exec ci4-app php spark migrate:rollback        # Rollback last migration
docker-compose exec ci4-app php spark db:seed SeederName      # Run specific seeder

# Local
php spark migrate                 # Run database migrations
php spark migrate:rollback        # Rollback last migration
php spark db:seed SeederName      # Run specific seeder
```

### Code Generation
```bash
php spark make:controller ControllerName
php spark make:model ModelName
php spark make:migration migration_name
php spark make:seeder SeederName
php spark make:filter FilterName
```

### Code Quality
```bash
./vendor/bin/php-cs-fixer fix          # Auto-fix code style issues
./vendor/bin/php-cs-fixer fix --dry-run  # Check without fixing
```

## Architecture

### Directory Structure
- `/app/` - Application logic (MVC components)
  - `Controllers/` - Request handlers, must extend BaseController
  - `Models/` - Database models, extend CodeIgniter\Model
  - `Views/` - Presentation templates (PHP files)
  - `Config/` - All configuration files (Routes, Database, App settings)
  - `Filters/` - Request/response filters for middleware functionality
  - `Helpers/` - Global helper functions
  - `Libraries/` - Custom libraries
- `/public/` - Web root (must be document root in production)
- `/writable/` - Cache, logs, uploads, sessions (must be writable)
- `/tests/` - PHPUnit test suites
- `/vendor/` - Composer dependencies (gitignored)

### Key Architectural Patterns

1. **MVC with Service Layer**: Controllers should be thin. Complex business logic goes in Libraries or Models.

2. **Configuration-Driven**: All settings in `/app/Config/`. Environment-specific overrides in `.env`.

3. **Routing**: Defined in `/app/Config/Routes.php`. RESTful routes supported with `resource()` method.

4. **Database Access**: Use CodeIgniter's Model class for database operations. Models provide built-in CRUD operations and query builder.

5. **Views**: Use view layouts and sections for template inheritance. Views are loaded via `return view('view_name', $data)`.

6. **Filters**: Implement middleware-like functionality. Define in `/app/Config/Filters.php` for global filters or apply to specific routes.

7. **Validation**: Use built-in validation library. Define rules in Controllers or separate Validation config files.

## Important Conventions

1. **Namespace**: All app code uses `App` namespace (PSR-4 autoloading)
2. **File Naming**: PascalCase for classes, match class name to filename
3. **Database Tables**: Snake_case, plural (e.g., `user_profiles`)
4. **Model Properties**: Define `$table`, `$primaryKey`, `$allowedFields` for automatic CRUD
5. **Environment Variables**: Database credentials and API keys must be in `.env`, never committed
6. **Public Assets**: CSS, JS, images go in `/public/` subdirectories
7. **Error Handling**: Use exceptions and CodeIgniter's error handlers

## Development Workflow

### With Docker (Recommended)
1. Ensure Docker and Docker Compose are installed
2. Run `docker-compose up -d` to start all services
3. Access services at their respective ports (CI4: 8080, Moodle: 8081, phpMyAdmin: 8082)
4. Use `docker-compose exec ci4-app bash` to run commands inside the container
5. Code changes in `/app`, `/public`, `/tests` are automatically synced via volumes

### Without Docker
1. Always check if Composer dependencies are installed (vendor/ directory exists)
2. Ensure `.env` file exists and is configured before running the application
3. Use Spark CLI for code generation to maintain consistency
4. Run tests before implementing significant changes
5. Keep controllers thin - business logic belongs in models or libraries
6. Use CodeIgniter's built-in helpers and libraries before creating custom ones

## Docker Environment Details

### Services
- **ci4-app**: CodeIgniter 4 application (PHP 8.2 + Apache)
- **mysql**: MySQL 8.0 for CI4 database
- **moodle**: Moodle LMS (Bitnami image)
- **moodle-db**: Separate MySQL instance for Moodle
- **phpmyadmin**: Database management interface

### Volumes
- `./app`, `./public`, `./writable`, `./tests`: Mounted for live development
- MySQL data persisted in named volumes
- Moodle data persisted in named volumes

### Database Credentials
- CI4 DB: ci4_user/ci4_password @ ci4_db
- Moodle DB: moodle/moodle_password @ moodle_db
- Root access: root/root_password (CI4), root/moodle_root (Moodle)