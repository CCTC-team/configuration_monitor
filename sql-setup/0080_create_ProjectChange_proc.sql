/*
    A procedure that provides paged data for viewing the Project Change queries for a project
*/

CREATE PROCEDURE GetProjectChanges
    (
        in projectId int,
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
 
    -- create the temporary table for the final results
    drop table if exists project_change_temp;
    create temporary table project_change_temp
    (   
        id mediumint not null auto_increment primary key,
        project_id INT(10) DEFAULT NULL,
        old_value TEXT DEFAULT NULL,
        new_value TEXT DEFAULT NULL,
        ts BIGINT(14) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    SET sqlQuery =
         concat('INSERT INTO project_change_temp
                    ( project_id, old_value, new_value, ts)
                SELECT project_id, old_value, new_value, ts
                    FROM project_changelog
                    WHERE project_id = ',  projectId,
                    ' -- minDate
                    and (? is null or ts >= ?)
                    -- maxDate
                    and (? is null or ts <= ?)');

    prepare qry FROM sqlQuery;
    EXECUTE qry using 
        minDate,minDate,
        maxDate,maxDate;
    DEALLOCATE prepare qry;

    SET sqlQuery =
         concat('SELECT *
                FROM project_change_temp order by ts ', retDirection, ' LIMIT ', pageSize, ' OFFSET ', skipCount, ';');

    prepare qry FROM sqlQuery;
    EXECUTE qry;
    DEALLOCATE prepare qry;

    -- return total count
    select count(*) as total_count from project_change_temp;

END;

-- call GetProjectChanges(13, 3, 'DAY', NULL, NULL, NULL); 
-- call GetProjectChanges(13, 3, 'DAY', NULL, NULL, NULL);