-- This script creates a trigger that logs inserts into the redcap_user_roles table into the user_role_changelog table
CREATE TRIGGER user_roles_insert_trigger
AFTER INSERT ON redcap_user_roles
FOR EACH ROW
BEGIN
	DECLARE new_values TEXT;
	DECLARE module_enabled INT DEFAULT 0;
    
    -- Check if the module is enabled for the project
    SELECT count(*) INTO module_enabled 
	FROM (SELECT em.external_module_id, emSettings.project_id FROM redcap_external_modules em
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
    WHERE emSettings2.key = 'user-role-changes-enable'
		AND emSettings2.value = 'true' ;
    
    -- Only proceed if module is enabled for the project
    IF module_enabled > 0 THEN
		-- Compute new concatenated values
		-- During insert, values are inserted and then unique_role_name is updated, so not include here as it will be empty string
		SET new_values = CONCAT_WS('/',
			COALESCE(NEW.role_name, ''), COALESCE(NEW.lock_record, ''), COALESCE(NEW.lock_record_multiform, ''), COALESCE(NEW.lock_record_customize, ''),
			COALESCE(NEW.data_export_tool, ''), COALESCE(NEW.data_export_instruments, ''), COALESCE(NEW.data_import_tool, ''), COALESCE(NEW.data_comparison_tool, ''), COALESCE(NEW.data_logging, ''),
			COALESCE(NEW.email_logging, ''), COALESCE(NEW.file_repository, ''), COALESCE(NEW.double_data, ''), COALESCE(NEW.user_rights, ''), COALESCE(NEW.data_access_groups, ''), COALESCE(NEW.graphical, ''),
			COALESCE(NEW.reports, ''), COALESCE(NEW.design, ''), COALESCE(NEW.alerts, ''), COALESCE(NEW.calendar, ''), COALESCE(NEW.data_entry, ''), COALESCE(NEW.api_export, ''), COALESCE(NEW.api_import, ''), COALESCE(NEW.api_modules, ''),
			COALESCE(NEW.mobile_app, ''), COALESCE(NEW.mobile_app_download_data, ''), COALESCE(NEW.record_create, ''), COALESCE(NEW.record_rename, ''), COALESCE(NEW.record_delete, ''),
			COALESCE(NEW.dts, ''), COALESCE(NEW.participants, ''), COALESCE(NEW.data_quality_design, ''), COALESCE(NEW.data_quality_execute, ''), COALESCE(NEW.data_quality_resolution, ''),
			COALESCE(NEW.random_setup, ''), COALESCE(NEW.random_dashboard, ''), COALESCE(NEW.random_perform, ''), COALESCE(NEW.realtime_webservice_mapping, ''),
			COALESCE(NEW.realtime_webservice_adjudicate, ''), COALESCE(NEW.external_module_config, ''), COALESCE(NEW.mycap_participants, ''), COALESCE(NEW.share, '')
		);
		INSERT INTO user_role_changelog (
			project_id, role_id, new_value, ts, operation_type
		) VALUES (
			NEW.project_id,
			NEW.role_id,
			new_values,
			NOW(),
			'INSERT'
		);
	END IF;
END;

-- drop trigger user_role_insert_trigger;