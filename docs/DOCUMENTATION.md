# Configuration Monitor Module

## Overview

The Configuration Monitor module tracks and logs changes to REDCap configurations at both the system and project levels. It provides comprehensive auditing capabilities for administrators to monitor and review configuration modifications over time.

**Version:** 1.0.0
**Namespace:** `CCTC\ConfigurationMonitorModule`
**Framework Version:** 14
**Author:** Mintoo Xavier (Cambridge University Hospital - Cambridge Cancer Trials Centre)

## Features

- **User Role Changes Tracking**: Monitors and logs all modifications to user role privileges within projects (39 tracked privileges)
- **Project Configuration Changes**: Tracks changes to project-level settings (173 tracked properties)
- **System Configuration Changes**: Monitors Control Center system-level configuration changes
- **Email Notifications**: Automated email alerts when configuration changes occur
- **CSV Export**: Export change logs to CSV format
- **Filtering & Pagination**: Search and filter changes by date, role, property, and more

## Compatibility

| Requirement | Version |
|-------------|---------|
| PHP | 8.0.27 - 8.2.29 |
| REDCap | 14.7.0 - 15.9.1 |

## File Structure

```
configuration_monitor_v1.0.0/
├── ConfigurationMonitorModule.php   # Main module class with hooks
├── config.json                      # Module configuration
├── GetDbData.php                    # Database query helper class
├── Utility.php                      # Date/time utility functions
├── Rendering.php                    # UI rendering helper functions
├── getparams.php                    # Parameter handling utilities
├── userRoleChanges.php              # User role changes page
├── projectChanges.php               # Project changes page
├── systemChanges.php                # System changes page (Control Center)
├── csv_export.php                   # CSV export functionality
├── README.md                        # User-facing documentation
├── DOCUMENTATION.md                 # Technical documentation (this file)
├── sql-setup/                       # SQL scripts for database setup
│   ├── 0010_create_table_user_role_changelog.sql
│   ├── 0020_user_roles_InsertTrigger.sql
│   ├── 0030_user_roles_UpdateTrigger.sql
│   ├── 0040_user_roles_DeleteTrigger.sql
│   ├── 0050_create_UserRoleChange_proc.sql
│   ├── 0060_create_table_project_changelog.sql
│   ├── 0070_projects_UpdateTrigger.sql
│   ├── 0080_create_ProjectChange_proc.sql
│   ├── 0090_create_table_system_changelog.sql
│   ├── 0100_system_UpdateTrigger.sql
│   └── 0110_create_SystemChange_proc.sql
└── automated_tests/                 # Cypress automated tests
```

## Database Objects

When the module is enabled at the system level, the following database objects are automatically created:

### Tables

| Table Name | Description |
|------------|-------------|
| `user_role_changelog` | Stores history of user role privilege changes |
| `project_changelog` | Stores history of project setting changes |
| `system_changelog` | Stores history of system configuration changes |

### Triggers

| Trigger Name | Table | Description |
|--------------|-------|-------------|
| `user_roles_insert_trigger` | `redcap_user_roles` | Logs new role creation |
| `user_roles_update_trigger` | `redcap_user_roles` | Logs role modifications |
| `user_roles_delete_trigger` | `redcap_user_roles` | Logs role deletions |
| `projects_update_trigger` | `redcap_projects` | Logs project setting changes |
| `system_update_trigger` | `redcap_config` | Logs system configuration changes |

### Stored Procedures

| Procedure Name | Description |
|----------------|-------------|
| `GetUserRoleChanges` | Retrieves filtered user role change logs with pagination |
| `GetProjectChanges` | Retrieves filtered project change logs with pagination |
| `GetSystemChanges` | Retrieves filtered system change logs with pagination |

## Configuration Settings

### System-Level Settings

| Setting Key | Type | Description | Default |
|-------------|------|-------------|---------|
| `system-changes-enable` | Checkbox | Enable tracking of system configuration changes | Unchecked |
| `sys-max-days-page` | Text | Maximum days to display in logs | 7 |
| `sys-email-enable` | Checkbox | Enable email notifications for system changes | Unchecked |
| `sys-from-emailid` | Email | Sender email address for notifications | - |
| `sys-to-emailids` | Email (repeatable) | Recipient email addresses | - |
| `sys-max-hours-email` | Text | Hours to look back for email reports | 3 |

### Project-Level Settings

| Setting Key | Type | Description | Default |
|-------------|------|-------------|---------|
| `user-role-changes-enable` | Checkbox | Enable tracking of user role changes | Unchecked |
| `project-changes-enable` | Checkbox | Enable tracking of project setting changes | Unchecked |
| `max-days-page` | Text | Maximum days to display in logs | 7 |
| `email-enable` | Checkbox | Enable email notifications | Unchecked |
| `from-emailid` | Email | Sender email address for notifications | - |
| `to-emailids` | Email (repeatable) | Recipient email addresses | - |
| `max-hours-email` | Text | Hours to look back for email reports | 3 |

## Access Control

- **Project Pages**: Requires User Rights privileges or Super User status
- **System Changes Page**: Requires Super User status
- Module links are hidden from users without appropriate permissions
- All configuration settings are restricted to super users only

## Hooks Used

| Hook | Purpose |
|------|---------|
| `redcap_module_system_enable` | Creates database tables, triggers, and stored procedures |
| `redcap_module_system_disable` | Removes triggers and stored procedures (tables preserved) |
| `redcap_module_link_check_display` | Controls visibility of module links based on settings and permissions |

## Cron Job

| Property | Value |
|----------|-------|
| Name | `configuration_monitor_cron` |
| Frequency | Every 30 minutes (1800 seconds) |
| Max Run Time | 15 minutes (900 seconds) |
| Function | Sends email notifications for recent configuration changes |

## Data Flow

### Change Capture Flow

```
1. User modifies project setting, user role, or system config
   ↓
2. Database trigger fires (INSERT, UPDATE, or DELETE)
   ↓
3. Change logged to custom table with timestamp, action type, old/new values
   ↓
4. Data available for viewing and export
```

### Email Notification Flow

```
1. Cron job runs every 30 minutes
   ↓
2. For each project with module and email enabled:
   - Query changes within configured time window (default: 3 hours)
   - If changes exist, generate HTML email and send to recipients
   ↓
3. For system-level (if enabled):
   - Query system changes within configured time window
   - If changes exist, send system email to recipients
```

### Page Display Flow

```
1. User clicks module link in project menu
   ↓
2. Hook checks user permissions and page settings
   ↓
3. Page loads with default filters (e.g., past 7 days)
   ↓
4. Stored procedure retrieves filtered data
   ↓
5. recordDiff() processes changes to extract modified fields
   ↓
6. makeTable() renders HTML display
```

## Classes and Key Methods

### ConfigurationMonitorModule

Main module class extending `AbstractExternalModule`.

| Method | Description |
|--------|-------------|
| `validateSettings($settings)` | Validates module configuration on save |
| `redcap_module_link_check_display()` | Controls link visibility based on permissions |
| `redcap_module_system_enable()` | Sets up database objects on enable |
| `redcap_module_system_disable()` | Cleans up database objects on disable |
| `recordDiff($dc, $tableName)` | Compares old/new values to identify changes |
| `makeTable($dcs, $userDateFormat, $tableName)` | Generates HTML table of changes |
| `sendEmail()` | Sends project-level email notifications |
| `sendSysEmail()` | Sends system-level email notifications |
| `configMonitorCron($cronInfo)` | Cron job entry point |

### GetDbData

Database query helper class.

| Method | Description |
|--------|-------------|
| `GetChangesFromSP()` | Calls stored procedures to retrieve change logs |
| `GetDataChangesFromResult()` | Parses database results into arrays |
| `validateDateParam()` | Validates and sanitizes date parameters |

### Utility

Date/time utility class.

| Method | Description |
|--------|-------------|
| `UserDateFormat()` | Gets user's preferred date format |
| `Now()` | Returns current DateTime |
| `NowAdjusted($modifier)` | Returns adjusted DateTime (e.g., "-3 hours") |
| `DateStringToDbFormat($date)` | Converts date string to database format (YmdHis) |

### Rendering

UI rendering helper class.

| Method | Description |
|--------|-------------|
| `MakePageSizeSelect()` | Generates pagination size dropdown |
| `MakeRetDirectionSelect()` | Generates sort direction dropdown |
| `MakeRoleSelect()` | Generates user role filter dropdown |
| `MakeFieldNameSelect()` | Generates system field filter dropdown |

## Page Features

All log pages (User Role Changes, Project Changes, System Changes) include:

- **Date Range Filtering**: Quick filters (Past day/week/month/year) or custom range
- **Property/Role Filtering**: Filter by specific changed property or role
- **Sorting**: Ascending or descending by timestamp
- **Pagination**: 10, 25, 50, 100, or 250 records per page
- **Export Options**:
  - Export current page as CSV
  - Export all filtered results as CSV
  - Export everything ignoring filters as CSV
- **Reset Button**: Clear all filters and return to defaults

## Tracked Properties

### User Role Privileges (39 fields)

- Role Name, Lock Record, Lock Record Multiform, Lock Record Customize
- Data Export Instruments, Data Import Tool, Data Comparison Tool
- Data Logging, Email Logging, File Repository, Double Data
- User Rights, Data Access Groups, Graphical, Reports, Design
- Alerts, Calendar, Data Entry, API Export, API Import, API Modules
- Mobile App, Mobile App Download Data, Record Create, Record Rename, Record Delete
- DTS, Participants, Data Quality Design/Execute/Resolution
- Random Setup/Dashboard/Perform, Realtime Webservice Mapping/Adjudicate
- External Module Config, MyCap Participants

### Project Properties (173 fields)

Including but not limited to:
- Basic: Project Name, App Title, Status, Purpose, Institution
- Configuration: Draft Mode, Surveys Enabled, Repeat Forms, Scheduling
- Security: Auth Method, Two Factor settings, Data Locked
- Features: Randomization, DTS, Data Resolution, Twilio, Sendgrid, MyCap
- Display: Custom notes, Header Logo, PDF settings, Hide forms options

### System Properties

All system-wide configuration settings from the Control Center including authentication, email, API, security, and file upload settings.

## Development Guide

### Adding a New Tracked Field

1. Update trigger SQL to include new field in `CONCAT_WS()`
2. Update column array in `recordDiff()` method
3. Re-install trigger (disable/enable module)

### Modifying Display Pages

All pages follow the pattern: Parse params → Query data → Render filters → Display table → Export buttons

### Customizing Emails

- Edit `sendEmail()` for project-level notifications
- Edit `sendSysEmail()` for system-level notifications
- Both called by `configMonitorCron()` cron job

## Troubleshooting

### Links Not Appearing

- Verify user has `user_rights` permission or superuser status
- Check that corresponding page is enabled in module settings
- Confirm module is enabled for the project

### No Changes Displayed

- Verify triggers exist: `SHOW TRIGGERS LIKE 'redcap_user_roles'`
- Check date range includes expected changes
- Verify stored procedures exist: `SHOW PROCEDURE STATUS WHERE Name LIKE 'Get%Changes'`

### Email Not Sending

- Check `email-enable` is checked
- Verify email addresses are configured
- Confirm REDCap cron is running
- Check changes occurred within the configured time window

### Database Verification

```sql
-- Check tables
SHOW TABLES LIKE '%changelog%';

-- Check triggers
SHOW TRIGGERS LIKE 'redcap_user_roles';
SHOW TRIGGERS LIKE 'redcap_projects';
SHOW TRIGGERS LIKE 'redcap_config';

-- Check stored procedures
SHOW PROCEDURE STATUS WHERE Name LIKE 'Get%Changes';

-- View recent changes
SELECT * FROM user_role_changelog ORDER BY timestamp DESC LIMIT 10;
SELECT * FROM project_changelog ORDER BY timestamp DESC LIMIT 10;
SELECT * FROM system_changelog ORDER BY timestamp DESC LIMIT 10;
```

## Important Notes

1. **Data Preservation**: Changelog tables are NOT dropped when the module is disabled to preserve audit history
2. **Module Updates**: When updating to a new version, disable and re-enable the module to update database objects
3. **Settings Validation**: At least one of `user-role-changes-enable` or `project-changes-enable` must be enabled at the project level
4. **Email Notifications**: Emails are only sent when there are actual changes to report
5. **Module Directory Prefix**: `configuration_monitor` (used in database triggers and settings checks)

---

*Module Version: 1.0.0*
*REDCap Framework Version: 14*
