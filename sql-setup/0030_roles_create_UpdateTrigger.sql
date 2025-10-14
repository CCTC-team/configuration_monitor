-- This script creates a trigger that logs updates to the redcap_user_roles table into the user_role_changelog table
CREATE TRIGGER user_role_update_trigger
AFTER UPDATE ON redcap_user_roles
FOR EACH ROW
BEGIN
	DECLARE old_values TEXT;
	DECLARE new_values TEXT;

	-- Compute old and new concatenated values
	-- During insert, values are inserted and then unique_role_name is updated.
	-- So not including unique_role_name here else an update record will be created during insert.
	SET old_values = CONCAT_WS('|',
		OLD.role_name, OLD.lock_record, OLD.lock_record_multiform, OLD.lock_record_customize,
		OLD.data_export_tool, OLD.data_export_instruments, OLD.data_import_tool, OLD.data_comparison_tool, OLD.data_logging,
		OLD.email_logging, OLD.file_repository, OLD.double_data, OLD.user_rights, OLD.data_access_groups, OLD.graphical,
		OLD.reports, OLD.design, OLD.alerts, OLD.calendar, OLD.data_entry, OLD.api_export, OLD.api_import, OLD.api_modules,
		OLD.mobile_app, OLD.mobile_app_download_data, OLD.record_create, OLD.record_rename, OLD.record_delete,
		OLD.dts, OLD.participants, OLD.data_quality_design, OLD.data_quality_execute, OLD.data_quality_resolution,
		OLD.random_setup, OLD.random_dashboard, OLD.random_perform, OLD.realtime_webservice_mapping,
		OLD.realtime_webservice_adjudicate, OLD.external_module_config, OLD.mycap_participants
	);

	SET new_values = CONCAT_WS('|',
		NEW.role_name, NEW.lock_record, NEW.lock_record_multiform, NEW.lock_record_customize,
		NEW.data_export_tool, NEW.data_export_instruments, NEW.data_import_tool, NEW.data_comparison_tool, NEW.data_logging,
		NEW.email_logging, NEW.file_repository, NEW.double_data, NEW.user_rights, NEW.data_access_groups, NEW.graphical,
		NEW.reports, NEW.design, NEW.alerts, NEW.calendar, NEW.data_entry, NEW.api_export, NEW.api_import, NEW.api_modules,
		NEW.mobile_app, NEW.mobile_app_download_data, NEW.record_create, NEW.record_rename, NEW.record_delete,
		NEW.dts, NEW.participants, NEW.data_quality_design, NEW.data_quality_execute, NEW.data_quality_resolution,
		NEW.random_setup, NEW.random_dashboard, NEW.random_perform, NEW.realtime_webservice_mapping,
		NEW.realtime_webservice_adjudicate, NEW.external_module_config, NEW.mycap_participants
	);

	-- Only insert if old and new values are different.
	IF ((old_values <> new_values)) THEN
		INSERT INTO user_role_changelog (
			project_id, role_id, old_value, new_value, ts, operation_type
		) VALUES (
			COALESCE(NEW.project_id, OLD.project_id),
			COALESCE(NEW.role_id, OLD.role_id),
			old_values,
			new_values,
			NOW(),
			'UPDATE'
		);
	END IF;
END;

-- drop trigger user_role_update_trigger;