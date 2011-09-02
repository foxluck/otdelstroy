<?php	
class UGAjaxUsersItemsAction extends UGAjaxAction
{

	public function __construct()
	{
		parent::__construct();
		$this->save();
	}
	
	public function save()
	{
		$n = Env::Post('n', Env::TYPE_INT, 30);
		if ($n >= 30 && $n <= 70) {
			User::setSetting('ITEMSONPAGE', $n, 'UG');
		} 	
	}
}
?>