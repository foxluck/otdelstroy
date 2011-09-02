<?php /* Smarty version 2.6.26, created on 2011-08-31 14:55:32
         compiled from product_price.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'product_price.html', 23, false),)), $this); ?>
<?php if (! $this->_tpl_vars['CPT_CONSTRUCTOR_MODE']): ?>
	<?php if ($this->_tpl_vars['currencies_count'] != 0 && $this->_tpl_vars['product_info']['Price'] > 0): ?>
				<?php if ($this->_tpl_vars['product_info']['list_price'] > 0 && $this->_tpl_vars['product_info']['list_price'] > $this->_tpl_vars['product_info']['Price'] && $this->_tpl_vars['product_info']['Price'] > 0): ?> 
		<span class="regularPrice"><?php echo $this->_tpl_vars['product_info']['list_priceWithUnit']; ?>
</span> 
		<?php endif; ?>
	
		<span class="totalPrice"><?php echo $this->_tpl_vars['product_info']['PriceWithUnit']; ?>
</span>
	
				<?php if ($this->_tpl_vars['product_info']['list_price'] > 0 && $this->_tpl_vars['product_info']['list_price'] > $this->_tpl_vars['product_info']['Price'] && $this->_tpl_vars['product_info']['Price'] > 0): ?> 
		<div>
			<span class="youSaveLabel"><?php echo 'Вы экономите'; ?>
:</span>
			<span class="youSavePrice"><?php echo $this->_tpl_vars['product_info'][14]; ?>
 (<?php echo $this->_tpl_vars['product_info'][15]; ?>
%)</span>
		</div>
		<?php endif; ?>
	<?php endif; ?>
	
		<?php if ($this->_tpl_vars['product_info']['product_code'] && @CONF_ENABLE_PRODUCT_SKU): ?>
	<div>
		<span class="productCodeLabel"><?php echo 'Артикул'; ?>
:&nbsp;</span>
		<span class="productCode"><i><?php echo ((is_array($_tmp=$this->_tpl_vars['product_info']['product_code'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</i></span>
	</div>
	<?php endif; ?>
	
		<?php if (@CONF_CHECKSTOCK == '1'): ?>
		<?php if ($this->_tpl_vars['product_info']['in_stock'] > 0): ?>
		<div>
			<?php echo 'На складе'; ?>
: 
			<?php if (@CONF_EXACT_PRODUCT_BALANCE): ?>
				<?php echo $this->_tpl_vars['product_info']['in_stock']; ?>

			<?php else: ?>
				<?php echo 'да'; ?>

			<?php endif; ?>
		</div>
		<?php endif; ?>
	<?php endif; ?>


	<?php if ($this->_tpl_vars['product_info']['shipping_freightUC']): ?>
	<div>
		<?php echo 'Стоимость упаковки'; ?>
:&nbsp;
			<span style="color: brown;"><?php echo $this->_tpl_vars['product_info']['shipping_freightUC']; ?>
</span>
	</div>
	<?php endif; ?>

	<?php if ($this->_tpl_vars['product_info']['min_order_amount'] > 1): ?>
	<div>
		<?php echo 'Минимальный заказ'; ?>
: <?php echo $this->_tpl_vars['product_info']['min_order_amount']; ?>
 
			<?php echo 'шт.'; ?>

	</div>
	<?php endif; ?>

	<?php if ($this->_tpl_vars['product_info']['weight'] > 0): ?>
	<div>
		<?php echo 'Вес продукта'; ?>
: <?php echo $this->_tpl_vars['product_info']['weight']; ?>
 <?php echo @CONF_WEIGHT_UNIT; ?>

	</div>
	<?php endif; 
 else: ?>
	<span class="regularPrice">$100</span> 
	<span class="totalPrice">$90</span>

	<div>
		<span class="youSaveLabel"><?php echo 'Вы экономите'; ?>
:</span>
		<span class="youSavePrice">$10 (10%)</span>
	</div>
	<?php if (@CONF_ENABLE_PRODUCT_SKU): ?>
	<div>
		<span class="productCodeLabel"><?php echo 'Артикул'; ?>
:&nbsp;</span>
		<span class="productCode">ART2800</span>
	</div>
	<?php endif; ?>
<?php endif; ?>