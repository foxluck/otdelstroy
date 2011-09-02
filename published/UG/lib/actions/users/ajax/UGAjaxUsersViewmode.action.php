<?php
class UGAjaxUsersViewmodeAction extends UGAjaxAction
{
	
	protected $mode;
	
	public static $view_modes = array(
		'columns' => 0,
		'detail' => 1,
		'tile' => 2 
	);
	
	public function __construct()
	{
		parent::__construct();
		$this->save();
	}
	
	public function save()
	{
		$elem = Env::Post('elem');
		list($block, $element_id) = explode(":", $elem);
		if (substr($block, -6) == "search") {
			$block = "search";
		}
		$this->mode = Env::Post('mode');	
		$this->mode = isset(self::$view_modes[$this->mode]) ? self::$view_modes[$this->mode] : 0;
		User::setSetting('VIEWMODE' . $block.$element_id, $this->mode); 	
	}
}
?>