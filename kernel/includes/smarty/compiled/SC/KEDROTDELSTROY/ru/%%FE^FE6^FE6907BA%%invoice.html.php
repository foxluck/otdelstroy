<?php /* Smarty version 2.6.26, created on 2011-09-01 05:31:35
         compiled from /home/kedr/domains/otdelstroy31.ru/public_html/published/SC/html/scripts/templates/forms/invoice.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'set_query_html', '/home/kedr/domains/otdelstroy31.ru/public_html/published/SC/html/scripts/templates/forms/invoice.html', 20, false),array('modifier', 'escape', '/home/kedr/domains/otdelstroy31.ru/public_html/published/SC/html/scripts/templates/forms/invoice.html', 39, false),array('modifier', 'default', '/home/kedr/domains/otdelstroy31.ru/public_html/published/SC/html/scripts/templates/forms/invoice.html', 55, false),array('modifier', 'htmlsafe', '/home/kedr/domains/otdelstroy31.ru/public_html/published/SC/html/scripts/templates/forms/invoice.html', 61, false),array('modifier', 'string_format', '/home/kedr/domains/otdelstroy31.ru/public_html/published/SC/html/scripts/templates/forms/invoice.html', 101, false),)), $this); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo 'ЗАКАЗ'; ?>
</title>
<base href="<?php echo @CONF_FULL_SHOP_URL; ?>
" /><style type="text/css"><?php echo '
@media screen {input,.noprint {display: inline;height: auto;} .printable{display: none;}}
@media print {input,.noprint {display: none;} .printable{display: inline;}}
body, td { font-family:Arial, Helvetica, sans-serif;}.printList { width:90%;  margin:30px auto;}.printList td { vertical-align:top;}h1 { margin:10px 0 0 0;}h4 { border-bottom:1px solid #ddd;margin:0; padding-bottom:10px; color:#666; font-size:11pt;}p { margin:0 0 0px 0;}.urllinks span { display:block; float:left; padding:0 5px 0 0;}.urllinks { zoom:1; overflow:hidden;}.strongtext {  font-weight:bold;}.middeltext { font-size:10pt;}.grey { background:#F7F7f7; padding:10px; margin:0 0 0 0; font-size:11pt;}.even { background:#F7F7f7;}.w1 { padding:0 10px 0 30px; text-align:right; width:1%; white-space:nowrap;}.w2 { text-align:right;padding:0 10px 0 0;width:83%}.invoiceHeader td { font-size:11pt;}.invoiceHeader td.invNum, .totalPrice { font-size:15pt; font-weight:bold; color:#000; padding:0 0 10px 0; line-height:15pt;}.invoiceDecsr { border-top:solid 1px #ddd; margin:15px 0 0 0;}.invoiceDecsr td { padding:5px; border-collapse:collapse; vertical-align:top; text-align:center;} .invoiceDecsr th { background:#f0f0f0; padding:5px;  font-weight:normal; font-size:11pt; }.invoiceDecsr td.lefttext, .invoiceDecsr th.lefttext { text-align:left;}.invoiceDecsr tr.total td { border-top:solid 1px #999; border-right:none;}.invoiceDecsr tr.total td.totalPrice { border:1px solid #999; background:#f0f0f0;width:20% }.centrtext {  text-align:center;}.greytext { color:#555; font-weight:bold;}.Footer { width:50%; margin:0 0 0 auto;}	'; ?>
</style>
<?php if (! $this->_tpl_vars['strict']): ?>
<script type="text/javascript">
var lang_strings = <?php echo '{'; ?>

	'edit_link':'<?php echo 'Корректировка перед печатью'; ?>
',
	'field_title':'<?php echo 'Двойной клик для редактирования'; ?>
',
	'save_link':'<?php echo 'OK'; ?>
'
<?php echo '}'; ?>

var page_url = '<?php echo ((is_array($_tmp='')) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
';
</script>
<script type="text/javascript" src="<?php echo @URL_JS; ?>
/inline_edit_printform.js"></script>
<?php endif; ?>
</head>
<body<?php if (! $this->_tpl_vars['strict']): ?> onload="Printform.init('inline_edit');"<?php endif; ?>>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "print_button.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<table cellpadding="20" cellspacing="0" width="90%" border="0"
	class="printList">
	<tr>
		<td class="">
		<?php if (@CONF_PRINTFORM_COMPANY_LOGO): ?>
		<h1><img src="<?php echo @URL_IMAGES; ?>
/<?php echo @CONF_PRINTFORM_COMPANY_LOGO; ?>
"/></h1>
		<?php else: ?>
		<h1><img src="<?php echo @URL_IMAGES; ?>
/companyname.gif"/></h1>
		<?php endif; ?>
		
		<div class="grey">
		<p class="strongtext"><?php echo ((is_array($_tmp=@CONF_SHOP_NAME)) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</p>
		<?php if ($this->_tpl_vars['COMPANYNAME']): ?><p class="strongtext"><?php echo $this->_tpl_vars['COMPANYNAME']; ?>
</p><?php endif; ?>
		<?php if ($this->_tpl_vars['COMPANYADDRESS']): ?><p><?php echo $this->_tpl_vars['COMPANYADDRESS']; ?>
</p><?php endif; ?>
		<?php if ($this->_tpl_vars['COMPANYPHONE']): ?><p><?php echo @PRINTFORMS_INVOICE_PHONE; ?>
:&nbsp;<?php echo $this->_tpl_vars['COMPANYPHONE']; ?>
</p><?php endif; ?>
		<br/>
		<p class="urllinks"><span><a href="<?php echo $this->_tpl_vars['store_url']; ?>
"><?php echo $this->_tpl_vars['store_url']; ?>
</a><br />
		<a href="mailto:<?php echo @CONF_GENERAL_EMAIL; ?>
"><?php echo @CONF_GENERAL_EMAIL; ?>
</a></span></p>
		</div>
		</td>
	</tr>
	<tr>
		<td>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"
			class="invoiceHeader">
			<tr>
				<td class="w1"><?php echo 'Инвойс'; ?>
:</td>
				<td class="invNum inline_edit"><?php echo ((is_array($_tmp=@$this->_tpl_vars['order']['orderID_view'])) ? $this->_run_mod_handler('default', true, $_tmp, "&nbsp;") : smarty_modifier_default($_tmp, "&nbsp;")); ?>
</td>
				<td class="w1 strongtext inline_edit"><?php echo ((is_array($_tmp=@$this->_tpl_vars['order']['date_print'])) ? $this->_run_mod_handler('default', true, $_tmp, "&nbsp;") : smarty_modifier_default($_tmp, "&nbsp;")); ?>
</td>
			</tr>
			<tr>
				<td class="w1"><?php echo 'Плательщик'; ?>
:</td>
				<td class="middeltext greytext">
				<?php if ($this->_tpl_vars['customer']['company']): ?><p class="inline_edit"><?php echo ((is_array($_tmp=$this->_tpl_vars['customer']['company'])) ? $this->_run_mod_handler('htmlsafe', true, $_tmp, true, true) : smarty_modifier_htmlsafe($_tmp, true, true)); ?>
</p><?php else: ?>
				<p class="inline_edit"><?php echo ((is_array($_tmp=$this->_tpl_vars['order']['billing_name'])) ? $this->_run_mod_handler('htmlsafe', true, $_tmp, true, true) : smarty_modifier_htmlsafe($_tmp, true, true)); ?>
</p>
				<?php endif; ?>
				<?php if ($this->_tpl_vars['order']['billing_address']): ?><p class="inline_edit"><?php echo ((is_array($_tmp=$this->_tpl_vars['order']['billing_address'])) ? $this->_run_mod_handler('htmlsafe', true, $_tmp, true, true) : smarty_modifier_htmlsafe($_tmp, true, true)); ?>
</p><?php endif; ?>
				
				<p class="inline_edit"><?php if ($this->_tpl_vars['order']['billing_state']): 
 echo ((is_array($_tmp=$this->_tpl_vars['order']['billing_state'])) ? $this->_run_mod_handler('htmlsafe', true, $_tmp, true, true) : smarty_modifier_htmlsafe($_tmp, true, true)); ?>
,&nbsp;<?php endif; ?>
<?php if ($this->_tpl_vars['order']['billing_city']): 
 echo ((is_array($_tmp=$this->_tpl_vars['order']['billing_city'])) ? $this->_run_mod_handler('htmlsafe', true, $_tmp, true, true) : smarty_modifier_htmlsafe($_tmp, true, true)); ?>
,&nbsp;<?php endif; ?>
<?php if ($this->_tpl_vars['order']['billing_zip']): 
 echo ((is_array($_tmp=$this->_tpl_vars['order']['billing_zip'])) ? $this->_run_mod_handler('htmlsafe', true, $_tmp, true, true) : smarty_modifier_htmlsafe($_tmp, true, true)); 
 endif; ?></p>
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td class="w1"><?php echo 'Получатель'; ?>
:</td>
				<td class="middeltext greytext">
				<?php if ($this->_tpl_vars['customer']['company']): ?><p class="inline_edit"><?php echo ((is_array($_tmp=$this->_tpl_vars['customer']['company'])) ? $this->_run_mod_handler('htmlsafe', true, $_tmp, true, true) : smarty_modifier_htmlsafe($_tmp, true, true)); ?>
</p><?php else: ?>
				<p class="inline_edit"><?php echo ((is_array($_tmp=$this->_tpl_vars['order']['shipping_name'])) ? $this->_run_mod_handler('htmlsafe', true, $_tmp, true, true) : smarty_modifier_htmlsafe($_tmp, true, true)); ?>
</p>
				<?php endif; ?>
				<?php if ($this->_tpl_vars['order']['shipping_address']): ?><p class="inline_edit"><?php echo ((is_array($_tmp=$this->_tpl_vars['order']['shipping_address'])) ? $this->_run_mod_handler('htmlsafe', true, $_tmp, true, true) : smarty_modifier_htmlsafe($_tmp, true, true)); ?>
</p><?php endif; ?>
				
				<p class="inline_edit"><?php if ($this->_tpl_vars['order']['shipping_state']): 
 echo ((is_array($_tmp=$this->_tpl_vars['order']['shipping_state'])) ? $this->_run_mod_handler('htmlsafe', true, $_tmp, true, true) : smarty_modifier_htmlsafe($_tmp, true, true)); ?>
,&nbsp;<?php endif; ?>
<?php if ($this->_tpl_vars['order']['shipping_city']): 
 echo ((is_array($_tmp=$this->_tpl_vars['order']['shipping_city'])) ? $this->_run_mod_handler('htmlsafe', true, $_tmp, true, true) : smarty_modifier_htmlsafe($_tmp, true, true)); ?>
,&nbsp;<?php endif; ?>
<?php if ($this->_tpl_vars['order']['shipping_zip']): 
 echo ((is_array($_tmp=$this->_tpl_vars['order']['shipping_zip'])) ? $this->_run_mod_handler('htmlsafe', true, $_tmp, true, true) : smarty_modifier_htmlsafe($_tmp, true, true)); 
 endif; ?></p>
				</td>
				<td>&nbsp;</td>
			</tr>

		</table>

		<table width="100%" border="0" cellpadding="0" cellspacing="0"
			class="invoiceDecsr">
			<tr>
				<th>&nbsp;</th>
				<th class="lefttext"><?php echo 'Описание'; ?>
</th>
				<th><?php echo 'Кол-во'; ?>
</th>
				<th><?php echo 'Цена'; ?>
</th>
			</tr>
<?php $_from = $this->_tpl_vars['order_content']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['id'] => $this->_tpl_vars['order_item']):
?>			
			<tr class="odd">
				<td><?php echo $this->_tpl_vars['id']+1; ?>
</td>
				<td class="lefttext inline_edit"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['order_item']['name'])) ? $this->_run_mod_handler('htmlsafe', true, $_tmp, true, true) : smarty_modifier_htmlsafe($_tmp, true, true)))) ? $this->_run_mod_handler('default', true, $_tmp, "&nbsp;") : smarty_modifier_default($_tmp, "&nbsp;")); ?>
</td>
				<td><?php echo ((is_array($_tmp=$this->_tpl_vars['order_item']['Quantity'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "%d") : smarty_modifier_string_format($_tmp, "%d")); ?>
</td>
				<td class="nobr"><?php echo $this->_tpl_vars['order_item']['PriceToShow']; ?>
</td>
			</tr>
<?php endforeach; endif; unset($_from); ?> 
<?php $this->assign('class', 'total'); ?>
<?php if ($this->_tpl_vars['order']['order_discount']): ?>
			<tr class="<?php echo $this->_tpl_vars['class']; ?>
">
				<td>&nbsp;</td>
				<td class="lefttext"><?php echo 'Скидка, %'; 
 echo ((is_array($_tmp=$this->_tpl_vars['order']['order_discount_percent'])) ? $this->_run_mod_handler('string_format', true, $_tmp, ', %0.1f%%') : smarty_modifier_string_format($_tmp, ', %0.1f%%')); ?>
</td>
				<td>&nbsp;</td>
				<td align="right"><?php echo $this->_tpl_vars['order']['order_discount_valueToShow']; ?>
</td>
			</tr>
<?php $this->assign('class', 'odd'); ?>			
<?php endif; ?>
<?php if ($this->_tpl_vars['order']['shipping_cost']): ?>
			<tr class="<?php echo $this->_tpl_vars['class']; ?>
">
				<td>&nbsp;</td>
				<td class="lefttext"><?php echo 'Стоимость доставки'; ?>
 (<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['order']['shipping_type'])) ? $this->_run_mod_handler('htmlsafe', true, $_tmp, true, true) : smarty_modifier_htmlsafe($_tmp, true, true)))) ? $this->_run_mod_handler('default', true, $_tmp, "&mdash;") : smarty_modifier_default($_tmp, "&mdash;")); ?>
)</td>
				<td>&nbsp;</td>
				<td class="nobr"><?php echo $this->_tpl_vars['order']['shipping_costToShow']; ?>
</td>
			</tr>
<?php $this->assign('class', 'odd'); ?>			
<?php endif; ?>
<?php if ($this->_tpl_vars['order']['tax']): ?>
			<tr class="<?php echo $this->_tpl_vars['class']; ?>
">
				<td>&nbsp;</td>
				<td class="lefttext"><?php echo 'Налог'; ?>
</td>
				<td>&nbsp;</td>
				<td class="nobr"><?php echo $this->_tpl_vars['order']['tax_toShow']; ?>
</td>
			</tr>
<?php endif; ?>
			<tr class="total">
				<td>&nbsp;</td>
				<td class="strongtext lefttext"><?php echo 'Итого'; ?>
</td>
				<td>&nbsp;</td>
				<td class="totalPrice"><?php echo $this->_tpl_vars['order']['order_amountToShow']; ?>
</td>
			</tr>
		</table>

		</td>
	</tr>
	<tr>
		<td>
		<div class="grey">
		<table border="0" cellpadding="0" cellspacing="0"
			class="invoiceHeader" width="100%">
			<tr>
				<td>&nbsp;</td>
				<td class="w2"><?php echo 'Оплата'; ?>
:</td>
				<td class="middeltext greytext inline_edit"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['order']['payment_type'])) ? $this->_run_mod_handler('htmlsafe', true, $_tmp, true, true) : smarty_modifier_htmlsafe($_tmp, true, true)))) ? $this->_run_mod_handler('default', true, $_tmp, "&nbsp;") : smarty_modifier_default($_tmp, "&nbsp;")); ?>
</td>
			</tr>

			<tr>
				<td>&nbsp;</td>
				<td class="w2"><?php echo 'Оплачено'; ?>
:</td>
				<td class="middeltext greytext inline_edit"><?php echo $this->_tpl_vars['order']['paid_date']; ?>
</td>
			</tr>
		</table>
		</div>
		</td>
	</tr>
</table>
</body>
</html>