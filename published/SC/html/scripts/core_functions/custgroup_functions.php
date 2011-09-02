<?php
	function GetCustomerGroupByCustomerId( $customerID ){
		
		$customerID = (int) $customerID;
		$q = db_query( "select custgroupID from ".CUSTOMERS_TABLE.
				" where customerID=$customerID" );
		$customer = db_fetch_row($q);
	
		if ( is_null($customer["custgroupID"]) || trim($customer["custgroupID"])=="" )
			return false;
	
		$q = db_phquery("SELECT * FROM ?#CUSTGROUPS_TABLE WHERE custgroupID=?", $customer["custgroupID"] );
		$row = db_fetch_row($q);
		LanguagesManager::ml_fillFields(CUSTGROUPS_TABLE, $row);
		return $row;
   	}


	function GetAllCustGroups(){
		
		$sql = '
			SELECT *, '.LanguagesManager::sql_prepareField('custgroup_name').' AS custgroup_name FROM ?#CUSTGROUPS_TABLE
			ORDER BY sort_order, custgroup_name
		';
		$dbHandler = &Core::getdbHandler();
		$Result = $dbHandler->ph_query($sql);
		$Groups = array();
		
	 	while( $_Row = $Result->fetchAssoc() ){
	 		
	 		$Groups[] = $_Row;
		}
		return $Groups;
	}

	function DeleteCustGroup($custgroupID)
	{
		db_query("update ".CUSTOMERS_TABLE." set custgroupID=NULL ".
				" where custgroupID='".$custgroupID."'");
		db_query("delete from ".CUSTGROUPS_TABLE.
			" where custgroupID='".$custgroupID."'");
	}

	function UpdateCustGroup($custgroupID, $custgroup_name, $custgroup_discount, $sort_order){

		$sql = '
			UPDATE ?#CUSTGROUPS_TABLE SET '.LanguagesManager::sql_prepareFieldUpdate('custgroup_name', $custgroup_name).',custgroup_discount=?,sort_order=? WHERE custgroupID=?
		';
		db_phquery($sql, $custgroup_discount,$sort_order,$custgroupID);				
	}


	function AddCustGroup($custgroup_name, $custgroup_discount, $sort_order){

		$name_inj = LanguagesManager::sql_prepareFieldInsert('custgroup_name', $custgroup_name);
		$sql = "
			INSERT ?#CUSTGROUPS_TABLE ({$name_inj['fields']}, custgroup_discount, sort_order) VALUES({$name_inj['values']},?,?)
		";
		db_phquery($sql, $custgroup_discount,$sort_order);
	}

?>