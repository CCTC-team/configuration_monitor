<?php

namespace CCTC\ProjectConfigurationChangesModule;

use DateTime;
use DateTimeRC;

require_once APP_PATH_DOCROOT . "/Classes/DateTimeRC.php";


class Utility {

    public static function getBaseUrl() : string
    {
        global $module;

        //returns something like https://localhost:8443/redcap_v13.8.1/ExternalModules/?prefix=log_helper&page=somepage&pid=22
        $url = $module->getUrl("somepage.php");

        //use regex to pull everything prior to the ExternalModules part
        $basePat = "/https:\/\/.*(?=\/ExternalModules)/";
        preg_match($basePat, $url, $urlMatches);

        return $urlMatches[0];
    }

    //now
    public static function Now() : DateTime
    {
        return date_create(date('Y-m-d H:i:s'));
    }

    //returns the date time now adjusted with the given modifier
    public static function NowAdjusted(?string $modifier) : string
    {
        if($modifier == null) {
            return self::Now()->format(self::UserDateTimeFormatNoSeconds());
        }

        try {
            return self::DateTimeNoSecondsInUserFormatAsString(self::Now()->modify($modifier));
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    //default min date
    public static function DefaultMinDate() : DateTime
    {
        return date_create(date("2022-01-01 00:00:00"));
    }

    // returns a nullable string date as a format compatible with the timestamp function
    // returns null if null given
    public static function DateStringToDbFormat(?string $date) : ?string
    {
        if($date == null) return null;

        $dateTime = DateTime::createFromFormat(self::UserDateTimeFormatNoSeconds(), $date);
        return $dateTime->format('YmdHis');
    }
}