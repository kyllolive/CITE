# CITE - Learning Management System

A lean MVC CodeIgniter 4 application with Moodle integration, optimized for rapid development.

## 🚀 Quick Start

```bash
# Clone the repository
git clone <repository-url> && cd CITE

# Start with Docker (recommended)
cp env .env
docker-compose up -d

# Run migrations & seeders
docker-compose exec ci4-app php spark migrate
docker-compose exec ci4-app php spark db:seed UserSeeder
docker-compose exec ci4-app php spark db:seed CourseSeeder

# Access the application
open http://localhost:8080
```

**Service URLs:**

- App: http://localhost:8080
- Moodle: http://localhost:8081 (admin/Admin@123)
- phpMyAdmin: http://localhost:8082 (root/root_password)

**Test Credentials:**

- Admin: `admin@cite.local` / `Admin@123`
- Instructor: `instructor@cite.local` / `Instructor@123`
- Student: `student@cite.local` / `Student@123`

## 📁 Project Structure

```
app/
├── Controllers/     # Thin controllers - HTTP handling only
├── Models/          # Fat models - data & business logic
├── Views/           # Templates (layouts, auth, courses)
├── Libraries/       # Complex logic & external services
├── Database/        # Migrations & seeders
└── Filters/         # Middleware (auth, guest, etc.)
```

## 💻 Developer Workflow

### Starting Your Day

```bash
# 1. Start Docker services
docker-compose up -d

# 2. Check everything is running
docker-compose ps

# 3. Watch logs (optional)
docker-compose logs -f ci4-app

# 4. Clean up container

docker-compose down --volumes --rmi all
```

### Making Changes

1. **Code changes** → Auto-synced via Docker volumes
2. **Database changes** → Create migration: `docker-compose exec ci4-app php spark make:migration`
3. **New feature** → Generate code: `docker-compose exec ci4-app php spark make:controller`
4. **Test changes** → Visit http://localhost:8080

### Ending Your Day

```bash
# Stop all services
docker-compose down
```

## 🔧 Important Commands

### Docker Management

```bash
docker-compose up -d              # Start all services
docker-compose down               # Stop all services
docker-compose ps                 # View running containers
docker-compose logs -f ci4-app    # Watch logs
docker-compose exec ci4-app bash  # Container shell access
docker-compose restart ci4-app    # Restart service
```

### Database

```bash
# Migrations
docker-compose exec ci4-app php spark migrate
docker-compose exec ci4-app php spark migrate:rollback
docker-compose exec ci4-app php spark migrate:status

# Seeders
docker-compose exec ci4-app php spark db:seed UserSeeder
docker-compose exec ci4-app php spark db:seed CourseSeeder
```

### Code Generation

```bash
# Create new components
docker-compose exec ci4-app php spark make:controller ControllerName
docker-compose exec ci4-app php spark make:model ModelName
docker-compose exec ci4-app php spark make:migration migration_name
docker-compose exec ci4-app php spark make:seeder SeederName
docker-compose exec ci4-app php spark make:filter FilterName
```

### Moodle Integration

```bash
# Sync users and courses with Moodle
docker-compose exec ci4-app php spark moodle:sync           # Sync all
docker-compose exec ci4-app php spark moodle:sync --users   # Users only
docker-compose exec ci4-app php spark moodle:sync --courses # Courses only
docker-compose exec ci4-app php spark moodle:sync --force   # Force sync

# Enable Moodle integration
# 1. Set MOODLE_SYNC_ENABLED=true in .env
# 2. Configure MOODLE_URL and MOODLE_TOKEN
# 3. Restart container: docker-compose restart ci4-app
```

### Testing & Quality

```bash
# Run tests
docker-compose exec ci4-app ./vendor/bin/phpunit
docker-compose exec ci4-app ./vendor/bin/phpunit --filter TestName

# Code style
docker-compose exec ci4-app ./vendor/bin/php-cs-fixer fix
docker-compose exec ci4-app ./vendor/bin/php-cs-fixer fix --dry-run
```

### Debugging

```bash
# View logs
docker-compose logs mysql         # MySQL logs
docker-compose logs ci4-app       # App logs
tail -f writable/logs/*.log       # CI4 logs

# Database access
docker-compose exec mysql mysql -u root -proot_password ci4_db

# Clear cache
docker-compose exec ci4-app php spark cache:clear
```

## ⚙️ Configuration

### Key Environment Variables

```env
# Application
CI_ENVIRONMENT = development
app.baseURL = 'http://localhost:8080'

# Database (Docker)
database.default.hostname = mysql
database.default.database = ci4_db
database.default.username = ci4_user
database.default.password = ci4_password

# Moodle Integration
MOODLE_URL = http://moodle:8081
MOODLE_TOKEN = your_token_here
MOODLE_SYNC_ENABLED = false
```

## 🎓 Moodle Integration Features

### Authentication

- **Single Sign-On** - Login with Moodle credentials
- **User Sync** - Automatic user creation in both systems
- **Token Management** - Secure session handling
- **Graceful Fallback** - Works even if Moodle is down

### Data Synchronization

- **User Profiles** - Sync name, email, and roles
- **Course Catalog** - Import courses from Moodle
- **Enrollment Status** - Track student enrollments
- **Progress Tracking** - Monitor completion rates

### Integration Points

| Feature        | Status     | Description                   |
| -------------- | ---------- | ----------------------------- |
| Authentication | ✅ Ready   | Login/register with Moodle    |
| User Sync      | ✅ Ready   | CLI command for bulk sync     |
| Course Import  | ✅ Ready   | Import Moodle courses         |
| Grade Sync     | 🔄 Planned | Two-way grade synchronization |
| Content Embed  | 🔄 Planned | Embed Moodle activities       |

## 🏗️ Architecture Principles

- **Fat Models, Thin Controllers** - Business logic in models
- **Progressive Enhancement** - Start simple, add complexity when needed
- **Service Layer** - Use Libraries for complex operations
- **Convention over Configuration** - Follow CI4 patterns

### When to Scale

| Component      | Red Flag           | Solution               |
| -------------- | ------------------ | ---------------------- |
| Controllers    | > 100 lines/method | Extract to Libraries   |
| Models         | > 300 lines        | Create service classes |
| Duplicate code | 3+ places          | Create helpers         |
| API endpoints  | 10+ endpoints      | Version your API       |

## 🛠️ Troubleshooting

```bash
# Fix permissions
docker-compose exec ci4-app chown -R www-data:www-data /var/www/html/writable

# Database issues
docker-compose ps                  # Check if MySQL is running
docker-compose logs mysql           # View MySQL logs

# Clear all caches
docker-compose exec ci4-app php spark cache:clear

# Rebuild container (if needed)
docker-compose down
docker-compose build --no-cache ci4-app
docker-compose up -d
```

## 📚 Resources

- [CodeIgniter 4 Docs](https://codeigniter.com/user_guide/)
- [Architecture Guide](docs/ARCHITECTURE_GUIDE.md)
- [CLAUDE.md](CLAUDE.md) - AI assistant instructions

## 📝 License

Built on CodeIgniter 4 (MIT License)
