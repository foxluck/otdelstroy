<?php
/**
 * @package Modules
 * @subpackage AdministratorScreens
 */
	class ProductWidgetsController extends ActionsController {
		
		function _search_products($searchstring){
			
			$searchstring = trim($searchstring);
			if(!$searchstring)return;
			
			$e_searchstring = preg_replace('/\%/u', '\\%', xEscapeSQLstring($searchstring));
			$fl_names = LanguagesManager::ml_getLangFieldNames('name');

			$gridEntry = ClassManager::getInstance('grid');
			/*@var $gridEntry Grid*/
			
			$qr_where = '
				WHERE (p.enabled = 1) AND (
					(LOWER(t1.name) LIKE LOWER("%'.$searchstring.'%")) OR
					(LOWER(p.product_code) LIKE LOWER("%'.$searchstring.'%")) OR
					( LOWER(p.'.implode(') LIKE LOWER("%'.$e_searchstring.'%") OR LOWER(p.', $fl_names).') LIKE LOWER("%'.$e_searchstring.'%") )
				)';
			
			$qr_from = '
				FROM ?#PRODUCTS_TABLE p 
				LEFT JOIN ?#TAGGED_OBJECTS_TBL t2 ON p.productID=t2.object_id 
				LEFT JOIN ?#TAGS_TBL t1 ON t2.tag_id=t1.id AND t2.object_type="product"
			';
			
			$gridEntry->query_total_rows_num = 'SELECT COUNT(DISTINCT p.productID) '.$qr_from.$qr_where;
			$gridEntry->query_select_rows = 'SELECT p.productID, p.Price, p.slug, '.LanguagesManager::sql_prepareField('p.name').' AS p_name '.$qr_from.$qr_where.'	GROUP BY p.productID';

			$gridEntry->show_rows_num_select = false;
			$gridEntry->default_sort_direction = 'ASC';
			$gridEntry->rows_num = 20;
			
			$gridEntry->registerHeader(translate("prdset_product_name"), 'p_name', true, 'asc');
			$gridEntry->prepare();
			
			$Register = &Register::getInstance();
			/*@var $Register Register*/
			$smarty = &$Register->get(VAR_SMARTY);
			/*@var $smarty Smarty*/

			$rows = $smarty->get_template_vars('GridRows');
			for($k = count($rows)-1; $k>=0; $k--){
				$rows[$k]['name'] = $rows[$k]['p_name'];
				$rows[$k]['price_str'] = show_price($rows[$k]['Price']);
			}
			
			$smarty->assign('GridRows', $rows);
		}
		
		function main(){
			
			$Register = &Register::getInstance();
			$smarty = &$Register->get(VAR_SMARTY);
			/* @var $smarty Smarty */

			$this->_search_products($this->getData('searchstring'));

			$LanguageEntry = &LanguagesManager::getDefaultLanguage();
			$btn_add2cart_alt = $LanguageEntry->getLocal('btn_add2cart');
			$btn_view_alt = $LanguageEntry->getLocal('btn_viewcart');
			$smarty->assign('btn_add2cart_alt', $btn_add2cart_alt['value']);
			$smarty->assign('btn_viewcart_alt', $btn_view_alt['value']);
			
			$smarty->assign('searchstring', $this->getData('searchstring'));
			$smarty->assign('iframe_width', CONF_PRDPICT_THUMBNAIL_SIZE+100);
			$smarty->assign('iframe_height', CONF_PRDPICT_THUMBNAIL_SIZE+200);
			$smarty->assign('admin_sub_dpt', 'product_widgets.html');
		}
	}
	
	ActionsController::exec('ProductWidgetsController');
?>