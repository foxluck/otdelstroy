<?php
/*****************************************************************************
*                                                                           *
* Shop-Script PREMIUM                                                       *
* Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
*                                                                           *
*****************************************************************************/

require("includes/functions.php");
require("includes/xml_parser.php");
//require("includes/xml_installer.php");
require("includes/serialization_functions.php");
//require("includes/order_status_functions.php" );
//require("includes/setting_functions.php");
//require("includes/category_functions.php");
//require("includes/registration_functions.php" );
//require("includes/statistic_functions.php" );
//require("includes/datetime_functions.php" );
//require("includes/aux_pages_functions.php" );
//require("includes/crypto_functions.php" );
//require("includes/consts.php");
require("includes/upgrade_ssfree10_sspremium.php");
require("includes/upgrade_sspro10_sspremium.php");
//require("includes/payment_functions.php");

require("cfg/paths.inc.php" );

//require("includes/version_function.php");


require("includes/mysql.php");

//session_start();

@set_time_limit(0);

//MagicQuotesRuntimeSetting();
@ini_set("magic_quotes_runtime",0);
if(function_exists('set_magic_quotes_runtime')&&!preg_match('/^5\.3/',PHP_VERSION)){
	set_magic_quotes_runtime(false);
}

/*DEL*/function _testWriteable()
{
	if ( !IsWriteable( DATABASE_STRUCTURE_XML_PATH ) )
	return STRING_COULDNT_REWRITE_FILE." ".DATABASE_STRUCTURE_XML_PATH;
	if ( !IsWriteable(TABLES_INC_PHP_PATH) )
	return STRING_COULDNT_REWRITE_FILE." ".TABLES_INC_PHP_PATH;
	if ( !IsWriteable(CONNECT_INC_PHP_PATH) )
	return STRING_COULDNT_REWRITE_FILE." ".CONNECT_INC_PHP_PATH;
	if ( file_exists(RESULT_XML_PATH) )
	{
		if ( !IsWriteable(RESULT_XML_PATH) )
		return STRING_COULDNT_REWRITE_FILE." ".RESULT_XML_PATH;
	}
	return "";
}

function _upgradeOldDataBase($upgradeFrom='')
{
	CreateTablesIncFile( "cfg/tables.inc.php", DATABASE_STRUCTURE_XML_PATH );

	include( "cfg/tables.inc.php" );

	if ( $upgradeFrom == "free" )
	{
		UpgradeSSfree10_to_sspremium( $_POST["adminLogin"], $_POST["adminPassword"] );
		CallInstallfunctions();

		// settings
		serImportWithConstantNameReplacing( "sql/setting_groups.sql" );
		serImportWithConstantNameReplacing( "sql/setting_constants.sql", true );

		// b
		ss_db_query("INSERT INTO ".CURRENCY_TYPES_TABLE." (CID, Name, code, currency_value, where2show, currency_iso_3) VALUES (1, 'US Dollars', 'US $', '1', 0, 'USD')");
		_setDefaultCurrency();

		// c
		serImportWithConstantNameReplacing( "sql/order_statuses.sql" );
		_setNewOrderStatus();
		_setCompletedStatus();
		_initializeAuxPages();
	}
	else if ($upgradeFrom == "pro")
	{
		$is_20 = db_table_exists(strtolower(SYSTEM_TABLE))||db_table_exists(SYSTEM_TABLE);
		if(!$is_20){
			#from pro 1.0

			UpgradeSSpro10_to_sspremium( 1,
			$_POST["adminLogin"], $_POST["adminPassword"] );
			CallInstallfunctions();

			// b
			serImportWithConstantNameReplacing( "sql/order_statuses.sql" );
			_setNewOrderStatus();
			_setCompletedStatus();

			

			_initializeAuxPages();
		}else{

			#from pro 2.0
			$sql = "
						SELECT value FROM ".SYSTEM_TABLE."
						WHERE varName='version_name'
					";
			list($VersionName) = db_fetch_row(ss_db_query($sql));
			if($VersionName != 'PREMIUM'){

				$sql = '
							SELECT value FROM '.SYSTEM_TABLE.'
							WHERE varName="version_number"
						';
				$VersionNumber = 0;
				list($VersionNumber) = db_fetch_row(ss_db_query($sql));
				$VersionNumber=floatval($VersionNumber);
				switch ($VersionNumber){
					case 2:
					require('includes/upgrade_sspro20_sspremium.php');
					break;
					case 2.1:
					case 2.11:
					case 2.12:
					$is_21  = true;
					require('includes/upgrade_sspro210_sspremium.php');
					break;
				}
				if($VersionNumber<2.11){
					ss_db_query("ALTER TABLE ".CUSTOMERS_TABLE." ADD ActivationCode VARCHAR(16)");
					ss_db_query("ALTER TABLE ".NEWS_TABLE." ADD emailed INT");
					ss_db_query("ALTER TABLE ".NEWS_TABLE." ADD priority INT");
				}
			}
		}

		if(!isset($is_21)){

			if(db_table_exists('__temp_ss__module_payment_invoice_phys'))
			ss_db_query('ALTER TABLE __temp_SS__module_payment_invoice_phys ADD module_id INT NOT NULL');
			if(db_table_exists('__temp_ss__module_payment_invoice_jur'))
			ss_db_query('ALTER TABLE __temp_SS__module_payment_invoice_jur ADD module_id INT NOT NULL');
			if(db_table_exists('__temp_ss__module_shipping_bycountries_byzones_rates_percent'))
			ss_db_query('ALTER TABLE __temp_SS__module_shipping_bycountries_byzones_rates_percent ADD module_id INT NOT NULL');
			if(db_table_exists('__temp_ss__module_shipping_bycountries_byzones_rates'))
			ss_db_query('ALTER TABLE __temp_SS__module_shipping_bycountries_byzones_rates ADD module_id INT NOT NULL');
		}
		//it's in sql file 
		//ss_db_query("insert into ".SETTINGS_GROUPS_TABLE." (settings_groupID, settings_group_name, sort_order) values ( 7, 'Google Analytics', 6)");
		
	}
	else if ($upgradeFrom == "premium") //upgrading premium->premium
	{
		$version = verGetPackageVersion();
		if (!$version) throw new Exception("corrupted Shop-Script PREMIUM database (couldn't fetch version information)");

		if($version < 1.25){
		if($version < 1.24){
			if($version < 1.23){
				if ($version < 1.22)
				{

					if ($version <= 1.2)
					{

						if ($version <= 1.1)
						{
							// upgrade from v. 1.1 and older to 1.2
							ss_db_query("alter table ".ORDERS_TABLE." modify cc_number varchar(255), modify cc_holdername varchar(255), modify cc_expires varchar(255), modify cc_cvv varchar(255)");

							// get database structure from XML file
							$xmlNodeTableArray = GetXmlTableNodeArray( DATABASE_STRUCTURE_XML_PATH );
							// create missing tables
							for( $i=0; $i < count($xmlNodeTableArray);  $i++ )
							{
								$attr = $xmlNodeTableArray[$i]->GetXmlNodeAttributes();
								if ($attr["NAME"] == CUSTOMER_REG_FIELDS_VALUES_TABLE_QUICKREG )
								{
									$sql = GetCreateTableSQL( $xmlNodeTableArray[$i] );
									ss_db_query( $sql );
								}
							}
						}

						//now upgrade 1.2 to 1.21
						//add lacking constants (settings)
						ss_db_query( "delete from ".SETTINGS_TABLE." where settings_constant_name LIKE 'CONF_ADDRESSFORM_%'" );


					}

					//upgrade to 1.21 to 1.22
					//database structure
					/**
						 * new tables
						 */
					ss_db_query("
								CREATE TABLE ".AFFILIATE_COMMISSIONS_TABLE." (
									cID int(11) NOT NULL auto_increment,
									Amount float default NULL,
									CurrencyISO3 char(3) default NULL,
									xDateTime datetime default NULL,
									Description varchar(255) default NULL,
									OrderID int(11) default NULL,
									CustomerID int(11) default NULL,
									PRIMARY KEY  (cID),
									KEY CUSTOMERID (CustomerID)
								)
							");
					ss_db_query("
								CREATE TABLE ".AFFILIATE_PAYMENTS_TABLE." (
									pID int(11) NOT NULL auto_increment,
									CustomerID int(11) default NULL,
									Amount float default NULL,
									CurrencyISO3 char(3) default NULL,
									xDate date default NULL,
									Description varchar(255) default NULL,
									PRIMARY KEY  (pID),
									KEY CUSTOMERID (CustomerID)
								)					
							");
					ss_db_query("
								CREATE TABLE ".LINK_EXCHANGE_CATEGORIES_TABLE." (
									le_cID int(11) NOT NULL auto_increment,
									le_cName varchar(100) default NULL,
									le_cSortOrder int(11) default NULL,
									PRIMARY KEY  (le_cID)
								)
							");
					ss_db_query("
								CREATE TABLE ".LINK_EXCHANGE_LINKS_TABLE." (
									le_lID int(11) NOT NULL auto_increment,
									le_lText varchar(255) default NULL,
									le_lURL varchar(255) default NULL,
									le_lCategoryID int(11) default NULL,
									le_lVerified datetime default NULL,
									PRIMARY KEY  (le_lID)
								)
							");
					/**
						 * changes in tables
						 */
					ss_db_query("ALTER TABLE ".CATEGORIES_TABLE." ADD meta_description VARCHAR(255)");
					ss_db_query("ALTER TABLE ".CATEGORIES_TABLE." ADD meta_keywords VARCHAR(255)");
					//ss_db_query("ALTER TABLE ".CATEGORIES_TABLE." ADD INDEX IDX_CATEGORIES1(parent)");

					ss_db_query("ALTER TABLE ".CUSTOMERS_TABLE." ADD affiliateID INT DEFAULT 0");
					ss_db_query("ALTER TABLE ".CUSTOMERS_TABLE." ADD affiliateEmailOrders INT DEFAULT 1");
					ss_db_query("ALTER TABLE ".CUSTOMERS_TABLE." ADD affiliateEmailPayments INT DEFAULT 1");
					//ss_db_query("ALTER TABLE ".CUSTOMERS_TABLE." ADD INDEX AFFILIATEID(affiliateID)");

					ss_db_query("ALTER TABLE ".ORDER_STATUS_CHANGE_LOG_TABLE." ADD status_comment VARCHAR(255)");

					ss_db_query("ALTER TABLE ".ORDERS_TABLE." ADD affiliateID INT DEFAULT 0");

				}

				/**
					 * upgrade from 1.22 to 1.23
					 */
				ss_db_query('ALTER TABLE '.ORDERS_TABLE.' ADD shippingServiceInfo VARCHAR(255)');

				/**
					 * update modules
					 */
				ss_db_query("ALTER TABLE ".MODULES_TABLE." ADD ModuleClassName VARCHAR(255) NOT NULL");

				ss_db_query('UPDATE '.SETTINGS_TABLE.' SET settings_html_function="setting_SELECT_BOX(CVeriSignLink::getTranstypeOptions()" WHERE
						settings_constant_name="CONF_PAYMENTMODULE_VERISIGNLINK_TRANSTYPE"');
				ss_db_query('UPDATE '.SETTINGS_TABLE.' SET settings_html_function="setting_CURRENCY_SELECT(" WHERE
						settings_constant_name="CONF_PAYMENTMODULE_VERISIGNLINK_USD_CURRENCY"');

				ss_db_query('UPDATE '.SETTINGS_TABLE.' SET settings_html_function="setting_CURRENCY_SELECT(" WHERE
						settings_constant_name="CONF_PAYMENTMODULE_RUPAY_USD_CURRENCY"');

				ss_db_query('UPDATE '.SETTINGS_TABLE.' SET settings_html_function="setting_SELECT_BOX(CPSiGateHTML::getChargeTypeOptions()," WHERE
						settings_constant_name="CONF_PAYMENTMODULE_PSIGATEHTML_CHARGETYPE"');
				ss_db_query('UPDATE '.SETTINGS_TABLE.' SET settings_html_function="setting_CURRENCY_SELECT(" WHERE
						settings_constant_name="CONF_PAYMENTMODULE_PSIGATEHTML_USD_CURRENCY"');

				ss_db_query('UPDATE '.SETTINGS_TABLE.' SET settings_html_function="setting_SELECT_BOX(CProtx::getModeOptions()," WHERE
						settings_constant_name="CONF_PAYMENTMODULE_PROTX_MODE"');

				ss_db_query('UPDATE '.SETTINGS_TABLE.' SET settings_html_function="setting_CURRENCY_SELECT(" WHERE
						settings_constant_name="CONF_PAYMENTMODULE_NETREGISTRY_DOLLAR_CURRENCY"');

				ss_db_query('UPDATE '.SETTINGS_TABLE.' SET settings_html_function="setting_CURRENCY_SELECT(" WHERE
						settings_constant_name="CONF_PAYMENTMODULE_MALSE_CURR_TYPE"');

				ss_db_query('UPDATE '.SETTINGS_TABLE.' SET settings_html_function="setting_CURRENCY_SELECT(" WHERE
						settings_constant_name="CONF_PAYMENTMODULE_LINKPOINT_USD_CURRENCY"');

				ss_db_query('UPDATE '.SETTINGS_TABLE.' SET settings_html_function="setting_CURRENCY_SELECT(" WHERE
						settings_constant_name="CONF_PAYMENTMODULE_INVOICE_PHYS_CURRENCY"');

				ss_db_query('UPDATE '.SETTINGS_TABLE.' SET settings_html_function="setting_CURRENCY_SELECT(" WHERE
						settings_constant_name="CONF_PAYMENTMODULE_INVOICE_JUR_CURRENCY"');

				ss_db_query('UPDATE '.SETTINGS_TABLE.' SET settings_html_function="setting_CURRENCY_SELECT(" WHERE
						settings_constant_name="CONF_PAYMENTMODULE_EGOLD_USD_CURRENCY"');

				ss_db_query('UPDATE '.SETTINGS_TABLE.' SET settings_html_function="setting_CURRENCY_SELECT(" WHERE
						settings_constant_name="CONF_PAYMENTMODULE_2CO_USD_CURRENCY"');

				if(db_table_exists('__temp_ss__module_payment_invoice_phys'))
				ss_db_query('ALTER TABLE __temp_SS__module_payment_invoice_phys ADD module_id INT NOT NULL');
				if(db_table_exists('__temp_ss__module_payment_invoice_jur'))
				ss_db_query('ALTER TABLE __temp_SS__module_payment_invoice_jur ADD module_id INT NOT NULL');
				if(db_table_exists('__temp_ss__module_shipping_bycountries_byzones_rates_percent'))
				ss_db_query('ALTER TABLE __temp_SS__module_shipping_bycountries_byzones_rates_percent ADD module_id INT NOT NULL');
				if(db_table_exists('__temp_ss__module_shipping_bycountries_byzones_rates'))
				ss_db_query('ALTER TABLE __temp_SS__module_shipping_bycountries_byzones_rates ADD module_id INT NOT NULL');
				
				
			}
			
			//probably it's in sql file too
			ss_db_query("insert into ".SETTINGS_GROUPS_TABLE." (settings_groupID, settings_group_name, sort_order) values ( 7, 'Google Analytics', 6)");
			// 1.23 --> 1.24
			ss_db_query("ALTER TABLE ".CUSTOMERS_TABLE." ADD ActivationCode VARCHAR(16)");
			ss_db_query("ALTER TABLE ".NEWS_TABLE." ADD emailed INT");
			ss_db_query("ALTER TABLE ".NEWS_TABLE." ADD priority INT");
		}
			
		}
		
	}

	/**
		 * add new columns in tables
		 */
	//updateTablesStructure();
	//print "<[:D";exit;
	/**
		 * import new constants
		 */
	serImportConstWithChecking( "sql/setting_constants.sql");
	
		
	
	// update package version
	verUpdatePackageVersion();
	verUpdatePackageName();

	// register admin
	//settingDefineConstants();
	//regRegisterAdmin( $_POST["adminLogin"], $_POST["adminPassword"] , true, $OldAdminLogin);
	//regAuthenticate( $_POST["adminLogin"], $_POST["adminPassword"] );


}

/*DEL*/function _sendByMail()
{
	//void
}

function _setDefaultCurrency()
{
	_setSettingOptionValue( "CONF_DEFAULT_CURRENCY", 1 );
}

function _setNewOrderStatus()
{
	_setSettingOptionValue( "CONF_NEW_ORDER_STATUS", 2 );
}

function _setCompletedStatus()
{
	_setSettingOptionValue( "CONF_COMPLETED_ORDER_STATUS", 5 );
}

function _setCustGroupByDefault()
{
	_setSettingOptionValue( "CONF_DEFAULT_CUSTOMER_GROUP", 1 );
}

function _setPaymentShippingMethod()
{
	payResetPaymentShippingMethods( 1 );
	paySetPaymentShippingMethod( 1, 1 );
	paySetPaymentShippingMethod( 1, 2 );
	paySetPaymentShippingMethod( 1, 3 );
	paySetPaymentShippingMethod( 1, 4 );
	paySetPaymentShippingMethod( 1, 5 );

	payResetPaymentShippingMethods( 2 );
	paySetPaymentShippingMethod( 2, 1 );
	paySetPaymentShippingMethod( 2, 2 );
	paySetPaymentShippingMethod( 2, 3 );
	paySetPaymentShippingMethod( 2, 4 );
	paySetPaymentShippingMethod( 2, 5 );

	payResetPaymentShippingMethods( 3 );
	paySetPaymentShippingMethod( 3, 1 );
	paySetPaymentShippingMethod( 3, 2 );
	paySetPaymentShippingMethod( 3, 3 );
	paySetPaymentShippingMethod( 3, 4 );
	paySetPaymentShippingMethod( 3, 5 );

	payResetPaymentShippingMethods( 4 );
	paySetPaymentShippingMethod( 4, 1 );
	paySetPaymentShippingMethod( 4, 2 );
	paySetPaymentShippingMethod( 4, 3 );
	paySetPaymentShippingMethod( 4, 4 );
	paySetPaymentShippingMethod( 4, 5 );


	payResetPaymentShippingMethods( 5 );
	paySetPaymentShippingMethod( 5, 1 );
	paySetPaymentShippingMethod( 5, 2 );
	paySetPaymentShippingMethod( 5, 3 );
	paySetPaymentShippingMethod( 5, 4 );
	paySetPaymentShippingMethod( 5, 5 );
}

function _initializeAuxPages()
{
	$aux_page_text =
	"<h1>Information page #1</h1>Your text here";
	auxpgAddAuxPage( 	"About your shopping cart",
	$aux_page_text, 1,
	"", "" );

	$aux_page_text =
	"<h1>Information page #2</h1>Your text here";
	auxpgAddAuxPage( 	"Shipping and payment",
	$aux_page_text, 1,
	"", "" );
}


?>