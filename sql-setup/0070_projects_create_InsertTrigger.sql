-- -- DELIMITER $$

-- -- This script creates a trigger that logs inserts into the redcap_projects table into the project_changelog table
-- CREATE TRIGGER projects_insert_trigger
-- AFTER INSERT ON redcap_projects
-- FOR EACH ROW
-- BEGIN
-- 	DECLARE new_values TEXT;

-- 	-- Compute new concatenated values
    
-- 	-- CHECK SIMILAR - __SALT__ unique_role_name is updated later after insert, so not include here as it will be empty string
-- 	SET new_values = CONCAT_WS('|',
-- 		NEW.project_name, NEW.app_title, NEW.status, NEW.inactive_time, NEW.completed_time, NEW.completed_by, 
--         NEW.data_locked, NEW.log_event_table, NEW.data_table, NEW.created_by, NEW.draft_mode, NEW.surveys_enabled, NEW.repeatforms, NEW.scheduling, NEW.purpose, 
--         NEW.purpose_other, NEW.show_which_records, NEW.__SALT__, NEW.count_project, NEW.investigators, NEW.project_note, NEW.online_offline, NEW.auth_meth, NEW.double_data_entry, 
--         NEW.project_language, NEW.project_encoding, NEW.is_child_of, NEW.date_shift_max, NEW.institution, NEW.site_org_type, NEW.grant_cite, NEW.project_contact_name, 
--         NEW.project_contact_email, NEW.headerlogo, NEW.auto_inc_set, NEW.custom_data_entry_note, NEW.custom_index_page_note, NEW.order_id_by, NEW.custom_reports, NEW.report_builder, 
--         NEW.disable_data_entry, NEW.google_translate_default, NEW.require_change_reason, NEW.dts_enabled, NEW.project_pi_firstname, NEW.project_pi_mi, NEW.project_pi_lastname, 
--         NEW.project_pi_email, NEW.project_pi_alias, NEW.project_pi_username, NEW.project_pi_pub_exclude, NEW.project_pub_matching_institution, NEW.project_irb_number, 
--         NEW.project_grant_number, NEW.history_widget_enabled, NEW.secondary_pk, NEW.secondary_pk_display_value, NEW.secondary_pk_display_label, NEW.custom_record_label, 
--         NEW.display_project_logo_institution, NEW.imported_from_rs, NEW.display_today_now_button, NEW.auto_variable_naming, NEW.randomization, NEW.enable_participant_identifiers, 
--         NEW.survey_email_participant_field, NEW.survey_phone_participant_field, NEW.data_entry_trigger_url, NEW.template_id, NEW.date_deleted, NEW.data_resolution_enabled, 
--         NEW.field_comment_edit_delete, NEW.drw_hide_closed_queries_from_dq_results, NEW.realtime_webservice_enabled, NEW.realtime_webservice_type, NEW.realtime_webservice_offset_days, 
--         NEW.realtime_webservice_offset_plusminus, NEW.last_logged_event, NEW.last_logged_event_exclude_exports, NEW.edoc_upload_max, NEW.file_attachment_upload_max, 
--         NEW.survey_queue_custom_text, NEW.survey_queue_hide, NEW.survey_auth_enabled, NEW.survey_auth_field1, NEW.survey_auth_event_id1, NEW.survey_auth_field2, 
--         NEW.survey_auth_event_id2, NEW.survey_auth_field3, NEW.survey_auth_event_id3, NEW.survey_auth_min_fields, NEW.survey_auth_apply_all_surveys, NEW.survey_auth_custom_message, 
--         NEW.survey_auth_fail_limit, NEW.survey_auth_fail_window, NEW.twilio_enabled, NEW.twilio_modules_enabled, NEW.twilio_hide_in_project, NEW.twilio_account_sid, 
--         NEW.twilio_auth_token, NEW.twilio_from_number, NEW.twilio_voice_language, NEW.twilio_option_voice_initiate, NEW.twilio_option_sms_initiate, 
--         NEW.twilio_option_sms_invite_make_call, NEW.twilio_option_sms_invite_receive_call, NEW.twilio_option_sms_invite_web, NEW.twilio_default_delivery_preference, 
--         NEW.twilio_request_inspector_checked, NEW.twilio_request_inspector_enabled, NEW.twilio_append_response_instructions, NEW.twilio_multiple_sms_behavior, 
--         NEW.twilio_delivery_preference_field_map, NEW.mosio_api_key, NEW.mosio_hide_in_project, NEW.two_factor_exempt_project, NEW.two_factor_force_project, NEW.disable_autocalcs, 
--         NEW.custom_public_survey_links, NEW.pdf_custom_header_text, NEW.pdf_show_logo_url, NEW.pdf_hide_secondary_field, NEW.pdf_hide_record_id, NEW.shared_library_enabled, 
--         NEW.allow_delete_record_from_log, NEW.delete_file_repository_export_files, NEW.custom_project_footer_text, NEW.custom_project_footer_text_link, NEW.google_recaptcha_enabled, 
--         NEW.datamart_allow_repeat_revision, NEW.datamart_allow_create_revision, NEW.datamart_enabled, NEW.break_the_glass_enabled, NEW.datamart_cron_enabled, NEW.datamart_cron_end_date, 
--         NEW.fhir_include_email_address_project, NEW.file_upload_vault_enabled, NEW.file_upload_versioning_enabled, NEW.missing_data_codes, NEW.record_locking_pdf_vault_enabled, 
--         NEW.record_locking_pdf_vault_custom_text, NEW.fhir_cdp_auto_adjudication_enabled, NEW.fhir_cdp_auto_adjudication_cronjob_enabled, NEW.project_dashboard_min_data_points, 
--         NEW.bypass_branching_erase_field_prompt, NEW.protected_email_mode, NEW.protected_email_mode_custom_text, NEW.protected_email_mode_trigger, NEW.protected_email_mode_logo, 
--         NEW.hide_filled_forms, NEW.hide_disabled_forms, NEW.form_activation_survey_autocontinue, NEW.sendgrid_enabled, NEW.sendgrid_project_api_key, NEW.mycap_enabled, 
--         NEW.file_repository_total_size, NEW.project_db_character_set, NEW.project_db_collation, NEW.ehr_id, NEW.allow_econsent_allow_edit, 
--         NEW.store_in_vault_snapshots_containing_completed_econsent
-- 	);
-- 	INSERT INTO project_changelog (
-- 		project_id, new_value, ts, operation_type
-- 	) VALUES (
-- 		NEW.project_id,
-- 		new_values,
-- 		NOW(),
-- 		'INSERT'
-- 	);
    
-- END;

-- -- END$$

-- -- drop trigger projects_insert_trigger;
