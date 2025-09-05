# Moodle Integration Setup Guide

## Current Issue
Moodle is redirecting API calls to `localhost:8081` instead of handling them properly. This needs to be fixed by configuring Moodle's web services and API access.

## Solution Steps

### 1. Access Moodle Admin Panel
1. Open browser and go to `http://localhost:8081`
2. Login with admin credentials (usually admin/Admin@123)

### 2. Enable Web Services
1. Go to **Site Administration → Advanced Features**
2. Check "Enable web services" ✅
3. Save changes

### 3. Enable REST Protocol
1. Go to **Site Administration → Server → Web services → Manage protocols**
2. Enable **REST protocol** ✅

### 4. Create Service User
1. Go to **Site Administration → Users → Accounts → Add a new user**
2. Create user:
   - Username: `cite_api_user`
   - Password: `CiteMoodle2025!`
   - First name: `CITE`
   - Last name: `API User`
   - Email: `cite@example.com`
3. Save user

### 5. Create External Service
1. Go to **Site Administration → Server → Web services → External services**
2. Click "Add" to create new service
3. Configure:
   - Name: `CITE Integration Service`
   - Short name: `cite_service`
   - Enabled: ✅
   - Authorised users only: ✅
4. Save

### 6. Add Functions to Service
1. Click on the service name to edit
2. Add these functions:
   - `core_webservice_get_site_info`
   - `core_user_get_users`
   - `core_user_create_users`
   - `core_course_get_courses`
   - `core_course_create_courses` 
   - `core_course_update_courses`
   - `core_course_get_courses_by_field`
   - `enrol_manual_enrol_users`
   - `enrol_manual_unenrol_users`
   - `core_enrol_get_users_courses`
   - `core_course_get_categories`
   - `core_course_create_categories`
   - `core_enrol_get_enrolled_users`

### 7. Authorize User for Service
1. In the service settings, go to "Authorised users"
2. Add the `cite_api_user` created in step 4

### 8. Generate Token
1. Go to **Site Administration → Server → Web services → Manage tokens**
2. Click "Create token"
3. Select:
   - User: `cite_api_user`
   - Service: `CITE Integration Service`
4. Save and copy the generated token

### 9. Update CITE Configuration
Update the `.env` file with the token:
```
MOODLE_TOKEN = [paste_your_generated_token_here]
```

### 10. Fix Moodle URL Configuration
The issue might also be Moodle's configured site URL. To fix this:

1. Go to **Site Administration → Server → HTTP**
2. Set **Force redirect to secure login**: No
3. **OR** access Moodle database and update:
   ```sql
   UPDATE mdl_config SET value = 'http://moodle_lms:80' WHERE name = 'wwwroot';
   ```

### 11. Test Integration
1. Access CITE at `http://localhost:8080`
2. Go to Admin → Manage Moodle
3. Click "Test Connection"
4. Should show successful connection

## Alternative Quick Setup via CLI

If you have CLI access to Moodle container:

```bash
# Enable web services
docker compose exec moodle-db mysql -u moodle -p moodle_db
UPDATE mdl_config SET value = 1 WHERE name = 'enablewebservices';
UPDATE mdl_config SET value = 'http://moodle_lms:80' WHERE name = 'wwwroot';

# Enable REST protocol  
INSERT INTO mdl_webservice_protocol (protocol, enabled) VALUES ('rest', 1);
```

## Troubleshooting

### If still getting redirect errors:
1. Check Moodle's `config.php` file
2. Ensure `$CFG->wwwroot` is set correctly
3. Clear Moodle cache

### If web service calls fail:
1. Verify token is correct
2. Check user has proper capabilities
3. Ensure service includes required functions

Once completed, the integration should work properly and you'll be able to:
- Sync courses from Moodle to CITE
- View Moodle course data
- Manage users and enrollments
- Use SSO functionality