create table IF NOT EXISTS system_changelog
(   
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	field_name VARCHAR (191) DEFAULT NULL,
	old_value TEXT DEFAULT NULL,
	new_value TEXT DEFAULT NULL,
	ts BIGINT(14) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- drop table system_changelog;
-- select * from system_changelog;