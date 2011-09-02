<?php
define('CARTVIEW_FADE', 'fade');
define('CARTVIEW_FRAME', 'frame');
define('CARTVIEW_WIDGET', 'widget');

class ShoppingCartController extends ActionsController{

	function _detect_cart_view(){

		$Register = &Register::getInstance();
		/*@var $Register Register*/
		$smarty = &$Register->get(VAR_SMARTY);
		/*@var $smarty Smarty*/
		if($smarty->get_template_vars('PAGE_VIEW') !== 'noframe')return CARTVIEW_FRAME;
		if($Register->is_set('widgets')&&$Register->get('widgets')){
			$smarty->assign('widget_view',true);
			return CARTVIEW_WIDGET;
		}
		if($smarty->get_template_vars('PAGE_VIEW') == 'noframe')return CARTVIEW_FADE;

		return CARTVIEW_FRAME;
	}

	function add_product(){

		$variants=array();
		foreach($this->getData() as $key => $val ){
				
			if(!strstr($key, 'option_'))continue;
			$variants[] = $val;
		}
		$res = cartAddToCart($this->getData('productID'), $variants, $this->getData('product_qty') );

		$Register = &Register::getInstance();
		/*@var $Register Register*/
		if($res === false){
			RedirectSQ('?ukey=product_not_found&view=&external=');
		}elseif($res === 0){
			RedirectSQ('?ukey=product_out_of_stock&view=&external=');
		}else{
			//iframe cookie security workaround
			$url_chunk = '';
			if(isset($_GET['widgets'])&&$_GET['widgets']&&!strpos($_SERVER['HTTP_REFERER'],$_SERVER['HTTP_HOST'])){
				$url_chunk .= '&check_cookie='.session_id().'';
				$url_chunk .= '&productID='.$this->getData('productID');
				$Register = &Register::getInstance();
				$widgets = false;
				$Register->set('widgets',$widgets);
				$_SERVER['REQUEST_URI'] = preg_replace('/(^|&)widgets=1/','', $_SERVER['REQUEST_URI']);
			}
			RedirectSQ('?ukey=cart&view=&external='.$url_chunk);
		}
	}

	function try_apply_discount_coupon()
	{
		$coupon_code = $this->getData('coupon_code');
	  
		$coupon_id = $this->_check_and_apply_coupon($coupon_code);
	  
		$resCart = cartGetCartContent();
		$resDiscount = dscGetCartDiscounts( $resCart["total_price"], (isset($_SESSION["log"]) ? $_SESSION["log"] : "") );
		$currencyEntry = Currency::getSelectedCurrencyInstance();
		$coupon_discount_show = $resDiscount['coupon_discount']['cu'] > 0 ? $currencyEntry->getView($resDiscount['coupon_discount']['cu']) : '';
	  
		$GLOBALS['_RESULT'] = array(
            'applied' => ($coupon_id != null ? 'Y' : 'N')
		,'new_coupon_show' => $coupon_discount_show
		,'new_total_show_value' => $currencyEntry->getView($resDiscount['total']['cu'])
		);
	  
		die();
	}

	function _check_and_apply_coupon($coupon_code)
	{
		ClassManager::includeClass('discount_coupon');
		$coupon_id = discount_coupon::check($coupon_code);
		if($coupon_id !== null)
		{
			discount_coupon::apply($coupon_id);
		};
	  
		return $coupon_id;
	}

	function remove_doscount_coupon()
	{
		ClassManager::includeClass('discount_coupon');
		discount_coupon::remove();
	  
		$resCart = cartGetCartContent();
		$resDiscount = dscGetCartDiscounts( $resCart["total_price"], (isset($_SESSION["log"]) ? $_SESSION["log"] : "") );
		$currencyEntry = Currency::getSelectedCurrencyInstance();

		$GLOBALS['_RESULT'] = array(
            'new_total_show_value' => $currencyEntry->getView($resDiscount['total']['cu'])
		);
	  
		die();
	}

	function main(){

		$Register = &Register::getInstance();
		$smarty = &$Register->get(VAR_SMARTY);
		/*@var $smarty Smarty*/
		// shopping cart
		
		//iframe cookie security workaround
		if(isset($_GET['check_cookie'])){
			if(($_GET['check_cookie'] != session_id())){

				$productID = (int)$_GET['productID'];
				$product_data = GetProduct($productID);
				$product_slug = ($product_data&&isset($product_data['slug']))?$product_data['slug']:'';
				$url = "?ukey=product_widget&productID={$productID}&product_slug={$product_slug}&check_cookie&";
				$widgets = false;
				$Register->set('widgets',$widgets);
				$_SERVER['REQUEST_URI'] = preg_replace('/(^|&)widgets=1/','', $_SERVER['REQUEST_URI']);
				RedirectSQ($url);
			}else{
				renderURL('check_cookie&productID','',true);
			}
		}

		if ( isset($_GET["make_more_exact_cart_content"]) )
		$smarty->assign( "make_more_exact_cart_content", 1 );

		if (isset($_GET["remove"]) && $_GET["remove"] > 0){ //remove from cart product with productID == $remove

			$cartEntry = new ShoppingCart();
			$cartEntry->loadCurrentCart();
			$cartEntry->setItemQuantity($_GET['remove'], 0);
			$cartEntry->saveCurrentCart();
			if($cartEntry->isEmpty())
			{
				//remove coupon from empty cart
				ClassManager::includeClass('discount_coupon');
				discount_coupon::remove();
			};
			RedirectSQ('remove=');
		}

		$cart_view = $this->_detect_cart_view();
		if (isset($_POST["update"])||isset($_POST["recalculate"])){ //update shopping cart content

			if($_POST['discount_coupon_code'] != '')
			{
				$this->_check_and_apply_coupon($_POST['discount_coupon_code']);
			};

			$cartEntry = new ShoppingCart();
			$cartEntry->loadCurrentCart();
				
			$upd_data = scanArrayKeysForID($_POST, 'count');
			foreach ($upd_data as $_itemID=>$_data){

				$cartEntry->setItemQuantity($_itemID, intval($_data['count']));
			}
				
			$cartEntry->saveCurrentCart();

			if($cartEntry->isEmpty())
			{
				//remove coupon from empty cart
				ClassManager::includeClass('discount_coupon');
				discount_coupon::remove();
			};
				
				
			if(cartCheckMinOrderAmount()&&cartCheckMinTotalOrderAmount())
			{
				switch ($cart_view){
					case CARTVIEW_FRAME:
						if(isset($_POST['checkout'])&&($Register->get('store_mode') == 'facebook')){
							$store_mode = false;
							$Register->set('store_mode',$store_mode);
							$jsgoto = '?ukey=checkout&view=noframe';
							RedirectSQ($jsgoto?'jsgoto='.base64_encode(set_query($jsgoto)):'');
						}
						RedirectSQ(isset($_POST['checkout'])?'?ukey=checkout':(isset($_POST['ppe_checkout_x'])?'ppexpresscheckout2=1':(isset($_POST['google_checkout_x'])?'googlecheckout2=1':'')));
						break;
					case CARTVIEW_WIDGET:
					case CARTVIEW_FADE:
						$jsgoto = isset($_POST['checkout'])?'?ukey=checkout&view=noframe':(isset($_POST['ppe_checkout_x'])?'ppexpresscheckout2=1&view=frame':(isset($_POST['google_checkout_x'])?'googlecheckout2=1&view=frame':''));
						RedirectSQ($jsgoto?'jsgoto='.base64_encode(set_query($jsgoto)):'');
						break;
				}}elseif(isset($_POST['checkout'])||isset($_POST['google_checkout_x'])||isset($_POST['ppe_checkout_x'])){
					$smarty->assign('cart_error_show','1');
				}
		}

		if (isset($_GET["clear_cart"])){ //completely clear shopping cart

			$cartEntry = new ShoppingCart();
			$cartEntry->loadCurrentCart();
			$cartEntry->cleanCurrentCart('erase');
				
			//remove coupon from empty cart
			ClassManager::includeClass('discount_coupon');
			discount_coupon::remove();
				
			RedirectSQ('clear_cart=');
		}
		if(isset($_POST['checkout'])){
			
			if(SystemSettings::is_hosted()&&file_exists(WBS_DIR.'/kernel/classes/class.metric.php')){
				include_once(WBS_DIR.'/kernel/classes/class.metric.php');

				$DB_KEY=SystemSettings::get('DB_KEY');
				$U_ID = sc_getSessionData('U_ID');

				$metric = metric::getInstance();
				$metric->addAction($DB_KEY, $U_ID,'SC', 'CHECKOUT', isset($_GET['widgets'])?'WIDGET':'STOREFRONT','');
			}
				
		}


		$resCart = cartGetCartContent();
		$resDiscount = dscGetCartDiscounts( $resCart["total_price"], (isset($_SESSION["log"]) ? $_SESSION["log"] : "") );

		$currencyEntry = Currency::getSelectedCurrencyInstance();
			
		$cart_discount_show = $resDiscount['other_discounts']['cu'] > 0 ? $currencyEntry->getView($resDiscount['other_discounts']['cu']) : '';
		$coupon_discount_show = $resDiscount['coupon_discount']['cu'] > 0 ? $currencyEntry->getView($resDiscount['coupon_discount']['cu']) : '';

		$smarty->assign("cart_content", xHtmlSpecialChars($resCart["cart_content"], null, 'name') );
		$smarty->assign("cart_amount", $resCart["total_price"] - $resDiscount["discount_standart_unit"]);
		$smarty->assign('cart_min', show_price(CONF_MINIMAL_ORDER_AMOUNT));
		$smarty->assign("cart_total",	$currencyEntry->getView($resDiscount['total']['cu']));

		$smarty->assign('cart_discount', $cart_discount_show);
		$smarty->assign('discount_percent', round($resDiscount['discount_percent'],1));
		$smarty->assign('coupon_discount', $coupon_discount_show);

		$smarty->assign("current_coupon", discount_coupon::getCurrentCoupon());

		if ( isset($_SESSION['log']) )
		$smarty->assign( 'shippingAddressID', regGetDefaultAddressIDByLogin($_SESSION['log']) );

		if(isset($_GET['min_order']))$smarty->assign('minOrder','error');

		if(isset($_GET['jsgoto'])){
			$smarty->assign('jsgoto', base64_decode($_GET['jsgoto']));
		}
		$smarty->assign('main_content_template', 'shopping_cart.html');
		$smarty->assign('main_body_style','style="'.(((CONF_SHOPPING_CART_VIEW==2)||($cart_view==CARTVIEW_FRAME))?'':'background:#FFFFFF;').'min-width:auto;width:auto;_width:auto;"');
	}
}

ActionsController::exec('ShoppingCartController');
?>