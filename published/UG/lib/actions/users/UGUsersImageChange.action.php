<?php

	class UGUsersImageChangeAction extends UGViewAction
	{
		
		public function __construct()
		{
			parent::__construct();
		}
		
    	public function prepareData()
        {
            $contactId = Env::Get('C_ID', Env::TYPE_BASE64, '1');
            $type = Env::Get('type', Env::TYPE_STRING, 'edit');
            
            $this->smarty->assign("C_ID", base64_encode($contactId));
            $this->smarty->assign("CF_ID", Env::Get('CF_ID', Env::TYPE_STRING, '1'));
            $this->smarty->assign("type", $type);

            if ( $type == 'edit' ) {
                $info = Contact::getInfo($contactId);
                $this->smarty->assign("IMG", $info['C_IMAGE']);
                
            }
        }
						
	}
	
?>