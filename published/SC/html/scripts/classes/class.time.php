<?php
require_once('Date.php');//PEAR class
class Time
{

	private static $serverTimeZoneID;
	private static $serverTimeZoneDST;
	private static $userTimeZoneID;
	private static $userTimeZoneDST;
	private static $displayTemplate;
	private static $inited;
	private static $enabledTimeZones;
	private static function init()
	{
		//get server time zone
		//
		Time::$userTimeZoneID = defined('CONF_STOREFRONT_TIME_ZONE')?CONF_STOREFRONT_TIME_ZONE:null;//58;//GMT +05:00 /*USER_TIME_ZONE_ID*/
		Time::$userTimeZoneDST = defined('CONF_STOREFRONT_TIME_ZONE_DST')?CONF_STOREFRONT_TIME_ZONE:0;
		$U_ID = sc_getSessionData('U_ID');

		if($U_ID&&SystemSettings::is_backend()){
			$dbq = "SELECT `NAME`,`VALUE` FROM `USER_SETTINGS` WHERE U_ID=? AND `NAME` IN ('TIME_ZONE_ID','TIME_ZONE_DST')";
			if($settings = db_phquery_fetch(DBRFETCH_ASSOC_ALL,$dbq,$U_ID)){
				foreach($settings as $row){
					switch($row['NAME']){
						case 'TIME_ZONE_ID':
							Time::$userTimeZoneID = $row['VALUE'];
							break;
						case 'TIME_ZONE_DST':
							Time::$userTimeZoneDST = $row['VALUE'];
							break;
					}
				}
			}

		}

		Time::$serverTimeZoneID = SystemSettings::get('SERVER_TIME_ZONE_ID');
		Time::$serverTimeZoneDST = SystemSettings::get('SERVER_TIME_ZONE_DST');
		Time::$displayTemplate = (strcmp(CONF_DATE_FORMAT,"DD.MM.YYYY")==0) ? "d.m.Y H:i:s" : "m/d/Y h:i:s A";
		Time::$enabledTimeZones = SystemSettings::get('SERVER_TZ');
		if(Time::$enabledTimeZones&&(!Time::$userTimeZoneID||!Time::$serverTimeZoneID)){
			Time::$enabledTimeZones = false;
		}
		Time::$inited = true;
	}

	static function setting_SELECT_TIME_ZONE($_SettingID)
	{
		$res = array();
		foreach($GLOBALS['_DATE_TIMEZONE_DATA'] as $time_zone_id => $time_zone){
			$res[] =array('title'=>$time_zone['longname'].' '.$time_zone['shortname'],'value'=>$time_zone_id);
		};
		return setting_SELECT_BOX($res,$_SettingID);
	}
	
	
	static function standartTime($time = null,$show_time = true)
	{
		if(!$time){//default value
			$time = time();
		}elseif(is_array($time)){//parsed value
			$time = strtotime(count($time>3)?self::dateTime($time):self::date($time));
		}elseif(!is_numeric($time)){//non parsed date/dateTime
			$time = $show_time?self::parseDateTime($time):self::parseDate($time);
			$time = (count($time>3)?self::dateTime($time):self::date($time));
			$time = strtotime($time);
		}
			
		$time = self::serverTimeToTime($time);
			
		if (defined('CONF_DATE_FORMAT')&&strcmp(constant('CONF_DATE_FORMAT'), "MM/DD/YYYY")==0){
			$format = "%m/%d/%Y";
		}else{//DD.MM.YYYY
			$format = "%d.%m.%Y";
		}
		if($show_time){
			$format .= " %H:%M:%S";
		}
		$standart_time = strftime($format, $time);
		return $standart_time;
	}
	
	static function timeOffset()
	{
		$server_time = time();
		$user_time = self::serverTimeToTime($server_time);
		return ($user_time-$server_time);
	}

	static function date($time = null)
	{
		if(is_array($time)){
			$parsed = $time;
			$date = sprintf("%04d-%02d-%02d",$parsed['year'],$parsed['month'],$parsed['day']);
		}elseif(!$time){
			$time = time();
			$date = strftime("%Y-%m-%d ", $time);
		}elseif(is_numeric($time)){
			$date = strftime("%Y-%m-%d ", $time);
		}elseif(self::isValidSatandartTime($time)){
			$parsed = self::parseStandartTime($time);
			$date = "{$parsed['year']}-{$parsed['month']}-{$parsed['day']}";
		}else{
			$parsed = self::parseDateTime($time);
			$time = strtotime(sprintf("%04d-%02d-%02d",$parsed['year'],$parsed['month'],$parsed['day']));
		}
		return $date;
	}
	// return date and time as a string in MySQL format
	static function dateTime($time = null)
	{
		if(is_array($time)){
			$parsed = $time;
			$date_time = sprintf("%04d-%02d-%02d %02d:%02d:%02d",$parsed['year'],$parsed['month'],$parsed['day'],$parsed['hour'],$parsed['minute'],$parsed['sec']);
		}elseif(is_numeric($time)){
			$date_time = strftime("%Y-%m-%d %H:%M:%S", $time);
		}elseif(!$time){
			$time = time();
			$date_time = strftime("%Y-%m-%d %H:%M:%S", $time);
		}elseif(self::isValidSatandartTime($time)){
			$parsed = self::parseStandartTime($time);
			$date_time = sprintf("%04d-%02d-%02d %02d:%02d:%02d",$parsed['year'],$parsed['month'],$parsed['day'],$parsed['hour'],$parsed['minute'],$parsed['sec']);
		}else{
			$parsed = self::parseDate($time);
			$date_time = strtotime(sprintf("%04d-%02d-%02d %02d:%02d:%02d",$parsed['year'],$parsed['month'],$parsed['day'],$parsed['hour'],$parsed['minute'],$parsed['sec']));
		}	
		return $date_time;
	}
	
	static function timeStamp($date = null)
	{
		if(is_array($date)){
			$parsed = $date;
			$time = strtotime(sprintf("%04d-%02d-%02d %02d:%02d:%02d",$parsed['year'],$parsed['month'],
			$parsed['day'],$parsed['hour'],$parsed['minute'],$parsed['sec']));
		}elseif(is_numeric($date)){
			$time = $date;
		}elseif(!$date){
			$time = time();
		}elseif(self::isValidSatandartTime($date)){
			$parsed = self::parseStandartTime($date);
			$time = strtotime(sprintf("%04d-%02d-%02d %02d:%02d:%02d",$parsed['year'],$parsed['month'],$parsed['day'],$parsed['hour'],$parsed['minute'],$parsed['sec']));
		}else{
			$parsed = self::parseDateTime($date);
			$time = strtotime(sprintf("%04d-%02d-%02d %02d:%02d:%02d",$parsed['year'],$parsed['month'],$parsed['day'],$parsed['hour'],$parsed['minute'],$parsed['sec']));
		}	
		return $time;
	}

	static function getInterval($date_begin,$date_end = null)
	{
		return ceil((($date_end?strtotime($date_end):time()) - strtotime($date_begin))/86400);
	}

	static function isValidSatandartTime($standart_time)
	{
		if (strcmp(_getSettingOptionValue("CONF_DATE_FORMAT"), "MM/DD/YYYY")==0){
			$pattern = '/^[\d]{2}\/[\d]{2}\/[\d]{4}/';
		}else{//DD.MM.YYYY
			$pattern = '/^[\d]{2}\.[\d]{2}\.[\d]{4}/';
		}
		return preg_match($pattern,$standart_time);
	}




	//parse
	static private function parseDateTime($date_time)
	{
		// 2004-12-30 13:25:41 - MySQL database datetime format
		if(!$datetime){
			$datetime = self::dateTime();
		}elseif(is_numeric($datetime)){
			$datetime = strftime("%Y-%m-%d %H:%M:%S", $datetime);
		}
		$pattern = '/^([\d]{4})-([\d]{2})-([\d]{2}) (([\d]{2}):([\d]{2}):([\d]{2}))$/';
		if(preg_match($pattern,$date_time,$matches)){
			$parsed = array();
			$parsed['year'] 	= (int)$matches[1];
			$parsed['month'] 	= (int)$matches[2];
			$parsed['day'] 		= (int)$matches[3];
			$parsed['time']		= (int)$matches[4];
			$parsed['hour'] 	= (int)$matches[5];
			$parsed['minute'] 	= (int)$matches[6];
			$parsed['sec'] 		= (int)$matches[7];
		}else{
			$parsed = false;
		}
		return $parsed;

	}

	static function parseDate($date = null)
	{
		// 2004-12-30 - MySQL database date format
		if(!$date){
			$date = self::date();
		}elseif(is_numeric($datetime)){
			$date = strftime("%Y-%m-%d", $date);
		}
		$pattern = '/^([\d]{4})-([\d]{2})-([\d]{2})/';
		if(preg_match($pattern,$date,$matches)){
			$parsed = array();
			$parsed['year'] 	= (int)$matches[1];
			$parsed['month'] 	= (int)$matches[2];
			$parsed['day'] 		= (int)$matches[3];
			$parsed['time']		= "00:00:00";
			$parsed['hour'] 	= "00";
			$parsed['minute'] 	= "00";
			$parsed['sec'] 		= "00";
		}else{
			$parsed = false;
		}
		return $parsed;
	}
	
	static function parseStandartTime($datetime = null)
	{
		$parsed = array();
		$parsed['day'] 		= substr($datetime, strpos(CONF_DATE_FORMAT, 'DD'),2);
		$parsed['month'] 	= substr($datetime, strpos(CONF_DATE_FORMAT, 'MM'),2);
		$parsed['year'] 	= substr($datetime, strpos(CONF_DATE_FORMAT, 'YYYY'),4);
		$parsed['time']		= "00:00:00";
		$parsed['hour'] 	= "00";
		$parsed['minute'] 	= "00";
		$parsed['sec'] 		= "00";
		$matches = null;
		if(preg_match('/(([\d]{2}):([\d]{2}):([\d]{2}))$/',$datetime,$matches)){
			$parsed['time']		= $matches[1];
			$parsed['hour'] 	= (int)$matches[2];
			$parsed['minute'] 	= (int)$matches[3];
			$parsed['sec'] 		= (int)$matches[4];
		}
		return $parsed;
	}



	static function getDaysInterval($time_begin,$time_end = null)
	{
		$time_begin = self::timeStamp($time_begin);
		$time_end = self::timeStamp($time_end);
		return ceil(($time_end - $time_begin)/86400);
	}


	static function serverTimeToTime($servertime)
	{
		if(!Time::$inited){
			Time::init();
		}
		if (Time::$enabledTimeZones)
		{
			$dt = new Date();

			$dt->setTZ( new Date_TimeZone(Time::$serverTimeZoneID, Time::$serverTimeZoneDST ) );
			$dt->setDate($servertime);

			$dt->convertTZ( new Date_TimeZone( Time::$userTimeZoneID, Time::$userTimeZoneDST ) );
			$time = $dt->getDate(DATE_FORMAT_UNIXTIME);
		}else{
			$time = $servertime;
		}
		return $time;
	}

	static function timeToServerTime($time)
	{
		if(!Time::$inited){
			Time::init();
		}
		if (Time::$enabledTimeZones)
		{
			$dt = new Date();

			$dt->setTZ( new Date_TimeZone( Time::$userTimeZoneID, Time::$userTimeZoneDST ) );
			$dt->setDate($time);

			$dt->convertTZ( new Date_TimeZone(Time::$serverTimeZoneID, Time::$serverTimeZoneDST ) );
			$servertime = $dt->getDate(DATE_FORMAT_UNIXTIME);
		}else{
			$servertime = $time;
		}

		return $servertime;

	}
}
?>