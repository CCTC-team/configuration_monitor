Feature: E.129.900 - The system shall allow enabling or disabling Project Changes tracking at the project level.

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
    Given I create a new project named "E.129.900" by clicking on "New Project" in the menu bar, selecting "Practice / Just for fun" from the dropdown, choosing file "redcap_val/Project_redcap_val_nodata.xml", and clicking the "Create Project" button
    When I click on the link labeled exactly "Manage"
    Then I should see "External Modules - Project Module Manager"
    And I should NOT see "Configuration Monitor - v1.0.0"
    When I click on the button labeled "Enable a module"
    Then I should see "Available Modules"
    And I click on the button labeled Enable for the external module named "Configuration Monitor - v1.0.0"
    Then I should see "Configuration Monitor - v1.0.0"
    And I should NOT see a link labeled "Project Changes"

    When I click on the button labeled exactly "Configure"
    Then I should see "Configure Module"
    When I check the checkbox labeled "Enable Project Changes"
    And I click on the button labeled "Save" in the dialog box
    Then I should see "Configuration Monitor - v1.0.0"
    # E.129.2600 - verify Project Changes link appears
    And I should see a link labeled "Project Changes"

    When I click on the link labeled "Project Changes"
    Then I should see "Changes in Project Settings"
    And I should see "This log shows changes made to project settings"
    And I should see "No changes to project settings have been made in this project"

    When I click on the link labeled "Project Setup"
    And I click on the button labeled "Disable" in the "Auto-numbering for records" row in the "Enable optional modules and customizations" section
    Then I should see a button labeled "Enable" in the "Auto-numbering for records" row in the "Enable optional modules and customizations" section

    When I click on the link labeled "Project Changes"
    Then I should see "This log shows changes made to project settings"
    And I should see a table header and rows containing the following values in the a table:
      |  Date / Time      | Changed Property | Old Value | New Value |
      |  mm/dd/yyyy hh:mm | Auto Inc Set	   | 1	       | 0         |

    Given I click on the link labeled "Project Setup"
    When I click on the button labeled "Enable" in the "Scheduling module" row in the "Enable optional modules and customizations" section
    Then I should see a button labeled "Disable" in the "Scheduling module" row in the "Enable optional modules and customizations" section

    Given I click on the button labeled "Additional customizations"
    When I check the checkbox labeled Require a 'reason' when making changes to existing records in additional customizations
    And I select "Data Resolution Workflow" in the dropdown field labeled "Enable:"
    And I check the checkbox labeled Enable the Data History popup for all data collection instruments in additional customizations
    When I click on the button labeled "Save"
    Then I should see "The Data Resolution Workflow has now been enabled!"
    And I click on the button labeled "Close" in the dialog box

    When I click on the link labeled "Project Changes"
    Then I should see "This log shows changes made to project settings"
    And I should see a table header and rows with rowspan containing the following values in a table:
      |  Date / Time      | Changed Property                        | Old Value | New Value               |
      |  mm/dd/yyyy hh:mm | Require Change Reason	                  | 0	        | 1                       |
      |  mm/dd/yyyy hh:mm | Secondary Pk Display Value	            | 1	        | 0                       |
      |  mm/dd/yyyy hh:mm | Secondary Pk Display Label	            | 1	        | 0                       |
      |  mm/dd/yyyy hh:mm | Data Resolution Enabled	                | 1	        | 2                       |
      |  mm/dd/yyyy hh:mm | Field Comment Edit Delete	              | 1	        | 0                       |
      |  mm/dd/yyyy hh:mm | Drw Hide Closed Queries From Dq Results | 1	        | 0                       |
      |  mm/dd/yyyy hh:mm | Protected Email Mode Custom Text	      | 	        | REDCap Secure Messaging |
      |  mm/dd/yyyy hh:mm | Scheduling                              | 0	        | 1                       |
      |  mm/dd/yyyy hh:mm | Auto Inc Set	                          | 1	        | 0                       |

    And I should see 9 rows in the project changes table

    # E.129.2100 - validate filtering project changes
    When I select "Scheduling" on the dropdown field labeled "Property"
    Then I should see a table header and rows containing the following values in the a table:
      |  Date / Time      | Changed Property | Old Value | New Value |
      |  mm/dd/yyyy hh:mm | Scheduling       | 0         | 1         |

    And I should see 1 row in the project changes table
    And I should NOT see "Require Change Reason"
    And I should NOT see "Secondary Pk Display Value"
    And I should NOT see "Secondary Pk Display Label"
    And I should NOT see "Data Resolution Enabled"
    And I should NOT see "Field Comment Edit Delete"
    And I should NOT see "Drw Hide Closed Queries From Dq Results"
    And I should NOT see "Protected Email Mode Custom Text"
    And I should NOT see "Auto Inc Set"

    # E.129.3000 - validate exporting project changes to CSV
    When I click on the button labeled "Export current page"
    Then the downloaded CSV with filename "E129800_ProjectChanges_yyyy-mm-dd_hhmm.csv" has the header and rows below
      | changed property | old value | new value |
      | Scheduling       | 0         | 1         |

    When I click on the button labeled "Export everything ignoring filters"
    Then the downloaded CSV with filename "E129800_ProjectChanges_yyyy-mm-dd_hhmm.csv" has the header and rows below
      | changed property                        | old value | new value               |
      | Require Change Reason	                  | 0	        | 1                       |
      | Secondary Pk Display Value	            | 1	        | 0                       |
      | Secondary Pk Display Label	            | 1	        | 0                       |
      | Data Resolution Enabled	                | 1	        | 2                       |
      | Field Comment Edit Delete	              | 1	        | 0                       |
      | Drw Hide Closed Queries From Dq Results | 1	        | 0                       |
      | Protected Email Mode Custom Text	      | 	        | REDCap Secure Messaging |
      | Scheduling                              | 0	        | 1                       |
      | Auto Inc Set	                          | 1	        | 0                       |

    # Disable external module in project
    Given I click on the link labeled exactly "Manage"
    And I click on the button labeled exactly "Disable"
    Then I should see "Disable module?" in the dialog box
    When I click on the button labeled "Disable module" in the dialog box
    Then I should NOT see "Configuration Monitor - v0.0.0"

    # Disable external module in Control Center
    Given I click on the link labeled "Control Center"
    When I click on the link labeled exactly "Manage"
    And I click on the button labeled exactly "Disable"
    Then I should see "Disable module?" in the dialog box
    When I click on the button labeled "Disable module" in the dialog box
    Then I should NOT see "Configuration Monitor - v0.0.0"
    And I logout