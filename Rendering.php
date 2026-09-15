<?php

namespace CCTC\ConfigurationMonitorModule;

use DateTime;

class Rendering
{
    public static function MakePageSizeSelect($pageSize) : string
    {
        $sel10 = $pageSize == 10 ? "selected" : "";
        $sel25 = $pageSize == 25 ? "selected" : "";
        $sel50 = $pageSize == 50 ? "selected" : "";
        $sel100 = $pageSize == 100 ? "selected" : "";
        $sel250 = $pageSize == 250 ? "selected" : "";

        return "
        <select id='pagesize' name='pagesize' class='x-form-text x-form-field' onchange='onFilterChanged(\"pagesize\")'>
            <option value='10' $sel10>10</option>
            <option value='25' $sel25>25</option>
            <option value='50' $sel50>50</option>
            <option value='100' $sel100>100</option>
            <option value='250' $sel250>250</option>
        </select>";
    }

    public static function MakeRetDirectionSelect($dataDirection) : string
    {
        $descSel = $dataDirection == "desc" ? "selected" : "";
        $ascSel = $dataDirection == "asc" ? "selected" : "";

        return "
        <select id='retdirection' name='retdirection' class='x-form-text x-form-field' onchange='onDirectionChanged()'>
            <option value='desc' $descSel>Descending</option>
            <option value='asc' $ascSel>Ascending</option>
        </select>";
    }

    public static function MakeRoleSelect($roles, $selected, $roleNames) : string
    {
        $anySelected = $selected == null ? "selected": "";
        $usrroles = "<option value='' $anySelected>any user role</option>";
        foreach ($roles as $role) {
            $sel = $selected == $role ? "selected" : "";
            // Role names are not unique, so the id is shown alongside the name and
            // remains the value being filtered on
            $name = htmlspecialchars($roleNames[$role], ENT_QUOTES, 'UTF-8');
            $roleId = htmlspecialchars($role, ENT_QUOTES, 'UTF-8');
            $usrroles .= "<option value='{$roleId}' {$sel}>{$name} ({$roleId})</option>";
        }

        return
            "<select id='role_id' name='role_id' class='x-form-text x-form-field' onchange='onFilterChanged(\"role_id\")' style='max-width: 180px;'>
            {$usrroles}
            </select>";
    }

    public static function MakeActionSelect($actions, $selected) : string
    {
        $anySelected = ($selected === null || $selected === '') ? "selected" : "";
        $options = "<option value='' $anySelected>any action</option>";
        foreach ($actions as $action) {
            $sel = $selected === $action ? "selected" : "";
            $escaped = htmlspecialchars($action, ENT_QUOTES, 'UTF-8');
            $options .= "<option value='{$escaped}' {$sel}>{$escaped}</option>";
        }

        return
            "<select id='action_type' name='action_type' class='x-form-text x-form-field' onchange='onFilterChanged(\"action_type\")' style='max-width: 180px;'>
            {$options}
            </select>";
    }

    public static function MakePrivilegeSelect($privileges, $selected) : string
    {
        $anySelected = $selected == null ? "selected": "";
        $options = "<option value='' $anySelected>any property</option>";
        foreach ($privileges as $privilege) {
            $sel = $selected == $privilege ? "selected" : "";
            $escaped = htmlspecialchars($privilege, ENT_QUOTES, 'UTF-8');
            $options .= "<option value='{$escaped}' {$sel}>{$escaped}</option>";
        }

        return
            "<select id='privilege_filter' name='privilege_filter' class='x-form-text x-form-field' onchange='onFilterChanged(\"privilege_filter\")' style='max-width: 180px;'>
            {$options}
            </select>";
    }

    public static function MakeFieldNameSelect($fieldNames, $selected) : string
    {
        $anySelected = $selected == null ? "selected": "";
        $options = "<option value='' $anySelected>any property</option>";
        foreach ($fieldNames as $fieldName) {
            $sel = $selected == $fieldName ? "selected" : "";
            $escaped = htmlspecialchars($fieldName, ENT_QUOTES, 'UTF-8');
            $options .= "<option value='{$escaped}' {$sel}>{$escaped}</option>";
        }

        return
            "<select id='field_name' name='field_name' class='x-form-text x-form-field' onchange='onFilterChanged(\"field_name\")' style='max-width: 180px;'>
            {$options}
            </select>";
    }

    // The pieces below make up the filter form shared by the three change log pages.
    // Each page arranges them in its own layout; the behaviour lives in assets/js/configMonitor.js.

    // hidden inputs that carry the page, paging and time frame state between requests
    public static function FilterFormHiddenInputs(string $moduleName, string $page, $projId, $totPages, int $pageNum,
        string $defaultTimeFilter, string $oneDayAgo, string $oneWeekAgo, string $oneMonthAgo, string $oneYearAgo) : string
    {
        $pidInput = empty($projId) ? "" : "<input type='hidden' id='pid' name='pid' value='{$projId}'>";
        $totPages = (int)$totPages;

        return "
        <input type='hidden' id='prefix' name='prefix' value='{$moduleName}'>
        <input type='hidden' id='page' name='page' value='{$page}'>
        {$pidInput}
        <input type='hidden' id='totpages' name='totpages' value='{$totPages}'>
        <input type='hidden' id='pagenum' name='pagenum' value='{$pageNum}'>

        <input type='hidden' id='defaulttimefilter' name='defaulttimefilter' value='{$defaultTimeFilter}'>
        <input type='hidden' id='onedayago' name='onedayago' value='{$oneDayAgo}'>
        <input type='hidden' id='oneweekago' name='oneweekago' value='{$oneWeekAgo}'>
        <input type='hidden' id='onemonthago' name='onemonthago' value='{$oneMonthAgo}'>
        <input type='hidden' id='oneyearago' name='oneyearago' value='{$oneYearAgo}'>";
    }

    // table cells for the min and max edit date inputs, each with a button to clear it
    public static function DateRangeCells(string $userDateFormat, ?string $minDate, ?string $maxDate) : string
    {
        return "
                <td><label for='startdt'>Min edit date</label></td>
                <td><input id='startdt' name='startdt' class='x-form-text x-form-field' type='text' data-df='{$userDateFormat}' value='{$minDate}'></td>
                <td><button class='clear-button' type='button' onclick='resetDate(\"startdt\")'><small><i class='fas fa-eraser'></i></small></button></td>
                <td style='width: 100px;'><label for='enddt'>Max edit date</label></td>
                <td><input id='enddt' name='enddt' class='x-form-text x-form-field' type='text' data-df='{$userDateFormat}' value='{$maxDate}'></td>
                <td><button style='margin-left: 0' class='clear-button' type='button' onclick='resetDate(\"enddt\")'><small><i class='fas fa-eraser'></i></small></button></td>";
    }

    // quick links to set the date range, with the selected one highlighted
    public static function TimeFrameButtons(string $defaultTimeFilter, string $style = '') : string
    {
        $buttons = [
            'customrange' => ['setCustomRange()', 'Custom range'],
            'onedayago'   => ['setTimeFrame("onedayago")', 'Past day'],
            'oneweekago'  => ['setTimeFrame("oneweekago")', 'Past week'],
            'onemonthago' => ['setTimeFrame("onemonthago")', 'Past month'],
            'oneyearago'  => ['setTimeFrame("oneyearago")', 'Past year'],
        ];

        $html = "<div class='btn-group bg-white' role='group' style='{$style}'>";
        foreach ($buttons as $timeFilter => [$onClick, $label]) {
            $active = $defaultTimeFilter == $timeFilter ? "active" : "";
            $html .= "
                        <button type='button' class='btn btn-outline-primary btn-xs {$active}' onclick='" . htmlspecialchars($onClick, ENT_QUOTES, 'UTF-8') . "'>{$label}</button>";
        }

        return $html . "
                    </div>";
    }

    // "records x to y of z" for the current page
    public static function PagingInfo(int $skipCount, int $showingCount, int $pageSize, int $totalCount) : string
    {
        $skipFrom = $showingCount == 0 ? 0 : $skipCount + 1;

        // adjust skipTo in cases where last page isn't a full page
        $skipTo = $skipCount + min($showingCount, $pageSize);

        return "records {$skipFrom} to {$skipTo} of {$totalCount}";
    }

    // previous and next page buttons, the paging info and the reset button
    public static function PagingButtons(string $pagingInfo) : string
    {
        return "
            <button id='btnprevpage' type='button' class='btn btn-outline-primary btn-xs mr-2' onclick='prevPage()'>
                <i class='fas fa-arrow-left fa-fw' style='font-size: medium; margin-top: 1px;'></i>
            </button>
            <button id='btnnextpage' type='button' class='btn btn-outline-primary btn-xs mr-4' onclick='nextPage()'>
                <i class='fas fa-arrow-right fa-fw' style='font-size: medium; margin-top: 1px;'></i>
            </button>
            {$pagingInfo}
            <button class='clear-button' style='margin-left: 10px' type='button' onclick='resetForm()'><i class='fas fa-broom'></i> reset</button>";
    }

    // the three CSV export buttons; export-records buttons are disabled when there is nothing to export
    public static function ExportButtons(string $moduleName, $projId, string $tableName) : string
    {
        $buttons = [
            ['current_page', 'export-records', 'Export current page'],
            ['all_pages', 'export-records', 'Export all pages'],
            ['everything', 'export-all', 'Export everything ignoring filters'],
        ];
        $icon = "<img src='" . APP_PATH_WEBROOT . "/Resources/images/xls.gif' style='position: relative;top: -1px;' alt=''>";

        $html = "<div class='ms-auto'>";
        foreach ($buttons as [$exportType, $class, $label]) {
            $html .= "
                <button class='jqbuttonmed ui-button ui-corner-all ui-widget {$class}' type='button' onclick='cleanUpParamsAndRun(\"{$moduleName}\", \"{$projId}\", \"{$exportType}\", \"{$tableName}\")'>
                    {$icon}
                    {$label}
                </button>";
        }

        return $html . "
            </div>";
    }

    // the shared stylesheet and script for the filter form; output after the form so the script can find it
    public static function PageAssets($module) : string
    {
        return "<link rel='stylesheet' href='" . $module->getUrl('assets/css/configMonitor.css') . "'>
<script type='text/javascript' src='" . $module->getUrl('assets/js/configMonitor.js') . "'></script>";
    }

}