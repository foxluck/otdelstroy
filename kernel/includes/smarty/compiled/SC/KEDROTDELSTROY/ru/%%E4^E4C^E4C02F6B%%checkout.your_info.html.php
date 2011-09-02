<?php /* Smarty version 2.6.26, created on 2011-08-31 17:14:25
         compiled from checkout.your_info.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'set_query_html', 'checkout.your_info.html', 18, false),array('modifier', 'translate', 'checkout.your_info.html', 26, false),array('modifier', 'replace', 'checkout.your_info.html', 26, false),array('modifier', 'escape', 'checkout.your_info.html', 26, false),array('function', 'cycle', 'checkout.your_info.html', 30, false),)), $this); ?>
<script type="text/javascript" src="<?php echo @URL_JS; ?>
/checkout.your_info.js"></script>
<style type="text/css">
<!--
<?php if ($this->_tpl_vars['action'] == 'auth'): 
 echo '
	#block-customerinfo{display: none;}
'; 
 else: 
 echo '
	#block-auth{display: none;}
'; 
 endif; ?>
<?php if ($this->_tpl_vars['billing_as_shipping']): 
 echo '
	#block-billing-address{display: none;}
'; 
 endif; ?>
<?php if (! $this->_tpl_vars['permanent_registering']): 
 echo '
	#block-loginpass-fields{display: none;}
'; 
 endif; ?>
-->
</style>

<form id="block-customerinfo" method="post" action="<?php echo ((is_array($_tmp='')) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
" <?php if ($this->_tpl_vars['email_exists'] || $this->_tpl_vars['login_form']): ?>style="display:none;"<?php endif; ?>>
<input name="action" value="process_customer_info" type="hidden" >

	<?php echo $this->_tpl_vars['MessageBlock']; ?>


	<table cellpadding="0" cellspacing="0" class="cellpadding" id="checkout_logininfo">
	<tr><td colspan="2">
		<p id="checkout_have_account">
		<?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp='checkout_already_have_account')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)))) ? $this->_run_mod_handler('replace', true, $_tmp, '%SHOPNAME%', @CONF_SHOP_NAME) : smarty_modifier_replace($_tmp, '%SHOPNAME%', @CONF_SHOP_NAME)))) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
 <a href="<?php echo ((is_array($_tmp='?ukey=auth')) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
" class="hndl_show_login"><?php echo 'Вход'; ?>
</a>
		</p>
	</td>
	</tr>
	<tr class="row_<?php echo smarty_function_cycle(array('name' => '__checkout','values' => 'odd,even'), $this);?>
">
		<td><span class="asterisk">*</span><?php echo 'Имя'; ?>
</td>
		<td>
			<input id="chk_first_name" name="customer_info[first_name]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['customer_info']['first_name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" class="inputtext" type="text" >
		</td>
	</tr>
	<tr class="row_<?php echo smarty_function_cycle(array('name' => '__checkout','values' => 'odd,even'), $this);?>
">
		<td><span class="asterisk">*</span><?php echo 'Фамилия'; ?>
</td>
		<td>
			<input id="chk_last_name" name="customer_info[last_name]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['customer_info']['last_name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" class="inputtext" type="text" >
		</td>
	</tr>
	<tr class="row_<?php echo smarty_function_cycle(array('name' => '__checkout','values' => 'odd,even'), $this);?>
">
		<td><span class="asterisk">*</span><?php echo 'Email'; ?>
</td>
		<td>
			<input name="customer_info[Email]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['customer_info']['Email'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" class="inputtext" type="text" >
		</td>
	</tr>
	<!-- ADDITIONAL FIELDS -->
	<?php $_from = $this->_tpl_vars['additional_fields']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['_field']):
?>
	<?php $this->assign('_field_name', "additional_field_".($this->_tpl_vars['_field']['reg_field_ID'])); ?>
	<tr class="row_<?php echo smarty_function_cycle(array('name' => '__checkout','values' => 'odd,even'), $this);?>
">
		<td><?php if ($this->_tpl_vars['_field']['reg_field_required']): ?><span class="asterisk">*</span><?php endif; 
 echo $this->_tpl_vars['_field']['reg_field_name']; ?>
</td>
		<td>
			<input type='text' name='customer_info[_custom_fields][<?php echo $this->_tpl_vars['_field']['reg_field_ID']; ?>
]' value='<?php echo ((is_array($_tmp=$this->_tpl_vars['customer_info']['_custom_fields'][$this->_tpl_vars['_field']['reg_field_ID']])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
' class="inputtext" >
		</td>
	</tr>
	<?php endforeach; endif; unset($_from); ?>
	</table>
	
	<p>
	<input name="customer_info[subscribed4news]" value="1"<?php if ($this->_tpl_vars['customer_info']['subscribed4news'] || ( $this->_tpl_vars['subscribed4news'] )): ?> checked<?php endif; ?> id="custinfo-subscribed4news" type="checkbox" >
	<label for="custinfo-subscribed4news"><?php echo 'Подписаться на новости'; ?>
</label>
	</p>
<?php if ($this->_tpl_vars['PAGE_VIEW'] != 'mobile'): ?>	
	<table cellpadding="0" cellspacing="0" id="checkout_addresses">
	<tr>
		<td>
			<strong><?php echo 'Адрес доставки заказа'; ?>
</strong>
		</td>
	<?php if (@CONF_ORDERING_REQUEST_BILLING_ADDRESS == '1'): ?>
		<td></td>
		<td>
			<strong><?php echo 'Адрес плательщика'; ?>
</strong>
			(<span class="field_description"><input name="billing_as_shipping" id="hndl-show-billing-address" type="checkbox"<?php if ($this->_tpl_vars['billing_as_shipping']): ?> checked<?php endif; ?> ><label for="hndl-show-billing-address"><?php echo 'Совпадает с адресом доставки заказа'; ?>
</label></span>)
		</td>
	<?php endif; ?>
	</tr>
	<tr>
		<td valign="top" id="checkout_addresses_shipping">
			<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => (@DIR_FTPLS)."/address_form.html", 'smarty_include_vars' => array('name_space' => 'shipping_address','address' => $this->_tpl_vars['shipping_address'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		</td>
	<?php if (@CONF_ORDERING_REQUEST_BILLING_ADDRESS == '1'): ?>
		<td style="padding:5px;"></td>
		<td valign="top" id="checkout_addresses_billing">
			<div id="block-billing-address">
			<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => (@DIR_FTPLS)."/address_form.html", 'smarty_include_vars' => array('name_space' => 'billing_address','address' => $this->_tpl_vars['billing_address'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
			</div>
		</td>
	<?php endif; ?>
	</tr>
	</table>
<?php else: ?>
	<table cellpadding="0" cellspacing="0" id="checkout_addresses">
	<tr>
		<td>
			<strong><?php echo 'Адрес доставки заказа'; ?>
</strong>
		</td>
	</tr>
	<tr>
		<td id="checkout_addresses_shipping">
			<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => (@DIR_FTPLS)."/address_form.html", 'smarty_include_vars' => array('name_space' => 'shipping_address','address' => $this->_tpl_vars['shipping_address'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		</td>
	</tr>
	<?php if (@CONF_ORDERING_REQUEST_BILLING_ADDRESS == '1'): ?>
	<tr>
		<td>
			<strong><?php echo 'Адрес плательщика'; ?>
</strong>
		</td>
	</tr>
	<tr>
		<td id="checkout_addresses_billing">
			<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => (@DIR_FTPLS)."/address_form.html", 'smarty_include_vars' => array('name_space' => 'billing_address','address' => $this->_tpl_vars['billing_address'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		</td>
	</tr>
	<?php endif; ?>
	</table>
<?php endif; ?>
	<p>
	<input name="permanent_registering" id="hndl-show-loginpass-fields"<?php if ($this->_tpl_vars['permanent_registering']): ?> checked<?php endif; ?> type="checkbox" >
	<label for="hndl-show-loginpass-fields"><?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp='checkout_permanent_registering')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)))) ? $this->_run_mod_handler('replace', true, $_tmp, '%SHOPNAME%', @CONF_SHOP_NAME) : smarty_modifier_replace($_tmp, '%SHOPNAME%', @CONF_SHOP_NAME)))) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</label>
	</p>
<?php if ($this->_tpl_vars['PAGE_VIEW'] != 'mobile'): ?>		
	<div id="block-loginpass-fields">
<?php endif; ?>
	<table cellpadding="0" cellspacing="0" class="cellpadding">
	<tr>
		<td><?php echo 'Логин'; ?>
</td>
		<td>
			<input name="customer_info[Login]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['customer_info']['Login'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" type="text" >
		</td>
	</tr>
	<tr>
		<td><?php echo 'Пароль'; ?>
</td>
		<td>
			<input name="customer_info[cust_password]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['customer_info']['cust_password'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" type="password" >
		</td>
	</tr>
	<tr>
		<td><?php echo 'Подтвердите пароль'; ?>
</td>
		<td>
			<input name="customer_info[cust_password1]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['customer_info']['cust_password1'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" type="password" >
		</td>
	</tr>
	</table>
<?php if ($this->_tpl_vars['PAGE_VIEW'] != 'mobile'): ?>	
	<br />
	</div>
<?php endif; ?>
	
	<?php if (@CONF_ENABLE_CONFIRMATION_CODE): ?>

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
			<input name="confirmation_code" value="" type="text" style="width:200px;" >
		</td>
	</tr>
	</table>
	<?php endif; ?>

    <?php if (! $this->_tpl_vars['SessionRefererLogin'] && @CONF_AFFILIATE_PROGRAM_ENABLED == 1): ?>
    <table cellpadding="6" cellspacing="0">
    <tr class="row_<?php echo smarty_function_cycle(array('name' => '__checkout','values' => 'odd,even'), $this);?>
">
        <td style="font-size: 90%;"><?php echo 'Кто направил (логин пользователя)<br /><i>оставьте это поле пустым, если сомневаетесь</i>'; ?>
</td>
        <td>
            <input name="customer_info[affiliationLogin]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['customer_info']['affiliationLogin'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" class="inputtext"  type="text" style="width: 110px;" >
        </td>
    </tr>
    </table>
    <?php endif; ?>
	
	<p>
	<input class="checkout_buttons" value="<?php echo 'Далее'; ?>
" type="submit" >
	</p>
</form>

<?php if ($this->_tpl_vars['email_exists']): ?>
<p><?php echo ((is_array($_tmp=((is_array($_tmp='checkout_email_exists')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)))) ? $this->_run_mod_handler('replace', true, $_tmp, '[email]', $this->_tpl_vars['email_exists']) : smarty_modifier_replace($_tmp, '[email]', $this->_tpl_vars['email_exists'])); ?>
</p>
<?php endif; ?>
<form action="<?php echo ((is_array($_tmp='')) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
" id="block-auth" method="post" <?php if ($this->_tpl_vars['email_exists'] || $this->_tpl_vars['login_form']): ?>style="display:block;"<?php endif; ?>>
	<?php echo $this->_tpl_vars['MessageBlock__auth']; ?>

	<input name="action" value="auth" type="hidden" >
	
	<?php echo 'Логин'; ?>
:
	<br />
	<input type="text" name="auth[Login]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['auth']['Login'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" size="40" >
	
	<p>
		<?php echo 'Пароль'; ?>
:
		<br />
		<input name="auth[cust_password]" type="password" size="40" >
	</p>
	
	<p>
		<input value="<?php echo 'Вход'; ?>
" type="submit" >
	</p>
	
	<p>
		<a href="<?php echo ((is_array($_tmp='?ukey=remind_password')) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
"><?php echo 'Забыли пароль?'; ?>
</a>
		&nbsp;
		<a href="<?php echo ((is_array($_tmp='?ukey=checkout')) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
" class="hndl_show_login"><?php echo 'Оформление заказа от имени нового покупателя'; ?>
</a>
	</p>
	
</form>