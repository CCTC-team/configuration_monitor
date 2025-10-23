<?php

namespace CCTC\ProjectConfigurationChangesModule;

class GetDbData
{

    static function GetDataChangesFromResult($result, $tableName) : array
    {
        $dataChanges = array();

        if ($tableName == "user_role_changes") {
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
        } else if ($tableName == "project_changes") {
            while ($row = db_fetch_assoc($result))
            {  
                $dc = [
                    "oldValue"  => $row["old_value"],
                    "newValue"  => $row["new_value"],
                    "timestamp" => $row["ts"],
                    //delete column from table ???
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
                    //delete column from table ???
                ];

                $dataChanges[] = $dc;
            }
        }
       
        // print_r($dataChanges);
        return $dataChanges;
    }

    // calls the GetUserRoleChanges or  GetProjectChanges stored procedures (based on tableName) with the given parameters and returns the relevant data
    public static function GetChangesFromSP(
        $projId, $minDate, $maxDate, $skipCount, $pageSize, $dataDirection, $tableName, $roleId = NULL, $fieldName = NULL)
    : array
    {

        global $module;
        global $conn;
        $roleId = $roleId == null ? "null" : $roleId;
        $minDate = $minDate == null ? "null" : $minDate;
        $maxDate = $maxDate == null ? "null" : $maxDate;
        $fieldName = $fieldName == null ? "null" : $fieldName;

        if ($tableName == "user_role_changes") {
            $query = "call GetUserRoleChanges($projId, $minDate, $maxDate, $skipCount, $pageSize, '$dataDirection', $roleId);";

        } else if ($tableName == "project_changes") {
            $query = "call GetProjectChanges($projId, $minDate, $maxDate, $skipCount, $pageSize, '$dataDirection');";

        } else {
            $query = "call GetSystemChanges('$fieldName', $minDate, $maxDate, $skipCount, $pageSize, '$dataDirection');";
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

                    if ($currentIndex == 2 && $tableName == "user_role_changes") {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $roleIds[] = $row['role_id'];
                        }
                    }

                    if ($currentIndex == 2 && $tableName == "system_changes") {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $fieldNames[] = $row['field_name'];
                        }
                    }
                    mysqli_free_result($result);
                    $currentIndex++;

                }
            } while (mysqli_next_result($conn));

            $updateTable .= "</tbody></table>";

        } else {
            echo "Error: " . $conn->error;
        }

        if($tableName == "user_role_changes") {
            return
            [
                "dataChanges" => $dataChanges,
                "roleIds" => $roleIds,
                "totalCount" => $totalCount
            ];
        } else if($tableName == "project_changes") {
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