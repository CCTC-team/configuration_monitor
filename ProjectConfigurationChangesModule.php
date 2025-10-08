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
        if (array_key_exists("to-emailids", $settings) && array_key_exists("from-emailid", $settings)) {
            $lastIndex = array_key_last($settings['to-emailids']);
            if(empty($settings['to-emailids'][$lastIndex]) or empty($settings['from-emailid'])) {
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

        if($rights['user_rights'] or $this->isSuperUser()) {
            return $link;
        } else {
            return 0;
        }
    }
   
    function execFromFile($file): void
    {
        $sql = file_get_contents(dirname(__FILE__) . "/sql-setup/$file");
        db_query($sql);
    }

    function redcap_module_system_enable($version): void
    {
        // Create the necessary table and triggers when the module is enabled
        self::execFromFile("0010_create_table_user_role_changelog.sql");
        self::execFromFile("0020_create_InsertTrigger.sql");
        self::execFromFile("0030_create_UpdateTrigger.sql");
        self::execFromFile("0040_create_DeleteTrigger.sql");
        self::execFromFile("0050_create_UserRoleChange_proc.sql");
    } 

    function redcap_module_system_disable($version): void
    {
        // Clean up the database objects when the module is disabled
        // Uncomment the line below if you want to drop the table when the module is disabled.
        // Be cautious as this will delete all logged data.
        // db_query("DROP TABLE IF EXISTS user_role_changelog;");
        db_query("DROP TRIGGER IF EXISTS user_role_insert_trigger;");
        db_query("DROP TRIGGER IF EXISTS user_role_update_trigger;");
        db_query("DROP TRIGGER IF EXISTS user_role_delete_trigger;");
        db_query("DROP PROCEDURE IF EXISTS GetUserRoleChanges;");
    }

    function userRoleChanges($roleID, $old, $new, $ts, $action): array
    {
        // $finalRow = array();
        if ($action !== 'UPDATE') {
            // For INSERT and DELETE actions, return a single row with all values
            // return self::createRow($roleID, 'All Privileges', $old ?: 'N/A', $new ?: 'N/A', $ts, $action);
            $finalRow[] = [
                'roleID' => $roleID,
                'privilege' => 'All Privileges',
                'oldValue' => $old ?: 'N/A',
                'newValue' => $new ?: 'N/A',
                'timestamp' => $ts,
                'action' => $action
            ];
        } else {

            //For update action, compare old and new values and return only changed privileges
            // Column names corresponding to the order of values in the concatenated string
            $userroleColumnNames = array("Role Name", "Unique Role Name", "Lock Record", "Lock Record Multiform", "Lock Record Customize", "Data Export Instruments", "Data Import Tool", "Data Comparison Tool", "Data Logging", "Email Logging", "File Repository", "Double Data", "User Rights", "Data Access Groups", "Graphical", "Reports", "Design", "Alerts", "Calendar", "Data Entry", "API Export", "API Import", "API Modules", "Mobile App", "Mobile App Download Data", "Record Create", "Record Rename", "Record Delete", "Dts", "Participants", "Data Quality Design", "Data Quality Execute", "Data Quality Resolution", "Random Setup", "Random Dashboard", "Random Perform", "Realtime Webservice Mapping", "Realtime Webservice Adjudicate", "External Module Config", "Mycap Participants");

            $old_parts = explode("|", $old);
            $new_parts = explode("|", $new);
        
            $max = max(count($old_parts), count($new_parts));
            for ($i = 0; $i < $max; $i++) {
                $o = $old_parts[$i] ?? '';
                $n = $new_parts[$i] ?? '';
                
                if ($o !== $n) {
                    // Data_Export_Instruments and Data_Entry privileges need special handling. 
                    // They contain multiple values in the format [text,number]
                    if ($i == 5 || $i == 19) {
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
                                // $row = array();
                                if ($oval != $nval) {           // Value differs
                                    // $row .= self::createRow($roleID, $userroleColumnNames[$i], "[$key,$oval]", "[$key,$nval]", $ts, $action);
                                
                                    $row = [
                                        'roleID' => $roleID,
                                        'privilege' => $userroleColumnNames[$i],
                                        'oldValue' => "[$key,$oval]",
                                        'newValue' => "[$key,$nval]",
                                        'timestamp' => $ts,
                                        'action' => $action
                                    ];

                                    $finalRow[] = $row;
                                }
                            }
                        }
                    } else {
                        // For other privileges, show full difference
                        // $row .= self::createRow($roleID, $userroleColumnNames[$i], $o, $n, $ts, $action);
                        $finalRow[] = [
                            'roleID' => $roleID,
                            'privilege' => $userroleColumnNames[$i],
                            'oldValue' => $o,
                            'newValue' => $n,
                            'timestamp' => $ts,
                            'action' => $action
                        ];

                    }
                }
            }
        }

        return $finalRow;
    }

    function MakeUserRoleTable($dcs, $userDateFormat) : string
    {
        // totalCount passed by reference to return total count of changes
        $roleChanges = array();
        // global $module;
        $table = "<table id='user_role_change_table' border='1'>
        <thead><tr style='background-color: #FFFFE0;'>
            <th style='width: 5%;padding: 5px'>Role ID</th>
            <th style='width: 15%;padding: 5px'>Timestamp</th>
            <th style='width: 15%;padding: 5px'>Action</th>
            <th style='width: 15%;padding: 5px'>Changed Privilege</th>
            <th style='width: 15%;padding: 5px'>Old Value</th>
            <th style='width: 15%;padding: 5px'>New Value</th>
        </tr></thead><tbody>";

        foreach($dcs as $dc) {
            // static $dcCount = 1; // Number of data changes
            $date = DateTime::createFromFormat('YmdHis', $dc["timestamp"]);
            $formattedDate = $date->format($userDateFormat);

            $roleChanges = self::userRoleChanges($dc["roleID"], $dc["oldValue"], $dc["newValue"], $formattedDate, $dc["action"]);
            // foreach ($roleChanges as $r) {
            //     echo "<br><br>";
            //     print_r($r);
            // }
            //
            // print_r($roleChanges);
            $table .= self::createRow($roleChanges);
            // if (is_array($row)) {
            //     $countChanges = 0; // Number of changed privileges within a single data change
            //     foreach ($row as $r) {
            //         $countChanges++;
            //         // echo "dcCount: $dcCount<br>";
            //         $table .= self::createRow($r['roleID'], $r['privilege'], $r['oldValue'], $r['newValue'], $r['timestamp'], $r['action'], $countChanges, $dcCount);
            //     }
            // }

            // $dcCount++;
        }

        // $totalCount = $count;

        return $table .= "</tbody></table>";
    }

    function createRow($roleChanges): string
    {
        $span = count($roleChanges);
        // echo "<br>span: $span<br>";
        $row = "<tr style='background-color: $bgColor;'>
                <td rowspan='$span'>" . $roleChanges[0]['roleID'] . "</td>
                <td rowspan='$span'>" . $roleChanges[0]['timestamp'] . "</td>
                <td rowspan='$span'>" . $roleChanges[0]['action'] . "</td>";

        foreach ($roleChanges as $r) {
            // $countChanges++;
            // echo "dcCount: $dcCount<br>";
            $row .= "<td>" . $r['privilege'] . "</td>
                <td>" . $r['oldValue'] . "</td>
                <td>" . $r['newValue'] . "</td></tr>" ;
        }

        // $row .= "</tr>";
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
        //run the stored proc
        $logDataSets = GetDbData::GetUserRoleChangesFromSP($projId, $minDateDb, $maxDateDb, 0, 25, "desc", $roleID);

        $dcs = $logDataSets['dataChanges'];
        $showingCount = count($dcs);

        if ($showingCount != 0) { // Only send email if there are changes

        global $datetime_format;

        $userDateFormat = str_replace('y', 'Y', strtolower($datetime_format));
        if(ends_with($datetime_format, "_24")){
            $userDateFormat = str_replace('_24', ' H:i', $userDateFormat);
        } else {
            $userDateFormat = str_replace('_12', ' H:i a', $userDateFormat);
        }

            $table = self::MakeUserRoleTable($dcs, $userDateFormat);

            // Prepare to-email parameters
            $to_emails = $this->getProjectSetting('to-emailids');
            $to = null;
            // Handle multiple email addresses separated by commas
            foreach ($to_emails as $to_email) {
                $to .= $to_email . ",";
            }

            $from = $this->getProjectSetting('from-emailid');
            $subject = "Project Configuration Changes Log";
            $body = "Dear User,<br><br>Please find attached the log detailing the recent changes to the project configuration within the last $max_days_email hours.<br>";
 
            $projectTitle = $this->getTitle();
    
            $body .= "<h3>Project Configuration Changes for Project ID: $projId - $projectTitle</h3>";
            // $body .= "<h3>userDateFormat: $userDateFormat</h3>";
            $body .= "<h4>Changes in User Role Privileges</h4>";
            $body .= "<p><i>This log shows changes made to user role privileges.</i></p>";

            $body .= $table;
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