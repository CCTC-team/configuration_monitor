<?php

echo "<h3>Changes in User Role Privileges</h3>";
echo "<p><i>This log shows changes made to user role privileges.</i></p>";

$user = $module->getUser();
$rights = $user->getRights();
echo "<br>rights[user_rights]: " . $rights['user_rights'];
echo "<br>isSuperUser: " . ($module->isSuperUser() ? 'true' : 'false') . "<br>";

if (($rights['user_rights'] == 0) && !$module->isSuperUser())   {
    echo "<br><b>You do not have permission to view this page.</b><br>";
    return;
}

$projId = $module->getProjectId();
$max_days = $module->getProjectSetting('max-days-index') ?? 7; // Default to 7 days if not set
echo "<h6><i>Displaying changes made in the last $max_days days.</i></h6>";

// $query = "select role_id, old_value, new_value, operation_type, change_timestamp from user_role_changelog where project_id = $projId and change_timestamp >= NOW() - INTERVAL $max_days DAY";
// $result = db_query($query);
$result = $module->userRoleQuery($projId, $max_days, 'DAY');

if ($result->num_rows == 0) {
    echo "<br><i>No changes to user role privileges have been made in this project.</i><br>";
}
else {
    $runMessage = "Messages will appear here after running an export.";
    $redcapPart = APP_PATH_WEBROOT;
    $moduleName = $module->getModuleName();

    echo "<script type='text/javascript'>
            function cleanUpParamsAndRun(moduleName, projId, exportType) {
                
                //construct the params from the current page params
                let finalUrl = APP_PATH_WEBROOT+'ExternalModules/?prefix=' + moduleName + '&page=csv_export&pid=' + projId;
                let params = new URLSearchParams(window.location.search);
                //ignore some params
                params.forEach((v, k) => {            
                    if(k !== 'prefix' && k !== 'page' && k !== 'pid' && k !== 'redcap_csrf_token' ) {                
                        finalUrl += '&' + k + '=' + encodeURIComponent(v);                                    
                    }
                });
                
                //add the param to determine what to export        
                finalUrl += '&export_type=' + exportType;
                
                window.location.href=finalUrl;                
            }
        </script>";

    $exportIcons = 
        "<div class='blue' style='padding-left:8px; padding-right:8px; border-width:1px; '>    
        <form class='mt-1' id='filterForm' name='queryparams' method='get' action=''>
            <div class='p-2 mt-1' style='display: flex; flex-direction: row;'>
                <button id='btnprevpage' type='button' class='btn btn-outline-primary btn-xs mr-2' onclick='prevPage()'>
                    <i class='fas fa-arrow-left fa-fw' style='font-size: medium; margin-top: 1px;'></i>
                </button>
                <button id='btnnextpage' type='button' class='btn btn-outline-primary btn-xs mr-4' onclick='nextPage()'>
                    <i class='fas fa-arrow-right fa-fw' style='font-size: medium; margin-top: 1px;'></i>
                </button>     
                $pagingInfo
                <button class='clear-button' style='margin-left: 10px' type='button' onclick='resetForm()'><i class='fas fa-broom'></i> reset</button>
                <div class='ms-auto'>            
                    <button class='jqbuttonmed ui-button ui-corner-all ui-widget' type='button' onclick='cleanUpParamsAndRun(\"$moduleName\", \"$projId\", \"current_page\")'>
                        <img src='" . APP_PATH_WEBROOT . "/Resources/images/xls.gif' style='position: relative;top: -1px;' alt=''>
                        Export current page
                    </button>
                    <button class='jqbuttonmed ui-button ui-corner-all ui-widget' type='button' onclick='cleanUpParamsAndRun(\"$moduleName\", \"$projId\", \"all_pages\")'>
                        <img src='" . APP_PATH_WEBROOT . "/Resources/images/xls.gif' style='position: relative;top: -1px;' alt=''>
                        Export all pages
                    </button>
                    <button class='jqbuttonmed ui-button ui-corner-all ui-widget' type='button' onclick='cleanUpParamsAndRun(\"$moduleName\", \"$projId\", \"everything\")'>
                        <img src='" . APP_PATH_WEBROOT . "/Resources/images/xls.gif' style='position: relative;top: -1px;' alt=''>
                        Export everything ignoring filters
                    </button>                                    
                </div>                               
            </div>                 
        </form>
        $runMessage      
        </div>
        <br/>";
    $updateTable = "<table id='user_role_change_table' border='1'>
            <thead><tr style='background-color: #FFFFE0;'>
                <th style='width: 5%;padding: 5px'>Role ID</th>
                <th style='width: 15%;padding: 5px'>Changed Privilege</th>
                <th style='width: 15%;padding: 5px'>Old Value</th>
                <th style='width: 15%;padding: 5px'>New Value</th>
                <th style='width: 15%;padding: 5px'>Timestamp</th>
                <th style='width: 15%;padding: 5px'>Action</th>
            </tr></thead><tbody>";

    while ($row = db_fetch_assoc($result)) {
        // Use the userRoleChanges function to find difference and format each row
        $updateTable .= $module->userRoleChanges($row['role_id'], $row['old_value'], $row['new_value'], $row['change_timestamp'], $row['operation_type']);
    }

    $updateTable .= "</tbody></table>";
    echo $exportIcons. $updateTable;
}


// $module->sendEmail();