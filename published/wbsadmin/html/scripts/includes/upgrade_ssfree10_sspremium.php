<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/


	function _createTable( $xmlNodeTableArray, $tableName )
	{
		for( $i=0; $i < count($xmlNodeTableArray);  $i++ )
		{
			$attr = $xmlNodeTableArray[$i]->GetXmlNodeAttributes();
			if ( $attr["NAME"] == $tableName )
			{
				$sql = GetCreateTableSQL( $xmlNodeTableArray[$i] );
				ss_db_query( $sql );
			}
		}
	}

	function _transStr( $str )
	{
		return mysql_real_escape_string($str);
		return str_replace( "'", "''", $str );
	}

	function _createRestTables__UpgradeSSfree10_to_sspremium( $xmlNodeTableArray )
	{
		for( $i=0; $i < count($xmlNodeTableArray);  $i++ )
		{
			$attr = $xmlNodeTableArray[$i]->GetXmlNodeAttributes();
			if ( 
					$attr["NAME"] != PRODUCTS_TABLE && 
					$attr["NAME"] != PRODUCT_PICTURES && 
					$attr["NAME"] != CATEGORIES_TABLE && 
					$attr["NAME"] != SPECIAL_OFFERS_TABLE &&
					$attr["NAME"] != ORDERS_TABLE &&
					$attr["NAME"] != ORDERED_CARTS_TABLE &&
					$attr["NAME"] != SHOPPING_CART_ITEMS_TABLE
				)
			{
				$sql = GetCreateTableSQL( $xmlNodeTableArray[$i] );
				ss_db_query( $sql );
			}
		}
	}

	function _convert__PRODUCTS_TABLE( $xmlNodeTableArray )
	{
		db_add_column( PRODUCTS_TABLE, "sort_order",				"int",			"0",	true );
		db_add_column( PRODUCTS_TABLE, "default_picture",			"int",			null,	true );
		db_add_column( PRODUCTS_TABLE, "date_added",				"datetime",		'NULL',	true );
		db_add_column( PRODUCTS_TABLE, "date_modified",				"datetime",		null,	true );
		db_add_column( PRODUCTS_TABLE, "viewed_times",				"int",			"0",	true );
		db_add_column( PRODUCTS_TABLE, "eproduct_filename",			"varchar(255)",	null,	true );
		db_add_column( PRODUCTS_TABLE, "eproduct_available_days",	"int",			"5",	true );
		db_add_column( PRODUCTS_TABLE, "eproduct_download_times",	"int",			"5",	true );
		db_add_column( PRODUCTS_TABLE, "weight",					"float",		null,	true );
		db_add_column( PRODUCTS_TABLE, "meta_description",			"varchar(255)",	null,	true );
		db_add_column( PRODUCTS_TABLE, "meta_keywords",				"varchar(255)",	null,	true );
		db_add_column( PRODUCTS_TABLE, "free_shipping",				"int",			0,		true );
		db_add_column( PRODUCTS_TABLE, "min_order_amount",			"int",			1,		true );
		db_add_column( PRODUCTS_TABLE, "shipping_freight",			"float",		0,		true );
		db_add_column( PRODUCTS_TABLE, "classID",					"int",			null,	true );

		_createTable( $xmlNodeTableArray, PRODUCT_PICTURES );

		$q = ss_db_query( "select productID, picture, thumbnail, big_picture from ".PRODUCTS_TABLE );
		while( $row=db_fetch_row($q) )
		{
			$row = array_map('trim',$row);
			if ($row["picture"]||$row["thumbnail"]||$row["big_picture"]){
				$insert = "insert into ".PRODUCT_PICTURES."( filename, thumbnail, enlarged, productID ) ".
						" values( '"._transStr($row["picture"])."', '"._transStr($row["thumbnail"])."', ".
								" '"._transStr($row["big_picture"])."', ".$row["productID"]." )";
				$res = ss_db_query( $insert );
				if($Id = ss_db_insert_id()){
				ss_db_query( "update ".PRODUCTS_TABLE." set default_picture=".$Id." ".
							" where productID=".$row["productID"] );
				}else{
					$debug=debug_backtrace();
					$clean_debug='';
					foreach ($debug as $call){
						if(isset($call['args']))unset($call['args']);
						$path=pathinfo($call['file']);
						$clean_debug.=$path['basename'].':'.$call['line'].' '.$call['function'].'<br>';
					}
					throw new Exception("<b>MySQL Insert Error</b>:".var_export(array($res,$Id),true)."<br><b>SQL query</b> : ".htmlentities($insert,ENT_QUOTES,'utf-8').'<br><pre>'.$clean_debug.'</pre><br>');
				}
			}
		}
		db_delete_column( PRODUCTS_TABLE, "picture" );
		db_delete_column( PRODUCTS_TABLE, "thumbnail" );
		db_delete_column( PRODUCTS_TABLE, "big_picture" );
	}

	function _convert__CATEGORIES_TABLE( $xmlNodeTableArray )
	{
		db_add_column( CATEGORIES_TABLE, "sort_order",					"int",	0, true );
		db_add_column( CATEGORIES_TABLE, "viewed_times",				"int",	0, true );
		db_add_column( CATEGORIES_TABLE, "allow_products_comparison",	"int",	0, true );
		db_add_column( CATEGORIES_TABLE, "allow_products_search",		"int",	1, true );
		db_add_column( CATEGORIES_TABLE, "show_subcategories_products",	"int",	1, true );

		db_add_column( CATEGORIES_TABLE, "meta_description",	"varchar(255)",	null, true );
		db_add_column( CATEGORIES_TABLE, "meta_keywords",		"varchar(255)",	null, true );

		$q = ss_db_query( "select MAX(categoryID) from ".CATEGORIES_TABLE);
		if( $row = db_fetch_row($q) )
		{
			$categoryID = ($row[0] + 1);
			ss_db_query( "update ".CATEGORIES_TABLE." set categoryID = ".$categoryID." WHERE categoryID=1");
			ss_db_query( "update ".CATEGORIES_TABLE." set parent = ".$categoryID." WHERE parent=1");
			ss_db_query( "update ".PRODUCTS_TABLE." set categoryID = ".$categoryID." WHERE categoryID=1" );
			ss_db_query( "update ".CATEGORIES_TABLE." set parent = 1 WHERE parent=0");
		}
		ss_db_query( "update ".CATEGORIES_TABLE." set viewed_times=0;" );
	}

	function _convert__SPECIAL_OFFERS_TABLE( $xmlNodeTableArray )
	{
		db_rename_column( SPECIAL_OFFERS_TABLE, "sort_order", "sort_order", "int", 0, true );
	}


	function _convert__ORDERS_TABLE( $xmlNodeTableArray )
	{
		db_add_column( ORDERS_TABLE, "customerID",			"int",			null, true );
		db_add_column( ORDERS_TABLE, "customer_ip",			"varchar(15)",	null, true );
		db_add_column( ORDERS_TABLE, "shipping_type",		"varchar(30)",	null, true );
		db_add_column( ORDERS_TABLE, "payment_type",		"varchar(30)",	null, true );
		db_add_column( ORDERS_TABLE, "customers_comment",	"varchar(255)", null, true );
		db_add_column( ORDERS_TABLE, "statusID",			"int",			null, true );
		db_add_column( ORDERS_TABLE, "shipping_cost",		"float",		null, true );
		db_add_column( ORDERS_TABLE, "order_discount",		"float",		null, true );
		db_add_column( ORDERS_TABLE, "order_amount",		"float",		null, true );
		db_add_column( ORDERS_TABLE, "currency_code",		"varchar(7)",	null, true );
		db_add_column( ORDERS_TABLE, "currency_value",		"float",		null, true );
		db_add_column( ORDERS_TABLE, "customer_firstname",	"varchar(64)",	null, true );
		db_add_column( ORDERS_TABLE, "customer_lastname",	"varchar(64)",	null, true );
		db_add_column( ORDERS_TABLE, "customer_email",		"varchar(50)",	null, true );

		db_add_column( ORDERS_TABLE, "shipping_firstname",	"varchar(64)",	null, true );
		db_add_column( ORDERS_TABLE, "shipping_lastname",	"varchar(64)",	null, true );
		db_add_column( ORDERS_TABLE, "shipping_country",	"varchar(64)",	null, true );
		db_add_column( ORDERS_TABLE, "shipping_state",		"varchar(64)",	null, true );
		db_add_column( ORDERS_TABLE, "shipping_zip",		"varchar(64)",	null, true );
		db_add_column( ORDERS_TABLE, "shipping_city",		"varchar(64)",	null, true );
		db_add_column( ORDERS_TABLE, "shipping_address",	"varchar(64)",	null, true );

		db_add_column( ORDERS_TABLE, "billing_firstname",	"varchar(64)",	null, true );
		db_add_column( ORDERS_TABLE, "billing_lastname",	"varchar(64)",	null, true );
		db_add_column( ORDERS_TABLE, "billing_country",		"varchar(64)",	null, true );
		db_add_column( ORDERS_TABLE, "billing_state",		"varchar(64)",	null, true );
		db_add_column( ORDERS_TABLE, "billing_zip",			"varchar(64)",	null, true );
		db_add_column( ORDERS_TABLE, "billing_city",		"varchar(64)",	null, true );
		db_add_column( ORDERS_TABLE, "billing_address",		"varchar(64)",	null, true );

		db_add_column( ORDERS_TABLE, "cc_number",			"varchar(32)",	null, true );
		db_add_column( ORDERS_TABLE, "cc_holdername",		"varchar(128)",	null, true );
		db_add_column( ORDERS_TABLE, "cc_expires",			"char(4)",		null, true );
		db_add_column( ORDERS_TABLE, "cc_cvv",				"varchar(4)",	null, true );
		db_add_column( ORDERS_TABLE, "affiliateID",				"int",	0, true );
		db_add_column( ORDERS_TABLE, "shippingServiceInfo",				"varchar(255)",	null, true );

		ss_db_query( "update ".ORDERS_TABLE." set  ".
						" customer_firstname = cust_firstname, ".
						" customer_lastname	 = cust_lastname, ".
						" customer_email = cust_email, ".
						" shipping_firstname = cust_firstname, ".
						" shipping_lastname	 = cust_lastname, ".
						" shipping_country   = cust_country, ".
						" shipping_state	 = cust_state, ".
						" shipping_zip		 = cust_zip, ".
						" shipping_city		 = cust_city, ".
						" shipping_address	 = cust_address, ".
						" billing_firstname	 = cust_firstname, ".
						" billing_lastname	 = cust_lastname, ".
						" billing_country    = cust_country, ".
						" billing_state	     = cust_state, ".
						" billing_zip		 = cust_zip, ".
						" billing_city		 = cust_city, ".
						" billing_address	 = cust_address " );
		db_delete_column( ORDERS_TABLE, "cust_firstname" );
		db_delete_column( ORDERS_TABLE, "cust_lastname" );
		db_delete_column( ORDERS_TABLE, "cust_email" );
		db_delete_column( ORDERS_TABLE, "cust_country" );
		db_delete_column( ORDERS_TABLE, "cust_zip" );
		db_delete_column( ORDERS_TABLE, "cust_state" );
		db_delete_column( ORDERS_TABLE, "cust_city" );
		db_delete_column( ORDERS_TABLE, "cust_address" );
		
		$sql = "
			SELECT orderID FROM ".ORDERS_TABLE."
		";
		$Result = ss_db_query($sql);
		while ($_Row = db_fetch_row($Result)){
			
			list($_OrderID) = $_Row;
			$sql = "
				SELECT SUM(Price*Quantity) FROM ".ORDERED_CARTS_TABLE."
				WHERE orderID = '".$_OrderID."'
			";
			list($_OrderAmount) = db_fetch_row(ss_db_query($sql));
			$sql = "
				UPDATE ".ORDERS_TABLE." SET order_amount='".$_OrderAmount."'
				WHERE orderID='".$_OrderID."'
			";
			ss_db_query($sql);
		}
		
		$sql = "
			UPDATE ".ORDERS_TABLE." 
			SET currency_code='USD', currency_value=1, statusID=2
		";
		ss_db_query($sql);
	}

	function _convert__ORDERED_CARTS_TABLE( $xmlNodeTableArray )
	{
		_createTable( $xmlNodeTableArray, SHOPPING_CART_ITEMS_TABLE );

		$ORDERED_CARTS_TABLE_content = array();
		$q = ss_db_query( "select productID, orderID, name, Price, Quantity from ".ORDERED_CARTS_TABLE );
		while ( $row=db_fetch_row($q) )
			$ORDERED_CARTS_TABLE_content[] = $row;
		ss_db_query( "delete from ".ORDERED_CARTS_TABLE );

		db_rename_column(	ORDERED_CARTS_TABLE, "productID", "itemID", "int", null, false );
		db_add_column( ORDERED_CARTS_TABLE, "tax",			"float",	null,	true );
		db_add_column( ORDERED_CARTS_TABLE, "load_counter", "int",		0,		true );


		foreach( $ORDERED_CARTS_TABLE_content as $ORDERED_CARTS_TABLE_item )
		{
			$q1 = ss_db_query( "insert into ".SHOPPING_CART_ITEMS_TABLE." ( productID ) ".
								" values( ".$ORDERED_CARTS_TABLE_item["productID"]." ) " );
			if($itemID = ss_db_insert_id()){
			ss_db_query( "insert into ".ORDERED_CARTS_TABLE." ( itemID, orderID, name, Price, Quantity ) ".
							" values( ".$itemID.", ".$ORDERED_CARTS_TABLE_item["orderID"].", ".
									" '"._transStr($ORDERED_CARTS_TABLE_item["name"])."', ".
									" ".$ORDERED_CARTS_TABLE_item["Price"].",  ".
									" ".$ORDERED_CARTS_TABLE_item["Quantity"]." )" );
			}else{
				$debug=debug_backtrace();
					$clean_debug='';
					foreach ($debug as $call){
						if(isset($call['args']))unset($call['args']);
						$path=pathinfo($call['file']);
						$clean_debug.=$path['basename'].':'.$call['line'].' '.$call['function'].'<br>';
					}
					throw new Exception("<b>MySQL Insert Error</b>:".var_export(array($res,$Id),true)."<br><b>SQL query</b> : ".htmlentities($insert,ENT_QUOTES,'utf-8').'<br><pre>'.$clean_debug.'</pre><br>');
				
			}
		}

	}



	function UpgradeSSfree10_to_sspremium( $login, $password )
	{		
		$xmlNodeTableArray = GetXmlTableNodeArray( DATABASE_STRUCTURE_XML_PATH );
		_convert__PRODUCTS_TABLE( $xmlNodeTableArray );
		_convert__CATEGORIES_TABLE( $xmlNodeTableArray );
		_convert__SPECIAL_OFFERS_TABLE( $xmlNodeTableArray );
		_convert__ORDERS_TABLE( $xmlNodeTableArray );
		_convert__ORDERED_CARTS_TABLE( $xmlNodeTableArray );
		_createRestTables__UpgradeSSfree10_to_sspremium( $xmlNodeTableArray );
		CreateReferConstraintsXML( DATABASE_STRUCTURE_XML_PATH );
	}

?>