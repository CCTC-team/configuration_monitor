Feature: E.129.1900 - The system shall restrict Configuration Monitor settings to super users only at both the system and project levels.

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

    # E.129.1900 - validate superuser-only access to system settings
    When I click on the button labeled exactly "Configure"
    Then I should see "Configure Module"
    When I check the checkbox labeled "Enable System Changes"
    And I click on the button labeled "Save" in the dialog box
    Then I should see "Configuration Monitor - v1.0.0"
    And I should see a link labeled "System Changes"
    And I logout

    # E.129.1900 - validate non-superuser access to system settings
    Given I login to REDCap with the user "Test_User1"
    Then I should NOT see a link labeled "Control Center"
    And I should NOT see a link labeled "System Changes"

    Given I create a new project named "E.129.1900" by clicking on "New Project" in the menu bar, selecting "Practice / Just for fun" from the dropdown, choosing file "redcap_val/Project_redcap_val_nodata.xml", and clicking the "Create Project" button
    And I logout
    
    # Enable external module in project
    Given I login to REDCap with the user "Test_Admin"
    When I click on the link labeled exactly "Manage"
    Then I should see "External Modules - Project Module Manager"
    And I should NOT see "Configuration Monitor - v1.0.0"
    When I click on the button labeled "Enable a module"
    Then I should see "Available Modules"
    And I click on the button labeled Enable for the external module named "Configuration Monitor - v1.0.0"
    Then I should see "Configuration Monitor - v1.0.0"

    # E.129.1900 - validate project changes access rights - superuser
    When I click on the button labeled exactly "Configure"
    Then I should see "Configure Module"
    When I check the checkbox labeled "Enable Project Changes"
    When I check the checkbox labeled "Enable User Role Changes"
    And I click on the button labeled "Save" in the dialog box
    Then I should see "Configuration Monitor - v1.0.0"
    And I should see a link labeled "Project Changes"
    And I should see a link labeled "User Role Changes"
    And I logout

    # E.129.1900 - validate project changes access rights - non-superuser, project admin
    Given I login to REDCap with the user "Test_User1"
    Then I should see a link labeled "Project Changes"
    And I should see a link labeled "User Role Changes"
    When I click on the link labeled exactly "Manage"
    Then I should see "Configuration Monitor - v1.0.0"
    When I click on the button labeled exactly "Configure"
    Then I should see "Configure Module"
    And I should NOT see "Enable Project Changes"
    And I should NOT see "Enable User Role Changes"
    And I logout

    # Disable external module in Control Center
    Given I login to REDCap with the user "Test_Admin"
    When I click on the link labeled "Control Center"
    And I click on the link labeled exactly "Manage"
    And I click on the button labeled exactly "Disable"
    Then I should see "Disable module?" in the dialog box
    When I click on the button labeled "Disable module" in the dialog box
    Then I should NOT see "Configuration Monitor - v1.0.0"
    And I logout

    # Verify no exceptions are thrown in the system
    Given I open Email
    Then I should NOT see an email with subject "REDCap External Module Hook Exception - configuration_monitor"