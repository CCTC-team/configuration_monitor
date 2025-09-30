<?php

require_once APP_PATH_DOCROOT . "/Config/init_project.php";
$lang = Language::getLanguage('English');

global $Proj;
$project_id = $Proj->id;
global $module;
$modName = $module->getModuleDirectoryName();

// require_once dirname(APP_PATH_DOCROOT, 1) . "/modules/$modName/DataEntryLogModule.php";
// require_once dirname(APP_PATH_DOCROOT, 1) . "/modules/$modName/GetDbData.php";

// require_once dirname(APP_PATH_DOCROOT, 1) . "/modules/$modName/DataChange.php";
// require_once dirname(APP_PATH_DOCROOT, 1) . "/modules/$modName/Utility.php";
// require_once dirname(APP_PATH_DOCROOT, 1) . "/modules/$modName/Rendering.php";
// require_once APP_PATH_DOCROOT . "/Classes/Records.php";
// require_once APP_PATH_DOCROOT . "/Classes/RCView.php";
// require_once APP_PATH_DOCROOT . "/Classes/DateTimeRC.php";

use CCTC\ProjectConfigurationChangesModule\GetDbData;
use CCTC\ProjectConfigurationChangesModule\DataEntryLogModule;

// Increase memory limit in case needed for intensive processing
//System::increaseMemory(2048);

// File: getparams.php
/** @var $projectId */
/** @var $maxDayHour */
/** @var $dayOrHour */
/** @var $skipCount */
/** @var $pageSize */
/** @var $dataDirection */

include "getparams.php";

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

//set all filters to null
if($exportType == 'everything') {
    $minDateDb = null;
    $maxDateDb = null;
    $role_id = null;
}



//run the stored proc
$result = GetDbData::GetDataLogsFromSP(
    $skipCount, $pageSize, $dataDirection, $recordId, $minDateDb, $maxDateDb, $dagUser,
    $editor, $dataevnt, $datagrp, $dataarm, $datainstance, $logDescription, $changeReason, $datafrm,
    $fieldNameOrLabel, $newDataValue, $excludeFieldNameRegex);

// Set headers
$headers = array("timestamp","role id","changed privilege","old value", "new value", "action");

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

            //timestamp
            $row["ts"] =
                $dc->timestamp == null || $dc->timestamp == ""
                    ? ""
                    : DateTime::createFromFormat('YmdHis', $dc->timestamp)->format('Y-m-d H:i:s');

            //add rest of columns
            $row["role_id"] = $dc->role_id;
            $row["record"] = $dc->recordId;
            $row["group id"] = $dc->groupId;
            $row["group name"] = $dc->groupName;
            $row["event id"] = $dc->eventId;
            $row["event name"] = $dc->eventName;
            $row["arm number"] = $dc->armNumber;
            $row["arm name"] = $dc->armName;
            $row["instance"] = $dc->instance;
            $row["form name"] = $dc->formName;
            $row["field"] = $dc->fieldName;
            $row["field label"] = $dc->fieldLabel;
            $row["value"] = $dc->newValue;
            $row["reason for change"] = $dc->reason;
            $row["action"] = $dc->description;

            fputcsv($fp, $row, $delim);
        }

        // Close file for writing
        fclose($fp);
        db_free_result($result);

        // Open file for downloading
        $app_title = $this->getTitle(); // strip_tags(label_decode($Proj->project['app_title']));
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
        Logging::logEvent("", Logging::getLogEventTable($project_id),"MANAGE",$project_id,"project_id = $project_id", "Export data entry logging (custom)");

    } catch (Exception $e) {
        $module->log("ex: ". $e->getMessage());
    }
}
else
{
    //error
	print $lang['global_01'];
}
