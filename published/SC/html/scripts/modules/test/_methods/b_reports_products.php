<?php
	include(DIR_FUNC.'/report_function.php');

	class ProductsReportController extends ActionsController {
		
		/**
		 * @var DataBase
		 */
		var $DBHandler;
		
		function __getProductsNum(){
			
			return db_phquery_fetch(DBRFETCH_FIRST, 'SELECT COUNT(*) FROM ?#PRODUCTS_TABLE');
		}
		
		function __getCategoriesNum(){
			
			return db_phquery_fetch(DBRFETCH_FIRST, 'SELECT COUNT(*) FROM ?#CATEGORIES_TABLE');
		}
		
		function __getInvisibleProductsNum(){
			
			return db_phquery_fetch(DBRFETCH_FIRST, 'SELECT COUNT(*) FROM ?#PRODUCTS_TABLE WHERE `enabled`<>1 OR `categoryID`=0');
		}
		
		function __getNotInStockProductsNum(){
			
			return db_phquery_fetch(DBRFETCH_FIRST, 'SELECT COUNT(*) FROM ?#PRODUCTS_TABLE WHERE `in_stock`<=0');
		}
		
		function ProductsReportController(){
			
			$Register = &Register::getInstance();
			$this->DBHandler = &$Register->get(VAR_DBHANDLER);
			
			parent::ActionsController();
		}
		
		function main(){
			
			$Register = &Register::getInstance();
			$smarty = &$Register->get(VAR_SMARTY);
			/* @var $smarty Smarty */
			$GetVars = &$Register->get(VAR_GET);
			
			$gridEntry = ClassManager::getInstance('grid');
			/*@var $gridEntry Grid*/
			
			$gridEntry->query_total_rows_num = 'SELECT COUNT(*) FROM ?#PRODUCTS_TABLE';
			$gridEntry->query_select_rows = 'SELECT *, '.LanguagesManager::sql_prepareField('name').' AS name FROM ?#PRODUCTS_TABLE';
			
			$gridEntry->default_sort_direction = 'DESC';
			$gridEntry->rows_num = 50;
			
			$gridEntry->registerHeader(translate("prdset_product_name"), 'name', false, 'asc','left');
			$gridEntry->registerHeader(translate("str_in_stock"), 'in_stock', false, 'asc','right');
			$gridEntry->registerHeader(translate("rep_views_count"), 'viewed_times', true, 'desc','right');
			$gridEntry->registerHeader(translate("prdset_product_sold"), 'items_sold', false, 'desc', 'right');
			$gridEntry->registerHeader(translate("rep_add2cart_count"), 'add2cart_counter', false, 'desc', 'right');
			$gridEntry->registerHeader(translate("prdset_product_rating"), 'customers_rating', false, 'desc','right');
			$gridEntry->registerHeader(translate("prdset_product_votes"), 'customer_votes', false, 'desc','right');
			
			//$gridEntry->setRowHandler('if(isset($row["in_stock"])&&$row["in_stock"]<0)$row["in_stock"] = "0 *";return $row;');
			
			$gridEntry->prepare();
						
			$smarty->assign(array(
				"admin_sub_dpt" => "reports_product_report.tpl.html",
				'stat' => array(
					'products_num' => $this->__getProductsNum(),
					'categories_num' =>  $this->__getCategoriesNum(),
					'invisible_products_num' => $this->__getInvisibleProductsNum(),
					'notinstock_products_num' => $this->__getNotInStockProductsNum()
					)
			));
		}
	}
	
	ActionsController::exec('ProductsReportController');
?>