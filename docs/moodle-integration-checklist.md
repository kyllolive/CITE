# Moodle Integration Quick Checklist

## Pre-Integration Checklist

- [ ] Moodle is running at http://localhost:8081
- [ ] CodeIgniter app is running at http://localhost:8080
- [ ] You have admin access to both systems
- [ ] Docker containers are up and running

## Moodle Configuration Checklist

### Basic Setup
- [ ] **Step 1:** Login to Moodle admin (admin/Admin@123)
- [ ] **Step 2:** Enable web services (Advanced features)
- [ ] **Step 3:** Enable REST protocol (Manage protocols)

### Service Setup
- [ ] **Step 4:** Create "CITE Integration Service" external service
- [ ] **Step 5:** Add required functions to service:
  - [ ] `core_webservice_get_site_info`
  - [ ] `core_user_get_users`
  - [ ] `core_user_create_users`
  - [ ] `core_course_get_courses`
  - [ ] `core_course_create_courses`
  - [ ] `core_course_update_courses`
  - [ ] `core_course_get_categories`
  - [ ] `core_enrol_get_users_courses`
  - [ ] `core_enrol_get_enrolled_users`
  - [ ] `enrol_manual_enrol_users`
  - [ ] `enrol_manual_unenrol_users`

### User Setup
- [ ] **Step 6:** Create cite_api user account
- [ ] **Step 7:** Assign Manager role to cite_api user
- [ ] **Step 8:** Authorize cite_api user for CITE Integration Service

### Token Generation
- [ ] **Step 9:** Generate API token for cite_api user
- [ ] **Step 9a:** Copy token immediately and save securely

## CodeIgniter Configuration Checklist

- [ ] **Step 10:** Update .env file with:
  - [ ] `MOODLE_URL = http://localhost:8081`
  - [ ] `MOODLE_TOKEN = [your-actual-token]`
  - [ ] `MOODLE_SYNC_ENABLED = true`
  - [ ] `MOODLE_SSO_ENABLED = true`

## Testing Checklist

- [ ] **Step 11:** Access admin panel at http://localhost:8080/admin/moodle
- [ ] **Step 11a:** Click "Test Connection" button
- [ ] **Step 11b:** Verify all tests pass:
  - [ ] Connection: Success ✅
  - [ ] Authentication: Success ✅
  - [ ] Web Services: Success ✅
  - [ ] Site Info: Retrieved ✅

## Post-Integration Verification

- [ ] Test user synchronization
- [ ] Test course listing
- [ ] Test enrollment functions
- [ ] Verify SSO login works
- [ ] Check error logs for any issues

## Quick Troubleshooting

| Issue | Quick Fix |
|-------|-----------|
| Invalid token error | Re-check token in .env file |
| Function not available | Add missing function to service |
| Connection timeout | Verify Moodle is running |
| Access denied | Check cite_api user permissions |

## Common Token Issues

- [ ] Token is exactly copied (no extra spaces)
- [ ] Service is enabled (not disabled)
- [ ] User is authorized for the service
- [ ] Functions are added to the service

## Security Checklist (Production)

- [ ] Change cite_api password from default
- [ ] Set IP restrictions on API token
- [ ] Enable HTTPS
- [ ] Rotate tokens regularly
- [ ] Monitor API usage logs

---

✅ **Integration Complete!** Your Moodle and CodeIgniter systems are now connected.

📖 **Full Documentation:** See `moodle-integration-setup.md` for detailed instructions.