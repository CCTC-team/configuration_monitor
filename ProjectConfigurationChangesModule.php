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

function createTrigger() 
    {
        $trigger = "CREATE TRIGGER user_role_changelog_update_trigger
        AFTER UPDATE ON redcap_user_roles
        FOR EACH ROW
        BEGIN
                -- Compute old and new concatenated values
            DECLARE old_values TEXT;
            DECLARE new_values TEXT;

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

            -- Only insert if old and new are different
            IF (old_values <> new_values) THEN
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
    
    function redcap_module_system_enable($version): void
    {
        self::createTable();
        self::createTrigger();
    } 

    function redcap_module_system_disable($version): void
    {
        // Uncomment the line below if you want to drop the table when the module is disabled.
        // Be cautious as this will delete all logged data.
        // self::exec("DROP TABLE IF EXISTS user_role_changelog;");
        self::exec("DROP TRIGGER IF EXISTS user_role_changelog_update_trigger;");
    }

    function showDifferences($roleID, $old, $new, $timestamp): string
    {
        $userroleColumnNames = array("Role Name", "Unique Role Name", "Lock Record", "Lock Record Multiform", "Lock Record Customize", "Data Export Instruments", "Data Import Tool", "Data Comparison Tool", "Data Logging", "Email Logging", "File Repository", "Double Data", "User Rights", "Data Access Groups", "Graphical", "Reports", "Design", "Alerts", "Calendar", "Data Entry", "API Export", "API Import", "API Modules", "Mobile App", "Mobile App Download Data", "Record Create", "Record Rename", "Record Delete", "Dts", "Participants", "Data Quality Design", "Data Quality Execute", "Data Quality Resolution", "Random Setup", "Random Dashboard", "Random Perform", "Realtime Webservice Mapping", "Realtime Webservice Adjudicate", "External Module Config", "Mycap Participants");

        $old_parts = explode("|", $old);
        $new_parts = explode("|", $new);
    
        $max = max(count($old_parts), count($new_parts));
        $row = "";
        for ($i = 0; $i < $max; $i++) {
            $o = $old_parts[$i] ?? '';
            $n = $new_parts[$i] ?? '';
            
            if ($o !== $n) {
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
                                // echo "Difference in Privilege - " . $userroleColumnNames[$i] . ":<br>";
                                // echo "   Old Value: [$key,$oval]<br>";
                                // echo "   New Value: [$key,$nval]<br><br>";
                                $row .= self::createRow($roleID, $userroleColumnNames[$i], "[$key,$oval]", "[$key,$nval]", $timestamp);
                            }
                        }
                    }
                } else {
                    // // For other columns, show full difference
                    // echo "Difference in  Privilege - " . $userroleColumnNames[$i] . ":<br>";
                    // echo "   Old Value: $o<br>";
                    // echo "   New Value: $n<br><br>";
                    $row .= self::createRow($roleID, $userroleColumnNames[$i], $o, $n, $timestamp);
                }
            }
        }
        return $row;
    }
    
    static function createRow($roleID, $privilege, $oldValue, $newValue, $timestamp): string
    {

        return
            "<tr>
                <td>$roleID</td>
                <td>$privilege</td>
                <td>$oldValue</td>
                <td>$newValue</td>
                <td>$timestamp</td>
            </tr>";
    }
  
    function projectConfig($roleID, $oldValue, $newValue, $timestamp): string
    {
        // $oldValue = "DataEntry|U-672XRX3MXN|0|0|0|[patient_details_and_consent,1][eligibility_criteria,2][confirmation_of_eligibility,0][patient_registration,0][gp_letter,0][baseline_visit_assessment,0][disease_and_transplant_details,0][research_blood_samples,0][routine_blood_test_results,0][followup_assessment,0][cmv_disease,0][reactivation_ie_detectable_viraemia_10e4iuml_on_2,0][cmv_treatment_details_antivirals,0][letermovir_treatment,0][gvh_disease_status,0][immunosuppressant_medication,0][details_of_reconsent,0][consent_withdrawal,0][end_of_study,0][pi_declaration,0]|0|0|0|0|0|0|1|0|0|0|1|0|0|[patient_details_and_consent,1][eligibility_criteria,1][confirmation_of_eligibility,1][patient_registration,1][gp_letter,1][baseline_visit_assessment,1][disease_and_transplant_details,1][research_blood_samples,1][routine_blood_test_results,1][followup_assessment,1][cmv_disease,1][reactivation_ie_detectable_viraemia_10e4iuml_on_2,1][cmv_treatment_details_antivirals,1][letermovir_treatment,1][gvh_disease_status,1][immunosuppressant_medication,1][details_of_reconsent,1][consent_withdrawal,1][end_of_study,1][pi_declaration,2]|0|0|0|0|0|1|0|0|0|1|0|1|2|0|0|0|0|0|1";
        // $newValue = "DataEntry|U-672XRX3MXN|0|0|0|[patient_details_and_consent,0][eligibility_criteria,2][confirmation_of_eligibility,0][patient_registration,0][gp_letter,0][baseline_visit_assessment,0][disease_and_transplant_details,0][research_blood_samples,0][routine_blood_test_results,0][followup_assessment,0][cmv_disease,0][reactivation_ie_detectable_viraemia_10e4iuml_on_2,0][cmv_treatment_details_antivirals,0][letermovir_treatment,0][gvh_disease_status,0][immunosuppressant_medication,0][details_of_reconsent,0][consent_withdrawal,0][end_of_study,0][pi_declaration,0]|0|0|0|0|0|0|1|0|0|0|0|0|0|[patient_details_and_consent,1][eligibility_criteria,0][confirmation_of_eligibility,1][patient_registration,1][gp_letter,1][baseline_visit_assessment,1][disease_and_transplant_details,1][research_blood_samples,1][routine_blood_test_results,1][followup_assessment,1][cmv_disease,1][reactivation_ie_detectable_viraemia_10e4iuml_on_2,1][cmv_treatment_details_antivirals,1][letermovir_treatment,1][gvh_disease_status,1][immunosuppressant_medication,1][details_of_reconsent,1][consent_withdrawal,1][end_of_study,1][pi_declaration,2]|0|0|0|0|0|1|0|0|0|1|0|1|2|0|0|0|0|0|1";
        
        $rows = self::showDifferences($roleID, $oldValue, $newValue, $timestamp);
       return $rows;
    }

}