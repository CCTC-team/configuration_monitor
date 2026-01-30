Feature: E.129.1000 - The system shall allow enabling or disabling User Role Changes tracking at the project level.

  As a REDCap end user
  I want to see that Configuration Monitor External Module work as expected

  Scenario: Enable external Module from Control Center
    Given I login to REDCap with the user "Test_Admin"
    When I click on the link labeled "Control Center"
    And I click on the link labeled "Manage"
    Then I should see "External Modules - Module Manager"
    And I should NOT see "Configuration Monitor - v1.0.0"
    When I click on the button labeled "Enable a module"
    And I wait for 2 seconds
    Then I should see "Available Modules"
    And I click on the button labeled "Enable" in the row labeled "Configuration Monitor"
    And I wait for 1 second
    And I click on the button labeled "Enable"
    Then I should see "Configuration Monitor - v1.0.0"
 
  Scenario: Enable external module in project
    Given I create a new project named "E.129.1000" by clicking on "New Project" in the menu bar, selecting "Practice / Just for fun" from the dropdown, choosing file "fixtures/cdisc_files/Project_redcap_val_nodata.xml", and clicking the "Create Project" button
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
    And I should NOT see a link labeled "User Role Changes"

    When I click on the button labeled "Configure"
    Then I should see "Configure Module"
    When I check the checkbox labeled "Enable User Role Changes"
    And I click on the button labeled "Save"
    Then I should see "Configuration Monitor - v1.0.0"
    # E.129.2700 - verify User Role Changes link appears
    And I should see a link labeled "User Role Changes"

    When I click on the link labeled "User Role Changes"
    Then I should see "Changes in User Role Privileges"
    And I should see "This log shows changes made to user role privileges"
    And I should see "No changes to user role privileges have been made in this project"

    ##ACTION: Insert New User role
    Given I click on the link labeled "User Rights"
    And I enter "TestRole" into the field with the placeholder text of "Enter new role name"
    And I click on the button labeled "Create role"
    Then I should see a dialog containing the following text: "Creating new role"
    When I click on the button labeled "Create role"
    Then I should see a table header and rows containing the following values in a table:
      | Role name   |
      | DataEntry   |
      | DataManager |
      | Monitor     |
      | TestRole    |

    When I click on the link labeled "User Role Changes"
    And I should see "This log shows changes made to user role privileges"
    And I should see a table header and rows containing the following values in the a table:
      | Role ID |  Action | Date / Time      | Changed Privilege | Old Value | New Value                                                                                                                                          |
      | 4       |  INSERT | mm/dd/yyyy hh:mm | All Privileges    | N/A	     | TestRole/0/0/0/[text_validation,1][data_types,1]/0/0/0/0/1/0/0/0/1/1/0/0/1/[text_validation,1][data_types,1]/0/0/0/0/0/1/0/0/0/1/0/0/1/0/0/0/0/0/1 |

    And I should see 1 row in the user role changes table

    ##ACTION: Update User role
    Given I click on the link labeled "User Rights"
    And I click on the link labeled "DataManager"
    Then I should see "Editing existing user role"
    And I check the radio labeled "Read Only"
    And I click on the radio in the column labeled "View & Edit" and the row labeled "Text Validation"
    And I click on the button labeled "Save Changes"
    Then I should see "successfully edited"

    ##ACTION: Delete User role
    Given I click on the link labeled "Monitor"
    And I click on the button labeled "Delete role"
    When I see a dialog containing the following text: "Delete role?"
    And I click on the button labeled "Delete role"
    Then I should NOT see "Monitor"

    When I click on the link labeled "User Role Changes"
    And I should see "This log shows changes made to user role privileges"
    And I should see a table header and rows with rowspan containing the following values in a table:
      | Role ID |  Action | Date / Time      | Changed Privilege       | Old Value           | New Value                                                                                                                                          |
      | 3       |  DELETE | mm/dd/yyyy hh:mm | All Privileges          | Monitor/0/0/0/[text_validation,0][data_types,0]/0/0/0/0/0/0/0/0/0/1/0/0/0/[text_validation,2][data_types,2]/0/0/0/0/0/0/0/0/0/1/0/1/1/0/0/0/0/0/1 | N/A                  |
      | 2       |  UPDATE | mm/dd/yyyy hh:mm | User Rights	           | 0	                 | 2                                                                                                                                                  |
      | 2       |  UPDATE | mm/dd/yyyy hh:mm | Data Entry		           | [text_validation,2] | [text_validation,1]                                                                                                                                |
      | 4       |  INSERT | mm/dd/yyyy hh:mm | All Privileges          | N/A	               | TestRole/0/0/0/[text_validation,1][data_types,1]/0/0/0/0/1/0/0/0/1/1/0/0/1/[text_validation,1][data_types,1]/0/0/0/0/0/1/0/0/0/1/0/0/1/0/0/0/0/0/1 |

    And I should see 4 rows in the user role changes table
    And I wait for 2 seconds

    # E.129.2100 - validate filtering user role changes
    When I select "2" on the dropdown field labeled "User Role"
    And I should see a table header and rows with rowspan containing the following values in a table:
      | Role ID |  Action | Date / Time      | Changed Privilege       | Old Value           | New Value           |
      | 2       |  UPDATE | mm/dd/yyyy hh:mm | User Rights	           | 0	                 | 2                   |
      | 2       |  UPDATE | mm/dd/yyyy hh:mm | Data Entry		           | [text_validation,2] | [text_validation,1] |

    And I should see 2 rows in the user role changes table
    And I should NOT see "All Privileges"
    And I should NOT see "TestRole"
    And I should NOT see "Monitor"
    # And I should NOT see "3"
    # And I should NOT see "4"

    # E.129.3000 - validate exporting user role changes to CSV
    When I click on the button labeled "Export current page"
    Then the downloaded CSV with filename "E1291000_UserRoleChanges_yyyy-mm-dd_hhmm.csv" has the header and rows below
      | role id |  action | changed privilege       | old value           | new value           |
      | 2       |  UPDATE | User Rights	            | 0	                  | 2                   |
      | 2       |  UPDATE | Data Entry		          | [text_validation,2] | [text_validation,1] |

    When I click on the button labeled "Export everything ignoring filters"
    Then the downloaded CSV with filename "E1291000_UserRoleChanges_yyyy-mm-dd_hhmm.csv" has the header and rows below
      | role id |  action | changed privilege       | old value           | new value           |
      | 3       |  DELETE | All Privileges          | Monitor/0/0/0/[text_validation,0][data_types,0]/0/0/0/0/0/0/0/0/0/1/0/0/0/[text_validation,2][data_types,2]/0/0/0/0/0/0/0/0/0/1/0/1/1/0/0/0/0/0/1 | N/A                  |
      | 2       |  UPDATE | User Rights	            | 0	                  | 2                   |
      | 2       |  UPDATE | Data Entry		          | [text_validation,2] | [text_validation,1] |
      | 4       |  INSERT | All Privileges          | N/A	                | TestRole/0/0/0/[text_validation,1][data_types,1]/0/0/0/0/1/0/0/0/1/1/0/0/1/[text_validation,1][data_types,1]/0/0/0/0/0/1/0/0/0/1/0/0/1/0/0/0/0/0/1 |

    # Disable external module in project
    Given I click on the link labeled "Manage"
    And I click on the button labeled "Disable"
    Then I should see "Disable module?"
    When I click on the button labeled "Disable module"
    Then I should NOT see "Configuration Monitor - v1.0.0"

    # Disable external module in Control Center
    Given I click on the link labeled "My Projects"
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