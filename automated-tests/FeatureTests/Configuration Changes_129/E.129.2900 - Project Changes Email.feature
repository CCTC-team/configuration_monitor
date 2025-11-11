Feature: E.129.1000 - The system shall allow enabling or disabling User Role Changes tracking at the project level.

  As a REDCap end user
  I want to see that Data Entry Log External Module work as expected

  Scenario: Enable external Module from Control Center
    Given I login to REDCap with the user "Test_Admin"
    When I click on the link labeled "Control Center"
    And I click on the link labeled exactly "Manage"
    Then I should see "External Modules - Module Manager"
    And I should NOT see "Configuration Monitor - v1.0.0"
    When I click on the button labeled "Enable a module"
    And I click on the button labeled Enable for the external module named "Configuration Monitor"
    And I click on the button labeled "Enable" in the dialog box
    Then I should see "Configuration Monitor - v1.0.0"
 
  Scenario: Enable external module in project
    Given I create a new project named "E.129.1000" by clicking on "New Project" in the menu bar, selecting "Practice / Just for fun" from the dropdown, choosing file "redcap_val/Project_redcap_val_nodata.xml", and clicking the "Create Project" button
    When I click on the link labeled exactly "Manage"
    Then I should see "External Modules - Project Module Manager"
    And I should NOT see "Configuration Monitor - v1.0.0"
    When I click on the button labeled "Enable a module"
    Then I should see "Available Modules"
    And I click on the button labeled Enable for the external module named "Configuration Monitor - v1.0.0"
    Then I should see "Configuration Monitor - v1.0.0"
    And I should NOT see a link labeled "User Role Changes"

    When I click on the button labeled exactly "Configure"
    Then I should see "Configure Module"
    When I check the checkbox labeled "Enable Project Changes"
    When I check the checkbox labeled "Enable User Role Changes"
    And I check the checkbox labeled "Enable Email"
    And I enter "from@sys.edu" into the input field labeled "Provide the email address used to send notifications:" in the dialog box
    And I enter "to@sys.edu" into the input field labeled "1. Provide the email address to receive configuration change notifications" in the dialog box
    When I click on the button labeled "Save" in the dialog box
    Then I should see "Configuration Monitor - v1.0.0"

    ##ACTION: Update User role
    Given I click on the link labeled "User Rights"
    And I click on the link labeled "DataManager"
    Then I should see "Editing existing user role" in the dialog box
    And I check the radio labeled "Read Only" in the dialog box
    And I click on the button labeled "Save Changes"
    Then I should see "successfully edited"

    When I click on the link labeled "User Role Changes"
    And I should see "This log shows changes made to user role privileges"
    And I should see a table header and rows containing the following values in the a table:
      | Role ID |  Action | Date / Time      | Changed Privilege | Old Value | New Value |
      | 2       |  UPDATE | mm/dd/yyyy hh:mm | User Rights	     | 0	       | 2         |

    When I click on the link labeled "Project Setup"
    And I click on the button labeled "Disable" in the "Auto-numbering for records" row in the "Enable optional modules and customizations" section
    Then I should see a button labeled "Enable" in the "Auto-numbering for records" row in the "Enable optional modules and customizations" section

    When I click on the link labeled "Project Changes"
    Then I should see "This log shows changes made to project settings"
    And I should see a table header and rows containing the following values in the a table:
      |  Date / Time      | Changed Property | Old Value | New Value |
      |  mm/dd/yyyy hh:mm | Auto Inc Set	   | 1	       | 0         |

    # Wait for email notification to be triggered
    And I wait for 15 seconds
    # Disable external module in Control Center
    Given I click on the link labeled "Control Center"
    And I click on the link labeled exactly "Manage"
    And I click on the button labeled exactly "Disable"
    Then I should see "Disable module?" in the dialog box
    When I click on the button labeled "Disable module" in the dialog box
    Then I should NOT see "Configuration Monitor - v1.0.0"
    And I logout

    # Change cron_frequency in config.json to 30 seconds for email notification test
    Given I open Email
    # Verify email notification for system configuration changes
    Then I should see an email for user "to@sys.edu" with subject "Project Configuration Changes Notification"
    # Verify no exceptions are thrown in the system
    Then I should NOT see an email with subject "REDCap External Module Hook Exception - configuration_monitor"
    When I open the email for user "to@sys.edu" with subject "Project Configuration Changes Notification"
    Then I should see "Please find attached the log detailing the recent changes to the project configuration within the last 3 hours"
    And I should see "Project Configuration Changes for Project ID: 13 - E.129.1000"
    And I should see "Changes in User Role Privileges"
    And I should see a user role changes table in the email with the following rows:
      | Role ID |  Action | Date / Time      | Changed Privilege | Old Value | New Value |
      | 2       |  UPDATE | mm/dd/yyyy hh:mm | User Rights	     | 0	       | 2         |

    And I should see "Changes in Project settings"
    And I should see a project changes table in the email with the following rows:
      |  Date / Time      | Changed Property | Old Value | New Value |
      |  mm/dd/yyyy hh:mm | Auto Inc Set	   | 1	       | 0         |