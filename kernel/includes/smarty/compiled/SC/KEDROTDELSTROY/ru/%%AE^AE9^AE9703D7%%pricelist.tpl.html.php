<?php /* Smarty version 2.6.26, created on 2011-08-31 17:16:04
         compiled from pricelist.tpl.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'pricelist.tpl.html', 20, false),array('modifier', 'set_query_html', 'pricelist.tpl.html', 25, false),)), $this); ?>
<?php echo '
<style type="text/css">
	td.sc-price-level0 {padding-left: 0px}
	td.sc-price-level1 {padding-left: 15px}
	td.sc-price-level2 {padding-left: 30px}
	td.sc-price-level3 {padding-left: 45px}
	td.sc-price-level4 {padding-left: 60px}
	td.sc-price-level5 {padding-left: 75px}
	td.sc-price-level6 {padding-left: 90px}
	td.sc-price-level7 {padding-left: 105px}
	td.sc-price-level8 {padding-left: 120px}
	td.sc-price-level9 {padding-left: 135px}
	td.sc-price-code {padding-left:3px;text-align:left}
	td.sc-price-count {padding-left:20px;text-align:center}
	td.sc-price-price {padding-left:20px;text-align:right}
</style>
'; ?>

<center>
	<h1><?php echo 'Прайс-лист'; ?>
 <?php echo ((is_array($_tmp=@CONF_SHOP_NAME)) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</h1>

	<?php if (! $this->_tpl_vars['printable_version'] && $this->_tpl_vars['PAGE_VIEW'] != 'mobile'): ?>

		<table border=0>
			  <tr><td style="width: 1%;"><a rel="nofollow" href="javascript:open_printable_version('<?php echo ((is_array($_tmp="view=printable")) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
');"><img src="<?php echo @URL_IMAGES; ?>
/printer-icon.gif" alt='<?php echo 'Версия для печати'; ?>
'></a></td>
			  <td><a rel="nofollow" href="javascript:open_printable_version('<?php echo ((is_array($_tmp="view=printable")) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
');"><?php echo 'Версия для печати'; ?>
</a></td>
			  </tr>
		 </table>

		<?php if ($this->_tpl_vars['string_product_sort']): ?>
			<p><?php echo $this->_tpl_vars['string_product_sort']; ?>
</p>
			<br>
			<br>
		<?php endif; ?>

	<?php endif; ?>
	<table border=0 cellspacing=0 cellpadding=3 style="width: 100%;">

		<?php $_from = $this->_tpl_vars['pricelist_elements']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['i'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['i']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['element']):
        $this->_foreach['i']['iteration']++;
?>
		<?php echo ''; 
 if ($this->_tpl_vars['element']['data']['is_category']): 
 echo '<tr class="background1">'; 
 if (@CONF_ENABLE_PRODUCT_SKU): 
 echo '<td>&nbsp;</td>'; 
 endif; 
 echo '<td colspan="3" class="sc-price-level'; 
 echo $this->_tpl_vars['element']['level']; 
 echo '">'; 
 if (! $this->_tpl_vars['printable_version']): 
 echo '<a href="'; 
 echo ((is_array($_tmp="?categoryID=".($this->_tpl_vars['element']['data']['id'])."&category_slug=".($this->_tpl_vars['element']['data']['slug']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); 
 echo '">'; 
 echo ((is_array($_tmp=$this->_tpl_vars['element']['data']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); 
 echo '</a>'; 
 else: 
 echo '<b>'; 
 echo ((is_array($_tmp=$this->_tpl_vars['element']['data']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); 
 echo '</b>'; 
 endif; 
 echo '</td></tr>'; 
 else: 
 echo '<tr>'; 
 if (@CONF_ENABLE_PRODUCT_SKU): 
 echo '<td class="sc-price-code" style="font-style: italic; white-space: nowrap">'; 
 echo $this->_tpl_vars['element']['data']['product_code']; 
 echo '</td>'; 
 endif; 
 echo '<td width=100% class="sc-price-level'; 
 echo $this->_tpl_vars['element']['level']; 
 echo '">'; 
 if (! $this->_tpl_vars['printable_version']): 
 echo '<a href="'; 
 echo ((is_array($_tmp="?ukey=product&productID=".($this->_tpl_vars['element']['data']['id'])."&product_slug=".($this->_tpl_vars['element']['data']['slug']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); 
 echo '">'; 
 echo ((is_array($_tmp=$this->_tpl_vars['element']['data']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); 
 echo '</a>'; 
 else: 
 echo '<b>'; 
 echo ((is_array($_tmp=$this->_tpl_vars['element']['data']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); 
 echo '</b>'; 
 endif; 
 echo '</td><td class="sc-price-count" style="white-space: nowrap;">'; 
 if (@CONF_CHECKSTOCK): 
 echo ''; 
 if ($this->_tpl_vars['element']['data']['in_stock'] > 0): 
 echo ''; 
 echo 'На складе'; 
 echo ''; 
 else: 
 echo ''; 
 echo 'Нет на складе'; 
 echo ''; 
 endif; 
 echo ''; 
 else: 
 echo '&nbsp;'; 
 endif; 
 echo '</td><td class="sc-price-price" style="font-weight: bold; white-space: nowrap">'; 
 echo $this->_tpl_vars['element']['data']['price']; 
 echo '</td></tr>'; 
 endif; 
 echo ''; ?>

		<?php endforeach; endif; unset($_from); ?>		
	</table>
</center>