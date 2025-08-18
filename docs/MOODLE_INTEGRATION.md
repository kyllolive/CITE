# Moodle Integration Guide

## Understanding Moodle's Architecture

Moodle uses its own database schema and upgrade system. Direct database modifications are strongly discouraged as they will break during Moodle updates.

## Integration Strategies

### 1. Web Services API (Recommended)
Use Moodle's built-in web services for integration:

```php
// Example: Fetch Moodle users from CI4
$moodleUrl = 'http://moodle:8080/webservice/rest/server.php';
$token = 'your_moodle_token';

$params = [
    'wstoken' => $token,
    'wsfunction' => 'core_user_get_users',
    'moodlewsrestformat' => 'json',
    'criteria' => [
        ['key' => 'email', 'value' => 'user@example.com']
    ]
];

$response = file_get_contents($moodleUrl . '?' . http_build_query($params));
$users = json_decode($response, true);
```

### 2. External Database Authentication
Configure Moodle to authenticate against your CI4 database:
- Go to: Site administration → Plugins → Authentication → External database
- Configure connection to CI4's MySQL database
- Map user fields between systems

### 3. Custom Moodle Plugin Development

Create a local plugin in the Moodle container:

```bash
# Access Moodle container
docker-compose exec moodle bash

# Navigate to local plugins directory
cd /bitnami/moodle/local/

# Create your plugin structure
mkdir yourplugin
cd yourplugin
```

Plugin structure:
```
/local/yourplugin/
├── version.php          # Version and dependencies
├── db/
│   ├── install.xml     # Database schema
│   ├── upgrade.php     # Database upgrades
│   └── access.php      # Capabilities
├── lang/en/
│   └── local_yourplugin.php  # Language strings
└── index.php           # Main plugin code
```

### 4. Shared Database Tables (Advanced)

If you need shared data between CI4 and Moodle:

```php
// In CI4 - Create integration tables
// app/Database/Migrations/2024-01-01-000000_CreateMoodleIntegration.php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMoodleIntegration extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'moodle_user_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'ci4_user_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'sync_status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'synced', 'error'],
                'default' => 'pending',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('moodle_integration');
    }

    public function down()
    {
        $this->forge->dropTable('moodle_integration');
    }
}
```

## Enabling Moodle Web Services

1. Access Moodle admin panel: http://localhost:8081
2. Navigate to: Site administration → Advanced features
3. Enable web services
4. Go to: Site administration → Plugins → Web services → External services
5. Add a new service for CI4 integration
6. Generate tokens for API access

## Development Workflow

### For Moodle Plugin Development:
```bash
# Edit plugin files locally (they're in the volume)
cd moodle_data/moodle/local/yourplugin/

# Bump version in version.php
# Moodle will detect and run upgrades automatically

# Clear Moodle cache after changes
docker-compose exec moodle php admin/cli/purge_caches.php
```

### For CI4-Moodle Integration:
```bash
# Create migration for integration tables
docker-compose exec ci4-app php spark make:migration CreateMoodleIntegration

# Run the migration
docker-compose exec ci4-app php spark migrate

# Create integration model
docker-compose exec ci4-app php spark make:model MoodleIntegrationModel
```

## Best Practices

1. **Never modify Moodle core files** - Use plugins
2. **Use Moodle's APIs** - Don't bypass security
3. **Cache API responses** - Reduce load
4. **Log integration events** - For debugging
5. **Handle API failures gracefully** - Moodle might be updating
6. **Version your plugins** - Use upgrade.php for schema changes

## Common Integration Scenarios

### 1. Single Sign-On (SSO)
- Use OAuth2 or SAML plugins
- Or implement custom authentication plugin

### 2. Grade Synchronization
```php
// Fetch grades from Moodle
$grades = $this->moodleAPI->getGrades($courseId, $userId);

// Store in CI4 for reporting
$this->gradeModel->syncFromMoodle($grades);
```

### 3. Course Enrollment
```php
// Enroll user from CI4 to Moodle course
$this->moodleAPI->enrollUser($userId, $courseId, $roleId);
```

### 4. Content Embedding
- Use Moodle's LTI (Learning Tools Interoperability)
- Embed CI4 content in Moodle courses

## Troubleshooting

### Check Moodle logs:
```bash
docker-compose exec moodle tail -f /bitnami/moodle/moodledata/log/apache2/error.log
```

### Check CI4 logs:
```bash
docker-compose logs -f ci4-app
```

### Database debugging:
Access phpMyAdmin at http://localhost:8082 to inspect both databases.