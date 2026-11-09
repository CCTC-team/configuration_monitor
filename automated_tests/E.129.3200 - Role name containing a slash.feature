Feature: E.129.3200 - The system shall show the full role name and the correct changed privilege when a role name contains a slash, in the Configuration Monitor external module.

  As a REDCap end user
  I want to see that Configuration Monitor External Module work as expected

  Scenario: Enable external Module from Control Center
    Given I login to REDCap with the user "Test_Admin"
    When I click on the link labeled "Control Center"
    And I click on the link labeled "Manage"
    Then I should see "External Modules - Module Manager"
    And I click on the button labeled "Disable"
    Then I should see "Disable module?"
    When I click on the button labeled "Disable module"
    Then I should NOT see "Configuration Monitor - v1.1.1"
    When I click on the button labeled "Enable a module"
    And I wait for 2 seconds
    Then I should see "Available Modules"
    And I click on the button labeled "Enable" in the row labeled "Configuration Monitor"
    And I wait for 1 second
    And I click on the button labeled "Enable"
    Then I should see "Configuration Monitor - v1.1.1"
 
  Scenario: Enable external module in project
    Given I create a new project named "E.129.3200" by clicking on "New Project" in the menu bar, selecting "Practice / Just for fun" from the dropdown, choosing file "fixtures/cdisc_files/Project_redcap_val_nodata.xml", and clicking the "Create Project" button
    When I click on the second link labeled "Manage"
    Then I should see "External Modules - Project Module Manager"
    And I should NOT see "Configuration Monitor - v1.1.1"
    When I click on the button labeled "Enable a module"
    And I wait for 2 seconds
    Then I should see "Available Modules"
    And I click on the button labeled "Enable" in the row labeled "Configuration Monitor"
    And I wait for 1 second
    And I click on the button labeled "Enable"
    Then I should see "Configuration Monitor - v1.1.1"
    And I should NOT see a link labeled "User Role Changes"

    When I click on the button labeled "Configure"
    Then I should see "Configure Module"
    When I check the checkbox labeled "Enable User Role Changes"
    And I click on the button labeled "Save"
    Then I should see "Configuration Monitor - v1.1.1"
    # E.129.2700 - verify User Role Changes link appears
    And I should see a link labeled "User Role Changes"

    When I click on the link labeled "User Role Changes"
    Then I should see "Changes in User Role Privileges"
    And I should see "This log shows changes made to user role privileges"
    And I should see "No changes to user role privileges have been made in this project"

    Given I click on the link labeled "User Rights"
    And I enter "Nurse/Coordinator" into the field with the placeholder text of "Enter new role name"
    And I click on the button labeled "Create role"
    Then I should see "Creating new role"
    When I click on the button labeled "Create role"
    And I wait for 3 seconds
    And I click on the link labeled "Nurse/Coordinator"
    Then I should see "Editing existing user role"
    And I check the radio labeled "Read Only"
    And I click on the button labeled "Save Changes"
    Then I should see "successfully edited"

    When I click on the link labeled "User Role Changes"
    Then I should see "This log shows changes made to user role privileges"
    And I should see "Nurse/Coordinator (4)"
    And I should see "User Rights"
    And I should NOT see "Data Access Groups"
    And the Configuration Monitor role filter should contain "Nurse/Coordinator (4)"

  Scenario: The filter controls keep their values and page through the results
    Given I add 12 rows to the Configuration Monitor user role change log
    Given I click on the link labeled "User Role Changes"
    When I click on the button labeled "Past month"
    And I wait for 2 seconds
    Then the Configuration Monitor filter "defaulttimefilter" should have the value "onemonthago"
    When I set the Configuration Monitor filter "retdirection" to "asc"
    Then the Configuration Monitor filter "retdirection" should have the value "asc"
    When I set the Configuration Monitor filter "pagesize" to "10"
    Then the Configuration Monitor filter "pagesize" should have the value "10"
    When I click the Configuration Monitor paging button "btnnextpage"
    Then the Configuration Monitor filter "pagenum" should have the value "1"
    When I click the Configuration Monitor paging button "btnprevpage"
    Then the Configuration Monitor filter "pagenum" should have the value "0"
    When I reset the Configuration Monitor filters
    Then the Configuration Monitor filter "retdirection" should have the value "desc"
    And the Configuration Monitor filter "pagenum" should have the value "0"

  Scenario: Disable external module
    Given I click on the link labeled "Control Center"
    And I click on the link labeled "Manage"
    Then I should see "External Modules - Module Manager"
    And I click on the button labeled "Disable"
    Then I should see "Disable module?"
    When I click on the button labeled "Disable module"
    Then I should NOT see "Configuration Monitor - v1.1.1"
    And I logout
