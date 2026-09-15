<?php

$projId = $module->getProjectId();
$maxDay = $module->getProjectSetting('max-days-page') ?: 7; // Default to 7 days if not set

require_once __DIR__ . "/pageSetup.php";

use CCTC\ConfigurationMonitorModule\Rendering;
use CCTC\ConfigurationMonitorModule\GetDbData;

echo "
<div class='projhdr'>
    <div style='float:left;'>
        <i class='fas fa-clipboard-list'></i> Changes in Project Settings
    </div>
</div>
<br/>
<p>
    This log shows changes made to project settings.
</p>
";

$privilegeFilter = '';
if (isset($_GET['privilege_filter'])) {
    // Sanitize privilege filter - allow alphanumeric, spaces, and underscores only
    $privilegeFilter = preg_replace('/[^a-zA-Z0-9_ ]/', '', $_GET['privilege_filter']);
}

$tableName = 'project-changes';
$page = "projectChanges";

//run the stored proc - get ALL records without pagination first if privilege filter is set
if (!empty($privilegeFilter)) {
    // Get all records to filter by privilege in PHP
    $logDataSetsAll = GetDbData::GetChangesFromSP($projId, $minDateDb, $maxDateDb, 0, 1000000, $dataDirection, $tableName);
    $dcsAll = $logDataSetsAll['dataChanges'];

    // Filter by privilege
    $dcsFiltered = $module->filterByPrivilege($dcsAll, $tableName, $privilegeFilter);

    // Apply manual pagination
    $totalCount = count($dcsFiltered);
    $dcs = array_slice($dcsFiltered, $skipCount, $pageSize);
    $showingCount = count($dcs);

    // Get unique privileges for the dropdown (from unfiltered data for current date range)
    $privilegesList = $module->getUniquePrivileges($dcsAll, $tableName);

} else {
    // Use normal database pagination when no privilege filter
    $logDataSets = GetDbData::GetChangesFromSP($projId, $minDateDb, $maxDateDb, $skipCount, $pageSize, $dataDirection, $tableName);
    $dcs = $logDataSets['dataChanges'];
    $totalCount = $logDataSets['totalCount'];
    $showingCount = count($dcs);

    // Get unique privileges for the dropdown
    $privilegesList = $module->getUniquePrivileges($logDataSets['dataChanges'], $tableName);
}

if ($showingCount == 0) {
    echo "<br><i>No changes to project settings have been made in this project.</i><br>";
    return;
}

$totPages = ceil($totalCount / $pageSize);

$privilegeSelect = Rendering::MakePrivilegeSelect($privilegesList, $privilegeFilter);
$pageSizeSelect = Rendering::MakePageSizeSelect($pageSize);
$retDirectionSelect = Rendering::MakeRetDirectionSelect($dataDirection);

//create the reset to return to default original state
$resetUrl = APP_PATH_WEBROOT . "/ExternalModules/?prefix=$moduleName&page=$page&pid=$projId";

echo "<div class='blue' style='padding-left:8px; padding-right:8px; border-width:1px; '>
    <form class='mt-1' id='filterForm' name='queryparams' method='get' action='' data-reset-url='$resetUrl'>
        " . Rendering::FilterFormHiddenInputs($moduleName, $page, $projId, $totPages, $pageNum, $defaultTimeFilter, $oneDayAgo, $oneWeekAgo, $oneMonthAgo, $oneYearAgo) . "

        <table>
            <tr>
                <td style='width: 100px;'><label>Property</label></td>
                <td style='width: 200px;'>$privilegeSelect</td>
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

echo $module->makeTable($dcs, $userDateFormat, $tableName);

echo Rendering::PageAssets($module);
