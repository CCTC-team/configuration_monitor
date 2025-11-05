Feature: E.129.700 - The system shall support the ability to set up and view logs using Configuration Monitor module.
  
  As a REDCap end user
  I want to see that Configuration Monitor External Module work as expected

  Scenario: Enable external Module from Control Center
    Given I login to REDCap with the user "Test_Admin"
    When I click on the link labeled "Control Center"
    Given I click on the link labeled exactly "Manage"
    Then I should see "External Modules - Module Manager"
    And I should NOT see "Configuration Monitor - v1.0.0"
    When I click on the button labeled "Enable a module"
    And I click on the button labeled Enable for the external module named "Configuration Monitor"
    And I click on the button labeled "Enable" in the dialog box
    Then I should see "Configuration Monitor - v1.0.0"
    Then I should NOT see a link labeled "System Changes"

    When I click on the button labeled exactly "Configure"
    Then I should see "Configure Module"
    When I check the checkbox labeled "Enable System Changes"
    And I click on the button labeled "Save" in the dialog box
    Then I should see "Configuration Monitor - v1.0.0"
    And I should see a link labeled "System Changes"

    When I click on the link labeled "System Changes"
    Then I should see "Changes in System settings"
    And I should see "This log shows changes made to system settings"
    And I should see "No changes have been made to the system settings."

    # EMAIL ADDRESS SET FOR REDCAP ADMIN - without it, emails are not send out from system
    When I click on the link labeled "General Configuration"
    Then I should see "General Configuration"
    When I enter "redcap@test.instance" into the input field labeled "Email Address of REDCap Administrator"
    And I click on the button labeled "Save Changes"
    Then I should see "Your system configuration values have now been changed"

    When I click on the link labeled "System Changes"
    Then I should see "This log shows changes made to system settings"
    And I should see a table header and rows containing the following values in the a table:
      |  Date / Time      | Changed Property      | Old Value               | New Value               |
      |  mm/dd/yyyy hh:mm | redcap_base_url       | https://localhost:8443	| https://localhost:8443/ |
      |  mm/dd/yyyy hh:mm | project_contact_email |                        	| redcap@test.instance    |

    Given I click on the link labeled exactly "Manage"
    Then I should see "External Modules - Module Manager"
    When I click on the button labeled exactly "Configure"
    Then I should see "Configure Module"
    When I check the checkbox labeled "Enable Email"
    And I click on the button labeled "Save" in the dialog box
    Then I should see "Provide the email address used to send notifications" in the dialog box
    When I enter "from@sys.edu" into the input field labeled "Provide the email address used to send notifications:" in the dialog box
    And I click on the button labeled "Save" in the dialog box
    Then I should see "1. Provide the email address to receive configuration change notifications" in the dialog box
    When I enter "to@sys.edu" into the input field labeled "1. Provide the email address to receive configuration change notifications" in the dialog box
    And I click on the button labeled "Save" in the dialog box
    Then I should see "Configuration Monitor - v1.0.0"

    # E.129.2200 - validate numeric values for page display
    When I click on the button labeled exactly "Configure"
    Then I should see "Configure Module"
    When I enter "4.8" into the input field labeled "Specify the maximum number of days to look back when displaying system configuration changes on the page (default 7 days)" in the dialog box
    And I click on the button labeled "Save" in the dialog box
    Then I should see "Specify the maximum number of days to look back when displaying system configuration changes on the page (default 7 days)" in the dialog box
    When I clear field and enter "4" into the input field labeled "Specify the maximum number of days to look back when displaying system configuration changes on the page (default 7 days)" in the dialog box
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

    Given I click on the link labeled "General Configuration"
    Then I should see "General Configuration"
    And I should see the dropdown field labeled "Automatically send basic statistics to REDCap Consortium?" with the option "Yes, send stats automatically" selected
    When I select "No, send stats manually" on the dropdown field labeled "Automatically send basic statistics to REDCap Consortium?"
    And I click on the button labeled "Save Changes"
    Then I should see "Your system configuration values have now been changed"

    When I click on the link labeled "System Changes" 
    Then I should see "This log shows changes made to system settings"
    And I should see a table header and rows containing the following values in the a table:
      |  Date / Time      | Changed Property      | Old Value               | New Value               |
      |  mm/dd/yyyy hh:mm | auto_report_stats     | 1                     	| 0                       |
      |  mm/dd/yyyy hh:mm | redcap_base_url       | https://localhost:8443	| https://localhost:8443/ |
      |  mm/dd/yyyy hh:mm | project_contact_email |                        	| redcap@test.instance    |

    And I should see 3 rows in the system changes table

    When I select "project_contact_email" on the dropdown field labeled "Property"
    Then I should see a table header and rows containing the following values in the a table:
      |  Date / Time      | Changed Property      | Old Value               | New Value               |
      |  mm/dd/yyyy hh:mm | project_contact_email |                        	| redcap@test.instance    |

    And I should see 1 row in the system changes table
    And I should NOT see "auto_report_stats"
    And I should NOT see "redcap_base_url"

    When I click on the button labeled "Export current page"
    Then the downloaded CSV with filename "SystemChanges_yyyy-mm-dd_hhmm.csv" has the header and rows below
      | changed property      | old value               | new value               |
      | project_contact_email |                        	| redcap@test.instance    |

    When I click on the button labeled "Export everything ignoring filters"
    Then the downloaded CSV with filename "SystemChanges_yyyy-mm-dd_hhmm.csv" has the header and rows below
      | changed property      | old value               | new value               |
      | auto_report_stats     | 1                     	| 0                       |
      | redcap_base_url       | https://localhost:8443	| https://localhost:8443/ |
      | project_contact_email |                        	| redcap@test.instance    |

    And I wait for 10 seconds
    # Disable external module in Control Center
    Given I click on the link labeled "Control Center"
    When I click on the link labeled exactly "Manage"
    And I click on the button labeled exactly "Disable"
    Then I should see "Disable module?" in the dialog box
    When I click on the button labeled "Disable module" in the dialog box
    Then I should NOT see "Configuration Monitor - v0.0.0"

    Given I click on the link labeled "User Activity Log"
    Then I should see a table header and row containing the following values in a table:
      | Time             | User       | Event                                                                              |
      | mm/dd/yyyy hh:mm | test_admin | Disable external module "configuration_monitor_v1.0.0" for system                  |
      | mm/dd/yyyy hh:mm | test_admin | Modify configuration for external module "configuration_monitor_v1.0.0" for system |
      | mm/dd/yyyy hh:mm | test_admin | Enable external module "configuration_monitor_v1.0.0" for system                   |

    And I logout

    # Change cron_frequency in config.json to 30 seconds to speed up the test execution
    Given I open Email
    # Verify email notification for system configuration changes
    Then I should see an email for user "to@sys.edu" with subject "System Configuration Changes Notification"
    # Verify no exceptions are thrown in the system
    Then I should NOT see an email with subject "REDCap External Module Hook Exception - configuration_monitor"
    When I open the email for user "to@sys.edu" with subject "System Configuration Changes Notification"
    # Not verifying the hours as it may vary based on execution time
    # Then I should see "Please find attached the log detailing the recent changes to the system configuration within the last 2 hours"
    Then I should see "Please find attached the log detailing the recent changes to the system configuration"
    And I should see an email table with the following rows:
      |  Date / Time      | Changed Property      | Old Value               | New Value               |
      # Not verifying the below lines as the data may vary based on execution time
      # |  mm/dd/yyyy hh:mm | auto_report_stats     | 1                     	| 0                       |
      |  mm/dd/yyyy hh:mm | redcap_base_url       | https://localhost:8443	| https://localhost:8443/ |
      |  mm/dd/yyyy hh:mm | project_contact_email |                        	| redcap@test.instance    |
