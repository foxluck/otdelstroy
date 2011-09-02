<?php

class DiscountsSettings extends ActionsController
{
    function postAction($action)
    {
        switch($action)
        {
            case 'set_cfg':
                $this->main();
                break;
        };
    }
    
    function set_dsc_state()
    {
        $dsc_type = preg_replace('/[^A-Za-z]/','',$this->getData('dsc_type'));
        $dsc_state = preg_replace('/[^A-Za-z]/','',$this->getData('dsc_state'));
        _setSettingOptionValue('CONF_DSC_'.strtoupper($dsc_type).'_ENABLED', strtoupper($dsc_state));
        die();
    }
    
    function set_cfg()
    {
        $dsc_calc = $this->getData('cfg_dsc_calc');
        _setSettingOptionValue('CONF_DSC_CALC', strtolower($dsc_calc));
    }
    
    function del_discount()
    {
        $dsc_id = $this->getData('dsc_id');
		if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
		{
			RedirectSQ('did='.$this->getData('did').'&safemode=yes' , 'index.php');
		};
		
		dscDeleteOrderPriceDiscount($dsc_id);
		RedirectSQ('did='.$this->getData('did'), 'index.php');
    }
    
    function save_order_price_discounts()
    {
		if (CONF_BACKEND_SAFEMODE)  //this action is forbidden when SAFE MODE is ON
		{
			RedirectSQ('safemode=yes' , 'index.php');
		};
		
		$dsc_type = $this->getData('discount_type');
		$error = false;
		$data = scanArrayKeysForID($_POST, array( 'percent_discount', 'price_range' ));
		foreach($data as $discount_id => $val)
		{
			if(!dscUpdateDiscount($discount_id, $val['price_range'], (float)$val['percent_discount']))
			{
			    $error = true;
			};
		};
	
		if($this->getData('new_price_range') != '' )
		{
			if(!_dscAddDiscount((float)$this->getData('new_price_range'), (float)$this->getData('new_percent_discount'), $dsc_type))
			{
				$error = true;
			};
		}
	
		$error ? RedirectSQ('error=yes') : RedirectSQ();
    }
    
	function main()
	{
		$Register = &Register::getInstance();
		$smarty = &$Register->get(VAR_SMARTY);

    	$discounts = dscGetAllOrderPriceDiscounts();
    	$so_discounts = dscGetAllOrderSumDiscounts();
    	$smarty->assign('discounts', $discounts);
    	$smarty->assign('so_discounts', $so_discounts);
    	
    	$dsc_states = array(
    	    'by_coupons' => defined('CONF_DSC_COUPONS_ENABLED')?constant('CONF_DSC_COUPONS_ENABLED'):''
    	   ,'by_usergroup' => defined('CONF_DSC_USERGROUP_ENABLED')?constant('CONF_DSC_USERGROUP_ENABLED'):''
    	   ,'by_amount' => defined('CONF_DSC_AMOUNT_ENABLED')?constant('CONF_DSC_AMOUNT_ENABLED'):''
    	   ,'by_orders' => defined('CONF_DSC_ORDERS_ENABLED')?constant('CONF_DSC_ORDERS_ENABLED'):''
    	);
    	foreach($dsc_states as &$value){
    			if($value === null){
    				$value = 'N';
    			}
    	}
    	
    	$smarty->assign('dsc_states', $dsc_states);
    	$smarty->assign('mng_coupons_url', set_query('ukey=discount_coupons'));
    	$smarty->assign('mng_usergroups_url', set_query('ukey=admin_custgroups'));
    	$smarty->assign('cfg_dsc_calc', defined('CONF_DSC_CALC')&&constant('CONF_DSC_CALC'));
    	
    	$smarty->assign('sub_template', 'backend/discounts.html');
	}
};

ActionsController::exec('DiscountsSettings');

/*
	$Register = &Register::getInstance();
	$smarty = &$Register->get(VAR_SMARTY);
	$GetVars = &$Register->get(VAR_GET);
	$PostVars = &$Register->get(VAR_POST);
	
	if ( isset($GetVars['delete']) ){
		
		if (CONF_BACKEND_SAFEMODE){ //this action is forbidden when SAFE MODE is ON
			RedirectSQ( 'delete=&safemode=yes' );
		}
		dscDeleteOrderPriceDiscount( $GetVars['delete'] );
		RedirectSQ( 'delete=' );
	}
	
	if ( isset($GetVars['error']) )$smarty->assign( 'error', 1 );
	
	if ( isset($PostVars['discount_type_save']) ){ //update discount type?
		if (CONF_BACKEND_SAFEMODE){ //this action is forbidden when SAFE MODE is ON
			RedirectSQ('safemode=yes' );
		}
		$PostVars['save'] = 1;
	}
	
	$control = settingCallHtmlFunction( 'CONF_DISCOUNT_TYPE' );
	
	if ( isset($PostVars['discount_type_save']) )
		RedirectSQ();
	
	$smarty->assign( 'control', $control );
	
	if ( isset($PostVars['save_order_price_discounts']) ){
		if (CONF_BACKEND_SAFEMODE){ //this action is forbidden when SAFE MODE is ON
			RedirectSQ( 'safemode=yes' );
		}
		$error = false;
		$data = scanArrayKeysForID($_POST, array( 'percent_discount', 'price_range' ) );
		foreach( $data as $discount_id => $val ){
			
			if ( !dscUpdateOrderPriceDiscount( $discount_id, $val['price_range'], (float)$val['percent_discount'] ) )$error = true;
		}
	
		if ( trim($PostVars['new_price_range']) != '' ){
			if ( !dscAddOrderPriceDiscount( (float)$PostVars['new_price_range'], 
				(float)$PostVars['new_percent_discount']) )
				$error = true;
		}
	
		if ( $error )RedirectSQ('error=yes');
		else RedirectSQ();
	}
	
	
	$discounts = dscGetAllOrderPriceDiscounts();
	$smarty->assign('discounts', $discounts );
	
	$dsc_stats = array(
	    'by_coupons' => _getSettingOptionValue('CONF_DSC_COUPONS_ENABLED')
	   ,'by_usergroup' => _getSettingOptionValue('CONF_DSC_USERGROUP_ENABLED')
	   ,'by_amount' => _getSettingOptionValue('CONF_DSC_AMOUNT_ENABLED')
	   ,'by_orders' => _getSettingOptionValue('CONF_DSC_ORDERS_ENABLED')
	);
	
	$smarty->assign('dsc_stats', $dsc_stats);
	$smarty->assign('mng_coupons_url', set_query('ukey=discount_coupons'));
	$smarty->assign('mng_usergroups_url', set_query('ukey=admin_custgroups'));
	
	$smarty->assign('sub_template', $this->getTemplatePath('backend/discounts.html'));
*/
?>