# Moodle Integration Setup Guide

Complete step-by-step guide to integrate Moodle LMS with your CodeIgniter 4 CITE application.

## Prerequisites

- ✅ Docker and Docker Compose installed
- ✅ Moodle running at http://localhost:8081
- ✅ CodeIgniter 4 application running at http://localhost:8080
- ✅ Admin access to both systems

## Integration Architecture

```
CodeIgniter App ←--→ Moodle Web Services API
     ↑                        ↓
   SSO Auth              Course/User Sync
     ↑                        ↓
   Badge System         Grade/Progress Tracking
```

## Part 1: Moodle Configuration

### Step 1: Access Moodle Admin Panel

1. Open your browser and navigate to http://localhost:8081
2. Login with admin credentials:
   - **Username:** `admin`
   - **Password:** `Admin@123`

### Step 2: Enable Web Services

1. Click the **gear icon** (⚙️) in the top navigation bar
2. Select **Site Administration**
3. Navigate to **Advanced features**
4. Check ✅ **"Enable web services"**
5. Click **Save changes**

### Step 3: Enable REST Protocol

1. In Site Administration, go to **Server** → **Web services** → **Manage protocols**
2. Find **REST protocol** in the list
3. Click the **Enable** link (eye icon should become visible/open)
4. Verify that REST shows as **"Enabled"**

### Step 4: Create External Service

1. Go to **Server** → **Web services** → **External services**
2. Click **Add** button
3. Fill in the service details:
   ```
   Name: CITE Integration Service
   Short name: cite_service
   Enabled: ✅ Yes
   Authorized users only: ✅ Yes
   Can download files: ❌ No (optional)
   Can upload files: ❌ No (optional)
   ```
4. Click **Add service**

### Step 5: Add Required Functions to Service

1. Click on your newly created **"CITE Integration Service"**
2. Click **Add functions**
3. Search and add these essential functions one by one:

#### Core Functions (Required)
- `core_webservice_get_site_info` - Basic site information
- `core_user_get_users` - Get user information
- `core_user_create_users` - Create new users
- `core_course_get_courses` - List all courses
- `core_course_create_courses` - Create new courses
- `core_course_update_courses` - Update course details
- `core_course_get_categories` - Get course categories
- `core_enrol_get_users_courses` - Get user enrollments
- `core_enrol_get_enrolled_users` - Get enrolled users in course

#### Enrollment Functions
- `enrol_manual_enrol_users` - Enroll users in courses
- `enrol_manual_unenrol_users` - Unenroll users from courses

#### Grade Functions (Optional)
- `gradereport_user_get_grade_items` - Get user grades
- `core_grades_get_grades` - Get detailed grades

#### Badge Functions (Optional - if using badges)
- `core_badges_get_badges` - Get available badges
- `core_badges_get_user_badges` - Get user's earned badges

### Step 6: Create API User Account

1. Go to **Users** → **Accounts** → **Add a new user**
2. Create the API user with these details:
   ```
   Username: cite_api
   Password: CiteMoodle2025!
   First name: CITE
   Last name: API
   Email: cite-api@example.com
   City/town: API User
   Country: Select your country
   ```
3. **Important:** Uncheck **"Force password change"**
4. Click **Create user**

### Step 7: Assign System Role to API User

1. Go to **Users** → **Permissions** → **Assign system roles**
2. Select **Manager** role (or create a custom role)
3. Click **Assign users**
4. Search for and select **cite_api** user
5. Click **Add** to assign the role

### Step 8: Authorize API User for Service

1. Go back to **Server** → **Web services** → **External services**
2. Click on **"CITE Integration Service"**
3. Click **Authorised users**
4. Click **Add** and search for **cite_api**
5. Select the user and click **Add**

### Step 9: Generate API Token

1. Go to **Server** → **Web services** → **Manage tokens**
2. Click **Create token**
3. Configure the token:
   ```
   User: cite_api
   Service: CITE Integration Service
   IP restriction: (leave empty for now)
   Valid until: (leave empty for no expiration)
   ```
4. Click **Save changes**
5. **IMPORTANT:** Copy the generated token immediately (it looks like: `a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6`)

## Part 2: CodeIgniter Configuration

### Step 10: Update Environment Variables

1. Open `/path/to/your/project/.env` file
2. Update the Moodle configuration section:
   ```env
   # Moodle Integration
   MOODLE_URL = http://localhost:8081
   MOODLE_TOKEN = your_actual_token_here
   MOODLE_SYNC_ENABLED = true
   MOODLE_SSO_ENABLED = true
   MOODLE_SSO_SECRET = cite_moodle_sso_secret_key_2025
   ```
3. Replace `your_actual_token_here` with the token from Step 9
4. Save the file

### Step 11: Test the Integration

1. Access your CITE admin panel: http://localhost:8080/admin/moodle
2. Click **Test Connection** button
3. You should see:
   ```
   ✅ Connection: Success
   ✅ Authentication: Success  
   ✅ Web Services: Success
   ✅ Site Info: Retrieved
   ```

## Part 3: Using the Integration

### Available API Functions

Your CodeIgniter app now has access to these capabilities:

#### User Management
```php
// Get user by email
$user = $moodleAPI->getUserByEmail('student@example.com');

// Create new user
$userId = $moodleAPI->createUser([
    'username' => 'newstudent',
    'password' => 'TempPass123!',
    'firstname' => 'New',
    'lastname' => 'Student',
    'email' => 'newstudent@example.com'
]);
```

#### Course Management
```php
// Get all courses
$courses = $moodleAPI->getCourses();

// Create new course
$courseId = $moodleAPI->createCourse([
    'fullname' => 'Advanced PHP Programming',
    'shortname' => 'ADVPHP2025',
    'summary' => 'Learn advanced PHP concepts'
]);
```

#### Enrollment Management
```php
// Enroll user in course
$moodleAPI->enrollUser($userId, $courseId, 5); // 5 = Student role

// Get user's courses
$userCourses = $moodleAPI->getUserCourses($userId);
```

### SSO (Single Sign-On) Usage

```php
// Generate SSO login URL for user
$ssoUrl = $moodleSSO->generateSSOUrl($userId);

// Generate course-specific access URL
$courseUrl = $moodleSSO->generateCourseAccessUrl($userId, $courseId);
```

## Part 4: Troubleshooting

### Common Issues and Solutions

#### ❌ Problem: "Invalid token" error
**Solution:**
- Verify token is correctly copied to `.env` file
- Check that CITE Integration Service is enabled
- Ensure cite_api user is authorized for the service

#### ❌ Problem: "Function not available" error
**Solution:**
- Go to Moodle admin → External services → CITE Integration Service
- Add the missing function to the service
- Check function name spelling

#### ❌ Problem: Connection timeout
**Solution:**
- Verify Moodle is running at http://localhost:8081
- Check Docker containers are running: `docker compose ps`
- Test Moodle accessibility in browser

#### ❌ Problem: "Access denied" error
**Solution:**
- Ensure cite_api user has Manager role or appropriate capabilities
- Check IP restrictions on the token (should be empty for local development)

#### ❌ Problem: SSO redirect issues
**Solution:**
- Verify `MOODLE_SSO_SECRET` matches between systems
- Check that Moodle auth plugin for CITE is installed
- Ensure user exists in both systems

### Testing Individual Functions

Use the admin panel to test specific functions:

1. Go to http://localhost:8080/admin/moodle/courses
2. View synced courses from Moodle
3. Test user management functions
4. Verify grade synchronization

### Database Verification

Check if integration is working by examining the database:

```sql
-- Check users with Moodle IDs
SELECT id, name, email, moodle_id FROM users WHERE moodle_id IS NOT NULL;

-- Check courses linked to Moodle
SELECT id, title, moodle_id FROM courses WHERE moodle_id IS NOT NULL;
```

## Part 5: Security Considerations

### Production Setup

1. **Change default passwords:**
   - Update cite_api user password
   - Use strong, unique passwords

2. **Restrict API access:**
   - Set IP restrictions on tokens
   - Use HTTPS in production
   - Regularly rotate API tokens

3. **Monitor API usage:**
   - Enable Moodle web service logs
   - Monitor API call frequency
   - Set up alerts for failed authentications

### Environment Variables Security

Never commit these to version control:
- `MOODLE_TOKEN`
- `MOODLE_SSO_SECRET`
- Database passwords

## Next Steps

After successful integration:

1. **Course Synchronization:** Set up automated course sync
2. **Badge Integration:** Configure badge awarding system
3. **Grade Sync:** Implement grade passback from Moodle
4. **User Provisioning:** Set up automatic user creation
5. **Reports Integration:** Build unified reporting dashboard

## Support Resources

- [Moodle Web Services Documentation](https://docs.moodle.org/dev/Web_services)
- [CodeIgniter 4 Documentation](https://codeigniter.com/user_guide/)
- Project issues: Create issue in your repository

---

**Last Updated:** September 2025  
**Tested With:** Moodle 4.1+, CodeIgniter 4.6+, PHP 8.1+