<?php

	define( "PAGE_DB_AUTH", "auth.php" );
	define( "PAGE_DB_WBSADMIN", "wbsadmin.php" );
	
	define( "PAGE_SECTION_BUY", "more.php" );	
	
	define( "PAGE_SECTION_SETUP", "setup.php" );
	
	define( "PAGE_DB_COMMON","setup.php?section=common");
	define( "PAGE_FRONTEND_SETUP","setup.php?section=frontend");
	if(file_exists("dblist.php")&&file_exists('multidbkey.php')){
		define( "PAGE_DB_DBLIST", "setup.php?section=dblist");			
		define( "PAGE_DB_DBPROFILE", "setup.php?section=dbprofile" );
		
		define( "PAGE_DB_SQLSERVERS", "setup.php?section=sqlservers");			
		define( "PAGE_DB_ADDMODSERVER", "setup.php?section=addmodserver" );
	}else{
		define( "PAGE_DB_DBLIST", "setup.php?section=dbprofile" );
		define( "PAGE_DB_DBPROFILE", "setup.php?section=dbprofile" );
			
		define( "PAGE_DB_SQLSERVERS", "setup.php?section=addmodserver");	
		define( "PAGE_DB_ADDMODSERVER", "setup.php?section=addmodserver" );
	}
	define( "PAGE_SMSMODULES", "setup.php?section=sms");
	define( "PAGE_MODULESMOD", "setup.php?section=modulesmod" );
	define( "PAGE_MODULESINSTALL", "setup.php?section=modulesinstall" );
	define( "PAGE_DB_LANGUAGES", "setup.php?section=languages");
	define( "PAGE_WA_MIGRATE", "setup.php?section=migrate" );

	define( "PAGE_SECTION_DIAGNOSTIC", "diagnostics.php" );	
	define( "PAGE_SECTION_UPDATE", "updatewa.php" );
	
	define( "PAGE_WA_UPDATE", "updatewa.php" );
	
	define( "PAGE_DB_LOCALIZATION", "setup.php?section=localization" );
	define( "PAGE_DB_ADDMODLANGUAGE", "setup.php?section=addmodlanguage" );
	define( "PAGE_DB_IMPORTEXPORTLANGUAGE", "setup.php?section=importexportlang" );
	define( "PAGE_DB_EXPORTLANGUAGE", "setup.php?section=exportlanguage" );
	
	define( "PAGE_DB_LOGDATA", "logdata.php" );

	define( "PAGE_DB_BALANCE", "balance.php" );
	
	//define( "PAGE_DB_WBSINSTALL_STEP1", "step1.php" );
	define( "PAGE_DB_WBSINSTALL_STEP1", "firststep.php" );
	define( "PAGE_DB_WBSINSTALL_STEP2", "step2.php" );
	define( "PAGE_DB_WBSINSTALL_STEP3", "step3.php" );

	define( "PAGE_DB_SETUPSERVERS", "setupservers.php" );
	define( "PAGE_DB_SETUPDBPROFILE", "setupdbprofile.php" );

	


	define( "DB_DEF_DATE_FORMAT", "m/d/Y" );

	define( "GUIDE_FILE", "../../../docs/WebAsystAdminGuide.pdf" );
	//define( "INSTALL_GUIDE_FILE", "../../../docs/WebAsystInstallGuide.pdf" );
	define( 'INSTALL_GUIDE_FILE', '../../../../help/webasystinstallguide.htm' );


	define( "WBS_MIMMEMORYSETTING", 8 );

	$accountOperationNames = array( aop_signup => 'app_aopsignedup_name', aop_dbcreate => 'app_aopdbcreate_name', aop_modify => 'app_aopmodify_name',
									aop_delete => 'app_aopdelete_name', aop_remove => 'app_aopremove_name', aop_restore=>'app_aoprestore_name' );

	$modificationClassesNames = array( HOST_DBSETTINGS => 33,
										 HOST_ADMINISTRATOR => 34,
										 HOST_FIRSTLOGIN => 35 );
	$modificationOptionNames = array( HOST_EXPIRE_DATE => 36,
										HOST_READONLY => 37,
										HOST_DBSIZE_LIMIT => 39,
										HOST_DATE_FORMAT => 41,
										HOST_MAXUSERCOUNT => 42,
										HOST_PASSWORD => 43,
										HOST_LANGUAGE => 44,
										HOST_COMPANYNAME => 45,
										HOST_LASTNAME => 46,
										HOST_FIRSTNAME => 47,
										HOST_EMAIL => 48,
										HOST_LOGINNAME => 49,
										HOST_PASSWORD => 50,
										HOST_DBUSER => 61,
										HOST_DBPASSWORD => 62,
										HOST_RECIPIENTSLIMIT => 63);
?>