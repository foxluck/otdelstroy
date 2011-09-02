<?php /* Smarty version 2.6.26, created on 2011-09-01 05:31:20
         compiled from backend/order_detailed.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'backend/order_detailed.html', 14, false),array('modifier', 'set_query', 'backend/order_detailed.html', 21, false),array('modifier', 'transcape', 'backend/order_detailed.html', 44, false),array('modifier', 'set_query_html', 'backend/order_detailed.html', 44, false),array('modifier', 'translate', 'backend/order_detailed.html', 73, false),array('modifier', 'replace', 'backend/order_detailed.html', 73, false),array('modifier', 'cat', 'backend/order_detailed.html', 94, false),array('modifier', 'lower', 'backend/order_detailed.html', 105, false),array('modifier', 'string_format', 'backend/order_detailed.html', 278, false),array('modifier', 'nl2br', 'backend/order_detailed.html', 282, false),array('function', 'cycle', 'backend/order_detailed.html', 264, false),)), $this); ?>
<script src='../../../common/html/res/ext/pr-prototype.js' type="text/javascript"></script>
<script src='../../../common/html/res/ext/pr-adapter.js' type="text/javascript"></script>
<script src='../../../common/html/res/ext/pr-effects.js' type="text/javascript"></script>
<script src='../../../common/html/res/ext/ext-all.js' type="text/javascript"></script>
<script type="text/javascript" src="../../../common/html/cssbased/domready.js"></script>
<link rel='stylesheet' type='text/css' href='../../../common/html/res/ext/resources/css/sc-my-ext-all.css'>
<link rel='stylesheet' type='text/css' href='../../../common/html/res/ext/resources/css/xtheme-slate.css'>
<link rel='stylesheet' type='text/css' href='../../../common/html/res/ext/resources/css/menu.css'>
<link rel='stylesheet' type='text/css' href='../../../common/html/res/ext/resources/css/layout.css'>
<script type="text/javascript">Ext.BLANK_IMAGE_URL = '../../../common/html/res/ext/resources/images/default/s.gif'</script>

<script type='text/javascript' src='<?php echo @URL_JS; ?>
/widget_checkout.js'></script>
<script type="text/javascript">
	var conf_full_shop_url = "<?php echo ((is_array($_tmp=@CONF_FULL_SHOP_URL)) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
";
</script>

	<h1 class="breadcrumbs"><a href='<?php echo $this->_tpl_vars['olist_url']; ?>
'><?php echo 'Список заказов'; ?>
</a>
	&raquo;
	<?php echo 'Заказ'; ?>
 #<?php echo $this->_tpl_vars['order']['orderID_view']; ?>
 &ndash; <span style="<?php echo $this->_tpl_vars['order']['status_style']; ?>
"><?php echo $this->_tpl_vars['order']['status_name']; ?>
</span>
	&nbsp;
	<input value="<?php echo 'Версия для печати'; ?>
" rel='<?php echo ((is_array($_tmp="?ukey=invoice&orderID=".($this->_tpl_vars['order']['orderID'])."&lang_iso2=".($this->_tpl_vars['invoice_lang'])."&furl_enable=1")) ? $this->_run_mod_handler('set_query', true, $_tmp) : smarty_modifier_set_query($_tmp)); ?>
' type="button" class="new_window" wnd_width='700' wnd_height='500' />
	</h1>
	
	<p>
		<?php echo 'Время заказа'; ?>
 <?php echo $this->_tpl_vars['order']['order_time']; ?>
 (<?php echo 'IP покупателя'; ?>
: <?php echo $this->_tpl_vars['order']['customer_ip']; ?>
)
	</p>

<?php echo $this->_tpl_vars['MessageBlock']; ?>


    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <colgroup>
            <col width="10%" />
            <col width="10%" />
            <col width="80%" />
        </colgroup>
        <tr>
            <td>
                                    	<p id="ord_orderactions">
                    <table border="0" cellpadding="0" cellspacing="0">
                    <tr>
                	<?php $_from = $this->_tpl_vars['order_actions']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['status_list'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['status_list']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['_action']):
        $this->_foreach['status_list']['iteration']++;
?>
                    <td>
                	<input value="<?php echo ((is_array($_tmp=$this->_tpl_vars['_action']['name'])) ? $this->_run_mod_handler('transcape', true, $_tmp) : smarty_modifier_transcape($_tmp)); ?>
" rel_source='<?php echo ((is_array($_tmp="action=exec_order_action&order_action_id=".($this->_tpl_vars['_action']['id']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
' rel='<?php echo ((is_array($_tmp="action=exec_order_action&order_action_id=".($this->_tpl_vars['_action']['id']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
' type="button" class="goto <?php if ($this->_tpl_vars['_action']['confirm']): 
 echo $this->_tpl_vars['_action']['confirm']; 
 endif; ?>" title="<?php echo ((is_array($_tmp=$this->_tpl_vars['_action']['confirm'])) ? $this->_run_mod_handler('transcape', true, $_tmp) : smarty_modifier_transcape($_tmp)); ?>
" style="<?php echo $this->_tpl_vars['_action']['_style']; ?>
;font-weight:bold;" onpreclick="obj=document.getElementById('change_status_comment');obj2=document.getElementById('notify_customer');if(obj&&obj2&&obj2.checked)this.setAttribute('rel',this.getAttribute('rel_source')+'&amp;status_comments='+obj.value+'&amp;notify_customer=1');"/>
                    </td>
                	<?php endforeach; endif; unset($_from); ?>
                	<?php if ($this->_tpl_vars['custom_order_statuses']): ?>
                    <td>
                	<input value="<?php echo 'Произвольный статус...'; ?>
" rel='ord_change_status_block' type="button" class="fade_div" wnd_width="300" wnd_height="260" />
                    </td>
                	<?php endif; ?>
                    
                    </tr>
                    <tr>
                    <td colspan="<?php echo $this->_foreach['status_list']['total']+1; ?>
">
                    	<label><input id="notify_customer" type="checkbox" onchange="JavaScript:obj=document.getElementById('change_status_comment_cnt');if(obj)obj.style.display=this.checked?'block':'none';"><?php echo 'Уведомить покупателя об этом изменении по email'; ?>
</label>
                    	<div id="change_status_comment_cnt" style="display:none;margin-left:10px;">
                    	<?php echo 'Добавить комментарий в сообщение'; ?>
<br>
                   		<textarea rows="3" cols="40" id="change_status_comment"></textarea>
                   		</div>
                    </td>
                    </tr>
                    </table>
                	</p>
            </td>
            <td style="padding-left: 20px;padding-top: 20px;" valign="top">
              	<input value="<?php echo 'Добавить комментарий...'; ?>
" rel='ord_add_comment_block' type="button" class="fade_div" wnd_width="450" wnd_height="250" style="margin-right: 20px;"/>
          	</td>
          	<td style="padding-left: 10px;">
                <?php if ($this->_tpl_vars['order']['statusID'] == @CONF_ORDSTATUS_CANCELLED || $this->_tpl_vars['order']['statusID'] == @CONF_ORDSTATUS_DELIVERED || $this->_tpl_vars['order']['statusID'] == @CONF_ORDSTATUS_REFUNDED): ?>
                    <div style="color: #777777; font-size: 80%;width:140px;"><?php echo ((is_array($_tmp=((is_array($_tmp='msg_cant_edit_order')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)))) ? $this->_run_mod_handler('replace', true, $_tmp, "%0%", $this->_tpl_vars['order']['status_name']) : smarty_modifier_replace($_tmp, "%0%", $this->_tpl_vars['order']['status_name'])); ?>
</span>
                <?php else: ?>
                    <a href="<?php echo $this->_tpl_vars['edit_url']; ?>
"><?php echo 'Редактировать заказ'; ?>
</a>
                <?php endif; ?>
            </td>
        </tr>
    </table>

<div style="padding-top: 14px;"></div>
<table  cellpadding="0" cellspacing="0">
<colgroup>
    <col width="50%" />
    <col width="50%" />
</colgroup>
<tr>
<td><h3><?php echo 'Информация о пользователе'; ?>
</h3></td>
<td><h3><?php echo 'Печатные формы'; ?>
</h3></td>
</tr>
<tr>
<td valign="top">
<?php $this->assign('customer_full_name', ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['order']['customer_firstname'])) ? $this->_run_mod_handler('cat', true, $_tmp, ' ') : smarty_modifier_cat($_tmp, ' ')))) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['order']['customer_lastname']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['order']['customer_lastname']))); ?>
<table>
<colgroup>
    <col width="10%" />
    <col width="90%" />
</colgroup>

<tr>
    <td valign="top" style="padding: 2px;"><?php echo 'Покупатель'; ?>
:</td>
    <td style="padding: 2px;">
        <a href="index.php?ukey=user_info&amp;userID=<?php echo $this->_tpl_vars['order']['customerID']; ?>
"><span id="ord_customer_name"><?php echo ((is_array($_tmp=$this->_tpl_vars['customer_full_name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</span></a>
        <span style="color: #666666;">(<?php echo ((is_array($_tmp=((is_array($_tmp='usr_custinfo_login')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)))) ? $this->_run_mod_handler('lower', true, $_tmp) : smarty_modifier_lower($_tmp)); ?>
:&nbsp;<?php if ($this->_tpl_vars['customer_login']): ?><strong><?php echo $this->_tpl_vars['customer_login']; ?>
</strong><?php else: 
 echo 'нет'; 
 endif; ?>)</span>
        <br />
        <?php if (@CONF_BACKEND_SAFEMODE == 0): 
 echo 'Email'; ?>
:&nbsp;<a href="mailto:<?php echo ((is_array($_tmp=$this->_tpl_vars['order']['customer_email'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['order']['customer_email'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</a>,&nbsp;<?php else: ?><b><?php echo 'Заблокировано к показу в защищенном режиме'; ?>
</b><?php endif; ?>
        <?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['order']['reg_fields_values']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
        <br><?php echo ((is_array($_tmp=$this->_tpl_vars['order']['reg_fields_values'][$this->_sections['i']['index']]['reg_field_name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['order']['reg_fields_values'][$this->_sections['i']['index']]['reg_field_value'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
        <?php endfor; endif; ?>
    </td>
</tr>
<?php if ($this->_tpl_vars['order']['customers_comment']): ?>
<tr>
	<td valign="top" style="padding: 2px;"><?php echo 'Комментарий'; ?>
:</td>
	<td style="padding: 2px;font-weight:bold;font-style:italic;"><?php echo ((is_array($_tmp=$this->_tpl_vars['order']['customers_comment'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</td>
</tr>
<?php endif; ?>
<tr>
	<td style="padding: 2px;"><?php echo 'Источник'; ?>
:</td>
	<td style="padding: 2px;"><?php echo ((is_array($_tmp="ordr_source_".($this->_tpl_vars['order']['source']))) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</td>
</tr>

</table>
</td>
			
	<td style="padding-right: 15px;" valign="top">
<?php if ($this->_tpl_vars['print_forms']): ?>
		
		<form>
		<table>
			<?php $_from = $this->_tpl_vars['print_forms']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['print_form_class'] => $this->_tpl_vars['print_form']):
?>
			<tr>
				<td>
					<input onload ="initCheckboxState(this);" onclick="storeCheckboxState(this.name,this.checked);" class="printforms" type="checkbox" checked="checked" id="print_<?php echo $this->_tpl_vars['print_form_class']; ?>
" name="printforms[<?php echo $this->_tpl_vars['print_form_class']; ?>
]" 
						value="<?php echo ((is_array($_tmp="?ukey=admin_print_form&orderID=&form_class=".($this->_tpl_vars['print_form_class']))) ? $this->_run_mod_handler('set_query', true, $_tmp) : smarty_modifier_set_query($_tmp)); ?>
">
				</td>
				<td>
					<nobr>
					<label for="print_<?php echo $this->_tpl_vars['print_form_class']; ?>
"><?php echo $this->_tpl_vars['print_form']['name']; ?>
</label>
										</nobr>
				</td>
			</tr>
			<?php endforeach; endif; unset($_from); ?>

			<tr><td colspan="2" align="left"><input id="printforms" onclick="show_printforms();" type="button" value="<?php echo 'Печать'; ?>
"></td></tr>
		</table>
		</form>
<script type="text/javascript">
<?php echo '
function show_printforms()
{
	var boxes = getElementsByClass(\'printforms\', document, \'input\');
	var win = "menubar=no,location=no,resizable=yes,scrollbars=yes";
	for(var i_max = boxes.length-1; i_max>=0; i_max--){
		if(boxes[i_max].checked){
			window.open(boxes[i_max].value, \'printableWin\'+i_max, win);
		}
	}
}
function storeCheckboxState(name,value)
{
	setCookie(\'xPOST[\'+name+\']\',value,90/*3 month*/);
}

function initCheckboxState()
{
	var boxes = getElementsByClass(\'printforms\', document, \'input\');
	var value;
	for(var i_max = boxes.length-1; i_max>=0; i_max--){
		value = getCookie(\'xPOST[\'+boxes[i_max].name+\']\');
		boxes[i_max].checked = (value==\'false\')?false:true;
	}
}

initCheckboxState();

'; ?>

</script>		
		<?php else: ?>
		&lt;<?php echo 'пустой список'; ?>
&gt;
		<?php endif; ?>
	</td></tr>
<tr>
<td><h3><?php echo 'Доставка'; ?>
 &mdash; <?php echo ((is_array($_tmp=$this->_tpl_vars['order']['shipping_type'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); 
 if ($this->_tpl_vars['order']['shippingServiceInfo']): ?> (<?php echo ((is_array($_tmp=$this->_tpl_vars['order']['shippingServiceInfo'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
)<?php endif; ?></h3></td>
<td><h3><?php echo 'Оплата'; ?>
 &mdash; <?php echo ((is_array($_tmp=$this->_tpl_vars['order']['payment_type'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</h3></td>
</tr>
<tr>
	<td style="padding-right: 15px;" valign="top">
		
		
		<?php echo 'Получатель'; ?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['order']['shipping_firstname'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['order']['shipping_lastname'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>

		<br />
		<?php if ($this->_tpl_vars['order']['shipping_address'] != ""): 
 echo ((is_array($_tmp=$this->_tpl_vars['order']['shipping_address'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
<br /><?php endif; ?>
		<?php if ($this->_tpl_vars['order']['shipping_city'] != ""): 
 echo ((is_array($_tmp=$this->_tpl_vars['order']['shipping_city'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); 
 endif; ?>
		<?php if ($this->_tpl_vars['order']['shipping_state'] != ""): 
 echo ((is_array($_tmp=$this->_tpl_vars['order']['shipping_state'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); 
 endif; ?>
		<?php if ($this->_tpl_vars['order']['shipping_zip'] != ""): 
 echo ((is_array($_tmp=$this->_tpl_vars['order']['shipping_zip'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
<br /><?php endif; ?>
		<?php if ($this->_tpl_vars['order']['shipping_country'] != ""): 
 echo ((is_array($_tmp=$this->_tpl_vars['order']['shipping_country'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); 
 endif; ?>
        <?php if (! ( $this->_tpl_vars['order']['shipping_address'] == '' && $this->_tpl_vars['order']['shipping_city'] == '' )): ?>
        <br />
            <a href="javascript: void(0);" id="sa_lookup" style="font-size: 90%"><?php echo 'Показать на карте'; ?>
</a>
        <?php endif; ?>
	</td>
	<td style="padding-right: 15px;" valign="top">
	
		
		<?php echo 'Плательщик'; ?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['order']['billing_firstname'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['order']['billing_lastname'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>

		<br />
		<?php if ($this->_tpl_vars['order']['billing_address'] != ""): 
 echo ((is_array($_tmp=$this->_tpl_vars['order']['billing_address'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
<br /><?php endif; ?>
		<?php if ($this->_tpl_vars['order']['billing_city'] != ""): 
 echo ((is_array($_tmp=$this->_tpl_vars['order']['billing_city'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); 
 endif; ?>
		<?php if ($this->_tpl_vars['order']['billing_state'] != ""): 
 echo ((is_array($_tmp=$this->_tpl_vars['order']['billing_state'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); 
 endif; ?>
		<?php if ($this->_tpl_vars['order']['billing_zip'] != ""): 
 echo ((is_array($_tmp=$this->_tpl_vars['order']['billing_zip'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
<br /><?php endif; ?>
		<?php if ($this->_tpl_vars['order']['billing_country'] != ""): 
 echo ((is_array($_tmp=$this->_tpl_vars['order']['billing_country'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); 
 endif; ?>
        <?php if (! ( $this->_tpl_vars['order']['billing_address'] == '' && $this->_tpl_vars['order']['billing_city'] == '' )): ?>
        <br />
            <a href="javascript: void(0);" id="ba_lookup" style="font-size: 90%"><?php echo 'Показать на карте'; ?>
</a>
		<?php endif; ?>
		<?php if ($this->_tpl_vars['order']['cc_number'] || $this->_tpl_vars['order']['cc_holdername'] || $this->_tpl_vars['order']['cc_expires'] || $this->_tpl_vars['order']['cc_expires']): ?>
		<p>
			<strong><?php echo 'Информация о кредитной карте'; ?>
</strong>
			<br />

			<?php if (true): ?>				<table>
				<tr>
					<td><?php echo 'Номер кредитной карты'; ?>
: <b><?php echo ((is_array($_tmp=$this->_tpl_vars['order']['cc_number'])) ? $this->_run_mod_handler('replace', true, $_tmp, "<", "&lt;") : smarty_modifier_replace($_tmp, "<", "&lt;")); ?>
</b></td>
				</tr>
				<tr>
					<td><?php echo 'Держатель карты'; ?>
: <b><?php echo ((is_array($_tmp=$this->_tpl_vars['order']['cc_holdername'])) ? $this->_run_mod_handler('replace', true, $_tmp, "<", "&lt;") : smarty_modifier_replace($_tmp, "<", "&lt;")); ?>
</b></td>
				</tr>
				<tr>
					<td><?php echo 'Истекает'; ?>
: <b><?php echo ((is_array($_tmp=$this->_tpl_vars['order']['cc_expires'])) ? $this->_run_mod_handler('replace', true, $_tmp, "<", "&lt;") : smarty_modifier_replace($_tmp, "<", "&lt;")); ?>
</b></td>
				</tr>
				<tr>
					<td><?php echo 'CVV'; ?>
: <b><?php echo ((is_array($_tmp=$this->_tpl_vars['order']['cc_cvv'])) ? $this->_run_mod_handler('replace', true, $_tmp, "<", "&lt;") : smarty_modifier_replace($_tmp, "<", "&lt;")); ?>
</b></td>
				</tr>
				</table>
			<?php else: ?>
			
			<?php echo 'Эта информация доступна только при защищенном соединении (SSL). Для доступа к этой информации выйдите из аккаунта, и войдите вновь, используя безопасное SSL соединение (нужно включить соответствующую галочку).'; ?>

			<?php endif; ?>
		<?php endif; ?>
	</td>

</tr>
</table>

<br />

<table class="grid" id="ord_order_content">

<tr class="gridsheader"> 
	<td><?php echo 'Наименование'; ?>
</td>
	<td nowrap="nowrap" align="center"><?php echo 'Кол-во'; ?>
</td>
	<td nowrap="nowrap" align="right"><?php echo 'Стоимость (без налога)'; ?>
</td>
</tr>
<?php if ($this->_tpl_vars['orderContent']): ?>
<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['orderContent']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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

<tr class="<?php echo smarty_function_cycle(array('values' => 'gridline,gridline1','name' => 'ord_content'), $this);?>
"> 
   	<td ><?php echo ((is_array($_tmp=$this->_tpl_vars['orderContent'][$this->_sections['i']['index']]['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</td>
	<td align="center"><?php echo $this->_tpl_vars['orderContent'][$this->_sections['i']['index']]['Quantity']; ?>
</td>
	<td align="right"><?php echo $this->_tpl_vars['orderContent'][$this->_sections['i']['index']]['PriceToShow']; ?>
</td>
</tr>
<?php endfor; endif; ?>
<?php else: ?>
<tr>
	<td colspan="3" style="color: #777777"><?php echo 'В этом заказе нет ни одного продукта.'; ?>
</td>
<?php endif; ?>
<tr class="gridsfooter"><td colspan="3">&nbsp;</td>

<?php if ($this->_tpl_vars['order']['order_discount'] || $this->_tpl_vars['order']['discount_description'] != ''): ?>
<tr>
	<td colspan="2"><?php echo 'Скидка, %'; 
 echo ((is_array($_tmp=$this->_tpl_vars['order']['order_discount_percent'])) ? $this->_run_mod_handler('string_format', true, $_tmp, ', %0.1f%%') : smarty_modifier_string_format($_tmp, ', %0.1f%%')); ?>
</td>
	<td align="right"><?php echo $this->_tpl_vars['order']['order_discount_valueToShow']; ?>
</td>
</tr>
<tr>
    <td colspan="3"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['order']['discount_description'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</td>
</tr>
<?php endif; ?>
<?php if ($this->_tpl_vars['order']['shipping_cost'] || $this->_tpl_vars['order']['tax']): ?>
<tr>
	<td colspan="2"><?php echo 'Подытог'; ?>
</td>
	<td align="right"><?php echo $this->_tpl_vars['order']['clear_total_priceToShow']; ?>
</td>
</tr>
<?php endif; ?>
<?php if ($this->_tpl_vars['order']['shipping_cost']): ?>
<tr>
	<td colspan="2"><?php echo 'Стоимость доставки'; ?>
</td>
	<td align="right"><?php echo $this->_tpl_vars['order']['shipping_costToShow']; ?>
</td>
</tr>
<?php endif; ?>
<?php if ($this->_tpl_vars['order']['tax']): ?>
<tr>
	<td colspan="2"><?php echo 'Налог'; ?>
</td>
	<td align="right"><?php echo $this->_tpl_vars['order']['tax_toShow']; ?>
</td>
</tr>
<?php endif; ?>
<tr id="ord_total_row">
	<td colspan="2"><?php echo 'Итого'; ?>
</td>
	<td align="right"><?php echo $this->_tpl_vars['order']['order_amountToShow']; ?>
</td>
</tr>
</table>

<?php if ($this->_tpl_vars['order_status_report']): ?>

<h3><?php echo 'История работы с заказом'; ?>
</h3>

<table class="grid">

<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['order_status_report']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
<tr class="<?php echo smarty_function_cycle(array('values' => 'gridline,gridline1'), $this);?>
">
	<td nowrap="nowrap">
		<?php echo $this->_tpl_vars['order_status_report'][$this->_sections['i']['index']]['status_change_time']; ?>

	</td>
	<td>
		<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['order_status_report'][$this->_sections['i']['index']]['status_comment'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>

	</td>
</tr>
<?php endfor; endif; ?>

</table>
<?php endif; ?>

<?php if ($this->_tpl_vars['custom_order_statuses']): ?>
<div id="ord_change_status_block">
<form action="<?php echo ((is_array($_tmp='')) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
" method="post">

	<input name="orderID" value="<?php echo $this->_tpl_vars['order']['orderID']; ?>
" type="hidden">
	<input name="action" value="set_custom_status" type="hidden">
	<?php echo 'Произвольный статус...'; ?>

	<p>
	<select name='statusID' class="div_fade_select">
		<option value='-1'><?php echo 'Пожалуйста, выберите'; ?>
</option>
		<?php $_from = $this->_tpl_vars['custom_order_statuses']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['_status']):
?>
		<option value='<?php echo $this->_tpl_vars['_status']['statusID']; ?>
'><?php echo $this->_tpl_vars['_status']['status_name']; ?>
</option>					
		<?php endforeach; endif; unset($_from); ?>
	</select>
	</p>
	<p>
	<label><input name ="notify_customer" type="checkbox" value="1" onchange="JavaScript:obj=document.getElementById('change_custom_status_comment');if(obj)obj.style.display=this.checked?'block':'none';"><?php echo 'Уведомить покупателя об этом изменении по email'; ?>
</label>
                    	<div id="change_custom_status_comment" style="display:none;margin-left:10px;">
                    	<?php echo 'Добавить комментарий в сообщение'; ?>
<br>
                   		<textarea name="status_comments" rows="2" cols="30"></textarea>
                   		</div>
  </p>
	<input value="<?php echo 'Изменить'; ?>
" type="submit" >

</form>
</div>
<?php endif; ?>

<div id="ord_add_comment_block">
	<form action="<?php echo ((is_array($_tmp='')) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
" method="post">
	<input name="orderID" value="<?php echo $this->_tpl_vars['order']['orderID']; ?>
" type="hidden" />
	<input name="action" value="post_comment" type="hidden" />
	
	<?php echo 'Добавить заметку в историю обработки заказа'; ?>

	<p>
	<textarea name="comment" cols="50" rows="4"  maxlength="255"></textarea>
	</p>
	<p><label><input type="checkbox" name="notify_customer" value="1"><?php echo 'Уведомить покупателя об этом изменении по email'; ?>
</label></p>
	<input value="<?php echo 'Добавить'; ?>
" type="submit" />
	</form>
</div>

<script type="text/javascript" language="JavaScript">

Ext.onReady(function(){
    var s_button = Ext.get('sa_lookup');
    var b_button = Ext.get('ba_lookup');

    if(s_button)
    {
        s_button.on('click', function(){
            var addr = '<?php echo ((is_array($_tmp=$this->_tpl_vars['order']['shipping_country'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['order']['shipping_city'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['order']['shipping_address_js'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
';
            addr = addr.replace("\n", " ");
            showMapWindow(addr);
        });
    };
    
    if(b_button)
    {
        b_button.on('click', function(){
            var addr = '<?php echo ((is_array($_tmp=$this->_tpl_vars['order']['billing_country'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['order']['billing_city'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['order']['billing_address_js'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
';
            addr = addr.replace("\n", "");
            showMapWindow(addr);
        });
    };
});

</script>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "backend/google_api/gmaps_ext_popup.html", 'smarty_include_vars' => array('map_win_name' => 'sa_addr_win','map_canvas_name' => 'sa_map_canvas')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>