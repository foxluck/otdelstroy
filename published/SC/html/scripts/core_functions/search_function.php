<?php
	function schIsAllowProductsSearch( $categoryID )
	{
		$q = db_query("select allow_products_search from ".CATEGORIES_TABLE.
			" where categoryID=$categoryID");
		if ( $row = db_fetch_row($q) )
			return $row["allow_products_search"];
		return false;		
	}


	function schUnSetOptionsToSearch( $categoryID )
	{
		$q = db_query( "select optionID from ".CATEGORY_PRODUCT_OPTIONS_TABLE.
			" where categoryID=$categoryID " );
		$data = array();
		while( $row = db_fetch_row($q) )
			$data[] = $row["optionID"];

		foreach( $data as $val )
		{
			db_query( " delete from ".CATEGORY_PRODUCT_OPTION_VARIANTS.
				" where categoryID=$categoryID AND optionID=$val" );

			db_query( " delete from ".CATEGORY_PRODUCT_OPTIONS_TABLE.
				" where categoryID=$categoryID AND optionID=$val" );
		}
	}

	function schSetOptionToSearch( $categoryID, $optionID, $set_arbitrarily )
	{
		db_query( "insert into ".CATEGORY_PRODUCT_OPTIONS_TABLE.
				" ( categoryID, optionID, set_arbitrarily ) ".
				" values( $categoryID, $optionID, $set_arbitrarily ) " );
	}

	function schOptionIsSetToSearch( $categoryID, $optionID )	{
		static  $result_cache = array();
		$SQL = 'SELECT 1 AS isSet,set_arbitrarily,optionID FROM ?#CATEGORY_PRODUCT_OPTIONS_TABLE WHERE categoryID=?';

		if(!isset($result_cache[$categoryID])){
			$q = db_phquery($SQL,$categoryID);
			while ( $row = db_fetch_assoc($q) ){
				$cur_optionID = intval($row['optionID']);
				unset($row['optionID']);
				$result_cache[$categoryID][$cur_optionID] = $row;
			}
			if(!isset($result_cache[$categoryID])){
				$result_cache[$categoryID] = array();
			}
		}
		if(!isset($result_cache[$categoryID][$optionID])){
			$result_cache[$categoryID][$optionID]['isSet']=0;
		}

		return $result_cache[$categoryID][$optionID];
	}

	function &schOptionsAreSetToSearch( $categoryID, &$options ){
		
		$TC = count($options);
		$r_OptionID2Option = array();
		$r_OptionID = array();
		$r_OptionRes = array();
		
		for ($j=0;$j<$TC;$j++){
			
			$r_OptionID2Option[$options[$j]['optionID']] = &$options[$j];
			$r_OptionID[] = $options[$j]['optionID'];
			if(count($r_OptionID)>299||($j+1)==$TC){
				
				$SQL = '
					SELECT optionID,set_arbitrarily FROM ?#CATEGORY_PRODUCT_OPTIONS_TABLE
					WHERE categoryID=? AND optionID IN(?@)
				';
				$Result = db_phquery($SQL, $categoryID, $r_OptionID);
				while ($Row = db_fetch_assoc($Result)){
					
					$r_OptionRes[] = array(
						'option' => &$r_OptionID2Option[$Row['optionID']],
						'set_arbitrarily' => $Row['set_arbitrarily'],
					);
				}
				
				$r_OptionID = array();
			}
		}
		
		return $r_OptionRes;
	}

	function schUnSetVariantsToSearch( $categoryID, $optionID )
	{
		db_query( " delete from ".CATEGORY_PRODUCT_OPTION_VARIANTS.
			    " where categoryID=$categoryID AND optionID=$optionID" );
	}

	function schSetVariantToSearch( $categoryID, $optionID, $variantID )
	{
		db_query( "insert into ".CATEGORY_PRODUCT_OPTION_VARIANTS.
				" ( optionID, categoryID, variantID )  ".
				" values( $optionID, $categoryID, $variantID ) " );
	}	

	function schVariantIsSetToSearch( $categoryID, $optionID, $variantID )
	{
		static $cache = array();
		$categoryID = intval($categoryID);
		if(!isset($cache[$categoryID])){
			$sql = <<<SQL
			SELECT COUNT(*) AS `cnt`,`optionID`,`variantID` 
			FROM `?#CATEGORY_PRODUCT_OPTION_VARIANTS`
			WHERE `categoryID`=?
			GROUP BY  `optionID`, `variantID`
SQL;
			$cache[$categoryID] = array();
			$q = db_phquery( $sql, $categoryID);
			while($row = db_fetch_assoc($q)){
				$row = array_map('intval',$row);
				if(!isset($cache[$categoryID][$row['optionID']])){
					$cache[$categoryID][$row['optionID']] = array();
				}
				$cache[$categoryID][$row['optionID']][$row['variantID']] = $row['cnt'];
			}
		}
		return ( 
			isset($cache[$categoryID][$optionID])
			&&isset($cache[$categoryID][$optionID][$variantID])
			&&($cache[$categoryID][$optionID][$variantID]>0) );
	}
?>