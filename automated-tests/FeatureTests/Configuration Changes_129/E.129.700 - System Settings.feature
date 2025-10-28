Feature: E.129.700 - The system shall support the ability to set up and view logs using Configuration Monitor module.
  
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
    And I should see a link labeled "System Changes"

    When I click on the link labeled "System Changes"
    Then I should see "Changes in System settings"
    And I should see "This log shows changes made to system settings in the last 7 days."
    And I should see "No changes have been made to the system settings."

    # EMAIL ADDRESS SET FOR REDCAP ADMIN - without it, emails are not send out from system
    When I click on the link labeled "General Configuration"
    Then I should see "General Configuration"
    When I enter "redcap@test.instance" into the input field labeled "Email Address of REDCap Administrator"
    And I click on the button labeled "Save Changes"
    Then I should see "Your system configuration values have now been changed"

    When I click on the link labeled "System Changes"
    Then I should see a table header and rows containing the following values in the a table:
      |  Date / Time      | Changed Property      | Old Value               | New Value               |
      |  mm/dd/yyyy hh:mm | redcap_base_url       | https://localhost:8443	| https://localhost:8443/ |
      |  mm/dd/yyyy hh:mm | project_contact_email |                        	| redcap@test.instance    |

    Given I click on the link labeled exactly "Manage"
    Then I should see "External Modules - Module Manager"
    When I click on the button labeled exactly "Configure"
    Then I should see "Configure Module"
    When I check the checkbox labeled "Enable Email"
    And I click on the button labeled "Save" in the dialog box
    Then I should see "Provide the email address used to send notifications" in the dialog box
    When I enter "from@sys.com" into the input field labeled "Provide the email address used to send notifications:" in the dialog box
    And I click on the button labeled "Save" in the dialog box
    Then I should see "1. Provide the email address to receive configuration change notifications" in the dialog box
    When I enter "to@sys.com" into the input field labeled "1. Provide the email address to receive configuration change notifications" in the dialog box
    And I click on the button labeled "Save" in the dialog box
    Then I should see "Configuration Monitor - v1.0.0"

    # E.129.2200 - validate numeric values for page display
    When I click on the button labeled exactly "Configure"
    Then I should see "Configure Module"
    When I enter "4.8" into the input field labeled "Specify the maximum number of days to look back when displaying system configuration changes on the page (default 7 days)" in the dialog box
    And I click on the button labeled "Save" in the dialog box
    Then I should see "Specify the maximum number of days to look back when displaying system configuration changes on the page (default 7 days)" in the dialog box
    When I enter "4" into the input field labeled "Specify the maximum number of days to look back when displaying system configuration changes on the page (default 7 days)" in the dialog box
    And I click on the button labeled "Save" in the dialog box
    Then I should see "Configuration Monitor - v1.0.0"

    # E.129.2200 - validate numeric values for email notifications
    When I click on the button labeled exactly "Configure"
    Then I should see "Configure Module"
    When I enter "2.7" into the input field labeled "Specify the maximum number of hours to look back when sending email notifications (default 3 hours)" in the dialog box
    And I click on the button labeled "Save" in the dialog box
    Then I should see "Specify the maximum number of hours to look back when sending email notifications (default 3 hours)" in the dialog box
    When I enter "2" into the input field labeled "Specify the maximum number of hours to look back when sending email notifications (default 3 hours)" in the dialog box
    And I click on the button labeled "Save" in the dialog box
    Then I should see "Configuration Monitor - v1.0.0"
    # And I logout

    # # Verify no exceptions are thrown in the system
    # Given I open Email
    # Then I should NOT see an email with subject "REDCap External Module Hook Exception - data_entry_log"