<?php

$sql = array();

// Connect to database
$model = new DbModel();

// Check
$types_count = $fields_count = 0;
try {
	$types_count = $model->query('SELECT COUNT(*) FROM CONTACTTYPE')->fetchField();
	$fields_count = $model->query('SELECT COUNT(*) FROM CONTACTFIELD')->fetchField();
} catch (Exception $e) {
}
// Check	
if ($types_count >= 2 && $fields_count) {
	throw new Exception('Update done');
}

// Alter for other services
$sql[] = "ALTER TABLE `DISK_USAGE` CHANGE  `DU_SIZE`  `DU_SIZE` BIGINT( 11 ) NULL";
if (!mysql_query('SELECT MMM_REPLY_TO FROM MMMESSAGE WHERE 0 ')) {
    $sql[] = "ALTER TABLE `MMMESSAGE` ADD MMM_REPLY_TO VARCHAR ( 128 ) NULL AFTER MMM_FROM";
}
// Photos for new rights managment system
if (mysql_query('SELECT 1 FROM PIXFOLDER WHERE 0') && !mysql_query('SELECT PF_SORT FROM PIXFOLDER WHERE 0')) {
    $sql[] = "ALTER TABLE `PIXFOLDER` ADD `PF_SORT` INT NOT NULL DEFAULT '0'";
}

// Contact alters and updates
$sql[] = "ALTER TABLE `UGROUP` CHANGE `UG_ID` `UG_ID` INT( 11 ) NOT NULL AUTO_INCREMENT";
$sql[] = "ALTER TABLE `UGROUP` DROP `UG_SETTINGS`";

$sql[] = "ALTER TABLE `WBS_USER` ADD UNIQUE `CONTACT_ID` ( `C_ID` )";

$sql[] = "ALTER TABLE `CFOLDER` DROP `CT_ID`";

$sql[] = "ALTER TABLE `CLIST` CHANGE `CL_ID` `CL_ID` INT( 11 ) NOT NULL AUTO_INCREMENT";
$sql[] = "ALTER TABLE `CLIST` ADD `CL_SQL` TEXT NOT NULL";
$sql[] = "ALTER TABLE `CLIST` ADD `CL_SEARCH` TEXT NOT NULL";
$sql[] = "UPDATE `CLIST` SET `CL_OWNER_U_ID` = (SELECT C_ID FROM WBS_USER WHERE U_ID = CL_OWNER_U_ID)";
$sql[] = "ALTER TABLE `CLIST` CHANGE `CL_OWNER_U_ID` `CL_C_ID` INT( 11 ) NOT NULL";
$sql[] = "ALTER TABLE `CLIST` CHANGE `CL_MODIFYUSERNAME` `CL_MODIFYCID` INT NULL";

$sql[] = "DELETE FROM `CONTACT` WHERE C_ID = 0";
$sql[] = "ALTER TABLE `CONTACT` CHANGE `C_ID` `C_ID` INT( 11 ) NOT NULL AUTO_INCREMENT";
$sql[] = "ALTER TABLE `CONTACT` CHANGE `C_EMAILADDRESS` `C_EMAILADDRESS` VARCHAR( 255 ) NOT NULL";
$sql[] = "ALTER TABLE `CONTACT` ADD `CT_ID` INT(11) NOT NULL DEFAULT '1' AFTER `C_ID`";
$sql[] = "UPDATE `CONTACT` SET CT_ID = 1 WHERE CT_ID = 0";
$sql[] = "ALTER TABLE `CONTACT` CHANGE `C_CREATEUSERNAME` `C_CREATECID` INT( 11 ) NULL";
$sql[] = "ALTER TABLE `CONTACT` ADD `C_CREATEAPP_ID` VARCHAR( 3 )";
$sql[] = "ALTER TABLE `CONTACT` ADD `C_CREATEMETHOD` VARCHAR( 20 )";
$sql[] = "ALTER TABLE `CONTACT` ADD `C_CREATESOURCE` VARCHAR ( 255 )";
$sql[] = "ALTER TABLE `CONTACT` ADD `C_LANGUAGE` varchar(3) DEFAULT NULL";
$sql[] = "ALTER TABLE `CONTACT` CHANGE `C_MODIFYUSERNAME` `C_MODIFYCID` INT ( 11 ) NULL";
$sql[] = "ALTER TABLE `CONTACT` ADD `C_FULLNAME` VARCHAR( 255 ) NOT NULL AFTER `CF_ID`";
$sql[] = "ALTER TABLE `CONTACT` ADD INDEX `CF_ID` ( `CF_ID` )";
$sql[] = "ALTER TABLE `CONTACT` ADD INDEX `C_EMAILADDRESS` ( `C_EMAILADDRESS` )";
$sql[] = "ALTER TABLE `CONTACT` ADD INDEX `C_FULLNAME` ( `C_FULLNAME` )";
$sql[] = <<<SQL
UPDATE CONTACT SET C_FULLNAME = TRIM(CONCAT(
IF(C_FIRSTNAME IS NOT NULL AND C_FIRSTNAME != '', CONCAT(C_FIRSTNAME, " "), ""),
IF(C_MIDDLENAME IS NOT NULL AND C_MIDDLENAME != '', CONCAT(SUBSTR(C_MIDDLENAME, 1, 1), ". "), ""), 	
IF(C_LASTNAME IS NOT NULL AND C_LASTNAME != '', CONCAT(C_LASTNAME, " "), "")
))
SQL;
$sql[] = "UPDATE CONTACT SET C_FULLNAME = C_COMPANY, CT_ID = 2 WHERE C_FULLNAME = '' AND C_COMPANY IS NOT NULL AND C_COMPANY != '' AND CT_ID <= 1";
$sql[] = "UPDATE CONTACT SET C_FIRSTNAME = C_NICKNAME, C_FULLNAME = C_NICKNAME WHERE C_FULLNAME = '' AND C_NICKNAME IS NOT NULL AND C_NICKNAME != ''";
$sql[] = "UPDATE CONTACT SET C_FIRSTNAME = C_EMAILADDRESS, C_FULLNAME = C_EMAILADDRESS WHERE C_FULLNAME = ''";
$sql[] = "UPDATE CONTACT SET C_SUBSCRIBER = 0 WHERE C_SUBSCRIBER = -1";
$sql[] = "INSERT INTO `U_ACCESSRIGHTS` (AR_ID, AR_PATH, AR_OBJECT_ID, AR_VALUE)
SELECT AR_ID, '/ROOT/CM/FUNCTIONS', 'ADMIN', 1 
FROM  `U_ACCESSRIGHTS` 
WHERE AR_PATH = '/ROOT/UG/SCREENS' AND AR_OBJECT_ID = 'UNG' AND AR_VALUE = 1";
$sql[] = "INSERT INTO `UG_ACCESSRIGHTS` (AR_ID, AR_PATH, AR_OBJECT_ID, AR_VALUE)
SELECT AR_ID, '/ROOT/CM/FUNCTIONS', 'ADMIN', 1 
FROM  `UG_ACCESSRIGHTS` 
WHERE AR_PATH = '/ROOT/UG/SCREENS' AND AR_OBJECT_ID = 'UNG' AND AR_VALUE = 1";

// New tables
$sql[] = "CREATE TABLE IF NOT EXISTS CONTACTFIELD (
  `CF_ID` int(11) NOT NULL AUTO_INCREMENT,
  `CF_DBNAME` varchar(50) DEFAULT NULL,
  `CF_TYPE` varchar(50) NOT NULL DEFAULT '',
  `CF_OPTIONS` text,
  `CF_NAME` text,
  `CF_STD` tinyint(1) NOT NULL DEFAULT '0',
  `CF_SECTION` int(11) DEFAULT NULL,
  `CF_SORTING` int(11) NOT NULL,
  PRIMARY KEY (`CF_ID`),
  UNIQUE KEY `CF_DBNAME` (`CF_DBNAME`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";

$sql[] = "CREATE TABLE IF NOT EXISTS CONTACTTYPE (
`CT_ID` int(11) NOT NULL AUTO_INCREMENT,
  `CT_NAME` text NOT NULL,
  `CT_OPTIONS` text NOT NULL,
  `CT_STD` tinyint(1) NOT NULL,
  PRIMARY KEY (`CT_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";

if (mysql_query('SELECT 1 FROM CTYPE WHERE 0')) {
    $sql[] = "TRUNCATE CONTACTFIELD";
    $sql[] = "TRUNCATE CONTACTTYPE";
}

$sql[] = "CREATE TABLE IF NOT EXISTS `EMAIL_CONTACT` (
  `EC_ID` int(11) NOT NULL,
  `EC_EMAIL` varchar(255) NOT NULL,
  PRIMARY KEY (`EC_ID`,`EC_EMAIL`),
  KEY `EC_EMAIL` (`EC_EMAIL`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

if (!mysql_query('SELECT 1 FROM EMAIL_CONTACT WHERE 0')) {
	$sql[] = "TRUNCATE EMAIL_CONTACT";
	$sql[] = "INSERT IGNORE INTO `EMAIL_CONTACT` (`EC_ID`, `EC_EMAIL`) SELECT C_ID, C_EMAILADDRESS FROM CONTACT";
}

$sql[] = "CREATE TABLE IF NOT EXISTS `CONTACTNOTE` (
  `CN_ID` int(11) NOT NULL AUTO_INCREMENT,
  `CN_CID` int(11) NOT NULL,
  `CN_TEXT` text NOT NULL,
  `CN_CREATECID` int(11) NOT NULL,
  `CN_CREATETIME` datetime NOT NULL,
  PRIMARY KEY (`CN_ID`),
  KEY `CN_CID` (`CN_CID`),
  KEY `CN_CREATETIME` (`CN_CREATETIME`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";

// Drop old tables
$sql[] = "DROP TABLE IF EXISTS APPLICATION";
$sql[] = "DROP TABLE IF EXISTS SCREEN";
$sql[] = "DROP TABLE IF EXISTS CFACCESS";
$sql[] = "DROP TABLE IF EXISTS CFGROUPACCESS";
$sql[] = "DROP TABLE IF EXISTS CLIST_GROUP";
$sql[] = "DROP TABLE IF EXISTS CLIST_FOLDER";
$sql[] = "DROP TABLE IF EXISTS AUX_GROUP_ACCESS";
$sql[] = "DROP TABLE IF EXISTS AUX_USER_ACCESS";
$sql[] = "DROP TABLE IF EXISTS USERSCREENACCESS";
$sql[] = "DROP TABLE IF EXISTS SCR_GROUP_ACCESS";

foreach ($sql as $q) {
    @mysql_query($q);
}



class OldContactType 
{
	protected static $store = array();
	
	protected $id;
	protected $data = array();
	protected $langs = array("eng");
	
	public function __construct($id, $languages = array("eng"))
	{
		$this->id = $id;
		$this->langs = $languages;
		if (isset(self::$store[$id])) {
			$this->data = self::$store[$id];
		} else {		
			$result = mysql_query("SELECT * FROM CTYPE WHERE CT_ID = '".$id."'");
			$data = mysql_fetch_assoc($result);
			if (!$data) {
				throw new Exception(_("Requested contact type is not found"));
			}
			$this->data = self::$store[$id] = $this->parse($data['CT_SETTINGS']);
		}
	}

	public function get($group_id = false)
	{
		return $group_id ? $this->data[$group_id] : $this->data;
	}
	
	/**
	 * Returns all fields of the type (without groups of the fields)
	 * 
	 * @return array
	 */
	public function getFields($group_id = false, $add_group_info = true)
	{
		$result = array();
		foreach($this->data as $group_data) {
			if (!$group_id || $group_data["ID"] == $group_id) {
				foreach($group_data["FIELDS"] as $field_id => $field_data) {
					if ($add_group_info) {
						$field_data["GROUPID"] = $group_data["ID"];
						$field_data["GROUPNAME"] = $group_data["LONG_NAME"];
					}
					$result[$field_id] = $field_data;
				}
			}
		}
		return $result;
		
	}
	
	public function getFieldsNames($group_id = false, $add_group_name = false, $images = true)
	{
		$result = array();
		foreach($this->data as $group_data) {
			if (!$group_id || $group_data['ID'] == $group_id) {
				foreach($group_data["FIELDS"] as $field_id => $field_data) {
					if ($images || $field_data['TYPE'] != 'IMAGE') {
						$result[$field_id] = ($add_group_name !== false ? $group_data['LONG_NAME'].$add_group_name : "") . $field_data['LONG_NAME'];
					}
				}
			}
		}		
		return $result;		
	}
	
	protected function parse($xml)
	{
		$result = array();
		// Try read xml
		$dom = new DOMDocument("1.0", "utf-8");
		try {
			$dom->loadXML($xml);
		} catch (Exception $e) {
			throw new RuntimeException(_("Error processing XML data"));
		}
		if (!$dom) {
			throw new RuntimeException(_("Error processing XML data"));
		}
					
		$xpath = new DOMXPath($dom);
		$groups = $xpath->query("/TYPE/FIELDGROUP");
		
		foreach ($groups as $group ) {
			$groupDesc = $this->getElementDescription($xpath, $group); 	
			$groupFields = array();
			
			$fields = $xpath->query("FIELD", $group );
			foreach ($fields as $field) {
				
				$fieldDesc = $this->getElementDescription($xpath, $field);
				if (!isset($fieldDesc ["REQUIRED"])) {
					$fieldDesc["REQUIRED"] = false;
				}
				if (!isset($fieldDesc ["REQUIRED_GROUP"])) {
					$fieldDesc["REQUIRED_GROUP"] = null;
				}
				$groupFields[$fieldDesc["ID"]] = $fieldDesc;
			}
			
			$groupDesc["FIELDS"] = $groupFields;
			
			$result[$groupDesc['ID']] = $groupDesc;
		}
		return $result;
	}
	
	
	protected function getElementDescription(&$xpath, &$elem)
	{
		$elemDesc = $this->getAttributes($elem);
		// Decode menu items
		if (isset($elemDesc['MENU']) && $elemDesc['MENU']) {
			$elemDesc['MENU'] = explode("^&^", base64_decode($elemDesc['MENU']));
		}
		// Check if field name elements exists
		$longNameElement = $xpath->query("LONG_NAME", $elem);
		if (!$longNameElement->length) {
			$name = $elemDesc["LONG_NAME"];
			// Load name from localization strings
			$elemDesc["LONG_NAME"] =  array();
			foreach ($this->langs as $lang_id) {
				$elemDesc['LONG_NAME'][$lang_id == 'gem' ? 'deu' : $lang_id] = $this->translate($name, $lang_id);
			}
		} else {
			// Load name from field description
			$elemDesc["LONG_NAME"] = array();
			foreach ($this->langs as $lang_id) {
				$elemDesc['LONG_NAME'][$lang_id] = $this->getElementName($xpath, $elem, "LONG_NAME", $lang_id);
			}
		}	
		return $elemDesc;	
	}
	
	protected function translate($name, $lang_id)
	{
		$dict = array(
			'app_longname_lastname' => array('eng' => 'Last Name', 'rus' => 'Фамилия', 'gem' => 'Name'),
			'app_longname_midname' => array('eng' => 'Middle Name', 'rus' => 'Отчество', 'gem' => 'Zweiter Vorname'),
			'app_longname_nickname' => array('eng' => 'Nickname', 'rus' => 'Псевдоним', 'gem' => 'Nickname'),
			'app_longname_homeph' => array('eng' => 'Home', 'rus' => 'Домашний', 'gem' => 'Privat'),
			'app_longname_workph' => array('eng' => 'Work', 'rus' => 'Рабочий', 'gem' => 'Geschäft'),
			'app_longname_mobileph' => array('eng' => 'Modile', 'rus' => 'Сотовый', 'gem' => 'Mobil'),
			'app_longname_fax' => array('eng' => 'Fax', 'rus' => 'Факс', 'gem' => 'Fax'),
			'app_longname_pager' => array('eng' => 'Pager', 'rus' => 'Пейджер', 'gem' => 'Pager'),
			'app_longname_homecity' => array('eng' => 'City', 'rus' => 'Город', 'gem' => 'Ort'),
			'app_longname_perspage' => array('eng' => 'Personal Web Page', 'rus' => 'Личная страница', 'gem' => 'Homepage'),
			'app_longname_birthday' => array('eng' => 'Birthday', 'rus' => 'День рождения', 'gem' => 'Geburtstag'),
			'app_longname_company' => array('eng' => 'Company', 'rus' => 'Компания', 'gem' => 'Geschäft'),
			'app_longname_department' => array('eng' => 'Department', 'rus' => 'Отдел', 'gem' => 'Abteilung'),
			'app_longname_jobtitle' => array('eng' => 'Job Title', 'rus' => 'Должность', 'gem' => 'Anstellung'),
			'app_longname_workstreet' => array('eng' => 'Street Address',  'rus' => 'Улица', 'gem' => 'Strasse'),
			'app_longname_officelocation' => array('eng' => 'Office Location', 'rus' => 'Офис', 'gem' => 'Büro'),
			'app_longname_buspage' => array('eng' => 'Business Web Page', 'rus' => 'Рабочая страница', 'gem' => 'Geschäft Homepage'),
			'app_longname_firstname' => array('eng' => 'First Name', 'rus' => 'Имя', 'gem' => 'Vorname'),
			'app_longname_workfax' => array('eng' => 'Business Fax', 'rus' => 'Рабочий Факс', 'gem' => 'Geschäft Fax'),
			'app_longname_homestreet' => array('eng' => 'Street Address', 'rus' => 'Улица', 'gem' => 'Strasse'),
			'app_longname_homestate' => array('eng' => 'State', 'rus' => 'Регион', 'gem' => 'Bezirk'),
			'app_longname_homepostcode' => array('eng' => 'Postal Code', 'rus' => 'Индекс', 'gem' => 'PLZ'),
			'app_longname_homecountry' => array('eng' => 'Country', 'rus' => 'Страна', 'gem' => 'Land'),
			'app_longname_workcity' => array('eng' => 'City', 'rus' => 'Город', 'gem' => 'Ort'),
			'app_longname_workstate' => array('eng' => 'State', 'rus' => 'Регион', 'gem' => 'Bezirk'),
			'app_longname_workpostcode' => array('eng' => 'Postal Code', 'rus' => 'Индекс', 'gem' => 'PLZ'),
			'app_longname_workcountry' => array('eng' => 'Country', 'rus' => 'Страна', 'gem' => 'Land'),
			'app_longname_email' => array('eng' => 'Email', 'rus' => 'Email', 'gem' => 'E-Mail'),
			'app_longname_notes' => array('eng' => 'Notes', 'rus' => 'Заметки', 'gem' => 'Notizen'),
			'app_contactgroup_title' => array('eng' => 'Contact', 'rus' => 'Контакт', 'gem' => 'Kontakt'),
			'app_phonesgroup_title' => array('eng' => 'Phones', 'rus' => 'Телефоны', 'gem' => 'Telefon'),
			'app_group_home' => array('eng' => 'Home', 'rus' => 'Домашний', 'gem' => 'Privat'),
			'app_group_business' => array('eng' => 'Business', 'rus' => 'Бизнес', 'gem' => 'Geschäft'),
			'app_group_notes' => array('eng' => 'Notes', 'rus' => 'Заметки', 'gem' => 'Notizen'),
		    'app_group_work' => array('eng' => 'Work', 'rus' => 'Рабочий', 'gem' => 'Arbeit'),
			'app_group_photo' => array('eng' => 'Photo', 'rus' => 'Фото', 'gem' => 'Foto'),	
		);
		return isset($dict[$name][$lang_id]) ? $dict[$name][$lang_id] : $name; 
	}

	protected function getAttributes($node)
	{
		$result = array();
		foreach ($node->attributes as $name => $value) {
			$result[$name] = $value->nodeValue;
		}
		return $result;
	}
	
	protected function getElementName( &$xpath, &$element, $nameNodeName, $language)
	{
		// Find name element
		$nameNodeElement = $xpath->query($nameNodeName, $element);
		if ( !$nameNodeElement->length ) {
			return "";
		}
		$nameNodeElement = $nameNodeElement->item(0);
		// Find name language element
		$languageElement = $xpath->query($language, $nameNodeElement );

		// Return language element value, if language element exists
		$langElementExists = $languageElement->length;
		if ( $langElementExists ) {
			$languageElement = $languageElement->item(0);
			$fieldNameExists = strlen($languageElement->attributes->getNamedItem("VALUE")->nodeValue);
		}

		if ( $langElementExists && $fieldNameExists ) {
			return base64_decode($languageElement->attributes->getNamedItem("VALUE")->nodeValue);
		} else {
			// Find English name element
			$languageElement = $xpath->query("eng", $nameNodeElement );

			if ($languageElement->length) {
				$languageElement = $languageElement->item(0);
				return base64_decode($languageElement->attributes->getNamedItem("VALUE")->nodeValue);				
			} else {
				return "";				
			}

		}
	}
}

$langs = Wbs::getDbkeyObj()->loadDBKeyLanguages();
$languages = array();
foreach ($langs as $lang) {
	$languages[] = $lang['ID'];
}

// One type of the Contact Only
$old_contact_type = new OldContactType("CON", $languages);
$data = $old_contact_type->get();


// Save contact data in new tables
$contact_type_model = new ContactTypeModel();
$sections = array();
$i = 0;
 
$person_fields = array();
$company_fields = array();
$is_company = false;

$std_fields = array(
	'C_FIRSTNAME' => 2, 
	'C_LASTNAME' => 2, 
	'C_MIDDLENAME' => 2, 
	'C_COMPANY' => 1, 
	'C_EMAILADDRESS' => 1,
    'C_X_PHOTO' => 1,
    'C_TITLE' => 2
);

$sort_fields = array(
	'C_FIRSTNAME' => 1, 
	'C_MIDDLENAME' => 2,
	'C_LASTNAME' => 3,
	'C_TITLE' => 4, 
	'C_COMPANY' => 5, 
	'C_EMAILADDRESS' => 6,
    'C_X_PHOTO' => 7,
);

$is_photo = false;
$main_section = 0;
foreach ($data as $group) {
	$section_id = $contact_type_model->addField(null, 'SECTION', $group['LONG_NAME'], null, null, ++$i, 0);
	if ($i == 1) {
	    $main_section = $section_id;
	}
	$j = 0; 
	foreach ($group['FIELDS'] as $field) {
	    $type = $field['TYPE'];
		switch ($field['TYPE']) {
			case 'MEMO':
				$type = "TEXT";
				$options = "";
				break;
			case 'EMAIL': 
			    if ($field['DBFIELD'] != 'C_EMAILADDRESS') {
			        $type = "VARCHAR";
			    }
			    $options = "";
			    break;
			case 'TEXT':
				$type = "VARCHAR";
				$options = $field['MAXLEN'];
				break;
			case 'NUMERIC':
				$options = $field['DECPLACES'];
				break;
			case 'MENU':
				$options = implode("\n", $field['MENU']);
				break;
			default:
				$options = "";
		}
	    if ($field['DBFIELD'] == 'C_MOBILEPHONE') {
	        $type = 'MOBILE';
	    }
		
		$std = isset($std_fields[$field['DBFIELD']]) ? $std_fields[$field['DBFIELD']] : 0;
		if ($i == 1) {
		    if ($std) {
		        $sort = $sort_fields[$field['DBFIELD']];
		    } else {
		        $sort = ++$j + 7;
		    }
		} else {
		    $sort = ++$j;
		}
		if ($i == 1 && $type == 'IMAGE') {
		    $is_photo = true;
		}
		if (!$is_photo && $field['DBFIELD'] == 'C_X_PHOTO') {
		    $is_photo = true;
		    $field_id = $contact_type_model->addField($field['DBFIELD'], $type, $field['LONG_NAME'], $options, $main_section, 6, $std);
		} else {
		    $field_id = $contact_type_model->addField($field['DBFIELD'], $type, $field['LONG_NAME'], $options, $section_id, $sort, $std);    
		}
		
		$f = array($field_id);
		if (isset($field['MANDATORY']) &&  $field['MANDATORY']) {
			$f[] = 1;
		}
		$person_fields[] = $f;
		if ($field['DBFIELD'] != 'C_FIRSTNAME' && $field['DBFIELD'] != 'C_LASTNAME' && $field['DBFIELD'] != 'C_MIDDLENAME') {
    		$company_fields[] = $f;
		}
	}
}
$main_section = ContactType::getMainSection();
// Check photo
if (!$is_photo) {
    $sql = "ALTER TABLE CONTACT ADD C_PHOTO TEXT NULL";
    @mysql_query($sql);
    $contact_type_model->addField('C_PHOTO', 'IMAGE', 'Photo', '', $main_section, 6, 2);
}
$company = ContactType::getFieldByDbName('C_COMPANY');
// Insert field Company
if (!$company || $company['section'] != $main_section) {
    $email_field = ContactType::getFieldByDbName('C_EMAILADDRESS');
    $sql = "UPDATE CONTACTFIELD 
    		SET CF_SORTING = CF_SORTING + 1
    		WHERE CF_SECTION = i:section AND CF_SORTING >= i:sorting AND CF_STD > 0";
    $contact_type_model->prepare($sql)->exec(array('section' => $main_section, 'sorting' => $email_field['sorting']));
    if (!$company) {    
    	$sql = "ALTER TABLE CONTACT ADD C_COMPANY VARCHAR(255) NULL";
    	@mysql_query($sql);
        $company = $contact_type_model->addField('C_COMPANY', 'VARCHAR', 'Company', "", $main_section, $email_field['sorting'], 1);
    } else {
        $contact_type_model->saveSorting($company['id'], $main_section, $email_field['sorting']);
        $company = $company['id'];
    }
}

$first = ContactType::getFieldId('C_FIRSTNAME');
$middle = ContactType::getFieldId('C_MIDDLENAME');
$last = ContactType::getFieldId('C_LASTNAME');
$required = array($first, $last);
$formats = array("!".$first."! !".$middle."!. !".$last."!");
$contact_type_model->addType('Person', $required, $formats, $person_fields, true);
$required = array($company);
$formats = array("!".$company."!");
$contact_type_model->addType('Company', $required, $formats, $company_fields, true);

?>