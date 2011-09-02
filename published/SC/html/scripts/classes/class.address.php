<?php
class Address extends DBObject {
	
	var $addressID;
	var $customerID;
	var $first_name;
	var $last_name;
	var $countryID = null;
	var $zoneID = null;
	var $zip;
	var $state = null;
	var $city;
	var $address;
	
	function __construct(){
		
		$this->__primary_key = 'addressID';
		$this->__db_table = CUSTOMER_ADDRESSES_TABLE;

		parent::__construct();
	}
	
	function checkInfo(){
		
		$res = parent::checkInfo();
		if(PEAR::isError($res))return $res;
		
		if(!$this->first_name)
			return PEAR::raiseError('err_input_name', null, null, null, 'first_name');
					
		if(!$this->last_name)
			return PEAR::raiseError('err_input_name', null, null, null, 'last_name');
			
		if(CONF_ADDRESSFORM_ADDRESS == 0 && !$this->address)
			return PEAR::raiseError('err_input_address', null, null, null, 'address');
		
		if(CONF_ADDRESSFORM_CITY == 0 && !$this->city)
			return PEAR::raiseError('err_input_city', null, null, null, 'city');
						
		if(CONF_ADDRESSFORM_STATE == 0 && !$this->zoneID && !$this->state)
			return PEAR::raiseError('err_input_state', null, null, null, is_null($this->zoneID)?'state':'zoneID');
						
		if(CONF_ADDRESSFORM_ZIP == 0 && !$this->zip)
			return PEAR::raiseError('err_input_zip', null, null, null, 'zip');
			
		if(!is_null($this->countryID) && !$this->countryID)
			return PEAR::raiseError('err_input_country', null, null, null, 'countryID');
			
		if($this->countryID){
			
			$zones = znGetZonesById($this->countryID);
			
			if(count($zones)){
				if(!$this->zoneID&&(CONF_ADDRESSFORM_STATE == 0))return PEAR::raiseError('err_region_does_not_belong_to_country', null, null, null, 'zoneID');
				$zone = znGetSingleZoneById($this->zoneID);
				if(($zone['countryID'] != $this->countryID)&&(CONF_ADDRESSFORM_STATE != 2))
					return PEAR::raiseError('err_region_does_not_belong_to_country', null, null, null, 'zoneID');
			}else{
				
				$this->zoneID = '';
				if((CONF_ADDRESSFORM_STATE == 0) && !$this->state)
					return PEAR::raiseError('err_input_state', null, null, null, 'state');
			}
		}
	}

	function getCountryName(){
		
		$country = cnGetCountryById($this->countryID);
		return isset($country["country_name"])?$country["country_name"]:'';
	}
	
	function getHTMLString(){
		
		$country = cnGetCountryById($this->countryID);
		$country = $country["country_name"];
		if ( $this->state == "" ){
			$zone = znGetSingleZoneById( $this->zoneID );
			$zone = $zone["zone_name"];
		}else{
			$zone = $this->state;
		}
		
		$strAddress = " <span style='font-size:110%;'>".xHtmlSpecialChars($this->first_name)." ".xHtmlSpecialChars($this->last_name).'</span>';
		if (strlen($this->address)>0) $strAddress .= "<br />".xHtmlSpecialChars($this->address);
		if (strlen($this->city)>0) $strAddress .= "<br />".xHtmlSpecialChars($this->city);
		if (strlen($zone)>0) $strAddress .= "  ".xHtmlSpecialChars($zone);
		if (strlen($this->zip)>0) $strAddress .= "  ".xHtmlSpecialChars($this->zip);
		if (strlen($country)>0) $strAddress .= "<br />".xHtmlSpecialChars($country);
	
		if(!$this->first_name && !$this->last_name && !$this->countryID && !$this->zoneID && !$this->zip && !$this->state && !$this->city && !$this->address && !trim(strip_tags($strAddress))){
			return '';
		}
		return $strAddress;
	}
	
	function getTextString(){
		
		$country = cnGetCountryById($this->countryID);
		$country = $country["country_name"];
		if ( $this->state == "" ){
			$zone = znGetSingleZoneById( $this->zoneID );
			$zone = $zone["zone_name"];
		}else{
			$zone = $this->state;
		}
		
		$strAddress = $this->first_name."  ".$this->last_name;
		if (strlen($this->address)>0) $strAddress .= "\n".$this->address;
		if (strlen($this->city)>0) $strAddress .= "\n".$this->city;
		if (strlen($zone)>0) $strAddress .= "  ".$zone;
		if (strlen($this->zip)>0) $strAddress .= "  ".$this->zip;
		if (strlen($country)>0) $strAddress .= "\n".$country;
	
		return $strAddress;
	}

	/**
	 * @param Customer
	 */
	function belong2Customer($customerEntry){
		
		return $customerEntry->customerID == $this->customerID;
	}
}
?>