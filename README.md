### Project Configuration Changes ###

The Project Configuration Changes module tracks and logs changes to project settings and user role privileges within REDCap projects. This module provides comprehensive auditing capabilities for project administrators to monitor and review configuration modifications over time.

This module creates database tables, triggers, and stored procedures when enabled at a system level. The database objects are automatically removed when the module is disabled.

#### System set up ####

Enabling the module at a system level will AUTOMATICALLY do the following via the system hook `redcap_module_system_enable`:

1. Create the `user_role_changelog` table in the REDCap database to store a log of all user role privilege changes
1. Create database triggers on table redcap_user_roles to automatically log changes to user roles when enabled in the project:
   - `user_role_insert_trigger` - logs when new user roles are created
   - `user_role_update_trigger` - logs when existing user roles are modified
   - `user_role_delete_trigger` - logs when user roles are deleted
1. Create the `GetUserRoleChanges` stored procedure to retrieve filtered user role change logs with pagination support
1. Create the `project_changelog` table in the REDCap database to store a log of all project setting changes
1. Create the `projects_update_trigger` database trigger on table redcap_projects to automatically log changes to project settings when enabled in the project (including when projects are deleted, as deletions are recorded as updates with the `date_deleted` timestamp set)
1. Create the `GetProjectChanges` stored procedure to retrieve filtered project change logs with pagination support
1. Register the `projectConfigCron` cron job to run every 3 hours. When executed, it checks all projects with both the module and email notifications enabled, then sends summary emails of recent configuration changes.
   
Disabling the module at a system level will AUTOMATICALLY do the following via the system hook `redcap_module_system_disable`:

1. Drop all three user role triggers (`user_role_insert_trigger`, `user_role_update_trigger`, `user_role_delete_trigger`)
1. Drop the `GetUserRoleChanges` stored procedure
1. Drop the `projects_update_trigger` trigger
1. Drop the `GetProjectChanges` stored procedure
1. De-register the `projectConfigCron` cron job

**Note:** The changelog tables (`user_role_changelog` and `project_changelog`) are NOT automatically dropped when the module is disabled to preserve historical audit data. To remove these tables, you must delete them manually using your database administration tools.

When a new version of the module becomes available, the module should be disabled and then re-enabled from the Control Center at the system level to ensure database objects are properly updated.

#### Set up and module configuration by project ####

After enabling the module at the system level, configure the following settings for each project where you want to use the module:

1. **Enable specific tracking features** - Choose which types of changes to track and display:
   - `user-role-changes-enable` - Enable tracking and display of user role privilege changes
   - `project-changes-enable` - Enable tracking and display of project setting changes

   At least one of these must be enabled for the module to function.

1. **Configure display settings** - Control how many days of history to show by default:
   - `max-days-page` - Maximum number of days to display in the change log pages (default: 7 days). This sets the initial date range when viewing the logs. Users can adjust the date range using the interface.

1. **Configure email notifications** (optional) - Set up automated email alerts for configuration changes:
   - `email-enable` - Enable email notifications
   - `from-emailid` - Email address that automated notifications will be sent from
   - `to-emailids` - One or more email addresses to receive notifications (supports multiple recipients)
   - `max-hours-email` - Maximum number of hours to look back when generating email reports (default: 3 hours). The cron job will email a summary of changes that occurred within this time window.
   - Only projects with changes during the time window will trigger emails
   - The email will only include the User Role and Project Changes if enabled

#### Module access and permissions ####

The module respects REDCap's user rights system:

- Only users with **User Rights** privileges or **Super Users** can access the module pages
- Users without these privileges will not see the module links in the project's External Modules menu
- Individual link visibility is controlled by the `user-role-changes-enable` and `project-changes-enable` settings

#### User Role Changes log page ####

The "Changes in User Role Privileges" page (`userRoleChanges.php`) displays a comprehensive log of all modifications to user role privileges, including:

**Logged information:**
- Role ID - The unique identifier of the role that was changed
- Timestamp - When the change occurred
- Action - The type of change (INSERT, UPDATE, or DELETE)
- Changed Privilege - The specific privilege that was modified (or "All Privileges" for INSERT/DELETE actions)
- Old Value - The previous value of the privilege
- New Value - The new value of the privilege

**Tracked privileges include:**
- Role Name, Lock Record, Lock Record Multiform, Lock Record Customize, Data Export Instruments, 
- Data Import Tool, Data Comparison Tool, Data Logging, Email Logging, File Repository, Double Data, 
- User Rights, Data Access Groups, Graphical, Reports, Design, Alerts, Calendar, Data Entry, API Export, 
- API Import, API Modules, Mobile App, Mobile App Download Data, Record Create, Record Rename, Record Delete, 
- Dts, Participants, Data Quality Design, Data Quality Execute, Data Quality Resolution, Random Setup, Random Dashboard, 
- Random Perform, Realtime Webservice Mapping, Realtime Webservice Adjudicate, External Module Config, Mycap Participants

**Special handling for complex privileges:**
- "Data Export Instruments" and "Data Entry" privileges contain multiple form-specific values in the format `[form_name,permission_level]`
- The module intelligently compares these complex values and displays only the specific forms/permissions that changed

**Page features:**
- **Filter by User Role** - Dropdown to show changes for a specific role or all roles
- **Date range filtering** - Set custom start and end dates, or use quick filters:
  - Past day, Past week, Past month, Past year, Custom range
- **Sorting** - Order results by timestamp (ascending or descending)
- **Pagination** - Configurable page size (10, 25, 50, 100, or all records)
- **Export options:**
  - Export current page - Download visible records as CSV
  - Export all pages - Download all filtered records as CSV
  - Export everything ignoring filters - Download complete log as CSV
- **Reset button** - Clear all filters and return to default view

#### Project Changes log page ####

The "Changes in Project Settings" page (`projectChanges.php`) displays a comprehensive log of all modifications to project-level settings, including:

**Logged information:**
- Timestamp - When the change occurred
- Changed Property - The specific project setting that was modified
- Old Value - The previous value of the setting
- New Value - The new value of the setting

**Tracked project properties include:**
- Project Name, App Title, Status, Inactive Time, Completed Time, Completed By, Data Locked, Draft Mode, Surveys Enabled,
- Repeat Forms, Scheduling, Purpose, Purpose Other, Show Which Records, Count Project, Investigators, Project Note, Online Offline,
- Auth Meth, Double Data Entry, Project Language, Project Encoding, Is Child Of, Date Shift Max, Institution, Site Org Type,
- Grant Cite, Project Contact Name, Project Contact Email, Header Logo, Auto Inc Set, Custom Data Entry Note, Custom Index Page Note,
- Order Id By, Custom Reports, Report Builder, Disable Data Entry, Google Translate Default, Require Change Reason, Dts Enabled, 
- Project Pi Firstname, Project Pi Mi, Project Pi Lastname,Project Pi Email, Project Pi Alias, Project Pi Username, 
- Project Pi Pub Exclude, Project Pub Matching Institution, Project Irb Number, Project Grant Number, History Widget Enabled, Secondary Pk, 
- Secondary Pk Display Value, Secondary Pk Display Label, Custom Record Label, Display Project Logo Institution, Imported From Rs, 
- Display Today Now Button, Auto Variable Naming, Randomization, Enable Participant Identifiers, Survey Email Participant Field, 
- Survey Phone Participant Field, Data Entry Trigger Url, Template Id, Date Deleted, Data Resolution Enabled, Field Comment Edit Delete, 
- Drw Hide Closed Queries From Dq Results, Realtime Webservice Enabled, Realtime Webservice Type, Realtime Webservice Offset Days,
- Realtime Webservice Offset Plusminus, Edoc Upload Max, File Attachment Upload Max, Survey Queue Custom Text, Survey Queue Hide, 
- Survey Auth Enabled, Survey Auth Field1, Survey Auth Event Id1, Survey Auth Field2, Survey Auth Event Id2, Survey Auth Field3, 
- Survey Auth Event Id3, Survey Auth Min Fields, Survey Auth Apply All Surveys, Survey Auth Custom Message,
- Survey Auth Fail Limit, Survey Auth Fail Window, Twilio Enabled, Twilio Modules Enabled, Twilio Hide In Project, Twilio Account Sid,
- Twilio Auth Token, Twilio From Number, Twilio Voice Language, Twilio Option Voice Initiate, Twilio Option Sms Initiate,
- Twilio Option Sms Invite Make Call, Twilio Option Sms Invite Receive Call, Twilio Option Sms Invite Web, Twilio Default Delivery Preference,
- Twilio Request Inspector Checked, Twilio Request Inspector Enabled, Twilio Append Response Instructions, Twilio Multiple Sms Behavior,
- Twilio Delivery Preference Field Map, Mosio Api Key, Mosio Hide In Project, Two Factor Exempt Project, Two Factor Force Project, 
- Disable Autocalcs, Custom Public Survey Links, Pdf Custom Header Text, Pdf Show Logo Url, Pdf Hide Secondary Field, Pdf Hide Record Id, 
- Shared Library Enabled, Allow Delete Record From Log, Delete File Repository Export Files, Custom Project Footer Text, 
- Custom Project Footer Text Link, Google Recaptcha Enabled, Datamart Allow Repeat Revision, Datamart Allow Create Revision, 
- Datamart Enabled, Break The Glass Enabled, Datamart Cron Enabled, Datamart Cron End Date, Fhir Include Email Address Project, 
- File Upload Vault Enabled, File Upload Versioning Enabled, Missing Data Codes, Record Locking Pdf Vault Enabled,
- Record Locking Pdf Vault Custom Text, Fhir Cdp Auto Adjudication Enabled, Fhir Cdp Auto Adjudication Cronjob Enabled, 
- Project Dashboard Min Data Points, Bypass Branching Erase Field Prompt, Protected Email Mode, Protected Email Mode Custom Text, 
- Protected Email Mode Trigger, Protected Email Mode Logo, Hide Filled Forms, Hide Disabled Forms, Form Activation Survey Autocontinue, 
- Sendgrid Enabled, Sendgrid Project Api Key, Mycap Enabled, File Repository Total Size, Ehr Id, Allow Econsent Allow Edit, 
- Store In Vault Snapshots Containing Completed Econsent

**Page features:**
- **Filter by Changed Properties** - Dropdown to show changes to a specific property or all properties
- **Date range filtering** - Set custom start and end dates, or use quick filters:
  - Past day, Past week, Past month, Past year, Custom range
- **Sorting** - Order results by timestamp (ascending or descending)
- **Pagination** - Configurable page size (10, 25, 50, 100, or all records)
- **Export options:**
  - Export current page - Download visible records as CSV
  - Export all pages - Download all filtered records as CSV
  - Export everything ignoring filters - Download complete log as CSV
- **Reset button** - Clear all filters and return to default view

#### Email notifications ####

When the email settings are provided, the module will automatically send email summaries:

**Email content includes:**
- Project ID and project title
- Summary of user role privilege changes (if `user-role-changes-enable` is checked and changes occurred)
- Summary of project setting changes (if `project-changes-enable` is checked and changes occurred)
- Formatted tables showing all changes within the configured time window (default: past 3 hours)
- Timestamp information using the REDCap system default date/time format

**Email behavior:**
- Emails are only sent if there are actual changes to privileges
- Multiple projects can be monitored by enabling the module in each project
- The cron job processes all enabled projects in a single run
- Recipients are configured per-project, allowing different notification lists for different projects

#### Technical details ####

**Database structure:**

The `user_role_changelog` table captures the full state of a role before and after changes:
- Stores all 39 role privilege fields as pipe-delimited strings for efficient comparison
- Includes role_id, action type (INSERT/UPDATE/DELETE), timestamp, and old/new value concatenations

The `project_changelog` table captures the full state of project settings before and after changes:
- Stores all 173 project property fields as pipe-delimited strings
- Includes project_id, action type (UPDATE only), timestamp, and old/new value concatenations

**Change detection:**

The module's `recordDiff()` method compares old and new values:
- For INSERT and DELETE actions, returns a single "All Privileges" row
- For UPDATE actions, parses the pipe-delimited strings and identifies only the fields that changed
- For complex multi-value fields in User Role (Data Export Instruments, Data Entry), performs deep comparison at the form level
- Returns granular change information for easy review and auditing

**Performance considerations:**

- Database triggers operate automatically with minimal performance impact
- Stored procedures provide efficient filtered queries with proper pagination
- When filtering by specific project properties, the module may load more records into memory
- Export functionality streams results directly to CSV for memory efficiency