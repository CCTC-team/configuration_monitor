<?php

echo "<h3>Changes in User Role Privileges</h3>";
echo "<p><i>This log shows changes made to user role privileges.</i></p>";

$project_id = $module->getProjectId();

// echo "project_id: $project_id<br>";

$query = "select old_value, new_value, role_id, change_timestamp from user_role_changelog where project_id = $project_id";

$result = db_query($query);

$table = "<table id='user_role_change_table' border='1'>
            <thead><tr style='background-color: #FFFFE0;'>
                <th style='width: 5%;padding: 5px'>Role ID</th>
                <th style='width: 15%;padding: 5px'>Changed Privilege</th>
                <th style='width: 15%;padding: 5px'>Old Value</th>
                <th style='width: 15%;padding: 5px'>New Value</th>
                <th style='width: 15%;padding: 5px'>Timestamp</th>
            </tr></thead><tbody>
        ";

// echo "Table created successfully: " . $result . "<br>";
while($row = db_fetch_assoc($result)){
    // echo "<br> Old Value: " . $row['old_value'] . "<br>";
    // echo "New Value: " . $row['new_value'] . "<br>";
    // echo "Role ID: " . $row['role_id'] . "<br>";
    // echo "Change Timestamp: " . $row['change_timestamp'] . "<br>";
    $table .= $module->projectConfig($row['role_id'], $row['old_value'], $row['new_value'], $row['change_timestamp']);
    
}

$table .= "</tbody></table>";

if ($result->num_rows == 0)
    echo "<br><i>No changes to user role privileges have been made in this project.</i><br>";
else
    echo $table;