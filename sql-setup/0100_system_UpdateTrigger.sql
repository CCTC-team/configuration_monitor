-- DELIMITER $$

-- This script creates a trigger that logs updates to the redcap_config table into the system_changelog table
CREATE TRIGGER system_update_trigger
AFTER UPDATE ON redcap_config
FOR EACH ROW
BEGIN
	DECLARE old_value TEXT;
	DECLARE new_value TEXT;
    
	-- Compute old and OLD concatenated values
    SET old_value = COALESCE(OLD.value, '');

    SET new_value = COALESCE(NEW.value, '');

    -- Only insert if old and OLD values are different
    IF ((NEW.field_name != 'temp_files_last_delete' and NEW.field_name != 'report_stats_url') AND (old_value <> new_value)) THEN
        INSERT INTO system_changelog (
            field_name, old_value, new_value, ts
        ) VALUES (
            NEW.field_name,
            old_value,
            new_value,
            NOW()
        );
    END IF;
END;

-- END$$

-- drop trigger system_update_trigger;
