create table project_changelog
(   
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	project_id INT(10) DEFAULT NULL,
	old_value TEXT DEFAULT NULL,
	new_value TEXT DEFAULT NULL,
	ts BIGINT(14) DEFAULT NULL,
	operation_type VARCHAR(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- drop table project_changelog;
-- select * from project_changelog;