<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
//
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Baba Buehler <baba@babaz.com>                               |
// |                                                                      |
// +----------------------------------------------------------------------+
//
// $Id: TimeZone.php,v 1.6 2004/05/16 12:48:06 pajoye Exp $
//
// Date_TimeZone Class
//

/**
 * TimeZone representation class, along with time zone information data.
 *
 * TimeZone representation class, along with time zone information data.
 * The default timezone is set from the first valid timezone id found
 * in one of the following places, in this order: <br>
 * 1) global $_DATE_TIMEZONE_DEFAULT<br>
 * 2) system environment variable PHP_TZ<br>
 * 3) system environment variable TZ<br>
 * 4) the result of date('T')<br>
 * If no valid timezone id is found, the default timezone is set to 'UTC'.
 * You may also manually set the default timezone by passing a valid id to
 * Date_TimeZone::setDefault().<br>
 *
 * This class includes time zone data (from zoneinfo) in the form of a global array, $_DATE_TIMEZONE_DATA.
 *
 *
 * @author Baba Buehler <baba@babaz.com>
 * @package Date
 * @access public
 * @version 1.0
 */
class Date_TimeZone
{
    /**
     * Time Zone ID of this time zone
     * @var string
     */
    var $id;
    /**
     * Long Name of this time zone (ie Central Standard Time)
     * @var string
     */
    var $longname;
    /**
     * Short Name of this time zone (ie CST)
     * @var string
     */
    var $shortname;
    /**
     * true if this time zone observes daylight savings time
     * @var boolean
     */
    var $hasdst;
    /**
     * DST Long Name of this time zone
     * @var string
     */
    var $dstlongname;
    /**
     * DST Short Name of this timezone
     * @var string
     */
    var $dstshortname;
    /**
     * offset, in milliseconds, of this timezone
     * @var int
     */
    var $offset;

    /**
     * System Default Time Zone
     * @var object Date_TimeZone
     */
    var $default;

    var $daylightTime=false;


    /**
     * Constructor
     *
     * Creates a new Date::TimeZone object, representing the time zone
     * specified in $id.  If the supplied ID is invalid, the created
     * time zone is UTC.
     *
     * @access public
     * @param string $id the time zone id
     * @return object Date_TimeZone the new Date_TimeZone object
     */
    function Date_TimeZone( $id, $dst=0 )
    {
        global $_DATE_TIMEZONE_DATA;

        if(Date_TimeZone::isValidID($id))
        {
            $this->id = $id;
            $this->longname = $_DATE_TIMEZONE_DATA[$id]['longname'];
            $this->shortname = $_DATE_TIMEZONE_DATA[$id]['shortname'];
            $this->offset = $_DATE_TIMEZONE_DATA[$id]['offset'];

            if($_DATE_TIMEZONE_DATA[$id]['hasdst']) {
                $this->hasdst = true;
                $this->dstlongname = $_DATE_TIMEZONE_DATA[$id]['dstlongname'];
                $this->dstshortname = $_DATE_TIMEZONE_DATA[$id]['dstshortname'];
            } else {
                $this->hasdst = false;
                $this->dstlongname = $this->longname;
                $this->dstshortname = $this->shortname;
            }

            $this->daylightTime = ( $this->hasdst && ( $dst != 0 ) ) ? true : false;

        } else {
            $this->id = '2';
            $this->longname = $_DATE_TIMEZONE_DATA[$this->id]['longname'];
            $this->shortname = $_DATE_TIMEZONE_DATA[$this->id]['shortname'];
            $this->hasdst = $_DATE_TIMEZONE_DATA[$this->id]['hasdst'];
            $this->offset = $_DATE_TIMEZONE_DATA[$this->id]['offset'];
        }
    }

    /**
     * Return a TimeZone object representing the system default time zone
     *
     * Return a TimeZone object representing the system default time zone,
     * which is initialized during the loading of TimeZone.php.
     *
     * @access public
     * @return object Date_TimeZone the default time zone
     */
    static function getDefault()
    {
        global $_DATE_TIMEZONE_DEFAULT;
        return new Date_TimeZone($_DATE_TIMEZONE_DEFAULT);
    }

    /**
     * Sets the system default time zone to the time zone in $id
     *
     * Sets the system default time zone to the time zone in $id
     *
     * @access public
     * @param string $id the time zone id to use
     */
    static function setDefault($id)
    {
        global $_DATE_TIMEZONE_DEFAULT;
        if(Date_TimeZone::isValidID($id)) {
            $_DATE_TIMEZONE_DEFAULT = $id;
        }
    }

    /**
     * Tests if given id is represented in the $_DATE_TIMEZONE_DATA time zone data
     *
     * Tests if given id is represented in the $_DATE_TIMEZONE_DATA time zone data
     *
     * @access public
     * @param string $id the id to test
     * @return boolean true if the supplied ID is valid
     */
    static function isValidID($id)
    {
        global $_DATE_TIMEZONE_DATA;
        if(isset($_DATE_TIMEZONE_DATA[$id])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Is this time zone equal to another
     *
     * Tests to see if this time zone is equal (ids match)
     * to a given Date_TimeZone object.
     *
     * @access public
     * @param object Date_TimeZone $tz the timezone to test
     * @return boolean true if this time zone is equal to the supplied time zone
     */
    function isEqual($tz)
    {
        if(strcasecmp($this->id, $tz->id) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Is this time zone equivalent to another
     *
     * Tests to see if this time zone is equivalent to
     * a given time zone object.  Equivalence in this context
     * is defined by the two time zones having an equal raw
     * offset and an equal setting of "hasdst".  This is not true
     * equivalence, as the two time zones may have different rules
     * for the observance of DST, but this implementation does not
     * know DST rules.
     *
     * @access public
     * @param object Date_TimeZone $tz the timezone object to test
     * @return boolean true if this time zone is equivalent to the supplied time zone
     */
    function isEquivalent($tz)
    {
        if($this->offset == $tz->offset && $this->hasdst == $tz->hasdst) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns true if this zone observes daylight savings time
     *
     * Returns true if this zone observes daylight savings time
     *
     * @access public
     * @return boolean true if this time zone has DST
     */
    function hasDaylightTime()
    {
        return $this->hasdst;
    }

    /**
     * Is the given date/time in DST for this time zone
     *
     * Attempts to determine if a given Date object represents a date/time
     * that is in DST for this time zone.  WARNINGS: this basically attempts to
     * "trick" the system into telling us if we're in DST for a given time zone.
     * This uses putenv() which may not work in safe mode, and relies on unix time
     * which is only valid for dates from 1970 to ~2038.  This relies on the
     * underlying OS calls, so it may not work on Windows or on a system where
     * zoneinfo is not installed or configured properly.
     *
     * @access public
     * @param object Date $date the date/time to test
     * @return boolean true if this date is in DST for this time zone
     */
    function inDaylightTime($date)
    {
	return $this->daylightTime;
    }

    /**
     * Get the DST offset for this time zone
     *
     * Returns the DST offset of this time zone, in milliseconds,
     * if the zone observes DST, zero otherwise.  Currently the
     * DST offset is hard-coded to one hour.
     *
     * @access public
     * @return int the DST offset, in milliseconds or zero if the zone does not observe DST
     */
    function getDSTSavings()
    {
        if($this->hasdst) {
            return 3600000;
        } else {
            return 0;
        }
    }

    /**
     * Get the DST-corrected offset to UTC for the given date
     *
     * Attempts to get the offset to UTC for a given date/time, taking into
     * account daylight savings time, if the time zone observes it and if
     * it is in effect.  Please see the WARNINGS on Date::TimeZone::inDaylightTime().
     *
     *
     * @access public
     * @param object Date $date the Date to test
     * @return int the corrected offset to UTC in milliseconds
     */
    function getOffset($date)
    {
        if($this->inDaylightTime($date)) {
            return $this->offset + $this->getDSTSavings();
        } else {
            return $this->offset;
        }
    }

    /**
     * Returns the list of valid time zone id strings
     *
     * Returns the list of valid time zone id strings
     *
     * @access public
     * @return mixed an array of strings with the valid time zone IDs
     */
    function getAvailableIDs()
    {
        global $_DATE_TIMEZONE_DATA;
        return array_keys($_DATE_TIMEZONE_DATA);
    }

    /**
     * Returns the id for this time zone
     *
     * Returns the time zone id  for this time zone, i.e. "America/Chicago"
     *
     * @access public
     * @return string the id
     */
    function getID()
    {
        return $this->id;
    }

    /**
     * Returns the long name for this time zone
     *
     * Returns the long name for this time zone,
     * i.e. "Central Standard Time"
     *
     * @access public
     * @return string the long name
     */
    function getLongName()
    {
        return $this->longname;
    }

    /**
     * Returns the short name for this time zone
     *
     * Returns the short name for this time zone, i.e. "CST"
     *
     * @access public
     * @return string the short name
     */
    function getShortName()
    {
        return $this->shortname;
    }

    /**
     * Returns the DST long name for this time zone
     *
     * Returns the DST long name for this time zone, i.e. "Central Daylight Time"
     *
     * @access public
     * @return string the daylight savings time long name
     */
    function getDSTLongName()
    {
        return $this->dstlongname;
    }

    /**
     * Returns the DST short name for this time zone
     *
     * Returns the DST short name for this time zone, i.e. "CDT"
     *
     * @access public
     * @return string the daylight savings time short name
     */
    function getDSTShortName()
    {
        return $this->dstshortname;
    }

    /**
     * Returns the raw (non-DST-corrected) offset from UTC/GMT for this time zone
     *
     * Returns the raw (non-DST-corrected) offset from UTC/GMT for this time zone
     *
     * @access public
     * @return int the offset, in milliseconds
     */
    function getRawOffset()
    {
        return $this->offset;
    }

} // Date_TimeZone



$GLOBALS['_DATE_TIMEZONE_DATA'] =

array(

'39' => array(
				'offset' => -43200000,
				'longname' => "Eniwetok, Kwajalein",
				'shortname' => '(GMT-12:00)',
				'hasdst' => false ),

'16' => array(
				'offset' => -39600000,
				'longname' => "Midway Island, Samoa",
				'shortname' => '(GMT-11:00)',
				'hasdst' => false ),

'15' => array(
				'offset' => -36000000,
				'longname' => "Hawaii",
				'shortname' => '(GMT-10:00)',
				'hasdst' => false ),

'14' => array(
				'offset' => -32400000,
				'longname' => "Alaska",
				'shortname' => '(GMT-09:00)',
				'hasdst' => true,
				'dstlongname' => "Alaska Daylight Time",
				'dstshortname' => 'AKDT' ),

'13' => array(
				'offset' => -28800000,
				'longname' => "Pacific Time (US and Canada); Tijuana",
				'shortname' => '(GMT-08:00)',
				'hasdst' => true,
				'dstlongname' => "Pacific Daylight Time",
				'dstshortname' => 'PDT' ),

'38' => array(
				'offset' => -25200000,
				'longname' => "Arizona",
				'shortname' => '(GMT-07:00)',
				'hasdst' => false ),

'12' => array(
				'offset' => -25200000,
				'longname' => "Mountain Time (US and Canada)",
				'shortname' => '(GMT-07:00)',
				'hasdst' => true,
				'dstlongname' => "Mountain Daylight Time",
				'dstshortname' => 'MDT' ),

'55' => array(
				'offset' => -21600000,
				'longname' => "Central America",
				'shortname' => '(GMT-06:00)',
				'hasdst' => false ),

'11' => array(
				'offset' => -21600000,
				'longname' => 'Central Time (US and Canada)',
			        'shortname' => '(GMT-06:00)',
			        'hasdst' => true,
        			'dstlongname' => 'DST Central Time (US and Canada)',
			        'dstshortname' => 'DST (GMT-06:00)'),
'37' => array(
				'offset' => -21600000,
				'longname' => 'Mexico City',
			        'shortname' => '(GMT-06:00)',
			        'hasdst' => true,
        			'dstlongname' => 'DST Mexico City',
			        'dstshortname' => 'DST (GMT-06:00)' ),
'36' => array(
				'offset' => -21600000,
				'longname' => 'Saskatchewan',
			        'shortname' => '(GMT-06:00)',
			        'hasdst' => false ),

'35' => array(
				'offset' => -18000000,
				'longname' => 'Bogota, Lima, Quito',
			        'shortname' => '(GMT-05:00)',
			        'hasdst' => false ),
'10' => array(
				'offset' => -18000000,
				'longname' => 'Eastern Time (US and Canada)',
			        'shortname' => '(GMT-05:00)',
			        'hasdst' => true,
        			'dstlongname' => 'DST Eastern Time (US and Canada)',
			        'dstshortname' => 'DST (GMT-05:00)'),
'34' => array(
				'offset' => -18000000,
				'longname' => 'Indiana (East)',
			        'shortname' => '(GMT-05:00)',
			        'hasdst' => false ),
'9' => array(
				'offset' => '-14400000',
				'longname' => 'Atlantic Time (Canada)',
			        'shortname' => '(GMT-04:00)',
			        'hasdst' => true,
        			'dstlongname' => 'DST Atlantic Time (Canada)',
			        'dstshortname' => 'DST (GMT-04:00)'),
'33' => array(
				'offset' => '-14400000',
				'longname' => 'Caracas, La Paz',
			        'shortname' => '(GMT-04:00)',
			        'hasdst' => false ),
'65' => array(
				'offset' => '-14400000',
				'longname' => 'Santiago',
			        'shortname' => '(GMT-04:00)',
			        'hasdst' => true,
        			'dstlongname' => 'DST Santiago',
			        'dstshortname' => 'DST (GMT-04:00)'),
'28' => array(
				'offset' => '-12600000',
				'longname' => 'Newfoundland',
			        'shortname' => '(GMT-03:30)',
			        'hasdst' => true,
        			'dstlongname' => 'DST Newfoundland',
			        'dstshortname' => 'DST (GMT-03:30)'),
'8' => array(
				'offset' => '-10800000',
				'longname' => 'Brasilia',
			        'shortname' => '(GMT-03:00)',
			        'hasdst' => true,
        			'dstlongname' => 'DST Brasilia',
			        'dstshortname' => 'DST (GMT-03:00)'),
'32' => array(
				'offset' => '-10800000',
				'longname' => 'Buenos Aires, Georgetown',
			        'shortname' => '(GMT-03:00)',
			        'hasdst' => false ),
'60' => array(
				'offset' => '-10800000',
				'longname' => 'Greenland',
			        'shortname' => '(GMT-03:00)',
			        'hasdst' => true,
        			'dstlongname' => 'DST Greenland',
			        'dstshortname' => 'DST (GMT-03:00)'),
'30' => array(
				'offset' => '-7200000',
				'longname' => 'Mid-Atlantic',
			        'shortname' => '(GMT-02:00)',
			        'hasdst' => true,
        			'dstlongname' => 'DST Mid-Atlantic',
			        'dstshortname' => 'DST (GMT-02:00)'),
'29' => array(
				'offset' => '-3600000',
				'longname' => 'Azores',
			        'shortname' => '(GMT-01:00)',
			        'hasdst' => true,
        			'dstlongname' => 'DST Azores',
			        'dstshortname' => 'DST (GMT-01:00)'),
'53' => array(
				'offset' => '-3600000',
				'longname' => 'Cape Verde Is.',
			        'shortname' => '(GMT-01:00)',
			        'hasdst' => false ),
'31' => array(
				'offset' => '-3600000',
				'longname' => 'Casablanca, Monrovia',
			        'shortname' => '(GMT)',
			        'hasdst' => false ),

'2' => array(
				'offset' => '-3600000',
				'longname' => 'Dublin, Edinburgh, Lisbon, London',
			        'shortname' => '(GMT)',
			        'hasdst' => true ),

'6' => array(
				'offset' => '3600000',
				'longname' => 'Belgrade, Bratislava, Budapest, Ljubljana, Prague',
			        'shortname' => '(GMT+01:00)',
			        'hasdst' => true,
        			'dstlongname' => 'DST Belgrade, Bratislava, Budapest, Ljubljana, Prague',
			        'dstshortname' => 'DST (GMT+01:00)'),
'3' => array(
				'offset' => '3600000',
				'longname' => 'Brussels, Copenhagen, Madrid, Paris',
			        'shortname' => '(GMT+01:00)',
			        'hasdst' => true,
        			'dstlongname' => 'DST Brussels, Copenhagen, Madrid, Paris',
			        'dstshortname' => 'DST (GMT+01:00)'),
'57' => array(
				'offset' => '3600000',
				'longname' => 'Sarajevo, Skopje, Sofija, Vilnius, Warsaw, Zagreb',
			        'shortname' => '(GMT+01:00)',
			        'hasdst' => true,
        			'dstlongname' => 'DST Sarajevo, Skopje, Sofija, Vilnius, Warsaw, Zagreb',
			        'dstshortname' => 'DST (GMT+01:00)'),
'69' => array(
				'offset' => '3600000',
				'longname' => 'West Central Africa',
			        'shortname' => '(GMT+01:00)',
			        'hasdst' => false ),
'7' => array(
				'offset' => '7200000',
				'longname' => 'Athens, Istanbul, Minsk',
			        'shortname' => '(GMT+02:00)',
			        'hasdst' => true,
        			'dstlongname' => 'DST Athens, Istanbul, Minsk',
			        'dstshortname' => 'DST (GMT+02:00)'),
'5' => array(
				'offset' => '7200000',
				'longname' => 'Bucharest',
			        'shortname' => '(GMT+02:00)',
			        'hasdst' => true,
        			'dstlongname' => 'DST Bucharest',
			        'dstshortname' => 'DST (GMT+02:00)'),
'49' => array(
				'offset' => '7200000',
				'longname' => 'Cairo',
			        'shortname' => '(GMT+02:00)',
			        'hasdst' => true,
        			'dstlongname' => 'DST Cairo',
			        'dstshortname' => 'DST (GMT+02:00)'),
'50' => array(
				'offset' => '7200000',
				'longname' => 'Harare, Pretoria',
			        'shortname' => '(GMT+02:00)',
			        'hasdst' => false ),
'4' => array(
				'offset' => '3600000',
				'longname' => 'Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna',
			        'shortname' => '(GMT+01:00)',
			        'hasdst' => true,
        			'dstlongname' => 'DST Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna',
			        'dstshortname' => 'DST (GMT+01:00)'),
'59' => array(
				'offset' => '7200000',
				'longname' => 'Helsinki, Riga, Tallinn',
			        'shortname' => '(GMT+02:00)',
			        'hasdst' => true,
        			'dstlongname' => 'DST Helsinki, Riga, Tallinn',
			        'dstshortname' => 'DST (GMT+02:00)'),
'27' => array(
				'offset' => '7200000',
				'longname' => 'Jerusalem',
			        'shortname' => '(GMT+02:00)',
			        'hasdst' => true,
        			'dstlongname' => 'DST Jerusalem',
			        'dstshortname' => 'DST (GMT+02:00)'),
'26' => array(
				'offset' => '10800000',
				'longname' => 'Baghdad',
			        'shortname' => '(GMT+03:00)',
			        'hasdst' => true,
        			'dstlongname' => 'DST Baghdad',
			        'dstshortname' => 'DST (GMT+03:00)'),
'74' => array(
				'offset' => '10800000',
				'longname' => 'Kuwait, Riyadh',
			        'shortname' => '(GMT+03:00)',
			        'hasdst' => false ),
'51' => array(
				'offset' => '10800000',
				'longname' => 'Moscow, St. Petersburg, Volgograd',
			        'shortname' => '(GMT+03:00)',
			        'hasdst' => true,
        			'dstlongname' => 'DST Moscow, St. Petersburg, Volgograd',
			        'dstshortname' => 'DST (GMT+03:00)'),
'56' => array(
				'offset' => '10800000',
				'longname' => 'Nairobi',
			        'shortname' => '(GMT+03:00)',
			        'hasdst' => false ),
'25' => array(
				'offset' => '12600000',
				'longname' => 'Tehran',
			        'shortname' => '(GMT+03:30)',
			        'hasdst' => true,
        			'dstlongname' => 'DST Tehran',
			        'dstshortname' => 'DST (GMT+03:30)'),
'24' => array(
				'offset' => '14400000',
				'longname' => 'Abu Dhabi, Muscat',
			        'shortname' => '(GMT+04:00)',
			        'hasdst' => false ),

'54' => array(
				'offset' => '14400000',
				'longname' => 'Baku, Tbilisi, Yerevan',
			        'shortname' => '(GMT+04:00)',
			        'hasdst' => true,
        			'dstlongname' => 'DST Baku, Tbilisi, Yerevan',
			        'dstshortname' => 'DST (GMT+04:00)'),
'48' => array(
				'offset' => '16200000',
				'longname' => 'Kabul',
			        'shortname' => '(GMT+04:30)',
			        'hasdst' => true,
        			'dstlongname' => 'DST Kabul',
			        'dstshortname' => 'DST (GMT+04:30)'),
'58' => array(
				'offset' => '18000000',
				'longname' => 'Ekaterinburg',
			        'shortname' => '(GMT+05:00)',
			        'hasdst' => true,
        			'dstlongname' => 'DST Ekaterinburg',
			        'dstshortname' => 'DST (GMT+05:00)'),
'47' => array(
				'offset' => '18000000',
				'longname' => 'Islamabad, Karachi, Tashkent',
			        'shortname' => '(GMT+05:00)',
			        'hasdst' => false ),
'23' => array(
				'offset' => '19800000',
				'longname' => 'Calcutta, Chennai, Mumbai, New Delhi',
			        'shortname' => '(GMT+05:30)',
			        'hasdst' => false ),
'62' => array(
				'offset' => '20700000',
				'longname' => 'Kathmandu',
			        'shortname' => '(GMT+05:45)',
			        'hasdst' => false ),
'46' => array(
				'offset' => '21600000',
				'longname' => 'Almaty, Novosibirsk',
			        'shortname' => '(GMT+06:00)',
			        'hasdst' => true,
        			'dstlongname' => 'DST Almaty, Novosibirsk',
			        'dstshortname' => 'DST (GMT+06:00)'),
'71' => array(
				'offset' => '21600000',
				'longname' => 'Astana, Dhaka',
			        'shortname' => '(GMT+06:00)',
			        'hasdst' => false ),
'66' => array(
				'offset' => '21600000',
				'longname' => 'Sri Jayawardenepura',
			        'shortname' => '(GMT+06:00)',
			        'hasdst' => false ),
'61' => array(
				'offset' => '23400000',
				'longname' => 'Rangoon',
			        'shortname' => '(GMT+06:30)',
			        'hasdst' => false ),
'22' => array(
				'offset' => '25200000',
				'longname' => 'Bangkok, Hanoi, Jakarta',
			        'shortname' => '(GMT+07:00)',
			        'hasdst' => false ),
'64' => array(
				'offset' => '25200000',
				'longname' => 'Krasnoyarsk',
			        'shortname' => '(GMT+07:00)',
			        'hasdst' => true,
        			'dstlongname' => 'DST Krasnoyarsk',
			        'dstshortname' => 'DST (GMT+07:00)'),
'45' => array(
				'offset' => '28800000',
				'longname' => 'Beijing, Chongqing, Hong Kong, Urumqi',
			        'shortname' => '(GMT+08:00)',
			        'hasdst' => false ),
'63' => array(
				'offset' => '28800000',
				'longname' => 'Irkutsk, Ulaan Bataar',
			        'shortname' => '(GMT+08:00)',
			        'hasdst' => true,
        			'dstlongname' => 'DST Irkutsk, Ulaan Bataar',
			        'dstshortname' => 'DST (GMT+08:00)'),
'21' => array(
				'offset' => '28800000',
				'longname' => 'Kuala Lumpur, Singapore',
			        'shortname' => '(GMT+08:00)',
			        'hasdst' => false ),
'73' => array(
				'offset' => '28800000',
				'longname' => 'Perth',
			        'shortname' => '(GMT+08:00)',
			        'hasdst' => false ),
'75' => array(
				'offset' => '28800000',
				'longname' => 'Taipei',
			        'shortname' => '(GMT+08:00)',
			        'hasdst' => false ),
'20' => array(
				'offset' => '32400000',
				'longname' => 'Osaka, Sapporo, Tokyo',
			        'shortname' => '(GMT+09:00)',
			        'hasdst' => false ),
'72' => array(
				'offset' => '32400000',
				'longname' => 'Seoul',
			        'shortname' => '(GMT+09:00)',
			        'hasdst' => false ),
'70' => array(
				'offset' => '32400000',
				'longname' => 'Yakutsk',
			        'shortname' => '(GMT+09:00)',
			        'hasdst' => true,
        			'dstlongname' => 'DST Yakutsk',
			        'dstshortname' => 'DST (GMT+09:00)'),
'19' => array(
				'offset' => '34200000',
				'longname' => 'Adelaide',
			        'shortname' => '(GMT+09:30)',
			        'hasdst' => true,
        			'dstlongname' => 'DST Adelaide',
			        'dstshortname' => 'DST (GMT+09:30)'),
'44' => array(
				'offset' => '34200000',
				'longname' => 'Darwin',
			        'shortname' => '(GMT+09:30)',
			        'hasdst' => false ),
'18' => array(
				'offset' => '36000000',
				'longname' => 'Brisbane',
			        'shortname' => '(GMT+10:00)',
			        'hasdst' => false ),
'76' => array(
				'offset' => '36000000',
				'longname' => 'Canberra, Melbourne, Sydney',
			        'shortname' => '(GMT+10:00)',
			        'hasdst' => true,
        			'dstlongname' => 'DST Canberra, Melbourne, Sydney',
			        'dstshortname' => 'DST (GMT+10:00)'),
'43' => array(
				'offset' => '36000000',
				'longname' => 'Guam, Port Moresby',
			        'shortname' => '(GMT+10:00)',
			        'hasdst' => false ),
'42' => array(
				'offset' => '36000000',
				'longname' => 'Hobart',
			        'shortname' => '(GMT+10:00)',
			        'hasdst' => true,
        			'dstlongname' => 'DST Hobart',
			        'dstshortname' => 'DST (GMT+10:00)'),
'68' => array(
				'offset' => '36000000',
				'longname' => 'Vladivostok',
			        'shortname' => '(GMT+10:00)',
			        'hasdst' => true,
        			'dstlongname' => 'DST Vladivostok',
			        'dstshortname' => 'DST (GMT+10:00)'),
'41' => array(
				'offset' => '39600000',
				'longname' => 'Magadan',
			        'shortname' => '(GMT+11:00)',
			        'hasdst' => true,
        			'dstlongname' => 'DST Magadan',
			        'dstshortname' => 'DST (GMT+11:00)'),
'41' => array(
				'offset' => '39600000',
				'longname' => 'Solomon Is., New Caledonia',
			        'shortname' => '(GMT+11:00)',
			        'hasdst' => false ),
'17' => array(
				'offset' => '43200000',
				'longname' => 'Auckland, Wellington',
			        'shortname' => '(GMT+12:00)',
			        'hasdst' => true,
        			'dstlongname' => 'DST Auckland, Wellington',
			        'dstshortname' => 'DST (GMT+12:00)'),
'40' => array(
				'offset' => '43200000',
				'longname' => 'Kamchatka',
			        'shortname' => '(GMT+12:00)',
			        'hasdst' => true,
        			'dstlongname' => 'DST Kamchatka',
			        'dstshortname' => 'DST (GMT+12:00)'),
'40' => array(
				'offset' => '43200000',
				'longname' => 'Fiji, Marshall Is.',
			        'shortname' => '(GMT+12:00)',
			        'hasdst' => false )
);

//
// Initialize default timezone
//  First try _DATE_TIMEZONE_DEFAULT global,
//  then PHP_TZ environment var, then TZ environment var
//
if(isset($_DATE_TIMEZONE_DEFAULT)
    && Date_TimeZone::isValidID($_DATE_TIMEZONE_DEFAULT)
) {
    Date_TimeZone::setDefault($_DATE_TIMEZONE_DEFAULT);
} elseif (getenv('PHP_TZ') && Date_TimeZone::isValidID(getenv('PHP_TZ'))) {
    Date_TimeZone::setDefault(getenv('PHP_TZ'));
} elseif (getenv('TZ') && Date_TimeZone::isValidID(getenv('TZ'))) {
    Date_TimeZone::setDefault(getenv('TZ'));
} elseif (Date_TimeZone::isValidID(date('T'))) {
    Date_TimeZone::setDefault(date('T'));
} else {
    Date_TimeZone::setDefault('UTC');
}
//
// END
?>
