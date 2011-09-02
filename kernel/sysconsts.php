<?php

	//
	//
	// WBS kernel and AA constants
	//
	//

	define( "WBS_DEF_NAME", "<your company name>" );

	define( "DB_FIRST", 0 );
	define( "DB_ARRAY", 1 );
	define( "DB_ARRAY_ORDERED", 2 );

	define( "LANG_RUS", "rus" );
	define( "LANG_ENG", "eng" );

	define( "WBS_USERNAME", "wbs_username" );
	define( "WBS_DBKEY", "wbs_dbkey" );
	define( "WBS_HOSTID", "wbs_hostid" );
	define( "WBS_SID", "WBS_SID" );

	define( "MIN_PASSWORD_LEN", 4 );

	define( "AA_APP_ID", "AA" );
	define( "MW_APP_ID", "MW" );
	define( "CM_APP_ID", "CM" );
	define( "MM_APP_ID", "MM" );
	define( "WBSADMIN_APP_ID", "wbsadmin" );
	
	//
	// Common string constants and parameter names
	//

	define( "LANGUAGE", "language" );
	define( "PASSWORD", "password" );
	define( "TEMPLATE", "template" );
	define( "SCREEN", "screen" );
	define( "MENULINKS", "menuLinks" );
	define( "VALUE", "value" );
	define( "MAILFORMAT", "mailformat" );
	define( "U_TIMEZONE", "timezone" );

	define( "SCREEN_LAYOUT", "layout" );
	define( "SCREEN_THEME", "theme" );
	define( "SCREEN_LOGO", "logo" );
	define( "SCREEN_CORNERS", "corners" );
	define ("SHOW_COMPANYTOP", "show_company_top");
	define ("SHOW_COMPANYNAMETOP", "show_company_name_top");

	define( "U_RECEIVESMESSAGES", "receivesmessages" );
	define( "U_CHANGEPASSWORD_RIGHT", "changePasssRight" );
	define( "U_CHANGETEMPLATE_RIGHT", "changeTemplateRight" );
	define( "U_CHANGENAME_RIGHT", "changeNameRight" );
	define( "U_SWITCHEMAIL_RIGHT", "switchEmailRight" );

	define( "USE_BLANK", "BLANK" );
	define( "USE_LAST", "LAST" );
	define( "USE_TIPSANDTRICKS", "TIPSANDTRICKS" );
	define( "ALLOW_DRACCESS", "ALLOW_DRACCESS" );

	if ( !defined('START_PAGE') )
		define( "START_PAGE", "START_PAGE" );

	//
	// User settings parameter names
	//

	define( "COMMONSETTINGS", "COMMONSETTINGS" );
	define( "MN_XML_NOTIFICATIONS", "FORBIDDEN_NOTIFICATIONS" );
	define( "MN_XML_NOTIFICATION", "NOTIFICATION" );
	define( "MN_XML_ID", "ID" );
	define( "MN_XML_APP_ID", "APP_ID" );
	define( "XML_FIRSTLOGINFLAG", "FIRSTLOGINFLAG" );

	//
	// Administrator account constants
	//

	define( "ADMIN_USERNAME", "ADMINISTRATOR" );

	//
	// Action names
	//

 	define( "ACTION", "action" );
 	define( "ACTION_NEW", "new" );
 	define( "ACTION_EDIT", "edit" );
 	define( "ACTION_DELETE", "delete" );
 	define( "ACTION_RENEW", "renew" );
 	
	//
	// Record status constants
	//

	define( "RS_ACTIVE", 0 );
	define( "RS_DELETED", 1 );
	define( "RS_LOCKED", 2 );

	$commonStatusNames = array( RS_ACTIVE=>'app_activestatus_name', RS_LOCKED=>'app_lockedstatus_name' );

	//
	// Login result codes
	//

	define( "LRC_INVALIDUSER", 1 );
	define( "LRC_INACTIVEUSER", 2 );
	define( "ST_INVALID", -1 );
	define( "ST_OK", 0 );
	define( "ST_INVALIDDA", -3 );

	//
	// User error constants
	//

	define("FATAL", E_USER_ERROR);
	define("ERROR", E_USER_WARNING);

	//
	// Default user settings
	//

	define( "XML_DEF_USERSETTINGS", "<%sxml version=\"1.0\"%s><COMMONSETTINGS START_PAGE=\"%s\" template=\"classic\" language=\"%s\" mailformat=\"html\"/>" );
	
	$DEF_USERSETTINGS = array(
			'START_PAGE' => "", 
			'template' => "classic",
			'language' => "", 
			'mailformat' => "html"
	);
	//
	// System error constants
	//

	define( "ERR_XML", 'app_errxml_message' );
	define( "ERR_REQUIREDFIELDS", 'app_requiredfields_message' );

	define( "ERR_SAVINGUSERSETTINGS", 'app_saveusersets_message' );
	define( "ERR_DATEFORMAT", 'app_invdateformat_message' );
	define( "ERR_TEXTLENGTH", 'app_textlen_message' );
	define( "ERR_GENERALACCESS", 'app_generalaccess_message' );
	define( "ERR_ATTACHFILE", 'app_attachfile_message' );
	define( "ERR_CREATEDIRECTORY", 'app_createdir_message' );
	define( "ERR_DELETEFILEFROMLIST", 'app_delfromlist_message' );
	define( "ERR_DELETEFILE", 'app_deletefile_message' );
	define( "ERR_COPYFILE", 'app_copyfile_message' );
	define( "ERR_UNKNOWNRECIPIENTADDRESS", 'app_unknownaddr_message' );
	define( "ERR_HANDLERNOTFOUND", 'app_handlernotfound_message' );
	define( "ERR_NOTAPPROVED", 'app_notapproved_message' );
	define( "ERR_NOTAPPROVEDERR", 'app_notapprovederr_message' );
	define( "ERR_DEPENDENTERROR", 'app_deperror_message' );
	define( "ERR_INVALIDNUMFORMAT", 'app_invnumformat_message' );
	define( "ERR_SPACELIMITEXCEEDED", 'app_spacelimit_message' );

	//
	// Application error codes
	//

	define( "ERRCODE_APPLICATION_ERR", 1000 );
	define( "ERRCODE_INVALIDFIELD", 1001 );
	define( "ERRCODE_INVALIDDATE", 1002 );
	define( "ERRCODE_INVALIDLENGTH", 1003 );
	define( "ERRCODE_HANDLERNOTFOUND", 1004 );
	define( "ERRCODE_NOTAPPROVED", 1005 );
	define( "ERRCODE_NOTAPPROVEDERR", 1006 );
	define( "ERRCODE_USEREXISTS", 1007 );
	define( "ERRCODE_SERVERNOTFOUND_ERR", 1008 );
	define( "ERRCODE_EMPTYREQGROUP", 1009 );
	define( "ERRCODE_INVCONTACTFIELD", 1010 );
	define( "ERRCODE_DUPLICATEEMAIL", 1011 );
	define( "ERRCODE_ADDEXTRAEMAIL", 1012 );

	//
	// Screens and applications attributes
	//

	define( "APP_ID", "APP_ID" );
	define( "APP_FOLDER", "APP_FOLDER" );
	define( "SCR_NAME", "NAME" );
	define( "SCR_UI_NAME", "UI_NAME" );
	define( "SCR_ID", "ID" );
	define( "SCR_PAGE", "PAGE" );
	define( "SCR_DESC", "DESC" );
	define( "MN_NAME", "NAME" );
	define( "SCR_TARGET", "TARGET" );

	define( "APP_NAME", "APP_NAME" );
	define( "APP_UI_NAME", "APP_UI_NAME" );
	define( "APP_PARENTS", "APP_PARENTS" );
	define( "APP_QUOTABLE", "APP_QUOTABLE" );
	define( "APP_SORTORDER", "APP_SORTORDER" );

	//
	// Application registration file consts
	//

	define( "APP_REG_WBSAPPLICATION", "WBSAPPLICATION" );
	define( "APP_REG_APPLICATION", "APPLICATION" );
	define( "APP_REG_APP_NAME", "APP_NAME" );
	define( "APP_REG_APP_QUOTABLE", "QUOTABLE" );
	define( "APP_REG_APP_SORTORDER", "SORTORDER" );
	define( "APP_REG_NAME", "NAME" );
	define( "APP_REG_UI_NAME", "UI_NAME" );
	define( "APP_REG_PARENTS", "PARENTS" );
	define( "APP_REG_PARENT", "PARENT" );
	define( "APP_REG_APP_ID", "APP_ID" );
	define( "APP_REG_EVENT_HANDLERS", "EVENT_HANDLERS" );
	define( "APP_REG_HANDLER", "HANDLER" );
	define( "APP_REG_HANDLER_PROC", "HANDLER_PROC" );
	define( "APP_REG_HANLDER_SCRIPT", "HANLDER_SCRIPT" );
	define( "APP_REG_MAIL_NOTIFICATIONS", "MAIL_NOTIFICATIONS" );
	define( "APP_REG_NOTIFICATION", "NOTIFICATION" );
	define( "APP_REG_ID", "ID" );
	define( "APP_REG_SCREENS", "SCREENS" );
	define( "APP_REG_SCREEN", "SCREEN" );
	define( "APP_REG_SCR_ID", "SCR_ID" );
	define( "APP_REG_SCR_PAGE", "SCR_PAGE" );
	define( "APP_REG_SCR_NAME", "SCR_NAME" );
	define( "APP_REG_EVENTS", "EVENTS" );
	define( "APP_REG_EVENT", "EVENT" );
	define( "APP_REG_PRIVATE", "PRIVATE" );
	define( "APP_REG_DEFAULT_LANGUAGE", "DEFAULT_LANGUAGE" );
	define( "APP_REG_ROBOTS", "ROBOTS" );
	define( "APP_REG_ROBOT", "ROBOT" );
	define( "APP_REG_PASSWORD", "PASSWORD" );
	define( "APP_REG_SERVICES", "SERVICES" );
	define( "APP_REG_USERRIGHTS", "USERRIGHTS" );
	define( "APP_REG_TREEDOCUMENT", "TREEDOCUMENT" );
	define( "APP_REG_ACCESSTABLE", "ACCESSTABLE" );
	define( "APP_REG_GROUPACCESSTABLE", "GROUPACCESSTABLE" );
	define( "APP_REG_TABLENAME", "TABLENAME" );
	define( "APP_REG_USERID", "USERID" );
	define( "APP_REG_GROUPID", "GROUPID" );
	define( "APP_REG_FOLDERID", "FOLDERID" );
	define( "APP_REG_RIGHTS", "RIGHTS" );
	define( "APP_REG_FOLDERTABLE", "FOLDERTABLE" );
	define( "APP_REG_FOLDERPARENTID", "FOLDERPARENTID" );
	define( "APP_REG_FOLDERNAME", "FOLDERNAME" );
	define( "APP_REG_FOLDERSTATUS", "STATUS" );
	define( "APP_REG_AUXRIGHTS", "AUXILIARY" );
	define( "APP_REG_RIGHT", "RIGHT" );
	define( "APP_REG_TYPE", "TYPE" );
	define( "APP_REG_DEFAULT", "DEFAULT" );
	define( "APP_REG_VARIABLE", "VARIABLE" );
	define( "APP_REG_DEPENDENCES", "DEPENDENCES" );
	define( "APP_REG_ENABLED", "ENABLED" );
	define( "APP_REGISTER_FILE", "wbs_application.xml" );

	define( "APP_REG_DOCUMENTTABLE", "DOCUMENTTABLE" );
	define( "APP_REG_DOCUMENTID", "DOCUMENTID" );
	define( "APP_REG_STATUS", "STATUS" );
	define( "APP_REG_MODIFYUID", "MODIFYUID" );

	define( "APP_REG_RIGHTS_ROOT", "ROOT" );
	define( "APP_REG_RIGHTS_ACCESS", "ACCESS" );
	define( "APP_REG_RIGHTS_ITEM",   "ITEM" );
	define( "APP_REG_RIGHTS_ITEMNAME",   "ITEMNAME" );
	define( "APP_REG_RIGHTS_READ",   "READ" );
	define( "APP_REG_RIGHTS_WRITE",  "WRITE" );
	define( "APP_REG_RIGHTS_FOLDER", "FOLDER" );

	define( "APP_REG_LOCAL_NAME", "LOCAL_NAME" );
	define( "APP_REG_LOCAL_UI_NAME", "LOCAL_UI_NAME" );

	define( "APP_CHECKED", "CHECKED" );

	//
	// Application settings constants
	//

	define( "APP_SETTINGS", "APP_SETTINGS" );
	
	// Ajax constants
	define ("SIMPLE_AJAX_DELIMITER", "__-WBS_AJAX_DEL-__");

	//
	// Attached files management constants
	//

	define( "AAF_ADD", 0 );
	define( "AAF_REPLACE", 1 );

	define( "AF_FILELIST", "FILELIST" );
	define( "AF_FILE", "FILE" );
	define( "AF_FILENAME", "FILENAME" );
	define( "AF_SCREENFILENAME", "SCREENFILENAME" );
	define( "AF_MIME_TYPE", "MIME_TYPE" );
	define( "AF_FILESIZE", "FILESIZE" );
	define( "AF_COMMENT", "COMMENT" );
	define( "AF_DISKFILENAME", "DISKFILENAME" );
	define( "AF_TMPFILENAME", "TMPFILENAME" );
	define( "AF_FILEDATE", "FILEDATE" );

	define( "AF_NEWFILE", "0" );
	define( "AF_EXISTINGFILE", "1" );

	define( "TMP_FILES_PREFIX", "tmp_" );

	define( "GIGABYTE_SIZE_RELATIVE", 1048576000 );
	define( "MEGABYTE_SIZE", 1048576 );
	define( "DEFUSED_MEMORY", 8 );

	define( "SYS_USER_ID", '$SYSTEM' );

	//
	// Symbols allowed for user input
	//

	define( "ID_SYMBOLS", "0123456789_-.abcdefghijklmnopqrstuvwxyz" );
	define( "KEY_SYMBOLS", "abcdefghijklmnopqrstuvwxyz0123456789" );
	define( "ALPHA_SYMBOLS", "abcdefghijklmnopqrstuvwxyz" );
	define( "DBNAME_SYMBOLS", '0123456789_-!@#$%^&()+={}[],~abcdefghijklmnopqrstuvwxyz' );

	//
	// Mail constants
	//

 	$ms_priority = array( 0=>"Low", 1=>"Normal", 2=>"High" );

	define( "MAILFORMAT_HTML", "html" );
	define( "MAILFORMAT_TEXT", "text" );
	$mail_formats = array( MAILFORMAT_HTML=>'amu_htmlmail_name', MAILFORMAT_TEXT=>'amu_textmail_name' );

	define( "HTMLE_BASE64", 1 );
	define( "HTMLE_QUOTEDPRINTABLE", 0 );

	//
	// Event handling constants
	//

	define( "APP_SCRIPTPATH", "appScriptPath" );
	define( "APP_DIRPATH", "appDirPath" );
	define( "KERNEL_LOCSTRINGS", "kernelStrings" );

	define( "CALL_TYPE", "CALL_TYPE" );
	define( "CT_ACTION", "CT_ACTION" );
	define( "CT_APPROVING", "CT_APPROVING" );
	define( "EVENT_APPROVED", "EVENT_APPROVED" );

	//
	// Date and time consts
	//

	$phpDateFormats = array( "MM/DD/YYYY"=>"m/d/Y", "MM.DD.YYYY"=>"m.d.Y", "DD.MM.YYYY"=>"d.m.Y" );
	$dateFormats = array( "m/d/Y"=>"MM/DD/YYYY", "m.d.Y"=>"MM.DD.YYYY", "d.m.Y"=>"DD.MM.YYYY" );
	$dateDelimiters = array( "m/d/Y"=>"/", "m.d.Y"=>".", "d.m.Y"=>"." );
	
	define ("DATEFORMAT_RUSSIAN", "d.m.Y");

	$timeZoneNameOffset = 600;
	$timeZoneNumber = 70;

	$timeZones = array( -720,-660,-600,-540,-480,-430,-360,-360,-360,-360,-360,-300,-300,-300,-240,-240,-240,
						-210,-180,-180,-180,-120,-60,-60,0,0,60,60,60,60,120,120,120,120,120,120,180,60,
						60,60,60,240,240,270,300,300,330,345,360,360,390,420,420,480,480,480,480,480,540,
						540,540,570,570,600,600,600,600,600,660,720,720 );

	define( "DATEFORMAT_DMY", "DMY" ); // 16 December 2004

	$monthFullNames = array( 'app_monjanfull_name', 'app_monfebfull_name', 'app_monmarfull_name', 'app_monaprfull_name', 'app_monmayfull_name',
							'app_monjunfull_name', 'app_monjulfull_name', 'app_monaugfull_name', 'app_monsepfull_name', 'app_monoctfull_name',
							'app_monnovfull_name', 'app_mondecfull_name' );

	$monthShortNames = array( 'app_monjanshort_name', 'app_monfebshort_name', 'app_monmarshort_name', 'app_monaprshort_name', 'app_monmayshort_name',
							'app_monjunshort_name', 'app_monjulshort_name', 'app_monaugshort_name', 'app_monsepshort_name', 'app_monoctshort_name',
							'app_monnovshort_name', 'app_mondecshort_name' );

	$shortWeekDays = array( 'app_weekmonshort_name', 'app_weektueshort_name', 'app_weekwedshort_name',
							'app_weekthushort_name', 'app_weekfrishort_name', 'app_weeksatshort_name',
							'app_weeksunshort_name' );

							
							
	//
	// Host data file consts
	//

	define( "HOST_DATABASE", "DATABASE" );
	define( "HOST_DBSETTINGS", "DBSETTINGS" );
	define( "HOST_PASSWORD", "PASSWORD" );
	define( "HOST_CREATEDATE", "CREATE_DATE" );
	define( "HOST_SIGNUP_DATETIME", "SIGNUP_DATETIME" );
	define( "HOST_EXPIRE_DATE", "EXPIRE_DATE" );
	define( "HOST_DATE_FORMAT", "DATE_FORMAT" );
	define( "HOST_DBSIZE_LIMIT", "DBSIZE_LIMIT" );
	define( "HOST_APPLICATIONS", "APPLICATIONS" );
	define( "HOST_LOGINHASHES", "LOGINHASHES" );
	define( "HOST_LOGINHASH", "LOGINHASH" );
	define( "HOST_ADVSETTINGS", "ADVSETTINGS" );
	define( "HOST_ADVPARAM", "PARAM" );
	define( "HOST_UNCONFIRMED", "UNCONFIRMED");
	define( "HOST_APPLICATION", "APPLICATION" );
	define( "HOST_APP_ID", "APP_ID" );
	define( "HOST_ADMINISTRATOR", "ADMINISTRATOR" );
	define( "HOST_TEMPLATE", "TEMPLATE" );
	define( "HOST_LANGUAGE", "LANGUAGE" );
	define( "HOST_READONLY", "READONLY" );
	define( "HOST_FIRSTLOGIN", "FIRSTLOGIN" );
	define( "HOST_LOGINNAME", "LOGINNAME" );
	define( "HOST_FIRSTNAME", "FIRSTNAME" );
	define( "HOST_LASTNAME", "LASTNAME" );
	define( "HOST_EMAIL", "EMAIL" );
	define( "HOST_COMPANYNAME", "COMPANYNAME" );
	define( "HOST_STATUS", "STATUS" );
	define( "HOST_STATUS_DELETED", "DELETED" );
	define( "HOST_DB_KEY", "DB_KEY" );
	define( "HOST_MAXUSERCOUNT", "MAX_USER_COUNT" );
	define( "HOST_MAXMAILBOXCOUNT", "MAX_MAILBOX_COUNT" );
	define( "HOST_RECIPIENTSLIMIT", "RECIPIENTS_LIMIT" );
	define( "HOST_SMS_RECIPIENTSLIMIT", "SMS_RECIPIENTS_LIMIT" );
	define( "HOST_DEFAULTENCODING", "DEFAULT_ENCODING" );
	define( "HOST_SQLSERVER", "SQLSERVER" );
	define( "HOST_DBNAME", "DB_NAME" );
	define( "HOST_DBPASSWORD", "DB_PASSWORD" );
	define( "HOST_TRIALDATASOURCE", "TRIAL_DATASOURCE" );
	define( "HOST_DBUSER", "DB_USER" );
	define( "HOST_DB_CREATE_OPTIONS", "DB_CREATE_OPTIONS" );
	define( "HOST_CREATE_OPTION", "CREATE_OPTION" );
	define( "HOST_DATABASE_USER", "DATABASE_USER" );
	define( "HOST_SOURCE", "SOURCE" );
	define( "HOST_DB_CREATE_OPTION", "DB_CREATE_OPTION" );
	define( "HOST_TEMPORARY", "TEMPORARY" );
	define( "HOST_BILLINGDATE", "BILLING_DATE" );
	define( "HOST_AUTORENEW", "AUTORENEW_MTO_ID" );

	define( "HOST_MODULES", "MODULES" );
	define( "HOST_ASSIGN", "ASSIGN" );
	define( "HOST_BALANCE", "BALANCE" );

	define( "HOST_ID", "ID" );
	define( "HOST_VALUE", "VALUE" );
	define( "HOST_CLASS", "CLASS" );
	define( "HOST_DISABLED", "DISABLED" );

	define( "HOST_DATABASE_USER_NEW", "DATABASE_USER_NEW" );
	define( "HOST_PASSWORD_NEW", "PASSWORD_NEW" );
	define( "HOST_DATABASE_USER_EXISTING", "DATABASE_USER_EXISTING" );
	define( "HOST_PASSWORD_EXISTING", "PASSWORD_EXISTING" );
	define( "HOST_DATABASE_NEW", "DATABASE_NEW" );
	define( "HOST_DATABASE_EXISTING", "DATABASE_EXISTING" );

	define( "HOST_SETTINGS", "SETTINGS" );
	define( "HOST_OPTION", "OPTION" );
	define( "HOST_OPTION_NAME", "NAME" );

	
	// new billing 
	//  
	define( "HOST_INSTALLED_FREE", 'DD,PD,CM,MM,PM,IT,QN,QP,SC,ST' );
	define( "HOST_PLAN_NAME", "SET_PLAN" );
	define( "HOST_INSTALL_APPS", "INSTALL_APPS" );
	define( "HOST_DEFAULT_PLAN", "FREE" );
	define( "HOST_PLAN_DB", "PLAN" );
	define( "HOST_FREE_APPS", "FREE_APPS" );
	define( "HOST_CUSTOM_APPS", "CUSTOM_APPS" );

	define( "HOST_CUSTOM_PLAN", "CUSTOM" );
	define( "HOST_OLD_CUSTOM_PLAN", "PAID" );
	
	
	define( "HOST_RECIPIENTS_LIMIT", "-1" );
	define( "HOST_SMS_RECIPIENTS_LIMIT", "5" );
	
	define( "HOST_LOG_DEFINED_PLAN", 'PLAN:%s;PERIOD: %s;MAX_USER_COUNT:%s;USERS:%s'  );
	
	define( "HOST_SC_FIRST_REST", 'prod'  );
	define( "HOST_SC_SECOND_REST", 'mo'  );
	
	//
	// Application pages, folders and email notifications constants
	//
	define( "SECTION_PAGES", "PAGES" );
	define( "SECTION_AUXRIGHTS", "AUXRIGHTS" );
	define( "SECTION_NOTIFICATIONS", "MAIL" );
	define( "SECTION_FOLDERS", "FOLDERS" );
	define( "SECTION_FOLDERRIGHTS", "FOLDERRIGHTS" );
	define( "SECTION_ROOTRIGHTS", "ROOTRIGHTS" );
	define( "SECTION_SHOWSHAREDPANEL", "SHOWSHARED" );

	define( "FOLDERS_NOFOLDERS", "NOFOLDERS" );

	//
	// User common system constants
	//

	$userCommonSysSettings = array( TEMPLATE,
									LANGUAGE,
									MAILFORMAT,
									START_PAGE,
									ALLOW_DRACCESS,
									WBS_ENCODING,
									U_RECEIVESMESSAGES,
									U_CHANGEPASSWORD_RIGHT,
									U_CHANGETEMPLATE_RIGHT,
									U_CHANGENAME_RIGHT,
									U_SWITCHEMAIL_RIGHT );

	$userCommonSysSettingsDefaults = array(
									LANGUAGE => LANG_ENG,
									MAILFORMAT => MAILFORMAT_HTML,
									ALLOW_DRACCESS => 0,
									U_RECEIVESMESSAGES => 1,
									U_CHANGEPASSWORD_RIGHT => 0,
									U_CHANGETEMPLATE_RIGHT => 0,
									U_CHANGENAME_RIGHT => 0,
									U_SWITCHEMAIL_RIGHT => 0);

	$userCommonAccessSettings = array( ALLOW_DRACCESS,
										U_CHANGEPASSWORD_RIGHT,
										U_CHANGETEMPLATE_RIGHT,
										U_CHANGENAME_RIGHT,
										U_SWITCHEMAIL_RIGHT );

	$userCommonAccessSettingPaths = array(
										ALLOW_DRACCESS => "/ROOT/MW/DA/DIRECTACCESS",
										U_CHANGEPASSWORD_RIGHT => "/ROOT/MW/SCREENS/CP",
										U_CHANGETEMPLATE_RIGHT => "/ROOT/MW/SCREENS/LF",
										U_CHANGENAME_RIGHT => "/ROOT/MW/SCREENS/PF/NC",
										U_SWITCHEMAIL_RIGHT => "/ROOT/MW/SCREENS/PF/EMAIL"
										);

	//
	// My WebAsyst constants
	//

	define( "MYWEBASYST_APP_ID", "MW" );
	define( "MYWEBASYST_PREFERENCES", "PF" );
	define( "MYWEBASYST_LOOKANDFEEL", "LF" );
	define( "MYWEBASYST_PASSWORD", "CP" );
	define ("WIDGETS_APP_ID", "WG");
	define ("UG_APP_ID", "UG");

	$myWebAsystRightsMapping = array(
										MYWEBASYST_PASSWORD => U_CHANGEPASSWORD_RIGHT,
										MYWEBASYST_LOOKANDFEEL => U_CHANGETEMPLATE_RIGHT
									);

	//
	// Database creation options
	//

	define( "DB_CREATION_NEW", "new" );
	define( "DB_CREATION_USEEXISTING", "use" );

	define( "DB_MAXDBKEYLEN", 64 );

	//
	// Default host variable values
	//

	define( "HOST_DEF_DATE_FORMAT", "MM/DD/YYYY" );
	define( "HOST_DEF_DBSIZE_LIMIT", "5" );
	define( "HOST_DEF_TEMPLATE", "classic" );
	define( "HOST_DEF_LANGUAGE", "eng" );
	define( "HOST_DEF_MAXUSERCOUNT", "5" );
	define( "HOST_SMS_BALANCE", '0.40');

	//
	// Database-dependent constants
	//

	define( "SQL_STRING_DELITIMTER", "'" );

	// Database date formats
	//
	define( "DATE_SQL_INPUT_FORMAT", "Y-m-d" );
	define( "DATE_SQL_OUTPUT_FORMAT", "Y-m-d" );
	define( "DATE_SQL_OUTPUT_DELIMITER", "-" );
	define( "TIME_SQL_INPUT_FORMAT", "H:i:s" );

	define( "WBS_DATABASE_TYPE", "mysql" );

	//
	// Common directories and settings
	//

	define( "ERR_LOG_FILE", WBS_DIR."kernel/errors.log" );
	define( "WBS_DBLSIT_DIR", WBS_DIR."dblist" );
	define( "WBS_PUBLISHED_DIR", WBS_DIR."published" );

	define( "WBS_PUBLICDATA_DIR", WBS_PUBLISHED_DIR."/publicdata" );

	define( "WBS_MODULES_DIR", WBS_DIR."/kernel/includes/wbsmodules" );

	define( "WBS_SMARTY_DIR", WBS_DIR."kernel/includes/smarty" );

	define( "WBS_TEMP_DIR", WBS_DIR."temp" );
	define( "PCLZIP_TEMPORARY_DIR", WBS_TEMP_DIR."/" );
	define( "TMP_FILES_LIFETIME", 12 );

	define( "MAIL_ENVELOPE_PATH_HTML", WBS_DIR."kernel/includes/mail_envelope.htm" );
	define( "MAIL_ENVELOPE_PATH_TEXT", WBS_DIR."kernel/includes/mail_envelope.txt" );

	define( "LOG_FILE_NAME", "wbs.log" );
	define( "SUPPORT_ADDRESS", "support@webasyst.net" );
	//
	// Account log constants
	//
	define( "ACCOUNT_LOG_FILE_NAME", "account.log" );

	define( "aop_signup", "SIGNUP" );
	define( "aop_dbcreate", "DBCREATE" );
	define( "aop_modify", "MODIFY" );
	define( "aop_delete", "DELETE" );
	define( "aop_remove", "REMOVE" );
	define( "aop_restore", "RESTORE" );

	define( "AOPR_LOG", "LOG" );
	define( "AOPR_ACCOUNT", "ACCOUNT" );
	define( "AOPR_LOGRECORD", "LOGRECORD" );
	define( "AOPR_TYPE", "TYPE" );
	define( "AOPR_DATETIME", "DATETIME" );
	define( "AOPR_SOURCE", "SOURCE" );
	define( "AOPR_DBKEY", "DB_KEY" );
	define( "AOPR_IP", "IP" );
	define( "AOPR_MODIFICATIONS", "MODIFICATIONS" );
	define( "AOPR_APPLICATIONS_ADDED", "APPLICATIONS_ADDED" );
	define( "AOPR_APPLICATIONS_REMOVED", "APPLICATIONS_REMOVED" );
	define( "AOPR_OPTION_MODIFICATION", "OPTION_MODIFICATION" );
	define( "AOPR_CLASS", "CLASS" );
	define( "AOPR_NAME", "NAME" );
	define( "AOPR_PREV", "PREV" );
	define( "AOPR_NEW", "NEW" );

	//
	// Tree document-folder representation classes
	//

	// Folder constants
	//

	define( "TREE_ROOT_FOLDER", 'ROOT' );

	define( 'TREE_REGULAR_FOLDER', 'REGULAR' );
	define( 'TREE_RECYCLED_FOLDER', 'RECYCLED' );
	define( 'TREE_AVAILABLE_FOLDERS', 'AVAILABLEFOLDERS' );

	// Folder rights
	//
	define( "TREE_ACCESS_RIGHTS", "TREE_ACCESS_RIGHTS" );

	define( "TREE_NOACCESS", 0 );

	define( "TREE_ONLYREAD", 1 );
	define( "TREE_ONLYWRITE", 2 );
	define( "TREE_ONLYFOLDER", 4 );

	define( "TREE_WRITEREAD", TREE_ONLYREAD |  TREE_ONLYWRITE );
	define( "TREE_READWRITE", TREE_ONLYREAD |  TREE_ONLYWRITE );
	define( "TREE_READWRITEFOLDER", TREE_ONLYREAD |  TREE_ONLYWRITE | TREE_ONLYFOLDER );

	$tree_access_mode_names = array( TREE_ONLYREAD=>'app_readaccessshort_name', TREE_WRITEREAD=>'app_writeaccessshort_name', TREE_READWRITEFOLDER=>'app_folderaccessshort_name' );
	$tree_access_mode_long_names = array( TREE_ONLYREAD=>'app_readaccess_name', TREE_WRITEREAD=>'app_readwraccess_name', TREE_READWRITEFOLDER=>'app_readwrfldaccess_name' );

	// Document constants
	//
	define( "TREE_DLSTATUS_NORMAL", 0 );
	define( "TREE_DLSTATUS_DELETED", -1 );

	// Folder constants
	//
	define( "TREE_FSTATUS_NORMAL", 0 );
	define( "TREE_FSTATUS_DELETED", -1 );

	// Operations constants
	//
	define( "TREE_COPYDOC", 0 );
	define( "TREE_MOVEDOC", 1 );
	define( "TREE_COPYFOLDER", 3 );
	define( "TREE_MOVEFOLDER", 4 );

	// User settings constants
	//
	define( "TREE_SHOWWHAREDPANEL", "_SHOWWHAREDPANEL" );

	//
	// Contacts and contact type descriptions
	//

	define( "CONTACT_BASIC_TYPE", "CON" );
	define( "CONTACT_CONTACTGROUP_ID", "CONTACT" );
	define( "CONTACT_PHOTOGROUP_ID", "PHOTO" );
	define( "DEF_CONTACT_FOLDER", "1." );

	define( "CONTACT_FIELD", "FIELD" );
	define( "CONTACT_GROUP", "FIELDGROUP" );
	define( "CONTACT_FIELDS", "FIELDS" );
	define( "CONTACT_ALIGN", "ALIGN" );
	define( "CONTACT_DBFIELD", "DBFIELD" );
	define( "CONTACT_FIELDID", "ID" );
	define( "CONTACT_GROUPID", "ID" );
	define( "CONTACT_REQUIRED", "REQUIRED" );
	define( "CONTACT_MAXLEN", "MAXLEN" );
	define( "CONTACT_DECPLACES", "DECPLACES" );
	define( "CONTACT_REQUIRED_GROUP", "REQUIRED_GROUP" );
	define( "CONTACT_FIELDGROUPID", "GROUPID" );
	define( "CONTACT_FIELDGROUPNAME", "GROUPNAME" );
	define( "CONTACT_MENU", "MENU" );
	define( "CONTACT_MENU_SEPARATOR", "^&^" );

	define( "CONTACT_FIELDUNIQUE", "UNIQUE" );
	define( "CONTACT_FIELDMANDATORY", "MANDATORY" );

	define( "CONTACT_FIELDGROUP_LONGNAME", "LONG_NAME" );
	define( "CONTACT_FIELDGROUP_SHORTNAME", "SHORT_NAME" );

	define( "CONTACT_NAMEFIELD", "NAME" );
	define( "CONTACT_NAMEVALUE", "VALUE" );
	define( "CONTACT_IDFIELD", "ID" );
	define( "CONTACT_FIRSTNAMEFIELD", "C_FIRSTNAME" );
	define( "CONTACT_LASTNAMEFIELD", "C_LASTNAME" );
	define( "CONTACT_NICKNAMEFIELD", "C_NICKNAME" );
	define( "CONTACT_EMAILFIELD", "C_EMAILADDRESS" );
	define( "CONTACT_MIDNAMEFIELD", "C_MIDDLENAME" );

	//
	// Concact field types
	//

	define( "CONTACT_FIELD_TYPE", "TYPE" );
	define( "CONTACT_FT_TEXT", "TEXT" );
	define( "CONTACT_FT_URL", "URL" );
	define( "CONTACT_FT_EMAIL", "EMAIL" );
	define( "CONTACT_FT_MEMO", "MEMO" );
	define( "CONTACT_FT_NUMERIC", "NUMERIC" );
	define( "CONTACT_FT_MENU", "MENU" );
	define( "CONTACT_FT_DATE", "DATE" );
	define( "CONTACT_FT_IMAGE", "IMAGE" );

	// Scalar field types
	//
	$scalarFieldTypes = array( CONTACT_FT_TEXT, CONTACT_FT_URL, CONTACT_FT_EMAIL, CONTACT_FT_MEMO, CONTACT_FT_NUMERIC, CONTACT_FT_DATE );

	$exportingFieldTypes = array( CONTACT_FT_TEXT, CONTACT_FT_URL, CONTACT_FT_EMAIL, CONTACT_FT_MEMO, CONTACT_FT_NUMERIC, CONTACT_FT_DATE, CONTACT_FT_MENU );

	// Custom contact fields
	//
	$contactCustomFieldsDesc = array(
									CONTACT_NAMEFIELD => array(
										CONTACT_FIELDGROUP_LONGNAME => 'ul_usernamefull_title',
										CONTACT_FIELDGROUP_SHORTNAME => 'ul_username_title',
										CONTACT_FIELD_TYPE => CONTACT_FT_TEXT
									)
								);

	//
	// Contact image field description
	//

	define( "CONTACT_IMGF_FILENAME", "FILENAME" );
	define( "CONTACT_IMGF_SIZE", "SIZE" );
	define( "CONTACT_IMGF_DISKFILENAME", "DISKFILENAME" );
	define( "CONTACT_IMGF_TYPE", "TYPE" );
	define( "CONTACT_IMGF_MIMETYPE", "MIMETYPE" );
	define( "CONTACT_IMGF_DATETIME", "DATETIME" );
	define( "CONTACT_IMGF_MODIFIED", "MODIFIED" );
	define( "CONTACT_IMGF_PREVFILENAME", "PREVFILENAME" );

	define( "CONTACT_IMGF_IMAGE", "IMAGE" );
	define( "CONTACT_IMG_FILEPREFIX", "IMG" );

	//
	// _Unsorted contact folder name
	//

	define( "UNSORTED_FOLDER_NAME", "_Unsorted" );

	//
	// Known image field types
	//

	$knownImageFieldFormats = array('jpg', 'gif', 'png');

	// Contact mandatory fields
	//
	$contactMandotoryFields = array( CONTACT_FIRSTNAMEFIELD, CONTACT_LASTNAMEFIELD, CONTACT_MIDNAMEFIELD, CONTACT_NICKNAMEFIELD, CONTACT_EMAILFIELD );

	//
	// User groups support
	//

	define( "UGR_ACTIVE", -3 );
	define( "UGR_INACTIVE", -2 );
	define( "UGR_DELETED", -1 );

	$user_group_status_link = array( UGR_ACTIVE=>RS_ACTIVE, UGR_INACTIVE=>RS_LOCKED, UGR_DELETED=>RS_DELETED );

	define( "UG_NAME", "UG_NAME" );
	define( "UG_ID", "UG_ID" );

	define( "SELECTED_GROUP", "LAST_UG_ID" );

	$ul_defaultColumnSet = array( CONTACT_NAMEFIELD );
	$ul_listColumnSet =  array( CONTACT_IDFIELD,
								CONTACT_NAMEFIELD,
								CONTACT_FIRSTNAMEFIELD,
								CONTACT_LASTNAMEFIELD,
								CONTACT_NICKNAMEFIELD,
								CONTACT_EMAILFIELD );

	// User list view modes
	//

	define( "UL_GRID_VIEW", "GRID" );
	define( "UL_LIST_VIEW", "LIST" );
	define( "UL_NOCOLUMNS", "NOCOLUMNS" );

	// CSV-files support
	//
	$csv_file_separators = array( ",", "\t", ";" );

	$cm_separators = array( ","=>"ecl_comma_item", "\t"=>"ecl_tab_item", ";"=>"ecl_semicolon_item", "."=>"ecl_period_item" );

	define( "CSV_CUSTOM_FORMAT", -1 );
	define( "CSV_DBNAME", "csv_dbname" );
	define( "CSV_DBREQUIRED", "csv_dbrequired" );
	define( "CSV_DBREQUIREDGROUP", "csv_dbrequiredgroup" );

	define( "CSV_LINKS", "LINKS" );
	define( "CSV_FILEFIELD", "FILEFIELD" );
	define( "CSV_DBFIELD", "DBFIELD" );
	define( "CSV_DBFIELDREQUIRED", "REQUIRED" );
	define( "CSV_IMPORTFIRSLN", "IMPORTFIRSTLINE" );
	define( "CSV_DELIMITER", "DELIMITER" );

	define( "USERLIST_FILEFORMATS", "USERLIST" );

	define( "FIF_LIST", "LIST" );
	define( "FIF_USER", "U_ID" );

	// Import/export constants
	//
	define( "CM_FILEFORMATS", "CONTACTS" );

	// Thumbnails
	//
	define( "THUMB_MAX_RESOLUTION", 1 );

	// Access types
	//
	define( "ACCESS_INDIVIDUAL", "IND" );
	define( "ACCESS_GROUP", "GROUP" );
	define( "ACCESS_SUMMARY", "SUM" );

	// Identity types
	//
	define( "IDT_GROUP", "GROUP" );
	define( "IDT_USER", "USER" );

	// Default group settings
	//
	define( "XML_DEF_GROUPSETTINGS", "<%sxml version=\"1.0\"%s><COMMONSETTINGS/>" );

	// Tips and Tricks
	//
	define( 'WBS_TT_ENDPOINT', "http://webasyst.webasyst.net/wbs/QP/soap/qp_webservice.php?DB_KEY=webasyst" );
	define( 'WBS_TT_IMG_PATH', "://webasyst.webasyst.net/wbs/publicdata/WEBASYST/attachments/qp/attachments/" );
	define( 'WBS_TT_SOAP_USER', 'WEBASYST_QUICKPAGES' );
	define( 'WBS_TT_SOAP_PWD', '81dc9bdb52d04dc20036dbd8313ed055' );

	define( "WBS_TT_QUICKSTART_SEEN", 'QUICKSTART_SEEN' );

	$qp_service_options = array( 'namespace' => 'urn:SOAP_QP_Server' );

	// Modules classes
	//

	define( "MODULE_CLASS_SMS", "sms" );
	define( "MODULE_CLASS_EMAIL", "email" );

	$modulesClasses = array(
		MODULE_CLASS_SMS => "mod_class_sms_name",
		MODULE_CLASS_EMAIL => "mod_class_email_name"
	);


	// Email sending modes
	//
	define( "EMAIL_MODE_MAIL", "MAIL" );
	define( "EMAIL_MODE_SENDMAIL", "SENDMAIL" );
	define( "EMAIL_MODE_QMAIL", "QMAIL" );
	define( "EMAIL_MODE_SMTP", "SMTP" );
	
	define( "EMAIL_MAX_RECEPIENTS_COUNT", 10);

	// SMS Constants

	define( "SMS_SYSTEM_USER", '$SYSTEM' );

	define( "SMS_STATUS_DELIVERED", 'DELIVERED' );
	define( "SMS_STATUS_PENDING", 'PENDING' );
	define( "SMS_STATUS_CANCELED", 'CANCELED' );
	define( "SMS_STATUS_CHARGE_ERROR", 'CHARGE_ERROR' );

	define( "SMS_CANCELING_TIMEOUT", 86400 );

	$sms_StatusNamesArray = array(
		SMS_STATUS_DELIVERED => "app_sms_delivered_status",
		SMS_STATUS_PENDING => "app_sms_pending_status",
		SMS_STATUS_CANCELED => "app_sms_canceled_status"
	);

	//
	// Contact text variables constants
	//

	// Variable sets
	//
	define( "VS_CONTACT", "CONTACT" );
	define( "VS_CURRENT_USER", "CURRENT_USER");
	define( "VS_COMPANY", "COMPANY" );

	// Available text field types
	//
	$contactTextFieldTypes = array( CONTACT_FT_TEXT, CONTACT_FT_URL, CONTACT_FT_EMAIL, CONTACT_FT_MEMO, CONTACT_FT_NUMERIC, CONTACT_FT_DATE, CONTACT_FT_MENU );

	// Company text constants
	//
	define( "CMP_TXTVAR_NAME", "COMPANY_NAME" );
	define( "CMP_TXTVAR_STREET", "COMPANY_STREETADDRESS" );
	define( "CMP_TXTVAR_CITY", "COMPANY_CITY" );
	define( "CMP_TXTVAR_STATE", "COMPANY_STATE" );
	define( "CMP_TXTVAR_ZIP", "COMPANY_ZIP" );
	define( "CMP_TXTVAR_COUNTRY", "COMPANY_COUNTRY" );
	define( "CMP_TXTVAR_CONTACT_NAME", "COMPANY_CONTACTNAME" );
	define( "CMP_TXTVAR_CONTACT_EMAIL", "COMPANY_CONTACTEMAIL" );
	define( "CMP_TXTVAR_CONTACT_PHONE", "COMPANY_CONTACTPHONE" );
	define( "CMP_TXTVAR_CONTACT_FAX", "COMPANY_CONTACTFAX" );

	$companyTextVariableNames = array( CMP_TXTVAR_NAME=>"app_cmpname_name",
										CMP_TXTVAR_STREET=>"app_cmpstreetaddr_name",
										CMP_TXTVAR_CITY=>"app_cmpcity_name",
										CMP_TXTVAR_STATE=>"app_cmpstatev_name",
										CMP_TXTVAR_ZIP=>"app_cmpzip_name",
										CMP_TXTVAR_COUNTRY=>"app_cmpcountry_name",
										CMP_TXTVAR_CONTACT_NAME=>"app_cmpcontactname_name",
										CMP_TXTVAR_CONTACT_EMAIL=>"app_cmpcontactemail_name",
										CMP_TXTVAR_CONTACT_PHONE=>"app_cmpcontactphone_name",
										CMP_TXTVAR_CONTACT_FAX=>"app_cmpcontactfax" );

	$companyTextVariableMap = array( CMP_TXTVAR_NAME=>"COM_NAME",
										CMP_TXTVAR_STREET=>"COM_ADDRESSSTREET",
										CMP_TXTVAR_CITY=>"COM_ADDRESSCITY",
										CMP_TXTVAR_STATE=>"COM_ADDRESSSTATE",
										CMP_TXTVAR_ZIP=>"COM_ADDRESSZIP",
										CMP_TXTVAR_COUNTRY=>"COM_ADDRESSCOUNTRY",
										CMP_TXTVAR_CONTACT_NAME=>"COM_CONTACTPERSON",
										CMP_TXTVAR_CONTACT_EMAIL=>"COM_EMAIL",
										CMP_TXTVAR_CONTACT_PHONE=>"COM_PHONE",
										CMP_TXTVAR_CONTACT_FAX=>"COM_FAX" );

	//
	// Contact Manager contacts
	//

	// Subscriber status
	//
	define( 'CM_SBST_PENDING', -1 );
	define( 'CM_SBST_ACTIVE', 1 );

	$subscruberStatusNames = array( CM_SBST_PENDING=>'app_pendingsubscr_label', CM_SBST_ACTIVE=>'app_normalsubscr_label' );
	
	// Rights
	//
	define( 'CM_MANAGELISTS_RIGHTS', 'MANAGELISTS' );
	define( 'CM_MANAGEUSERS_RIGHTS', 'MANAGEUSERS' );
	define( 'APP_CANTOOLS_RIGHTS', 'CANTOOLS');
	define ('APP_CANREPORTS_RIGHTS', 'CANREPORTS');
	define ('APP_CANWIDGETS_RIGHTS', 'CANWIDGETS');
	
	// Special folder statuses
	//
	define ('FOLDER_SPECIALSTATUS_PM_ROOT', '11');
	define ('FOLDER_SPECIALSTATUS_PM_PROJECT', '2');
	define ('FOLDER_SPECIALSTATUS_DD_SUBFOLDER', '1');
	
	//
	// Escaping string types
	//
	define('ESCSQLTYPE_GENERAL', 1);
	define('ESCSQLTYPE_HOLDERS', 2);

	// Account management constants
	//
	define('URL_UPGRADE_HELP', 'http://www.webasyst.net/hosted-accounts.htm');
	define('URL_REGISTER_HELP', 'http://www.webasyst.net/hosted-accounts.htm');

	define('PLAN_FREE', 'FREE');
	define('BILLING_BEFORE_SUSPEND_DAYS', 5);
	
	define ("AA_START_SEARCH_DATE", '03/04/1986');
?>
