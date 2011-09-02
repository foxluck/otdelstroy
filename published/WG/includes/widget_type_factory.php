<?php

class WidgetTypeFactory 
{		
	// object instance
	private static $instance = null;
	private static $all_types = array();
	
	private function __construct() {}
	
	private function __clone() {}
	
	public static function getInstance() 
	{
	   if (self::$instance === null) {
	     self::$instance = new self;
	   }
	   return self::$instance;
	} 		
	
	function registerWidgetType ($type, &$widget) 
	{
		self::$all_types[$type] = $widget;
	}
		
	function getWidgetType ($id) 
	{
		if (!isset(self::$all_types[$id])) {		
			$widgetFilename = PATH_WG_WIDGETS . $id . "/widget.php";
			include_once ($widgetFilename);
		}
		
		if (isset(self::$all_types[$id])) {
			return self::$all_types[$id];
		} else {
			return PEAR::raiseError ("Cannot find type: $type");
		}
	}
		
}
?>