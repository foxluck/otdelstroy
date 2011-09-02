<?php 

class UGUsersSettingsAction extends UGUsersAction 
{
    public function __construct($ajax = false)
    {
        parent::__construct($ajax);

     	if (!$this->user_id) {
     		if (Env::Get('go')) {
     			User::checkLimits(1);
     		} else {
     			try {
     				User::checkLimits(1);
     			} catch (Exception $e) {
     			echo <<<HTML
<script>location.href = "{$_SERVER['REQUEST_URI']}&go=1";</script>
HTML;
					exit;     			
    	 		}
     		}
     	}       
    }
    
    public function formatArray($a) 
    {
    	$result = array();
    	foreach ($a as $key => $value) {
    		$result[] = array(
    			'key' => $key,
    			'value' => $value
    		);
    	}	
    	return $result;
    }
    
    
    public function prepareData()
    {
        if ($this->contact_info['U_STATUS'] == 3) {
            $time = User::getSetting('INVITETIME', 'UG', $this->contact_info['U_ID']);
            if ($time) {
                $this->smarty->assign('invite_datetime', WbsDateTime::getTime($time)." ". WbsDateTime::ago($time));
            }
        }
        $languages = Wbs::getDbkeyObj()->loadDBKeyLanguages();
        $langs = array();
        foreach ($languages as $lang_info) {
        	$langs[$lang_info['ID']] = $lang_info['NAME'];
        }
        $rights = new Rights($this->user_id);
        
        //print_r($screens_obj);
        $screens = array(
        	'BLANK' => '<'._("blank").'>',
        	'LAST' => '<'._("last opened").'>'
        ) + $rights->getApps(true);
	        
		$user_settings_model = new UserSettingsModel();
		$settings = $user_settings_model->getAll($this->user_id, ''); 	        
        
        $timezones = TimeZones::$zones;
        foreach ($timezones as &$timezone) {
        	$timezone = $timezone['shortname']." ".$timezone['longname'];
        }
        $system_tz = Wbs::getSystemObj()->getTimeZone() ? Wbs::getSystemObj()->getTimeZone()->id : false;
        $time_zone_id = isset($settings['TIME_ZONE_ID']) ? $settings['TIME_ZONE_ID'] : $system_tz;
        $time_zone_dst = isset($settings['TIME_ZONE_DST']) ? $settings['TIME_ZONE_DST'] : 1;        
		$fields = array(
		        array(
		            'language',
		            _('Language'),
		            'MENU',
		            isset($settings['language']) ? $settings['language'] : 'eng',
		            0,
		            $this->formatArray($langs)
			    ),
		        array(
		            'TIME_ZONE_ID',
		            _('Time zone'),
		            'MENU',
                     $time_zone_id,
		            0,
		            $this->formatArray($timezones)
			    ),			    
		        array(
		            'TIME_ZONE_DST',
		            _('Daylight saving time'),
		            'CHECKBOX',
		            $time_zone_dst,
		            0
			    ),			    			
			    array(
			    	'U_LOCAL_TIME',
			    	_('User local time'),
			    	'SPAN',
			    	WbsDateTime::getTime(time(), new CTimeZone($time_zone_id, $time_zone_dst), "<b>H:i</b>"),
			    	0
			    ),    	  
                array(
                    'START_PAGE',
                    _('Start Page'),
                    'MENU',
                    isset($settings['START_PAGE']) ?  $settings['START_PAGE'] : "BLANK",
                    0,
                    $this->formatArray($screens)
                )
		);
		$quota_fields = array();
		if (Wbs::getDbkeyObj()->existsModule('sms')) {
			$sms_model = new SmsModel();
			$quota_fields[] =  array(
	            'sms',
	            _('SMS messages'),
	            'NUMERIC',
	            (int)$sms_model->getBalance($this->user_id),
	            0,
	            "",
	            _('Leave blank for unlimited quota')		            
		    );			
			
		}
		
		if ($this->contact_info['U_STATUS'] == 3) {
    		$key = User::getSetting("INVITEKEY", 'UG', $this->user_id);
    		if (!$key) {
    			$key = substr(md5(uniqid("INVITE")), -6);
    			User::setSetting("INVITEKEY", $key, "UG", $this->user_id);
    		}
    		$key = substr(md5($this->contact_id), 0, 6).$this->contact_id.$key;
    		
    		$this->smarty->assign('invite_url', Url::get("/invite.php?key=".$key.(Wbs::isHosted() ? "" : "&DB_KEY=".base64_encode(Wbs::getDbkeyObj()->getDbkey())), true));
		}
		$this->smarty->assign('sms', Wbs::getDbkeyObj()->existsModule('sms'));
        $this->smarty->assign('quota_js', json_encode($quota_fields));				
        $this->smarty->assign('js', json_encode($fields));
        $this->smarty->assign('user_status', $this->contact_info['U_STATUS']);
        $this->smarty->assign('login', $this->contact_info['U_STATUS'] == Groups::USER_INVITED ? '&lt;'._("not created yet").'&gt;' : $this->user_id);
        parent::prepareData();     
        if ($this->ajax) {
			$rights = new Rights($this->user_id);
			$access = $rights->getApps();
			$this->smarty->assign("access", $access);            
        }   
    }
}


?>