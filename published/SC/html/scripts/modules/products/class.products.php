<?php
#удалить потом
if(!class_exists('products',false)){
class Products extends Module {
	
	function initInterfaces(){
		
		$this->Interfaces = array(
			'show product' => array(
				'name' => 'Show product',
				'method' => 'methodShowProduct',
				),
			'discuss product' => array(
				'name' => 'Discuss product',
				'method' => 'methodDiscussProduct',
				),
			'b_product_settings' => array(
				'name' => 'Product settings',
				),
			'b_product_option_configuration' => array(
				'name' => 'Product option settings',
				),
			'b_related_products_setup' => array(
				'name' => 'Related products administration',
				),
			'comparison_products' => array(
				'name' => 'Products comparison',
				),
		);
	}
	
	function methodShowProduct(){
		
		global $smarty;
		include(DIR_MODULES.'/'.$this->ModuleDir.'/scripts/product_detailed.php');
	}
	
	function methodDiscussProduct(){
		
		global $smarty;
		include(DIR_ROOT.'/includes/product_discussion.php');
	}
}
}
?>