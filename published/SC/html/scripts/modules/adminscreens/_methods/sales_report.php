<?php
/**
 * @package Modules
 * @subpackage AdministratorScreens
 */
	class SalesReportController extends ActionsController {
		
		/**
		 * @return xmlNodeX
		 */
		function &_initSettingsXML(){
			
			$defaultCurrency = Currency::getDefaultCurrencyInstance();

			$xml = new xmlNodeX();
			$xml->renderTreeFromFile(DIR_CHARTS.'/settings/period_sales_report.xml');
			
			$r_xmlPrecision = $xml->xPath('/settings/precision');
			if(count($r_xmlPrecision)){
				$xmlPrecision = &$r_xmlPrecision[0];
			}else{
				$xmlPrecision = &$xml->child('precision');
			}
			$xmlPrecision->setData($defaultCurrency->decimal_places);

			$r_xmlColumn = $xml->xPath('/settings/column');
			if(count($r_xmlColumn)){
				$xmlColumn = &$r_xmlColumn[0];
			}else{
				$xmlColumn = &$xml->child('column');
			}

			$r_xmlBalloonText = $xmlColumn->xPath('/column/balloon_text');
			if(count($r_xmlBalloonText)){
				$xmlBalloonText = &$r_xmlBalloonText[0];
			}else{
				$xmlBalloonText = &$xmlColumn->child('balloon_text');
			}
			
			$xmlBalloonText->setData('{series} : '.$defaultCurrency->display_template);
			
			$xmlDecimalsSeparator = &$xml->getFirstChildByName('decimals_separator');
			if(!is_object($xmlDecimalsSeparator))
				$xmlDecimalsSeparator = &$xml->child('decimals_separator');
			$xmlDecimalsSeparator->setData($defaultCurrency->decimal_symbol);	
			
			$xmlThousandsSeparator = &$xml->getFirstChildByName('thousands_separator');
			if(!is_object($xmlThousandsSeparator))
				$xmlThousandsSeparator = &$xml->child('thousands_separator');
			$xmlThousandsSeparator->setData($defaultCurrency->thousands_delimiter=='_'?' ':$defaultCurrency->thousands_delimiter);	
			
			return $xml;
		}
		
		function get_period_sales_report_week(){

			$Register = &Register::getInstance();
			$DBHandler = &$Register->get(VAR_DBHANDLER);
			/* @var $DBHandler DataBase */
			
			$defaultCurrency = Currency::getDefaultCurrencyInstance();
			$max_sum = 0;
			
			/**
			 * This week
			 */
			//$period_begin = date('Y-m-d 00:00:00', strtotime('-'.(date('w')-1).' day'));
			//$period_end = date('Y-m-d 23:59:59');
			$delta = (defined('CONF_FIRST_WEEKDAY')&&(constant('CONF_FIRST_WEEKDAY')==6))?0:-1;
			$period_begin = $this->__getPeriodBegin('-'.(date('w')+$delta).' day');
			$period_end = $this->__getPeriodEnd(date('Y-m-d 23:59:59'));
			$offset_sql = $this->__getOffset();
			
			$dbq = '
				SELECT WEEKDAY(order_time'.$offset_sql.'), SUM(order_amount) FROM ?#ORDERS_TABLE 
				WHERE statusID="'.xEscapeSQLstring(CONF_ORDSTATUS_DELIVERED).'" AND order_time>="'.$period_begin.'" AND order_time<="'.$period_end.'"
				GROUP BY WEEKDAY(order_time'.$offset_sql.')
			';
			
			$DBRes = $DBHandler->ph_query($dbq);
			
			$this_week = array();
			while ($row = $DBRes->fetchRow()) {
				if($max_sum<$row[1])$max_sum = $row[1];
				$this_week[$row[0]] = $row[1];
			}
			
			/**
			 * Last week
			 */
			//$period_begin = $this->__getPeriodBegin('-13 day');
			//$period_end = $this->__getPeriodEnd('-1 week');
			
			//$period_begin = '-'.(int)((int)date('w')-8).' day';
			$period_begin = '-'.(date('w')+7+$delta).' day';
			$period_end = '-'.(date('w')+1+$delta).' day';
			
			$period_begin = $this->__getPeriodBegin($period_begin);
			$period_end = $this->__getPeriodEnd($period_end);
				
			$dbq = '
				SELECT WEEKDAY(order_time'.$offset_sql.'), SUM(order_amount) FROM ?#ORDERS_TABLE 
				WHERE statusID="'.xEscapeSQLstring(CONF_ORDSTATUS_DELIVERED).'" AND order_time>="'.$period_begin.'" AND order_time<="'.$period_end.'"
				GROUP BY WEEKDAY(order_time'.$offset_sql.')
			';
			
			$DBRes = $DBHandler->ph_query($dbq);
			
			$last_week = array();
			while ($row = $DBRes->fetchRow()) {
				if($max_sum<$row[1])$max_sum = $row[1];
				$last_week[$row[0]] = $row[1];
			}

			$no_data = !count($this_week) && !count($last_week);
			
			$xml = new xmlNodeX('chart');
			if(!$no_data){
				$xmlSeries = &$xml->child('series');
				$xmlGraphs = &$xml->child('graphs');
				$xmlLastWeek = &$xmlGraphs->child('graph', array('gid' => 0));
				$xmlThisWeek = &$xmlGraphs->child('graph', array('gid' => 1));
				global $rWeekDays;
				
				for ($w=0; $w<=6; $w++){
	
					$_w = $w+(defined('CONF_FIRST_WEEKDAY')?CONF_FIRST_WEEKDAY:0);
					if($_w>6)$_w-=7;
					$last_amount = isset($last_week[$_w])?$last_week[$_w]:0;
					$this_amount = isset($this_week[$_w])?$this_week[$_w]:0;
					$xmlSeries->child('value', array('xid' => $_w), $rWeekDays[$_w]);
					$xmlLastWeek->child('value', array('xid' => $_w), $defaultCurrency->round($last_amount));
					$xmlThisWeek->child('value', array('xid' => $_w), $defaultCurrency->round($this_amount));
				}
			}
				
			header('Content-Type: text/xml');
			print $xml->getNodeXML(-1, true);
			
			exit(1);
		}
		
		function settings_period_sales_report_week(){
			
			$xml = &$this->_initSettingsXML();
				
			$xmlGraphs = &$xml->getFirstChildByName('graphs');
			if(!is_object($xmlGraphs))
				$xmlGraphs = &$xml->child('graphs');
				
			$r_xmlGraph = $xmlGraphs->getChildrenByName('graph');
			for ($i=count($r_xmlGraph)-1; $i>=0; $i--){
				
				$xmlGraph = &$r_xmlGraph[$i];
				/*@var $xmlGraph xmlNodeX*/
				$xmlTitle = &$xmlGraph->getFirstChildByName('title');
				$xmlTitle->setData(translate($xmlGraph->attribute('gid')?'srep_this_week':'srep_last_week'));
			}
				
			$r_xmlNoData = $xml->xPath('/settings/strings/no_data');
			if(count($r_xmlNoData))$r_xmlNoData[0]->setData(translate('msg_no_data'));
			
			header('Content-type: text/xml');
			print $xml->getNodeXML(-1, true);
			exit(1);
		}
		
		function get_period_sales_report_month(){

			$Register = &Register::getInstance();
			$DBHandler = &$Register->get(VAR_DBHANDLER);
			/* @var $DBHandler DataBase */
			
			$defaultCurrency = Currency::getDefaultCurrencyInstance();
			$max_day = 0;
			
			/**
			 * This month
			 */
			$period_begin = $this->__getPeriodBegin(date('Y-m-01 00:00:00'));
			$period_end = $this->__getPeriodEnd(date('Y-m-d 23:59:59'));
			
			$offset_sql = $this->__getOffset();
			
			
			
			$max_day = getMonthDays(time());
			$dbq = '
				SELECT DAY(order_time'.$offset_sql.'), SUM(order_amount) FROM ?#ORDERS_TABLE 
				WHERE statusID="'.xEscapeSQLstring(CONF_ORDSTATUS_DELIVERED).'" AND order_time>="'.$period_begin.'" AND order_time<="'.$period_end.'"
				GROUP BY DAY(order_time'.$offset_sql.')
			';

			$DBRes = $DBHandler->ph_query($dbq);
			
			$this_month = array();
			while ($row = $DBRes->fetchRow()) {
				$this_month[$row[0]] = $row[1];
			}

			/**
			 * Last month
			 */
			
			$period_end = date('Y-m-d 23:59:59', strtotime('-1 day', strtotime(date('Y-m-01'))));
			$period_begin = date('Y-m-01 00:00:00', strtotime($period_end));
			
			$period_begin = $this->__getPeriodBegin($period_begin);
			$period_end = $this->__getPeriodEnd($period_end);
			$max_day_ = date('d', strtotime('-1 day',$period_end));
			if($max_day<$max_day_){
				$max_day = $max_day_;
			}
			
			$dbq = '
				SELECT DAY(order_time'.$offset_sql.'), SUM(order_amount) FROM ?#ORDERS_TABLE 
				WHERE statusID="'.xEscapeSQLstring(CONF_ORDSTATUS_DELIVERED).'" AND order_time>="'.$period_begin.'" AND order_time<="'.$period_end.'"
				GROUP BY DAY(order_time'.$offset_sql.')
			';
			$DBRes = $DBHandler->ph_query($dbq);
			
			$last_month = array();
			while ($row = $DBRes->fetchRow()) {
				$last_month[$row[0]] = $row[1];
			}

			$xml = new xmlNodeX('chart');
			if(count($last_month) || count($this_month)){
				
				$xmlSeries = &$xml->child('series');
				$xmlGraphs = &$xml->child('graphs');
				$xmlLastWeek = &$xmlGraphs->child('graph', array('gid' => 0));
				$xmlThisWeek = &$xmlGraphs->child('graph', array('gid' => 1));
	
				for ($d=1; $d<=$max_day; $d++){
	
					$last_amount = isset($last_month[$d])?$last_month[$d]:0;
					$this_amount = isset($this_month[$d])?$this_month[$d]:0;
					$xmlSeries->child('value', array('xid' => $d), $d);
					$xmlLastWeek->child('value', array('xid' => $d), $defaultCurrency->round($last_amount));
					$xmlThisWeek->child('value', array('xid' => $d), $defaultCurrency->round($this_amount));
				}
			}

			header('Content-Type: text/xml');
			print $xml->getNodeXML(-1, true);
			
			exit(1);
		}
		
		function settings_period_sales_report_month(){
			
			$xml = &$this->_initSettingsXML();
				
			$xmlGraphs = &$xml->getFirstChildByName('graphs');
			if(!is_object($xmlGraphs))
				$xmlGraphs = &$xml->child('graphs');
				
			$r_xmlGraph = $xmlGraphs->getChildrenByName('graph');
			for ($i=count($r_xmlGraph)-1; $i>=0; $i--){
				
				$xmlGraph = &$r_xmlGraph[$i];
				/*@var $xmlGraph xmlNodeX*/
				$xmlTitle = &$xmlGraph->getFirstChildByName('title');
				$xmlTitle->setData(translate($xmlGraph->attribute('gid')?'srep_this_month':'srep_last_month'));
			}
				
			$r_xmlNoData = $xml->xPath('/settings/strings/no_data');
			if(count($r_xmlNoData))$r_xmlNoData[0]->setData(translate('msg_no_data'));
			
			header('Content-type: text/xml');
			print $xml->getNodeXML(-1, true);
			exit(1);
		}
		
		function get_period_sales_report_year(){

			$Register = &Register::getInstance();
			$DBHandler = &$Register->get(VAR_DBHANDLER);
			/* @var $DBHandler DataBase */
			
			$defaultCurrency = Currency::getDefaultCurrencyInstance();
			$max_sum = 0;
			
			/**
			 * This year
			 */
			$period_begin = date('Y-01-01 00:00:00');
			$period_end = date('Y-12-31 23:59:59');
			
			$period_begin = $this->__getPeriodBegin($period_begin);
			$period_end = $this->__getPeriodEnd($period_end);
			$offset_sql = $this->__getOffset();
			
			$dbq = '
				SELECT MONTH(order_time'.$offset_sql.'), SUM(order_amount) FROM ?#ORDERS_TABLE 
				WHERE statusID="'.xEscapeSQLstring(CONF_ORDSTATUS_DELIVERED).'" AND order_time>="'.$period_begin.'" AND order_time<="'.$period_end.'"
				GROUP BY MONTH(order_time'.$offset_sql.')
			';
			
			$DBRes = $DBHandler->ph_query($dbq);
			
			$this_year = array();
			while ($row = $DBRes->fetchRow()) {
				$this_year[$row[0]] = $row[1];
			}
			
			/**
			 * Last year
			 */
			$ly = date('Y')-1;
			$period_begin = $ly.'-01-01 00:00:00';
			$period_end = $ly.'-12-31 23:59:59';
			
			$period_begin = $this->__getPeriodBegin($period_begin);
			$period_end = $this->__getPeriodEnd($period_end);
			
			$dbq = '
				SELECT MONTH(order_time), SUM(order_amount) FROM ?#ORDERS_TABLE 
				WHERE statusID="'.xEscapeSQLstring(CONF_ORDSTATUS_DELIVERED).'" AND order_time>="'.$period_begin.'" AND order_time<="'.$period_end.'"
				GROUP BY MONTH(order_time)
			';
			
			$DBRes = $DBHandler->ph_query($dbq);
			
			$last_year = array();
			while ($row = $DBRes->fetchRow()) {
				$last_year[$row[0]] = $row[1];
			}

			$xml = new xmlNodeX('chart');
			if(count($last_year) || count($this_year)){
				
				$xmlSeries = &$xml->child('series');
				$xmlGraphs = &$xml->child('graphs');
				$xmlLastWeek = &$xmlGraphs->child('graph', array('gid' => 0));
				$xmlThisWeek = &$xmlGraphs->child('graph', array('gid' => 1));
				global $rMonths;
				
				for ($w=1; $w<=12; $w++){
	
					$last_amount = isset($last_year[$w])?$last_year[$w]:0;
					$this_amount = isset($this_year[$w])?$this_year[$w]:0;
					$xmlSeries->child('value', array('xid' => $w), $rMonths[$w]);
					$xmlLastWeek->child('value', array('xid' => $w), $defaultCurrency->round($last_amount));
					$xmlThisWeek->child('value', array('xid' => $w), $defaultCurrency->round($this_amount));
				}
			}

			header('Content-Type: text/xml');
			print $xml->getNodeXML(-1, true);
			
			exit(1);
		}
		
		function settings_period_sales_report_year(){
			
			
			$xml = &$this->_initSettingsXML();
				
			$xmlGraphs = &$xml->getFirstChildByName('graphs');
			if(!is_object($xmlGraphs))
				$xmlGraphs = &$xml->child('graphs');
				
			$r_xmlGraph = $xmlGraphs->getChildrenByName('graph');
			for ($i=count($r_xmlGraph)-1; $i>=0; $i--){
				
				$xmlGraph = &$r_xmlGraph[$i];
				/*@var $xmlGraph xmlNodeX*/
				$xmlTitle = &$xmlGraph->getFirstChildByName('title');
				$xmlTitle->setData(translate($xmlGraph->attribute('gid')?'srep_this_year':'srep_last_year'));
			}
			
			$r_Rotate = $xml->xPath('/settings/values/category/rotate');
			if(count($r_Rotate))
				$r_Rotate[0]->setData('true');
				
			$r_xmlNoData = $xml->xPath('/settings/strings/no_data');
			if(count($r_xmlNoData))$r_xmlNoData[0]->setData(translate('msg_no_data'));
				
			header('Content-type: text/xml');
			print $xml->getNodeXML(-1, true);
			exit(1);
		}
		
		function data_all_time(){
			
			$Register = &Register::getInstance();
			$DBHandler = &$Register->get(VAR_DBHANDLER);
			/* @var $DBHandler DataBase */
			$defaultCurrency = Currency::getDefaultCurrencyInstance();
			/* @var $defaultCurrency Currency*/
			$offset_sql = $this->__getOffset();
			$dbq = '
				SELECT YEAR(order_time), MONTH(order_time'.$offset_sql.'), SUM(order_amount) FROM ?#ORDERS_TABLE
				WHERE statusID='.intval(CONF_ORDSTATUS_DELIVERED).' GROUP BY MONTH(order_time'.$offset_sql.'),YEAR(order_time'.$offset_sql.')
			';
			$DBRes = $DBHandler->ph_query($dbq);
			
			$sales = array();
			$start_year = $curr_year = date('Y');
			while ($row = $DBRes->fetchRow()) {
				
				$sales[$row[0].':'.$row[1]] = $row[2];
				if($row[0]<$start_year)$start_year = $row[0];
			}
			$xmlChart = new xmlNodeX('chart');
			$xmlSeries = &$xmlChart->child('series');
			$xmlGraphs = &$xmlChart->child('graphs');
			$xmlGraph1 = &$xmlGraphs->child('graph', array('gid'=>1));
			
			$current_month = date('m');
			global $rMonths;
			
			for (;$start_year<=$curr_year; $start_year++){
				
				$xmlSeries->child('value', array('xid' => sprintf('%02d%02d', $start_year, 0)), $start_year);
				for ($m=1; $m<=12; $m++){
					
					if($start_year == $curr_year && $m>$current_month)break;
					$xmlSeries->child('value', array('xid' => sprintf('%02d%02d', $start_year, $m)), $rMonths[$m].' '.$start_year);
					$xmlGraph1->child('value', array('xid' => sprintf('%02d%02d', $start_year, $m)), $defaultCurrency->round(isset($sales[$start_year.':'.$m])?$sales[$start_year.':'.$m]:0));
				}
			}

			header('Content-type: text/xml');
			print $xmlChart->getNodeXML(-1);
			die;
		}
		
		function settings_all_time(){

			$defaultCurrency = Currency::getDefaultCurrencyInstance();
			
			$xmlSettings = new xmlNodeX();
			$xmlSettings->renderTreeFromFile(DIR_CHARTS.'/settings/all_time.xml');
			
			$xmlColumn = &$xmlSettings->getFirstChildByName('column');
			$xmlBalloonText = &$xmlColumn->getFirstChildByName('balloon_text');
			$xmlBalloonText->setData('{series}: '.$defaultCurrency->display_template);
			
			$xmlPrecision = &$xmlSettings->getFirstChildByName('precision');
			$xmlPrecision->setData($defaultCurrency->decimal_places);
			
			$xmlDecimalSeparator = &$xmlSettings->getFirstChildByName('decimals_separator');
			$xmlDecimalSeparator->setData($defaultCurrency->decimal_symbol);
			
			$xmlThousandsSeparator = &$xmlSettings->getFirstChildByName('thousands_separator');
			$xmlThousandsSeparator->setData($defaultCurrency->thousands_delimiter=='_'?' ':$defaultCurrency->thousands_delimiter);
			
			header('Content-type: text/xml');
			print $xmlSettings->getNodeXML(-1);
			die;
		}
		
		function data_this_month(){
			
			$Register = &Register::getInstance();
			$DBHandler = &$Register->get(VAR_DBHANDLER);
			/* @var $DBHandler DataBase */
			$defaultCurrency = Currency::getDefaultCurrencyInstance();
			$all_sales = array();
			$delivered_sales = array();
			
			$period_begin = date('Y-m-01 00:00:00', $this->getData('utime_begin'));
			$period_end = date('Y-m-d 23:59:59', $this->getData('utime_end'));
			
			$period_begin = $this->__getPeriodBegin($period_begin,true);
			$period_end = $this->__getPeriodEnd($period_end,true);
			
			$offset_sql = $this->__getOffset();
			$dbq = '
				SELECT SUM(order_amount) AS `sum`, DAY(order_time'.$offset_sql.') AS `day` FROM ?#ORDERS_TABLE 
				WHERE statusID<>"'.xEscapeSQLstring(ostGetCanceledStatusId()).'" AND `order_time`>="'.$period_begin.'" AND `order_time`<="'.$period_end.'"
				GROUP BY `day` ORDER BY `day` 
			';
			$DBRes = $DBHandler->ph_query($dbq);
			while ($row = $DBRes->fetchAssoc())
				$all_sales[intval($row['day'])] = $row['sum'];
				
			$dbq = '
				SELECT SUM(order_amount) AS `sum`, DAY(order_time'.$offset_sql.') AS `day` FROM ?#ORDERS_TABLE 
				WHERE statusID="'.xEscapeSQLstring(CONF_ORDSTATUS_DELIVERED).'" AND `order_time`>="'.$period_begin.'" AND `order_time`<="'.$period_end.'"
				GROUP BY `day` ORDER BY `day` 
			';
			
			$DBRes = $DBHandler->ph_query($dbq);
			while ($row = $DBRes->fetchAssoc())
				$delivered_sales[intval($row['day'])] = $row['sum'];


			$xnChart = new xmlNodeX('chart');
			$xnXaxis = &$xnChart->child('xaxis');
			$xnGraphs = &$xnChart->child('graphs');
			$no_data = !count($delivered_sales) && !count($all_sales);
			if(!$no_data){
				$xnGraph1 = &$xnGraphs->child('graph', array('gid' => '2'));
				$xnGraph2 = &$xnGraphs->child('graph', array('gid' => '1'));
			}
			
			for($i=1, $i_max=intval(date('d', $this->getData('utime_end'))); $i<=$i_max; $i++){
				$xnXaxis->child('value', array('xid' => $i), $i);
				if(!$no_data){
					$xnGraph1->child('value', array('xid' => $i), isset($all_sales[$i])?$defaultCurrency->round($all_sales[$i]):'0.00');
					$xnGraph2->child('value', array('xid' => $i), isset($delivered_sales[$i])?$defaultCurrency->round($delivered_sales[$i]):'0.00');
				}
			}
				
			header('Content-type: text/xml');
			print $xnChart->getNodeXML(-1);
			die;
		}
		
		function settings_this_month(){
			
			$xml = &$this->_initSettingsXML();
			$defaultCurrency = Currency::getDefaultCurrencyInstance();
			
			$xmlSettings = new xmlNodeX();
			$xmlSettings->renderTreeFromFile(DIR_CHARTS.'/settings/this_month.xml');
			
			$r_xmlBalloonText = $xmlSettings->xPath('/settings/graphs/graph/balloon_text');
			for($i=0,$i_max=count($r_xmlBalloonText); $i< $i_max; $i++)
				$r_xmlBalloonText[$i]->setData($defaultCurrency->display_template);

			$r_xmlGraph = $xmlSettings->xPath('/settings/graphs/graph');
			for($i=0,$i_max=count($r_xmlGraph); $i< $i_max; $i++){
				$xnTitle = &$r_xmlGraph[$i]->getFirstChildByName('title');
				$xnTitle->setData(translate($r_xmlGraph[$i]->attribute('gid')==2?'srep_allorders':'srep_delivered_orders'));
			}
				
			$xmlPrecision = &$xmlSettings->getFirstChildByName('precision');
			$xmlPrecision->setData($defaultCurrency->decimal_places);
			
			$xmlDecimalSeparator = &$xmlSettings->getFirstChildByName('decimals_separator');
			$xmlDecimalSeparator->setData($defaultCurrency->decimal_symbol);
			
			$xmlThousandsSeparator = &$xmlSettings->getFirstChildByName('thousands_separator');
			$xmlThousandsSeparator->setData($defaultCurrency->thousands_delimiter=='_'?' ':$defaultCurrency->thousands_delimiter);
			
			$r_xmlNoData = $xmlSettings->xPath('/settings/strings/no_data');
			if(count($r_xmlNoData))$r_xmlNoData[0]->setData(translate('msg_no_data'));
			header('Content-type: text/xml');
			print $xmlSettings->getNodeXML(-1);
			die;
		}
		
		function __getFirstOrderUTime(){
			
			$time = db_phquery_fetch(DBRFETCH_FIRST, 'SELECT MIN(order_time) FROM ?#ORDERS_TABLE');
			return $time?strtotime($time):time();
		}
		
		function main(){
			
			$Register = &Register::getInstance();
			$smarty = &$Register->get(VAR_SMARTY);
			/* @var $smarty Smarty */

			
			$Register = &Register::getInstance();
			$DBHandler = &$Register->get(VAR_DBHANDLER);
			/* @var $DBHandler DataBase */
			
			$defaultCurrency = Currency::getDefaultCurrencyInstance();
			/* @var $defaultCurrency Currency */

			$dbq = '
				SELECT count(*) as count, SUM(order_amount) FROM ?#ORDERS_TABLE 
				WHERE statusID="'.xEscapeSQLstring(CONF_ORDSTATUS_DELIVERED).'"';
			
			$DBRes = $DBHandler->ph_query($dbq);
			
			if($row = $DBRes->fetchRow()) {
				
				$smarty->assign('srep_statistic', str_replace(array('{N}','{M}'),array($row[0],$defaultCurrency->getView($row[1])),translate('srep_alltime_info')));
			}
			
			$periods = array(
				'week' => 'srep_this_p_week',
				'month' => 'srep_this_p_month',
				'year' => 'srep_this_p_year',
			);
			$order_months = array();
			$utime_first_order = $this->__getFirstOrderUTime();
			$utime_first_order = mktime(0,0,0, date('m', $utime_first_order), 1, date('Y', $utime_first_order));			
			$utime_current = time();
			global $rMonths;
			do{

				$order_months[$rMonths[intval(date('m', $utime_first_order))].' '.date('Y', $utime_first_order)] = array(
						mktime(0,0,0, date('m', $utime_first_order), 1, date('Y', $utime_first_order))
				,
					mktime(23,59,59, date('m', $utime_first_order), getMonthDays($utime_first_order), date('Y', $utime_first_order))
					);
				$utime_first_order = strtotime('+1 month', $utime_first_order);
			}while($utime_first_order<=$utime_current);
			$order_months = array_reverse($order_months);
			$smarty->assign('order_months', $order_months);
			$smarty->assign('periods', $periods);
			
			$product_period_begin = strtotime('-'.(date('w')-1).' day');
			$product_period_end = time();
			
			$smarty->assign('product_period_begin', Time::standartTime($product_period_begin));
			$smarty->assign('product_period_end', Time::standartTime($product_period_end));
			$smarty->assign('no_orders', ordGetOrdersNum()<=0);
			
			$smarty->assign('admin_sub_dpt', 'sales_report.html');
		}
		
		function __getPeriodBegin($period_begin,$is_user_time = false){
			//$period_begin = '-6 day';
			//print "\nBEGIN\n{$period_begin}\n";
			if(!$is_user_time){
			$period_begin = strtotime($period_begin);
			//print "{$period_begin}\n";
			$period_begin = Time::StandartTime($period_begin,false).' 00:00:00';
			//print "{$period_begin}\n";
			}
			$period_begin = Time::timeStamp($period_begin);
			//print "{$period_begin}\n";
			$period_begin = Time::timeToServerTime($period_begin);
			//print "{$period_begin}\n";
			$period_begin = Time::dateTime($period_begin);
			//print "{$period_begin}\n";
			return $period_begin;
		}
		
		function __getPeriodEnd($period_end,$is_user_time = false){
			//$period_end = 'now';
			//print "\nEND\n{$period_end}\n";
			if(!$is_user_time){
			$period_end = strtotime($period_end);
			//print "{$period_end}\n";
			$period_end = Time::StandartTime($period_end,false).' 23:59:59';
			//print "{$period_end}\n";
			}
			$period_end = Time::timeStamp($period_end);
			//print "{$period_end}\n";
			$period_end = Time::timeToServerTime($period_end);
			//print "{$period_end}\n";
			$period_end = Time::dateTime($period_end);
			//print "{$period_end}\n";
			return $period_end;
		}
		
		function __getOffset()
		{
			$offset = Time::timeOffset();
			if($offset!=0){
				$offset_sql = sprintf(' %s INTERVAL %d SECOND',(($offset>0)?'+':'-'),abs($offset));
			}else{
				$offset_sql = '';
			}
			return $offset_sql;
		}
		
	}
	
	ActionsController::exec('SalesReportController');
?>