<?php

global $module;
$modName = $module->getModuleDirectoryName();

require_once dirname(APP_PATH_DOCROOT, 1) . "/modules/$modName/Utility.php";
use CCTC\ConfigurationMonitorModule\Utility;

//set the helper dates for use in the quick links
$oneDayAgo = Utility::NowAdjusted('-1 days');
$oneWeekAgo = Utility::NowAdjusted('-7 days');
$oneMonthAgo = Utility::NowAdjusted('-1 months');
$oneYearAgo = Utility::NowAdjusted('-1 years');

if (isset($_GET['tableName'])) {
    $tableName = $_GET['tableName'];
}

if ($tableName != 'system-changes') {
    $projId = $module->getProjectId();
    $maxDay = $module->getProjectSetting('max-days-page') ?? 7; // Default to 7 days if not set

} else {
    $maxDay = $module->getSystemSetting('sys-max-days-page') ?? 7; // Default to 7 days if not set

}

//get form values
$minDate = Utility::NowAdjusted('-'. $maxDay . 'days'); //default to maxDay days ago

if (isset($_GET['startdt'])) {
    $minDate = $_GET['startdt'];
}
$maxDate = null;
if (isset($_GET['enddt'])) {
    $maxDate = $_GET['enddt'];
}

//set the default to one week
$defaultTimeFilter = "oneweekago";
$customActive = "";
$dayActive = "";
$weekActive = "active";
$monthActive = "";
$yearActive = "";
if (isset($_GET['defaulttimefilter'])) {
    $defaultTimeFilter = $_GET['defaulttimefilter'];
    $customActive = $defaultTimeFilter == "customrange" ? "active" : "";
    $dayActive = $defaultTimeFilter == "onedayago" ? "active" : "";
    $weekActive = $defaultTimeFilter == "oneweekago" ? "active" : "";
    $monthActive = $defaultTimeFilter == "onemonthago" ? "active" : "";
    $yearActive = $defaultTimeFilter == "oneyearago" ? "active" : "";
}

$dataDirection = "desc";
if (isset($_GET['retdirection'])) {
    $dataDirection = $_GET['retdirection'];
}
$pageSize = 25;
if (isset($_GET['pagesize'])) {
    $pageSize = $_GET['pagesize'];
}
$pageNum = 0;
if (isset($_GET['pagenum'])) {
    $pageNum = $_GET['pagenum'];
}


//use the export_type param to determine what to export and adjust params accordingly
$exportType = 'everything'; //default
if (isset($_GET['export_type'])) {
    $exportType = $_GET['export_type'];
}

$roleID = NULL; //default to NULL meaning all roles
if (isset($_GET['role_id'])) {
    $roleID = $_GET['role_id'];
}

$fieldName = NULL; //default to NULL meaning all fields
if (isset($_GET['field_name'])) {
    $fieldName = $_GET['field_name'];
}

$privilegeFilter = ''; //default to empty meaning all privileges
if (isset($_GET['privilege_filter'])) {
    $privilegeFilter = $_GET['privilege_filter'];
}

$skipCount = (int)$pageSize * (int)$pageNum;
$minDateDb = Utility::DateStringToDbFormat($minDate);
$maxDateDb = Utility::DateStringToDbFormat($maxDate);