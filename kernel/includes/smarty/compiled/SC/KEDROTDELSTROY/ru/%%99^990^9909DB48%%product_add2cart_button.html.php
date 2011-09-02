<?php /* Smarty version 2.6.26, created on 2011-08-31 14:55:32
         compiled from product_add2cart_button.html */ ?>
<?php if ($this->_tpl_vars['__cpt_local_settings']['product_extra']): 
 $this->assign('product_extra', $this->_tpl_vars['__cpt_local_settings']['product_extra']); 
 endif; ?>

<?php if (! $this->_tpl_vars['CPT_CONSTRUCTOR_MODE']): 
 if (! $this->_tpl_vars['printable_version']): ?>
	<?php if ($this->_tpl_vars['product_info']['ordering_available'] && $this->_tpl_vars['product_info']['Price'] > 0 && ( @CONF_SHOW_ADD2CART == 1 ) && ( @CONF_CHECKSTOCK == 0 || $this->_tpl_vars['product_info']['in_stock'] > 0 )): ?>
	<?php if ($this->_tpl_vars['__cpt_local_settings']['request_product_count'] && ! $this->_tpl_vars['widget']): ?>
		<?php echo 'Кол-во'; ?>
:&nbsp;<input name="product_qty" class="product_qty" type="text" size="3" default_value="<?php echo $this->_tpl_vars['product_info']['min_order_amount']; ?>
" value="<?php echo $this->_tpl_vars['product_info']['min_order_amount']; ?>
">&nbsp;
	<?php endif; ?>
	<input name='add2cart' <?php if (( $this->_tpl_vars['PAGE_VIEW'] == 'facebook' ) || ( $this->_tpl_vars['PAGE_VIEW'] == 'vkontakte' )): ?>type="submit" value="<?php echo 'добавить в корзину'; ?>
" <?php else: ?>type="image" src="<?php if ($this->_tpl_vars['__cpt_local_settings']['view'] == 'small'): 
 echo $this->_tpl_vars['button_add2cart_small']; 
 else: 
 echo $this->_tpl_vars['button_add2cart_big']; 
 endif; ?>" alt="<?php echo 'добавить в корзину'; ?>
" <?php endif; ?> title="<?php echo 'добавить в корзину'; ?>
"
		<?php if (@CONF_SHOPPING_CART_VIEW != @SHCART_VIEW_PAGE || $this->_tpl_vars['widget']): ?>
		class="add2cart_handler" rel="<?php if ($this->_tpl_vars['widget']): ?>widget<?php endif; ?>" <?php endif; ?> >
	<?php elseif (@CONF_SHOW_ADD2CART == 1 && @CONF_CHECKSTOCK && ! $this->_tpl_vars['product_info']['in_stock'] && $this->_tpl_vars['product_info']['ordering_available']): ?>
		<div class="prd_out_of_stock"><?php echo 'Нет на складе'; ?>
</div>
	<?php endif; ?>
	
<?php endif; 
 else: ?>
	<?php if ($this->_tpl_vars['__cpt_local_settings']['request_product_count'] && ! $this->_tpl_vars['widget']): ?>
		<?php echo 'Кол-во'; ?>
:&nbsp;<input name="product_qty" type="text" size="3" value="<?php echo 'Кол-во'; ?>
">&nbsp;
	<?php endif; ?>
	<img border="0" src="<?php echo $this->_tpl_vars['button_add2cart_big']; ?>
" alt="<?php echo 'добавить в корзину'; ?>
" title="<?php echo 'добавить в корзину'; ?>
"/>
<?php endif; ?>