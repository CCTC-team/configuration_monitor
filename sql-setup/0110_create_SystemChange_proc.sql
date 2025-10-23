/*
    A procedure that provides paged data for viewing the System Change queries
*/
-- DELIMITER $$

CREATE PROCEDURE GetSystemChanges
    (
        in fieldName varchar(191) collate utf8mb4_unicode_ci,
        in minDate bigint,
        in maxDate bigint,
        in skipCount int,
        in pageSize int,
        in retDirection varchar(4) collate utf8mb4_unicode_ci
    )
BEGIN

    DECLARE sqlQuery mediumtext;

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

    -- Replace string 'null' with actual null
    if fieldName = 'null' then
        set fieldName = null;
    end if;

    -- create the temporary table for the final results
    drop table if exists system_changes_temp;
    create temporary table system_changes_temp
    (   
        id mediumint not null auto_increment primary key,
        field_name VARCHAR (191) DEFAULT NULL,
        old_value TEXT DEFAULT NULL,
        new_value TEXT DEFAULT NULL,
        ts BIGINT(14) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    SET sqlQuery =
         concat('INSERT INTO system_changes_temp
                    ( field_name, old_value, new_value, ts)
                SELECT field_name, old_value, new_value, ts
                    FROM system_changelog
                    -- field_name filter
					WHERE (? is null or field_name = ?)
                    -- minDate
                   and (? is null or ts >= ?)
                    -- maxDate
                   and (? is null or ts <= ?)');

    prepare qry FROM sqlQuery;
    EXECUTE qry using
       fieldName, fieldName,
       minDate, minDate,
       maxDate, maxDate;
    DEALLOCATE prepare qry;

    SET sqlQuery =
         concat('SELECT *
                FROM system_changes_temp order by ts ', retDirection, ' LIMIT ', pageSize, ' OFFSET ', skipCount, ';');

    prepare qry FROM sqlQuery;
    EXECUTE qry;
    DEALLOCATE prepare qry;

    -- return total count
    select count(*) as total_count from system_changes_temp;

    -- return distinct field_names in the result
    SELECT DISTINCT field_name from system_changes_temp ORDER BY field_name;

END;
-- END$$

-- DROP PROCEDURE IF EXISTS GetSystemChanges;
-- call GetSystemChanges(13, 3, 'DAY', NULL, NULL, NULL);
-- call GetSystemChanges(13, 3, 'DAY', NULL, NULL, NULL);