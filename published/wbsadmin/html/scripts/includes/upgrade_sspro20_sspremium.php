<?php
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

ss_db_query("ALTER TABLE ".CUSTOMERS_TABLE." ADD affiliateID INT DEFAULT 0");
ss_db_query("ALTER TABLE ".CUSTOMERS_TABLE." ADD affiliateEmailOrders INT DEFAULT 1");
ss_db_query("ALTER TABLE ".CUSTOMERS_TABLE." ADD affiliateEmailPayments INT DEFAULT 1");
ss_db_query("ALTER TABLE ".CUSTOMERS_TABLE." ADD INDEX AFFILIATEID(affiliateID)");

ss_db_query("ALTER TABLE ".ORDERS_TABLE." ADD affiliateID INT DEFAULT 0");

db_add_column( PRODUCTS_TABLE, "eproduct_filename",			"varchar(255)",	null,	true );
db_add_column( PRODUCTS_TABLE, "eproduct_available_days",	"int",			"5",	true );
db_add_column( PRODUCTS_TABLE, "eproduct_download_times",	"int",			"5",	true );

db_add_column( CATEGORIES_TABLE, "allow_products_comparison",	"int",	0, true );
db_add_column( CATEGORIES_TABLE, "allow_products_search",		"int",	1, true );

ss_db_query("
	CREATE TABLE ".PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE." (
	  variantID int(11) NOT NULL auto_increment,
	  optionID int(11) NOT NULL default '0',
	  option_value varchar(255) default NULL,
	  sort_order int(11) default '0',
	  PRIMARY KEY  (variantID)
	)
");

ss_db_query("
	CREATE TABLE ".CATEGORY_PRODUCT_OPTIONS_TABLE." (
	  optionID int(11) NOT NULL default '0',
	  categoryID int(11) NOT NULL default '0',
	  set_arbitrarily int(11) default '1',
	  PRIMARY KEY  (optionID,categoryID)
	)
");

ss_db_query("
	CREATE TABLE ".CATEGORY_PRODUCT_OPTION_VARIANTS." (
	  optionID int(11) NOT NULL default '0',
	  categoryID int(11) NOT NULL default '0',
	  variantID int(11) NOT NULL default '0',
	  PRIMARY KEY  (optionID,categoryID,variantID)
	)
");

ss_db_query("
	CREATE TABLE ".CATEGORIY_PRODUCT_TABLE." (
	  productID int(11) NOT NULL default '0',
	  categoryID int(11) NOT NULL default '0',
	  PRIMARY KEY  (productID,categoryID)
	)
");

ss_db_query("
	CREATE TABLE ".PRODUCTS_OPTIONS_SET_TABLE." (
	  productID int(11) NOT NULL default '0',
	  optionID int(11) NOT NULL default '0',
	  variantID int(11) NOT NULL default '0',
	  price_surplus float default '0',
	  PRIMARY KEY  (productID,optionID,variantID)
	)
");

ss_db_query("
	CREATE TABLE ".SHOPPING_CART_ITEMS_CONTENT_TABLE." (
	  itemID int(11) NOT NULL default '0',
	  variantID int(11) NOT NULL default '0'
	)
");

/**
 * to 1.23
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
?>