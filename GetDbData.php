<?php

namespace CCTC\ProjectConfigurationChangesModule;

class GetDbData
{

    static function GetDataChangesFromResult($result) : array
    {
        $dataChanges = array();

        while ($row = db_fetch_assoc($result))
        {  
            $dc = new DataChange();
            $dc->roleID = $row['role_id'];
            $dc->oldValue = $row['old_value'];
            $dc->newValue = $row['new_value'];
            $dc->timestamp = $row['ts'];
            $dc->action = $row['operation_type'];

            $dataChanges[] = $dc;
            // print_r($dc);
        }

        return $dataChanges;
    }

    // calls the GetUserRoleChanges stored procedure with the given parameters and returns the relevant data
    public static function GetUserRoleChangesFromSP(
        $projId, $maxDays, $skipCount, $pageSize, $dataDirection, $roleId)
    : array
    {

        global $module;
        global $conn;

        $query = "call GetUserRoleChanges($projId, $maxDays, $skipCount, $pageSize, '$dataDirection', $roleId);";

        $num_rows = 0;
        $currentIndex = 0;
        $roleIds = array();
        $dataChanges = array();

        if (mysqli_multi_query($conn, $query)) {
            // $updateTable = "<table id='user_role_change_table' border='1'>
            //     <thead><tr style='background-color: #FFFFE0;'>
            //         <th style='width: 5%;padding: 5px'>Role ID</th>
            //         <th style='width: 15%;padding: 5px'>Changed Privilege</th>
            //         <th style='width: 15%;padding: 5px'>Old Value</th>
            //         <th style='width: 15%;padding: 5px'>New Value</th>
            //         <th style='width: 15%;padding: 5px'>Timestamp</th>
            //         <th style='width: 15%;padding: 5px'>Action</th>
            //     </tr></thead><tbody>";

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
                    mysqli_free_result($result);
                    $currentIndex++;

                }
            } while (mysqli_next_result($conn));

            $updateTable .= "</tbody></table>";
            // echo $updateTable;

        } else {
            echo "Error: " . $conn->error;
        }

        return
            [
                "dataChanges" => $dataChanges,
                "roleIds" => $roleIds,
            ];
    }

}