<!-- <?php

namespace CCTC\ProjectConfigurationChangesModule;

use ExternalModules\AbstractExternalModule;

use REDCap;

class ProjectConfigurationChangesModule extends AbstractExternalModule {

  
    function projectConfig() {
        $old_value = "role_name = 'TrialAdmin', design = 1, alerts = 0, user_rights = 0, data_access_groups = 1, reports = 0, graphical = 0, participants = 1, calendar = 1, data_import_tool = 0, data_comparison_tool = 0, data_logging = 1, file_repository = 0, data_quality_design = 0, data_quality_execute = 1, api_export = 0, api_import = 0, api_modules = 0, mobile_app = 0, mobile_app_download_data = 0, record_create = 1, record_rename = 0, record_delete = 0, lock_record_customize = 0, lock_record = 1, lock_record_multiform = 1,data_entry = '[patient_details_and_consent,1][eligibility_criteria,1][confirmation_of_eligibility,1][patient_registration,1][gp_letter,1][baseline_visit_assessment,1][disease_and_transplant_details,1][research_blood_samples,1][routine_blood_test_results,1][followup_assessment,1][cmv_disease,1][reactivation_ie_detectable_viraemia_10e4iuml_on_2,1][cmv_treatment_details_antivirals,1][letermovir_treatment,1][gvh_disease_status,1][immunosuppressant_medication,1][details_of_reconsent,1][consent_withdrawal,1][end_of_study,1][pi_declaration,1]',data_export_instruments = '[patient_details_and_consent,0][eligibility_criteria,0][confirmation_of_eligibility,0][patient_registration,0][gp_letter,0][baseline_visit_assessment,0][disease_and_transplant_details,0][research_blood_samples,0][routine_blood_test_results,0][followup_assessment,0][cmv_disease,0][reactivation_ie_detectable_viraemia_10e4iuml_on_2,0][cmv_treatment_details_antivirals,0][letermovir_treatment,0][gvh_disease_status,0][immunosuppressant_medication,0][details_of_reconsent,0][consent_withdrawal,0][end_of_study,0][pi_declaration,0]' ";
        $new_value = "role_name = 'TrialAdmin', design = 0, alerts = 0, user_rights = 0, data_access_groups = 1, reports = 0, graphical = 0, participants = 1, calendar = 1, data_import_tool = 0, data_comparison_tool = 0, data_logging = 1, file_repository = 0, data_quality_design = 0, data_quality_execute = 1, api_export = 0, api_import = 0, api_modules = 0, mobile_app = 0, mobile_app_download_data = 0, record_create = 1, record_rename = 0, record_delete = 0, lock_record_customize = 0, lock_record = 1, lock_record_multiform = 1,data_entry = '[patient_details_and_consent,1][eligibility_criteria,1][confirmation_of_eligibility,1][patient_registration,1][gp_letter,1][baseline_visit_assessment,1][disease_and_transplant_details,1][research_blood_samples,1][routine_blood_test_results,1][followup_assessment,1][cmv_disease,1][reactivation_ie_detectable_viraemia_10e4iuml_on_2,1][cmv_treatment_details_antivirals,1][letermovir_treatment,1][gvh_disease_status,1][immunosuppressant_medication,1][details_of_reconsent,1][consent_withdrawal,1][end_of_study,1][pi_declaration,1]',data_export_instruments = '[patient_details_and_consent,0][eligibility_criteria,0][confirmation_of_eligibility,0][patient_registration,0][gp_letter,0][baseline_visit_assessment,0][disease_and_transplant_details,0][research_blood_samples,0][routine_blood_test_results,0][followup_assessment,0][cmv_disease,0][reactivation_ie_detectable_viraemia_10e4iuml_on_2,0][cmv_treatment_details_antivirals,0][letermovir_treatment,0][gvh_disease_status,0][immunosuppressant_medication,0][details_of_reconsent,0][consent_withdrawal,0][end_of_study,0][pi_declaration,0]' ";
        
        function showDifferences($old, $new) {
            $result = [];

            // Match "key = value" pairs
            preg_match_all('/\s*,?\s*([^=]+?)\s*=\s*([\s\S]*?)(?=,\s*\w+\s*=|$)/', $old, $matches, PREG_SET_ORDER);
            
            foreach ($matches as $match) {
                $key = trim($match[1]);
                $val = trim($match[2]);

                if (in_array($match[1], ['data_entry', 'data_export_instruments'])) {

                    preg_match_all('/\[([a-zA-Z0-9_]+),([0-9]+)\]/', $match[2], $nmatches, PREG_SET_ORDER);

                    $subArray = [];
                    foreach ($nmatches as $nmatch) {
                        $keySub = $nmatch[1];   // text before comma
                        $valSub = $nmatch[2];   // number after comma
                        $subArray[$keySub] = $valSub;
                    }
                    $result[$key] = $subArray;

                }
                
                else
                    $result[$key] = $val;
            }
            
            // Print the associative array
            // print_array($result);        
            $max = max(count($old_parts), count($new_parts));
        
            for ($i = 0; $i < $max; $i++) {
                $o = $old_parts[$i] ?? '';
                $n = $new_parts[$i] ?? '';
                $id = 1; // to create table row ids
                
                if ($o !== $n) {
                    if ($i == 5 || $i == 19) {
                        preg_match_all('/\[([a-zA-Z0-9_]+),([0-9]+)\]/', $n, $nmatches, PREG_SET_ORDER);
                        preg_match_all('/\[([a-zA-Z0-9_]+),([0-9]+)\]/', $o, $omatches, PREG_SET_ORDER);

                        $nresult = [];
                        foreach ($nmatches as $nmatch) {
                            $key = $nmatch[1];   // text before comma
                            $val = $nmatch[2];   // number after comma
                            $nresult[$key] = $val;
                        }

                        $oresult = [];
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
