<?php
/**
 * Autoload
 * 
 * @copyright WebAsyst © 2008-2009
 * @author WebAsyst Team
 * @version SVN: $Id: autoload.php 10187 2011-07-08 08:35:30Z alexmuz $
 * @see http://php.net/autoload
 */
class AutoLoad
{
	private static $__autoload = array();
	private static $__rules = array();
	
	/**
	 * Returns path by class name
	 * 
	 * @param string $class
	 * @return string
	 */
	public static function get($class)
	{
		// Array has high priority
		if (isset(self::$__autoload[$class])) {
			return self::$__autoload[$class];
		}
		elseif (preg_match("/([A-Z]{2})(.*?)Plugin.*/", $class, $match)) {
			return "published/".$match[1]."/plugins/".$match[2]."/".$class.".class.php";
		}
		elseif (substr($class, -16) == 'WidgetController') {
			return "published/WG/lib/".substr($class, 0, -16)."/".str_replace('Controller', '.controller.php', $class);	
		}
		elseif (substr($class, -6) == 'Action' || substr($class, -10) == 'Controller') {
		        $parts = strtolower(preg_replace("/^([A-Z]{2})([A-Z][a-z]+)([A-Z-0-9][a-z0-9]+)[A-Za-z0-9]*$/u", "$2|$3", $class));
		        $parts = explode("|", $parts);
	        	if ($parts[0] == 'ajax') {
		            $folder = $parts[1].'/ajax';
		        }
	        	else {
		            $folder = $parts[0];
		        }
		
	        	return "published/".substr($class, 0, 2)."/lib/actions/".$folder."/".str_replace(array('Action', 'Controller'), array('.action.php', '.controller.php'), $class);
		}
		elseif (substr($class, -6) == 'Layout') {
			return "published/".substr($class, 0, 2)."/lib/layouts/".str_replace("Layout", ".layout.php", $class);
		}
		elseif (substr($class, -9) == 'Decorator') {
			return "published/".substr($class, 0, 2)."/lib/decorators/".str_replace("Decorator", ".decorator.php", $class);
		} 
		// Check rules
		else {
			foreach (self::$__rules as $rule) {
				list($function, $path, $add_class) = $rule;
				
				if (is_callable($function) && $function($class)) {
					switch ($add_class) {
						case 1: 
							return $path . "/" . $class . ".class" . ".php";							
						case 2: 
							return $path . "/class." . strtolower($class) . ".php";
						case 3:
							return $path. "/". str_replace("Model", ".model.php", $class);							
						default:
							return $path . "/" . $class . ".php";
					}
				}
			}
		}


	}
	
	/**
	 * Add class and path to $__autoload 
	 * 
	 * @param string $class
	 * @param string $path
	 */
	public static function add($class, $path) 
	{
		self::$__autoload[$class] = $path;
	}
	
	/**
	 * Add rule for a group of classes
	 * 
	 * @param string $cond 'substr($class, -5) == "Query"'
	 * @param string $dir_path
	 *  
	 * @return bool
	 */
	public static function addRule($cond, $path, $add_class = 0) 
	{
		$function = create_function('$class', 'return '.$cond.';');
		self::$__rules[] = array($function, $path, $add_class);			
	}
	
	/**
	 * Load autoload from array
	 * 
	 * @param array $__autoload
	 */
	public static function load($__autoload)
	{
		if (is_array($__autoload)) {
			self::$__autoload = self::$__autoload + $__autoload;
		}
	}
}


// Autoload for the system
$__autoload = array(

    // Contact Models
    'ListsModel' => "published/CM/lib/model/Lists.model.php",
	'ContactListModel' => "published/CM/lib/model/ContactList.model.php",
	'ContactNotesModel' => "published/CM/lib/model/ContactNotes.model.php",

    // New Core
    "Env" => "system/lib/Env.class.php",
	"Url" => "system/lib/Url.class.php",
	"GetText" => "system/lib/GetText.class.php",
	"Registry" => "system/lib/Registry.class.php",

	"ContactWidget" => "published/CM/lib/entity/ContactWidget.class.php",
	"SupportForm" => "published/ST/lib/classes/SupportForm.class.php",

    "FileCacher" => "system/lib/model/cacher/FileCacher.class.php",
	"iCacher" => "system/lib/model/cacher/iCacher.class.php",

    "dsRTE" => "system/lib/dsrte/dsrte.php",

    //System path
    "AppPath" => "system/lib/AppPath.class.php",

	"WbsFiles" => "system/lib/filesystem/WbsFiles.php",
	"DiskQuotaManager" => "",

	//image libs
	"WbsImage" => "system/lib/image/WbsImage.class.php",
	"WbsImageGd" => "system/lib/image/WbsImageGd.class.php",

    "StringUtils" => "system/lib/utils/StringUtils.class.php",
    
	// exceptions
	"NoImplementedException" => "system/packages/exceptions/NoImplementedException.php",
	"RuntimeException" => "system/packages/exceptions/RuntimeException.php",
	"MySQLException" => "system/lib/exceptions/MySQLException.class.php",
	"MysqlException" => "system/lib/exceptions/MySQLException.class.php",
	"LimitException" => "system/lib/exceptions/LimitException.class.php",
	"SystemException" => "system/lib/exceptions/SystemException.class.php",
	"HideException" => "system/lib/exceptions/HideException.class.php",
	"UserException" => "system/lib/exceptions/UserException.class.php",

	"HTMLExceptionDecorator" => "system/lib/decorators/HTMLExceptionDecorator.class.php",

	// date
	"CDateTime" => "system/packages/date/CDate.php",
	"CDate" => "system/packages/date/CDate.php",
	"CTimeZone" => "system/packages/date/CTimeZone.php",
	"TimeZones" => "system/packages/date/TimeZones.php",
	"WbsDateTime" => "system/packages/date/WbsDateTime.php",
	"WbsTimeZone" => "system/packages/date/WbsTimeZone.php",
	// wbs
	"Wbs" => "system/packages/wbs/Wbs.php",
	"WbsSystem" => "system/packages/wbs/WbsSystem.php",
	"WbsApplication" => "system/packages/wbs/WbsApplication.php",
	"WbsUpdater" => "system/packages/wbs/WbsUpdater.php",
	"WbsSystemFiles" => "system/packages/wbs/WbsSystemFiles.php",
	"Dbkey" => "system/packages/wbs/Dbkey.php",
	"DbkeyFiles" => "system/packages/wbs/DbkeyFiles.php",
	"MainAppScreen" => "system/packages/wbs/Screen.php",
	// db
	"MysqlDb" => "system/packages/db/MysqlDb.php",
	"Wdb" => "system/packages/db/Wdb.php",
	// data_model
	"JsListWrapper" => "system/packages/data_model/JsListWrapper.php",
	"JsTreeWrapper" => "system/packages/data_model/JsTreeWrapper.php",
	"WbsDataModel" => "system/packages/data_model/WbsDataModel.php",
	"WbsFolder" => "system/packages/data_model/WbsFolder.php",
	"WbsFoldersTree" => "system/packages/data_model/WbsFoldersTree.php",
	"WbsRecord" => "system/packages/data_model/WbsRecord.php",
	"WbsRecordset" => "system/packages/data_model/WbsRecordset.php",
	"WbsListFolder" => "system/packages/data_model/WbsListFolder.php",
	"WbsTreeFolder" => "system/packages/data_model/WbsTreeFolder.php",
	"WbsTreeRootFolder" => "system/packages/data_model/WbsTreeRootFolder.php",
	"WbsFolderNode" => "system/packages/data_model/WbsFoldersNode.php",
	// mail
	"Mailer" => "system/packages/mail/Mailer.php",
	"MailMessage" => "system/packages/mail/MailMessage.php",
	"NotificationMessage" => "system/packages/mail/NotificationMessage.php",
	"MailBox" => "system/packages/mail/MailBox.php",
	"MailDecode" => "system/packages/mail/MailDecode.php",
	"MailParsers" => "system/packages/mail/MailParsers.php",
	// sms
	"SMS" => "system/packages/sms/sms.php",
	"hqsms" => "system/packages/sms/hqsms.php",
	// json
	"Services_JSON" => "kernel/classes/JSON.php",
	"metric" => "kernel/classes/class.metric.php",
	// files
	"FilesFunctions" => "system/packages/files/FilesFunctions.php",
	// WebQuery
	"WebQuery" => "system/packages/webquery/WebQuery.php",
	// Preproc and Smarty
	"Preproc" => "system/packages/preproc/Preproc.php",
	"Smarty" => "kernel/includes/smarty/Smarty.class.php",
	"WbsSmarty" => "system/lib/WbsSmarty.class.php",
	// Locale
	"waLocale" => "system/packages/localization/Locale.php",
	"LocalizationLoader" => "kernel/includes/locloader.class.php",
	// users
	"Users" => "system/packages/users/Users.php",
	"WbsUser" => "system/packages/users/WbsUser.php",

	"CurrentUser" => "system/packages/users/CurrentUser.php",
	"PublicUser" => "system/packages/users/PublicUser.php",
	"IUser" => "system/packages/users/IUser.php", 
	"RightsMask" => "system/packages/users/RightsMask.php",
	"DiskQuotaManager" => "system/packages/users/DiskQuotaManager.php",
	// contacts
    // sql 
    "CSqlFilter" => "system/packages/sql_query/CSqlFilter.php",

	// Basic models (for all applications)
	"GroupsModel" => "system/lib/model/Groups.model.php",
	"UsersModel" => "system/lib/model/Users.model.php",
	"ContactsModel" => "system/lib/model/Contacts.model.php",
	"UserRightsModel" => "system/lib/model/UserRights.model.php",
	"GroupsRightsModel" => "system/lib/model/GroupsRights.model.php",
	"FileFormatModel" => "system/lib/model/FileFormat.model.php",
	
	// Entity
	"Widget" => "system/lib/entity/Widget.class.php",
	"User" => "system/lib/entity/User.class.php",
    "Contact" => "system/lib/entity/Contact.class.php",
	"Groups" => "system/lib/entity/Groups.class.php",
	"Company" => "system/lib/entity/Company.class.php",
	"Rights" => "system/lib/entity/Rights.class.php",
	"RightValue" => "system/lib/entity/RightValue.class.php",
	"ContactType" => "system/lib/entity/ContactType.class.php",

	"CSV" => "system/lib/CSV.class.php",
	"Limits" => "system/lib/Limits.class.php",

	// Actions
	"MailAction" => "system/lib/actions/Mail.action.php",

	// Old Kernel
	"metric" => "kernel/classes/class.metric.php",

	// new classes
	"Action" => "system/lib/classes/Action.class.php",
	"Layout" => "system/lib/classes/Layout.class.php",
	"Decorator" => "system/lib/classes/Decorator.class.php",
	"View" => "system/lib/classes/View.class.php",
	"Controller" => "system/lib/controllers/Controller.class.php",
	"ViewController" => "system/lib/controllers/ViewController.class.php",
	"JsonController" => "system/lib/controllers/JsonController.class.php",

	// interfaces
	"iController" => "system/lib/interfaces/iController.interface.php",
	"iAction" => "system/lib/interfaces/iAction.interface.php",
	"iDecorator" => "system/lib/interfaces/iDecorator.interface.php",
	"iLayout" => "system/lib/interfaces/iLayout.interface.php",
	"iView" => "system/lib/interfaces/iView.interface.php",
	
 	// new mail
 	'MailReader' => 'system/lib/mail/MailReader.class.php',
	'MailPOP3' => 'system/lib/mail/MailPOP3.class.php',
	'MailIMAP' => 'system/lib/mail/MailIMAP.class.php',
	'MailRetrieval' => 'system/lib/mail/MailRetrieval.class.php',
	'MailDecoder' => 'system/lib/mail/MailDecoder.class.php',
	'MailParser' => 'system/lib/mail/MailParser.class.php',

);

AutoLoad::load($__autoload);

// Autoload rules for system 
AutoLoad::addRule('substr($class, -8) == "SqlQuery"', "system/packages/sql_query");
AutoLoad::addRule('substr($class, 0, 2) == "Db"', "system/lib/model", 1);
AutoLoad::addRule('substr($class, -5) == "Model"', "system/lib/model", 3);
AutoLoad::addRule('substr($class, 0, 10) == "Expression"', "system/lib/expression", 1);

define ("AUTOLOAD_PATH", realpath(dirname(__FILE__). "/.."));

/**
 * Autoload - system function
 * 
 * @param string $class
 * @see http://php.net/autoload
 */
function __autoload($class)
{
	$file = AutoLoad::get($class);

	if ($file && file_exists(AUTOLOAD_PATH . DIRECTORY_SEPARATOR . $file)) {
		include(AUTOLOAD_PATH . DIRECTORY_SEPARATOR . $file);
	} 
}

?>
