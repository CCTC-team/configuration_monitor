<?php

namespace CCTC\ProjectConfigurationChangesModule;

use CCTC\ProjectConfigurationChangesModule\GetDbData;
use CCTC\ProjectConfigurationChangesModule\Rendering;

use REDCap;
use DateTime;
use ExternalModules\AbstractExternalModule;

class ProjectConfigurationChangesModule extends AbstractExternalModule {

    
    public function validateSettings($settings): ?string
    {
        if (array_key_exists("to-emailids", $settings) && array_key_exists("from-emailid", $settings) && array_key_exists("user-role-changes-enable", $settings) && array_key_exists("project-changes-enable", $settings)) {
            $lastIndex = array_key_last($settings['to-emailids']);
            if(empty($settings['to-emailids'][$lastIndex]) or empty($settings['from-emailid']) or (empty($settings['user-role-changes-enable']) and empty($settings['project-changes-enable']))) {
                return "Please ensure Project Configuration Changes External Module settings are configured.";
            }
        }

        if (array_key_exists("max-days-index", $settings) and !empty($settings['max-days-index'])) {
            if(intval($settings['max-days-index']) != $settings['max-days-index']) {
                return "The maximum number of days should be a number";
            }
        }

        if (array_key_exists("max-hours-email", $settings) and !empty($settings['max-hours-email'])) {
            if(intval($settings['max-hours-email']) != $settings['max-hours-email']) {
                return "The maximum number of hours for email should be a number";
            }
        }
    
        return null;
    }

    public function redcap_module_link_check_display($project_id, $link) {

        $user = $this->getUser();
        $rights = $user->getRights();

        // Hide from non-privileged users
        if(!($rights['user_rights'] or $this->isSuperUser())) {
            return null;
        }

        // Get the URL from the link (whether it's an array or string)
        $url = is_array($link) ? $link['url'] : $link;

        // // DEBUG: Log what we're checking
        // REDCap::logEvent("Link Check Debug",
        //     "URL: $url\n" .
        //     "user-role-changes-enable: " . var_export($this->getProjectSetting('user-role-changes-enable'), true) . "\n" .
        //     "project-changes-enable: " . var_export($this->getProjectSetting('project-changes-enable'), true)
        // );

        // Check specific link against corresponding config setting
        if(strpos($url, 'userRoleChanges') !== false) {
            return $this->getProjectSetting('user-role-changes-enable') ? $link : null;
        } elseif(strpos($url, 'projectChanges') !== false) {
            return $this->getProjectSetting('project-changes-enable') ? $link: null;
        }

        return null;
    }
   
    function execFromFile($file): void
    {
        $sql = file_get_contents(dirname(__FILE__) . "/sql-setup/$file");
        db_query($sql);
    }

    function redcap_module_system_enable($version): void
    {
        // Create the necessary table and triggers when the module is enabled
        //User Role Change Log Table, Triggers and Stored Procedure
        self::execFromFile("0010_create_table_user_role_changelog.sql");
        self::execFromFile("0020_roles_create_InsertTrigger.sql");
        self::execFromFile("0030_roles_create_UpdateTrigger.sql");
        self::execFromFile("0040_roles_create_DeleteTrigger.sql");
        self::execFromFile("0050_create_UserRoleChange_proc.sql");

        //Project Change Log Table, Update Trigger and Stored Procedure
        // Project deletions are logged as an UPDATE, with date_deleted set to the current timestamp.
        self::execFromFile("0060_create_table_project_changelog.sql");
        self::execFromFile("0070_projects_create_UpdateTrigger.sql");
        self::execFromFile("0080_create_ProjectChange_proc.sql");
    } 

    function redcap_module_system_disable($version): void
    {
        // Clean up the database objects when the module is disabled

        // Drop the User Role Change Log table, triggers, and stored procedure
        // Uncomment the line below if you want to drop the table when the module is disabled.
        // Be cautious as this will delete all logged data.
        // db_query("DROP TABLE IF EXISTS user_role_changelog;");
        db_query("DROP TRIGGER IF EXISTS user_role_insert_trigger;");
        db_query("DROP TRIGGER IF EXISTS user_role_update_trigger;");
        db_query("DROP TRIGGER IF EXISTS user_role_delete_trigger;");
        db_query("DROP PROCEDURE IF EXISTS GetUserRoleChanges;");

        // Drop the Project Change Log table, update trigger, and stored procedure
        // Uncomment the line below if you want to drop the table when the module is disabled.
        // Be cautious as this will delete all logged data.
        // db_query("DROP TABLE IF EXISTS project_changelog;");
        db_query("DROP TRIGGER IF EXISTS projects_update_trigger;");
        db_query("DROP PROCEDURE IF EXISTS GetProjectChanges;");
    }

    function getUniquePrivileges($dcs, $tableName): array
    {
        $privileges = array();

        foreach($dcs as $dc) {
            $changes = self::recordDiff($dc, $tableName);
            foreach ($changes as $change) {
                if (!in_array($change['privilege'], $privileges)) {
                    $privileges[] = $change['privilege'];
                }
            }
        }

        sort($privileges);
        return $privileges;
    }

    function filterByPrivilege($dcs, $tableName, $privilegeFilter): array
    {
        if (empty($privilegeFilter)) {
            return $dcs;
        }

        $filtered = array();

        foreach($dcs as $dc) {
            $changes = self::recordDiff($dc, $tableName);
            $hasMatchingPrivilege = false;

            foreach ($changes as $change) {
                if ($change['privilege'] === $privilegeFilter) {
                    $hasMatchingPrivilege = true;
                    break;
                }
            }

            if ($hasMatchingPrivilege) {
                $filtered[] = $dc;
            }
        }

        return $filtered;
    }

    function recordDiff($dc, $tableName): array
    {
        //Only UserRoleChanges has insert and delete actions
        if ($dc["action"] !== 'UPDATE') {
            // For INSERT and DELETE actions, return a single row with all values
            $finalRow[] = [
                'id' => $dc["id"],
                'privilege' => 'All Privileges',
                'oldValue' => $dc["oldValue"] ?: 'N/A',
                'newValue' => $dc["newValue"] ?: 'N/A',
                'timestamp' => $dc["timestamp"],
                'action' => $dc["action"]
            ];
        } else {
            //For update action, compare old and new values and return only changed privileges
            // Column names corresponding to the order of values in the concatenated string
            $userroleColumnNames = array("Role Name", "Lock Record", "Lock Record Multiform", "Lock Record Customize", "Data Export Instruments", 
                                    "Data Import Tool", "Data Comparison Tool", "Data Logging", "Email Logging", "File Repository", "Double Data", 
                                    "User Rights", "Data Access Groups", "Graphical", "Reports", "Design", "Alerts", "Calendar", "Data Entry", "API Export", 
                                    "API Import", "API Modules", "Mobile App", "Mobile App Download Data", "Record Create", "Record Rename", "Record Delete", 
                                    "Dts", "Participants", "Data Quality Design", "Data Quality Execute", "Data Quality Resolution", "Random Setup", "Random Dashboard", 
                                    "Random Perform", "Realtime Webservice Mapping", "Realtime Webservice Adjudicate", "External Module Config", "Mycap Participants");
            $projectColumnNames = array("Project Name", "App Title", "Status", "Inactive Time", "Completed Time", "Completed By", "Data Locked", "Draft Mode", "Surveys Enabled",
                                    "Repeat Forms", "Scheduling", "Purpose", "Purpose Other", "Show Which Records", "Count Project", "Investigators", "Project Note", "Online Offline",
                                    "Auth Meth", "Double Data Entry", "Project Language", "Project Encoding", "Is Child Of", "Date Shift Max", "Institution", "Site Org Type",
                                    "Grant Cite", "Project Contact Name", "Project Contact Email", "Header Logo", "Auto Inc Set", "Custom Data Entry Note", "Custom Index Page Note",
                                    "Order Id By", "Custom Reports", "Report Builder", "Disable Data Entry", "Google Translate Default", "Require Change Reason", "Dts Enabled", "Project Pi Firstname", "Project Pi Mi", "Project Pi Lastname",
                                    "Project Pi Email", "Project Pi Alias", "Project Pi Username", "Project Pi Pub Exclude", "Project Pub Matching Institution", "Project Irb Number",
                                    "Project Grant Number", "History Widget Enabled", "Secondary Pk", "Secondary Pk Display Value", "Secondary Pk Display Label", "Custom Record Label",
                                    "Display Project Logo Institution", "Imported From Rs", "Display Today Now Button", "Auto Variable Naming", "Randomization", "Enable Participant Identifiers",
                                    "Survey Email Participant Field", "Survey Phone Participant Field", "Data Entry Trigger Url", "Template Id", "Date Deleted", "Data Resolution Enabled",
                                    "Field Comment Edit Delete", "Drw Hide Closed Queries From Dq Results", "Realtime Webservice Enabled", "Realtime Webservice Type", "Realtime Webservice Offset Days",
                                    "Realtime Webservice Offset Plusminus", "Edoc Upload Max", "File Attachment Upload Max", "Survey Queue Custom Text", "Survey Queue Hide", "Survey Auth Enabled",
                                    "Survey Auth Field1", "Survey Auth Event Id1", "Survey Auth Field2", "Survey Auth Event Id2", "Survey Auth Field3", "Survey Auth Event Id3", "Survey Auth Min Fields",
                                    "Survey Auth Apply All Surveys", "Survey Auth Custom Message",
                                    "Survey Auth Fail Limit", "Survey Auth Fail Window", "Twilio Enabled", "Twilio Modules Enabled", "Twilio Hide In Project", "Twilio Account Sid",
                                    "Twilio Auth Token", "Twilio From Number", "Twilio Voice Language", "Twilio Option Voice Initiate", "Twilio Option Sms Initiate",
                                    "Twilio Option Sms Invite Make Call", "Twilio Option Sms Invite Receive Call", "Twilio Option Sms Invite Web", "Twilio Default Delivery Preference",
                                    "Twilio Request Inspector Checked", "Twilio Request Inspector Enabled", "Twilio Append Response Instructions", "Twilio Multiple Sms Behavior",
                                    "Twilio Delivery Preference Field Map", "Mosio Api Key", "Mosio Hide In Project", "Two Factor Exempt Project", "Two Factor Force Project", "Disable Autocalcs",
                                    "Custom Public Survey Links", "Pdf Custom Header Text", "Pdf Show Logo Url", "Pdf Hide Secondary Field", "Pdf Hide Record Id", "Shared Library Enabled",
                                    "Allow Delete Record From Log", "Delete File Repository Export Files", "Custom Project Footer Text", "Custom Project Footer Text Link", "Google Recaptcha Enabled",
                                    "Datamart Allow Repeat Revision", "Datamart Allow Create Revision", "Datamart Enabled", "Break The Glass Enabled", "Datamart Cron Enabled", "Datamart Cron End Date",
                                    "Fhir Include Email Address Project", "File Upload Vault Enabled", "File Upload Versioning Enabled", "Missing Data Codes", "Record Locking Pdf Vault Enabled",
                                    "Record Locking Pdf Vault Custom Text", "Fhir Cdp Auto Adjudication Enabled", "Fhir Cdp Auto Adjudication Cronjob Enabled", "Project Dashboard Min Data Points",
                                    "Bypass Branching Erase Field Prompt", "Protected Email Mode", "Protected Email Mode Custom Text", "Protected Email Mode Trigger", "Protected Email Mode Logo",
                                    "Hide Filled Forms", "Hide Disabled Forms", "Form Activation Survey Autocontinue", "Sendgrid Enabled", "Sendgrid Project Api Key", "Mycap Enabled",
                                    "File Repository Total Size", "Ehr Id", "Allow Econsent Allow Edit", "Store In Vault Snapshots Containing Completed Econsent"
                                    );
            
            $columnNames = ($tableName == 'user_role_changes') ? $userroleColumnNames : $projectColumnNames;
            $old_parts = explode("|", $dc["oldValue"]);
            $new_parts = explode("|", $dc["newValue"]);

            $max = max(count($old_parts), count($new_parts));

            for ($i = 0; $i < $max; $i++) {
                $o = $old_parts[$i] ?? '';
                $n = $new_parts[$i] ?? '';
                
                if ($o !== $n) {
                    // "Data Export Instruments" and "Data Entry" privileges need special handling.
                    // They contain multiple values in the format [text,number]
                    if (in_array($columnNames[$i], ["Data Export Instruments", "Data Entry"]) && $tableName == 'user_role_changes') {
                        preg_match_all('/\[([a-zA-Z0-9_]+),([0-9]+)\]/', $n, $nmatches, PREG_SET_ORDER);
                        preg_match_all('/\[([a-zA-Z0-9_]+),([0-9]+)\]/', $o, $omatches, PREG_SET_ORDER);

                        $nresult = []; // Array for new values
                        foreach ($nmatches as $nmatch) {
                            $key = $nmatch[1];   // text before comma
                            $val = $nmatch[2];   // number after comma
                            $nresult[$key] = $val;
                        }

                        $oresult = []; // Array for old values
                        foreach ($omatches as $omatch) {
                            $key = $omatch[1];   // text before comma
                            $val = $omatch[2];   // number after comma
                            $oresult[$key] = $val;
                        }
                        
                        foreach ($oresult as $key => $oval) {
                            if (isset($nresult[$key])) {         // Key exists in both arrays
                                $nval = $nresult[$key];
                                if ($oval != $nval) {           // Value differs
                                    $row = [
                                        'id' => $dc["id"],
                                        'privilege' => $columnNames[$i],
                                        'oldValue' => "[$key,$oval]",
                                        'newValue' => "[$key,$nval]",
                                        'timestamp' => $dc["timestamp"],
                                        'action' => $dc["action"]
                                    ];

                                    $finalRow[] = $row;
                                }
                            }
                        }
                    } else {
                        // For other privileges, show full difference

                        $finalRow[] = [
                            'id' => $dc["id"] ?? null, // Project changes do not have id
                            'privilege' => $columnNames[$i],
                            'oldValue' => $o,
                            'newValue' => $n,
                            'timestamp' => $dc["timestamp"],
                            'action' => $dc["action"]
                        ];
                    }
                }
            }
        }

        return $finalRow;
    }

    function MakeUserRoleTable($dcs, $userDateFormat, $tableName) : string
    {
        $changes = array();
        if ($tableName == "user_role_changes") {
            $table = "<table id='user_role_change_table' border='1'>
            <thead><tr style='background-color: #FFFFE0;'>
                <th style='width: 5%;padding: 5px'>Role ID</th>
                <th style='width: 15%;padding: 5px'>Timestamp</th>
                <th style='width: 15%;padding: 5px'>Action</th>
                <th style='width: 15%;padding: 5px'>Changed Privilege</th>
                <th style='width: 15%;padding: 5px'>Old Value</th>
                <th style='width: 15%;padding: 5px'>New Value</th>
            </tr></thead><tbody>";
        } else {
            $table = "<table id='project_change_table' border='1'>
            <thead><tr style='background-color: #FFFFE0;'>
                <th style='width: 15%;padding: 5px'>Timestamp</th>
                <th style='width: 15%;padding: 5px'>Changed Property</th>
                <th style='width: 15%;padding: 5px'>Old Value</th>
                <th style='width: 15%;padding: 5px'>New Value</th>
            </tr></thead><tbody>";
        }
        foreach($dcs as $dc) {

            $date = DateTime::createFromFormat('YmdHis', $dc["timestamp"]);
            $formattedDate = $date->format($userDateFormat);
            $dc["timestamp"] = $formattedDate;
            $changes = self::recordDiff($dc, $tableName);
            $table .= self::createRow($changes, $tableName);
        }

        return $table .= "</tbody></table>";
    }

    function createRow($changes, $tableName): string
    {
        $span = count($changes);
        $row = "<tr>";

        if ($tableName == "user_role_changes") {
            $row .= "<td rowspan='$span'>" . $changes[0]['id'] . "</td>
                    <td rowspan='$span'>" . $changes[0]['action'] . "</td>";
        }

        $row .= "<td rowspan='$span'>" . $changes[0]['timestamp'] . "</td>";

        foreach ($changes as $r) {
            $row .= "<td>" . $r['privilege'] . "</td>
                <td>" . $r['oldValue'] . "</td>
                <td>" . $r['newValue'] . "</td></tr>" ;
        }

        return $row;
    }

    function sendEmail(): void
    {
        global $Proj;
        $modName = $this->getModuleDirectoryName();

        require_once dirname(APP_PATH_DOCROOT, 1) . "/modules/$modName/GetDbData.php";
        require_once dirname(APP_PATH_DOCROOT, 1) . "/modules/$modName/Rendering.php";
        require_once dirname(APP_PATH_DOCROOT, 1) . "/modules/$modName/Utility.php";

        $projId = $this->getProjectId();
        $maxHour = $this->getProjectSetting('max-hours-email') ?? 3; // Default to 3 hours if not set
        $roleID = NULL; //all roles
        $minDate = Utility::NowAdjusted('-'. $maxHour . 'hours'); //default to maxHour hours ago
        $minDateDb = Utility::DateStringToDbFormat($minDate);
        $maxDateDb = NULL; //no max date
        $showingCount1 = 0;
        $showingCount2 = 0;

        //run the stored proc
        if ($this->getProjectSetting('user-role-changes-enable')) {
            $logDataSetsUserRoles = GetDbData::GetChangesFromSP($projId, $minDateDb, $maxDateDb, 0, 25, "desc", "user_role_changes", $roleID);
            $dcs1 = $logDataSetsUserRoles['dataChanges'];
            $showingCount1 = count($dcs1);
        }
        if ($this->getProjectSetting('project-changes-enable')) {
            $logDataSetsProj = GetDbData::GetChangesFromSP($projId, $minDateDb, $maxDateDb, 0, 25, "desc", "project_changes");
            $dcs2 = $logDataSetsProj['dataChanges'];
            $showingCount2 = count($dcs2);
        }

        if ($showingCount1 != 0 or $showingCount2 != 0) { // Only send email if there are changes

            global $default_datetime_format;

            $userDateFormat = str_replace('y', 'Y', strtolower($default_datetime_format));
            if(ends_with($default_datetime_format, "_24")){
                $userDateFormat = str_replace('_24', ' H:i', $userDateFormat);
            } else {
                $userDateFormat = str_replace('_12', ' H:i a', $userDateFormat);
            }

            // Prepare to-email parameters
            $to_emails = $this->getProjectSetting('to-emailids');
            $to = null;
            // Handle multiple email addresses separated by commas
            foreach ($to_emails as $to_email) {
                $to .= $to_email . ",";
            }

            $from = $this->getProjectSetting('from-emailid');
            $projectTitle = $this->getTitle();

            $subject = "Project Configuration Changes Log";
            $body = "Dear User,<br><br>Please find attached the log detailing the recent changes to the project configuration within the last $max_days_email hours.<br>";
            $body .= "<h3>Project Configuration Changes for Project ID: $projId - $projectTitle</h3>";

            if ($showingCount1 != 0) {
                $table1 = self::MakeUserRoleTable($dcs1, $userDateFormat, "user_role_changes");
                $body .= "<h4>Changes in User Role Privileges</h4>";
                $body .= "<p><i>This log shows changes made to user role privileges.</i></p>";
                $body .= $table1;
                $body .= "<br><br>";
            }

            if ($showingCount2 != 0) {
                $table2 = self::MakeUserRoleTable($dcs2, $userDateFormat, "project_changes");
                $body .= "<h4>Changes in Project settings</h4>";
                $body .= "<p><i>This log shows changes made to project settings.</i></p>";
                $body .= $table2;
                $body .= "<br><br>";
            }

            $body .= "<b style='color: #f00a0aff;'>Note: This is an automated email. Please do not reply to this message.</b>";
            $body .= "<br><br>Best regards,<br>Your REDCap Team";

            $email_sent = REDCap::email(
                $to,           // Recipient email address
                $from,         // Sender email address
                $subject,      // Email subject
                $body      // Email body
            );

            if ($email_sent) {
                $this->log("Email sent successfully!");
            } else {
                $this->log("Failed to send email.");
            }
        }
    }

    function projectConfigCron($cronInfo = array()) {
        try {
            $this->log("Starting the \"{$cronInfo['cron_description']}\" cron job...");
            foreach ($this->getProjectsWithModuleEnabled() as $localProjectId) {
                $this->setProjectId($localProjectId);
        
                // Project specific method calls go here.
                $this->sendEmail();
            }
        
            return "The \"{$cronInfo['cron_description']}\" cron job completed successfully.";
        } catch ( \Throwable $e ) {
            $this->log("Error updating projects", [ "error" => $e->getMessage() ]);
            return "The \"{$cronInfo['cron_name']}\" cron job failed: " . $e->getMessage();
        }
    }
}