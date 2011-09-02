<?php /* Smarty version 2.6.26, created on 2011-08-31 17:25:20
         compiled from checkout.billing.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'set_query_html', 'checkout.billing.html', 10, false),array('modifier', 'escape', 'checkout.billing.html', 16, false),)), $this); ?>
<?php if (@CONF_ORDERING_REQUEST_BILLING_ADDRESS): ?>
	<?php echo 'Адрес плательщика'; ?>
 (<a href='<?php echo $this->_tpl_vars['change_address_url']; ?>
'><?php echo 'Изменить адрес'; ?>
</a>):
	<p>
	<strong><?php echo $this->_tpl_vars['billing_address_str']; ?>
</strong>
	</p>
<?php endif; ?>
	
	<p><?php echo 'Выберите способ оплаты'; ?>
:</p>
	
	<form action="<?php echo ((is_array($_tmp='')) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
" method="POST" name='MainForm'>
	<input name="action" value="select_payment" type="hidden" >
	
	<table cellspacing="1" cellpadding="4">
	<?php $_from = $this->_tpl_vars['payment_methods']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['pm'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['pm']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['_payment_method']):
        $this->_foreach['pm']['iteration']++;
?>
	<tr>
		<td valign="top"><label for="payment-method-<?php echo $this->_tpl_vars['_payment_method']['PID']; ?>
"><?php if ($this->_tpl_vars['_payment_method']['logo']): ?><img src="<?php echo ((is_array($_tmp=$this->_tpl_vars['_payment_method']['logo'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['_payment_method']['Name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" title="<?php echo ((is_array($_tmp=$this->_tpl_vars['_payment_method']['Name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" /><?php else: ?>&nbsp;<?php endif; ?></label></td>
		<td valign="top">
			<input name="paymentMethodID" id="payment-method-<?php echo $this->_tpl_vars['_payment_method']['PID']; ?>
" value="<?php echo $this->_tpl_vars['_payment_method']['PID']; ?>
" <?php if ($this->_foreach['pm']['iteration'] == 1 && ! $this->_tpl_vars['paymentMethodID'] || $this->_tpl_vars['paymentMethodID'] == $this->_tpl_vars['_payment_method']['PID']): ?>checked<?php endif; ?> type="radio" >
		</td>
		<td valign="top">
			<strong><label for="payment-method-<?php echo $this->_tpl_vars['_payment_method']['PID']; ?>
"><?php echo $this->_tpl_vars['_payment_method']['Name']; ?>
</label></strong>
			<?php if ($this->_tpl_vars['_payment_method']['description']): ?><br /><?php echo $this->_tpl_vars['_payment_method']['description']; 
 endif; ?>
		</td>
	</tr>
	<?php endforeach; endif; unset($_from); ?>
	</table>
	
	<?php if (! $this->_tpl_vars['payment_methods']): ?>
		<span style="color: red; font-weight: bold;"><?php echo 'Нет доступных способов для оплаты'; ?>
...</span>
	<?php else: ?>
		<br />
		<input type="submit" name="continue_button"  class="checkout_buttons" value="<?php echo 'Далее'; ?>
" >
	<?php endif; ?>
	
	</form>