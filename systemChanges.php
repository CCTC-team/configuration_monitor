<?php

$maxDay = $module->getSystemSetting('sys-max-days-page') ?: 7; // Default to 7 days if not set

require_once __DIR__ . "/pageSetup.php";

use CCTC\ConfigurationMonitorModule\Rendering;
use CCTC\ConfigurationMonitorModule\GetDbData;

echo "
<h4 style='margin-top: 0;'>
    <i class='fas fa-clipboard-list'></i> Changes in System Settings
</h4>
<br/>
<p>
    This log shows changes made to system settings.
</p>
";

$fieldName = NULL; //default to NULL meaning all fields
if (isset($_GET['field_name']) && $_GET['field_name'] !== '') {
    // Sanitize field name - allow alphanumeric and underscores only
    $fieldName = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['field_name']);
}

$tableName = 'system-changes';
$page = "systemChanges";
$projId = NULL;
$roleID = NULL;

//run the stored proc
$logDataSets = GetDbData::GetChangesFromSP($projId, $minDateDb, $maxDateDb, $skipCount, $pageSize, $dataDirection, $tableName, $roleID, $fieldName);

$dcs = $logDataSets['dataChanges'];
$totalCount = $logDataSets['totalCount']; // number of system changes
$showingCount = count($dcs); // number of system changes shown on this page
$totPages = ceil($totalCount / $pageSize);

$fieldNameSelect = Rendering::MakeFieldNameSelect($logDataSets['fieldNames'], $fieldName);
$pageSizeSelect = Rendering::MakePageSizeSelect($pageSize);
$retDirectionSelect = Rendering::MakeRetDirectionSelect($dataDirection);

//create the reset to return to default original state
$resetUrl = APP_PATH_WEBROOT . "/ExternalModules/?prefix=$moduleName&page=$page";

echo "<div class='blue' style='padding-left:8px; padding-right:8px; border-width:1px; '>
    <form class='mt-1' id='filterForm' name='queryparams' method='get' action='' data-reset-url='$resetUrl'>
        " . Rendering::FilterFormHiddenInputs($moduleName, $page, $projId, $totPages, $pageNum, $defaultTimeFilter, $oneDayAgo, $oneWeekAgo, $oneMonthAgo, $oneYearAgo) . "

        <table>
            <tr>
                <td style='width: 100px;'><label>Property</label></td>
                <td style='width: 250px;'>$fieldNameSelect</td>
            </tr>
            <tr>
                <td><label for='retdirection'>Order by</label></td>
                <td>$retDirectionSelect</td>
                <td></td>
                <td style='width: 100px;'><label for='pagesize' class='mr-2'>Page size</label></td>
                <td>$pageSizeSelect</td>
            </tr>
            <tr>
                " . Rendering::DateRangeCells($userDateFormat, $minDate, $maxDate) . "
            </tr>
            <tr>
                <td colspan='5'>
                    " . Rendering::TimeFrameButtons($defaultTimeFilter) . "
                </td>
            </tr>
        </table>
        <div class='p-2 mt-1' style='display: flex; flex-direction: row;'>
            " . Rendering::PagingButtons(Rendering::PagingInfo($skipCount, $showingCount, $pageSize, $totalCount)) . "
        </div>
        <div>
            " . Rendering::ExportButtons($moduleName, $projId, $tableName) . "
        </div>
    </form>
    </div>
    <br/>";

if ($showingCount == 0) {
    echo "<br><i>No changes have been made to the system settings.</i><br>";
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
