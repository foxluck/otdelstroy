<?php
	class productOptionConfiguratorController extends ActionsController {
		
		function save_values(){
			
			$Register = &Register::getInstance();
			$GetVars = &$Register->get(VAR_GET);
			
			$optionID = isset($GetVars['optionID'])?$GetVars['optionID']:0;
			$productID = isset($GetVars['productID'])?$GetVars['productID']:prdCreateEmptyProduct();
			
			$option_show_times = (int)$this->getData('option_show_times');
			if ( (int)$option_show_times <= 0 )$option_show_times = 1;
	
			$data = scanArrayKeysForID($this->getData(), array( 'switchOn', 'price_surplus' ));
			
			UpdateConfiguriableProductOption($optionID, $productID,$option_show_times, $this->getData('default_radiobutton'), $data );
			
			Message::raiseMessageRedirectSQ(MSG_SUCCESS, '&saved=ok&productID='.$productID, 'msg_information_save');
		}
		
		function main(){
			
			$Register = &Register::getInstance();
			$GetVars = &$Register->get(VAR_GET);
			$smarty = &$Register->get(VAR_SMARTY);
			/* @var $smarty Smarty */
			
			$optionID = isset($GetVars['optionID'])?$GetVars['optionID']:0;
			$productID = isset($GetVars['productID'])?$GetVars['productID']:0;
	
			$smarty->assign('OptionInfo', optGetOptionById($optionID));
			$q=db_phquery('SELECT option_show_times, variantID FROM ?#PRODUCT_OPTIONS_VALUES_TABLE WHERE optionID=? AND productID=?',$optionID,$productID);
			if ( $r=db_fetch_row($q) ){
				$option_show_times=$r['option_show_times'];
				$variantID_default=$r['variantID'];
			}else{
				$option_show_times=1;
				$variantID_default=null;
			}
			$smarty->assign('option_show_times',$option_show_times);
			$smarty->assign('variantID_default',$variantID_default);
			
			$checked_variants_num = 0;
			
			$OptionVariants = optGetOptionValues($optionID);
			//TODO: minimize SQL queries here

			foreach ($OptionVariants as $_Ind=>$_Variant){
				
				$q=db_phquery('SELECT price_surplus FROM ?#PRODUCTS_OPTIONS_SET_TABLE WHERE productID=? AND optionID=? AND variantID=?',$productID,$optionID, $_Variant['variantID']);
				if ( $r=db_fetch_row($q) )$OptionVariants[$_Ind]['price_surplus'] = $r["price_surplus"];
		
				$q1=db_phquery('SELECT COUNT(*) as cnt FROM ?#PRODUCTS_OPTIONS_SET_TABLE WHERE productID=? AND optionID=? AND variantID=?',$productID,$optionID,$_Variant['variantID']);
				$r1=db_fetch_row($q1);
				$OptionVariants[$_Ind]['checked'] = ($r1['cnt']!=0);
				$checked_variants_num += $OptionVariants[$_Ind]['checked'];
			}
			
			//new code
			/*$variantIDs = array();
			$OptionVariantsByID = array();
			foreach ($OptionVariants as $_Ind=>$_Variant){
				
				$OptionVariantsByID[$_Variant['variantID']] = $_Variant;
				$variantIDs[] = $_Variant['variantID'];
				
			}
			if(count($variantIDs)){
				
				$q=db_phquery('SELECT price_surplus,variantID FROM ?#PRODUCTS_OPTIONS_SET_TABLE WHERE productID=? AND optionID=? AND variantID IN (?@)',$productID,$optionID, $variantIDs);
				while($r=db_fetch_row($q) ){
					$OptionVariantsByID[$r["variantID"]]['price_surplus'] = $r["price_surplus"];
				}
		
				$q1=db_phquery('SELECT COUNT(*),variantID as cnt FROM ?#PRODUCTS_OPTIONS_SET_TABLE WHERE productID=? AND optionID=? AND variantID IN (?@) GROUP BY variantID',$productID,$optionID,$variantIDs);
				while($r1=db_fetch_row($q1)){
					$OptionVariantsByID[$r1["variantID"]]['checked'] = ($r1['cnt']!=0);
					$checked_variants_num += $OptionVariantsByID[$r1["variantID"]]['checked'];
				}
			}*/
			
			
			
			$smarty->assign('OptionVariants', $OptionVariants);
			$smarty->assign('OptionVariantsNumber', count($OptionVariants));
			
			$smarty->assign('checked_variants_num', $checked_variants_num);
			$smarty->assign('productID', $productID);
		}
	}
	
	ActionsController::exec('productOptionConfiguratorController');
?>