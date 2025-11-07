-- This script creates a trigger that logs deletions from the redcap_user_roles table into the user_role_changelog table
CREATE TRIGGER user_roles_delete_trigger
AFTER DELETE ON redcap_user_roles
FOR EACH ROW
BEGIN
	DECLARE old_values TEXT;
	DECLARE module_enabled INT DEFAULT 0;
    
    -- Check if the module is enabled for the project
    SELECT count(*) INTO module_enabled 
	FROM (SELECT em.external_module_id, emSettings.project_id FROM redcap_external_modules em
        INNER JOIN redcap_external_module_settings emSettings
            ON em.external_module_id = emSettings.external_module_id
            AND emSettings.project_id = OLD.project_id
        WHERE em.directory_prefix = 'configuration_monitor'
            AND emSettings.key = 'enabled'
            AND emSettings.value = 'true'
        ) as a
    INNER JOIN redcap_external_module_settings emSettings2
        ON a.external_module_id = emSettings2.external_module_id
        AND a.project_id = emSettings2.project_id
    WHERE emSettings2.key = 'user-role-changes-enable'
		AND emSettings2.value = 'true' ;
    
    -- Only proceed if module is enabled for the project
    IF module_enabled > 0 THEN
		-- Compute old concatenated values
		SET old_values = CONCAT_WS('/',
			OLD.role_name, OLD.lock_record, OLD.lock_record_multiform, OLD.lock_record_customize,
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
	END IF;
END;

-- drop trigger user_role_delete_trigger;