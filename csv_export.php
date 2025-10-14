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
/** @var $exportType */
/** @var $tableName */

include "getparams.php";

//run the query using the same params as on the index page when the query called
//runForExport means it only returns the actual data requested (and not data for filters)

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

//run the stored proc
$result = GetDbData::GetChangesFromSP($projId, $minDateDb, $maxDateDb, $skipCount, $pageSize, $dataDirection, $tableName, $roleID);
// Set headers
if($tableName == 'user_role_changes') {
    $headers = array("role id", "timestamp", "action", "changed privilege", "old value", "new value");
    $download_filename = "_UserRoleChanges_";
} else {
    $headers = array("timestamp", "action", "changed privilege", "old value", "new value");
    $download_filename = "_ProjectChanges_";
}

// Set file name and path
$filename = APP_PATH_TEMP . date("YmdHis") . '_' . PROJECT_ID . '_' . $tableName . '.csv';

// Begin writing file from query result
$fp = fopen($filename, 'w');

if ($fp && $result)
{
    try {

        $delim = User::getCsvDelimiter();

        // Write headers to file
        fputcsv($fp, $headers, $delim);

        // Set values for this row and write to file
        if ($tableName == 'user_role_changes') {
            foreach ($result["dataChanges"] as $dc) {

                $dcChanges = $module->tableDiff($dc, $tableName);
                if (is_array($dcChanges)) {
                    foreach ($dcChanges as $dc) {
                        $row["id"] = $dc["id"];
                        //timestamp
                        $row["timestamp"] =
                            $dc["timestamp"] == null || $dc["timestamp"] == ""
                                ? ""
                                : DateTime::createFromFormat('YmdHis', $dc["timestamp"])->format($userDateFormat);
                        $row["action"] = $dc["action"];
                        $row["privilege"] = $dc["privilege"];
                        $row["oldValue"] = $dc["oldValue"];
                        $row["newValue"] = $dc["newValue"];
                        fputcsv($fp, $row, $delim);
                    }
                }
            }
        } else {
            foreach ($result["dataChanges"] as $dc) {

                $dcChanges = $module->tableDiff($dc, $tableName);
                if (is_array($dcChanges)) {
                    foreach ($dcChanges as $dc) {
                        //timestamp
                        $row["timestamp"] =
                            $dc["timestamp"] == null || $dc["timestamp"] == ""
                                ? ""
                                : DateTime::createFromFormat('YmdHis', $dc["timestamp"])->format($userDateFormat);
                        $row["action"] = $dc["action"];
                        $row["privilege"] = $dc["privilege"];
                        $row["oldValue"] = $dc["oldValue"];
                        $row["newValue"] = $dc["newValue"];
                        fputcsv($fp, $row, $delim);
                    }
                }
            }
        }

        // Close file for writing
        fclose($fp);
        db_free_result($result);

        // Open file for downloading
        $app_title = strip_tags(label_decode($Proj->project['app_title']));
        // $app_title = $module->getTitle();
        $download_filename = camelCase(html_entity_decode($app_title, ENT_QUOTES)) . $download_filename . date("Y-m-d_Hi") . ".csv";

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
