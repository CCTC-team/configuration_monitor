-- DELIMITER $$

-- This script creates a trigger that logs updates to the redcap_projects table into the project_changelog table
CREATE TRIGGER projects_update_trigger
AFTER UPDATE ON redcap_projects
FOR EACH ROW
BEGIN
	DECLARE old_values TEXT;
	DECLARE new_values TEXT;
    DECLARE module_enabled INT DEFAULT 0;

    -- Check if the module is enabled for the project
   SELECT count(*) INTO module_enabled FROM
	    (SELECT em.external_module_id, emSettings.project_id FROM redcap_external_modules em
        INNER JOIN redcap_external_module_settings emSettings
            ON em.external_module_id = emSettings.external_module_id
            AND emSettings.project_id = NEW.project_id
        WHERE em.directory_prefix = 'configuration_monitor'
            AND emSettings.key = 'enabled'
            AND emSettings.value = 'true'
        ) as a
    INNER JOIN redcap_external_module_settings emSettings2
        ON a.external_module_id = emSettings2.external_module_id
        AND a.project_id = emSettings2.project_id
    WHERE emSettings2.key = 'project-changes-enable'
		AND emSettings2.value = 'true' ;

    -- Only proceed if module is enabled for the project
    IF module_enabled > 0 THEN
	    -- Compute old and OLD concatenated values
        SET old_values = CONCAT_WS('|',
            COALESCE(OLD.project_name, ''), COALESCE(OLD.app_title, ''), COALESCE(OLD.status, ''), COALESCE(OLD.inactive_time, ''), COALESCE(OLD.completed_time, ''), COALESCE(OLD.completed_by, ''), 
            COALESCE(OLD.data_locked, ''), COALESCE(OLD.draft_mode, ''), COALESCE(OLD.surveys_enabled, ''), COALESCE(OLD.repeatforms, ''), COALESCE(OLD.scheduling, ''), COALESCE(OLD.purpose, ''), 
            COALESCE(OLD.purpose_other, ''), COALESCE(OLD.show_which_records, ''), COALESCE(OLD.count_project, ''), COALESCE(OLD.investigators, ''), COALESCE(OLD.project_note, ''), COALESCE(OLD.online_offline, ''), COALESCE(OLD.auth_meth, ''), COALESCE(OLD.double_data_entry, ''), 
            COALESCE(OLD.project_language, ''), COALESCE(OLD.project_encoding, ''), COALESCE(OLD.is_child_of, ''), COALESCE(OLD.date_shift_max, ''), COALESCE(OLD.institution, ''), COALESCE(OLD.site_org_type, ''), COALESCE(OLD.grant_cite, ''), COALESCE(OLD.project_contact_name, ''), 
            COALESCE(OLD.project_contact_email, ''), COALESCE(OLD.headerlogo, ''), COALESCE(OLD.auto_inc_set, ''), COALESCE(OLD.custom_data_entry_note, ''), COALESCE(OLD.custom_index_page_note, ''), COALESCE(OLD.order_id_by, ''), COALESCE(OLD.custom_reports, ''), COALESCE(OLD.report_builder, ''), 
            COALESCE(OLD.disable_data_entry, ''), COALESCE(OLD.google_translate_default, ''), COALESCE(OLD.require_change_reason, ''), COALESCE(OLD.dts_enabled, ''), COALESCE(OLD.project_pi_firstname, ''), COALESCE(OLD.project_pi_mi, ''), COALESCE(OLD.project_pi_lastname, ''), 
            COALESCE(OLD.project_pi_email, ''), COALESCE(OLD.project_pi_alias, ''), COALESCE(OLD.project_pi_username, ''), COALESCE(OLD.project_pi_pub_exclude, ''), COALESCE(OLD.project_pub_matching_institution, ''), COALESCE(OLD.project_irb_number, ''), 
            COALESCE(OLD.project_grant_number, ''), COALESCE(OLD.history_widget_enabled, ''), COALESCE(OLD.secondary_pk, ''), COALESCE(OLD.secondary_pk_display_value, ''), COALESCE(OLD.secondary_pk_display_label, ''), COALESCE(OLD.custom_record_label, ''), 
            COALESCE(OLD.display_project_logo_institution, ''), COALESCE(OLD.imported_from_rs, ''), COALESCE(OLD.display_today_now_button, ''), COALESCE(OLD.auto_variable_naming, ''), COALESCE(OLD.randomization, ''), COALESCE(OLD.enable_participant_identifiers, ''), 
            COALESCE(OLD.survey_email_participant_field, ''), COALESCE(OLD.survey_phone_participant_field, ''), COALESCE(OLD.data_entry_trigger_url, ''), COALESCE(OLD.template_id, ''), COALESCE(OLD.date_deleted, ''), COALESCE(OLD.data_resolution_enabled, ''), 
            COALESCE(OLD.field_comment_edit_delete, ''), COALESCE(OLD.drw_hide_closed_queries_from_dq_results, ''), COALESCE(OLD.realtime_webservice_enabled, ''), COALESCE(OLD.realtime_webservice_type, ''), COALESCE(OLD.realtime_webservice_offset_days, ''), 
            COALESCE(OLD.realtime_webservice_offset_plusminus, ''), COALESCE(OLD.edoc_upload_max, ''), COALESCE(OLD.file_attachment_upload_max, ''), 
            COALESCE(OLD.survey_queue_custom_text, ''), COALESCE(OLD.survey_queue_hide, ''), COALESCE(OLD.survey_auth_enabled, ''), COALESCE(OLD.survey_auth_field1, ''), COALESCE(OLD.survey_auth_event_id1, ''), COALESCE(OLD.survey_auth_field2, ''), 
            COALESCE(OLD.survey_auth_event_id2, ''), COALESCE(OLD.survey_auth_field3, ''), COALESCE(OLD.survey_auth_event_id3, ''), COALESCE(OLD.survey_auth_min_fields, ''), COALESCE(OLD.survey_auth_apply_all_surveys, ''), COALESCE(OLD.survey_auth_custom_message, ''), 
            COALESCE(OLD.survey_auth_fail_limit, ''), COALESCE(OLD.survey_auth_fail_window, ''), COALESCE(OLD.twilio_enabled, ''), COALESCE(OLD.twilio_modules_enabled, ''), COALESCE(OLD.twilio_hide_in_project, ''), COALESCE(OLD.twilio_account_sid, ''), 
            COALESCE(OLD.twilio_auth_token, ''), COALESCE(OLD.twilio_from_number, ''), COALESCE(OLD.twilio_voice_language, ''), COALESCE(OLD.twilio_option_voice_initiate, ''), COALESCE(OLD.twilio_option_sms_initiate, ''), 
            COALESCE(OLD.twilio_option_sms_invite_make_call, ''), COALESCE(OLD.twilio_option_sms_invite_receive_call, ''), COALESCE(OLD.twilio_option_sms_invite_web, ''), COALESCE(OLD.twilio_default_delivery_preference, ''), 
            COALESCE(OLD.twilio_request_inspector_checked, ''), COALESCE(OLD.twilio_request_inspector_enabled, ''), COALESCE(OLD.twilio_append_response_instructions, ''), COALESCE(OLD.twilio_multiple_sms_behavior, ''), 
            COALESCE(OLD.twilio_delivery_preference_field_map, ''), COALESCE(OLD.mosio_api_key, ''), COALESCE(OLD.mosio_hide_in_project, ''), COALESCE(OLD.two_factor_exempt_project, ''), COALESCE(OLD.two_factor_force_project, ''), COALESCE(OLD.disable_autocalcs, ''), 
            COALESCE(OLD.custom_public_survey_links, ''), COALESCE(OLD.pdf_custom_header_text, ''), COALESCE(OLD.pdf_show_logo_url, ''), COALESCE(OLD.pdf_hide_secondary_field, ''), COALESCE(OLD.pdf_hide_record_id, ''), COALESCE(OLD.shared_library_enabled, ''), 
            COALESCE(OLD.allow_delete_record_from_log, ''), COALESCE(OLD.delete_file_repository_export_files, ''), COALESCE(OLD.custom_project_footer_text, ''), COALESCE(OLD.custom_project_footer_text_link, ''), COALESCE(OLD.google_recaptcha_enabled, ''), 
            COALESCE(OLD.datamart_allow_repeat_revision, ''), COALESCE(OLD.datamart_allow_create_revision, ''), COALESCE(OLD.datamart_enabled, ''), COALESCE(OLD.break_the_glass_enabled, ''), COALESCE(OLD.datamart_cron_enabled, ''), COALESCE(OLD.datamart_cron_end_date, ''), 
            COALESCE(OLD.fhir_include_email_address_project, ''), COALESCE(OLD.file_upload_vault_enabled, ''), COALESCE(OLD.file_upload_versioning_enabled, ''), COALESCE(OLD.missing_data_codes, ''), COALESCE(OLD.record_locking_pdf_vault_enabled, ''), 
            COALESCE(OLD.record_locking_pdf_vault_custom_text, ''), COALESCE(OLD.fhir_cdp_auto_adjudication_enabled, ''), COALESCE(OLD.fhir_cdp_auto_adjudication_cronjob_enabled, ''), COALESCE(OLD.project_dashboard_min_data_points, ''), 
            COALESCE(OLD.bypass_branching_erase_field_prompt, ''), COALESCE(OLD.protected_email_mode, ''), COALESCE(OLD.protected_email_mode_custom_text, ''), COALESCE(OLD.protected_email_mode_trigger, ''), COALESCE(OLD.protected_email_mode_logo, ''), 
            COALESCE(OLD.hide_filled_forms, ''), COALESCE(OLD.hide_disabled_forms, ''), COALESCE(OLD.form_activation_survey_autocontinue, ''), COALESCE(OLD.sendgrid_enabled, ''), COALESCE(OLD.sendgrid_project_api_key, ''), COALESCE(OLD.mycap_enabled, ''), 
            COALESCE(OLD.file_repository_total_size, ''), COALESCE(OLD.ehr_id, ''), COALESCE(OLD.allow_econsent_allow_edit, ''), COALESCE(OLD.store_in_vault_snapshots_containing_completed_econsent, '')
        );

        SET new_values = CONCAT_WS('|',
            COALESCE(NEW.project_name, ''), COALESCE(NEW.app_title, ''), COALESCE(NEW.status, ''), COALESCE(NEW.inactive_time, ''), COALESCE(NEW.completed_time, ''), COALESCE(NEW.completed_by, ''), 
            COALESCE(NEW.data_locked, ''), COALESCE(NEW.draft_mode, ''), COALESCE(NEW.surveys_enabled, ''), COALESCE(NEW.repeatforms, ''), COALESCE(NEW.scheduling, ''), COALESCE(NEW.purpose, ''), 
            COALESCE(NEW.purpose_other, ''), COALESCE(NEW.show_which_records, ''), COALESCE(NEW.count_project, ''), COALESCE(NEW.investigators, ''), COALESCE(NEW.project_note, ''), COALESCE(NEW.online_offline, ''), COALESCE(NEW.auth_meth, ''), COALESCE(NEW.double_data_entry, ''), 
            COALESCE(NEW.project_language, ''), COALESCE(NEW.project_encoding, ''), COALESCE(NEW.is_child_of, ''), COALESCE(NEW.date_shift_max, ''), COALESCE(NEW.institution, ''), COALESCE(NEW.site_org_type, ''), COALESCE(NEW.grant_cite, ''), COALESCE(NEW.project_contact_name, ''), 
            COALESCE(NEW.project_contact_email, ''), COALESCE(NEW.headerlogo, ''), COALESCE(NEW.auto_inc_set, ''), COALESCE(NEW.custom_data_entry_note, ''), COALESCE(NEW.custom_index_page_note, ''), COALESCE(NEW.order_id_by, ''), COALESCE(NEW.custom_reports, ''), COALESCE(NEW.report_builder, ''), 
            COALESCE(NEW.disable_data_entry, ''), COALESCE(NEW.google_translate_default, ''), COALESCE(NEW.require_change_reason, ''), COALESCE(NEW.dts_enabled, ''), COALESCE(NEW.project_pi_firstname, ''), COALESCE(NEW.project_pi_mi, ''), COALESCE(NEW.project_pi_lastname, ''), 
            COALESCE(NEW.project_pi_email, ''), COALESCE(NEW.project_pi_alias, ''), COALESCE(NEW.project_pi_username, ''), COALESCE(NEW.project_pi_pub_exclude, ''), COALESCE(NEW.project_pub_matching_institution, ''), COALESCE(NEW.project_irb_number, ''), 
            COALESCE(NEW.project_grant_number, ''), COALESCE(NEW.history_widget_enabled, ''), COALESCE(NEW.secondary_pk, ''), COALESCE(NEW.secondary_pk_display_value, ''), COALESCE(NEW.secondary_pk_display_label, ''), COALESCE(NEW.custom_record_label, ''), 
            COALESCE(NEW.display_project_logo_institution, ''), COALESCE(NEW.imported_from_rs, ''), COALESCE(NEW.display_today_now_button, ''), COALESCE(NEW.auto_variable_naming, ''), COALESCE(NEW.randomization, ''), COALESCE(NEW.enable_participant_identifiers, ''), 
            COALESCE(NEW.survey_email_participant_field, ''), COALESCE(NEW.survey_phone_participant_field, ''), COALESCE(NEW.data_entry_trigger_url, ''), COALESCE(NEW.template_id, ''), COALESCE(NEW.date_deleted, ''), COALESCE(NEW.data_resolution_enabled, ''), 
            COALESCE(NEW.field_comment_edit_delete, ''), COALESCE(NEW.drw_hide_closed_queries_from_dq_results, ''), COALESCE(NEW.realtime_webservice_enabled, ''), COALESCE(NEW.realtime_webservice_type, ''), COALESCE(NEW.realtime_webservice_offset_days, ''), 
            COALESCE(NEW.realtime_webservice_offset_plusminus, ''), COALESCE(NEW.edoc_upload_max, ''), COALESCE(NEW.file_attachment_upload_max, ''), 
            COALESCE(NEW.survey_queue_custom_text, ''), COALESCE(NEW.survey_queue_hide, ''), COALESCE(NEW.survey_auth_enabled, ''), COALESCE(NEW.survey_auth_field1, ''), COALESCE(NEW.survey_auth_event_id1, ''), COALESCE(NEW.survey_auth_field2, ''), 
            COALESCE(NEW.survey_auth_event_id2, ''), COALESCE(NEW.survey_auth_field3, ''), COALESCE(NEW.survey_auth_event_id3, ''), COALESCE(NEW.survey_auth_min_fields, ''), COALESCE(NEW.survey_auth_apply_all_surveys, ''), COALESCE(NEW.survey_auth_custom_message, ''), 
            COALESCE(NEW.survey_auth_fail_limit, ''), COALESCE(NEW.survey_auth_fail_window, ''), COALESCE(NEW.twilio_enabled, ''), COALESCE(NEW.twilio_modules_enabled, ''), COALESCE(NEW.twilio_hide_in_project, ''), COALESCE(NEW.twilio_account_sid, ''), 
            COALESCE(NEW.twilio_auth_token, ''), COALESCE(NEW.twilio_from_number, ''), COALESCE(NEW.twilio_voice_language, ''), COALESCE(NEW.twilio_option_voice_initiate, ''), COALESCE(NEW.twilio_option_sms_initiate, ''), 
            COALESCE(NEW.twilio_option_sms_invite_make_call, ''), COALESCE(NEW.twilio_option_sms_invite_receive_call, ''), COALESCE(NEW.twilio_option_sms_invite_web, ''), COALESCE(NEW.twilio_default_delivery_preference, ''), 
            COALESCE(NEW.twilio_request_inspector_checked, ''), COALESCE(NEW.twilio_request_inspector_enabled, ''), COALESCE(NEW.twilio_append_response_instructions, ''), COALESCE(NEW.twilio_multiple_sms_behavior, ''), 
            COALESCE(NEW.twilio_delivery_preference_field_map, ''), COALESCE(NEW.mosio_api_key, ''), COALESCE(NEW.mosio_hide_in_project, ''), COALESCE(NEW.two_factor_exempt_project, ''), COALESCE(NEW.two_factor_force_project, ''), COALESCE(NEW.disable_autocalcs, ''), 
            COALESCE(NEW.custom_public_survey_links, ''), COALESCE(NEW.pdf_custom_header_text, ''), COALESCE(NEW.pdf_show_logo_url, ''), COALESCE(NEW.pdf_hide_secondary_field, ''), COALESCE(NEW.pdf_hide_record_id, ''), COALESCE(NEW.shared_library_enabled, ''), 
            COALESCE(NEW.allow_delete_record_from_log, ''), COALESCE(NEW.delete_file_repository_export_files, ''), COALESCE(NEW.custom_project_footer_text, ''), COALESCE(NEW.custom_project_footer_text_link, ''), COALESCE(NEW.google_recaptcha_enabled, ''), 
            COALESCE(NEW.datamart_allow_repeat_revision, ''), COALESCE(NEW.datamart_allow_create_revision, ''), COALESCE(NEW.datamart_enabled, ''), COALESCE(NEW.break_the_glass_enabled, ''), COALESCE(NEW.datamart_cron_enabled, ''), COALESCE(NEW.datamart_cron_end_date, ''), 
            COALESCE(NEW.fhir_include_email_address_project, ''), COALESCE(NEW.file_upload_vault_enabled, ''), COALESCE(NEW.file_upload_versioning_enabled, ''), COALESCE(NEW.missing_data_codes, ''), COALESCE(NEW.record_locking_pdf_vault_enabled, ''), 
            COALESCE(NEW.record_locking_pdf_vault_custom_text, ''), COALESCE(NEW.fhir_cdp_auto_adjudication_enabled, ''), COALESCE(NEW.fhir_cdp_auto_adjudication_cronjob_enabled, ''), COALESCE(NEW.project_dashboard_min_data_points, ''), 
            COALESCE(NEW.bypass_branching_erase_field_prompt, ''), COALESCE(NEW.protected_email_mode, ''), COALESCE(NEW.protected_email_mode_custom_text, ''), COALESCE(NEW.protected_email_mode_trigger, ''), COALESCE(NEW.protected_email_mode_logo, ''), 
            COALESCE(NEW.hide_filled_forms, ''), COALESCE(NEW.hide_disabled_forms, ''), COALESCE(NEW.form_activation_survey_autocontinue, ''), COALESCE(NEW.sendgrid_enabled, ''), COALESCE(NEW.sendgrid_project_api_key, ''), COALESCE(NEW.mycap_enabled, ''), 
            COALESCE(NEW.file_repository_total_size, ''), COALESCE(NEW.ehr_id, ''), COALESCE(NEW.allow_econsent_allow_edit, ''), COALESCE(NEW.store_in_vault_snapshots_containing_completed_econsent, '')
        );

        -- Only insert if old and OLD values are different
        IF ((old_values <> new_values)) THEN
            INSERT INTO project_changelog (
                project_id, old_value, new_value, ts
            ) VALUES (
                NEW.project_id,
                old_values,
                new_values,
                NOW()
            );
        END IF;
    END IF;
END;

-- END$$

-- drop trigger projects_update_trigger;
