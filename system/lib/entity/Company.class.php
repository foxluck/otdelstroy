<?php
/** 
 * Class for the works with company variables
 * 
 * @copyright WebAsyst © 2008-2009
 * @author WebAsyst Team
 * @version SVN: $Id: Company.class.php 7703 2009-12-17 11:34:58Z alexmuz $
 */
class Company 
{
	protected static $store = array();
	protected static $fields = array();
	
	protected function __construct() {}
	
	public static function get($name = false)
	{
		if (!self::$store) {
			$company_model = new DbModel();
			self::$store = $company_model->query("SELECT * FROM COMPANY")->fetchAssoc();
		} 
		if (!$name) {
			return self::$store;
		}
		return isset(self::$store[$name]) ? self::$store[$name] : false; 
	}
	
	public static function getName()
	{
		return self::get('COM_NAME');
	}
	
	public static function getFields()
	{
		if (!self::$fields) {
			self::$fields = array(
				'COMPANY_NAME' => array('name' => _s('Company name'), 'dbname' => 'COM_NAME'),
				'COMPANY_STREETADDRESS' => array('name' => _s('Street address'), 'dbname' => 'COM_ADDRESSSTREET'),
				'COMPANY_CITY' => array('name' => _s('City'), 'dbname' => 'COM_ADDRESSCITY'),
				'COMPANY_STATE' => array('name' => _s('State'), 'dbname' => 'COM_ADDRESSSTATE'),
				'COMPANY_ZIP' => array('name' => _s('Zip'), 'dbname' => 'COM_ADDRESSZIP'),
				'COMPANY_COUNTRY' => array('name' => _s('Country'), 'dbname' => 'COM_ADDRESSCOUNTRY'),
				'COMPANY_CONTACTNAME' => array('name' => _s('Contact name'), 'dbname' => 'COM_CONTACTPERSON'),
				'COMPANY_CONTACTEMAIL' => array('name' => _s('Contact email'), 'dbname' => 'COM_EMAIL'),
				'COMPANY_CONTACTPHONE' => array('name' => _s('Contact phone'), 'dbname' => 'COM_PHONE'),
				'COMPANY_CONTACTFAX' => array('name' => _s('Contact fax'), 'dbname' => 'COM_FAX')
			);		
		}
		return self::$fields;
	}
	
}
?>