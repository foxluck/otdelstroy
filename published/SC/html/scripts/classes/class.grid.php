<?php
	class Grid extends Object {
		
		var $query_total_rows_num;
		var $query_select_rows;
		var $rows_num = 20;
		var $total_rows_num;
		
		var $headers = array();
		var $sort_fields = array();
		var $default_sort_field = '';
		var $default_sort_direction = '';
		var $show_rows_num_select = true;
		
		var $get_sort_name = 'sort';
		var $get_direction_name = 'direction';
		
		var $smarty_headers_name = 'GridHeaders';
		
		var $__debug = false;
		var $__row_handler = '';
		
		function setRowHandler($func_code){
			
			$this->__row_handler = $func_code;
		}
		
		function registerHeader($title, $field_name = '', $default_sort = false, $default_sort_direction = 'asc', $align = '', $add_str = ''){
			
			$Register = &Register::getInstance();
			$GetVars = &$Register->get(VAR_GET);
			
			$this->headers[] = array(
				'header_name' => translate($title),
				'ascsort' => $field_name?array('getvars'=>'&'.$this->get_sort_name.'='.$field_name.'&'.$this->get_direction_name.'=ASC'):'',
				'descsort' => $field_name?array('getvars'=>'&'.$this->get_sort_name.'='.$field_name.'&'.$this->get_direction_name.'=DESC'):'',
			    'add_str' => $add_str
				);
			$this->sort_fields[$field_name] = 1;
				
			$j = count($this->headers)-1;
			if(isset($GetVars[$this->get_sort_name])&&isset($GetVars[$this->get_direction_name])&&$this->headers[$j]['ascsort']['getvars'] == '&'.$this->get_sort_name.'='.$GetVars[$this->get_sort_name].'&'.$this->get_direction_name.'='.$GetVars[$this->get_direction_name] )
				$this->headers[$j]['ascsort']['enabled'] = 1;
			elseif(isset($GetVars[$this->get_sort_name])&&isset($GetVars[$this->get_direction_name])&&$this->headers[$j]['descsort']['getvars'] == '&'.$this->get_sort_name.'='.$GetVars[$this->get_sort_name].'&'.$this->get_direction_name.'='.$GetVars[$this->get_direction_name] )
				$this->headers[$j]['descsort']['enabled'] = 1;
			if(!isset($GetVars[$this->get_sort_name]) && $default_sort)$this->headers[$j][strtolower($this->default_sort_direction).'sort']['enabled'] = 1;

			$this->headers[$j]['defsort'] = $this->headers[$j][$default_sort_direction.'sort'];
			if($default_sort)$this->default_sort_field = $field_name;
			if($default_sort && $default_sort_direction)$this->default_sort_direction = $default_sort_direction;
			
			if($align){
				$this->headers[$j]['align'] = $align;
			}
		}
		
		function prepare(){
			
			$Register = &Register::getInstance();
			$smarty = &$Register->get(VAR_SMARTY);
			/* @var $smarty Smarty */
			$GetVars = &$Register->get(VAR_GET);
			$DBHandler = &$Register->get(VAR_DBHANDLER);
			/* @var $DBHandler DataBase */
			$PostVars = &$Register->get(VAR_POST);
			
			if(isset($PostVars['rows_num']))renderURL('rows_num='.$PostVars['rows_num'], '', true);
			
			$DBRes = $DBHandler->ph_query($this->query_total_rows_num);
			if($this->__debug){
				print '<br />'.$DBHandler->last_sql;
			}
			
			$this->total_rows_num = $DBRes->fetchRow(0);
			$rows_show_all = isset($GetVars['rows_num']) && $GetVars['rows_num'] == 'show_all';
			
			$this->rows_num = isset($GetVars['rows_num']) && intval($GetVars['rows_num'])>0?intval($GetVars['rows_num']):$this->rows_num;
			$total_pages = ceil($this->total_rows_num/$this->rows_num);
			
			$page = isset($GetVars['page'])&&intval($GetVars['page'])>0?intval($GetVars['page']):1;
			if($page > $total_pages)$page = $total_pages;
			if($page < 1)$page = 1;
			
			$sort = isset($GetVars[$this->get_sort_name])&&isset($this->sort_fields[$GetVars[$this->get_sort_name]])?$GetVars[$this->get_sort_name]:$this->default_sort_field;
			$direction = isset($GetVars[$this->get_direction_name]) && in_array($GetVars[$this->get_direction_name], array('ASC', 'DESC'))?$GetVars[$this->get_direction_name]:$this->default_sort_direction;
			
			$DBRes = $DBHandler->ph_query($this->query_select_rows.($sort?' ORDER BY '.$sort.($direction?' '.$direction:''):'').($rows_show_all?'':(' LIMIT '.(($page-1)*$this->rows_num).', '.$this->rows_num)));
			if($this->__debug){
				print '<br />'.$DBHandler->last_sql;
			}
			
			if($this->__row_handler)$func = create_function('$row', $this->__row_handler);
			
			$GridRows = array();
			while($_row = $DBRes->fetchAssoc()){
				
				if(isset($func))$_row = call_user_func($func, $_row);
				$GridRows[] = $_row;
			}
			
			if($this->show_rows_num_select)$smarty->assign('rows_show_all', $rows_show_all);
			if($this->show_rows_num_select)$smarty->assign('rows', array(10 => 10, 20 => 20, 50=>50, 100=>100));
			$smarty->assign('rows_num', $this->rows_num);
			$smarty->assign('Lister', $rows_show_all?'':getLister($page, $total_pages, $this->rows_num));
			$smarty->assign('GridRows', $GridRows);
			$smarty->assign('total_rows_num', $this->total_rows_num);
			$this->prepare_headers();
			$smarty->assign('GridHeadersNum', count($this->headers));
		}
		
		function prepare_headers(){
			
			$Register = &Register::getInstance();
			$smarty = &$Register->get(VAR_SMARTY);
			/* @var $smarty Smarty */
			$smarty->assign($this->smarty_headers_name, $this->headers);
			$smarty->assign('GridHeadersNum', count($this->headers));
		}
		
		function exportRows()
		{
			$Register = &Register::getInstance();
			$GetVars = &$Register->get(VAR_GET);
			$DBHandler = &$Register->get(VAR_DBHANDLER);
			/* @var $DBHandler DataBase */
			$PostVars = &$Register->get(VAR_POST);
			
			$sort = isset($GetVars[$this->get_sort_name])&&isset($this->sort_fields[$GetVars[$this->get_sort_name]])?$GetVars[$this->get_sort_name]:$this->default_sort_field;
			$direction = isset($GetVars[$this->get_direction_name]) && in_array($GetVars[$this->get_direction_name], array('ASC', 'DESC'))?$GetVars[$this->get_direction_name]:$this->default_sort_direction;
			
			$DBRes = $DBHandler->ph_query($this->query_select_rows.($sort?' ORDER BY '.$sort.($direction?' '.$direction:''):''));
			if($this->__debug){
				print '<br />'.$DBHandler->last_sql;
			}
			
			if($this->__row_handler)$func = create_function('$row', $this->__row_handler);
			
			$GridRows = array();
			while($_row = $DBRes->fetchAssoc()){
				
				if(isset($func))$_row = call_user_func($func, $_row);
				$GridRows[] = $_row;
			}
			return $GridRows;
		}
	}
?>