Feature: E.129.1800 - The system shall validate that email settings are complete when email notifications are enabled at either the system or project level.

  As a REDCap end user
  I want to see that Configuration Monitor External Module work as expected

  System settings are validated in E.129.2300 - System Changes Access Rights.feature
  
  Scenario: Enable external Module from Control Center
    Given I login to REDCap with the user "Test_Admin"
    When I click on the link labeled "Control Center"
    Given I click on the link labeled "Manage"
    Then I should see "External Modules - Module Manager"
    And I should NOT see "Configuration Monitor - v1.0.0"
    When I click on the button labeled "Enable a module"
    And I wait for 2 seconds
    Then I should see "Available Modules"
    And I click on the button labeled "Enable" in the row labeled "Configuration Monitor"
    And I wait for 1 second
    And I click on the button labeled "Enable"
    Then I should see "Configuration Monitor - v1.0.0"

    Given I create a new project named "E.129.1800" by clicking on "New Project" in the menu bar, selecting "Practice / Just for fun" from the dropdown, choosing file "fixtures/cdisc_files/Project_redcap_val_nodata.xml", and clicking the "Create Project" button
    # Enable external module in project
    When I click on the link labeled "Manage"
    Then I should see "External Modules - Project Module Manager"
    And I should NOT see "Configuration Monitor - v1.0.0"
    When I click on the button labeled "Enable a module"
    Then I should see "Available Modules"
    And I click on the button labeled "Enable" in the row labeled "Configuration Monitor - v1.0.0"
    Then I should see "Configuration Monitor - v1.0.0"
    And I should NOT see a link labeled "Project Changes"

    # Configure module with Project Changes and Email notifications
    When I click on the button labeled "Configure"
    Then I should see "Configure Module"
    When I check the checkbox labeled "Enable Project Changes"
    When I uncheck the checkbox labeled "Enable Project Changes"
    # E.129.1300 - at least one tracking option must be enabled
    When I click on the button labeled "Save"
    Then I should see "Enable Project Changes"
    When I check the checkbox labeled "Enable Project Changes"
    And I click on the button labeled "Save"
    Then I should see "Configuration Monitor - v1.0.0"

    When I click on the button labeled "Configure"
    Then I should see "Configure Module"
    When I check the checkbox labeled "Enable User Role Changes"
    # E.129.1600
    And I check the checkbox labeled "Enable Email"
    And I click on the button labeled "Save"
    Then I should see "Provide the email address used to send notifications"
    # E.129.1700, E.129.1800
    When I enter "from@sys.edu" into the input field labeled "Provide the email address used to send notifications:"
    And I click on the button labeled "Save"
    Then I should see "1. Provide the email address to receive configuration change notifications"
    When I enter "to@sys.edu" into the input field labeled "1. Provide the email address to receive configuration change notifications"
    And I click on the button labeled "Save"
    Then I should see "Configuration Monitor - v1.0.0"

    When I click on the button labeled "Configure"
    Then I should see "Configure Module"
    # E.129.1400
    When I enter "4.8" into the input field labeled "Specify the maximum number of days to look back when displaying configuration changes on the page (default 7 days)"
    And I click on the button labeled "Save"
    Then I should see "Specify the maximum number of days to look back when displaying configuration changes on the page (default 7 days)"
    When I clear field and enter "4" into the input field labeled "Specify the maximum number of days to look back when displaying configuration changes on the page (default 7 days)"
    And I click on the button labeled "Save"
    Then I should see "Configuration Monitor - v1.0.0"

    When I click on the button labeled "Configure"
    Then I should see "Configure Module"
    # E.129.1500
    When I enter "2.7" into the input field labeled "Specify the maximum number of hours to look back when sending email notifications (default 3 hours)"
    And I click on the button labeled "Save"
    Then I should see "Specify the maximum number of hours to look back when sending email notifications (default 3 hours)"
    When I clear field and enter "2" into the input field labeled "Specify the maximum number of hours to look back when sending email notifications (default 3 hours)"
    And I click on the button labeled "Save"
    Then I should see "Configuration Monitor - v1.0.0"

    # Disable external module in project
    Given I click on the link labeled "Manage"
    And I click on the button labeled "Disable"
    Then I should see "Disable module?"
    When I click on the button labeled "Disable module"
    Then I should NOT see "Configuration Monitor - v1.0.0"

    Given I click on the link labeled "Logging"
    Then I should see a table header and row containing the following values in the logging table:
      | Time / Date      | Username   | Action                                                                              | List of Data Changes OR Fields Exported                                                                                    |
      | mm/dd/yyyy hh:mm | test_admin | Disable external module "configuration_monitor_v1.0.0" for project                  |                                                                                                                            |
      | mm/dd/yyyy hh:mm | test_admin | Modify configuration for external module "configuration_monitor_v1.0.0" for project | max-hours-email                                                                                                            |
      | mm/dd/yyyy hh:mm | test_admin | Modify configuration for external module "configuration_monitor_v1.0.0" for project | max-days-page                                                                                                              |
      | mm/dd/yyyy hh:mm | test_admin | Modify configuration for external module "configuration_monitor_v1.0.0" for project | user-role-changes-enable, email-enable, from-emailid, to-emailids                                                          |
      | mm/dd/yyyy hh:mm | test_admin | Modify configuration for external module "configuration_monitor_v1.0.0" for project | reserved-hide-from-non-admins-in-project-list, user-role-changes-enable, project-changes-enable, email-enable, to-emailids |
      | mm/dd/yyyy hh:mm | test_admin | Enable external module "configuration_monitor_v1.0.0" for project                   |                                                                                                                            |

    # Disable external module in Control Center
    And I click on the link labeled "Control Center"
    When I click on the link labeled "Manage"
    And I click on the button labeled "Disable"
    Then I should see "Disable module?"
    When I click on the button labeled "Disable module"
    Then I should NOT see "Configuration Monitor - v1.0.0"

    Given I click on the link labeled "User Activity Log"
    Then I should see a table header and row containing the following values in a table:
      | Time             | User       | Event                                                                               |
      | mm/dd/yyyy hh:mm | test_admin | Disable external module "configuration_monitor_v1.0.0" for system                   |
      | mm/dd/yyyy hh:mm | test_admin | Disable external module "configuration_monitor_v1.0.0" for project                  |
      | mm/dd/yyyy hh:mm | test_admin | Modify configuration for external module "configuration_monitor_v1.0.0" for project |
      | mm/dd/yyyy hh:mm | test_admin | Enable external module "configuration_monitor_v1.0.0" for project                   |
      | mm/dd/yyyy hh:mm | test_admin | Enable external module "configuration_monitor_v1.0.0" for system                    |

    And I logout

    # Verify no exceptions are thrown in the system
    Given I open Email
    Then I should NOT see an email with subject "REDCap External Module Hook Exception - configuration_monitor"