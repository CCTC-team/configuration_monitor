<?php

namespace CCTC\ProjectConfigurationChangesModule;

use ExternalModules\AbstractExternalModule;

use REDCap;

class ProjectConfigurationChangesModule extends AbstractExternalModule {

    function createTable() {
        $table = "CREATE TABLE IF NOT EXISTS user_role_changelog (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            project_id INT(10) DEFAULT NULL,
            role_id INT(10) DEFAULT NULL,
            old_value TEXT DEFAULT NULL,
            new_value TEXT DEFAULT NULL,
            change_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            operation_type VARCHAR(100) DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        self::exec($table);
    }

    function exec($query): void
    {
        db_query($query);
    }

function createUpdateTrigger() 
    {
        $trigger = "CREATE TRIGGER user_role_update_trigger
        AFTER UPDATE ON redcap_user_roles
        FOR EACH ROW
        BEGIN
            DECLARE old_values TEXT;
            DECLARE new_values TEXT;

            -- Compute old and new concatenated values
            SET old_values = CONCAT_WS('|',
                OLD.role_name, OLD.unique_role_name, OLD.lock_record, OLD.lock_record_multiform, OLD.lock_record_customize,
                OLD.data_export_tool, OLD.data_export_instruments, OLD.data_import_tool, OLD.data_comparison_tool, OLD.data_logging,
                OLD.email_logging, OLD.file_repository, OLD.double_data, OLD.user_rights, OLD.data_access_groups, OLD.graphical,
                OLD.reports, OLD.design, OLD.alerts, OLD.calendar, OLD.data_entry, OLD.api_export, OLD.api_import, OLD.api_modules,
                OLD.mobile_app, OLD.mobile_app_download_data, OLD.record_create, OLD.record_rename, OLD.record_delete,
                OLD.dts, OLD.participants, OLD.data_quality_design, OLD.data_quality_execute, OLD.data_quality_resolution,
                OLD.random_setup, OLD.random_dashboard, OLD.random_perform, OLD.realtime_webservice_mapping,
                OLD.realtime_webservice_adjudicate, OLD.external_module_config, OLD.mycap_participants
            );

            SET new_values = CONCAT_WS('|',
                NEW.role_name, NEW.unique_role_name, NEW.lock_record, NEW.lock_record_multiform, NEW.lock_record_customize,
                NEW.data_export_tool, NEW.data_export_instruments, NEW.data_import_tool, NEW.data_comparison_tool, NEW.data_logging,
                NEW.email_logging, NEW.file_repository, NEW.double_data, NEW.user_rights, NEW.data_access_groups, NEW.graphical,
                NEW.reports, NEW.design, NEW.alerts, NEW.calendar, NEW.data_entry, NEW.api_export, NEW.api_import, NEW.api_modules,
                NEW.mobile_app, NEW.mobile_app_download_data, NEW.record_create, NEW.record_rename, NEW.record_delete,
                NEW.dts, NEW.participants, NEW.data_quality_design, NEW.data_quality_execute, NEW.data_quality_resolution,
                NEW.random_setup, NEW.random_dashboard, NEW.random_perform, NEW.realtime_webservice_mapping,
                NEW.realtime_webservice_adjudicate, NEW.external_module_config, NEW.mycap_participants
            );

            -- Only insert if old and new values are different
            IF ((old_values <> new_values) AND (OLD.unique_role_name = NEW.unique_role_name)) THEN
                INSERT INTO user_role_changelog (
                    project_id, role_id, old_value, new_value,
                    operation_type
                ) VALUES (
                    COALESCE(NEW.project_id, OLD.project_id),
                    COALESCE(NEW.role_id, OLD.role_id),
                    old_values,
                    new_values,
                    'UPDATE'
                );
            END IF;
        END;";
            
        self::exec($trigger);
    }
    
    function createInsertTrigger() 
    {
        $trigger = "CREATE TRIGGER user_role_insert_trigger
        AFTER INSERT ON redcap_user_roles
        FOR EACH ROW
        BEGIN
            DECLARE new_values TEXT;

            -- Compute new concatenated values
            SET new_values = CONCAT_WS('|',
                NEW.role_name, NEW.unique_role_name, NEW.lock_record, NEW.lock_record_multiform, NEW.lock_record_customize,
                NEW.data_export_tool, NEW.data_export_instruments, NEW.data_import_tool, NEW.data_comparison_tool, NEW.data_logging,
                NEW.email_logging, NEW.file_repository, NEW.double_data, NEW.user_rights, NEW.data_access_groups, NEW.graphical,
                NEW.reports, NEW.design, NEW.alerts, NEW.calendar, NEW.data_entry, NEW.api_export, NEW.api_import, NEW.api_modules,
                NEW.mobile_app, NEW.mobile_app_download_data, NEW.record_create, NEW.record_rename, NEW.record_delete,
                NEW.dts, NEW.participants, NEW.data_quality_design, NEW.data_quality_execute, NEW.data_quality_resolution,
                NEW.random_setup, NEW.random_dashboard, NEW.random_perform, NEW.realtime_webservice_mapping,
                NEW.realtime_webservice_adjudicate, NEW.external_module_config, NEW.mycap_participants
            );
			INSERT INTO user_role_changelog (
				project_id, role_id, new_value, operation_type
			) VALUES (
				NEW.project_id,
				NEW.role_id,
				new_values,
				'INSERT'
			);
        END;";
            
        self::exec($trigger);
    }

    function createDeleteTrigger() 
    {
        $trigger = "CREATE TRIGGER user_role_delete_trigger
        AFTER DELETE ON redcap_user_roles
        FOR EACH ROW
        BEGIN
            DECLARE old_values TEXT;

            -- Compute old concatenated values
            SET old_values = CONCAT_WS('|',
                OLD.role_name, OLD.unique_role_name, OLD.lock_record, OLD.lock_record_multiform, OLD.lock_record_customize,
                OLD.data_export_tool, OLD.data_export_instruments, OLD.data_import_tool, OLD.data_comparison_tool, OLD.data_logging,
                OLD.email_logging, OLD.file_repository, OLD.double_data, OLD.user_rights, OLD.data_access_groups, OLD.graphical,
                OLD.reports, OLD.design, OLD.alerts, OLD.calendar, OLD.data_entry, OLD.api_export, OLD.api_import, OLD.api_modules,
                OLD.mobile_app, OLD.mobile_app_download_data, OLD.record_create, OLD.record_rename, OLD.record_delete,
                OLD.dts, OLD.participants, OLD.data_quality_design, OLD.data_quality_execute, OLD.data_quality_resolution,
                OLD.random_setup, OLD.random_dashboard, OLD.random_perform, OLD.realtime_webservice_mapping,
                OLD.realtime_webservice_adjudicate, OLD.external_module_config, OLD.mycap_participants
            );
			INSERT INTO user_role_changelog (
				project_id, role_id, old_value, operation_type
			) VALUES (
				OLD.project_id,
				OLD.role_id,
				old_values,
				'DELETE'
			);
        END;";
            
        self::exec($trigger);
    }

    function redcap_module_system_enable($version): void
    {
        self::createTable();
        self::createInsertTrigger();
        self::createUpdateTrigger();
        self::createDeleteTrigger();

    } 

    function redcap_module_system_disable($version): void
    {
        // Uncomment the line below if you want to drop the table when the module is disabled.
        // Be cautious as this will delete all logged data.
        self::exec("DROP TABLE IF EXISTS user_role_changelog;");
        self::exec("DROP TRIGGER IF EXISTS user_role_insert_trigger;");
        self::exec("DROP TRIGGER IF EXISTS user_role_update_trigger;");
        self::exec("DROP TRIGGER IF EXISTS user_role_delete_trigger;");
    }

    function userRoleChanges($roleID, $old, $new, $timestamp, $action): string
    {
        if ($action !== 'UPDATE') {
            // For INSERT and DELETE actions, return a single row with all values
            return self::createRow($roleID, 'All Privileges', $old ?: 'N/A', $new ?: 'N/A', $timestamp, $action);
        }

        //For update action, compare old and new values and return only changed privileges
        // Column names corresponding to the order of values in the concatenated string
        $userroleColumnNames = array("Role Name", "Unique Role Name", "Lock Record", "Lock Record Multiform", "Lock Record Customize", "Data Export Instruments", "Data Import Tool", "Data Comparison Tool", "Data Logging", "Email Logging", "File Repository", "Double Data", "User Rights", "Data Access Groups", "Graphical", "Reports", "Design", "Alerts", "Calendar", "Data Entry", "API Export", "API Import", "API Modules", "Mobile App", "Mobile App Download Data", "Record Create", "Record Rename", "Record Delete", "Dts", "Participants", "Data Quality Design", "Data Quality Execute", "Data Quality Resolution", "Random Setup", "Random Dashboard", "Random Perform", "Realtime Webservice Mapping", "Realtime Webservice Adjudicate", "External Module Config", "Mycap Participants");

        $old_parts = explode("|", $old);
        $new_parts = explode("|", $new);
    
        $max = max(count($old_parts), count($new_parts));
        $row = "";
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
                            if ($oval != $nval) {           // Value differs
                                $row .= self::createRow($roleID, $userroleColumnNames[$i], "[$key,$oval]", "[$key,$nval]", $timestamp, $action);
                            }
                        }
                    }
                } else {
                    // For other privileges, show full difference
                    $row .= self::createRow($roleID, $userroleColumnNames[$i], $o, $n, $timestamp, $action);
                }
            }
        }
        return $row;
    }
    
    static function createRow($roleID, $privilege, $oldValue, $newValue, $timestamp, $action): string
    {

        return
            "<tr>
                <td>$roleID</td>
                <td>$privilege</td>
                <td>$oldValue</td>
                <td>$newValue</td>
                <td>$timestamp</td>
                <td>$action</td>
            </tr>";
    }

    function userRoleQuery($projId, $max, $hourOrDay): \mysqli_result
    {
        $query = "SELECT role_id, old_value, new_value, change_timestamp, operation_type
                  FROM user_role_changelog
                  WHERE project_id = $projId 
                  and change_timestamp >= NOW() - INTERVAL $max $hourOrDay"; // Adjust the interval as needed

        return db_query($query);
    }

    function sendEmail(): void
    {
        $projId = $this->getProjectId();
        $max_days_email = $this->getProjectSetting('max-days-email') ?? 3; // Default to 3 hours if not set
        // $query = "SELECT role_id, old_value, new_value, change_timestamp, operation_type
        //           FROM user_role_changelog
        //           WHERE project_id = $projId 
        //           and change_timestamp >= NOW() - INTERVAL $max_days_email HOUR"; // Adjust the interval as needed

        // $result = db_query($query);
        $result = $this->userRoleQuery($projId, $max_days_email, 'HOUR');

        if ($result->num_rows != 0) { // Only send email if there are changes
            
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
    
            $projectTitle = REDCap::getProjectTitle($projId);
    
            $body .= "<h3>Project Configuration Changes for Project ID: $projId - $projectTitle</h3>";
            $body .= "<h4>Changes in User Role Privileges</h4>";
            $body .= "<p><i>This log shows changes made to user role privileges.</i></p>";


            // Generate the HTML table content
            $updateTable = "<table id='user_role_change_table' border='1'>
            <thead><tr style='background-color: #FFFFE0;'>
                <th style='width: 5%;padding: 5px'>Role ID</th>
                <th style='width: 15%;padding: 5px'>Changed Privilege</th>
                <th style='width: 15%;padding: 5px'>Old Value</th>
                <th style='width: 15%;padding: 5px'>New Value</th>
                <th style='width: 15%;padding: 5px'>Timestamp</th>
                <th style='width: 15%;padding: 5px'>Action</th>
            </tr></thead><tbody>";

            while ($row = db_fetch_assoc($result)) {
                // Use the userRoleChanges function to find difference and format each row
                $updateTable .= $this->userRoleChanges($row['role_id'], $row['old_value'], $row['new_value'],
                     $row['change_timestamp'], $row['operation_type']);
            }

            $updateTable .= "</tbody></table>";
            $body .= $updateTable;
            $body .= "<br><br>Best regards,<br>Your REDCap Team";

            $email_sent = REDCap::email(
                $to,           // Recipient email address
                $from,         // Sender email address
                $subject,      // Email subject
                $body      // Email body
            );
        
            if ($email_sent) {
                echo "<br>Email sent successfully!";
            } else {
                echo "<br>Failed to send email.";
            }
        }
    }

    function projectConfigCron($cronInfo) {
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

    // function userHasViewAccess(): bool
    // {
    //     if ($this->isSuperUser()) { // Super Users can view the configuration page
    //         return true;
    //     }

    //     $viewUsers = $this->getProjectSetting('users-view-config');
    //     $user = $this->getUser();
    //     $rights = $user->getRights();

    //     // echo "<br> User: " . $user . " rights: " . $rights;
    //     foreach ($viewUsers as $viewUser) {
    //         if($rights['role_id'] == (int)$viewUsers) {
    //             // echo "<br> User has view access: " . $viewUser;
    //             // echo "<br> rights['role_id']: " . $rights['role_id'];
    //             // echo "<br> (int)viewUsers: " . (int)$viewUsers;
    //             return true;
    //         }
    //     }

    //     return false;
    // }

}