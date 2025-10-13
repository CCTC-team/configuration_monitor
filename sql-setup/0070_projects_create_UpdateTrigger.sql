delimiter $$

-- This script creates a trigger that logs updates to the redcap_user_roles table into the user_role_changelog table
CREATE TRIGGER redcap_projects_update_trigger
AFTER UPDATE ON redcap_projects
FOR EACH ROW
BEGIN
	DECLARE old_values TEXT;
	DECLARE new_values TEXT;

	-- Compute old and OLD concatenated values
	SET old_values = CONCAT_WS('|',
		OLD.project_id, OLD.project_name, OLD.app_title, OLD.status, OLD.creation_time, OLD.production_time, OLD.inactive_time, OLD.completed_time, OLD.completed_by, 
        OLD.data_locked, OLD.log_event_table, OLD.data_table, OLD.created_by, OLD.draft_mode, OLD.surveys_enabled, OLD.repeatforms, OLD.scheduling, OLD.purpose, 
        OLD.purpose_other, OLD.show_which_records, OLD.__SALT__, OLD.count_project, OLD.investigators, OLD.project_note, OLD.online_offline, OLD.auth_meth, OLD.double_data_entry, 
        OLD.project_language, OLD.project_encoding, OLD.is_child_of, OLD.date_shift_max, OLD.institution, OLD.site_org_type, OLD.grant_cite, OLD.project_contact_name, 
        OLD.project_contact_email, OLD.headerlogo, OLD.auto_inc_set, OLD.custom_data_entry_note, OLD.custom_index_page_note, OLD.order_id_by, OLD.custom_reports, OLD.report_builder, 
        OLD.disable_data_entry, OLD.google_translate_default, OLD.require_change_reason, OLD.dts_enabled, OLD.project_pi_firstname, OLD.project_pi_mi, OLD.project_pi_lastname, 
        OLD.project_pi_email, OLD.project_pi_alias, OLD.project_pi_username, OLD.project_pi_pub_exclude, OLD.project_pub_matching_institution, OLD.project_irb_number, 
        OLD.project_grant_number, OLD.history_widget_enabled, OLD.secondary_pk, OLD.secondary_pk_display_value, OLD.secondary_pk_display_label, OLD.custom_record_label, 
        OLD.display_project_logo_institution, OLD.imported_from_rs, OLD.display_today_now_button, OLD.auto_variable_naming, OLD.randomization, OLD.enable_participant_identifiers, 
        OLD.survey_email_participant_field, OLD.survey_phone_participant_field, OLD.data_entry_trigger_url, OLD.template_id, OLD.date_deleted, OLD.data_resolution_enabled, 
        OLD.field_comment_edit_delete, OLD.drw_hide_closed_queries_from_dq_results, OLD.realtime_webservice_enabled, OLD.realtime_webservice_type, OLD.realtime_webservice_offset_days, 
        OLD.realtime_webservice_offset_plusminus, OLD.last_logged_event, OLD.last_logged_event_exclude_exports, OLD.edoc_upload_max, OLD.file_attachment_upload_max, 
        OLD.survey_queue_custom_text, OLD.survey_queue_hide, OLD.survey_auth_enabled, OLD.survey_auth_field1, OLD.survey_auth_event_id1, OLD.survey_auth_field2, 
        OLD.survey_auth_event_id2, OLD.survey_auth_field3, OLD.survey_auth_event_id3, OLD.survey_auth_min_fields, OLD.survey_auth_apply_all_surveys, OLD.survey_auth_custom_message, 
        OLD.survey_auth_fail_limit, OLD.survey_auth_fail_window, OLD.twilio_enabled, OLD.twilio_modules_enabled, OLD.twilio_hide_in_project, OLD.twilio_account_sid, 
        OLD.twilio_auth_token, OLD.twilio_from_number, OLD.twilio_voice_language, OLD.twilio_option_voice_initiate, OLD.twilio_option_sms_initiate, 
        OLD.twilio_option_sms_invite_make_call, OLD.twilio_option_sms_invite_receive_call, OLD.twilio_option_sms_invite_web, OLD.twilio_default_delivery_preference, 
        OLD.twilio_request_inspector_checked, OLD.twilio_request_inspector_enabled, OLD.twilio_append_response_instructions, OLD.twilio_multiple_sms_behavior, 
        OLD.twilio_delivery_preference_field_map, OLD.mosio_api_key, OLD.mosio_hide_in_project, OLD.two_factor_exempt_project, OLD.two_factor_force_project, OLD.disable_autocalcs, 
        OLD.custom_public_survey_links, OLD.pdf_custom_header_text, OLD.pdf_show_logo_url, OLD.pdf_hide_secondary_field, OLD.pdf_hide_record_id, OLD.shared_library_enabled, 
        OLD.allow_delete_record_from_log, OLD.delete_file_repository_export_files, OLD.custom_project_footer_text, OLD.custom_project_footer_text_link, OLD.google_recaptcha_enabled, 
        OLD.datamart_allow_repeat_revision, OLD.datamart_allow_create_revision, OLD.datamart_enabled, OLD.break_the_glass_enabled, OLD.datamart_cron_enabled, OLD.datamart_cron_end_date, 
        OLD.fhir_include_email_address_project, OLD.file_upload_vault_enabled, OLD.file_upload_versioning_enabled, OLD.missing_data_codes, OLD.record_locking_pdf_vault_enabled, 
        OLD.record_locking_pdf_vault_custom_text, OLD.fhir_cdp_auto_adjudication_enabled, OLD.fhir_cdp_auto_adjudication_cronjob_enabled, OLD.project_dashboard_min_data_points, 
        OLD.bypass_branching_erase_field_prompt, OLD.protected_email_mode, OLD.protected_email_mode_custom_text, OLD.protected_email_mode_trigger, OLD.protected_email_mode_logo, 
        OLD.hide_filled_forms, OLD.hide_disabled_forms, OLD.form_activation_survey_autocontinue, OLD.sendgrid_enabled, OLD.sendgrid_project_api_key, OLD.mycap_enabled, 
        OLD.file_repository_total_size, OLD.project_db_character_set, OLD.project_db_collation, OLD.ehr_id, OLD.allow_econsent_allow_edit, 
        OLD.store_in_vault_snapshots_containing_completed_econsent
	);

	SET new_values = CONCAT_WS('|',
		NEW.project_id, NEW.project_name, NEW.app_title, NEW.status, NEW.creation_time, NEW.production_time, NEW.inactive_time, NEW.completed_time, NEW.completed_by, 
        NEW.data_locked, NEW.log_event_table, NEW.data_table, NEW.created_by, NEW.draft_mode, NEW.surveys_enabled, NEW.repeatforms, NEW.scheduling, NEW.purpose, 
        NEW.purpose_other, NEW.show_which_records, NEW.__SALT__, NEW.count_project, NEW.investigators, NEW.project_note, NEW.online_offline, NEW.auth_meth, NEW.double_data_entry, 
        NEW.project_language, NEW.project_encoding, NEW.is_child_of, NEW.date_shift_max, NEW.institution, NEW.site_org_type, NEW.grant_cite, NEW.project_contact_name, 
        NEW.project_contact_email, NEW.headerlogo, NEW.auto_inc_set, NEW.custom_data_entry_note, NEW.custom_index_page_note, NEW.order_id_by, NEW.custom_reports, NEW.report_builder, 
        NEW.disable_data_entry, NEW.google_translate_default, NEW.require_change_reason, NEW.dts_enabled, NEW.project_pi_firstname, NEW.project_pi_mi, NEW.project_pi_lastname, 
        NEW.project_pi_email, NEW.project_pi_alias, NEW.project_pi_username, NEW.project_pi_pub_exclude, NEW.project_pub_matching_institution, NEW.project_irb_number, 
        NEW.project_grant_number, NEW.history_widget_enabled, NEW.secondary_pk, NEW.secondary_pk_display_value, NEW.secondary_pk_display_label, NEW.custom_record_label, 
        NEW.display_project_logo_institution, NEW.imported_from_rs, NEW.display_today_now_button, NEW.auto_variable_naming, NEW.randomization, NEW.enable_participant_identifiers, 
        NEW.survey_email_participant_field, NEW.survey_phone_participant_field, NEW.data_entry_trigger_url, NEW.template_id, NEW.date_deleted, NEW.data_resolution_enabled, 
        NEW.field_comment_edit_delete, NEW.drw_hide_closed_queries_from_dq_results, NEW.realtime_webservice_enabled, NEW.realtime_webservice_type, NEW.realtime_webservice_offset_days, 
        NEW.realtime_webservice_offset_plusminus, NEW.last_logged_event, NEW.last_logged_event_exclude_exports, NEW.edoc_upload_max, NEW.file_attachment_upload_max, 
        NEW.survey_queue_custom_text, NEW.survey_queue_hide, NEW.survey_auth_enabled, NEW.survey_auth_field1, NEW.survey_auth_event_id1, NEW.survey_auth_field2, 
        NEW.survey_auth_event_id2, NEW.survey_auth_field3, NEW.survey_auth_event_id3, NEW.survey_auth_min_fields, NEW.survey_auth_apply_all_surveys, NEW.survey_auth_custom_message, 
        NEW.survey_auth_fail_limit, NEW.survey_auth_fail_window, NEW.twilio_enabled, NEW.twilio_modules_enabled, NEW.twilio_hide_in_project, NEW.twilio_account_sid, 
        NEW.twilio_auth_token, NEW.twilio_from_number, NEW.twilio_voice_language, NEW.twilio_option_voice_initiate, NEW.twilio_option_sms_initiate, 
        NEW.twilio_option_sms_invite_make_call, NEW.twilio_option_sms_invite_receive_call, NEW.twilio_option_sms_invite_web, NEW.twilio_default_delivery_preference, 
        NEW.twilio_request_inspector_checked, NEW.twilio_request_inspector_enabled, NEW.twilio_append_response_instructions, NEW.twilio_multiple_sms_behavior, 
        NEW.twilio_delivery_preference_field_map, NEW.mosio_api_key, NEW.mosio_hide_in_project, NEW.two_factor_exempt_project, NEW.two_factor_force_project, NEW.disable_autocalcs, 
        NEW.custom_public_survey_links, NEW.pdf_custom_header_text, NEW.pdf_show_logo_url, NEW.pdf_hide_secondary_field, NEW.pdf_hide_record_id, NEW.shared_library_enabled, 
        NEW.allow_delete_record_from_log, NEW.delete_file_repository_export_files, NEW.custom_project_footer_text, NEW.custom_project_footer_text_link, NEW.google_recaptcha_enabled, 
        NEW.datamart_allow_repeat_revision, NEW.datamart_allow_create_revision, NEW.datamart_enabled, NEW.break_the_glass_enabled, NEW.datamart_cron_enabled, NEW.datamart_cron_end_date, 
        NEW.fhir_include_email_address_project, NEW.file_upload_vault_enabled, NEW.file_upload_versioning_enabled, NEW.missing_data_codes, NEW.record_locking_pdf_vault_enabled, 
        NEW.record_locking_pdf_vault_custom_text, NEW.fhir_cdp_auto_adjudication_enabled, NEW.fhir_cdp_auto_adjudication_cronjob_enabled, NEW.project_dashboard_min_data_points, 
        NEW.bypass_branching_erase_field_prompt, NEW.protected_email_mode, NEW.protected_email_mode_custom_text, NEW.protected_email_mode_trigger, NEW.protected_email_mode_logo, 
        NEW.hide_filled_forms, NEW.hide_disabled_forms, NEW.form_activation_survey_autocontinue, NEW.sendgrid_enabled, NEW.sendgrid_project_api_key, NEW.mycap_enabled, 
        NEW.file_repository_total_size, NEW.project_db_character_set, NEW.project_db_collation, NEW.ehr_id, NEW.allow_econsent_allow_edit, 
        NEW.store_in_vault_snapshots_containing_completed_econsent
	);

	-- Only insert if old and OLD values are different
    
    -- CHECK SIMIALR
	-- Have to check if unique_role_name is the same to avoid logging changes when role is inserted.
	-- Insert is treated as insert defualt values and then update with actual values.
	IF ((old_values <> new_values) AND (OLD.project_id = NEW.project_id)) THEN
		INSERT INTO user_role_changelog (
			project_id, old_value, OLD_value, ts, operation_type
		) VALUES (
			COALESCE(NEW.project_id, OLD.project_id),
			old_values,
			new_values,
			NOW(),
			'UPDATE'
		);
	END IF;


END$$
-- drop trigger redcap_projects_update_trigger;