<?php

require_once APP_PATH_DOCROOT . "/Config/init_project.php";
$lang = Language::getLanguage('English');

$modName = $module->getModuleDirectoryName();

require_once dirname(APP_PATH_DOCROOT, 1) . "/modules/$modName/ProjectConfigurationChangesModule.php";
require_once dirname(APP_PATH_DOCROOT, 1) . "/modules/$modName/GetDbData.php";

require_once dirname(APP_PATH_DOCROOT, 1) . "/modules/$modName/Utility.php";
require_once dirname(APP_PATH_DOCROOT, 1) . "/modules/$modName/Rendering.php";

require_once APP_PATH_DOCROOT . "/Classes/Records.php";
require_once APP_PATH_DOCROOT . "/Classes/RCView.php";
require_once APP_PATH_DOCROOT . "/Classes/DateTimeRC.php";

use CCTC\ProjectConfigurationChangesModule\GetDbData;
use CCTC\ProjectConfigurationChangesModule\DataEntryLogModule;


// Increase memory limit in case needed for intensive processing
//System::increaseMemory(2048);

// File: getparams.php
/** @var $projId */
/** @var $minDateDb */
/** @var $maxDateDb */
/** @var $skipCount */
/** @var $pageSize */
/** @var $dataDirection */
/** @var $roleID */
/** @var $defaultTimeFilter */
/** @var $oneDayAgo */
/** @var $oneWeekAgo */
/** @var $oneMonthAgo */
/** @var $oneYearAgo */
/** @var $customActive */
/** @var $dayActive */
/** @var $weekActive */
/** @var $monthActive */
/** @var $yearActive */
/** @var $maxDate */
/** @var $minDate */
/** @var $userDateFormat */

include "getparams.php";

// alert("Result(getparams): ");


//run the query using the same params as on the index page when the query called
//runForExport means it only returns the actual data requested (and not data for filters)

//use the export_type param to determine what to export and adjust params accordingly
$exportType = $_GET['export_type'];

//if current_page then keep the params already captured from getparams.php


//change paging to include everything
if($exportType == 'all_pages' || $exportType == 'everything') {
    //change the pagesize to a sensible 'unlimited' max. Actual max for limit as unsigned int is 18446744073709551615
    //but use 1 million
    $skipCount = 0;
    $pageSize = 1000000;
}

if($exportType == 'everything') {
    $roleID = NULL; //all roles
    $minDateDb = null;
    $maxDateDb = null;
}



// $skipCount = 0;
// $pageSize = 25;
// $dataDirection = "desc";
// $roleID = "NULL"; //all roles


//run the stored proc
$result = GetDbData::GetUserRoleChangesFromSP($projId, $minDateDb, $maxDateDb, $skipCount, $pageSize, $dataDirection, $roleID);

// Set headers
$headers = array("role id", "changed privilege", "old value", "new value", "action", "timestamp");

// Set file name and path
$filename = APP_PATH_TEMP . date("YmdHis") . '_' . PROJECT_ID . '_user_role_changes.csv';

// Begin writing file from query result
$fp = fopen($filename, 'w');

if ($fp && $result)
{
    try {

        $delim = User::getCsvDelimiter();

        // Write headers to file
        fputcsv($fp, $headers, $delim);

        // Set values for this row and write to file
        foreach ($result["dataChanges"] as $dc) {

            $dcChanges = $module->userRoleChanges($dc["roleID"], $dc["oldValue"], $dc["newValue"], $dc["timestamp"], $dc["action"]);
            if (is_array($dcChanges)) {
                foreach ($dcChanges as $dc) {
                    // $r['roleID'], $r['privilege'], $r['oldValue'], $r['newValue'], $r['ts'], $r['action']
                    $row["roleID"] = $dc["roleID"];
                    $row["privilege"] = $dc["privilege"];
                    $row["oldValue"] = $dc["oldValue"];
                    $row["newValue"] = $dc["newValue"];
                    $row["action"] = $dc["action"];
                    //timestamp
                    $row["timestamp"] = 
                        $dc["timestamp"] == null || $dc["timestamp"] == ""
                            ? ""
                            : DateTime::createFromFormat('YmdHis', $dc["timestamp"])->format($userDateFormat);
                    fputcsv($fp, $row, $delim);

                    
                
                }
            }
            
            

            // fputcsv($fp, $row, $delim);
        }

        // Close file for writing
        fclose($fp);
        db_free_result($result);

        // Open file for downloading
        $app_title = strip_tags(label_decode($Proj->project['app_title']));
        // $app_title = $module->getTitle();
        $download_filename = camelCase(html_entity_decode($app_title, ENT_QUOTES)) . "_UserRoleChanges_" . date("Y-m-d_Hi") . ".csv";

        header('Pragma: anytextexeptno-cache', true);
        header("Content-type: application/csv");
        header("Content-Disposition: attachment; filename=$download_filename");

        // Open file for reading and output to user
        $fp = fopen($filename, 'rb');
        print addBOMtoUTF8(fread($fp, filesize($filename)));

        // Close file and delete it from temp directory
        fclose($fp);
        unlink($filename);

        // Logging
        Logging::logEvent("", Logging::getLogEventTable($projId),"MANAGE",$projId,"project_id = $projId", "Export user role changes (custom)");

    } catch (Exception $e) {
        $module->log("ex: ". $e->getMessage());
    }
}
else
{
    //error
	print $lang['global_01'];
}
