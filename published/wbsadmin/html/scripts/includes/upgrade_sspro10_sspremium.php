<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/

	function _createRestTables__Upgrade_sspro10_sspremium( $xmlNodeTableArray )
	{
		for( $i=0; $i < count($xmlNodeTableArray);  $i++ )
		{
			$attr = $xmlNodeTableArray[$i]->GetXmlNodeAttributes();
			if (
					$attr["NAME"] != CUSTOMERS_TABLE &&
					$attr["NAME"] != ORDER_STATUES_TABLE &&
					$attr["NAME"] != ORDERS_TABLE &&
					$attr["NAME"] != SHOPPING_CARTS_TABLE &&
					$attr["NAME"] != PRODUCTS_TABLE &&
					$attr["NAME"] != CATEGORIES_TABLE &&
					$attr["NAME"] != SPECIAL_OFFERS_TABLE &&
					$attr["NAME"] != ORDERED_CARTS_TABLE &&
					$attr["NAME"] != SPECIAL_OFFERS_TABLE &&
					$attr["NAME"] != NEWS_TABLE &&
					$attr["NAME"] != DISCUSSIONS_TABLE &&
					$attr["NAME"] != PAYMENT_TYPES_TABLE &&
					$attr["NAME"] != CURRENCY_TYPES_TABLE &&
					$attr["NAME"] != SHIPPING_METHODS_TABLE &&
					$attr["NAME"] != RELATED_PRODUCTS_TABLE &&
					$attr["NAME"] != PRODUCT_OPTIONS_TABLE &&
					$attr["NAME"] != PRODUCT_OPTIONS_VALUES_TABLE &&
					$attr["NAME"] != MAILING_LIST_TABLE && 
					$attr["NAME"] != CUSTOMER_ADDRESSES_TABLE &&
					$attr["NAME"] != SHOPPING_CART_ITEMS_TABLE && 
					$attr["NAME"] != PRODUCT_PICTURES &&
					$attr["NAME"] != SETTINGS_GROUPS_TABLE && 
					$attr["NAME"] != SETTINGS_TABLE )
			{
				$sql = GetCreateTableSQL( $xmlNodeTableArray[$i] );
				ss_db_query( $sql );
			}
		}
	}

	function _convert__CUSTOMERS_TABLE( $xmlNodeTableArray )
	{
		$q = ss_db_query( "select Login, cust_password, Email, Country, City, Address, Phone, ".
					" first_name, default_currency, subscribed4news, ZIP, State, last_name ".
					" from ".CUSTOMERS_TABLE );
		$customers = array();
	 	while( $row = db_fetch_row($q) )
			$customers[] = $row;

		db_delete_table( CUSTOMERS_TABLE );

		_createTable( $xmlNodeTableArray, CUSTOMERS_TABLE );
		_createTable( $xmlNodeTableArray, CUSTOMER_ADDRESSES_TABLE );

		foreach( $customers as $customer )
		{
			$customer["cust_password"] = base64_encode( $customer["cust_password"], null);
			if ( $customer["default_currency"] == null )
				$customer["default_currency"] = "NULL";
			ss_db_query( "insert into ".CUSTOMERS_TABLE." ( Login, cust_password, Email, ".
						" first_name, last_name, subscribed4news, custgroupID, ".
						" addressID, reg_datetime, CID ) ".
						" values( '"._transStr($customer["Login"])."', '"._transStr($customer["cust_password"])."', ".
						" '"._transStr($customer["Email"])."', '"._transStr($customer["first_name"])."', ".
						" '"._transStr($customer["last_name"])."', ".(int)$customer["subscribed4news"].", ".
						"  NULL, NULL, '".get_current_time()."', ".$customer["default_currency"]." ) ");
			$customerID = ss_db_insert_id();

			ss_db_query( "insert into ".CUSTOMER_ADDRESSES_TABLE.
				" ( customerID, first_name, last_name, countryID, zoneID, zip, state, city, address ) ".
				" values( ".$customerID.", '"._transStr($customer["first_name"])."', '"._transStr($customer["last_name"])."', ".
				"          NULL, NULL, '"._transStr($customer["ZIP"])."', '"._transStr($customer["State"])."', ".
				"			'"._transStr($customer["City"])."', '"._transStr($customer["Address"])."' ) ");
			$addressID = ss_db_insert_id();

			ss_db_query( "update ".CUSTOMERS_TABLE." set addressID=".$addressID."  ".
						" where customerID=".$customerID );
		}
	}

	function _add__ORDER_STATUES_TABLE( $xmlNodeTableArray, $orderStatusesAreSet )
	{
		_createTable( $xmlNodeTableArray, ORDER_STATUES_TABLE );
		serImportWithConstantNameReplacing( "./sql/setting_groups.sql" );
		serImportWithConstantNameReplacing( "./sql/setting_constants.sql", true );
		_setNewOrderStatus();
		_setCompletedStatus();
	}


	function _convert__ORDERS_TABLE__Upgrade_sspro10_sspremium( $xmlNodeTableArray, $orderStatusesAreSet )
	{
		$q = ss_db_query( "select orderID, customer_login, payment_type, customers_comment, order_time, ".
					" Done, shipping_type, final_shipping_address, shipping_cost, ".
					" calculate_tax, tax from ".ORDERS_TABLE );
		$orders = array();
		while( $order = db_fetch_row($q) )
			$orders[] = $order;

		db_delete_table( ORDERS_TABLE );
		_createTable( $xmlNodeTableArray, ORDERS_TABLE );
		
		$statuses = array(
			'CONF_COMPLETED_ORDER_STATUS'=>0,
			'CONF_NEW_ORDER_STATUS'=>0,
		);
		$sql = "select `settings_constant_name`,`settings_value` from `".SETTINGS_TABLE."` WHERE
		 settings_constant_name IN ('CONF_COMPLETED_ORDER_STATUS','CONF_NEW_ORDER_STATUS')";
		$q = ss_db_query($sql);
		while ( $row = db_fetch_row( $q ) ){
			$statuses[$row[0]] = $row[1];
		}

		foreach( $orders as $order )
		{
			if ( $orderStatusesAreSet )
			{
				if ( $order["Done"] )
					$orderStatus = $statuses["CONF_COMPLETED_ORDER_STATUS"];
				else
					$orderStatus =	$statuses["CONF_NEW_ORDER_STATUS"];
			}
			else
				$orderStatus = "NULL";

			$q = ss_db_query( "select productID, Price, Quantity from ".
					ORDERED_CARTS_TABLE." where orderID=".$order["orderID"] );
			$orderAmount = 0;
			while( $row=db_fetch_row($q) )
			{
				$orderAmount += $row["Price"]*$row["Quantity"];
			}

			// ???????????????????????????????????
			// �� ����� ����� ��������� ����� ?
			// if ( $order["calculate_tax"] )
			// {
			//		$order["tax"]
			//		$order["shipping_cost"]
			// }
			// $currency_code	- ?
			// $currency_value	- ?
			$currency_code = "";
			$currency_value = 1;


			$customerID = regGetIdByLogin( $order["customer_login"] );
			$q = ss_db_query( "select first_name, last_name, Email, addressID from ".
				CUSTOMERS_TABLE." where customerID=".$customerID );
			$customer = db_fetch_row($q);

			$billingAddress = ss_db_query( "select  first_name, last_name, countryID, zoneID, zip, ".
						" state, city, address from ".CUSTOMER_ADDRESSES_TABLE.
						" where customerID=".$customer["addressID"] );
			$billingAddress = db_fetch_row( $billingAddress );

			// �������� ��� countryID, zoneID
			ss_db_query( "insert into ".ORDERS_TABLE.
					" ( customerID, order_time, customer_ip, ".
					"   shipping_type, payment_type, customers_comment, ".
					"	statusID, shipping_cost, order_discount, ".
					"	order_amount, currency_code, currency_value, ".
					"	customer_firstname, customer_lastname, customer_email, ".
					"	shipping_firstname, shipping_lastname, shipping_country, ".
					"						shipping_state, shipping_zip, shipping_city, shipping_address, ".
					"	billing_firstname, billing_lastname, billing_country, ".
					"						billing_state, billing_zip, billing_city, billing_address, ".
					"	cc_number, cc_holdername, cc_expires, cc_cvv ) ".
					" values( ".
					"   '".$customerID."', '".$order["order_time"]."', '', ".
					"   '"._transStr($order["shipping_type"])."', '"._transStr($order["payment_type"])."', '"._transStr($order["customers_comment"])."',  ".
					"   ".$orderStatus.", ".$order["shipping_cost"].", 0, ".
					"	".$orderAmount.",  '"._transStr($currency_code)."', ".$currency_value.",  ".
					"	'"._transStr($customer["first_name"])."', '"._transStr($customer["last_name"])."', '"._transStr($customer["Email"])."', ".
					"	'', '', '', ".
					"						'', '', '', '"._transStr($order["final_shipping_address"])."', ".
					"   '"._transStr($billingAddress["first_name"])."', '"._transStr($billingAddress["last_name"])."', '', ".
					"						'"._transStr($billingAddress["state"])."', '"._transStr($billingAddress["zip"])."', '"._transStr($billingAddress["city"])."', '"._transStr($billingAddress["address"])."', ".
					"	'', '', '', '' )" );
		}
	}


	function _convert__ORDERED_CARTS_TABLE__Upgrade_sspro10_sspremium( $xmlNodeTableArray )
	{
		$ORDERED_CARTS_TABLE_content = array();
		$q = ss_db_query( "select productID, orderID, name, Price, Quantity from ".ORDERED_CARTS_TABLE );
		while ( $row=db_fetch_row($q) )
			$ORDERED_CARTS_TABLE_content[] = $row;
		ss_db_query( "delete from ".ORDERED_CARTS_TABLE );

		db_rename_column( ORDERED_CARTS_TABLE, "productID", "itemID", "int", null, false );
		db_add_column( ORDERED_CARTS_TABLE, "tax",			"float",	null,	true );
		db_add_column( ORDERED_CARTS_TABLE, "load_counter", "int",		0,		true );

		foreach( $ORDERED_CARTS_TABLE_content as $ORDERED_CARTS_TABLE_item )
		{
			$q1 = ss_db_query( "insert into ".SHOPPING_CART_ITEMS_TABLE." ( productID ) ".
								" values( ".$ORDERED_CARTS_TABLE_item["productID"]." ) " );
			$itemID = ss_db_insert_id();
			ss_db_query( "insert into ".ORDERED_CARTS_TABLE." ( itemID, orderID, name, Price, Quantity ) ".
							" values( ".$itemID.", ".$ORDERED_CARTS_TABLE_item["orderID"].", ".
									" '"._transStr($ORDERED_CARTS_TABLE_item["name"])."', ".
									" ".$ORDERED_CARTS_TABLE_item["Price"].",  ".
									" ".$ORDERED_CARTS_TABLE_item["Quantity"]." )" );
		}
	}


	function _convert__SHOPPING_CARTS_TABLE( $xmlNodeTableArray )
	{
		$q = ss_db_query( "select customer_login, productID, Quantity from ".SHOPPING_CARTS_TABLE );
		$SHOPPING_CARTS_TABLE_content = array();
		while( $row=db_fetch_row($q) )
			$SHOPPING_CARTS_TABLE_content[] = $row;

		db_delete_table( SHOPPING_CARTS_TABLE );
		_createTable( $xmlNodeTableArray, SHOPPING_CARTS_TABLE );
		_createTable( $xmlNodeTableArray, SHOPPING_CART_ITEMS_TABLE );
		foreach( $SHOPPING_CARTS_TABLE_content as $row )
		{
			$customerID = regGetIdByLogin( $row["customer_login"] );
			if ( $customerID == null )
				continue;
			ss_db_query( "insert into ".SHOPPING_CART_ITEMS_TABLE." ( productID ) ".
							" values( ".$row["productID"]." ) " );
			$itemID = ss_db_insert_id();
			ss_db_query( "insert into ".SHOPPING_CARTS_TABLE." ( customerID, itemID, Quantity ) ".
						 " values( ".$customerID.", ".$itemID.", ".$row["Quantity"]." )"	);
		}
	}


	function _convert__NEWS_TABLE( $xmlNodeTableArray )
	{
		$q = ss_db_query( "select add_date, Body, add_stamp from ".NEWS_TABLE );
		$data = array();
		while( $row = db_fetch_row($q) )
			$data[] = $row;
		db_delete_table( NEWS_TABLE );
		_createTable( $xmlNodeTableArray, NEWS_TABLE );
		foreach( $data as $row )
		{
			ss_db_query( "insert into ".NEWS_TABLE." ( add_date, title, picture, ".
				" textToPublication, textToMail ) ".
				" values ".
				" (  '".$row["add_date"]."', '', '', '"._transStr($row["Body"])."', '' ) " );
		}
	}

	function _convert__DISCUSSIONS_TABLE( $xmlNodeTableArray )
	{
		return;
	}

	function _convert__PRODUCT_OPTIONS_TABLE( $xmlNodeTableArray )
	{
		db_rename_column( PRODUCT_OPTIONS_TABLE, "sort_order", "sort_order", "int", 0, true );
	}

	function _convert__PRODUCT_OPTIONS_VALUES_TABLE( $xmlNodeTableArray )
	{
		db_add_column( PRODUCT_OPTIONS_VALUES_TABLE, "option_type", "bit",	0, true );
		db_add_column( PRODUCT_OPTIONS_VALUES_TABLE, "option_show_times", "int", 1, true );
		db_add_column( PRODUCT_OPTIONS_VALUES_TABLE, "variantID", "int", null, true );
	}


	function _convert__MAILING_LIST_TABLE( $xmlNodeTableArray )
	{
		db_add_column( MAILING_LIST_TABLE, "customerID", "int",	null, true );
		$q_mail = ss_db_query( "select MID, Email from ".MAILING_LIST_TABLE );
		while( $mail = db_fetch_row($q_mail) )
		{
			$q_customer = ss_db_query( "select customerID from ".
								CUSTOMERS_TABLE." where Email='"._transStr($mail["Email"])."'" );
			$customer = db_fetch_row( $q_customer );
			if ( $customer["customerID"] == null )
				$customer["customerID"] = "NULL";
			ss_db_query( "update ".MAILING_LIST_TABLE." set customerID=".$customer["customerID"].
							" where MID=".$mail["MID"] );
		}
	}

	function _convert__RELATED_PRODUCTS_TABLE( $xmlNodeTableArray )
	{
		return;
	}

	function _convert__SHIPPING_METHODS_TABLE( $xmlNodeTableArray )
	{
		db_delete_column( SHIPPING_METHODS_TABLE, "lump_sum" );
		db_delete_column( SHIPPING_METHODS_TABLE, "percent_value" );
		db_add_column( SHIPPING_METHODS_TABLE, "email_comments_text", "text", null, true );
		db_add_column( SHIPPING_METHODS_TABLE, "module_id", "int",	null, true );
		db_rename_column( SHIPPING_METHODS_TABLE, "sort_order", "sort_order", "int", 0, true );
	}


	function _convert__PAYMENT_TYPES_TABLE( $xmlNodeTableArray )
	{
		db_rename_column( PAYMENT_TYPES_TABLE, "sort_order", "sort_order",	"int",  0, true );
		db_add_column( PAYMENT_TYPES_TABLE, "module_id",			"int",	null,	true );
		db_add_column( PAYMENT_TYPES_TABLE, "email_comments_text",  "text", null,	true );
	}


	function _convert__CURRENCY_TYPES_TABLE( $xmlNodeTableArray )
	{
		db_rename_column( CURRENCY_TYPES_TABLE, "sort_order", "sort_order", "int", 0, true );
		db_add_column( CURRENCY_TYPES_TABLE, "currency_iso_3", "char(3)", null, true );
	}



	function UpgradeSSpro10_to_sspremium( $orderStatusesAreSet, $login, $password )
	{
		$xmlNodeTableArray = GetXmlTableNodeArray( DATABASE_STRUCTURE_XML_PATH );
		_createTable( $xmlNodeTableArray, SETTINGS_GROUPS_TABLE );
		_createTable( $xmlNodeTableArray, SETTINGS_TABLE );
		_convert__CUSTOMERS_TABLE( $xmlNodeTableArray );
		_add__ORDER_STATUES_TABLE( $xmlNodeTableArray, $orderStatusesAreSet );
		_convert__ORDERS_TABLE__Upgrade_sspro10_sspremium( $xmlNodeTableArray, $orderStatusesAreSet );
		_convert__SHOPPING_CARTS_TABLE( $xmlNodeTableArray );
		_convert__PRODUCTS_TABLE( $xmlNodeTableArray );
		_convert__CATEGORIES_TABLE( $xmlNodeTableArray );
		_convert__SPECIAL_OFFERS_TABLE( $xmlNodeTableArray );
		_convert__ORDERED_CARTS_TABLE__Upgrade_sspro10_sspremium( $xmlNodeTableArray );
		_convert__SPECIAL_OFFERS_TABLE( $xmlNodeTableArray );
		_convert__NEWS_TABLE( $xmlNodeTableArray );
		_convert__DISCUSSIONS_TABLE( $xmlNodeTableArray );
		_convert__PAYMENT_TYPES_TABLE( $xmlNodeTableArray );
		_convert__CURRENCY_TYPES_TABLE( $xmlNodeTableArray );
		_convert__SHIPPING_METHODS_TABLE( $xmlNodeTableArray );
		_convert__RELATED_PRODUCTS_TABLE( $xmlNodeTableArray );
		_convert__PRODUCT_OPTIONS_TABLE( $xmlNodeTableArray );
		_convert__PRODUCT_OPTIONS_VALUES_TABLE( $xmlNodeTableArray );
		_convert__MAILING_LIST_TABLE( $xmlNodeTableArray );
		_createRestTables__Upgrade_sspro10_sspremium( $xmlNodeTableArray );
		CreateReferConstraintsXML( DATABASE_STRUCTURE_XML_PATH );
	}

?>