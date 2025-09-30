<!-- <?php

namespace CCTC\ProjectConfigurationChangesModule;

use ExternalModules\AbstractExternalModule;

use REDCap;

class ProjectConfigurationChangesModule extends AbstractExternalModule {

    // public function createCustomTable() {
    //     $sql = "CREATE TABLE IF NOT EXISTS my_custom_table (
    //                 id INT AUTO_INCREMENT PRIMARY KEY,
    //                 project_id INT NOT NULL,
    //                 record_id INT NOT NULL,
    //                 field_name VARCHAR(100),
    //                 old_value TEXT,
    //                 new_value TEXT,
    //                 timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    //             ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    //     $this->query($sql);
    // }

    // function exec($query): void
    // {
    //     db_query($query);
    // }

    // function execFromFile($file): void
    // {
    //     $sql = file_get_contents(dirname(__FILE__) . "/sql-setup/$file");
    //     db_query($sql);
    // }

    // function redcap_module_system_enable($version): void
    // {
    //     //just creates the required trigger and necessary tables
    //     // self::execFromFile("0010_create_redcap_user_roles_trigger.sql");
    //     // $this->createCustomTable();

       
    // }


    // function redcap_module_system_disable($version): void
    // {
    //     //just drops the sql stored proc required for the module to work
    //     self::exec("DROP TRIGGER IF EXISTS project_changelog_update_trigger;");
    //     self::exec("DROP TABLE IF EXISTS project_changelog;");

    // }
    function projectConfig() {
        $old_value = "DataEntry|U-672XRX3MXN|0|0|0|[patient_details_and_consent,0][eligibility_criteria,2][confirmation_of_eligibility,0][patient_registration,0][gp_letter,0][baseline_visit_assessment,0][disease_and_transplant_details,0][research_blood_samples,0][routine_blood_test_results,0][followup_assessment,0][cmv_disease,0][reactivation_ie_detectable_viraemia_10e4iuml_on_2,0][cmv_treatment_details_antivirals,0][letermovir_treatment,0][gvh_disease_status,0][immunosuppressant_medication,0][details_of_reconsent,0][consent_withdrawal,0][end_of_study,0][pi_declaration,0]|0|0|0|0|0|0|1|0|0|0|1|0|0|[patient_details_and_consent,1][eligibility_criteria,1][confirmation_of_eligibility,1][patient_registration,1][gp_letter,1][baseline_visit_assessment,1][disease_and_transplant_details,1][research_blood_samples,1][routine_blood_test_results,1][followup_assessment,1][cmv_disease,1][reactivation_ie_detectable_viraemia_10e4iuml_on_2,1][cmv_treatment_details_antivirals,1][letermovir_treatment,1][gvh_disease_status,1][immunosuppressant_medication,1][details_of_reconsent,1][consent_withdrawal,1][end_of_study,1][pi_declaration,2]|0|0|0|0|0|1|0|0|0|1|0|1|2|0|0|0|0|0|1";
        $new_value = "DataEntry|U-672XRX3MXN|0|0|0|[patient_details_and_consent,0][eligibility_criteria,2][confirmation_of_eligibility,0][patient_registration,0][gp_letter,0][baseline_visit_assessment,0][disease_and_transplant_details,0][research_blood_samples,0][routine_blood_test_results,0][followup_assessment,0][cmv_disease,0][reactivation_ie_detectable_viraemia_10e4iuml_on_2,0][cmv_treatment_details_antivirals,0][letermovir_treatment,0][gvh_disease_status,0][immunosuppressant_medication,0][details_of_reconsent,0][consent_withdrawal,0][end_of_study,0][pi_declaration,0]|0|0|0|0|0|0|1|0|0|0|0|0|0|[patient_details_and_consent,1][eligibility_criteria,1][confirmation_of_eligibility,1][patient_registration,1][gp_letter,1][baseline_visit_assessment,1][disease_and_transplant_details,1][research_blood_samples,1][routine_blood_test_results,1][followup_assessment,1][cmv_disease,1][reactivation_ie_detectable_viraemia_10e4iuml_on_2,1][cmv_treatment_details_antivirals,1][letermovir_treatment,1][gvh_disease_status,1][immunosuppressant_medication,1][details_of_reconsent,1][consent_withdrawal,1][end_of_study,1][pi_declaration,2]|0|0|0|0|0|1|0|0|0|1|0|1|2|0|0|0|0|0|1";
        function showDifferences($old, $new) {
            $userroleColumnNames = array("role_name", "unique_role_name", "lock_record", "lock_record_multiform", "lock_record_customize", "data_export_instruments", "data_import_tool", "data_comparison_tool", "data_logging", "email_logging", "file_repository", "double_data", "user_rights", "data_access_groups", "graphical", "reports", "design", "alerts", "calendar", "data_entry", "api_export", "api_import", "api_modules", "mobile_app", "mobile_app_download_data", "record_create", "record_rename", "record_delete", "dts", "participants", "data_quality_design", "data_quality_execute", "data_quality_resolution", "random_setup", "random_dashboard", "random_perform", "realtime_webservice_mapping", "realtime_webservice_adjudicate", "external_module_config", "mycap_participants");

            $old_parts = explode("|", $old);
            $new_parts = explode("|", $new);
        
            $max = max(count($old_parts), count($new_parts));
        
            for ($i = 0; $i < $max; $i++) {
                $o = $old_parts[$i] ?? '';
                $n = $new_parts[$i] ?? '';
                $id = 1; // to create table row ids
                
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
                                    echo "Difference in " . $userroleColumnNames[$i] . ":<br>";
                                    echo "   Old: [$key,$oval]<br>";
                                    echo "   New: [$key,$nval]<br><br>";
                                }
                            }
                        }
                    } else {
                        // For other columns, show full difference
                        echo "Difference in " . $userroleColumnNames[$i] . ":<br>";
                        echo "   Old: $o<br>";
                        echo "   New: $n<br><br>";
                    }
                }
            }
        }
        
        showDifferences($old_value, $new_value);
        
    }

} -->
