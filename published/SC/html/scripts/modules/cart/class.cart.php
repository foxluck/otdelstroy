<?php
class Cart extends Module {
	
	function initInterfaces(){
		
		$this->Interfaces = array(
			'cart_info' => array(
				'key' => 'cart_info',
				'name' => 'Shopping cart info',
				'method' => 'methodCartInfo',
				),
			'cart' => array(
				'key' => 'cart',
				'name' => 'Shopping cart screen',
				'method' => 'methodCart',
				),
		);
	}
	
	function methodCartInfo(){
		
		global $smarty;
		//include(DIR_INCLUDES.'/shopping_cart_info.php');
		include(DIR_MODULES.'/'.$this->ModuleDir.'/scripts/shopping_cart_info.php');
	}
	
	function methodCart(){
		
		global $smarty;
		include(DIR_MODULES.'/'.$this->ModuleDir.'/scripts/shopping_cart.php');
	}
}
?>