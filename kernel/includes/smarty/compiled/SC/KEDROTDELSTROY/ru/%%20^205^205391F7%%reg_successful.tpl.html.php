<?php /* Smarty version 2.6.26, created on 2011-08-31 17:20:57
         compiled from reg_successful.tpl.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'set_query_html', 'reg_successful.tpl.html', 21, false),)), $this); ?>

<?php if ($this->_tpl_vars['reg_terminated']): ?> 
	<center><b><?php echo 'Ваша регистрация была успешно отменена. Спасибо за покупки!'; ?>
</b></center>

<?php else: ?>

	<?php if ($this->_tpl_vars['reg_updating']): ?>
		<br><br><br>
		<center><b><?php echo 'Данные успешно изменены.<br />Спасибо!'; ?>
</b></center>
	<?php else: ?>

		<?php if (@CONF_ENABLE_REGCONFIRMATION): ?>
		<center><h1><?php echo 'Активация учетной записи'; ?>
</h1></center>
		<?php else: ?>
		<br><br><br>
		<?php endif; ?>

		<center><b><?php echo 'Регистрация прошла успешно.<br />Спасибо!'; ?>
</b></center>
		<center><a href="<?php echo ((is_array($_tmp='ukey=office')) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
"><?php echo 'Личный кабинет'; ?>
</a></center>		
		
		<?php if (@CONF_ENABLE_REGCONFIRMATION): ?>
			<form method="get" action="<?php echo ((is_array($_tmp='ukey=act_customer')) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
" style="text-align:center;">
				<input type="hidden" name="ukey" value="act_customer" >
				<?php echo 'Для активации учетной записи, пожалуйста, введите ключ активации (отправлен вам по email):<br />'; ?>
  <input type="text" name="act_code" value="<?php echo $this->_tpl_vars['ActCode']; ?>
" >
				<br />
				<br />
				<input type="submit" value="<?php echo 'Активировать'; ?>
" >
			</form>
		<?php endif; ?>
	<?php endif; ?>

<?php endif; ?>