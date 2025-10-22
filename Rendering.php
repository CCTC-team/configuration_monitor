<?php

namespace CCTC\ProjectConfigurationChangesModule;

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

    public static function MakeRoleSelect($roles, $selected) : string
    {
        $anySelected = $selected == null ? "selected": "";
        $usrroles = "<option value='' $anySelected>any user role</option>";
        foreach ($roles as $role) {
            $sel = $selected == $role ? "selected" : "";
            $usrroles .= "<option value='{$role}' {$sel}>{$role}</option>";
        }

        return
            "<select id='role_id' name='role_id' class='x-form-text x-form-field' onchange='onFilterChanged(\"role_id\")' style='max-width: 180px;'>
            {$usrroles}
            </select>";
    }

    public static function MakePrivilegeSelect($privileges, $selected) : string
    {
        $anySelected = $selected == null ? "selected": "";
        $options = "<option value='' $anySelected>any privilege</option>";
        foreach ($privileges as $privilege) {
            $sel = $selected == $privilege ? "selected" : "";
            $options .= "<option value='{$privilege}' {$sel}>{$privilege}</option>";
        }

        return
            "<select id='privilege_filter' name='privilege_filter' class='x-form-text x-form-field' onchange='onFilterChanged(\"privilege_filter\")' style='max-width: 180px;'>
            {$options}
            </select>";
    }

    public static function MakeFieldNameSelect($fieldNames, $selected) : string
    {
        $anySelected = $selected == null ? "selected": "";
        $options = "<option value='' $anySelected>any field name</option>";
        foreach ($fieldNames as $fieldName) {
            $sel = $selected == $fieldName ? "selected" : "";
            $options .= "<option value='{$fieldName}' {$sel}>{$fieldName}</option>";
        }

        return
            "<select id='field_name' name='field_name' class='x-form-text x-form-field' onchange='onFilterChanged(\"field_name\")' style='max-width: 180px;'>
            {$options}
            </select>";
    }

}