<?php
/**
 * Find country with presented alpha-iso
 *
 * @param string $iso_alpha: could be 2 alphas or 3 alphas
 * @return array
 */
function cnGetCountryByAlphaISO($alpha_iso){
	
	$country = array();
	$alpha_iso = trim($alpha_iso);
	if(!strlen($alpha_iso))return $country;
	
	$country = db_phquery_fetch(DBRFETCH_ASSOC, 'SELECT * FROM ?#COUNTRIES_TABLE WHERE '.(strlen($alpha_iso)==2?'country_iso_2':'country_iso_3').'=?', $alpha_iso);
	LanguagesManager::ml_fillFields(COUNTRIES_TABLE, $country);
	return $country;
}

/**
 * Get country information
 *
 * @param int $countryID
 * @return array
 */
function cnGetCountryById( $countryID )
{
	if ( is_null($countryID) || $countryID == "" )$countryID = "NULL";		
	
	$country = db_phquery_fetch(DBRFETCH_ROW, "SELECT * FROM ?#COUNTRIES_TABLE WHERE countryID=?", $countryID );
	LanguagesManager::ml_fillFields(COUNTRIES_TABLE, $country);
	return $country;
}



/**
 * @param array $callBackParam: offset, CountRowOnPage
 * @return array - "countryID" - id, "country_name" - name, "country_iso_2"	- ISO abbreviation ( 2 chars ), "country_iso_3"	- ISO abbreviation ( 3 chars )
 */
function cnGetCountries( $callBackParam, &$count_row, $navigatorParams = null ){
	
	if ( $navigatorParams != null ){
		
		$offset			= $navigatorParams["offset"];
		$CountRowOnPage	= $navigatorParams["CountRowOnPage"];
	}else{
		
		$offset = 0;
		$CountRowOnPage = 0;
	}

	$q=db_phquery("SELECT *, ".LanguagesManager::sql_prepareField('country_name')." AS country_name FROM ?#COUNTRIES_TABLE ORDER BY country_name" );
	$data=array();
	$i=0;
	while( $row=db_fetch_assoc($q) )
	{
		if ( ($i >= $offset && $i < $offset + $CountRowOnPage) || 
				$navigatorParams == null  )
		{
			$data[$row['countryID']] = $row;
		}
		$i++;
	}
	$count_row = $i;
	return $data;
}

/**
 * Delete country
 *
 * @param int $countryID
 */
function cnDeleteCountry($countryID)
{
 	$tax_classes = taxGetTaxClasses();
	foreach( $tax_classes as $class )
		taxDeleteRate( $class["classID"], $countryID );

	db_query("update ".CUSTOMER_ADDRESSES_TABLE.
		" set countryID=NULL where countryID='".$countryID."'");
	$q = db_query("select zoneID from ".ZONES_TABLE.
		" where countryID='".$countryID."'" );
	while( $r = db_fetch_row( $q ) )
	{
		db_query( "update ".CUSTOMER_ADDRESSES_TABLE.
			" set zoneID=NULL where zoneID='".$r["zoneID"]."'" );
	}
	db_query("delete from ".ZONES_TABLE.
		" where countryID='".$countryID."'" );
	db_query("delete from ".COUNTRIES_TABLE.
		" where countryID='".$countryID."'" );
}

/**
 * Update country information
 *
 * @param int $countryID
 * @param mixed $country_name
 * @param string $country_iso_2
 * @param string $country_iso_3
 */
function cnUpdateCountry( $countryID, $country_name, $country_iso_2, $country_iso_3 ){
	
	$dbq = "
		UPDATE ?#COUNTRIES_TABLE 
		SET ".LanguagesManager::sql_prepareFieldUpdate('country_name', $country_name).", country_iso_2=?, country_iso_3=?
		WHERE countryID=?
	";
	db_phquery($dbq, $country_iso_2, $country_iso_3, $countryID);
}

/**
 * Add country
 *
 * @param mixed $country_name
 * @param string $country_iso_2
 * @param string $country_iso_3
 * @return int - new country id
 */
function cnAddCountry( $country_name, $country_iso_2, $country_iso_3  ){
	
	$cnt_inj = LanguagesManager::sql_prepareFieldInsert('country_name', $country_name);

	$dbq = "
		INSERT ?#COUNTRIES_TABLE ({$cnt_inj['fields']}, country_iso_2, country_iso_3 )
		VALUES( {$cnt_inj['values']}, ?, ?)
	";
	db_phquery($dbq, $country_iso_2, $country_iso_3);
	return db_insert_id();
}

function cnGetCountriesNames()
{
    $q = db_phquery("SELECT *, ".LanguagesManager::sql_prepareField('country_name')." AS country_name FROM ?#COUNTRIES_TABLE ORDER BY country_name" );
	$countries = array();
	while( $r = db_fetch_row($q) )
	{
	    $countries[$r['countryID']] = $r['country_name'];
	};
	return $countries;
}

?>