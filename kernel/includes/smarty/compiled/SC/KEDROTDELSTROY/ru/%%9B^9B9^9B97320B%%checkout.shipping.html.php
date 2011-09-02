<?php /* Smarty version 2.6.26, created on 2011-08-31 17:23:00
         compiled from checkout.shipping.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'set_query_html', 'checkout.shipping.html', 8, false),array('modifier', 'escape', 'checkout.shipping.html', 17, false),array('function', 'count', 'checkout.shipping.html', 15, false),)), $this); ?>
	<?php echo 'Адрес доставки заказа'; ?>
 (<a href='<?php echo $this->_tpl_vars['change_address_url']; ?>
'><?php echo 'Изменить адрес'; ?>
</a>):
	<p>
	<strong><?php if ($this->_tpl_vars['shipping_address_str']): 
 echo $this->_tpl_vars['shipping_address_str']; 
 else: 
 echo 'Не определено'; 
 endif; ?></strong>
	</p>
	
	<p><?php echo 'Выберите способ доставки заказа'; ?>
:</p>
	
	<form action="<?php echo ((is_array($_tmp='')) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
" name='MainForm' method='post'>
	<input name="action" value="select_shipping" type="hidden" >
	
	<?php if ($this->_tpl_vars['shipping_methods']): ?>
	<table border="0" cellspacing="1" cellpadding="4">
	<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['shipping_methods']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
	<?php if ($this->_tpl_vars['shipping_costs'][$this->_sections['i']['index']] != "n/a"): ?>
	<?php echo smarty_function_count(array('item' => '_CostsNum','array' => $this->_tpl_vars['shipping_costs'][$this->_sections['i']['index']]), $this);?>

	<tr><td>
	<label for="shipping-method-<?php echo $this->_tpl_vars['shipping_methods'][$this->_sections['i']['index']]['SID']; ?>
"><?php if ($this->_tpl_vars['shipping_methods'][$this->_sections['i']['index']]['logo']): ?><img src="<?php echo ((is_array($_tmp=$this->_tpl_vars['shipping_methods'][$this->_sections['i']['index']]['logo'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['shipping_methods'][$this->_sections['i']['index']]['Name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" title="<?php echo ((is_array($_tmp=$this->_tpl_vars['shipping_methods'][$this->_sections['i']['index']]['Name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" /><?php else: ?>&nbsp;<?php endif; ?></label>
	</td>
		<td valign="top">
			<input name="shippingMethodID" class="radio_shipping_method" value="<?php echo $this->_tpl_vars['shipping_methods'][$this->_sections['i']['index']]['SID']; ?>
" id="shipping-method-<?php echo $this->_tpl_vars['shipping_methods'][$this->_sections['i']['index']]['SID']; ?>
" <?php if ($this->_tpl_vars['shipping_methods_count'] == 1 || $this->_tpl_vars['shippingMethodID'] == $this->_tpl_vars['shipping_methods'][$this->_sections['i']['index']]['SID']): ?> checked<?php endif; ?> type="radio" >
		</td>
		<td valign="top">
			<strong><label for="shipping-method-<?php echo $this->_tpl_vars['shipping_methods'][$this->_sections['i']['index']]['SID']; ?>
"><?php echo $this->_tpl_vars['shipping_methods'][$this->_sections['i']['index']]['Name']; ?>
</label></strong>
			<br />
			<?php echo $this->_tpl_vars['shipping_methods'][$this->_sections['i']['index']]['description']; ?>

		</td>
		<td valign="top">
			<?php if ($this->_tpl_vars['_CostsNum'] > 1): ?>
			<select name="shippingServiceID[<?php echo $this->_tpl_vars['shipping_methods'][$this->_sections['i']['index']]['SID']; ?>
]">
			<?php $this->assign('_SID', $this->_tpl_vars['shipping_methods'][$this->_sections['i']['index']]['SID']); ?>
			<?php $_from = $this->_tpl_vars['shipping_costs'][$this->_sections['i']['index']]; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['_Rate']):
?>
				<option value="<?php echo $this->_tpl_vars['_Rate']['id']; ?>
" <?php if ($this->_tpl_vars['shippingServiceID'][$this->_tpl_vars['_SID']] == $this->_tpl_vars['_Rate']['id']): ?>selected<?php endif; ?>><?php echo $this->_tpl_vars['_Rate']['name']; ?>
 - <?php echo $this->_tpl_vars['_Rate']['rate']; ?>
</option>
			<?php endforeach; endif; unset($_from); ?>
			</select>
			<?php else: ?>
			<?php if ($this->_tpl_vars['shipping_costs'][$this->_sections['i']['index']][0]['name']): 
 echo $this->_tpl_vars['shipping_costs'][$this->_sections['i']['index']][0]['name']; 
 if ($this->_tpl_vars['shipping_costs'][$this->_sections['i']['index']][0]['rate']): ?> - <?php endif; 
 endif; 
 echo $this->_tpl_vars['shipping_costs'][$this->_sections['i']['index']][0]['rate']; ?>

			<?php endif; ?>
		</td>
	</tr>
	<?php endif; ?>
	<?php endfor; endif; ?>
	</table>
	<?php else: ?>
		<font color="red"><b><?php echo 'Нет доступных способов доставки'; ?>
...</b></font>
	<?php endif; ?>
	
	<?php if ($this->_tpl_vars['shipping_methods']): ?><p>
		<input type="submit" name="continue_button" class="checkout_buttons" value="<?php echo 'Далее'; ?>
" >
	</p><?php endif; ?>
	
		
	<?php if ($this->_tpl_vars['PAGE_VIEW'] != 'mobile' && $this->_tpl_vars['shipping_methods_count'] > 1): 
 echo '
	<script language=\'javascript\' type="text/javascript">
		function select_shipping_methodClickHandler(){
	
			document.MainForm.continue_button.disabled = true;
			var r_shipping_method = getElementsByClass(\'radio_shipping_method\', document.MainForm);
			for(var i=r_shipping_method.length-1; i>=0; i--){
	
				r_shipping_method[i].onclick = select_shipping_methodClickHandler;
				if(!r_shipping_method[i].checked)continue;
				
				document.MainForm.continue_button.disabled = false;
				break;
			}
		}
		select_shipping_methodClickHandler();
	</script>
	'; ?>
 <?php endif; ?>
	</form>