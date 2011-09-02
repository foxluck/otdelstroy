<?php
/**
 * Get zone info
 *
 * @param integer $country_id
 * @param string $alpha_iso: 2 alphas iso code
 */
function znGetZoneByAlphaISO($country_id, $alpha_iso){

	$zone = db_phquery_fetch(DBRFETCH_ASSOC, 'SELECT * FROM ?#ZONES_TABLE WHERE countryID=? AND zone_code=?',$country_id, $alpha_iso );
	LanguagesManager::ml_fillFields(ZONES_TABLE, $zone);
	return $zone;
}

// *****************************************************************************
// Purpose	determine weither zone belongs to particlar country
// Inputs
// Remarks
// Returns	true if zone belongs to particlar country
function ZoneBelongsToCountry($zoneID, $countryID)
{
	$q = db_query("select count(*) from ".ZONES_TABLE." where countryID=$countryID");
	$row = db_fetch_row( $q );
	if ( $row[0]!=0 )
	{
		if ( trim($zoneID) == (string)((int)$zoneID)  )
		{
			$q = db_query("select count(*) from ".ZONES_TABLE.
				" where countryID=$countryID AND zoneID=$zoneID");
			$row = db_fetch_row( $q );
			return ($row[0] != 0);
		}
		else
		return false;
	}
	return true;
}

/**
 * Get country zones
 *
 * @param int $countryID: country ID
 * @return array: "zoneID"	- id, "zone_name"	- zone name, "zone_code"	- zone code, "countryID"	- countryID
 */
function znGetZones( $countryID = null ){
	static $zones = array();
	static $inited = false;

	$countryID = max(0,intval($countryID));
	$data = array();

	if($countryID){
		if(!isset($zones[$countryID])){
			$sql = 'SELECT *, '.LanguagesManager::sql_prepareField('zone_name').' AS zone_name FROM ?#ZONES_TABLE WHERE countryID=?';
			$q = db_phquery($sql,$countryID);
			while( $row=db_fetch_assoc($q) ){
				LanguagesManager::ml_fillFields(ZONES_TABLE, $row);
				if(!is_array($zones[$row['countryID']])){
					$zones[$row['countryID']] = array();
				}
				$zones[$row['countryID']][$row['zoneID']]=$row;
			}
		}
		if(isset($zones[$countryID])){
			$data = $zones[$countryID];
		}else{
			$data = array();
		}

	}else{
		if(!$inited){
			$sql = 'SELECT *, '.LanguagesManager::sql_prepareField('zone_name').' AS zone_name FROM ?#ZONES_TABLE ORDER BY zone_name';
			$q = db_phquery($sql,$countryID);
			while( $row=db_fetch_assoc($q) ){
				LanguagesManager::ml_fillFields(ZONES_TABLE, $row);
				if(!is_array($zones[$row['countryID']])){
					$zones[$row['countryID']] = array();
				}
				$zones[$row['countryID']][$row['zoneID']]=$row;
			}
			$inited = true;
		}
		foreach($zones as $zone){
			$data = array_merge($data,$zone);
		}
	}
	return $data;
}

/**
 * Get all zones of particular country
 *
 * @param int $countryID
 * @return array
 */
function znGetZonesById($countryID)
{
	if ( is_null($countryID) || $countryID == "" )$countryID = "NULL";

	return db_phquery_fetch(DBRFETCH_ROW_ALL, 'SELECT *, '.LanguagesManager::sql_prepareField('zone_name').' AS zone_name FROM ?#ZONES_TABLE WHERE countryID=? ORDER BY zone_name', $countryID);
}

/**
 * Get zone information
 *
 * @param int $zoneID
 * @return array
 */
function znGetSingleZoneById( $zoneID ){

	if ( is_null($zoneID) || $zoneID == "" )$zoneID = "NULL";
	$zone = db_phquery_fetch(DBRFETCH_ROW, 'SELECT * FROM ?#ZONES_TABLE WHERE zoneID=?',$zoneID );
	LanguagesManager::ml_fillFields(ZONES_TABLE, $zone);
	return $zone;
}



// *****************************************************************************
// Purpose	deletes Zone
// Inputs     		id
// Remarks
// Returns		nothing
function znDeleteZone($zoneID)
{
	$tax_classes = taxGetTaxClasses();
	foreach( $tax_classes as $class )
	taxDeleteZoneRate( $class["classID"], $zoneID );

	db_query("update ".CUSTOMER_ADDRESSES_TABLE.
		" set zoneID=NULL where zoneID='".$zoneID."'");
	db_query("delete from ".ZONES_TABLE.
		" where zoneID='".$zoneID."'" );	
}

/**
 * Update zone information
 *
 * @param unknown_type $zoneID
 * @param unknown_type $zone_name
 * @param unknown_type $zone_code
 * @param unknown_type $countryID
 */
function znUpdateZone( $zoneID, $zone_name, $zone_code, $countryID ){

	$sql = '
		UPDATE ?#ZONES_TABLE SET '.LanguagesManager::sql_prepareFieldUpdate('zone_name', $zone_name).',zone_code=?,countryID=? WHERE zoneID=?
	';
	db_phquery($sql, $zone_code, $countryID, $zoneID);
}

/**
 * Add zone
 *
 * @param string $zone_name
 * @param string $zone_code
 * @param int $countryID
 * @return int : inserted zone id
 */
function znAddZone( $zone_name, $zone_code, $countryID  ){

	$name_inj = LanguagesManager::sql_prepareFieldInsert('zone_name', $zone_name);
	$sql = '
		INSERT ?#ZONES_TABLE ('.$name_inj['fields'].', zone_code, countryID) VALUES('.$name_inj['values'].',?,?)
	';
	db_phquery($sql, $zone_code, $countryID);
	return db_insert_id();
}

?>