<?php /* Smarty version 2.6.26, created on 2011-08-31 14:46:24
         compiled from shopping_cart_info.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'set_query', 'shopping_cart_info.html', 5, false),array('modifier', 'replace', 'shopping_cart_info.html', 9, false),)), $this); ?>
<?php if (@CONF_SHOW_ADD2CART == 1): ?>
<?php if (@CONF_SHOPPING_CART_VIEW != @SHCART_VIEW_PAGE): ?>
<?php $this->assign('checkout_class', 'hndl_proceed_checkout'); ?>
<?php endif; ?>
<div class="shcart_link"><a class="<?php echo $this->_tpl_vars['checkout_class']; ?>
" rel="nofollow" href="<?php echo ((is_array($_tmp="?ukey=cart")) ? $this->_run_mod_handler('set_query', true, $_tmp) : smarty_modifier_set_query($_tmp)); ?>
"><?php echo 'Моя корзина'; ?>
</a></div>
	
<?php if ($this->_tpl_vars['shopping_cart_items']): ?>
<div id="shpcrtgc"><?php echo $this->_tpl_vars['shopping_cart_items']; ?>
 <?php echo 'продукт(ов): '; ?>
</div>
<div id="shpcrtca"><?php echo ((is_array($_tmp=$this->_tpl_vars['shopping_cart_value_shown'])) ? $this->_run_mod_handler('replace', true, $_tmp, '"', '&quot;') : smarty_modifier_replace($_tmp, '"', '&quot;')); ?>
</div>
<?php echo '
<script type="text/javascript">
<!--
var shopping_cart_info = getElementByClass(\'cpt_shopping_cart_info\', document, \'div\');
if(shopping_cart_info){shopping_cart_info.id=\'cart_not_empty\';}
//-->
</script>
'; ?>

<?php else: ?>
<div id="shpcrtgc"><?php echo '(пусто)'; ?>
</div>
<div id="shpcrtca">&nbsp;</div>
<?php endif; ?>
<?php endif; ?>