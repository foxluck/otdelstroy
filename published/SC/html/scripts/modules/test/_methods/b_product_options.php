<?php
class ProductOptionsController extends ActionsController{

	function delete_variant(){
	
		safeMode(true, 'variantID=');
		
		db_phquery('DELETE FROM ?#CATEGORY_PRODUCT_OPTION_VARIANTS WHERE variantID=?', $this->getData('variantID'));
		db_phquery('DELETE FROM ?#PRODUCT_OPTIONS_VALUES_TABLE WHERE variantID=?', $this->getData('variantID'));
		db_phquery('DELETE FROM ?#PRODUCTS_OPTIONS_SET_TABLE WHERE variantID=?', $this->getData('variantID'));
		db_phquery("DELETE FROM ?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE WHERE variantID=?", $this->getData('variantID'));
		
		RedirectSQ('variantID=');
	}
	
	function save_values(){

		safeMode(true);
	
		// update existing values
		$updateOptions = scanArrayKeysForID($_POST, array( "sort_order", 'option_value_\w{2}' ) );
		optUpdateOptionValues($updateOptions);
	
		// add new value
		if ( !LanguagesManager::ml_isEmpty('option_value', $_POST)){
			
			optAddOptionValue($_POST);
		}
	
		Message::raiseMessageRedirectSQ(MSG_SUCCESS, '', 'msg_update_successful');
	}
	
	function delete_option(){
		
		safeMode(true, 'optionID=');
		
		db_phquery("DELETE FROM ?#CATEGORY_PRODUCT_OPTION_VARIANTS where optionID=?", $this->getData('optionID'));
		db_phquery("DELETE FROM ?#CATEGORY_PRODUCT_OPTIONS_TABLE where optionID=?", $this->getData('optionID'));
		db_phquery("DELETE FROM ?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE where optionID=?", $this->getData('optionID'));
		db_phquery("DELETE FROM ?#PRODUCTS_OPTIONS_SET_TABLE where optionID=?", $this->getData('optionID'));
		db_phquery("DELETE FROM ?#PRODUCT_OPTIONS_VALUES_TABLE where optionID=?", $this->getData('optionID'));
		db_phquery("DELETE FROM ?#PRODUCT_OPTIONS_TABLE where optionID=?", $this->getData('optionID'));
		
		RedirectSQ('optionID=');
	}
	
	function save_options(){
		
		safeMode(true);
		
		//save existing
		$updateOptions = scanArrayKeysForID($_POST, array( "extra_option_\w{2}", "extra_sort" ) );
		//now update database
		optUpdateOptions($updateOptions);
	
		//add a new option
		if ( !LanguagesManager::ml_isEmpty('name', $this->getData()) ){
			
			$this->setData('sort_order', $this->getData('add_sort'));
			optAddOption($this->getData());
		}
		
		Message::raiseMessageRedirectSQ(MSG_SUCCESS, '', 'msg_update_successful');
	}
	
	function main(){
		
		$Register = &Register::getInstance();
		/*@var $Register Register*/

		$smarty = &$Register->get(VAR_SMARTY);
		/*@var $smarty Smarty*/

		if ( !isset($_GET["optionID"]) ){
		
			//now select all available product options
			$options = optGetOptions();
			$smarty->assign("options", $options);
		}else{
			
			$option = optGetOptionById( $_GET["optionID"] );
			$values = optGetOptionValues( $_GET["optionID"] );
		
			$smarty->assign("optionID", $_GET["optionID"] );
			$smarty->assign("values", $values);
			$smarty->assign("option_name",$option["name"]);
			$smarty->assign("value_count", count($values) );
		}
		
		$smarty->assign("admin_sub_dpt", "product_options.html");
	}
}

ActionsController::exec('ProductOptionsController');
?>