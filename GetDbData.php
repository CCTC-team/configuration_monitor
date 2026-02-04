<?php

namespace CCTC\ConfigurationMonitorModule;

class GetDbData
{
    // Validate date parameter format (YmdHis) for SQL queries
    private static function validateDateParam($date): string
    {
        if ($date === null || $date === '') {
            return 'null';
        }
        // Ensure date is numeric only (YmdHis format)
        $cleanDate = preg_replace('/[^0-9]/', '', $date);
        if (strlen($cleanDate) === 14 && ctype_digit($cleanDate)) {
            return $cleanDate;
        }
        return 'null';
    }

    static function GetDataChangesFromResult($result, $tableName) : array
    {
        $dataChanges = array();

        if ($tableName == "user-role-changes") {
            while ($row = db_fetch_assoc($result))
            {  
                $dc = [
                    "id"    => $row["role_id"],
                    "oldValue"  => $row["old_value"],
                    "newValue"  => $row["new_value"],
                    "timestamp" => $row["ts"],
                    "action"    => $row["operation_type"]
                ];

                $dataChanges[] = $dc;
            }
        } else if ($tableName == "project-changes") {
            while ($row = db_fetch_assoc($result))
            {  
                $dc = [
                    "oldValue"  => $row["old_value"],
                    "newValue"  => $row["new_value"],
                    "timestamp" => $row["ts"],
                    "action"    => "UPDATE"
                ];

                $dataChanges[] = $dc;
            }
        }
        else {
            while ($row = db_fetch_assoc($result))
            {  
                $dc = [
                    "privilege"    => $row["field_name"],
                    "oldValue"  => $row["old_value"],
                    "newValue"  => $row["new_value"],
                    "timestamp" => $row["ts"]
                ];

                $dataChanges[] = $dc;
            }
        }
       
        return $dataChanges;
    }

    // calls the GetUserRoleChanges or  GetProjectChanges stored procedures (based on tableName) with the given parameters and returns the relevant data
    public static function GetChangesFromSP(
        $projId, $minDate, $maxDate, $skipCount, $pageSize, $dataDirection, $tableName, $roleId = NULL, $fieldName = NULL)
    : array
    {

        global $module;
        global $conn;

        // Sanitize and validate all parameters to prevent SQL injection
        $projId = ($projId === null || $projId === '') ? 'null' : (int)$projId;
        $roleId = ($roleId === null || $roleId === '') ? 'null' : (int)$roleId;
        $skipCount = (int)$skipCount;
        $pageSize = (int)$pageSize;

        // Whitelist dataDirection to prevent injection
        $dataDirection = in_array(strtolower($dataDirection), ['asc', 'desc']) ? strtolower($dataDirection) : 'desc';

        // Validate date format (YmdHis) or set to null
        $minDate = self::validateDateParam($minDate);
        $maxDate = self::validateDateParam($maxDate);

        // Sanitize fieldName - allow only alphanumeric and underscores
        $fieldName = ($fieldName === null || $fieldName === '') ? 'null' : "'" . preg_replace('/[^a-zA-Z0-9_]/', '', $fieldName) . "'";

        if ($tableName == "user-role-changes") {
            $query = "call GetUserRoleChanges($projId, $minDate, $maxDate, $skipCount, $pageSize, '$dataDirection', $roleId);";

        } else if ($tableName == "project-changes") {
            $query = "call GetProjectChanges($projId, $minDate, $maxDate, $skipCount, $pageSize, '$dataDirection');";

        } else {
            $query = "call GetSystemChanges($fieldName, $minDate, $maxDate, $skipCount, $pageSize, '$dataDirection');";
        }
        
        $currentIndex = 0;
        $roleIds = array();
        $dataChanges = array();
        $totalCount = array();
        $fieldNames = array();

        if (mysqli_multi_query($conn, $query)) {

            do {
                if ($result = mysqli_store_result($conn)) {
                    if($currentIndex == 0) {
                        $dataChanges = self::GetDataChangesFromResult($result, $tableName);
                    }


                    if ($currentIndex == 1) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $totalCount = $row['total_count'];
                        }
                    }

                    if ($currentIndex == 2 && $tableName == "user-role-changes") {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $roleIds[] = $row['role_id'];
                        }
                    }

                    if ($currentIndex == 2 && $tableName == "system-changes") {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $fieldNames[] = $row['field_name'];
                        }
                    }
                    mysqli_free_result($result);
                    $currentIndex++;

                }
            } while (mysqli_next_result($conn));

        } else {
            // Log error instead of echoing to prevent information disclosure
            error_log("Configuration Monitor DB Error: " . $conn->error);
        }

        if($tableName == "user-role-changes") {
            return
            [
                "dataChanges" => $dataChanges,
                "roleIds" => $roleIds,
                "totalCount" => $totalCount
            ];
        } else if($tableName == "project-changes") {
            return
            [
                "dataChanges" => $dataChanges,
                "totalCount" => $totalCount
            ];
        } else {
            return
            [
                "dataChanges" => $dataChanges,
                "fieldNames" => $fieldNames,
                "totalCount" => $totalCount
            ];
        }
    }

}