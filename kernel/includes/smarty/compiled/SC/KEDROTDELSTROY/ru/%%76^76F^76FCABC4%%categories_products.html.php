<?php /* Smarty version 2.6.26, created on 2011-09-01 08:17:13
         compiled from backend/categories_products.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'translate', 'backend/categories_products.html', 5, false),array('modifier', 'cat', 'backend/categories_products.html', 14, false),array('modifier', 'escape', 'backend/categories_products.html', 23, false),array('modifier', 'default', 'backend/categories_products.html', 23, false),array('modifier', 'transcape', 'backend/categories_products.html', 23, false),array('modifier', 'set_query', 'backend/categories_products.html', 35, false),array('modifier', 'set_query_html', 'backend/categories_products.html', 43, false),array('function', 'cycle', 'backend/categories_products.html', 179, false),)), $this); ?>
<table id="tbl-block" cellspacing="0" cellpadding="0">
<tr height="1%">
	<td colspan="2"  height="1%">
		<h1><?php echo ((is_array($_tmp=$this->_tpl_vars['CurrentDivision']['name'])) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</h1>
	
		<?php echo $this->_tpl_vars['MessageBlock']; ?>

	</td>
</tr>
<tr>
<td id="left-block">
	<div id="left-top">
	<form method="POST" name="search_form" action='<?php echo ((is_array($_tmp=$this->_tpl_vars['urlToSubmit'])) ? $this->_run_mod_handler('cat', true, $_tmp, "&search=yes") : smarty_modifier_cat($_tmp, "&search=yes")); ?>
'>
				<input type="text" class="input_message" rel="<?php echo 'Поиск продуктов'; ?>
" name="search_value" value="<?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['search_value'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')))) ? $this->_run_mod_handler('default', true, $_tmp, 'cpt_lbl_product_search') : smarty_modifier_default($_tmp, 'cpt_lbl_product_search')))) ? $this->_run_mod_handler('transcape', true, $_tmp) : smarty_modifier_transcape($_tmp)); ?>
" />
		<input type="submit" value="<?php echo 'Найти'; ?>
" />
		<?php if ($this->_tpl_vars['searched_done']): ?>
			
		<?php endif; ?>
		</form>
	</div>
	<?php if (true || ! $this->_tpl_vars['searched_done']): ?>
	<div id="left-div">
	<ul>		<li><div <?php if (1 == $this->_tpl_vars['categoryID'] && ! $this->_tpl_vars['searched_done']): ?>class="current"<?php endif; ?>>
			<img style="visibility:hidden;" src="images_common/minus.gif" alt="<?php echo 'Свернуть'; ?>
" border="0" />
			<a href="<?php echo ((is_array($_tmp="&categoryID=1&search")) ? $this->_run_mod_handler('set_query', true, $_tmp) : smarty_modifier_set_query($_tmp)); ?>
"><?php echo 'Корень'; ?>
</a>
			(<?php echo $this->_tpl_vars['products_in_root_category']; ?>
)
		</div></li>
	 <?php $_from = $this->_tpl_vars['categories']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['_category']):
?>
	 	<li><div <?php if ($this->_tpl_vars['_category']['categoryID'] == $this->_tpl_vars['categoryID'] && ! $this->_tpl_vars['searched_done']): ?>class="current"<?php endif; ?>>
			<?php unset($this->_sections['j']);
$this->_sections['j']['name'] = 'j';
$this->_sections['j']['loop'] = is_array($_loop=$this->_tpl_vars['_category']['level']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['j']['max'] = (int)$this->_tpl_vars['_category']['level'];
$this->_sections['j']['show'] = true;
if ($this->_sections['j']['max'] < 0)
    $this->_sections['j']['max'] = $this->_sections['j']['loop'];
$this->_sections['j']['step'] = 1;
$this->_sections['j']['start'] = $this->_sections['j']['step'] > 0 ? 0 : $this->_sections['j']['loop']-1;
if ($this->_sections['j']['show']) {
    $this->_sections['j']['total'] = min(ceil(($this->_sections['j']['step'] > 0 ? $this->_sections['j']['loop'] - $this->_sections['j']['start'] : $this->_sections['j']['start']+1)/abs($this->_sections['j']['step'])), $this->_sections['j']['max']);
    if ($this->_sections['j']['total'] == 0)
        $this->_sections['j']['show'] = false;
} else
    $this->_sections['j']['total'] = 0;
if ($this->_sections['j']['show']):

            for ($this->_sections['j']['index'] = $this->_sections['j']['start'], $this->_sections['j']['iteration'] = 1;
                 $this->_sections['j']['iteration'] <= $this->_sections['j']['total'];
                 $this->_sections['j']['index'] += $this->_sections['j']['step'], $this->_sections['j']['iteration']++):
$this->_sections['j']['rownum'] = $this->_sections['j']['iteration'];
$this->_sections['j']['index_prev'] = $this->_sections['j']['index'] - $this->_sections['j']['step'];
$this->_sections['j']['index_next'] = $this->_sections['j']['index'] + $this->_sections['j']['step'];
$this->_sections['j']['first']      = ($this->_sections['j']['iteration'] == 1);
$this->_sections['j']['last']       = ($this->_sections['j']['iteration'] == $this->_sections['j']['total']);
?>&nbsp;&nbsp;&nbsp;<?php endfor; endif; ?>
			<?php if (! $this->_tpl_vars['_category']['ExpandedCategory']): ?>
				<?php if ($this->_tpl_vars['_category']['ExistSubCategories']): ?>
					<a href='<?php echo ((is_array($_tmp="&expandCat=".($this->_tpl_vars['_category']['categoryID']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
'><img src="images_common/plus.gif" alt="<?php echo 'Развернуть'; ?>
" /></a>
				<?php else: ?>
					<img src="images_common/minus.gif" style="visibility:hidden;" />
				<?php endif; ?>
			<?php else: ?>
				<?php if ($this->_tpl_vars['_category']['ExistSubCategories']): ?>
					<a href='<?php echo ((is_array($_tmp="&shrinkCat=".($this->_tpl_vars['_category']['categoryID']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
'><img src="images_common/minus.gif" alt="<?php echo 'Свернуть'; ?>
" /></a>
				<?php else: ?>
					<img src="images_common/minus.gif" style="visibility:hidden;" />
				<?php endif; ?>
			<?php endif; ?>
			
			<a href='<?php echo ((is_array($_tmp="&categoryID=".($this->_tpl_vars['_category']['categoryID'])."&expandCat=".($this->_tpl_vars['_category']['categoryID'])."&search")) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
'><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['_category']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')))) ? $this->_run_mod_handler('default', true, $_tmp, "(no name)") : smarty_modifier_default($_tmp, "(no name)")); ?>
</a>
			<?php if (! $this->_tpl_vars['_category']['ExpandedCategory']): ?>(<?php echo $this->_tpl_vars['_category']['products_count_admin']; ?>
)<?php else: ?>(<?php echo $this->_tpl_vars['_category']['products_count_category']; ?>
)<?php endif; ?>
		</div></li>
	 <?php endforeach; endif; unset($_from); ?>

	</ul>

	<div id="left-bottom">
	
	<p>	
	<input type="button" value='<?php echo 'Добавить категорию'; ?>
' class="goto" rel='<?php echo ((is_array($_tmp="?ukey=category_settings&parent=".($this->_tpl_vars['categoryID']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
' />
	</p>
	
	</div>
	
	</div>
	<?php endif; ?>
	<script type="text/javascript" src="<?php echo @URL_JS; ?>
/niftycube.js"></script>
	<script type="text/javascript" src="<?php echo @URL_JS; ?>
/functions.js"></script>
	<script type="text/javascript" src="<?php echo @URL_JS; ?>
/widget_checkout.js"></script>	
	<script type="text/javascript">
	Nifty('#left-div', 'tl bl');
	Nifty('div.current', 'tl bl');
	</script>
</td>


<td id="right-block">
	<?php if ($this->_tpl_vars['searched_done']): ?>
	<h2><?php echo $this->_tpl_vars['searched_count']; ?>
</h2>
	<?php else: ?>
	<h2>
	<?php if ($this->_tpl_vars['categoryID'] != 1): ?>
		<?php echo ((is_array($_tmp=$this->_tpl_vars['category_name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>

	<?php else: ?>
		<?php echo 'Корень'; ?>

	<?php endif; ?>
	
	<?php if ($this->_tpl_vars['categoryID'] != 1): ?>
	&nbsp;
	<a href='<?php echo ((is_array($_tmp="ukey=category_settings&categoryID=".($this->_tpl_vars['categoryID']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
'><?php echo 'Редактировать категорию'; ?>
</a>
	&nbsp;
	<a href='<?php echo ((is_array($_tmp="action=delete_category")) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
' title="<?php echo 'Удалить?'; ?>
" class="confirm_action"><?php echo 'Удалить категорию'; ?>
</a>
	<?php endif; ?>
	</h2>
	
	<p>
	<input value="<?php echo 'Добавить продукт'; ?>
" type="button" class="goto" rel='<?php echo ((is_array($_tmp="?ukey=product_settings&categoryID=".($this->_tpl_vars['categoryID']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
' />
	</p>
	
	<?php if ($this->_tpl_vars['categoryID'] == 1): ?>
	<p style="text-align: center"><?php echo '<font color=red>Все продукты, находящиеся в корне, не видны пользователям!</font>'; ?>
</p>
	<?php endif; ?>
	
	<?php endif; ?>
	
	

	<?php if ($this->_tpl_vars['GridRows']): ?>
	<form action='<?php echo $this->_tpl_vars['urlToSubmit']; ?>
' method="post" name="MainForm" id="MainForm">
	<input name="action" value="save_products" type="hidden" />
		
		<table width="100%" border="0" cellpadding="0" cellspacing="0" class="grid">
		<thead>
		<tr class="gridsheader">
			<td><input type="checkbox" class="groupcheckbox" id="group-box" rel="select_product" /></td>
			<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "backend/gridheader.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		</tr>
		</thead>
		<tfoot>
		<tr class="gridsfooter"> 
			<td colspan="4">
				<?php echo $this->_tpl_vars['navigatorHtml']; ?>

							</td>
			<td  colspan="<?php if (( ( @CONF_CHECKSTOCK == 1 ) && ( ( @CONF_VKONTAKTE_ENABLED == 1 && false ) || ( @CONF_FACEBOOK_ENABLED == 1 ) ) )): ?>6<?php elseif (( ( @CONF_CHECKSTOCK == 1 ) || ( @CONF_VKONTAKTE_ENABLED == 1 && false ) || ( @CONF_FACEBOOK_ENABLED == 1 ) )): ?>5<?php else: ?>4<?php endif; ?>" style="border-left: solid 1px #F5F0BB;border-right: solid 1px #F5F0BB;border-bottom: solid 1px #F5F0BB;">
			&uarr;
			<span style="font-size:smaller;"><?php echo 'Умножить все цены на'; ?>
</span>
				<input style="font-size:smaller;" type="text" id="multiply_price_value" value="1.000" size="5">
			<span id="multiply-price-handler" style="font-size:smaller;border-bottom:1px dashed;color: #597380 !important;cursor:pointer;"><?php echo 'Умножить'; ?>
</span>
			
			<span style="float: right;">
				<input name="save_products" type="submit" value='<?php echo 'Сохранить цены и сортировку'; ?>
' />
			</span>
			</td>
		</tr>
		<tr class="gridsfooter"> 
			<td colspan="<?php if (( ( @CONF_CHECKSTOCK == 1 ) && ( ( @CONF_VKONTAKTE_ENABLED == 1 && false ) || ( @CONF_FACEBOOK_ENABLED == 1 ) ) )): ?>10<?php elseif (( ( @CONF_CHECKSTOCK == 1 ) || ( @CONF_VKONTAKTE_ENABLED == 1 && false ) || ( @CONF_FACEBOOK_ENABLED == 1 ) )): ?>9<?php else: ?>8<?php endif; ?>">
					<div style="float: left;">
						<?php echo 'Выбранные продукты'; ?>
: <input type="submit" name="delete_selected" class="confirm_action" title="<?php echo 'Удалить?'; ?>
" value='<?php echo 'Удалить'; ?>
' />
					</div>
					<div style="float: left;">
						&nbsp;
						<input name="categoryID" id="parent-category-categoryID" value="<?php echo ((is_array($_tmp=@$this->_tpl_vars['parent_category']['categoryID'])) ? $this->_run_mod_handler('default', true, $_tmp, 1) : smarty_modifier_default($_tmp, 1)); ?>
" type="hidden" />
					</div>
					<div style="float: left;">	
						&nbsp;					
						<input name="move_selected" type="submit" value='<?php echo 'Переместить в...'; ?>
' id="choose-parentcategory-handler"/>
					</div>
					<div style="float: left;">	
						&nbsp;					
						<input name="duplicate_selected" type="submit" value='<?php echo 'Создать дубликат(ы)'; ?>
' id="duplicate_products-handler"/>
					</div>
			</td>
		</tr>
		<?php if (( @CONF_VKONTAKTE_ENABLED == 1 ) && false): ?>
		<tr class="gridsfooter"> 
			<td style="border-width: 0px;" colspan="<?php if (( ( @CONF_CHECKSTOCK == 1 ) && ( ( @CONF_VKONTAKTE_ENABLED == 1 && false ) || ( @CONF_FACEBOOK_ENABLED == 1 ) ) )): ?>10<?php elseif (( ( @CONF_CHECKSTOCK == 1 ) || ( @CONF_VKONTAKTE_ENABLED == 1 && false ) || ( @CONF_FACEBOOK_ENABLED == 1 ) )): ?>9<?php else: ?>8<?php endif; ?>">
					<div style="float: left;">	
						<?php echo 'Вконтакте'; ?>
:&nbsp;					
						<input name="vkontakte_change" type="submit" value="<?php echo 'Экспорт во «Вконтакт»'; ?>
" id="vkontakte_change-handler"/>
					</div>
					<div style="float: left;">	
						&nbsp;					
						<input name="vkontakte_remove" type="submit" value="<?php echo 'Удалить из каталога «Вконтакта»'; ?>
" id="vkontakte_remove-handler"/>
					</div>
			</td>
		</tr>
		<?php endif; ?>
		</tfoot>
		<tbody>
		<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['GridRows']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
		<?php $this->assign('product_url', ((is_array($_tmp="?ukey=product_settings&productID=".($this->_tpl_vars['GridRows'][$this->_sections['i']['index']]['productID'])."&categoryID=&expandCat=&offset=&sort=&sort_dir=&search=&search_value=")) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp))); ?>
		<tr class="<?php echo smarty_function_cycle(array('values' => 'gridline1,gridline'), $this);
 if ($this->_tpl_vars['GridRows'][$this->_sections['i']['index']]['in_stock'] <= 0 && @CONF_CHECKSTOCK): ?> gridline_outofstock<?php endif; ?>">
		
			<td><input name="selected_product_<?php echo $this->_tpl_vars['GridRows'][$this->_sections['i']['index']]['productID']; ?>
" class="checkbox select_product" rel="group-box" type="checkbox" value="1" /></td>
			<td><a href="<?php echo $this->_tpl_vars['product_url']; ?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['GridRows'][$this->_sections['i']['index']]['product_code'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
&nbsp;</a></td>
			<td width="50%"><a href="<?php echo $this->_tpl_vars['product_url']; ?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['GridRows'][$this->_sections['i']['index']]['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
&nbsp;<?php if ($this->_tpl_vars['GridRows'][$this->_sections['i']['index']]['enabled'] != 1): ?><span class="notice"><?php echo '(не представлен в пользовательской части)'; ?>
</span><?php endif; ?></a></td>
			<td align="right"><a href="<?php echo $this->_tpl_vars['product_url']; ?>
"><?php echo $this->_tpl_vars['GridRows'][$this->_sections['i']['index']]['customers_rating']; ?>
&nbsp;</a></td>
			<td align="center" style="border-left: solid 1px #F5F0BB;border-right: solid 1px #F5F0BB;"><input type="text" class="multiply_price" name="price_<?php echo $this->_tpl_vars['GridRows'][$this->_sections['i']['index']]['productID']; ?>
" size="10" value="<?php echo $this->_tpl_vars['GridRows'][$this->_sections['i']['index']]['Price']; ?>
" /></td>
			<?php if (@CONF_CHECKSTOCK == 1): ?>
			<td><input type="text" name="left_<?php echo $this->_tpl_vars['GridRows'][$this->_sections['i']['index']]['productID']; ?>
" size="5" value="<?php echo $this->_tpl_vars['GridRows'][$this->_sections['i']['index']]['in_stock']; ?>
" /></td>
			<?php endif; ?>
			<td align="right"><?php echo $this->_tpl_vars['GridRows'][$this->_sections['i']['index']]['items_sold']; 
 if (! $this->_tpl_vars['GridRows'][$this->_sections['i']['index']]['ordering_available']): ?><div class="notice"><?php echo 'продукт сейчас нельзя заказать'; ?>
</div><?php endif; ?></td>
			<?php if (( ( @CONF_VKONTAKTE_ENABLED == 1 && false ) || ( @CONF_FACEBOOK_ENABLED == 1 ) )): ?>
			<td align="right">
				<?php if (( @CONF_VKONTAKTE_ENABLED == 1 ) && false): ?>
				<?php if ($this->_tpl_vars['GridRows'][$this->_sections['i']['index']]['vkontakte_update_timestamp']): ?>
					<img src="./images_common/vkontakte/vkontakte.ico" alt="<?php echo $this->_tpl_vars['GridRows'][$this->_sections['i']['index']]['vkontakte_update_timestamp']; ?>
" title="<?php echo $this->_tpl_vars['GridRows'][$this->_sections['i']['index']]['vkontakte_update_timestamp']; ?>
">
				<?php else: ?>
					&mdash;
				<?php endif; ?>
				<?php endif; ?>
			</td>
			<?php endif; ?>
			<td align="center"><input type='text' name='sort_order_<?php echo $this->_tpl_vars['GridRows'][$this->_sections['i']['index']]['productID']; ?>
' value="<?php echo $this->_tpl_vars['GridRows'][$this->_sections['i']['index']]['sort_order']; ?>
" size="2" /></td>
			<td style="border-right: solid 1px #F5F0BB;"><a href='<?php echo ((is_array($_tmp="action=delete_product&productID=".($this->_tpl_vars['GridRows'][$this->_sections['i']['index']]['productID']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
' title='<?php echo 'Удалить?'; ?>
' class="confirm_action"><img src="images_common/remove.gif" border="0" alt='<?php echo 'Удалить'; ?>
' /></a></td>
		</tr>	
		<?php endfor; endif; ?>
		</tbody>
		</table>
		
	</form>
	<script type="text/javascript">
	var categoryID = '<?php echo $this->_tpl_vars['categoryID']; ?>
';
				<?php echo '
getLayer(\'multiply-price-handler\').onclick = function(){
	var multiply = document.getElementById(\'multiply_price_value\');
	if(multiply&&multiply.value>0){
			var inputs = getElementsByClass(\'multiply_price\',null,\'input\');
			for(var l=inputs.length-1; l>=0; l--){
				inputs[l].value = Math.round(10000*inputs[l].value*multiply.value)/10000;
			}
		}
};				
getLayer(\'choose-parentcategory-handler\').onclick = function(){if(getCountCheckGroupBox(\'select_product\')<1){alert(\''; 
 echo 'Пожалуйста, выберите продукт'; 
 echo '\');return false;}categoryTreeManager.show_tree(\'choose_parentcategory\');return false;};
getLayer(\'duplicate_products-handler\').onclick = function(){if(getCountCheckGroupBox(\'select_product\')<1){alert(\''; 
 echo 'Пожалуйста, выберите продукт'; 
 echo '\');return false;}};
'; 
 if (( @CONF_VKONTAKTE_ENABLED == 1 && false )): 
 echo '
getLayer(\'vkontakte_change-handler\').onclick = function(){if(getCountCheckGroupBox(\'select_product\')<1){alert(\''; 
 echo 'Пожалуйста, выберите продукт'; 
 echo '\');return false;}};
getLayer(\'vkontakte_remove-handler\').onclick = function(){if(getCountCheckGroupBox(\'select_product\')<1){alert(\''; 
 echo 'Пожалуйста, выберите продукт'; 
 echo '\');return false;}};
'; 
 endif; 
 echo '
var categoryTreeManager = {
	\'show_tree\': function(action){
		var url = set_query(\'?ukey=category_tree&js_action=\'+action+\'&productID=\');
		sswgt_CartManager.shop_url = "'; 
 echo ((is_array($_tmp=@CONF_FULL_SHOP_URL)) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); 
 echo '";
		sswgt_CartManager.show(url, 550, 500); 	
	},
	\'hide_tree\': function(){
		sswgt_CartManager.hide();
	},
	\'actions\': {
		\'choose_parentcategory\': {
			\'onclick\': function(node){				
				if(categoryID == node.getSetting(\'categoryID\')){
					categoryTreeManager.hide_tree();
				}
				categoryTreeManager.hide_tree();
				var breadCrumbs = node.getSetting(\'name\');
				var p = node.ParentNode;
				while(p){
					breadCrumbs = p.getSetting(\'name\')+" » "+breadCrumbs;
					p = p.ParentNode;
				}
				if(window.confirm(\''; 
 echo 'Переместить выбранные продукты в'; 
 echo '\'+\' \'+breadCrumbs+\'?\')){
					getLayer(\'parent-category-categoryID\').value = node.getSetting(\'categoryID\');
					getLayer(\'choose-parentcategory-handler\').onclick = function(){return true;};
					getLayer(\'choose-parentcategory-handler\').click();
				}				
			}
		}
	},
	
	\'eval\': function(action, handler, node, wnd){
		
		this.actions[action][handler](node, wnd);
	}
}
'; ?>
</script>
	
	<?php else: ?>
	<p style="text-align: center"><?php echo 'Нет продуктов'; ?>
</p>
	<?php endif; ?>

</td>
</tr>
</table>