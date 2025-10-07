<?php

namespace CCTC\ProjectConfigurationChangesModule;

class GetDbData
{

    static function GetDataChangesFromResult($result) : array
    {
        $dataChanges = array();

        while ($row = db_fetch_assoc($result))
        {  
            $dc = [
                "roleID"    => $row["role_id"],
                "oldValue"  => $row["old_value"],
                "newValue"  => $row["new_value"],
                "timestamp" => $row["ts"],
                "action"    => $row["operation_type"]
            ];

            $dataChanges[] = $dc;
            // print_r($dc);
        }

        return $dataChanges;
    }

    // calls the GetUserRoleChanges stored procedure with the given parameters and returns the relevant data
    public static function GetUserRoleChangesFromSP(
        $projId, $minDate, $maxDate, $skipCount, $pageSize, $dataDirection, $roleId)
    : array
    {

        global $module;
        global $conn;
        $roleId = $roleId == null ? "null" : $roleId;
        $minDate = $minDate == null ? "null" : $minDate;
        $maxDate = $maxDate == null ? "null" : $maxDate;

        $query = "call GetUserRoleChanges($projId, $minDate, $maxDate, $skipCount, $pageSize, '$dataDirection', $roleId);";

        
        $num_rows = 0;
        $currentIndex = 0;
        $roleIds = array();
        $dataChanges = array();

        if (mysqli_multi_query($conn, $query)) {

            do {
                if ($result = mysqli_store_result($conn)) {
                    // echo "<br>result num rows $currentIndex: " . $result->num_rows . "<br>";
                    if($currentIndex == 0) {
                        $dataChanges = self::GetDataChangesFromResult($result);
                    }

                    if ($currentIndex == 1) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            // echo "<br>role_id: " . $row['role_id'] . "<br>";
                            $roleIds[] = $row['role_id'];
                            // print_r($roleIds);
                        }
                    }

                    if ($currentIndex == 2) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            // echo "<br>role_id: " . $row['role_id'] . "<br>";
                            $totalCount = $row['total_count'];
                            // print_r($roleIds);
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

        return
            [
                "dataChanges" => $dataChanges,
                "roleIds" => $roleIds,
                "totalCount" => $totalCount
            ];
    }

}