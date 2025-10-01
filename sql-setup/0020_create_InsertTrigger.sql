-- This script creates a trigger that logs inserts into the redcap_user_roles table into the user_role_changelog table
CREATE TRIGGER user_role_insert_trigger
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
END;