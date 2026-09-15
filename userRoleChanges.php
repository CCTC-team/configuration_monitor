<?php

$projId = $module->getProjectId();
$maxDay = $module->getProjectSetting('max-days-page') ?: 7; // Default to 7 days if not set

require_once __DIR__ . "/pageSetup.php";

use CCTC\ConfigurationMonitorModule\Rendering;
use CCTC\ConfigurationMonitorModule\GetDbData;

echo "
<div class='projhdr'>
    <div style='float:left;'>
        <i class='fas fa-clipboard-list'></i> Changes in User Role Privileges
    </div>
</div>
<br/>
<p>
    This log shows changes made to user role privileges.
</p>
";

$roleID = NULL; //default to NULL meaning all roles
if (isset($_GET['role_id']) && $_GET['role_id'] !== '') {
    // Cast to integer for role ID
    $roleID = (int)$_GET['role_id'];
}

//default to NULL meaning all actions; anything not INSERT/UPDATE/DELETE falls back to NULL
$actionType = GetDbData::NormaliseActionType($_GET['action_type'] ?? null);

$tableName = 'user-role-changes';
$page = "userRoleChanges";

//run the stored proc
$logDataSets = GetDbData::GetChangesFromSP($projId, $minDateDb, $maxDateDb, $skipCount, $pageSize, $dataDirection, $tableName, $roleID, NULL, $actionType);

$dcs = $logDataSets['dataChanges'];
$totalCount = $logDataSets['totalCount']; // number of User Roles being changed
$showingCount = count($dcs); // number of User Roles being shown on this page
$totPages = ceil($totalCount / $pageSize);

$roleSelect = Rendering::MakeRoleSelect($logDataSets['roleIds'], $roleID, $logDataSets['roleNames']);
$actionSelect = Rendering::MakeActionSelect(GetDbData::ACTION_TYPES, $actionType);
$pageSizeSelect = Rendering::MakePageSizeSelect($pageSize);
$retDirectionSelect = Rendering::MakeRetDirectionSelect($dataDirection);

//create the reset to return to default original state
$resetUrl = APP_PATH_WEBROOT . "/ExternalModules/?prefix=$moduleName&page=$page&pid=$projId";

echo "<div class='blue' style='padding-left:8px; padding-right:8px; border-width:1px; '>
    <form class='mt-1' id='filterForm' name='queryparams' method='get' action='' data-reset-url='$resetUrl'>
        " . Rendering::FilterFormHiddenInputs($moduleName, $page, $projId, $totPages, $pageNum, $defaultTimeFilter, $oneDayAgo, $oneWeekAgo, $oneMonthAgo, $oneYearAgo) . "

        <table>
            <tr>
                <td style='width: 100px;'><label>User Role</label></td>
                <td style='width: 200px;'>$roleSelect</td>
                <td></td>
                <td style='width: 100px;'><label>Action</label></td>
                <td>$actionSelect</td>
            </tr>
            <tr>
                " . Rendering::DateRangeCells($userDateFormat, $minDate, $maxDate) . "
                <td>
                    " . Rendering::TimeFrameButtons($defaultTimeFilter, 'margin-left: 30px;') . "
                </td>
            </tr>
            <tr>
                <td><label for='retdirection'>Order by</label></td>
                <td>$retDirectionSelect</td>
                <td></td>
                <td><label for='pagesize' class='mr-2'>Page size</label></td>
                <td>$pageSizeSelect</td>
            </tr>
        </table>
        <div class='p-2 mt-1' style='display: flex; flex-direction: row;'>
            " . Rendering::PagingButtons(Rendering::PagingInfo($skipCount, $showingCount, $pageSize, $totalCount)) . "
            " . Rendering::ExportButtons($moduleName, $projId, $tableName) . "
        </div>
    </form>
    </div>
    <br/>";

if ($showingCount == 0) {
    echo "<br><i>No changes to user role privileges have been made in this project.</i><br>";
    echo "<script type='text/javascript'>
        //hide export buttons
        document.querySelectorAll('.jqbuttonmed.export-records').forEach(button => {
            button.disabled = true;
        });
    </script>";
} else {
    echo $module->makeTable($dcs, $userDateFormat, $tableName);
}

echo Rendering::PageAssets($module);
