<?php /* Smarty version 2.6.26, created on 2011-08-31 17:16:21
         compiled from feedback.tpl.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'set_query_html', 'feedback.tpl.html', 11, false),array('modifier', 'escape', 'feedback.tpl.html', 14, false),)), $this); ?>

<h1><?php echo 'Обратная связь'; ?>
</h1>

<?php if ($this->_tpl_vars['sent'] == NULL): ?>

<?php echo $this->_tpl_vars['MessageBlock']; ?>


<p><?php echo 'Вы можете отправить нам запрос по электронной почте с помощью следующей формы.'; ?>
</p>

<form name="form1" method="post" action="<?php echo ((is_array($_tmp='')) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
">
<div class="common_wrapper">
	<?php echo 'Имя'; ?>
:<br>
	<input name="customer_name" type="text" size="40" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['feedback_data']['customer_name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
"><br>

	<?php echo 'Email'; ?>
<br>
	<input name="customer_email" type="text" size="40" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['feedback_data']['customer_email'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
"><br>

	<?php echo 'Тема'; ?>
:<br>
	<input name="message_subject" type="text" size="40" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['feedback_data']['message_subject'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
"><br>

	<?php echo 'Сообщение'; ?>
:<br>
	<textarea name="message_text" cols="60" rows="7"><?php echo ((is_array($_tmp=$this->_tpl_vars['feedback_data']['message_text'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</textarea>

	<?php if (@CONF_ENABLE_CONFIRMATION_CODE): ?>
	<br />
	<br />
	<table cellpadding="6" cellspacing="0">
	<tr class="background1">
		<td colspan="2"><?php echo 'Введите число, изображенное на рисунке'; ?>
</td>
	</tr>
	<tr class="background1">
		<td align="right"><img src="<?php echo @URL_ROOT; ?>
/imgval.php" alt="code" align="right" /></td>
		<td>
			<input name="fConfirmationCode" value="" type="text" style="width:200px;" >
		</td>
	</tr>
	</table>
	<?php endif; ?>

	<p>
	<input type="submit" value="OK">
	<input type="hidden" name="send" value="yes">
	<input type="hidden" name="feedback" value="yes">
	</p>
</div>
</form>

<?php else: ?>

<p><span class=faq style="color: blue; font-weight: bold;"><?php echo '<B>Сообщение успешно отправлено.</B><br>Мы ответим Вам в ближайшее время. Спасибо за Ваш запрос!'; ?>
</span></p>

<?php endif; ?>