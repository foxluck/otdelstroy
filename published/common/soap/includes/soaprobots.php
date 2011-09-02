<?php

	//
	// SOAP error codes and functions for external robots support
	//

	define( "SOAPROBOT_ERR_AUTHORIZATION", 1 );
	define( "SOAPROBOT_ERR_QUERYEXECUTING", 2); // Unable to execute query
	define( "SOAPROBOT_ERR_INVALIDCHARS", 2001 );
	define( "SOAPROBOT_ERR_DBKEYEXISTS", 2002 );
	define( "SOAPROBOT_ERR_DATABASEOPTION", 2000 );
	define( "SOAPROBOT_ERR_DBKEYFORMAT", 2003 );
	define( "SOAPROBOT_ERR_INVALIDFIELDLENGTH", 2004 );
	define( "SOAPROBOT_ERR_DBEXISTS", 2005 );
	define( "SOAPROBOT_ERR_EMPTYFIELD", 2006 );
	define( "SOAPROBOT_ERR_PASSWORDMISMATCH", 2007 );
	define( "SOAPROBOT_ERR_PASSWORDLENGTH", 2008 );
	define( "SOAPROBOT_ERR_INVALIDLOGINCHARS", 2009 );
	define( "SOAPROBOT_ERR_INVALIDNAMESTARTCHARS", 2010 ); // First name and last name must start with a Latin character
	define( "SOAPROBOT_ERR_DATEFORMAT", 2011 );
	define( "SOAPROBOT_ERR_INTFORMAT", 2012 );
	define( "SOAPROBOT_ERR_DBPROFILECREATE", 2013 ); // Unable to create database profile file
	define( "SOAPROBOT_ERR_DBKEYNOTFOUND", 2015 ); // Your Database Key is not found
	define( "SOAPROBOT_ERR_DBPROFILELOADING", 2016 ); // Error loading database profile file
	define( "SOAPROBOT_ERR_INVALIDADMINPASSWORD", 2017 ); // Invalid administrator password	
	define( "SOAPROBOT_ERR_INVALIDFIELD", 2018 ); // Invalid field
	define( "SOAPROBOT_ERR_INVALIDACCOUNTEMAIL", 2019 ); // Invalid account email address
	define( "SOAPROBOT_ERR_ACCOUNTNAMERESERVED", 3001 ); // Invalid account email address
	define( "SOAPROBOT_ERR_ACCOUNTNAMECREATION", 3002 ); // Invalid account email address
?>