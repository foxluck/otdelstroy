<?php /* Smarty version 2.6.26, created on 2011-08-31 17:21:14
         compiled from order_history.tpl.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'cycle', 'order_history.tpl.html', 15, false),array('modifier', 'set_query', 'order_history.tpl.html', 16, false),)), $this); ?>

<?php if ($this->_tpl_vars['user_orders']): ?>
<p>
	<center><?php echo $this->_tpl_vars['orders_navigator']; ?>
</center>
<p>

<table cellspacing="0" cellpadding="6" width="100%">
	
	<tr class="background1">
	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "gridsheader.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	</tr>
	
	<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['user_orders']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
	<tr class="<?php echo smarty_function_cycle(array('values' => 'row_odd,row_even'), $this);?>
">
		<td><a href='<?php echo ((is_array($_tmp="ukey=order_detailed&orderID=".($this->_tpl_vars['user_orders'][$this->_sections['i']['index']]['orderID']))) ? $this->_run_mod_handler('set_query', true, $_tmp) : smarty_modifier_set_query($_tmp)); ?>
'><?php echo $this->_tpl_vars['user_orders'][$this->_sections['i']['index']]['orderID_view']; ?>
</a></td>
		<td nowrap="nowrap"><?php echo $this->_tpl_vars['user_orders'][$this->_sections['i']['index']]['order_time']; ?>
</td>
		<td align="center"><?php echo $this->_tpl_vars['user_orders'][$this->_sections['i']['index']]['OrderStatus']; ?>
</td>
		<td align="right"><?php echo $this->_tpl_vars['user_orders'][$this->_sections['i']['index']]['order_amount']; ?>
</td>
	</tr>
	<?php endfor; endif; ?>

</table>

<?php else: ?>
	<p><?php echo 'нет заказов'; ?>
</p>
<?php endif; ?>