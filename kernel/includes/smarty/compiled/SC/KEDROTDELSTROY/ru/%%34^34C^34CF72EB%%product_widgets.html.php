<?php /* Smarty version 2.6.26, created on 2011-09-01 08:16:22
         compiled from backend/product_widgets.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'translate', 'backend/product_widgets.html', 1, false),array('modifier', 'regex_replace', 'backend/product_widgets.html', 7, false),array('modifier', 'set_query_html', 'backend/product_widgets.html', 14, false),array('modifier', 'escape', 'backend/product_widgets.html', 16, false),array('modifier', 'set_query', 'backend/product_widgets.html', 51, false),array('modifier', 'replace', 'backend/product_widgets.html', 66, false),)), $this); ?>
<h1><?php echo ((is_array($_tmp=$this->_tpl_vars['CurrentDivision']['name'])) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</h1>

<?php echo 'Здесь вы найдете инструменты, с помощью которых сможете <strong>превратить ваш любой веб-сайт или блог в интернет-магазин</strong> &mdash; будь то веб-сайт со сложной системой управления, веб-сайт на Народ.ру, или же блог ЖЖ, Mail.Ru, Яндекс, Blogger &mdash; это не имеет значения.<br /><br />Виджет (widget) &mdash; это фрагмент HTML-кода, который вы добавляете на страницу вашего веб-сайта, а он реализуют некоторую функцию. Здесь вы можете получить HTML-код виджета, который отобразит информацию о любом продукте вашего интернет-магазина (который вы добавите здесь), или же который дает возможность заказать определенный продукт прямо на вашем веб-сайте или блоге, не покидая его контекст.<br /><br />Для внедрения виджета на ваш веб-сайт просто получите его HTML-код здесь и добавьте на страницу сайта.<br />Все заказы, которые посетители вашего веб-сайта оформят, вы увидите здесь - в администрировании магазина, а также получите уведомления о них по электронной почте.<br /><br />Смотрите наши <a href="http://www.webasyst.ru/support/shop/manual.html#Widgets" target="_blank">примеры использования виджетов</a>.'; ?>


<h2><?php echo 'Виджеты "Информация о продукте" и "Добавить продукт в корзину"'; ?>
</h2>
<?php if (@CONF_ON_WEBASYST): ?>
<?php $this->assign('js_src', ((is_array($_tmp=(@BASE_URL)."/shop/js/widget_checkout.js")) ? $this->_run_mod_handler('regex_replace', true, $_tmp, "/([^:])\/\//", "\\1/") : smarty_modifier_regex_replace($_tmp, "/([^:])\/\//", "\\1/"))); ?>
<?php else: ?>
<?php $this->assign('js_src', ((is_array($_tmp=(@BASE_URL).(@URL_JS)."/widget_checkout.js")) ? $this->_run_mod_handler('regex_replace', true, $_tmp, "/([^:])\/\//", "\\1/") : smarty_modifier_regex_replace($_tmp, "/([^:])\/\//", "\\1/"))); ?>
<?php endif; ?>

<div class="marginblock">

	<form method="get" action="<?php echo ((is_array($_tmp='')) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
">
	<input name="did" value="<?php echo $this->_tpl_vars['CurrentDivision']['id']; ?>
" type="hidden" />
	<input name="searchstring" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['searchstring'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" type="text" size="30" />
	<input value="<?php echo 'Найти продукт'; ?>
" type="submit" />
	</form>
	<?php if ($this->_tpl_vars['GridRows']): ?>
		<div><strong><?php echo 'Результаты поиска'; ?>
</strong></div>	
		
		<p><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "backend/lister.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></p>
		<table class="grid" width="600">	
		<?php $_from = $this->_tpl_vars['GridRows']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['_row']):
?>
		<tbody class="pwgt_wgt_block collapsed" id="pwgt-prdwgt-block-<?php echo $this->_tpl_vars['_row']['productID']; ?>
">
		<tr id="pwgt-prdrow-<?php echo $this->_tpl_vars['_row']['productID']; ?>
" class="pwgt_prd_block">
			<td width="1%" nowrap="nowrap"><a href='<?php echo ((is_array($_tmp="?ukey=product_widget&productID=".($this->_tpl_vars['_row']['productID'])."&product_slug=".($this->_tpl_vars['_row']['slug'])."&furl_enable=1")) ? $this->_run_mod_handler('set_query_html', true, $_tmp, @WIDGET_SHOP_URL) : smarty_modifier_set_query_html($_tmp, @WIDGET_SHOP_URL)); ?>
' class="pwgt_hndl_widget" rel="<?php echo $this->_tpl_vars['_row']['productID']; ?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['_row']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</a></td>
			<td width="1%" nowrap="nowrap"><?php echo $this->_tpl_vars['_row']['price_str']; ?>
</td>
			<td></td>
		</tr>
		<tr id="pwgt-wgtrow-<?php echo $this->_tpl_vars['_row']['productID']; ?>
" class="pwgt_wgt_block highlight">
			<td colspan="3">
			<table width="100%">
			<tr>
				<td width="50%" valign="top">
					<strong><?php echo 'Информация о продукте'; ?>
</strong>
					<p>
					<textarea readonly="readonly" class="prd" rows="4" cols="40" wrap="hard" style="font-size:70%;width:100%;" onclick="this.select();" onfocus="this.select();"></textarea>
					</p>
					
					<p><strong><?php echo 'Так это будет выглядеть на вашем сайте:'; ?>
</strong></p>
					
					<div class="prd prd_frame"></div>
					<p class="notice"><?php echo 'Этот виджет отобразит краткую информацию об этом продукте вместе с кнопкой "Добавить в корзину".'; ?>
</p>
				</td>
				<td width="50%" valign="top">
					<strong><?php echo 'Только кнопка "Добавить в корзину"'; ?>
</strong>
				
<?php ob_start(); ?>
<script type="text/javascript" src='<?php echo $this->_tpl_vars['js_src']; ?>
'></script>
<a href='<?php echo ((is_array($_tmp="?ukey=cart&action=add_product&productID=".($this->_tpl_vars['_row']['productID'])."&furl_enable=1&widgets=1")) ? $this->_run_mod_handler('set_query', true, $_tmp, @WIDGET_SHOP_URL) : smarty_modifier_set_query($_tmp, @WIDGET_SHOP_URL)); ?>
' rel="<?php echo @BASE_WA_URL; ?>
" target="_blank" onclick="if(sswgt_CartManager)return sswgt_CartManager.add2cart(this);" title="<?php echo $this->_tpl_vars['btn_add2cart_alt']; ?>
"><img src='<?php echo @BASE_URL; 
 if (@CONF_ON_WEBASYST): ?>shop/<?php endif; 
 echo $this->_tpl_vars['button_add2cart_big']; ?>
' alt="<?php echo $this->_tpl_vars['btn_add2cart_alt']; ?>
" border='0' /></a>
<?php $this->_smarty_vars['capture']['_add2cart'] = ob_get_contents(); ob_end_clean(); ?>
					<p>
					<textarea readonly="readonly" class="pwgt_add2cart" rows="4" cols="40" wrap="hard" style="font-size:70%;width:100%;" onclick="this.select();" onfocus="this.select();"><?php echo ((is_array($_tmp=$this->_smarty_vars['capture']['_add2cart'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</textarea>
					</p>
					
					<p><strong><?php echo 'Так это будет выглядеть на вашем сайте:'; ?>
</strong></p>
					
					<div><?php echo $this->_smarty_vars['capture']['_add2cart']; ?>
</div>
					<p class="notice"><?php echo 'Используйте этот виджет, если информация об этом продукте уже опубликована на странице вашего сайта или блога, и вы просто хотите добавить возможность заказать этот продукт. Этот виджет отобразит всего лишь одну кнопку &mdash; "Добавить в корзину". Добавление продукта и его заказ будут происходить так, что пользователь не покинет контекст веб-сайта, куда вы установили виджет.'; ?>
</p>
				</td>
			</tr>
			</table>
			
			<a href='<?php echo ((is_array($_tmp="?ukey=product_settings&productID=".($this->_tpl_vars['_row']['productID']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
' target="_blank"><?php echo ((is_array($_tmp=((is_array($_tmp='pwgt_edit_product')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)))) ? $this->_run_mod_handler('replace', true, $_tmp, '%PRODUCT_NAME%', $this->_tpl_vars['_row']['name']) : smarty_modifier_replace($_tmp, '%PRODUCT_NAME%', $this->_tpl_vars['_row']['name'])); ?>
</a>
			<br />
			<a href='<?php echo ((is_array($_tmp="?ukey=product&productID=".($this->_tpl_vars['_row']['productID']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
' target="_blank"><?php echo ((is_array($_tmp=((is_array($_tmp='pwgt_view_product')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)))) ? $this->_run_mod_handler('replace', true, $_tmp, '%PRODUCT_NAME%', $this->_tpl_vars['_row']['name']) : smarty_modifier_replace($_tmp, '%PRODUCT_NAME%', $this->_tpl_vars['_row']['name'])); ?>
</a>
			<br />
			
			</td>
		</tr>
		<tr class="pwgt_wgt_block">
			<td colspan="3">&nbsp;</td>
		</tr>
		</tbody>
		<?php endforeach; endif; unset($_from); ?>
		</table>
		<p><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "backend/lister.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></p>
	<?php elseif ($this->_tpl_vars['searchstring']): ?>
		<?php echo 'Ничего не найдено'; ?>

	<?php endif; ?>
</div>

<h2><?php echo 'Виджет "Открыть корзину / Оформить заказ"'; ?>
</h2>

<div class="marginblock">
	<p><?php echo 'Этот виджет отображает кнопку "Корзина", по нажатию на которую пользователь сможет посмотреть текущее содержимое корзины, не уходя с вашего веб-сайта или блога, и приступить к оформлению заказа:'; ?>
</p>
	
<?php ob_start(); ?>
<script type="text/javascript" src='<?php echo $this->_tpl_vars['js_src']; ?>
'></script>
<a href='<?php echo ((is_array($_tmp="?ukey=cart&furl_enable=1&widgets=1")) ? $this->_run_mod_handler('set_query', true, $_tmp, @WIDGET_SHOP_URL) : smarty_modifier_set_query($_tmp, @WIDGET_SHOP_URL)); ?>
' rel="<?php echo @BASE_WA_URL; ?>
" target="_blank" onclick="if(sswgt_CartManager)return sswgt_CartManager.go2cart(this);" title="<?php echo $this->_tpl_vars['btn_viewcart_alt']; ?>
" ><img alt="<?php echo $this->_tpl_vars['btn_viewcart_alt']; ?>
" src="<?php echo @BASE_URL; 
 if (@CONF_ON_WEBASYST): ?>shop/<?php endif; 
 echo $this->_tpl_vars['button_viewcart']; ?>
" border="0" /></a>
<?php $this->_smarty_vars['capture']['_viewcart'] = ob_get_contents(); ob_end_clean(); ?>
	<textarea readonly="readonly" class="pwgt_viewcart" rows="4" cols="60" wrap="hard" style="font-size:70%" onclick="this.select();" onfocus="this.select();"><?php echo ((is_array($_tmp=$this->_smarty_vars['capture']['_viewcart'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</textarea>
	
	<p><strong><?php echo 'Так это будет выглядеть на вашем сайте:'; ?>
</strong></p>
	
	<div><?php echo $this->_smarty_vars['capture']['_viewcart']; ?>
</div>
	<p><?php echo 'ВАЖНО: Этот код только лишь отобразит кнопку для открытия страницы корзины, но корзина будет пуста, если покупатель не добавил продукты в нее. Необходимо использовать этот виджет совместно с виджетом "Добавить в корзину".'; ?>
</p>
</div>

<script type="text/javascript" src="<?php echo @URL_JS; ?>
/product_widgets.js"></script>
<script type="text/javascript">
	iframe_width = "<?php echo $this->_tpl_vars['iframe_width']; ?>
";
	iframe_height = "<?php echo $this->_tpl_vars['iframe_height']; ?>
";
</script>