<?php

$modName = $module->getModuleDirectoryName();

require_once dirname(APP_PATH_DOCROOT, 1) . "/modules/$modName/Utility.php";
require_once dirname(APP_PATH_DOCROOT, 1) . "/modules/$modName/Rendering.php";
require_once dirname(APP_PATH_DOCROOT, 1) . "/modules/$modName/GetDbData.php";

require_once APP_PATH_DOCROOT . "/Classes/Records.php";
require_once APP_PATH_DOCROOT . "/Classes/RCView.php";
require_once APP_PATH_DOCROOT . "/Classes/DateTimeRC.php";

use CCTC\ConfigurationMonitorModule\Utility;
use CCTC\ConfigurationMonitorModule\Rendering;
use CCTC\ConfigurationMonitorModule\GetDbData;

$maxDay = $module->getSystemSetting('max-days-sys') ?? 7; // Default to 7 days if not set
// echo "maxDay: $maxDay";

echo "
<h4 style='margin-top: 0;'>
    <i class='fas fa-clipboard-list'></i> Changes in System settings
</h4>
<br/>
<p>
    This log shows changes made to system settings.
</p>
";

//set the helper dates for use in the quick links
$oneDayAgo = Utility::NowAdjusted('-1 days');
$oneWeekAgo = Utility::NowAdjusted('-7 days');
$oneMonthAgo = Utility::NowAdjusted('-1 months');
$oneYearAgo = Utility::NowAdjusted('-1 years');

$minDate = Utility::NowAdjusted('-'. $maxDay . 'days'); //default to maxDay days ago

// echo "minDate: $minDate<br>";

//get form values
if (isset($_GET['startdt'])) {
    $minDate = $_GET['startdt'];
}
$maxDate = null;
if (isset($_GET['enddt'])) {
    $maxDate = $_GET['enddt'];
}

//set the default to one week
$defaultTimeFilter = "customrange";
$customActive = "active";
$dayActive = "";
$weekActive = "";
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

$fieldName = NULL; //default to NULL meaning all roles
if (isset($_GET['field_name'])) {
    $fieldName = $_GET['field_name'];
}

$dataDirection = "desc";
if (isset($_GET['retdirection'])) {
    $dataDirection = $_GET['retdirection'];
}

$pageSize = 10;
if (isset($_GET['pagesize'])) {
    $pageSize = $_GET['pagesize'];
}

$pageNum = 0;
if (isset($_GET['pagenum'])) {
    $pageNum = $_GET['pagenum'];
}

$skipCount = (int)$pageSize * (int)$pageNum;
 
$minDateDb = Utility::DateStringToDbFormat($minDate);
$maxDateDb = Utility::DateStringToDbFormat($maxDate);

$actMinAsDate = $minDate == "" ? Utility::DefaultMinDate() : Utility::DateStringAsDateTime($minDate);
$actMaxAsDate = $maxDate == "" ? Utility::Now() : Utility::DateStringAsDateTime($maxDate);
$fixMaxDate = $actMaxAsDate > Utility::Now() ? Utility::Now() : $actMaxAsDate;

$diff = $actMaxAsDate->diff($actMinAsDate);


// echo "<br>Base Url: " . Utility::GetBaseUrl();
// echo"<br> Module directory name: $moduleName<br>";

// echo "<br> maxDay: $maxDay<br>";
// echo "<br> skipCount: $skipCount<br>";
// echo "<br> pageSize: $pageSize<br>";
// echo "<br> pageNum: $pageNum<br>";
// echo "<br> maxDateDb: $maxDateDb";
// echo "<br>minDateDb: $minDateDb<br>";

$tableName = 'system_changes';
$projId = NULL;
$roleID = NULL;
// echo "fieldName: $fieldName";
//run the stored proc
$logDataSets = GetDbData::GetChangesFromSP($projId, $minDateDb, $maxDateDb, $skipCount, $pageSize, $dataDirection, $tableName, $roleID, $fieldName);

$fieldNames = $logDataSets['fieldNames'];
$dcs = $logDataSets['dataChanges'];
$totalCount = $logDataSets['totalCount']; // number of User Roles being changed
$showingCount = count($dcs); // number of User Roles being shown on this page
// echo "<br> TotalCount: $totalCount<br>";
// echo "<br>showingCount: $showingCount<br>";
// print_array($fieldNames);
$fieldNameSelect = Rendering::MakeFieldNameSelect($fieldNames, $fieldName);
$totPages = ceil($totalCount / $pageSize);
$actPage = (int)$pageNum + 1;
// echo "<br> dataDirection: $dataDirection<br>";
$skipFrom = $showingCount == 0 ? 0 : $skipCount + 1;

// adjust skipTo in cases where last page isn't a full page
if($showingCount < $pageSize) {
    $skipTo = $skipCount + $showingCount;
} else {
    $skipTo = $skipCount + (int)$pageSize;
}

$pagingInfo = "records {$skipFrom} to {$skipTo} of {$totalCount}";
$moduleName = "configuration_monitor";
$page = "systemChanges";

//create the reset to return to default original state
$resetUrl = Utility::GetBaseUrl() . "/ExternalModules/?prefix=$moduleName&page=$page";
// echo "resetUrl: $resetUrl";
$doReset = "window.location.href='$resetUrl';";
$pageSizeSelect = Rendering::MakePageSizeSelect($pageSize);
$retDirectionSelect = Rendering::MakeRetDirectionSelect($dataDirection);

echo "<script type='text/javascript'>
        function cleanUpParamsAndRun(moduleName, exportType, tableName) {
            //construct the params from the current page params
            let finalUrl = app_path_webroot+'ExternalModules/?prefix=' + moduleName + '&page=csv_export';

            let params = new URLSearchParams(window.location.search);
            //ignore some params
            params.forEach((v, k) => {            
                if(k !== 'prefix' && k !== 'page' && k !== 'redcap_csrf_token' ) {
                    finalUrl += '&' + k + '=' + encodeURIComponent(v);                                    
                }
            });
            
            //add the param to determine what to export        
            finalUrl += '&export_type=' + exportType;
            finalUrl += '&tableName=' + tableName;

            window.location.href=finalUrl;
            // console.log(finalUrl);
        }
        
        function resetForm() { 
            showProgress(1);        
            $doReset 
        }
    </script>";

$exportIcons = 
    "<div class='blue' style='padding-left:8px; padding-right:8px; border-width:1px; '>    
    <form class='mt-1' id='filterForm' name='queryparams' method='get' action=''>
        <input type='hidden' id='prefix' name='prefix' value='$moduleName'>
        <input type='hidden' id='page' name='page' value='$page'>
        <input type='hidden' id='totpages' name='totpages' value='$totPages'>
        <input type='hidden' id='pagenum' name='pagenum' value='$pageNum'>
        
        <input type='hidden' id='defaulttimefilter' name='defaulttimefilter' value='$defaultTimeFilter'>
        <input type='hidden' id='onedayago' name='onedayago' value='$oneDayAgo'>
        <input type='hidden' id='oneweekago' name='oneweekago' value='$oneWeekAgo'>
        <input type='hidden' id='onemonthago' name='onemonthago' value='$oneMonthAgo'>
        <input type='hidden' id='oneyearago' name='oneyearago' value='$oneYearAgo'>
                                                                    
        <table>
            <tr>
                <td style='width: 100px;'><label for='field_name'>Property</label></td>
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
                <td><label for='startdt'>Min edit date</label></td>
                <td><input id='startdt' name='startdt' class='x-form-text x-form-field' type='text' data-df='$userDateFormat' value='$minDate'></td>
                <td><button class='clear-button' type='button' onclick='resetDate(\"startdt\")'><small><i class='fas fa-eraser'></i></small></button></td>
                <td><label for='enddt'>Max edit date</label></td>
                <td><input id='enddt' name='enddt' class='x-form-text x-form-field' type='text' data-df='$userDateFormat' value='$maxDate'></td>
                <td><button style='margin-left: 0' class='clear-button' type='button' onclick='resetDate(\"enddt\")'><small><i class='fas fa-eraser'></i></small></button></td>
            </tr>
            <tr>
                <td colspan='5'>
                    <div class='btn-group bg-white' role='group'>
                        <button type='button' class='btn btn-outline-primary btn-xs $customActive' onclick='setCustomRange()'>Custom range</button>
                        <button type='button' class='btn btn-outline-primary btn-xs $dayActive' onclick='setTimeFrame(\"onedayago\")'>Past day</button>
                        <button type='button' class='btn btn-outline-primary btn-xs $weekActive' onclick='setTimeFrame(\"oneweekago\")'>Past week</button>
                        <button type='button' class='btn btn-outline-primary btn-xs $monthActive' onclick='setTimeFrame(\"onemonthago\")'>Past month</button>
                        <button type='button' class='btn btn-outline-primary btn-xs $yearActive' onclick='setTimeFrame(\"oneyearago\")'>Past year</button>
                    </div>
                </td>
            </tr>
        </table>
        <div class='p-2 mt-1' style='display: flex; flex-direction: row;'>
            <button id='btnprevpage' type='button' class='btn btn-outline-primary btn-xs mr-2' onclick='prevPage()'>
                <i class='fas fa-arrow-left fa-fw' style='font-size: medium; margin-top: 1px;'></i>
            </button>
            <button id='btnnextpage' type='button' class='btn btn-outline-primary btn-xs mr-4' onclick='nextPage()'>
                <i class='fas fa-arrow-right fa-fw' style='font-size: medium; margin-top: 1px;'></i>
            </button>     
            $pagingInfo
            <button class='clear-button' style='margin-left: 10px' type='button' onclick='resetForm()'><i class='fas fa-broom'></i> reset</button>
        </div>
        <div>
            <div class='ms-auto'>            
                <button class='jqbuttonmed ui-button ui-corner-all ui-widget export-records' type='button' onclick='cleanUpParamsAndRun(\"$moduleName\", \"current_page\", \"$tableName\")'>
                    <img src='" . APP_PATH_WEBROOT . "/Resources/images/xls.gif' style='position: relative;top: -1px;' alt=''>
                    Export current page
                </button>
                <button class='jqbuttonmed ui-button ui-corner-all ui-widget export-records' type='button' onclick='cleanUpParamsAndRun(\"$moduleName\", \"all_pages\", \"$tableName\")'>
                    <img src='" . APP_PATH_WEBROOT . "/Resources/images/xls.gif' style='position: relative;top: -1px;' alt=''>
                    Export all pages
                </button>
                <button class='jqbuttonmed ui-button ui-corner-all ui-widget export-all' type='button' onclick='cleanUpParamsAndRun(\"$moduleName\", \"everything\", \"$tableName\")'>
                    <img src='" . APP_PATH_WEBROOT . "/Resources/images/xls.gif' style='position: relative;top: -1px;' alt=''>
                    Export everything ignoring filters
                </button>                                    
            </div>                               
        </div>                 
    </form>
    </div>
    <br/>";
    
echo $exportIcons;

if ($showingCount == 0) {
    echo "<br><i>No changes have been made to the system settings.</i><br>";
    echo "<script type='text/javascript'>
        //hide export buttons
        document.querySelectorAll('.jqbuttonmed.export-records').forEach(button => {
            button.disabled = true;
        });
    </script>";
} else {
    //gets the users preferred data format which is used as data attribute on the datetimepicker field
    global $datetime_format;

    $userDateFormat = str_replace('y', 'Y', strtolower($datetime_format));

    if(ends_with($datetime_format, "_24")){
        $userDateFormat = str_replace('_24', ' H:i', $userDateFormat);
    } else {
        $userDateFormat = str_replace('_12', ' H:i a', $userDateFormat);
    }
    $table = $module->makeTable($dcs, $userDateFormat, $tableName);
    echo $table;
}
?>

<style>

    #filterForm > table > tbody > tr > td:nth-child(2) {
        width: 150px;
    }

    #startdt + button, #enddt + button {
        background-color: transparent;
        border: none;
    }

    .clear-button {
        background-color: transparent;
        border: none;
        color: #0a53be;
        margin-right: 4px;
        margin-left: 4px;
        margin-top: 1px;
    }


</style>

<script>

    //gets the date format to use from the built-in format from REDCap for use with js rather than the format
    //used for $userDateFormat
    let dateFormat = user_date_format_jquery

    $('#startdt').datetimepicker({
        dateFormat: dateFormat,
        showOn: 'button', buttonImage: app_path_images+'date.png',
        onClose: function () {
            if(document.getElementById('startdt').value) {
                document.getElementById('defaulttimefilter').value = 'customrange';
                submitForm('startdt');
            }
        }
    });
    $('#enddt').datetimepicker({
        dateFormat: dateFormat,
        showOn: 'button', buttonImage: app_path_images+'date.png',
        onClose: function () {
            if(document.getElementById('enddt').value) {
                document.getElementById('defaulttimefilter').value = 'customrange';
                submitForm('enddt');
            }
        }
    });

    function setCustomRange() {
        document.getElementById('defaulttimefilter').value = 'customrange';
        document.querySelector('#startdt + button').click();
    }

    function setTimeFrame(timeframe) {
        document.getElementById('startdt').value = document.getElementById(timeframe).value;
        document.getElementById('enddt').value = '';
        document.getElementById('defaulttimefilter').value = timeframe;
        resetPaging();
        submitForm('startdt');
    }

    function resetDataForm() {
        let dataForm = document.getElementById('datafrm');
        dataForm.value = '';
    }

    function nextPage() {
        let currPage = document.getElementById('pagenum');
        let totPages = document.getElementById('totpages');
        if (currPage.value < totPages.value) {
            currPage.value = Number(currPage.value) + 1;
            submitForm('pagenum');
        }
    }

    function prevPage() {
        let currPage = document.getElementById('pagenum');
        if(currPage.value > 0) {
            currPage.value = Number(currPage.value) - 1;
            submitForm('pagenum');
        }
    }

    function resetPaging() {
        let currPage = document.getElementById('pagenum');
        currPage.value = 0;
        let totPages = document.getElementById('totpages');
        totPages.value = 0;
    }

    function onDirectionChanged() {
        submitForm('retdirection');
    }

    function onFilterChanged(id) {
        resetPaging();
        submitForm(id);
    }

    // use this when a field changes so can run request on any change
    function submitForm(src) {
        // alert("submitForm called with src: " + src);
        showProgress(1);

        let frm = document.getElementById('filterForm');

        //clear the csrfToken
        let csrfToken = document.querySelector('input[name="redcap_csrf_token"]');
        csrfToken.value = '';
        // alert("Submitting form with " + src + " changed");
        frm.submit();
    }

    function resetDate(dateId) {
        if (document.getElementById(dateId).value) {
            document.getElementById(dateId).value = '';
            document.getElementById('defaulttimefilter').value = 'customrange';
            submitForm(dateId);
        }
    }

    function clearFilter(id) {
        if (document.getElementById(id).value) {
            document.getElementById(id).value = '';
            submitForm(id);
        }
    }

    $(window).on('load', function() {

        //handle disabling nav buttons when not applicable
        let currPage = document.getElementById('pagenum');
        let totPages = document.getElementById('totpages');

        document.getElementById('btnprevpage').disabled = currPage.value === '0';
        document.getElementById('btnnextpage').disabled = parseInt(currPage.value) + 1 === parseInt(totPages.value);

    });

</script>