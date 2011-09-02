<?php
	class CDateTime {
		var $value; // unix timestamp value
		var $displayFormat = "d.m.Y";
		static $defaultDisplayFormat = "d.m.Y"; // display format by default
		static $defaultTimeZone = null;
		static $defaultDisplayTimeZone = null;
		var $timeZone = null;
		var $displayTimeZone = null;
			
		static $dateFormats = array(
			"MM/DD/YYYY" => array ("phpFormat" => "m/d/Y"),
			"MM.DD.YYYY" => array ("phpFormat" => "m.d.Y"),
			"DD.MM.YYYY" => array ("phpFormat" => "d.m.Y")
		);
		
		
		public function __construct($dateValue = null) {
			if (is_numeric($dateValue)) {
				$this->value = $dateValue;
			} elseif (is_string($dateValue)) {
				$this->value = strtotime($dateValue);
			}
			if ($dateValue == null)
				$this->value = null;
		}
		
		public static function setDefaultDisplayFormat($format) {
			self::$defaultDisplayFormat = self::$dateFormats[$format]["phpFormat"];
		}
		
		public static function setDefaultTimeZone($timeZone) {
			self::$defaultTimeZone = $timeZone;
		}
		
		public static function setDefaultDisplayTimeZone($timeZone) {
			self::$defaultDisplayTimeZone = $timeZone;			
		}
		
		public function setDisplayFormat($format) {
			$this->displayFormat = $format;
		}
		
		public function setTimeZone($timeZone) {
			$this->timeZone = $timeZone;
		}
		
		public function setDisplayTimeZone($timeZone) {
			$this->displayTimeZone = $timeZone;
		}
		
		public static function now() {
			$instance = new self(time());
			$instance->setDisplayFormat(self::$defaultDisplayFormat);
			$instance->setTimeZone(self::$defaultTimeZone);
			$instance->setDisplayTimeZone(self::$defaultDisplayTimeZone);
			return $instance;
		}
		
		public static function fromStr($str) {
			$instance = new self($str);
			$instance->setDisplayFormat(self::$defaultDisplayFormat);
			$instance->setTimeZone(self::$defaultTimeZone);
			$instance->setDisplayTimeZone(self::$defaultDisplayTimeZone);
			return $instance;
		}
		
		public function toStr() {
			if (!$this->value)
				return null;
			return date("YmdHis", $this->value);
		}
		
		// Out date in display format
		public function display() {
			if (!$this->value)
				return "";
			$displayValue = $this->value;
			if ($this->timeZone && $this->displayTimeZone)
				$displayValue = $this->convertValueForTimezone($this->timeZone, $this->displayTimeZone);
			return date($this->displayFormat  . " H:i", $displayValue);
		}
		
		protected function convertValueForTimezone($fromTz, $toTz) {
			if (!($fromTz && $toTz))
				return $value;
			$value = $this->value;
			$fromOffset = $fromTz->getOffset($this);
			$toOffset = $toTz->getOffset($this);
			$value = $value - ($fromOffset - $toOffset);
			return $value;
		}
			
		
		// Convert this date to utc timezone
		public function toUtc() {
			if (!$this->timeZone)
				return $this->value;
			
			$offset = intval($this->timeZone->getOffset($this))/1000;
			$this->value += $offset;
      $this->timeZone = TimeZones::getUtcTimeZone();
		}
		
		function addSeconds($sec)
    {
    	$this->value += $sec;
    }
    
    function subtractSeconds($sec)
    {
    	$this->value -= $sec;        
    }
	}
	
	class CDate extends CDateTime {
		public function toStr() {
			if (!$this->value)
				return null;
			return date("YmdHis", $this->value);
		}
		
		public static function today() {
			$instance = new self(mktime(0,0,0));
			$instance->setDisplayFormat(self::$defaultDisplayFormat);
			return $instance;
		}
	}
?>