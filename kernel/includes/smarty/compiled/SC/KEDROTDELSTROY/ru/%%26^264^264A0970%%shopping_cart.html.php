<?php /* Smarty version 2.6.26, created on 2011-08-31 17:14:05
         compiled from shopping_cart.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'translate', 'shopping_cart.html', 7, false),array('modifier', 'set_query_html', 'shopping_cart.html', 10, false),array('modifier', 'set_query', 'shopping_cart.html', 17, false),array('modifier', 'escape', 'shopping_cart.html', 63, false),array('modifier', 'string_format', 'shopping_cart.html', 105, false),array('modifier', 'default', 'shopping_cart.html', 166, false),array('function', 'cycle', 'shopping_cart.html', 62, false),)), $this); ?>
<script type="text/javascript" src="<?php echo @URL_JS; ?>
/JsHttpRequest.js"></script>
<div id="blck-content">	
<script type="text/javascript" src="<?php echo @URL_JS; ?>
/JsHttpRequest.js"></script>
<table cellpadding="0" cellspacing="0" width="100%">
<tr>
	<td <?php if ($this->_tpl_vars['PAGE_VIEW'] == 'noframe'): ?>class="background_cart_top"<?php endif; ?> id="cart_page_title">
		<h1><?php echo ((is_array($_tmp=$this->_tpl_vars['CurrentDivision']['name'])) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</h1>
	<?php if ($this->_tpl_vars['cart_content'] && ! $this->_tpl_vars['widget_view']): ?>
	<div>
		<a id="my_closeLink" href='<?php echo ((is_array($_tmp="?ukey=home&view=frame")) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
' target="_parent">
			<?php echo '&laquo; вернуться к покупкам'; ?>

		</a>
	</div>
	<?php endif; ?>
	</td>
	<?php if ($this->_tpl_vars['cart_content']): ?>
	<td <?php if ($this->_tpl_vars['PAGE_VIEW'] == 'noframe'): ?>class="background_cart_top"<?php endif; ?> id="cart_clear"><a href='<?php echo ((is_array($_tmp="?ukey=cart&view&clear_cart=yes")) ? $this->_run_mod_handler('set_query', true, $_tmp) : smarty_modifier_set_query($_tmp)); ?>
'><?php echo 'Очистить корзину'; ?>
</a>
	</td>
	<?php endif; ?>
</tr>
</table>

<div <?php if ($this->_tpl_vars['PAGE_VIEW'] == 'noframe'): ?>class="paddingblock"<?php endif; ?>>

<?php echo $this->_tpl_vars['MessageBlock']; ?>



<?php if ($this->_tpl_vars['cart_content']): ?>


	<?php if ($this->_tpl_vars['make_more_exact_cart_content']): ?>
	<p><?php echo 'В вашей корзине обнаружены продукты, добавленные при предыдущем пользовании нашего магазина. Пожалуйста, уточните содержимое заказа перед оформлением.'; ?>
</p>
	<?php endif; ?>
	<?php if ($this->_tpl_vars['cart_amount'] < @CONF_MINIMAL_ORDER_AMOUNT & $this->_tpl_vars['cart_error_show'] == 1 & ! $this->_tpl_vars['MessageBlock']): ?>
	<div class='error_block'><span class="error_flag"><?php echo 'Сумма заказа должна быть не менее '; ?>
 <?php echo $this->_tpl_vars['cart_min']; ?>
</span></div>
	<?php endif; ?>
	
	<form action="<?php echo ((is_array($_tmp='?ukey=cart&view')) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
" name="ShoppingCartForm" method="post" target="_self">
	<input type="hidden" name="update" value="1" >
	<input type="hidden" name="shopping_cart" value="1" >
	
	<table id="cart_content_tbl" cellspacing="0">
    <colgroup>
        <col width="10%" />
        <col width="50%" />
        <col width="15%" />
        <col width="20%" />
        <col width="5%" />
    </colgroup>
	<tr id="cart_content_header">
		<td></td>
		<td></td>
		<td align="center"><?php echo 'Кол-во'; ?>
</td>
		<td align="center"><?php echo 'Стоимость'; ?>
</td>
		<td></td>
	</tr>

	<?php $this->assign('ProductsNum', 0); ?>
	<?php unset($this->_sections['i']);
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['cart_content']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['name'] = 'i';
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

	<tr class='row_<?php echo smarty_function_cycle(array('values' => "odd,even"), $this);?>
'>
		<td align="center" valign="top" width="1%"><?php if ($this->_tpl_vars['cart_content'][$this->_sections['i']['index']]['thumbnail_url']): ?><img src="<?php echo ((is_array($_tmp=$this->_tpl_vars['cart_content'][$this->_sections['i']['index']]['thumbnail_url'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" width="<?php echo $this->_tpl_vars['cart_content'][$this->_sections['i']['index']]['thumbnail_width']; ?>
" /><?php else: ?>&nbsp;<?php endif; ?></td>
		<td>
			<?php if (! $this->_tpl_vars['widget_view']): ?>
			<?php if (@CONF_ENABLE_PRODUCT_SKU && $this->_tpl_vars['cart_content'][$this->_sections['i']['index']]['product_code']): ?><i><?php echo $this->_tpl_vars['cart_content'][$this->_sections['i']['index']]['product_code']; ?>
</i> <?php endif; ?>
			<a href='<?php echo ((is_array($_tmp="?ukey=product&productID=".($this->_tpl_vars['cart_content'][$this->_sections['i']['index']]['productID'])."&product_slug=".($this->_tpl_vars['cart_content'][$this->_sections['i']['index']]['slug']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
' <?php if ($this->_tpl_vars['PAGE_VIEW'] == 'noframe'): ?>class="gofromfade"<?php endif; ?>><?php echo $this->_tpl_vars['cart_content'][$this->_sections['i']['index']]['name']; ?>
</a>
			<?php else: ?>
			<?php if (@CONF_ENABLE_PRODUCT_SKU && $this->_tpl_vars['cart_content'][$this->_sections['i']['index']]['product_code']): ?><i><?php echo $this->_tpl_vars['cart_content'][$this->_sections['i']['index']]['product_code']; ?>
</i> <?php endif; ?>
			<?php echo $this->_tpl_vars['cart_content'][$this->_sections['i']['index']]['name']; ?>

			<?php endif; ?>
			<?php if ($this->_tpl_vars['cart_content'][$this->_sections['i']['index']]['brief_description']): ?><div class="cart_product_brief_description"><?php echo $this->_tpl_vars['cart_content'][$this->_sections['i']['index']]['brief_description']; ?>
</div><?php endif; ?>
		</td>
		<td align="center">
			<?php $this->assign('ProductsNum', $this->_tpl_vars['ProductsNum']+$this->_tpl_vars['cart_content'][$this->_sections['i']['index']]['quantity']); ?>

			<?php if ($this->_tpl_vars['session_items']): 
 $this->assign('_prdid', $this->_tpl_vars['session_items'][$this->_sections['i']['index']]); ?>
			<?php else: 
 $this->assign('_prdid', $this->_tpl_vars['cart_content'][$this->_sections['i']['index']]['id']); ?>
			<?php endif; ?>
			
			<input class="cart_product_quantity digit" type="text" maxlength="10" name="count_<?php echo $this->_tpl_vars['_prdid']; ?>
" value="<?php echo $this->_tpl_vars['cart_content'][$this->_sections['i']['index']]['quantity']; ?>
" size="5" >
			
			<?php if ($this->_tpl_vars['cart_content'][$this->_sections['i']['index']]['min_order_amount']): ?>
			<div class="error_block"><span class="error_msg" style="font-size:smaller">
				<?php echo 'Минимальный заказ'; ?>
 
				<?php echo $this->_tpl_vars['cart_content'][$this->_sections['i']['index']]['min_order_amount']; ?>
 
				<?php echo 'шт.'; ?>

			</span></div>
			<?php endif; ?>
		</td>
		<td align="center" nowrap="nowrap">
			<?php echo $this->_tpl_vars['cart_content'][$this->_sections['i']['index']]['cost']; ?>

		</td>
		<td align="center">
			<a href='<?php echo ((is_array($_tmp="?ukey=cart&view&remove=".($this->_tpl_vars['_prdid']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
' title='<?php echo 'Удалить'; ?>
'>
			<img src="<?php echo @URL_IMAGES; ?>
/remove.gif" alt='<?php echo 'Удалить'; ?>
' />
			</a>
		</td>
	</tr>
	<?php endfor; endif; ?>

    <?php if ($this->_tpl_vars['cart_discount'] != ''): ?>
    <tr>
        <td colspan="3" class="cart_discount_label">
            <?php echo 'Скидка, %'; ?>
,&nbsp;<?php echo ((is_array($_tmp=$this->_tpl_vars['discount_percent'])) ? $this->_run_mod_handler('string_format', true, $_tmp, '%0.1f%%') : smarty_modifier_string_format($_tmp, '%0.1f%%')); ?>

        </td>
        <td align="center" nowrap="nowrap">
            - <span id="discount_value"><?php echo $this->_tpl_vars['cart_discount']; ?>
</span>    
        </td>
        <td></td>
    </tr>
    <?php endif; ?>

    <?php if (@CONF_DSC_COUPONS_ENABLED == 'Y'): ?>
    	<tr id="coupon_form" style="display: <?php if ($this->_tpl_vars['current_coupon'] != '0'): ?>none<?php endif; ?>;">
    		<td colspan="3" class="cart_discount_label">
    			<?php echo 'Купон на скидку (если есть)'; ?>
:
                <input type="text" size="12" maxlength="10" name="discount_coupon_code" id="discount_coupon_code" value="" onBlur="onApplyButtonClick();" onkeypress="return noenter(event);" >
                <button type="button" onClick="onApplyButtonClick();"  tabindex="1001"><?php echo 'Применить'; ?>
</button>
    		</td>
    		<td align="center">
                <span id="wrong_coupon_lbl" style="color: #666666; font-size: 80%; display: none;"><?php echo 'Купон не найден'; ?>
</span>
                <b id="processing_coupon_lbl" style="color: blue; display: none;"><?php echo 'Проверка'; ?>
</b>
            </td>
    	</tr>
        <tr id="coupon_info" style="display: <?php if ($this->_tpl_vars['current_coupon'] == '0'): ?>none<?php endif; ?>;">
            <td colspan="3" class="cart_discount_label">
                <?php echo 'Купон на скидку (если есть)'; ?>
:
                <b id="coupon_info_code"><?php echo $this->_tpl_vars['current_coupon']; ?>
</b>
                <button type="button" onClick="onDeleteCouponClick();" tabindex="1002"><?php echo 'Изменить'; ?>
</button>
            </td>
            <td align="center">
                - <span id="coupon_discount_value"><?php echo $this->_tpl_vars['coupon_discount']; ?>
</span>
            </td>
        </tr>
    <?php endif; ?>

    <?php if ($this->_tpl_vars['cart_discount'] == '' && @CONF_DSC_COUPONS_ENABLED == 'N'): ?>
    <tr style="height: 30px;"></tr>
    <?php endif; ?>

	<tr>
		<td id="cart_total_label" colspan="2">
			<?php echo 'Итого'; ?>

		</td>
		<td align="center">
			<input type="submit" name="recalculate" value='<?php echo 'Пересчитать'; ?>
' tabindex="1004" >
		</td>
		<td id="cart_total" align="center"><?php echo $this->_tpl_vars['cart_total']; ?>
</td>
		<td></td>
	</tr>
	
	<tr>
		<td colspan="5" align="right" id="cart_checkout_btn">
			<input type="submit" class="btn_checkout" name="checkout" value="<?php echo 'Оформить заказ'; ?>
" id="btn-checkout" type="submit" tabindex="1005" >
			
<!-- 			<?php if (( $this->_tpl_vars['GoogleCheckout_CheckoutButton'] || $this->_tpl_vars['PPExpressCheckout_button'] || $this->_tpl_vars['VKontakteCheckout_button'] ) && $this->_tpl_vars['PAGE_VIEW'] != 'mobile'): ?> Checkout replacement -->
			<p><?php echo '&mdash; или используйте &mdash;'; ?>
</p>
			<!-- <?php echo ' hack for vkontakte-->
			<style type="text/css">
				#checkout_replacements td {padding: 0;}
			</style>
			<!-- '; ?>
 -->
			<table id="checkout_replacements" style="padding: 0;">
			<tr>
				<td valign="middle"><?php echo ((is_array($_tmp=@$this->_tpl_vars['GoogleCheckout_CheckoutButton'])) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>
</td>
				<td valign="middle"><?php echo ((is_array($_tmp=@$this->_tpl_vars['PPExpressCheckout_button'])) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>
</td>
				<td valign="middle"><?php echo ((is_array($_tmp=@$this->_tpl_vars['VKontakteCheckout_button'])) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>
</td>
			</tr>
			</table>
<!--			<?php endif; ?> -->
		</td>
	</tr>
	</table>

	</form>
<?php else: ?>

	<p style="text-align: center;"><?php echo 'Ваша корзина пуста'; ?>
</p>
<?php endif; ?>
</div>

</div>

<script type="text/javascript" language="javascript">
<?php if ($this->_tpl_vars['PAGE_VIEW'] == 'noframe' && ! $_GET['external']): ?> 	
	<?php echo '
	function adjust_cart_window(){
		
		var wndSize = getWindowSize(parent);
		
		var scr_h = wndSize[1] - 100;
		var wnd_h = getLayer(\'blck-content\').offsetHeight + 85;
		parent.resizeFadeIFrame(null, Math.min(scr_h, wnd_h));
	}
	'; ?>

	adjust_cart_window();
	
	<?php if ($this->_tpl_vars['ProductsNum']): ?>
		parent.document.getElementById('shpcrtgc').innerHTML="<?php echo $this->_tpl_vars['ProductsNum']; ?>
 <?php echo 'продукт(ов)'; ?>
";
		parent.document.getElementById('shpcrtca').innerHTML='<?php echo $this->_tpl_vars['cart_total']; ?>
';
	<?php else: ?>
		parent.document.getElementById('shpcrtgc').innerHTML="<?php echo '(пусто)'; ?>
";
		parent.document.getElementById('shpcrtca').innerHTML="&nbsp;";
	<?php endif; ?>
<?php endif; ?>
		
	<?php if ($this->_tpl_vars['jsgoto']): ?>
		document.getElementById('btn-checkout').disabled = true;
		if (!top)closeFadeIFrame(true);
	    if (top)top.location = "<?php echo $this->_tpl_vars['jsgoto']; ?>
";
	    else document.location.href = "<?php echo $this->_tpl_vars['jsgoto']; ?>
";
	<?php endif; ?>

<?php echo '
function onApplyButtonClick()
{
    var coupon_code = document.getElementById(\'discount_coupon_code\').value;
    document.getElementById(\'wrong_coupon_lbl\').style.display = \'none\';
    document.getElementById(\'processing_coupon_lbl\').style.display = \'\';
    document.forms[\'ShoppingCartForm\'].recalculate.disabled = true;
    document.forms[\'ShoppingCartForm\'].checkout.disabled = true;
    
    var req = new JsHttpRequest();
    req.onreadystatechange = function()
    {
        if (req.readyState != 4)return;
        
        document.getElementById(\'processing_coupon_lbl\').style.display = \'none\';
        document.forms[\'ShoppingCartForm\'].recalculate.disabled = false;
        document.forms[\'ShoppingCartForm\'].checkout.disabled = false;
        if(req.responseJS.applied == \'N\')
        {
            document.getElementById(\'wrong_coupon_lbl\').style.display = \'\';
            return;
        };
        
        document.getElementById(\'coupon_form\').style.display = \'none\';
        document.getElementById(\'coupon_info\').style.display = \'\';
        document.getElementById(\'coupon_info_code\').innerHTML = coupon_code;
        document.getElementById(\'cart_total\').innerHTML = req.responseJS.new_total_show_value;
        '; 
 if ($this->_tpl_vars['PAGE_VIEW'] == 'noframe' && ! $_GET['external']): 
 echo '
            parent.document.getElementById(\'shpcrtca\').innerHTML = req.responseJS.new_total_show_value;
        '; 
 endif; 
 echo '
        if(req.responseJS.new_coupon_show != \'\')
        {
            document.getElementById(\'coupon_discount_value\').innerHTML = req.responseJS.new_coupon_show;
        };
    };
    
    try
    {
        req.open(null, set_query(\'&ukey=cart&caller=1&initscript=ajaxservice\'), true);
        req.send({\'action\': \'try_apply_discount_coupon\', \'coupon_code\': coupon_code});
    }
    catch ( e )
    {
      catchResult(e);
    }
    finally { ;}
};

function onDeleteCouponClick()
{
    var req = new JsHttpRequest();
    req.onreadystatechange = function()
    {
        if (req.readyState != 4)return;
        document.getElementById(\'coupon_form\').style.display = \'\';
        document.getElementById(\'wrong_coupon_lbl\').style.display = \'none\';
        document.getElementById(\'coupon_info\').style.display = \'none\';
        document.getElementById(\'discount_coupon_code\').value = document.getElementById(\'coupon_info_code\').innerHTML; 
        document.getElementById(\'cart_total\').innerHTML = req.responseJS.new_total_show_value;
        '; 
 if ($this->_tpl_vars['PAGE_VIEW'] == 'noframe' && ! $_GET['external']): 
 echo '
            parent.document.getElementById(\'shpcrtca\').innerHTML = req.responseJS.new_total_show_value;
        '; 
 endif; 
 echo '
    };
    
    try
    {
        req.open(null, set_query(\'&ukey=cart&caller=1&initscript=ajaxservice\'), true);
        req.send({\'action\': \'remove_doscount_coupon\'});
    }
    catch ( e )
    {
      catchResult(e);
    }
    finally { ;}
};

function noenter(event)
{
    if(event.keyCode == 13)
    {
        document.getElementById(\'discount_coupon_code\').blur();
        return false;
    };
};
 
'; ?>

</script>