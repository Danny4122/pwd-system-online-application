# CHO Module - Changes Documentation

## Overview
This document outlines the changes made to the CHO (City Health Office) module for the PWD Application System. The module allows CHO staff/doctors to manage PWD applications, view members, and approve/reject applications.

---

## Files Modified

### 1. `src/doctor/applications.php`
**Purpose:** Displays all applications with search, barangay filter, and pagination.

**Changes:**
- Converted from static HTML to dynamic PHP with PostgreSQL integration
- Added search functionality (by name, PWD number, barangay)
- Added barangay dropdown filter populated from database
- Added pagination (10 records per page)
- Added "Clear" button to reset search/filters
- Updated status badge to show workflow status (DRAFT, SUBMITTED, PDAO REVIEW, CHO REVIEW, ACCEPTED, FEEDBACK)
- Fixed badge width to prevent text cutoff
- Removed filter icon from search bar
- Data now pulled from `application_draft` table for accurate barangay display

### 2. `src/doctor/accepted.php`
**Purpose:** Displays approved/accepted applications.

**Changes:**
- Complete rewrite for consistent sidebar structure
- Added search and barangay filter functionality
- Added pagination
- Added "Clear" button for search
- Fixed sidebar styling to match other pages
- Data pulled from `application_draft` for barangay info

### 3. `src/doctor/pending.php`
**Purpose:** Displays applications pending CHO review (workflow_status = 'cho_review').

**Changes:**
- Added search and barangay filter functionality
- Added pagination
- Added "Clear" button for search
- Fixed search bar alignment
- Removed filter icon
- Data pulled from `application_draft` for barangay info

### 4. `src/doctor/denied.php`
**Purpose:** Displays denied/rejected applications.

**Changes:**
- Added search and barangay filter functionality
- Added pagination
- Added "Clear" button for search
- Fixed search bar alignment
- Removed filter icon
- Data pulled from `application_draft` for barangay info

### 5. `src/doctor/members.php`
**Purpose:** Displays list of approved PWD members.

**Changes:**
- Converted to dynamic PHP with database integration
- Added search functionality
- Added pagination
- Added avatar with initials
- Shows age calculated from birthdate
- Data pulled from `application_draft` for member info

### 6. `src/doctor/CHO_dashboard.php`
**Purpose:** Dashboard showing statistics and charts.

**Changes:**
- Updated to pull real statistics from database instead of hardcoded values
- Fixed ENUM casting issue (`application_type::text`) for PostgreSQL queries
- Statistics now show: PWDs (approved), NEW, RENEW, LOST ID counts

### 7. `src/doctor/view_a.php`
**Purpose:** View individual application details with approve/reject actions.

**Changes:**
- Added conditional display of action buttons (hidden when already approved/rejected)
- Fixed "Back to list" link to go to applications.php

### 8. `src/doctor/logout.php` (NEW FILE)
**Purpose:** Handle user logout.

**Features:**
- Clears all session variables
- Deletes session cookie
- Destroys session
- Redirects to signin page

---

## Files Modified (Backend/Config)

### 9. `api/admin_action.php`
**Purpose:** API endpoint for application actions (verify/reject).

**Changes:**
- Fixed PHP error on line 29 (`$_SESSION['is_admin']` access without isset check)
- Changed to `!empty($_SESSION['is_admin'])` for safe access

### 10. `includes/workflow.php`
**Purpose:** Defines workflow actions and role permissions.

**Changes:**
- Updated `cho_verify` action to set status to 'Approved' (was 'CHO Verified')
- Added 'ADMIN' role to allowed roles for `cho_verify` action

---

## Features Summary

### Search & Filter
- All list pages have search functionality
- Search works on: first name, last name, PWD number, barangay
- Barangay dropdown filter on application pages
- "Clear" button appears when search/filter is active

### Pagination
- All list pages have pagination (10 records per page)
- Shows Previous/Next buttons and page numbers
- Only displays when more than 10 records exist

### Status Badges
| Status | Color | Description |
|--------|-------|-------------|
| DRAFT | Gray | Application started but not submitted |
| SUBMITTED | Blue | Application submitted, awaiting PDAO |
| PDAO REVIEW | Purple | Under PDAO review |
| CHO REVIEW | Orange | Awaiting CHO verification |
| ACCEPTED | Green | Application approved |
| FEEDBACK | Red | Application denied/needs revision |

### Application Actions
- **Verify (confirm PWD):** Approves the application, sets status to 'Approved'
- **Reject:** Denies the application, requires remarks
- Action buttons hidden after application is processed

### Sidebar Navigation
- Dashboard
- Members (approved PWDs)
- Applications (all)
- Manage Applications submenu:
  - Accepted
  - Pending
  - Denied
- Logout

---

## Database Tables Used

- `application` - Main application records
- `applicant` - Applicant personal information
- `application_draft` - Form data stored as JSONB (step-based)
- `application_status_history` - Audit trail of status changes
- `documentrequirements` - Uploaded document paths

---

## Key Technical Notes

1. **Barangay Data Location:** Barangay is stored in `application_draft.data` (JSONB at step 1), not in the `applicant` table directly. All queries use `COALESCE(d.data->>'barangay', ap.barangay, '')` pattern.

2. **ENUM Casting:** PostgreSQL ENUM types require casting to text before using string functions: `LOWER(application_type::text)`

3. **Session Variables:** Role is stored in `$_SESSION['role']` and converted to uppercase for comparison.

4. **CSRF Protection:** All forms include CSRF token validation.
