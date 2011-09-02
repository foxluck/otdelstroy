<?php
	class ShoppingCart{
		
		/**
		 * Items in shoppping cart
		 *
		 * @var xmlNodeX
		 */
		var $Items;
		/**
		 * Reference to current item in iteration
		 *
		 * @var xmlNodeX
		 */
		var $iterCurrItem;
		
		function ShoppingCart(){
			
			$this->Items = new xmlNodeX('items');
		}
		
		function loadCurrentCartFromSession(){
			
			$i=0;
			
			$this->Items = new xmlNodeX('items');
			
			if(!isset($_SESSION["gids"]) || !is_array($_SESSION["gids"]))return false;
			
			foreach( $_SESSION["gids"] as $productID ){
				
				if($productID==0)continue;
		
				$dbr = db_phquery( 'SELECT * FROM ?#PRODUCTS_TABLE WHERE productID=?',$productID);
				if( !db_num_rows($dbr['resource']) )continue;
				$product = db_fetch_assoc($dbr);

				$aItem = &$this->Items->child('item');
				
				$aProduct = &$aItem->child('product', array('id' => $productID, 'free-shipping' => $product['free_shipping']));
				if($product['shipping_freight'])
					$aProduct->child('freight', array('currency'=>''), $product['shipping_freight']);
				$aItem->child('quantity', null, $_SESSION['counts'][$i]);
		
				$aItem->attribute('id', $productID.(isset($_SESSION["configurations"][$i])&&count($_SESSION["configurations"][$i])?'_'.implode('_', $_SESSION["configurations"][$i]):''));
				$aVariants = &$aItem->child('variants');
				foreach( $_SESSION["configurations"][$i] as $var ){
					
					$aVariants->child('variant', array('id'=>$var));
				}
				$aPrice = &$aItem->child('price');
				$aPrice->attribute('currency','');
				$aPrice->setData(GetPriceProductWithOption(isset($_SESSION["configurations"][$i])?$_SESSION["configurations"][$i]:array(), $productID));
				$i++;
			}
		}
		
		function loadCurrentCustomerCart($customerID){
		
			$this->Items = new xmlNodeX('items');
			/**
			 * Select all items from SHOPPING_CARTS_TABLE
			 */
			$dbq = '
				SELECT itemID, Quantity FROM ?#SHOPPING_CARTS_TABLE WHERE customerID=?
			';
			$q_items = db_phquery($dbq, $customerID);
			while($item = db_fetch_assoc($q_items)){
				
				$productID = GetProductIdByItemId( $item["itemID"] );
				
				if ( $productID == null || trim($productID) == "" )continue;
				$dbr = db_phquery( 'SELECT * FROM ?#PRODUCTS_TABLE WHERE productID=?',$productID);
				if( !db_num_rows($dbr['resource']) )continue;
				$product = db_fetch_assoc($dbr);
				
				$aItem = &$this->Items->child('item');
				$aItem->attribute('id', $item['itemID']);
				
				$aProduct = &$aItem->child('product', array('id' => $productID, 'free-shipping' => $product['free_shipping']));
				if($product['shipping_freight'])
					$aProduct->child('freight', array('currency'=>''), $product['shipping_freight']);
				$aItem->child('quantity', null, $item['Quantity']);
		
				$variants = array();
				$variants = GetConfigurationByItemId( $item["itemID"] );
				$aVariants = &$aItem->child('variants');
				
				foreach ($variants as $v){
					
					$aVariants->child('variant', array('id'=>$v));
				}
				$aPrice = &$aItem->child('price');
				$aPrice->attribute('currency','');
				$aPrice->setData(GetPriceProductWithOption($variants, $productID));
			}
		}
		
		/**
		 * @param int $orderID
		 * @param array $shipping_info - ('countryID','zoneID', 'zip')
		 * @param array $billing_info - ('countryID','zoneID', 'zip')
		 */
		function saveToOrderedCarts($orderID, $shipping_info, $billing_info, $calculate_tax = true){
			
			$sql = "DELETE FROM ?#ORDERED_CARTS_TABLE WHERE orderID=?";
			db_phquery($sql,$orderID);
			
			$r_aItem = $this->Items->getChildNodes('item');
			$tc = count($r_aItem);
			for ($i=0; $i<$tc; $i++){
				
				$aItem = &$r_aItem[$i];
				/* @var $aItem xmlNodeX */
				$aProduct = &$aItem->getFirstChildByName('product');
				$productID = $aProduct->attribute('id');
				db_phquery('INSERT ?#SHOPPING_CART_ITEMS_TABLE (productID) VALUES(?)',$productID);
				$aItem->attribute('id', db_insert_id(SHOPPING_CART_ITEMS_TABLE));
				//if(strpos($aItem->attribute('id'), '_') !== false){
					
				//	db_phquery('INSERT ?#SHOPPING_CART_ITEMS_TABLE (productID) VALUES(?)',$productID);
				//	$aItem->attribute('id', db_insert_id(SHOPPING_CART_ITEMS_TABLE));

					$aVariants = &$aItem->getFirstChildByName('variants');
					$r_aVariant = $aVariants->getChildrenByName('variant');
					
					foreach ($r_aVariant as $aVariant){
						/* @var $aVariant xmlNodeX */
						
						db_phquery('INSERT ?#SHOPPING_CART_ITEMS_CONTENT_TABLE (itemID, variantID) 
							VALUES(?,?)', $aItem->attribute('id'), $aVariant->attribute('id'));
					}
				//}
				
				$dbq = '
					SELECT '.LanguagesManager::sql_prepareField('name').' AS name, product_code FROM ?#PRODUCTS_TABLE WHERE productID=?
				';
				$q_product = db_phquery($dbq, $productID);
				$product = db_fetch_row( $q_product );
				
				$productComplexName = '';
				
				$aVariants = &$aItem->getFirstChildByName('variants');
				$r_aVariant = $aVariants->getChildrenByName('variant');
				$variants = array();
				
				foreach ($r_aVariant as $aVariant){
					/* @var $aVariant xmlNodeX */
					$variants[] = $aVariant->attribute('id');
				}
				
				$options = GetStrOptions( $variants );
				if ( $options != "" )
					$productComplexName = $product["name"]." (".$options.")";
				else
				$productComplexName = $product["name"];
				if(
					//defined('CONF_ENABLE_PRODUCT_SKU')&&
					//constant('CONF_ENABLE_PRODUCT_SKU')&&
					$product["product_code"]){
					$productComplexName = "[".$product["product_code"]."] ".$productComplexName;
				}
		
				$price = GetPriceProductWithOption( $variants, $productID );

				$tax = $calculate_tax?taxCalculateTax2( $productID, $shipping_info, $billing_info ):0;
				
				$dbq = '
					INSERT ?#ORDERED_CARTS_TABLE (itemID, orderID, name, Price, Quantity, tax )
					VALUES (?, ?, ?, ?, ?, ?)
				';
				db_phquery($dbq, $aItem->attribute('id'), $orderID, $productComplexName, $price, $aItem->getChildData('quantity'), $tax);
				
				$q = db_phquery( 'SELECT statusID FROM ?#ORDERS_TABLE WHERE orderID=?',$orderID );
				$order = db_fetch_row( $q );
				if ( $order["statusID"] != ostGetCanceledStatusId() && CONF_CHECKSTOCK ){
					
					$dbq = '
						UPDATE ?#PRODUCTS_TABLE SET in_stock=in_stock-'.xEscapeSQLstring($aItem->getChildData('quantity')).'
						WHERE productID=? 
					';
					db_phquery($dbq, $productID);
				}
			}
		}
		
		/**
		 * @return string
		 */
		function exportInXML(){

			return $this->Items->getNodeXML();
		}
		
		/**
		 * @param string $xmlCart
		 */
		function loadFromXML($xmlCart){
			
			$this->Items->renderTreeFromInner($xmlCart);
		}
	
		/**
		 * Emulate cartGetCartContent function not fully: doesnt fill feight_cost
		 *
		 * @return array: (cart_content, total_price, freight_cost)
		 */
		function emulate_cartGetCartContent(){
			
			$cart_content = array();
			$freight_cost = 0;
			$r_aItem = $this->Items->getChildNodes('item');
			foreach ($r_aItem as $aItem){
				/* @var $aItem xmlNodeX */
				$aProduct = &$aItem->getFirstChildByName('product');
				$aPrice = &$aItem->getFirstChildByName('price');
				$product = GetProduct($aProduct->attribute('id'));
				$strOptions=GetStrOptions($this->emulate_GetConfigurationByItemId($aItem));
				if ( trim($strOptions) != '' )$product['name'].='  ('.$strOptions.')';

				$cart_content[] = array(
					'productID' =>  $aProduct->attribute('id'),
					'id'		=>	$aItem->attribute('id')?$aItem->attribute('id'):0, 
					'name'		=>	$product['name'], 
					'quantity'	=>	$aItem->getChildData('quantity'),
					'free_shipping'	=>	$aProduct->attribute('free-shipping'), 
					'costUC' =>	$aPrice->getData(),
					'cost' => show_price($aItem->getChildData('quantity')*PaymentModule::_convertCurrency($aPrice->getData(), $aPrice->attribute('currency'), 0), 0),
					'product_code' =>	$product['product_code'],
					);
				$aFreight = $aProduct->getFirstChildByName('freight');
				if(!is_null($aFreight)){
					
					$freight_cost += $aItem->getChildData('quantity')*virtualModule::_convertCurrency($aFreight->getData(), $aFreight->attribute('currency'), 0);
				}
			}
			
			$cart = array( 	
			'cart_content'	=> $cart_content, 
			'total_price'	=> $this->calculateTotalPrice(),
			'freight_cost'	=> $freight_cost );
			return $cart;
		}

		/**
		 * Emulate GetConfigurationByItemId function
		 *
		 * @param xmlNodeX $xnItem - now only xmlNodex. todo in future itemID understanding
		 * return array: (<variantID1>,<variantID2>,...)
		 */
		function emulate_GetConfigurationByItemId($xnItem){
			
			$variant_ids = array();
				$xnVariants = &$xnItem->getFirstChildByName('variants');
				if(is_null($xnVariants))return $variant_ids;
				
				$r_xnVariant = $xnVariants->getChildrenByName('variant');
				foreach ($r_xnVariant as $xnVariant){
					/* @var $xnVariant xmlNodeX */
					$variant_ids[] = $xnVariant->attribute('id');
				}
			return $variant_ids;
		}
		
		/**
		 * @param bool $roundfloat: if true total price will have view like 0.00
		 * @param mixed
		 * @return float: total price in UC
		 */
		function calculateTotalPrice($roundfloat = true, $currency = 0, $ignore_freeshipping = false){
			
			$total_price = 0;
			$r_aItem = $this->Items->getChildNodes('item');
			foreach ($r_aItem as $aItem){
				/* @var $aItem xmlNodeX */
				$aProduct = &$aItem->getFirstChildByName('product');
				if($ignore_freeshipping && $aProduct->attribute('free-shipping'))continue;
				
				$aPrice = &$aItem->getFirstChildByName('price');
				$aQuantity = &$aItem->getFirstChildByName('quantity');
				
				$item_price = virtualModule::_convertCurrency($aPrice->getData(), $aPrice->attribute('currency'),$currency);
				
				$total_price += ($roundfloat?RoundFloatValue($item_price):$item_price)*$aQuantity->getData();
			}
			
			return $total_price;
		}
		
		function calculateTotalPriceWithoutFreeShippingProducts($roundfloat = true, $currency = 0){
			
			return $this->calculateTotalPrice($roundfloat, $currency, true);
		}
	
		function loadCurrentCart(){

			ClassManager::includeClass('customer');
			$customerEntry = Customer::getAuthedInstance();
			$customerID = isset($_SESSION["log"])?regGetIdByLogin($_SESSION["log"]):0;
			if($customerEntry instanceof  customer){
				$this->loadCurrentCustomerCart($customerEntry->customerID);
			}else{
				$this->loadCurrentCartFromSession();
			}
		}

		function saveCurrentCart(){
			

			ClassManager::includeClass('customer');
			$customerEntry = Customer::getAuthedInstance();
			$customerID = isset($_SESSION["log"])?regGetIdByLogin($_SESSION["log"]):0;
			if($customerEntry instanceof  customer){
				$this->saveToCurrentCustomerCart();
			}else{
				$this->saveToCurrentSessionCart();
			}
		}
		
		function saveToCurrentSessionCart(){
			
			$this->cleanCurrentCart('recalculate');
			$r_aItem = $this->Items->getChildNodes('item');
			$tc = count($r_aItem);
			for ($i=0; $i<$tc; $i++){
				
				$aItem = &$r_aItem[$i];
				/* @var $aItem xmlNodeX */
				$aProduct = &$aItem->getFirstChildByName('product');
				$productID = $aProduct->attribute('id');

				$aVariants = &$aItem->getFirstChildByName('variants');
				$r_aVariant = $aVariants->getChildrenByName('variant');
				$variants = array();
				
				foreach ($r_aVariant as $aVariant){
					/* @var $aVariant xmlNodeX */
					$variants[] = $aVariant->attribute('id');
				}
				
				cartAddToCart($productID, $variants, $aItem->getChildData('quantity'));
			}
			cartMinimizeCart();
			
		}
		
		function saveToCurrentCustomerCart(){
			
			$this->saveToCurrentSessionCart();
		}
		
		function cleanCurrentCart($mode='succes'){
			
			cartClearCartContet($mode);
		}
		
		function isEmpty(){
			
			return count($this->Items->getChildNodes('item'))<=0;
		}
		
		/**
		 * Set item quantity. If less 0 remove item
		 *
		 * @param xmlNodeX $Item - item node
		 * @param int $quantity
		 */
		function _setItemQuantity(&$Item, $quantity){
			
			$xnQuantity = &$Item->getFirstChildByName('quantity');
			$xnQuantity->setData(intval($quantity));
		}
		
		/**
		 * Get item quantity
		 *
		 * @param xmlNodeX $Item - item node
		 * @return int
		 */
		function _getItemQuantity(&$Item){
			
			$xnQuantity = &$Item->getFirstChildByName('quantity');
			return $xnQuantity->getData();
		}
		
		/**
		 * Remove item from cart
		 *
		 * @param int $itemID - unique item id
		 */
		function removeItem($itemID){
			
			$xnItem = &$this->_getItem($itemID);
			if(!($xnItem instanceof xmlNodeX))return false;
			
			$this->Items->removeChildNode($xnItem);
			
			return true;
		}
		
		/**
		 * Get item node
		 *
		 * @param int $itemID - item id
		 * @return xmlNodeX - item node
		 */
		function &_getItem($itemID){
			
			@list($xnItem) = $this->Items->xPath('/items/item[@id="'.xHtmlSpecialChars($itemID).'"]');
			
			return $xnItem;
		}
		
		function setItemQuantity($itemID, $quantity){
			
			if($quantity<=0){
				$this->removeItem($itemID);
				return ;
			}
			
			$xnItem = $this->_getItem($itemID);
			if($xnItem instanceof xmlNodeX){
				$this->_setItemQuantity($xnItem, $quantity);
			}
		}
	}
?>