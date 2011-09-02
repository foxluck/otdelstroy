<?php
	class CTimeZone {
		private $offset;
		public $LongName;
		public $ShortName;
		public $id;
		protected $daylightTime=false;
		
		
		public function __construct($id, $dst) {
			$tzData = TimeZones::getTimeZoneData($id, $dst);
			
			$this->id = $id;
			$this->LongName = $tzData["longname"];
			$this->ShortName = $tzData["shortname"];
			$this->offset = $tzData["offset"];
			$this->hasDst = $tzData["hasdst"];
			$this->daylightTime = ( $this->hasDst && ( $dst != 0 ) ) ? true : false;			
		}
		
		public function load() {
		}
		
    public function getOffset($date)
    {
        if($this->inDaylightTime($date)) {
            return ($this->offset + $this->getDSTSavings())/1000;
        } else {
            return $this->offset / 1000;
        }
    }
    
    public function inDaylightTime($date)
    {
	    return $this->daylightTime;
    }
    
    protected function getDSTSavings()
    {
        if($this->hasDst) {
            return 3600000;
        } else {
            return 0;
        }
    }
	}	
?>