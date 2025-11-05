Feature: E.129.800 - The system shall support the ability to set up and view logs using Data Entry Log module.
  
  As a REDCap end user
  I want to see that Data Entry Log External Module work as expected

  Scenario: Enable external Module from Control Center
    Given I login to REDCap with the user "Test_Admin"
    When I click on the link labeled "Control Center"
    # EMAIL ADDRESS SET FOR REDCAP ADMIN - without it, emails are not send out from system
    When I click on the link labeled "General Configuration"
    Then I should see "General Configuration"
    When I enter "redcap@test.instance" into the input field labeled "Email Address of REDCap Administrator"
    And I click on the button labeled "Save Changes"
    Then I should see "Your system configuration values have now been changed"

    Given I click on the link labeled exactly "Manage"
    Then I should see "External Modules - Module Manager"
    And I should NOT see "Configuration Monitor - v1.0.0"
    When I click on the button labeled "Enable a module"
    And I click on the button labeled Enable for the external module named "Configuration Monitor"
    And I click on the button labeled "Enable" in the dialog box
    Then I should see "Configuration Monitor - v1.0.0"
 
  Scenario: Enable external module in project
    Given I create a new project named "E.129.800" by clicking on "New Project" in the menu bar, selecting "Practice / Just for fun" from the dropdown, choosing file "redcap_val/Project_redcap_val_nodata.xml", and clicking the "Create Project" button
    When I click on the link labeled exactly "Manage"
    Then I should see "External Modules - Project Module Manager"
    And I should NOT see "Configuration Monitor - v1.0.0"
    When I click on the button labeled "Enable a module"
    Then I should see "Available Modules"
    And I click on the button labeled Enable for the external module named "Configuration Monitor - v1.0.0"
    Then I should see "Configuration Monitor - v1.0.0"
    And I should see "Module that displays the configuration changes"

    When I click on the link labeled exactly "Manage"
    Then I should see "External Modules - Project Module Manager"
    When I click on the button labeled exactly "Configure"
    Then I should see "Configure Module"
    Then I should see "Enable Project Changes"
    # Added te below 2 steps else cypress was not loading the dialog box properly
    When I check the checkbox labeled "Enable Project Changes"
    When I uncheck the checkbox labeled "Enable Project Changes"
    And I click on the button labeled "Save" in the dialog box
    Then I should see "Enable Project Changes"
    When I check the checkbox labeled "Enable Project Changes"
    And I click on the button labeled "Save" in the dialog box
    Then I should see "Configuration Monitor - v1.0.0"
    And I should see a link labeled "Project Changes"

    When I click on the link labeled "Project Changes"
    Then I should see "Changes in Project Settings"
    And I should see "This log shows changes made to project settings"
    And I should see "No changes to project settings have been made in this project"

    When I click on the link labeled "Project Setup"
    And I click on the button labeled "Disable" in the "Auto-numbering for records" row in the "Enable optional modules and customizations" section
    Then I should see a button labeled "Enable" in the "Auto-numbering for records" row in the "Enable optional modules and customizations" section

    When I click on the link labeled "Project Changes"
    Then I should see "This log shows changes made to project settings"
    And I should see a table header and rows containing the following values in the a table:
      |  Date / Time      | Changed Property | Old Value | New Value |
      |  mm/dd/yyyy hh:mm | Auto Inc Set	   | 1	       | 0         |

    Given I click on the link labeled exactly "Manage"
    Then I should see "External Modules - Project Module Manager"
    When I click on the button labeled exactly "Configure"
    Then I should see "Configure Module"
    When I check the checkbox labeled "Enable Email"
    And I click on the button labeled "Save" in the dialog box
    Then I should see "Provide the email address used to send notifications" in the dialog box
    When I enter "from@proj.edu" into the input field labeled "Provide the email address used to send notifications:" in the dialog box
    And I click on the button labeled "Save" in the dialog box
    Then I should see "1. Provide the email address to receive configuration change notifications" in the dialog box
    When I enter "to@proj.edu" into the input field labeled "1. Provide the email address to receive configuration change notifications" in the dialog box
    And I click on the button labeled "Save" in the dialog box
    Then I should see "Configuration Monitor - v1.0.0"

    # E.129.2200 - validate numeric values for page display
    When I click on the button labeled exactly "Configure"
    Then I should see "Configure Module"
    When I enter "4.8" into the input field labeled "Specify the maximum number of days to look back when displaying configuration changes on the page (default 7 days)" in the dialog box
    And I click on the button labeled "Save" in the dialog box
    Then I should see "Specify the maximum number of days to look back when displaying configuration changes on the page (default 7 days)" in the dialog box
    When I clear field and enter "4" into the input field labeled "Specify the maximum number of days to look back when displaying configuration changes on the page (default 7 days)" in the dialog box
    And I click on the button labeled "Save" in the dialog box
    Then I should see "Configuration Monitor - v1.0.0"

    # E.129.2200 - validate numeric values for email notifications
    When I click on the button labeled exactly "Configure"
    Then I should see "Configure Module"
    When I enter "2.7" into the input field labeled "Specify the maximum number of hours to look back when sending email notifications (default 3 hours)" in the dialog box
    And I click on the button labeled "Save" in the dialog box
    Then I should see "Specify the maximum number of hours to look back when sending email notifications (default 3 hours)" in the dialog box
    When I clear field and enter "2" into the input field labeled "Specify the maximum number of hours to look back when sending email notifications (default 3 hours)" in the dialog box
    And I click on the button labeled "Save" in the dialog box
    Then I should see "Configuration Monitor - v1.0.0"

    Given I click on the link labeled "Project Setup"
    When I click on the button labeled "Enable" in the "Scheduling module" row in the "Enable optional modules and customizations" section
    Then I should see a button labeled "Disable" in the "Scheduling module" row in the "Enable optional modules and customizations" section

    Given I click on the button labeled "Additional customizations"
    When I check the checkbox labeled Require a 'reason' when making changes to existing records in additional customizations
    And I select "Data Resolution Workflow" in the dropdown field labeled "Enable:"
    And I check the checkbox labeled Enable the Data History popup for all data collection instruments in additional customizations
    When I click on the button labeled "Save"
    Then I should see "The Data Resolution Workflow has now been enabled!"
    And I click on the button labeled "Close" in the dialog box

    When I click on the link labeled "Project Changes"
    Then I should see "This log shows changes made to project settings"
    And I should see a table header and rows containing the following values in the a table:
      | Changed Property                        | Old Value | New Value               |
      | Require Change Reason	                  | 0	        | 1                       |
      | Secondary Pk Display Value	            | 1	        | 0                       |
      | Secondary Pk Display Label	            | 1	        | 0                       |
      | Data Resolution Enabled	                | 1	        | 2                       |
      | Field Comment Edit Delete	              | 1	        | 0                       |
      | Drw Hide Closed Queries From Dq Results | 1	        | 0                       |
      | Protected Email Mode Custom Text	      | 	        | REDCap Secure Messaging |
      | Scheduling                              | 0	        | 1                       |
      | Auto Inc Set	                          | 1	        | 0                       |

    And I should see 9 rows in the project changes table

    # When I select "project_contact_email" on the dropdown field labeled "Property"
    # Then I should see a table header and rows containing the following values in the a table:
    #   |  Date / Time      | Changed Property      | Old Value               | New Value               |
    #   |  mm/dd/yyyy hh:mm | project_contact_email |                        	| redcap@test.instance    |

    # And I should see 1 row in the system changes table
    # And I should NOT see "auto_report_stats"
    # And I should NOT see "redcap_base_url"

    # When I click on the button labeled "Export current page"
    # Then the downloaded CSV with filename "SystemChanges_yyyy-mm-dd_hhmm.csv" has the header and rows below
    #   | changed property      | old value               | new value               |
    #   | project_contact_email |                        	| redcap@test.instance    |

    # When I click on the button labeled "Export everything ignoring filters"
    # Then the downloaded CSV with filename "SystemChanges_yyyy-mm-dd_hhmm.csv" has the header and rows below
    #   | changed property      | old value               | new value               |
    #   | auto_report_stats     | 1                     	| 0                       |
    #   | redcap_base_url       | https://localhost:8443	| https://localhost:8443/ |
    #   | project_contact_email |                        	| redcap@test.instance    |

    # And I wait for 10 seconds
    # # Scenario: E.129.100 - Disable external module
    # # Disable external module in project
    # Given I click on the link labeled exactly "Manage"
    # Then I should see "External Modules - Project Module Manager"
    # And I should see "Configuration Monitor - v1.0.0"
    # When I click on the button labeled exactly "Disable"
    # Then I should see "Disable module?" in the dialog box
    # When I click on the button labeled "Disable module" in the dialog box
    # Then I should NOT see "Configuration Monitor - v1.0.0"

    # Given I click on the link labeled "Logging"
    # Then I should see a table header and row containing the following values in the logging table:
    #   | Date / Time      | Username   | Action                                                                       | List of Data Changes OR Fields Exported                                                                                                                           |
    #   | mm/dd/yyyy hh:mm | test_admin | Disable external module "data_entry_log_v0.0.0" for project                  |                                                                                                                                                                   |
    #   | mm/dd/yyyy hh:mm | test_admin | Modify configuration for external module "data_entry_log_v0.0.0" for project | always-exclude-fields-with-regex, display-event-id-with-event-name, display-arm-id-with-arm-name, display-dag-id-with-dag-name                                    |
    #   | mm/dd/yyyy hh:mm | test_admin | Modify configuration for external module "data_entry_log_v0.0.0" for project | reserved-hide-from-non-admins-in-project-list, max-days-all-records, display-event-id-with-event-name, display-arm-id-with-arm-name, display-dag-id-with-dag-name |
    #   | mm/dd/yyyy hh:mm | test_admin | Enable external module "data_entry_log_v0.0.0" for project                   |                                                                                                                                                                   |

    # # Disable external module in Control Center
    # Given I click on the link labeled "Control Center"
    # When I click on the link labeled exactly "Manage"
    # And I click on the button labeled exactly "Disable"
    # Then I should see "Disable module?" in the dialog box
    # When I click on the button labeled "Disable module" in the dialog box
    # Then I should NOT see "Configuration Monitor - v0.0.0"

    # Given I click on the link labeled "User Activity Log"
    # Then I should see a table header and row containing the following values in a table:
    #   | Time             | User       | Event                                                                               |
    #   | mm/dd/yyyy hh:mm | test_admin | Disable external module "configuration_monitor_v1.0.0" for system                   |
    #   | mm/dd/yyyy hh:mm | test_admin | Disable external module "configuration_monitor_v1.0.0" for project                  |
    #   | mm/dd/yyyy hh:mm | test_admin | Modify configuration for external module "configuration_monitor_v1.0.0" for project |
    #   | mm/dd/yyyy hh:mm | test_admin | Enable external module "configuration_monitor_v1.0.0" for project                   |
    #   | mm/dd/yyyy hh:mm | test_admin | Enable external module "configuration_monitor_v1.0.0" for system                    |

    # And I logout

    # # Change cron_frequency in config.json to 30 seconds to speed up the test execution
    # Given I open Email
    # # Verify email notification for system configuration changes
    # Then I should see an email for user "to@sys.edu" with subject "System Configuration Changes Notification"
    # # Verify no exceptions are thrown in the system
    # Then I should NOT see an email with subject "REDCap External Module Hook Exception - configuration_monitor"
    # When I open the email for user "to@sys.edu" with subject "System Configuration Changes Notification"
    # # Not verifying the hours as it may vary based on execution time
    # # Then I should see "Please find attached the log detailing the recent changes to the system configuration within the last 2 hours"
    # Then I should see "Please find attached the log detailing the recent changes to the system configuration"
    # And I should see an email table with the following rows:
    #   |  Date / Time      | Changed Property      | Old Value               | New Value               |
    #   # Not verifying the below lines as the data may vary based on execution time
    #   # |  mm/dd/yyyy hh:mm | auto_report_stats     | 1                     	| 0                       |
    #   |  mm/dd/yyyy hh:mm | redcap_base_url       | https://localhost:8443	| https://localhost:8443/ |
    #   |  mm/dd/yyyy hh:mm | project_contact_email |                        	| redcap@test.instance    |