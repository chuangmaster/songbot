<?php


class UtilityService {

    /**
     * get date and time, default timezone is Asia Taipei
     * @param String $timezone_identifier timezones default Asia Taipei and more refs http://php.net/manual/en/timezones.asia.php
     * @return String datetime
     */
    public static function getDateTime($timezone_identifier = "Asia/Taipei") {
        date_default_timezone_set($timezone_identifier);
        $dateTime = date("Y-m-d h:i:s");
        return $dateTime;
    }

}
