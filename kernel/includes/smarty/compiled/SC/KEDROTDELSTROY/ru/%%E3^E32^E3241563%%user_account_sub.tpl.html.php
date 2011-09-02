<?php /* Smarty version 2.6.26, created on 2011-08-31 17:21:14
         compiled from user_account_sub.tpl.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'set_query_html', 'user_account_sub.tpl.html', 3, false),array('modifier', 'translate', 'user_account_sub.tpl.html', 21, false),)), $this); ?>
<h1 <?php if ($this->_tpl_vars['printable_version']): ?>align="center"<?php endif; ?>>
<?php if (! $this->_tpl_vars['printable_version']): ?>
	<a href='<?php echo ((is_array($_tmp="?ukey=office")) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
'><?php echo 'Мой счет'; ?>
</a>
	&raquo;
	<?php if ($this->_tpl_vars['CurrentDivision']['ukey'] == 'address_editor' || $this->_tpl_vars['CurrentDivision']['ukey'] == 'add_address'): ?>
	<a href='<?php echo ((is_array($_tmp="?ukey=address_book")) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
'><?php echo 'Адресная книга'; ?>
</a>
	&raquo;
	<?php endif; ?>
	<?php if ($this->_tpl_vars['CurrentDivision']['ukey'] == 'affiliate_balance' || $this->_tpl_vars['CurrentDivision']['ukey'] == 'affiliate_settings' || $this->_tpl_vars['CurrentDivision']['ukey'] == 'affiliate_money' || $this->_tpl_vars['CurrentDivision']['ukey'] == 'affiliate_history'): ?>
	<?php echo 'Партнерская программа'; ?>

	&raquo;
	<?php endif; ?>
<?php endif; ?>
	<?php if ($this->_tpl_vars['CurrentDivision']['ukey'] == 'order_detailed'): ?>
<?php if (! $this->_tpl_vars['printable_version']): ?>
	<a href='<?php echo ((is_array($_tmp="ukey=order_history&did=&orderID=")) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
'><?php echo 'История заказов'; ?>
</a>
	&raquo;
<?php endif; ?>
	<?php echo 'Заказ'; ?>
 #<?php echo $this->_tpl_vars['order']['orderID_view']; ?>

	<?php else: ?>
	<?php echo ((is_array($_tmp=$this->_tpl_vars['CurrentDivision']['name'])) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>

	<?php endif; ?>
</h1>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => $this->_tpl_vars['CurrentSubTpl'], 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>