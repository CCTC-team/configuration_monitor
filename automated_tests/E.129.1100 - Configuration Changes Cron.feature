Feature: E.129.1100 - The system shall register a cron job (configuration_monitor_cron) when the module is enabled at the system level.

  As a REDCap end user
  I want to see that Configuration Monitor External Module work as expected

  Scenario: Enable external Module from Control Center
    Given I login to REDCap with the user "Test_Admin"
    # Enable external module from Control Center
    When I click on the link labeled "Control Center"
    Given I click on the link labeled "Manage"
    Then I should see "External Modules - Module Manager"
    When I click on the button labeled "Enable a module"
    And I wait for 2 seconds
    Then I should see "Available Modules"
    And I click on the button labeled "Enable" in the row labeled "Configuration Monitor"
    And I wait for 1 second
    And I click on the button labeled "Enable"
    Then I should see "Configuration Monitor - v1.0.0"

    # Change cron_frequency in config.json to 30 seconds for email notification test
    And I wait for 35 seconds
    # E.129.1100
    When I click on the link labeled "Cron Jobs"
    Then I should see a table header and row containing the following values in a table:
      | Job Name                                           | Description                                                |
      | configuration_monitor_cron (configuration_monitor) |  Send email notifications for recent configuration changes |

    # Disable external module in Control Center
    Given I click on the link labeled "Manage"
    When I click on the button labeled "Disable"
    Then I should see "Disable module?"
    When I click on the button labeled "Disable module"
    Then I should NOT see "Configuration Monitor - v1.0.0"
    And I logout

    # Verify no exceptions are thrown in the system
    Given I open Email
    Then I should NOT see an email with subject "REDCap External Module Hook Exception - configuration_monitor"