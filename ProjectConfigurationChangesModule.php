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
        //User Role Change Log Table, Triggers and Stored Procedure
        self::execFromFile("0010_create_table_user_role_changelog.sql");
        self::execFromFile("0020_roles_create_InsertTrigger.sql");
        self::execFromFile("0030_roles_create_UpdateTrigger.sql");
        self::execFromFile("0040_roles_create_DeleteTrigger.sql");
        self::execFromFile("0050_create_UserRoleChange_proc.sql");

        //Project Change Log Table, Triggers and Stored Procedure
        self::execFromFile("0060_create_table_project_changelog.sql");
        self::execFromFile("0070_projects_create_InsertTrigger.sql");
        self::execFromFile("0080_projects_create_UpdateTrigger.sql");
        self::execFromFile("0090_projects_create_DeleteTrigger.sql");
        self::execFromFile("0100_create_ProjectChange_proc.sql");
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

        // Drop the Project Change Log table, triggers, and stored procedure
        // Uncomment the line below if you want to drop the table when the module is disabled.
        // Be cautious as this will delete all logged data.
        // db_query("DROP TABLE IF EXISTS project_changelog;");
        db_query("DROP TRIGGER IF EXISTS projects_update_trigger;");
        db_query("DROP PROCEDURE IF EXISTS GetProjectChanges;");
    }

    function tableDiff($dc, $tableName): array
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
            $projectColumnNames = array("project_name", "app_title", "status", "inactive_time", "completed_time", "completed_by", "data_locked", "draft_mode", "surveys_enabled", 
                                    "repeatforms", "scheduling", "purpose", "purpose_other", "show_which_records", "count_project", "investigators", "project_note", "online_offline", 
                                    "auth_meth", "double_data_entry", "project_language", "project_encoding", "is_child_of", "date_shift_max", "institution", "site_org_type", 
                                    "grant_cite", "project_contact_name", "project_contact_email", "headerlogo", "auto_inc_set", "custom_data_entry_note", "custom_index_page_note", 
                                    "order_id_by", "custom_reports", "report_builder", "disable_data_entry", "google_translate_default", "require_change_reason", "dts_enabled", "project_pi_firstname", "project_pi_mi", "project_pi_lastname", 
                                    "project_pi_email", "project_pi_alias", "project_pi_username", "project_pi_pub_exclude", "project_pub_matching_institution", "project_irb_number", 
                                    "project_grant_number", "history_widget_enabled", "secondary_pk", "secondary_pk_display_value", "secondary_pk_display_label", "custom_record_label", 
                                    "display_project_logo_institution", "imported_from_rs", "display_today_now_button", "auto_variable_naming", "randomization", "enable_participant_identifiers", 
                                    "survey_email_participant_field", "survey_phone_participant_field", "data_entry_trigger_url", "template_id", "date_deleted", "data_resolution_enabled", 
                                    "field_comment_edit_delete", "drw_hide_closed_queries_from_dq_results", "realtime_webservice_enabled", "realtime_webservice_type", "realtime_webservice_offset_days", 
                                    "realtime_webservice_offset_plusminus", "edoc_upload_max", "file_attachment_upload_max", "survey_queue_custom_text", "survey_queue_hide", "survey_auth_enabled", 
                                    "survey_auth_field1", "survey_auth_event_id1", "survey_auth_field2", "survey_auth_event_id2", "survey_auth_field3", "survey_auth_event_id3", "survey_auth_min_fields", 
                                    "survey_auth_apply_all_surveys", "survey_auth_custom_message", 
                                    "survey_auth_fail_limit", "survey_auth_fail_window", "twilio_enabled", "twilio_modules_enabled", "twilio_hide_in_project", "twilio_account_sid", 
                                    "twilio_auth_token", "twilio_from_number", "twilio_voice_language", "twilio_option_voice_initiate", "twilio_option_sms_initiate", 
                                    "twilio_option_sms_invite_make_call", "twilio_option_sms_invite_receive_call", "twilio_option_sms_invite_web", "twilio_default_delivery_preference", 
                                    "twilio_request_inspector_checked", "twilio_request_inspector_enabled", "twilio_append_response_instructions", "twilio_multiple_sms_behavior", 
                                    "twilio_delivery_preference_field_map", "mosio_api_key", "mosio_hide_in_project", "two_factor_exempt_project", "two_factor_force_project", "disable_autocalcs", 
                                    "custom_public_survey_links", "pdf_custom_header_text", "pdf_show_logo_url", "pdf_hide_secondary_field", "pdf_hide_record_id", "shared_library_enabled", 
                                    "allow_delete_record_from_log", "delete_file_repository_export_files", "custom_project_footer_text", "custom_project_footer_text_link", "google_recaptcha_enabled", 
                                    "datamart_allow_repeat_revision", "datamart_allow_create_revision", "datamart_enabled", "break_the_glass_enabled", "datamart_cron_enabled", "datamart_cron_end_date", 
                                    "fhir_include_email_address_project", "file_upload_vault_enabled", "file_upload_versioning_enabled", "missing_data_codes", "record_locking_pdf_vault_enabled", 
                                    "record_locking_pdf_vault_custom_text", "fhir_cdp_auto_adjudication_enabled", "fhir_cdp_auto_adjudication_cronjob_enabled", "project_dashboard_min_data_points", 
                                    "bypass_branching_erase_field_prompt", "protected_email_mode", "protected_email_mode_custom_text", "protected_email_mode_trigger", "protected_email_mode_logo", 
                                    "hide_filled_forms", "hide_disabled_forms", "form_activation_survey_autocontinue", "sendgrid_enabled", "sendgrid_project_api_key", "mycap_enabled", 
                                    "file_repository_total_size", "ehr_id", "allow_econsent_allow_edit", "store_in_vault_snapshots_containing_completed_econsent");
            
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
                <th style='width: 15%;padding: 5px'>Action</th>
                <th style='width: 15%;padding: 5px'>Changed Property</th>
                <th style='width: 15%;padding: 5px'>Old Value</th>
                <th style='width: 15%;padding: 5px'>New Value</th>
            </tr></thead><tbody>";
        }
        foreach($dcs as $dc) {

            $date = DateTime::createFromFormat('YmdHis', $dc["timestamp"]);
            $formattedDate = $date->format($userDateFormat);
            $dc["timestamp"] = $formattedDate;
            $changes = self::tableDiff($dc, $tableName);
            $table .= self::createRow($changes, $tableName);
        }

        return $table .= "</tbody></table>";
    }

    function createRow($changes, $tableName): string
    {
        $span = count($changes);
        $row = "<tr>";

        if ($tableName == "user_role_changes")
            $row .= "<td rowspan='$span'>" . $changes[0]['id'] . "</td>";
               
 
        $row .= "<td rowspan='$span'>" . $changes[0]['timestamp'] . "</td>
                <td rowspan='$span'>" . $changes[0]['action'] . "</td>";        

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

        //run the stored proc
        $logDataSetsUserRoles = GetDbData::GetChangesFromSP($projId, $minDateDb, $maxDateDb, 0, 25, "desc", "user_role_changes", $roleID);
        $logDataSetsProj = GetDbData::GetChangesFromSP($projId, $minDateDb, $maxDateDb, 0, 25, "desc", "project_changes");

        $dcs1 = $logDataSetsUserRoles['dataChanges'];
        $showingCount1 = count($dcs1);

        $dcs2 = $logDataSetsProj['dataChanges'];
        $showingCount2 = count($dcs2);

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