<?php /* Smarty version 2.6.26, created on 2011-08-31 14:55:32
         compiled from product_info.frame.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'set_query_html', 'product_info.frame.html', 5, false),array('modifier', 'escape', 'product_info.frame.html', 7, false),array('modifier', 'set_query', 'product_info.frame.html', 20, false),array('function', 'component', 'product_info.frame.html', 16, false),)), $this); ?>
<script type="text/javascript" src="<?php echo @URL_JS; ?>
/product_detailed.js"></script>
<?php if ($this->_tpl_vars['widget']): ?>
	<?php $this->assign('_ext_url', "&view=noframe&external=1"); ?>
<?php endif; ?>
<form rel="<?php echo $this->_tpl_vars['product_info']['productID']; ?>
" action='<?php echo ((is_array($_tmp="")) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
' method="post">
<input name="productID" value="<?php echo $this->_tpl_vars['product_info']['productID']; ?>
" type="hidden" >
<input class="product_price" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['product_info']['price_incurr'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" type="hidden" >
<input class="product_list_price" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['product_info']['list_price_incurr'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" type="hidden" >

<?php if ($this->_tpl_vars['product_info'] != NULL || $this->_tpl_vars['CPT_CONSTRUCTOR_MODE']): ?>

<?php if ($this->_tpl_vars['PAGE_VIEW'] != 'printable'): ?>

<table cellpadding="0" cellspacing="0" width="100%">
	<tr>
		<td valign="top"><?php echo smarty_function_component(array('cpt_id' => 'product_category_info'), $this);?>
</td>
		<?php if ($this->_tpl_vars['PAGE_VIEW'] != 'mobile'): ?>
		<td align=right> 		 <table cellpadding="0" cellspacing="0" class="product_print_v">
		  <tr><td><a rel="nofollow" href="javascript:open_printable_version('<?php echo ((is_array($_tmp="view=printable")) ? $this->_run_mod_handler('set_query', true, $_tmp) : smarty_modifier_set_query($_tmp)); ?>
');"><img src="<?php echo @URL_IMAGES; ?>
/printer-icon.gif" alt="<?php echo 'Версия для печати'; ?>
" /></a></td>
		  <td><a rel="nofollow" href="javascript:open_printable_version('<?php echo ((is_array($_tmp="view=printable")) ? $this->_run_mod_handler('set_query', true, $_tmp) : smarty_modifier_set_query($_tmp)); ?>
');"><?php echo 'Версия для печати'; ?>
</a></td>
		  </tr>
		 </table>
		</td>
		<?php endif; ?>
	</tr>
</table>

<?php endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "product_info.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php endif; ?>

</form>