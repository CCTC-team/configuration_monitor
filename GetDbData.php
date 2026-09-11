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

    // Normalise a user-supplied action filter to one of ACTION_TYPES, or null for "any action"
    public static function NormaliseActionType($actionType): ?string
    {
        if ($actionType === null || $actionType === '') {
            return null;
        }
        $actionType = strtoupper(trim((string)$actionType));

        return in_array($actionType, self::ACTION_TYPES, true) ? $actionType : null;
    }

    // Turn an action filter into a SQL literal for the stored procedure call
    private static function validateActionType($actionType): string
    {
        $actionType = self::NormaliseActionType($actionType);

        return $actionType === null ? 'null' : "'" . $actionType . "'";
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

    // The operation types recorded by the user_roles insert/update/delete triggers
    const ACTION_TYPES = ['INSERT', 'UPDATE', 'DELETE'];

    // calls the GetUserRoleChanges or  GetProjectChanges stored procedures (based on tableName) with the given parameters and returns the relevant data
    public static function GetChangesFromSP(
        $projId, $minDate, $maxDate, $skipCount, $pageSize, $dataDirection, $tableName, $roleId = NULL, $fieldName = NULL, $actionType = NULL)
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

        // Whitelist actionType against the operation types the triggers record
        $actionType = self::validateActionType($actionType);

        if ($tableName == "user-role-changes") {
            $query = "call GetUserRoleChanges($projId, $minDate, $maxDate, $skipCount, $pageSize, '$dataDirection', $roleId, $actionType);";

        } else if ($tableName == "project-changes") {
            $query = "call GetProjectChanges($projId, $minDate, $maxDate, $skipCount, $pageSize, '$dataDirection');";

        } else {
            $query = "call GetSystemChanges($fieldName, $minDate, $maxDate, $skipCount, $pageSize, '$dataDirection');";
        }
        
        $currentIndex = 0;
        $roleIds = array();
        $roleNames = array();
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
                            $roleNames[$row['role_id']] = $row['role_name'];
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
                "roleNames" => $roleNames,
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