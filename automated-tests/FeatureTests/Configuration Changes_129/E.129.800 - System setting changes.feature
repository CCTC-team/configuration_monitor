Feature: E.129.800 - The system shall allow enabling or disabling System Changes tracking at the system level.

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
    Then I should NOT see a link labeled "System Changes"

    When I click on the button labeled exactly "Configure"
    Then I should see "Configure Module"
    When I check the checkbox labeled "Enable System Changes"
    And I click on the button labeled "Save" in the dialog box
    Then I should see "Configuration Monitor - v1.0.0"
    # E.129.2500 - verify System Changes link appears
    And I should see a link labeled "System Changes"

    When I click on the link labeled "System Changes"
    Then I should see "Changes in System Settings"
    And I should see "This log shows changes made to system settings"
    And I should see "No changes have been made to the system settings."

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

    And I should see 2 rows in the system changes table

    Given I click on the link labeled "General Configuration"
    Then I should see "General Configuration"
    And I should see the dropdown field labeled "Automatically send basic statistics to REDCap Consortium?" with the option "Yes, send stats automatically" selected
    When I select "No, send stats manually" on the dropdown field labeled "Automatically send basic statistics to REDCap Consortium?"
    And I click on the button labeled "Save Changes"
    Then I should see "Your system configuration values have now been changed"

    When I click on the link labeled "System Changes" 
    Then I should see "This log shows changes made to system settings"
    And I should see a table header and rows containing the following values in the a table:
      |  Date / Time      | Changed Property      | Old Value               | New Value               |
      |  mm/dd/yyyy hh:mm | auto_report_stats     | 1                     	| 0                       |
      |  mm/dd/yyyy hh:mm | redcap_base_url       | https://localhost:8443	| https://localhost:8443/ |
      |  mm/dd/yyyy hh:mm | project_contact_email |                        	| redcap@test.instance    |

    And I should see 3 rows in the system changes table

    # E.129.2100 - validate filtering system changes
    When I select "project_contact_email" on the dropdown field labeled "Property"
    Then I should see a table header and rows containing the following values in the a table:
      |  Date / Time      | Changed Property      | Old Value               | New Value               |
      |  mm/dd/yyyy hh:mm | project_contact_email |                        	| redcap@test.instance    |

    And I should see 1 row in the system changes table
    And I should NOT see "auto_report_stats"
    And I should NOT see "redcap_base_url"

    # E.129.3000 - validate exporting system changes to CSV
    When I click on the button labeled "Export current page"
    Then the downloaded CSV with filename "SystemChanges_yyyy-mm-dd_hhmm.csv" has the header and rows below
      | changed property      | old value               | new value               |
      | project_contact_email |                        	| redcap@test.instance    |

    When I click on the button labeled "Export everything ignoring filters"
    Then the downloaded CSV with filename "SystemChanges_yyyy-mm-dd_hhmm.csv" has the header and rows below
      | changed property      | old value               | new value               |
      | auto_report_stats     | 1                     	| 0                       |
      | redcap_base_url       | https://localhost:8443	| https://localhost:8443/ |
      | project_contact_email |                        	| redcap@test.instance    |

    # Disable external module in Control Center
    Given I click on the link labeled "Control Center"
    When I click on the link labeled exactly "Manage"
    And I click on the button labeled exactly "Disable"
    Then I should see "Disable module?" in the dialog box
    When I click on the button labeled "Disable module" in the dialog box
    Then I should NOT see "Configuration Monitor - v0.0.0"
    And I logout
  
    # Verify no exceptions are thrown in the system
    Given I open Email
    Then I should NOT see an email with subject "REDCap External Module Hook Exception - configuration_monitor"