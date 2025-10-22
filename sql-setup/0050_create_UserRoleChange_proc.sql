/*
    A procedure that provides paged data for viewing the User Role Change queries for a project
*/

CREATE PROCEDURE GetUserRoleChanges
    (
        in projectId int,
        in minDate bigint,
        in maxDate bigint,
        in skipCount int,
        in pageSize int,
        in retDirection varchar(4) collate utf8mb4_unicode_ci,
        in roleId int
    )
BEGIN

    DECLARE sqlQuery mediumtext;
    DECLARE roleidfilter mediumtext;

   -- if skip not given then default to 0
    if skipCount is null then
        set skipCount = 0;
    end if;

    -- if pageSize not given then default to 50
    if pageSize is null then
        set pageSize = 50;
    end if;

    -- direction of returned results by ts - either 'asc' or 'desc'
    if retDirection is null or retDirection = '' then
        set retDirection = 'desc';
    end if;
 
    -- create the temporary table for the final results
    drop table if exists user_role_change_temp;
    create temporary table user_role_change_temp
    (   
        id mediumint not null auto_increment primary key,
        role_id INT(10) DEFAULT NULL,
        old_value TEXT DEFAULT NULL,
        new_value TEXT DEFAULT NULL,
        ts BIGINT(14) DEFAULT NULL,
        operation_type VARCHAR(100) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    SET sqlQuery =
         concat('INSERT INTO user_role_change_temp
                    ( role_id, old_value, new_value, ts, operation_type)
                SELECT role_id, old_value, new_value, ts, operation_type
                    FROM user_role_changelog
                    WHERE project_id = ',  projectId,
                    ' -- role_id filter
					and (? is null or role_id = ?) 
					-- minDate
                    and (? is null or ts >= ?)
                    -- maxDate
                    and (? is null or ts <= ?)');

    prepare qry FROM sqlQuery;
    EXECUTE qry using 
        roleId, roleId,
        minDate, minDate,
        maxDate, maxDate;
    DEALLOCATE prepare qry;

    SET sqlQuery =
         concat('SELECT *
                FROM user_role_change_temp order by ts ', retDirection, ' LIMIT ', pageSize, ' OFFSET ', skipCount, ';');

    prepare qry FROM sqlQuery;
    EXECUTE qry;
    DEALLOCATE prepare qry;

    -- return total count
    select count(*) as total_count from user_role_change_temp;

    -- return distinct role ids in the result
    SELECT DISTINCT role_id from user_role_change_temp ORDER BY role_id;

END;

-- call GetUserRoleChanges(13, 3, 'DAY', NULL, NULL, NULL, NULL); -- all roles
-- call GetUserRoleChanges(13, 3, 'DAY', NULL, NULL, NULL, 24);