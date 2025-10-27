E.129.100 - The system shall support the ability to enable/disable Configuration Monitor external module.
E.129.200 - The system shall support the ability to enable Configuration Monitor external module on all projects by default.
E.129.300 - The system shall support the ability to make Configuration Monitor external module discoverable by users.
E.129.400 - The system shall support the ability to allow non-admins to enable Configuration Monitor external module on projects.
E.129.500 - The system shall support the ability to hide Configuration Monitor external module from non-admins in the list of enabled modules on each project.
E.129.600 - The system shall support the ability to view the usage of Configuration Monitor external module.
E.129.700 - The system shall preserve changelog tables (user_role_changelog, project_changelog, system_changelog) when the module is disabled to maintain historical audit data.
E.129.800 - The system shall register a cron job (configuration_monitor_cron) when the module is enabled at the system level.
E.129.900 - The system shall de-register the configuration_monitor_cron job when the module is disabled at the system level.
E.129.1000 - The system shall support the ability to enable/disable User Role Changes tracking at the project level via the user-role-changes-enable setting.
E.129.1100 - The system shall support the ability to enable/disable Project Changes tracking at the project level via the project-changes-enable setting.
E.129.1200 - The system shall support the ability to enable/disable System Changes tracking at the system level via the system-changes-enable setting.
E.129.1300 - The system shall require at least one tracking option (user-role-changes-enable or project-changes-enable) to be enabled at the project level.
E.129.1400 - The system shall support the ability to configure the maximum number of days to display on change log pages at the project level (max-days-page setting).
E.129.1500 - The system shall support the ability to configure the maximum number of days to display on system change log page at the system level (sys-max-days-page setting).
E.129.1600 - The system shall support the ability to enable/disable email notifications at the project level via the email-enable setting.
E.129.1700 - The system shall support the ability to enable/disable system-level email notifications via the sys-email-enable setting.
E.129.1800 - The system shall support the ability to configure email settings for project-level notifications.
E.129.1900 - The system shall support the ability to configure email settings for system-level notifications.
E.129.2000 - The system shall validate that email settings are complete when email notifications are enabled at the project level.
E.129.2100 - The system shall validate that email settings are complete when email notifications are enabled at the system level.
E.129.2200 - The system shall validate that numeric settings (max-days-page, max-hours-email, sys-max-days-page, sys-max-hours-email) contain valid numeric values.
E.129.2300 - The system shall restrict project-level Configuration Monitor settings to super users only.
E.129.2400 - The system shall restrict system-level Configuration Monitor settings to super users only.
E.129.2500 - The system shall support the ability to display User Role Changes (Insert, Update, Delete operations) in a tabular format showing Role ID, Timestamp, Action, Changed Privilege, Old Value, and New Value.
E.129.2600 - The system shall support the ability to filter User Role Changes.
E.129.2700 - The system shall display form-specific changes for Data Export Instruments and Data Entry privileges in the format [form_name,permission_level].
E.129.2800 - The system shall support the ability to display Project Changes in a tabular format showing Timestamp, Changed Property, Old Value, and New Value.
E.129.2900 - The system shall support the ability to filter Project Changes.
E.129.3000 - The system shall support the ability to display System Changes in a tabular format showing Timestamp, Changed Property, Old Value, and New Value.
E.129.3100 - The system shall support the ability to filter System Changes.
E.129.3200 - The system shall restrict access to the System Changes page to super users only.
E.129.3300 - The system shall display the System Changes link in the Control Center's External Modules section when system-changes-enable is checked.
E.129.3400 - The system shall send automated email summaries for project-level configuration changes when email-enable is checked and changes occurred.
E.129.3500 - The system shall send automated email summaries for system-level configuration changes when sys-email-enable is checked and changes occurred.
E.129.3600 - The system shall include User Role Changes summary in project-level emails when user-role-changes-enable is checked and changes occurred.
E.129.3700 - The system shall include Project Changes summary in project-level emails when project-changes-enable is checked and changes occurred.
E.129.3800 - The system shall process all enabled projects in a single cron job run for project-level notifications.
E.129.3900 - The system shall restrict access to User Role Changes page to users with user_rights privilege or super user status.
E.129.4000 - The system shall restrict access to Project Changes page to users with user_rights privilege or super user status.
E.129.4100 - The system shall restrict access to System Changes page to super users only.
E.129.4200 - The system shall hide the User Role Changes link when user-role-changes-enable setting is not checked.
E.129.4300 - The system shall hide the Project Changes link when project-changes-enable setting is not checked.
E.129.4400 - The system shall hide the System Changes link when system-changes-enable setting is not checked.
E.129.4500 - The system shall hide module page links from users without user_rights privilege (unless they are super users).
E.129.4600 - The system shall support the ability to export User Role Changes to CSV format.
E.129.4700 - The system shall support the ability to export Project Changes to CSV format.
E.129.4800 - The system shall support the ability to export System Changes to CSV format.
E.129.4900 - The system shall support three export options: Export current page, Export all pages, Export everything ignoring filters.