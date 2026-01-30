Feature: E.129.2400 - The system shall restrict access to Project Changes and User Role Changes pages to users with user_rights privilege or super user status.

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

    Given I create a new project named "E.129.2400" by clicking on "New Project" in the menu bar, selecting "Practice / Just for fun" from the dropdown, choosing file "fixtures/cdisc_files/Project_redcap_val_nodata.xml", and clicking the "Create Project" button
    # Enable external module in project
    When I click on the link labeled "Manage"
    Then I should see "External Modules - Project Module Manager"
    And I should NOT see "Configuration Monitor - v1.0.0"
    When I click on the button labeled "Enable a module"
    And I wait for 2 seconds
    Then I should see "Available Modules"
    And I click on the button labeled "Enable" in the row labeled "Configuration Monitor"
    And I wait for 1 second
    And I click on the button labeled "Enable"
    Then I should see "Configuration Monitor - v1.0.0"
    And I should NOT see a link labeled "Project Changes"
    And I should NOT see a link labeled "User Role Changes"

    # Configure module with Project Changes and Email notifications
    When I click on the button labeled "Configure"
    Then I should see "Configure Module"
    When I check the checkbox labeled "Enable Project Changes"
    When I check the checkbox labeled "Enable User Role Changes"
    And I click on the button labeled "Save"
    Then I should see "Configuration Monitor - v1.0.0"
    # E.129.2400 - validate project changes access rights - superuser
    And I should see a link labeled "Project Changes"
    And I should see a link labeled "User Role Changes"

    # Add User Test_User1 with 'User Rights - No Access'
    Given I click on the link labeled "User Rights"
    And I enter "Test_User1" into the input field labeled "Add with custom rights"
    And I click on the button labeled "Add with custom rights"
    # User Rights - No Access
    And I check the radio labeled "No Access"
    And I click on the button labeled "Add user"
    Then I should see "successfully added"

    # Add User Test_User2 with 'User Rights - Read Only'
    When I enter "Test_User2" into the input field labeled "Add with custom rights"
    And I click on the button labeled "Add with custom rights"
    # User Rights - Read Only
    And I check the radio labeled "Read Only"
    And I click on the button labeled "Add user"
    Then I should see "successfully added"

    # Add User Test_User3 with 'User Rights - Full Access'
    When I enter "Test_User3" into the input field labeled "Add with custom rights"
    And I click on the button labeled "Add with custom rights"
    # User Rights - Full Access
    And I check the radio labeled "Full Access"
    And I click on the button labeled "Add user"
    Then I should see "successfully added"
    And I logout

    # E.129.2400 - validate project changes access rights
    Given I login to REDCap with the user "Test_User1"
    Then I should NOT see a link labeled "Project Changes"
    And I should NOT see a link labeled "User Role Changes"
    And I logout

    # E.129.2400 - validate project changes access rights
    Given I login to REDCap with the user "Test_User2"
    Then I should see a link labeled "Project Changes"
    And I should see a link labeled "User Role Changes"
    And I logout

    # E.129.2400 - validate project changes access rights
    Given I login to REDCap with the user "Test_User3"
    Then I should see a link labeled "Project Changes"
    Then I should see a link labeled "User Role Changes"
    And I logout

    # Disable external module in Control Center
    Given I login to REDCap with the user "Test_Admin"
    When I click on the link labeled "Control Center"
    And I click on the link labeled "Manage"
    And I click on the button labeled "Disable"
    Then I should see "Disable module?"
    When I click on the button labeled "Disable module"
    Then I should NOT see "Configuration Monitor - v1.0.0"
    And I logout

    # Verify no exceptions are thrown in the system
    Given I open Email
    Then I should NOT see an email with subject "REDCap External Module Hook Exception - configuration_monitor"