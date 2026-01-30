Feature: E.129.2300 - The system shall restrict access to the System Changes page to super users only.

  As a REDCap end user
  I want to see that Configuration Monitor External Module work as expected

  Scenario: Enable external Module from Control Center
    Given I login to REDCap with the user "Test_Admin"
    # Enable external module from Control Center
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

    # Configure module with System Changes and Email notifications
    When I click on the button labeled "Configure"
    Then I should see "Configure Module"
    When I check the checkbox labeled "Enable System Changes"
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
    When I enter "4.8" into the input field labeled "Specify the maximum number of days to look back when displaying system configuration changes on the page (default 7 days)"
    And I click on the button labeled "Save"
    Then I should see "Specify the maximum number of days to look back when displaying system configuration changes on the page (default 7 days)"
    When I clear field and enter "4" into the input field labeled "Specify the maximum number of days to look back when displaying system configuration changes on the page (default 7 days)"
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
    And I logout

    # E.129.2300 - validate superuser-only access to system settings
    Given I login to REDCap with the user "Test_User1"
    Then I should NOT see a link labeled "Control Center"
    And I logout

    Given I login to REDCap with the user "Test_Admin"
    # Disable external module in Control Center
    And I click on the link labeled "Control Center"
    When I click on the link labeled "Manage"
    And I click on the button labeled "Disable"
    Then I should see "Disable module?"
    When I click on the button labeled "Disable module"
    Then I should NOT see "Configuration Monitor - v1.0.0"
    And I logout

    # Verify no exceptions are thrown in the system
    Given I open Email
    Then I should NOT see an email with subject "REDCap External Module Hook Exception - configuration_monitor"