<?php /* Smarty version 2.6.26, created on 2011-09-01 08:46:47
         compiled from backend/reports_product_report.tpl.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'translate', 'backend/reports_product_report.tpl.html', 2, false),array('modifier', 'replace', 'backend/reports_product_report.tpl.html', 4, false),array('modifier', 'set_query_html', 'backend/reports_product_report.tpl.html', 16, false),array('modifier', 'escape', 'backend/reports_product_report.tpl.html', 18, false),array('function', 'cycle', 'backend/reports_product_report.tpl.html', 17, false),)), $this); ?>
<h1><?php echo ((is_array($_tmp=$this->_tpl_vars['CurrentDivision']['name'])) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</h1>

<div><?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp='prep_total_products')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)))) ? $this->_run_mod_handler('replace', true, $_tmp, '[products]', $this->_tpl_vars['stat']['products_num']) : smarty_modifier_replace($_tmp, '[products]', $this->_tpl_vars['stat']['products_num'])))) ? $this->_run_mod_handler('replace', true, $_tmp, '[categories]', $this->_tpl_vars['stat']['categories_num']) : smarty_modifier_replace($_tmp, '[categories]', $this->_tpl_vars['stat']['categories_num'])); ?>
</div>
<?php if ($this->_tpl_vars['stat']['invisible_products_num'] > 0): ?><div><?php echo ((is_array($_tmp=((is_array($_tmp='prep_invisible_products')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)))) ? $this->_run_mod_handler('replace', true, $_tmp, '[products]', $this->_tpl_vars['stat']['invisible_products_num']) : smarty_modifier_replace($_tmp, '[products]', $this->_tpl_vars['stat']['invisible_products_num'])); ?>
</div><?php endif; ?>
<?php if (@CONF_CHECKSTOCK && $this->_tpl_vars['stat']['notinstock_products_num'] > 0): ?><div><?php echo ((is_array($_tmp=((is_array($_tmp='prep_notinstock_products')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)))) ? $this->_run_mod_handler('replace', true, $_tmp, '[products]', $this->_tpl_vars['stat']['notinstock_products_num']) : smarty_modifier_replace($_tmp, '[products]', $this->_tpl_vars['stat']['notinstock_products_num'])); ?>
</div><?php endif; ?>

<table width="100%" border="0" cellpadding="0" cellspacing="0" class="grid">
	<tr> 
		<td colspan="7" style="padding-bottom:8px;">&nbsp;<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "backend/lister.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></td>
	</tr>
	<tr class="gridsheader">
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "backend/gridheader.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	</tr>
<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['GridRows']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>
	<?php $this->assign('product_url', ((is_array($_tmp="?ukey=product_settings&productID=".($this->_tpl_vars['GridRows'][$this->_sections['i']['index']]['productID']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp))); ?>
	<tr class="<?php echo smarty_function_cycle(array('values' => 'gridline1,gridline'), $this);
 if ($this->_tpl_vars['GridRows'][$this->_sections['i']['index']]['in_stock'] <= 0 && @CONF_CHECKSTOCK): ?> gridline_outofstock<?php endif; ?>">
		<td width="40%"><a href="<?php echo $this->_tpl_vars['product_url']; ?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['GridRows'][$this->_sections['i']['index']]['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
&nbsp;<?php if ($this->_tpl_vars['GridRows'][$this->_sections['i']['index']]['enabled'] != 1): ?><span class="notice"><?php echo '(не представлен в пользовательской части)'; ?>
</span><?php endif; ?></a></td>
		<td width="10%" align="right"><a href="<?php echo $this->_tpl_vars['product_url']; ?>
"><?php echo $this->_tpl_vars['GridRows'][$this->_sections['i']['index']]['in_stock']; 
 if ($this->_tpl_vars['GridRows'][$this->_sections['i']['index']]['in_stock'] <= 0 && @CONF_CHECKSTOCK): ?>*<?php endif; ?>&nbsp;</a></td>
		<td width="10%" align="right"><a href="<?php echo $this->_tpl_vars['product_url']; ?>
"><?php echo $this->_tpl_vars['GridRows'][$this->_sections['i']['index']]['viewed_times']; ?>
&nbsp;</a></td>
		<td width="10%" align="right"><a href="<?php echo $this->_tpl_vars['product_url']; ?>
"><?php echo $this->_tpl_vars['GridRows'][$this->_sections['i']['index']]['items_sold']; ?>
&nbsp;<?php if (! $this->_tpl_vars['GridRows'][$this->_sections['i']['index']]['ordering_available']): ?><div class="notice"><?php echo 'продукт сейчас нельзя заказать'; ?>
</div><?php endif; ?></a></td>
		<td width="10%" align="right"><a href="<?php echo $this->_tpl_vars['product_url']; ?>
"><?php echo $this->_tpl_vars['GridRows'][$this->_sections['i']['index']]['add2cart_counter']; ?>
&nbsp;</a></td>
		<td width="10%" align="right"><a href="<?php echo $this->_tpl_vars['product_url']; ?>
"><?php echo $this->_tpl_vars['GridRows'][$this->_sections['i']['index']]['customers_rating']; ?>
&nbsp;</a></td>
		<td width="10%" align="right" class="endgrid"><a href="<?php echo $this->_tpl_vars['product_url']; ?>
"><?php echo $this->_tpl_vars['GridRows'][$this->_sections['i']['index']]['customer_votes']; ?>
&nbsp;</a></td>
	</tr>	
<?php endfor; endif; ?>
	<tr class="gridsfooter"> 
		<td colspan="7">&nbsp;<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "backend/lister.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></td>
	</tr>
</table>

<?php if (@CONF_CHECKSTOCK): ?>
<p class="field_descr gridline_outofstock">* <?php echo 'Нет на складе'; ?>
</p>
<?php endif; ?>