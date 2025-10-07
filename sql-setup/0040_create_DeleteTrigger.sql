-- This script creates a trigger that logs deletions from the redcap_user_roles table into the user_role_changelog table
CREATE TRIGGER user_role_delete_trigger
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
		project_id, role_id, old_value, ts, operation_type
	) VALUES (
		OLD.project_id,
		OLD.role_id,
		old_values,
		NOW(),
		'DELETE'
	);
END;