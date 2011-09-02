<?php

	class UGUsersImageAddAction extends UGViewAction
	{
		
		public function __construct()
		{
			parent::__construct();
		}
		
    	public function prepareData()
        {
            $this->smarty->assign("C_ID", base64_encode(Env::Get('C_ID', Env::TYPE_STRING, '1')));
            $this->smarty->assign("CF_ID", Env::Get('CF_ID', Env::TYPE_STRING, '1'));
        }
						
	}
	
?>