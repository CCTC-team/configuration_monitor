<?php

namespace CCTC\ProjectConfigurationChangesModule;

class GetDbData
{

    static function GetDataChangesFromResult($result, $tableName) : array
    {
        $dataChanges = array();

        if($tableName == "redcap_user_roles") {
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
        } else {
            while ($row = db_fetch_assoc($result))
            {  
                $dc = [
                    "oldValue"  => $row["old_value"],
                    "newValue"  => $row["new_value"],
                    "timestamp" => $row["ts"],
                    "action"    => $row["operation_type"]
                ];

                $dataChanges[] = $dc;
            }
        }
       
        // print_r($dataChanges);
        return $dataChanges;
    }

    // calls the GetUserRoleChanges or  GetProjectChanges stored procedures (based on tableName) with the given parameters and returns the relevant data
    public static function GetChangesFromSP(
        $projId, $minDate, $maxDate, $skipCount, $pageSize, $dataDirection, $tableName, $roleId = NULL)
    : array
    {

        global $module;
        global $conn;
        $roleId = $roleId == null ? "null" : $roleId;
        $minDate = $minDate == null ? "null" : $minDate;
        $maxDate = $maxDate == null ? "null" : $maxDate;

        if($tableName == "redcap_user_roles") {
            $query = "call GetUserRoleChanges($projId, $minDate, $maxDate, $skipCount, $pageSize, '$dataDirection', $roleId);";
        } else {
            $query = "call GetProjectChanges($projId, $minDate, $maxDate, $skipCount, $pageSize, '$dataDirection');";
        }
        
        $currentIndex = 0;
        $roleIds = array();
        $dataChanges = array();

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

                    if ($currentIndex == 2 && $tableName == "redcap_user_roles") {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $roleIds[] = $row['role_id'];
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

        if($tableName == "redcap_user_roles") {
            return
            [
                "dataChanges" => $dataChanges,
                "roleIds" => $roleIds,
                "totalCount" => $totalCount
            ];
        } else
            return
                [
                    "dataChanges" => $dataChanges,
                    "totalCount" => $totalCount
                ];
    }

}