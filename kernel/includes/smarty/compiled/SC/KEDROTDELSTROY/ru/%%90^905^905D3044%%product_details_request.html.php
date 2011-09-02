<?php /* Smarty version 2.6.26, created on 2011-08-31 14:55:32
         compiled from product_details_request.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'translate', 'product_details_request.html', 10, false),array('modifier', 'replace', 'product_details_request.html', 10, false),array('modifier', 'escape', 'product_details_request.html', 10, false),)), $this); ?>
<?php if (! $this->_tpl_vars['CPT_CONSTRUCTOR_MODE']): ?>
<?php if ($this->_tpl_vars['PAGE_VIEW'] != 'printable'): ?>
<div class="pageSeparator"></div>
<a name="product-request"></a>

<p>
	<a name="inquiry"></a>
	<?php if ($this->_tpl_vars['sent'] == NULL): ?>

	<h2><?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp='feedback_title_productpage')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)))) ? $this->_run_mod_handler('replace', true, $_tmp, "[product_name]", ($this->_tpl_vars['product_info']['name'])) : smarty_modifier_replace($_tmp, "[product_name]", ($this->_tpl_vars['product_info']['name']))))) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</h2>

	<p><?php echo 'Вы можете задать нам вопрос(ы) с помощью следующей формы.'; ?>
</p>

	<?php echo $this->_tpl_vars['MessageBlock__prd_request']; ?>


	<input name="message_subject" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['product_info']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" type="hidden" >
	<input name="productID" value="<?php echo $this->_tpl_vars['product_info']['productID']; ?>
" type="hidden" >
	
	<?php echo 'Имя'; ?>
:<br />
	<input name="customer_name"  value="<?php echo ((is_array($_tmp=$this->_tpl_vars['prd_request']['customer_name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" type="text" size="40" ><br />

	<?php echo 'Email'; ?>
<br />
	<input name="customer_email" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['prd_request']['customer_email'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" type="text" size="40" >


	<p><?php echo 'Пожалуйста, сформулируйте Ваши вопросы относительно '; ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['product_info']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
:<br>
	<textarea name="message_text" cols="50" rows="10"><?php echo ((is_array($_tmp=$this->_tpl_vars['prd_request']['message_text'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</textarea>
	</p>
	
	<?php if (@CONF_ENABLE_CONFIRMATION_CODE): ?>
		<br />
		<table cellpadding="6" cellspacing="0">
		<tr class="background1">
			<td colspan="2"><?php echo 'Введите число, изображенное на рисунке'; ?>
</td>
		</tr>
<!--		<?php if ($this->_tpl_vars['PAGE_VIEW'] == 'mobile'): ?> mobile view -->
		<tr class="background1">
			<td colspan="2" align="center">
				<img src="<?php echo @URL_ROOT; ?>
/imgval.php" alt="code" />
				<br />
				<input name="fConfirmationCode" value="" type="text" >
			</td>
		</tr>
<!--		<?php else: ?> generic view -->
		<tr class="background1">
			<td align="right">
				<img src="<?php echo @URL_ROOT; ?>
/imgval.php" alt="code" align="right" />
			</td>
			<td>
				<input name="fConfirmationCode" value="" type="text" style="width:200px;" >
			</td>
		</tr>
<!--		<?php endif; ?> -->
		</table>
	<?php endif; ?>

	<p>
	<input type="submit" name="request_information" value="OK">
	</p>

	<?php else: ?>

	<p><span class=faq style="color: blue; font-weight: bold;"><?php echo '<B>Сообщение успешно отправлено.</B><br>Мы ответим Вам в ближайшее время. Спасибо за Ваш запрос!'; ?>
</span></p>

	<?php endif; ?>
<?php endif; ?>
<?php else: ?>
	<h2><?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp='feedback_title_productpage')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)))) ? $this->_run_mod_handler('replace', true, $_tmp, "[product_name]", ($this->_tpl_vars['product_info']['name'])) : smarty_modifier_replace($_tmp, "[product_name]", ($this->_tpl_vars['product_info']['name']))))) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</h2>

	<p><?php echo 'Вы можете задать нам вопрос(ы) с помощью следующей формы.'; ?>
</p>
	
	...
<?php endif; ?>