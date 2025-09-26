DROP TRIGGER IF EXISTS user_role_insert_trigger;

DELIMITER $$

CREATE TRIGGER user_role_insert_trigger
        AFTER INSERT ON redcap_user_roles
        FOR EACH ROW
        BEGIN

			INSERT INTO user_role_changelog (
				project_id, role_id, new_value, operation_type
			) VALUES (
				NEW.project_id,
				NEW.role_id,
				NEW.role_name,
				'INSERT'
			);
        END$$
	DELIMITER ;

select * from user_role_changelog;

