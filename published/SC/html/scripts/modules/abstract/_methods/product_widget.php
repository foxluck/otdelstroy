<?php
	class ProductWidgetController extends ActionsController {
		
		function main(){
			
			$Register = &Register::getInstance();
			$smarty = &$Register->get(VAR_SMARTY);
			/* @var $smarty Smarty */

			$product = GetProduct($this->getData('productID'));
			if(!$product)RedirectSQ('?ukey=product_not_found&view=noframe&productwidget=1');
			if(!$product['enabled'])RedirectSQ('?ukey=product_not_found&view=noframe&productwidget=1');

			$product['PriceWithUnit'] = show_price( $product['Price'] );
			$product['list_priceWithUnit'] = show_price( $product['list_price'] );
			$product['product_extra'] = GetExtraParametrs($product['productID']);
			$product['SavePrice'] = show_price($product['list_price']-$product['Price']);
			$product['PriceWithOutUnit']= show_priceWithOutUnit( $product['Price'] );
			if ($product['list_price'])
				$product['SavePricePercent'] = ceil(((($product['list_price']-$product['Price'])/$product['list_price'])*100));

			_setPictures( $product );

			$smarty->assign('productwidget', 1);
			$smarty->assign('product_info', $product);
			$smarty->assign('main_body_style', ' style="background: #FFF;"');
			$smarty->assign('main_body_tpl', 'product_widget.html');
		}
	}
	
	ActionsController::exec('ProductWidgetController');
?>