Feature: E.129.3100 - The system shall record configuration changes for the Configuration Monitor external module (who, when, old->new) to the module's View Logs page.

  As a REDCap administrator
  I want every configuration change to be written to the module's External Module Logs
  So that there is an audit trail of who changed which setting, when, and from what value to what.

  Scenario: Enable external module from Control Center
    Given I login to REDCap with the user "Test_Admin"
    When I click on the link labeled "Control Center"
    And I click on the link labeled "Manage"
    Then I should see "External Modules - Module Manager"
    And I should NOT see "Configuration Monitor - v1.1.0"
    When I click on the button labeled "Enable a module"
    And I wait for 2 seconds
    Then I should see "Available Modules"
    And I click on the button labeled "Enable" in the row labeled "Configuration Monitor"
    And I wait for 1 second
    And I click on the button labeled "Enable"
    Then I should see "Configuration Monitor - v1.1.0"

  Scenario: First system configuration save logs the initial values
    # This module carries system-scope settings as well as project-scope ones, so
    # the audit trail is verified at both scopes. System settings are configured
    # from the Control Center and log under "Configuration changed (system)".
    Given I login to REDCap with the user "Test_Admin"
    When I click on the link labeled "Control Center"
    And I click on the link labeled "Manage"
    Then I should see "External Modules - Module Manager"
    And I should see "Configuration Monitor - v1.1.0"

    Given I click on the button labeled "Configure"
    Then I should see "Configure Module"
    When I check the checkbox labeled "Enable System Changes"
    And I enter "14" into the input field labeled "Specify the maximum number of days to look back when displaying system configuration changes"
    And I click on the button labeled "Save"
    Then I should see "Configuration Monitor - v1.1.0"

    #VERIFY - the audit trail on the module's own View Logs page
    When I click on the link labeled "View Logs"
    Then I should see "External Module Logs"
    And I should see a table header and row containing the following values in a table:
      | Module                 | Message                        | UserName   |
      | configuration_monitor  | Configuration changed (system) | Test_Admin |

    # The hook logs one entry per changed key in config.json order
    # (system-changes-enable then sys-max-days-page), and View Logs shows newest
    # first, so the FIRST button is sys-max-days-page and the SECOND is
    # system-changes-enable. The email settings are left disabled, so their keys
    # stay empty and are not logged.
    When I click on the first button labeled "Show Parameters"
    Then I should see "Log Entry Parameters"
    And I should see a table header and row containing the following values in a table:
      | Name      | Value            |
      | setting   | sys-max-days-page |
      | old_value | (empty)          |
      | new_value | 14               |
    And I click on the button labeled "Close"
    Then I should see "External Module Logs"

    When I click on the second button labeled "Show Parameters"
    Then I should see "Log Entry Parameters"
    And I should see a table header and row containing the following values in a table:
      | Name      | Value                 |
      | setting   | system-changes-enable |
      | old_value | (empty)               |
      | new_value | 1                     |

  Scenario: First project configuration save logs the initial values
    Given I login to REDCap with the user "Test_Admin"
    And I create a new project named "E.129.3100" by clicking on "New Project" in the menu bar, selecting "Practice / Just for fun" from the dropdown, choosing file "fixtures/cdisc_files/Project_redcap_val_nodata.xml", and clicking the "Create Project" button
    And I click on the link labeled "Manage"
    Then I should see "External Modules - Project Module Manager"
    When I click on the button labeled "Enable a module"
    And I click on the button labeled "Enable" in the row labeled "Configuration Monitor - v1.1.0"
    Then I should see "Configuration Monitor - v1.1.0"

    Given I click on the button labeled "Configure"
    Then I should see "Configure Module"
    When I check the checkbox labeled "Enable User Role Changes"
    And I enter "10" into the input field labeled "Specify the maximum number of days to look back when displaying configuration changes"
    And I click on the button labeled "Save"
    Then I should see "Configuration Monitor - v1.1.0"

    #VERIFY - the audit trail on the module's own View Logs page
    When I click on the link labeled "View Logs"
    Then I should see "External Module Logs"
    And I should see a table header and row containing the following values in a table:
      | Module                | Message                         | UserName   |
      | configuration_monitor | Configuration changed (project) | Test_Admin |

    # config.json order is user-role-changes-enable, project-changes-enable then
    # max-days-page, so newest-first the FIRST button is max-days-page and the
    # SECOND is user-role-changes-enable (project-changes-enable was left
    # unchecked, so it stays empty and is not logged).
    When I click on the first button labeled "Show Parameters"
    Then I should see "Log Entry Parameters"
    And I should see a table header and row containing the following values in a table:
      | Name      | Value         |
      | setting   | max-days-page |
      | old_value | (empty)       |
      | new_value | 10            |
    And I click on the button labeled "Close"
    Then I should see "External Module Logs"

    When I click on the second button labeled "Show Parameters"
    Then I should see "Log Entry Parameters"
    And I should see a table header and row containing the following values in a table:
      | Name      | Value                    |
      | setting   | user-role-changes-enable |
      | old_value | (empty)                  |
      | new_value | 1                        |

  Scenario: Changing a setting logs an old->new audit entry
    # rctf starts each scenario from a clean browser page, so re-navigate to the
    # project fresh (same pattern as the other continuation scenarios).
    Given I login to REDCap with the user "Test_Admin"
    When I click on the link labeled "My Projects"
    And I click on the link labeled "E.129.3100"
    And I click on the link labeled "Manage"
    Then I should see "External Modules - Project Module Manager"
    And I should see "Configuration Monitor - v1.1.0"

    When I click on the button labeled "Configure"
    Then I should see "Configure Module"
    And I clear field and enter "20" into the input field labeled "Specify the maximum number of days to look back when displaying configuration changes"
    Then I click on the button labeled "Save"
    And I should see "Configuration Monitor - v1.1.0"

    #VERIFY - the audit trail on the module's own View Logs page
    When I click on the link labeled "View Logs"
    Then I should see "External Module Logs"
    And I should see a table header and row containing the following values in a table:
      | Module                | Message                         | UserName   |
      | configuration_monitor | Configuration changed (project) | Test_Admin |

    When I click on the first button labeled "Show Parameters"
    Then I should see "Log Entry Parameters"
    And I should see a table header and row containing the following values in a table:
      | Name      | Value         |
      | setting   | max-days-page |
      | old_value | 10            |
      | new_value | 20            |
    And I click on the button labeled "Close"
    Then I should see "External Module Logs"

    # Disable the external module from the Control Center
    When I click on the link labeled "Control Center"
    And I click on the link labeled "Manage"
    Then I should see "External Modules - Module Manager"
    And I click on the button labeled "Disable"
    Then I should see "Disable module?"
    When I click on the button labeled "Disable module"
    Then I should NOT see "Configuration Monitor - v1.1.0"

    # Verify no exceptions are thrown in the system
    Given I open Email
    Then I should NOT see an email with subject "REDCap External Module Hook Exception - configuration_monitor"
