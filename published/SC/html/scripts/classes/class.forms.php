<?php
class Forms extends VirtualModule2
{
	const GENERIC_FORM = 'generic';
	const MODULE_FORM = 'module';
	public $template_path;

	static function listModules($list_installed = true)
	{
		parent::listModules($list_installed);
		if(isset(self::$classes['printforms'])) {
			unset(self::$classes['printforms']);
		}
		return self::$classes;
	}

	static function listConnectedModules($properties = array(), $strict = false)
	{
		$modules = self::listModules(false);
		self::filterModules($modules, $properties, $strict);
		return $modules;
	}

	static function filterModules(&$modules, $properties = array(), $strict = false)
	{
		if(is_array($properties) && count($properties)) {
			foreach($modules as $id=>$module) {
				foreach($properties as $field=>$value) {
					if(!is_array($value)) {
						$value = array($value);
					}
					$field = "_{$field}";
					if(!isset($module[$field])) {
						if($strict) {
							unset($modules[$id]);
							continue 2;
						}
					}elseif(!in_array($module[$field], $value)) {
						unset($modules[$id]);
						continue 2;
					}
				}
			}
		}
		return $modules;

	}

	function display($strict = true)
	{
		if($this->template_path && file_exists($this->template_path)) {
			$smarty = &Core::getSmarty();
			/*@var $smarty Smarty */
			$smarty->template_dir = dirname($this->template_path);
			foreach($this->SettingsFields as $setting=>$params) {
				$smarty->assign($setting, $params['settings_value']);
			}
			$smarty->assign('strict', $strict);
			$smarty->display($this->template_path);
		}else {
			print translate('print_form_not_found');
		}
		exit;
	}

	function _initSettingFields()
	{
		;
	}
	function _initVars()
	{
		;
	}

	function __construct($module_id = 0)
	{
		parent::__construct($module_id);
		$class = get_class($this);
		$this->template_path = DIR_FORMS.'/'.strtolower($class).'.html';
		$this->title = $class;
	}

	function getProperties()
	{
		$properties = array();
		$properties['class']	 = $class = get_class($this);
		$properties['version']	 = $this->version;
		if($this->template_path && file_exists($this->template_path)) {
			$properties['template']	 = basename($this->template_path);
			$properties['filesize']	 = sprintf('%0.2f Kb', filesize($this->template_path)/1024);
			$properties['path']		 = substr(str_replace(realpath(WBS_DIR),'',realpath($this->template_path)),1);
		}
		$properties = array_merge(self::getModuleProperties(self::$path.'/class.'.strtolower($class).'.php'), $properties);
		return $properties;
	}

	static function getListConnectedModules($sp_module_id = 0)
	{
		$modules_id = array();
		$dbq = 'SELECT * FROM ?#SOME_TABLE WHERE module_id=?';
		$connected_modules_info = db_phquery_fetch(DBRFETCH_ASSOC_ALL,$dbq,$sp_module_id);
	}

	function verifyOrderData($order)
	{
		//check order time
		$order_time = isset($_GET["order_time"])?base64_decode($_GET["order_time"]):'';//yyyy-mm-dd hh:mm:ss
		$res = null;
		if(preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/', $order_time, $res)) {
			$order_time = $res[0];
		}else{
			$order_time = 'none';
		}

		//check customer e-mail
		$customer_email =isset($_GET["customer_email"])?base64_decode($_GET["customer_email"]):'';
		$res = null;
		if(preg_match('/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/', $customer_email, $res)) {
			$customer_email = strtolower($res[0]);
		}else {
			$customer_email ='none';
		}
		//check customer
		if(!isset($_SESSION['log']) || !$_SESSION['log']) {
			$mode = defined('CONF_STRICT_ACCESS')?constant('CONF_STRICT_ACCESS'):'lastname';
			if($mode == 'auth') {//auth only|status only|full
				RedirectSQ('?ukey=auth');
			}else {
				$storage = Cache::getInstance('order_status', Cache::SESSION);
				if($storage->get($order["orderID"])!=$order["customerID"]) {
					return false;
				}
			}
		}else {
			$customerID = regGetIdByLogin($_SESSION["log"]);
			if($order["customerID"] != $customerID) {
				return false;
			}
		}

		return (($order_time===$order['order_time_mysql']) && ($customer_email===strtolower($order['customer_email'])))?true:false;
			
	}
}
Forms::$path = DIR_MODULES.DIRECTORY_SEPARATOR.'/printforms';
Forms::$type = FORM_MODULE;
?>