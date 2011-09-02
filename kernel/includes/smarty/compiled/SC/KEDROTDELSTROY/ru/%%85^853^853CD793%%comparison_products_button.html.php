<?php /* Smarty version 2.6.26, created on 2011-08-31 14:46:34
         compiled from comparison_products_button.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'set_query', 'comparison_products_button.html', 4, false),)), $this); ?>
<?php if (( $this->_tpl_vars['PAGE_VIEW'] != 'vkontakte' ) && ( $this->_tpl_vars['PAGE_VIEW'] != 'facebook' )): ?>
<?php if ($this->_tpl_vars['show_comparison'] > 0 && $this->_tpl_vars['products_to_show']): ?>
<form action='<?php echo ((is_array($_tmp="?categoryID=&category_slug=&ukey=product_comparison")) ? $this->_run_mod_handler('set_query', true, $_tmp) : smarty_modifier_set_query($_tmp)); ?>
' method="post">
	<input type="hidden" value='' class="comparison_products" name='comparison_products' >
	<input value='<?php echo 'Сравнить выбранные продукты'; ?>
' class="hndl_submit_prds_cmp" onclick='submitProductsComparison();' type="button" >
</form>
<?php endif; ?>
<?php endif; ?>