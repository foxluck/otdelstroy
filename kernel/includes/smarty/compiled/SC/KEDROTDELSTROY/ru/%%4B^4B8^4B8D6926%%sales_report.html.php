<?php /* Smarty version 2.6.26, created on 2011-09-01 08:17:17
         compiled from backend/sales_report.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'translate', 'backend/sales_report.html', 1, false),array('modifier', 'set_query_html', 'backend/sales_report.html', 21, false),array('modifier', 'escape', 'backend/sales_report.html', 21, false),array('modifier', 'set_query', 'backend/sales_report.html', 70, false),)), $this); ?>
<h1><?php echo ((is_array($_tmp=$this->_tpl_vars['CurrentDivision']['name'])) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</h1>

<script type="text/javascript" src="<?php echo @URL_JS; ?>
/swfobject.js"></script>
<script type="text/javascript">
translate.srep_this_p_week = "<?php echo 'Текущая и предыдущая недели'; ?>
";
translate.srep_this_p_month = "<?php echo 'Текущий и предыдущий месяцы'; ?>
";
translate.srep_this_p_year = "<?php echo 'Текущий и предыдущий годы'; ?>
";
</script>

<p><?php echo 'Ниже представлены графики, отражающие динамику продаж вашего интернет-магазина.<br />Отчет "Динамика продаж" позволяет сопоставить объем доставленных заказов ко всем совершенным заказам.<br />Отчеты "Сравнение" и "Продажи за весь период" показывают данные только о доставленных заказах.'; ?>
</p>

<?php if ($this->_tpl_vars['no_orders']): ?>
<div style="text-align: center"><?php echo 'Нет заказов'; ?>
</div>
<?php else: ?>
<h2><?php echo 'Динамика продаж'; ?>
</h2>

<div class="blockquote">
<?php echo 'Выборка данных за'; ?>
:
	<select id="order-months">
	<?php $_from = $this->_tpl_vars['order_months']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['fr_ordermonths'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fr_ordermonths']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['_title'] => $this->_tpl_vars['_period']):
        $this->_foreach['fr_ordermonths']['iteration']++;
?>
	<option value='<?php echo ((is_array($_tmp="?ukey=sales_report&action=data_this_month&utime_begin=".($this->_tpl_vars['_period'][0])."&utime_end=".($this->_tpl_vars['_period'][1]))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
'<?php if (($this->_foreach['fr_ordermonths']['iteration'] <= 1)): ?> selected="selected"<?php endif; ?>><?php echo ((is_array($_tmp=$this->_tpl_vars['_title'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</option>
	<?php endforeach; endif; unset($_from); ?>
	</select>
	<div id="this-month-container" style="height: 320px; width: 650px;margin-top:5px;">
			<strong>You need to upgrade your Flash Player</strong>
	</div>
</div>

<h2><?php echo 'Сравнить'; ?>
</h2>
<div class="blockquote">
<?php echo 'Выборка данных за'; ?>
:
	<select id="change-period-handler">
	<?php $_from = $this->_tpl_vars['periods']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['_key'] => $this->_tpl_vars['_period']):
?>
	<option value='<?php echo $this->_tpl_vars['_key']; ?>
' <?php if ($this->_tpl_vars['_key'] == 'month'): ?>selected="selected"<?php endif; ?>><?php echo ((is_array($_tmp=$this->_tpl_vars['_period'])) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</option>
	<?php endforeach; endif; unset($_from); ?>
	</select>
<div id="flashcontent" style="height: 320px; width: 650px;margin-top:5px;">
	<div id="content_week">
		<strong>You need to upgrade your Flash Player</strong>
	</div>
	<div id="content_month">
	</div>
	<div id="content_year">
	</div>
</div>
</div>

<br />
<br />
	
<h2><?php echo 'Продажи за весь период работы магазина'; ?>
</h2>
<?php if ($this->_tpl_vars['srep_statistic']): ?><p><?php echo $this->_tpl_vars['srep_statistic']; ?>
</p><?php endif; ?>
<div class="blockquote" id="all-time-container" style="height: 320px; width: 650px;">
		<strong>You need to upgrade your Flash Player</strong>
</div>

<script type="text/javascript">
		// <![CDATA[		
		var periods = ['week', 'month', 'year'];
		var r_swf = {};
		var current_period = 'month';
		
		for(var k=periods.length-1; k>=0; k-- ){

			if(periods[k] != 'month')
				getLayer("content_"+periods[k]).style.display = 'none';
				
			r_swf[periods[k]] = new SWFObject("<?php echo @URL_CHARTS; ?>
/amcolumn.swf", "amcolumn_"+periods[k], "100%", "100%", "8", "#FFFFFF");
			r_swf[periods[k]].addVariable("path", "<?php echo @URL_CHARTS; ?>
/");
			r_swf[periods[k]].addVariable("settings_file", escape("<?php echo ((is_array($_tmp='?ukey=sales_report&action=')) ? $this->_run_mod_handler('set_query', true, $_tmp) : smarty_modifier_set_query($_tmp)); ?>
&action=settings_period_sales_report_"+periods[k]));
			r_swf[periods[k]].addVariable("data_file", escape("<?php echo ((is_array($_tmp='?ukey=sales_report&action=')) ? $this->_run_mod_handler('set_query', true, $_tmp) : smarty_modifier_set_query($_tmp)); ?>
&action=get_period_sales_report_"+periods[k]));
			r_swf[periods[k]].addVariable("preloader_color", "#000000");
			r_swf[periods[k]].write("content_"+periods[k]);
		}		
		
		var so = new SWFObject("<?php echo @URL_CHARTS; ?>
/amcolumn.swf", "amcolumn_alltime", "100%", "100%", "8", "#FFFFFF");
		so.addVariable("path", "<?php echo @URL_CHARTS; ?>
/");
		so.addVariable("settings_file", escape("<?php echo ((is_array($_tmp='?ukey=sales_report&action=settings_all_time')) ? $this->_run_mod_handler('set_query', true, $_tmp) : smarty_modifier_set_query($_tmp)); ?>
"));
		so.addVariable("data_file", escape("<?php echo ((is_array($_tmp='?ukey=sales_report&action=data_all_time')) ? $this->_run_mod_handler('set_query', true, $_tmp) : smarty_modifier_set_query($_tmp)); ?>
"));
		so.addVariable("preloader_color", "#000000");
		so.write("all-time-container");
		
		getLayer('change-period-handler').onchange = function(){

			getLayer("content_"+current_period).style.display = "none";
			current_period = select_getCurrValue(this);
			getLayer("content_"+current_period).style.display = "";
		}

		getLayer('order-months').onchange = function(){
		
			var objSWF = getLayer('amline_thismonth');
			if(objSWF){
				var p = objSWF.parentNode;
				p.removeChild(objSWF);
			}
			
			var so = new SWFObject("<?php echo @URL_CHARTS; ?>
/amline.swf", "amline_thismonth", "100%", "100%", "8", "#FFFFFF");
			so.addVariable("path", "<?php echo @URL_CHARTS; ?>
/");
			so.addVariable("settings_file", escape("<?php echo ((is_array($_tmp='?ukey=sales_report&action=settings_this_month')) ? $this->_run_mod_handler('set_query', true, $_tmp) : smarty_modifier_set_query($_tmp)); ?>
"));
			so.addVariable("data_file", escape(select_getCurrValue(this)));
			so.addVariable("preloader_color", "#000000");
			so.write("this-month-container");
		}
		getLayer('order-months').onchange();
		// ]]>
	</script>
<?php endif; ?>