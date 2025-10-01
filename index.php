<?php
// use CCTC\ProjectConfigurationChangesModule\ProjectConfigurationChangesModule;

$modName = $module->getModuleDirectoryName();

require_once APP_PATH_DOCROOT . "/Classes/REDCap.php";
require_once dirname(APP_PATH_DOCROOT, 1) . "/modules/$modName/Utility.php";
require_once dirname(APP_PATH_DOCROOT, 1) . "/modules/$modName/DataChange.php";
require_once dirname(APP_PATH_DOCROOT, 1) . "/modules/$modName/Rendering.php";
require_once dirname(APP_PATH_DOCROOT, 1) . "/modules/$modName/GetDbData.php";

require_once APP_PATH_DOCROOT . "/Classes/DateTimeRC.php";

use CCTC\ProjectConfigurationChangesModule\Utility;
use CCTC\ProjectConfigurationChangesModule\Rendering;
use CCTC\ProjectConfigurationChangesModule\GetDbData;

$user = $module->getUser();
$rights = $user->getRights();
// echo "<br>rights[user_rights]: " . $rights['user_rights'];
// echo "<br>isSuperUser: " . ($module->isSuperUser() ? 'true' : 'false') . "<br>";

// Only users with valid user rights (excluding 'No Access') or super users can access this page
if (($rights['user_rights'] == 0) && !$module->isSuperUser())   {
    echo "<br><b>You do not have permission to view this page.</b><br>";
    return;
}

// Check user's expiration date (if exists)
if ($user_rights['expiration'] != "" && $user_rights['expiration'] <= TODAY)
{
    $GLOBALS['no_access'] = 1;
    // Instead of returning 'false', return '2' specifically so we can note to user that the password has expired
    return '2';
}

// global $conn;
$projId = $module->getProjectId();
$maxDays = $module->getProjectSetting('max-days-index') ?? 7; // Default to 7 days if not set
// include "getparams.php";

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

$skipCount = (int)$pageSize * (int)$pageNum;

$roleID = -1; //default to -1 meaning all roles
if (isset($_GET['role_id'])) {
    $roleID = $_GET['role_id'];
}

echo "<h3>Changes in User Role Privileges</h3>";
echo "<p><i>This log shows changes made to user role privileges in the last $max_days days.</i></p>";
// echo "<br> projId: $projId<br>";
// echo "<br> max_days: $max_days<br>";
// echo "<br> skipCount: $skipCount<br>";
// echo "<br> pageSize: $pageSize<br>";
// echo "<br> pageNum: $pageNum<br>";
// echo "<br> dataDirection: $dataDirection<br>";
// $query = "call GetUserRoleChanges($projId, $max_days, $skipCount, $pageSize, '$dataDirection', $roleID);";
// $num_rows = 0;
// $currentIndex = 0;


// if (mysqli_multi_query($conn, $query)) {
//     $updateTable = "<table id='user_role_change_table' border='1'>
//         <thead><tr style='background-color: #FFFFE0;'>
//             <th style='width: 5%;padding: 5px'>Role ID</th>
//             <th style='width: 15%;padding: 5px'>Changed Privilege</th>
//             <th style='width: 15%;padding: 5px'>Old Value</th>
//             <th style='width: 15%;padding: 5px'>New Value</th>
//             <th style='width: 15%;padding: 5px'>Timestamp</th>
//             <th style='width: 15%;padding: 5px'>Action</th>
//         </tr></thead><tbody>";

//     do {
//         if ($result = mysqli_store_result($conn)) {
//             echo "<br>result num rows $currentIndex: " . $result->num_rows . "<br>";
//             if($currentIndex == 0) {
//                 $num_rows = $result->num_rows;
//                 while ($row = mysqli_fetch_assoc($result)) {
//                     $updateTable .= $module->userRoleChanges($row['role_id'], $row['old_value'], $row['new_value'], $row['ts'], $row['operation_type']);
//                 }
//             }

//             if ($currentIndex == 1) {
//                 while ($row = mysqli_fetch_assoc($result)) {
//                     // echo "<br>role_id: " . $row['role_id'] . "<br>";
//                     $roleIds[] = $row['role_id'];
//                     // print_r($roleIds);
//                 }
//             }
//             mysqli_free_result($result);
//             $currentIndex++;

//         }
//     } while (mysqli_next_result($conn));

//     $updateTable .= "</tbody></table>";
//     // echo $updateTable;

// } else {
//     echo "Error: " . $conn->error;
// }

 //run the stored proc
$logDataSets = GetDbData::GetUserRoleChangesFromSP($projId, $maxDays, $skipCount, $pageSize, $dataDirection, $roleID);

$runMessage = "";

$dcs = $logDataSets['dataChanges'];
$showingCount = count($dcs);
// echo "<br>showingCount: $showingCount<br>";

if ($showingCount == 0) {
    echo "<br><i>No changes to user role privileges have been made in this project.</i><br>";
}
else {

    $minDate = $oneWeekAgo;
    if (isset($_GET['startdt'])) {
        $minDate = $_GET['startdt'];
    }
    $maxDate = null;
    if (isset($_GET['enddt'])) {
        $maxDate = $_GET['enddt'];
    }


    $minDateDb = Utility::DateStringToDbFormat($minDate);
    $maxDateDb = Utility::DateStringToDbFormat($maxDate);

    $actMinAsDate = $minDate == "" ? Utility::DefaultMinDate() : Utility::DateStringAsDateTime($minDate);
    $actMaxAsDate = $maxDate == "" ? Utility::Now() : Utility::DateStringAsDateTime($maxDate);
    $fixMaxDate = $actMaxAsDate > Utility::Now() ? Utility::Now() : $actMaxAsDate;

    $diff = $actMaxAsDate->diff($actMinAsDate);

    
    //gets the users preferred data format which is used as data attribute on the datetimepicker field
    global $datetime_format;

    $skipFrom = $showingCount == 0 ? 0 : $skipCount + 1;

    // adjust skipTo in cases where last page isn't a full page
    if($showingCount < $pageSize) {
        $skipTo = $skipCount + $showingCount;
    } else {
        $skipTo = $skipCount + (int)$pageSize;
    }

    $csvExportPage = $module->getUrl('csv_export.php');

    $pagingInfo = "records {$skipFrom} to {$skipTo} of {$totalCount}";
    $runMessage = "Messages will appear here after running an export.";
    $moduleName = $module->getModuleName();
    $page = "index.php";

    // echo "<br>Base Url: " . Utility::GetBaseUrl();
    // echo "<br>Project ID: $projId<br>";
    // echo"<br> Module directory name: $moduleName<br>";

    //create the reset to return to default original state
    $resetUrl = Utility::GetBaseUrl() . "/ExternalModules/?prefix=$moduleName&page=$page&pid=$projId";
    $doReset = "window.location.href='$resetUrl';";
    $pageSizeSelect = Rendering::MakePageSizeSelect($pageSize);
    $retDirectionSelect = Rendering::MakeRetDirectionSelect($dataDirection);
    $roleIds = $logDataSets['roleIds'];
    $roleSelect = Rendering::MakeRoleSelect($roleIds, $roleID);

    $updateTable = "<table id='user_role_change_table' border='1'>
        <thead><tr style='background-color: #FFFFE0;'>
            <th style='width: 5%;padding: 5px'>Role ID</th>
            <th style='width: 15%;padding: 5px'>Changed Privilege</th>
            <th style='width: 15%;padding: 5px'>Old Value</th>
            <th style='width: 15%;padding: 5px'>New Value</th>
            <th style='width: 15%;padding: 5px'>Timestamp</th>
            <th style='width: 15%;padding: 5px'>Action</th>
        </tr></thead><tbody>";

    foreach($dcs as $dc) {
        // $date = DateTime::createFromFormat('YmdHis', $dc->timestamp);
        // $formattedDate = $date->format($userDateFormat);
        $updateTable .= $module->userRoleChanges($dc->roleID, $dc->oldValue, $dc->newValue, $dc->timestamp, $dc->action);
    }
    $updateTable .= "</tbody></table>";
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
            <input type='hidden' id='pid' name='pid' value='$projId'>
            <input type='hidden' id='totpages' name='totpages' value='$totPages'>
            <input type='hidden' id='pagenum' name='pagenum' value='$pageNum'>
            
            <input type='hidden' id='defaulttimefilter' name='defaulttimefilter' value='$defaultTimeFilter'>
            <input type='hidden' id='onedayago' name='onedayago' value='$oneDayAgo'>
            <input type='hidden' id='oneweekago' name='oneweekago' value='$oneWeekAgo'>
            <input type='hidden' id='onemonthago' name='onemonthago' value='$oneMonthAgo'>
            <input type='hidden' id='oneyearago' name='oneyearago' value='$oneYearAgo'>
                                                                        
            <table>
                <tr>
                              
                </tr>
                <tr>
                    <td><label for='min_date'>Min edit date</label></td>
                    <td><input id='startdt' style='width: 150px' name='startdt' class='x-form-text x-form-field' type='text' data-df='$userDateFormat' value='$minDate'></td>
                    <td><button class='clear-button' type='button' onclick='resetDate(\"startdt\")'><small><i class='fas fa-eraser'></i></small></button></td>
                    
                    <td><label for='max_date'>Max edit date</label></td>
                    <td><input id='enddt' name='enddt' class='x-form-text x-form-field' type='text' data-df='$userDateFormat' value='$maxDate'></td>
                    <td><button style='margin-left: 0' class='clear-button' type='button' onclick='resetDate(\"enddt\")'><small><i class='fas fa-eraser'></i></small></button></td>
                    
                    <td>
                        <div class='btn-group bg-white' role='group';  style='margin-left: 30px;'>                
                            <button type='button' class='btn btn-outline-primary btn-xs $customActive' onclick='setCustomRange()'>Custom range</button>
                            <button type='button' class='btn btn-outline-primary btn-xs $dayActive' onclick='setTimeFrame(\"onedayago\")'>Past day</button>
                            <button type='button' class='btn btn-outline-primary btn-xs $weekActive' onclick='setTimeFrame(\"oneweekago\")'>Past week</button>
                            <button type='button' class='btn btn-outline-primary btn-xs $monthActive' onclick='setTimeFrame(\"onemonthago\")'>Past month</button>
                            <button type='button' class='btn btn-outline-primary btn-xs $yearActive' onclick='setTimeFrame(\"oneyearago\")'>Past year</button>
                        </div>                                        
                    </td>                                    
                </tr>                       
                <tr>
                    <td><label for='role_id'>Userrole</label></td>
                    <td>$roleSelect</td>
                    <td/>      
                    <td><label for='retdirection'>Order by</label></td>                
                    <td>$retDirectionSelect</td>
                    <td/>
                    <td style='width: 50px'><label for='pagesize' class='mr-2'>Page size</label></td>                
                    <td>$pageSizeSelect</td>                
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
    

    // while ($row = db_fetch_assoc($result)) {
    //     // Use the userRoleChanges function to find difference and format each row
    //     $updateTable .= $module->userRoleChanges($row['role_id'], $row['old_value'], $row['new_value'], $row['ts'], $row['operation_type']);
    // }

    echo $exportIcons. $updateTable;
}

// $module->sendEmail();


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

    //fix for #104 and #106
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

    function resetEditor() {
        let editor = document.getElementById('editor');
        editor.value = '';
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
        showProgress(1);

        let frm = document.getElementById('filterForm');
        // apply this for the role_id drop down to work
        let logRole = document.getElementById('role_id');
        logRole.name = 'role_id';

        //clear the csrfToken
        let csrfToken = document.querySelector('input[name="redcap_csrf_token"]');
        csrfToken.value = '';

        frm.submit();
    }

    function resetDate(dateId) {
        if(document.getElementById(dateId).value) {
            document.getElementById(dateId).value = '';
            document.getElementById('defaulttimefilter').value = 'customrange';
            submitForm(dateId);
        }
    }

    function clearFilter(id) {
        if(document.getElementById(id).value) {
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