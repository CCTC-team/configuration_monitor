<?php
// Shared set-up for the change log pages (systemChanges.php, projectChanges.php and
// userRoleChanges.php). Loads the helper classes and reads the filters every page has -
// date range, time frame, order and paging - from the query string.
// The including page must set $maxDay (the default number of days to look back) first.

$modName = $module->getModuleDirectoryName();

require_once dirname(APP_PATH_DOCROOT, 1) . "/modules/$modName/Utility.php";
require_once dirname(APP_PATH_DOCROOT, 1) . "/modules/$modName/Rendering.php";
require_once dirname(APP_PATH_DOCROOT, 1) . "/modules/$modName/GetDbData.php";

require_once APP_PATH_DOCROOT . "/Classes/Records.php";
require_once APP_PATH_DOCROOT . "/Classes/RCView.php";
require_once APP_PATH_DOCROOT . "/Classes/DateTimeRC.php";

use CCTC\ConfigurationMonitorModule\Utility;

$moduleName = "configuration_monitor";

//gets the users preferred data format which is used as data attribute on the datetimepicker field
global $datetime_format;
$userDateFormat = Utility::PhpDateTimeFormat($datetime_format);

//set the helper dates for use in the quick links
$oneDayAgo = Utility::NowAdjusted('-1 days');
$oneWeekAgo = Utility::NowAdjusted('-7 days');
$oneMonthAgo = Utility::NowAdjusted('-1 months');
$oneYearAgo = Utility::NowAdjusted('-1 years');

$minDate = Utility::NowAdjusted('-'. $maxDay . 'days'); //default to maxDay days ago

//get form values with input sanitization
if (isset($_GET['startdt'])) {
    // Sanitize date input - strip tags and limit length
    $minDate = htmlspecialchars(substr($_GET['startdt'], 0, 20), ENT_QUOTES, 'UTF-8');
}
$maxDate = null;
if (isset($_GET['enddt'])) {
    $maxDate = htmlspecialchars(substr($_GET['enddt'], 0, 20), ENT_QUOTES, 'UTF-8');
}

$defaultTimeFilter = "customrange";
if (isset($_GET['defaulttimefilter'])) {
    // Whitelist allowed time filter values
    $allowedFilters = ['customrange', 'onedayago', 'oneweekago', 'onemonthago', 'oneyearago'];
    $defaultTimeFilter = in_array($_GET['defaulttimefilter'], $allowedFilters) ? $_GET['defaulttimefilter'] : 'customrange';
}

$dataDirection = "desc";
if (isset($_GET['retdirection'])) {
    // Whitelist allowed direction values
    $dataDirection = in_array($_GET['retdirection'], ['asc', 'desc']) ? $_GET['retdirection'] : 'desc';
}

$pageSize = 10;
if (isset($_GET['pagesize'])) {
    // Cast to integer and limit to reasonable values
    $pageSize = max(10, min(250, (int)$_GET['pagesize']));
}

$pageNum = 0;
if (isset($_GET['pagenum'])) {
    // Cast to integer, ensure non-negative
    $pageNum = max(0, (int)$_GET['pagenum']);
}

$skipCount = (int)$pageSize * (int)$pageNum;

$minDateDb = Utility::DateStringToDbFormat($minDate);
$maxDateDb = Utility::DateStringToDbFormat($maxDate);
