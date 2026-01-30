Feature: E.129.2800 - The system shall send automated email summaries containing System Changes when changes occur and email notifications are enabled.

  As a REDCap end user
  I want to see that Configuration Monitor External Module work as expected

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

    When I click on the button labeled "Configure"
    Then I should see "Configure Module"
    When I check the checkbox labeled "Enable System Changes"
    And I check the checkbox labeled "Enable Email"
    And I enter "from@sys.edu" into the input field labeled "Provide the email address used to send notifications:"
    And I enter "to@sys.edu" into the input field labeled "1. Provide the email address to receive configuration change notifications"
    When I click on the button labeled "Save"
    Then I should see "Configuration Monitor - v1.0.0"

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

    # Disable external module in Control Center
    Given I click on the link labeled "Control Center"
    When I click on the link labeled "Manage"
    And I click on the button labeled "Disable"
    Then I should see "Disable module?"
    When I click on the button labeled "Disable module"
    Then I should NOT see "Configuration Monitor - v1.0.0"

    # Re-enable EM to verify data persistence
    When I click on the button labeled "Enable a module"
    And I wait for 2 seconds
    Then I should see "Available Modules"
    And I click on the button labeled "Enable" in the row labeled "Configuration Monitor"
    And I wait for 1 second
    And I click on the button labeled "Enable"
    Then I should see "Configuration Monitor - v1.0.0"
    # E.129.1200 - verify system changes are retained
    When I click on the link labeled "System Changes" 
    Then I should see "This log shows changes made to system settings"
    And I should see a table header and rows containing the following values in the a table:
      |  Date / Time      | Changed Property      | Old Value               | New Value               |
      |  mm/dd/yyyy hh:mm | redcap_base_url       | https://localhost:8443	| https://localhost:8443/ |
      |  mm/dd/yyyy hh:mm | project_contact_email |                        	| redcap@test.instance    |

    # Wait for email notification to be triggered
    # For testing purposes, set cron_frequency to 30 seconds in config.json
    And I wait for 15 seconds
    # Disable external module in Control Center
    Given I click on the link labeled "Manage"
    And I click on the button labeled "Disable"
    Then I should see "Disable module?"
    When I click on the button labeled "Disable module"
    Then I should NOT see "Configuration Monitor - v1.0.0"
    And I logout

    # Change cron_frequency in config.json to 30 seconds for email notification test
    Given I open Email
    # Verify email notification for system configuration changes
    Then I should see an email for user "to@sys.edu" with subject "System Configuration Changes Notification"
    # Verify no exceptions are thrown in the system
    Then I should NOT see an email with subject "REDCap External Module Hook Exception - configuration_monitor"
    When I open the email for user "to@sys.edu" with subject "System Configuration Changes Notification"
    Then I should see "Please find attached the log detailing the recent changes to the system configuration within the last 3 hours" in the email body
    And I should see a system changes table in the email with the following rows:
      |  Date / Time      | Changed Property      | Old Value               | New Value               |
      |  mm/dd/yyyy hh:mm | redcap_base_url       | https://localhost:8443	| https://localhost:8443/ |
      |  mm/dd/yyyy hh:mm | project_contact_email |                        	| redcap@test.instance    |
