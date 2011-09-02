<?php /* Smarty version 2.6.26, created on 2011-08-31 14:46:34
         compiled from product_brief.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'set_query_html', 'product_brief.html', 4, false),array('modifier', 'escape', 'product_brief.html', 13, false),array('function', 'counter', 'product_brief.html', 58, false),)), $this); ?>
<?php if ($this->_tpl_vars['product_info'] != NULL): ?>
<?php if ($this->_tpl_vars['product_info']['slug']): ?>
<?php $this->assign('_product_url', ((is_array($_tmp="?ukey=product&productID=".($this->_tpl_vars['product_info']['productID'])."&product_slug=".($this->_tpl_vars['product_info']['slug']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp))); ?>
<?php else: ?>
<?php $this->assign('_product_url', ((is_array($_tmp="?ukey=product&productID=".($this->_tpl_vars['product_info']['productID']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp))); ?>
<?php endif; ?>
<?php if ($this->_tpl_vars['widget']): 
 $this->assign('_form_action_url', "&view=noframe&external=1"); 
 endif; ?>
<!-- start product_brief.html -->
<form class="product_brief_block" action='<?php echo ((is_array($_tmp="?ukey=cart".($this->_tpl_vars['_form_action_url']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
' method="post" rel="<?php echo $this->_tpl_vars['product_info']['productID']; ?>
" <?php if ($this->_tpl_vars['widget']): ?>target="_blank"<?php endif; ?>>
	<input name="action" value="add_product" type="hidden">
	<input name="productID" value="<?php echo $this->_tpl_vars['product_info']['productID']; ?>
" type="hidden">
	<input class="product_price" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['product_info']['PriceWithOutUnit'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" type="hidden">
	<?php $this->assign('_cnt', ''); ?>
	
	<?php if ($this->_tpl_vars['product_info']['thumbnail'] || $this->_tpl_vars['product_info']['picture']): ?>
	<div class="prdbrief_thumbnail">
	<table cellpadding="0" cellspacing="0">
		<tr>
			<td align="center" valign="middle" style="width: <?php echo @CONF_PRDPICT_THUMBNAIL_SIZE; ?>
px; height: <?php echo @CONF_PRDPICT_THUMBNAIL_SIZE; ?>
px;">
				<!-- Thumbnail -->
				<a <?php echo $this->_tpl_vars['target']; ?>
 href='<?php echo $this->_tpl_vars['_product_url']; ?>
'>
<?php if ($this->_tpl_vars['product_info']['thumbnail']): ?>
					<img src="<?php echo @URL_PRODUCTS_PICTURES; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['product_info']['thumbnail'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); ?>
" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['product_info']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" title="<?php echo ((is_array($_tmp=$this->_tpl_vars['product_info']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
">
<?php elseif ($this->_tpl_vars['product_info']['picture']): ?>
					<img src="<?php echo @URL_PRODUCTS_PICTURES; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['product_info']['picture'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); ?>
" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['product_info']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" title="<?php echo ((is_array($_tmp=$this->_tpl_vars['product_info']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
">
<?php endif; ?>
				</a>
			</td>
		</tr>
	</table>
	</div>
	<?php endif; ?>
	
	<div class="prdbrief_name">
		<a <?php echo $this->_tpl_vars['target']; ?>
 href='<?php echo $this->_tpl_vars['_product_url']; ?>
'><?php echo ((is_array($_tmp=$this->_tpl_vars['product_info']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</a>
		<?php if ($this->_tpl_vars['product_info']['product_code'] && @CONF_ENABLE_PRODUCT_SKU): ?>
		<br><i><?php echo ((is_array($_tmp=$this->_tpl_vars['product_info']['product_code'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</i>
<?php endif; ?>
	</div>
	
	<?php if ($this->_tpl_vars['product_info']['brief_description']): ?>
	<div class="prdbrief_brief_description"><?php echo $this->_tpl_vars['product_info']['brief_description']; ?>
</div>
	<?php endif; ?>

    <?php if (@CONF_VOTING_FOR_PRODUCTS == 'True'): ?>
	<?php if ($this->_tpl_vars['PAGE_VIEW'] != 'mobile' && $this->_tpl_vars['product_info']['customer_votes'] > 0): ?> 		<div class="sm-current-rating1">
			<div class="sm-current-rating1-back">&nbsp;</div>
			<div class="sm-current-rating1-front" style="width: <?php echo $this->_tpl_vars['product_info']['customers_rating']*13; ?>
px;">&nbsp;</div>
		</div>
	<?php endif; ?>
    <?php endif; ?>
	<?php if ($this->_tpl_vars['product_info']['product_extra']): ?>
	<div class="prdbrief_options">
		<table>
		<?php echo smarty_function_counter(array('name' => 'select_counter','start' => 0,'skip' => 1,'print' => false,'assign' => 'select_counter_var'), $this);?>

		<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['product_info']['product_extra']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
		<?php if ($this->_tpl_vars['product_info']['product_extra'][$this->_sections['i']['index']]['option_type'] != 0): ?>
		<?php unset($this->_sections['k']);
$this->_sections['k']['name'] = 'k';
$this->_sections['k']['loop'] = is_array($_loop=$this->_tpl_vars['product_info']['product_extra'][$this->_sections['i']['index']]['option_show_times']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['k']['show'] = true;
$this->_sections['k']['max'] = $this->_sections['k']['loop'];
$this->_sections['k']['step'] = 1;
$this->_sections['k']['start'] = $this->_sections['k']['step'] > 0 ? 0 : $this->_sections['k']['loop']-1;
if ($this->_sections['k']['show']) {
    $this->_sections['k']['total'] = $this->_sections['k']['loop'];
    if ($this->_sections['k']['total'] == 0)
        $this->_sections['k']['show'] = false;
} else
    $this->_sections['k']['total'] = 0;
if ($this->_sections['k']['show']):

            for ($this->_sections['k']['index'] = $this->_sections['k']['start'], $this->_sections['k']['iteration'] = 1;
                 $this->_sections['k']['iteration'] <= $this->_sections['k']['total'];
                 $this->_sections['k']['index'] += $this->_sections['k']['step'], $this->_sections['k']['iteration']++):
$this->_sections['k']['rownum'] = $this->_sections['k']['iteration'];
$this->_sections['k']['index_prev'] = $this->_sections['k']['index'] - $this->_sections['k']['step'];
$this->_sections['k']['index_next'] = $this->_sections['k']['index'] + $this->_sections['k']['step'];
$this->_sections['k']['first']      = ($this->_sections['k']['iteration'] == 1);
$this->_sections['k']['last']       = ($this->_sections['k']['iteration'] == $this->_sections['k']['total']);
?>
		<tr>					
			<td>
				<?php echo ((is_array($_tmp=$this->_tpl_vars['product_info']['product_extra'][$this->_sections['i']['index']]['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); 
 if ($this->_tpl_vars['product_info']['product_extra'][$this->_sections['i']['index']]['option_show_times'] > 1): ?> (<?php echo $this->_sections['k']['index']+1; ?>
):<?php else: ?>:<?php endif; ?>
			</td>
			<td>
				<?php echo smarty_function_counter(array('name' => 'select_counter','assign' => '_cnt'), $this);?>

				<select name='option_<?php echo $this->_tpl_vars['_cnt']; ?>
' class="product_option">
				<option value='' rel="0"><?php echo 'Не определено'; ?>
</option>
				<?php unset($this->_sections['j']);
$this->_sections['j']['name'] = 'j';
$this->_sections['j']['loop'] = is_array($_loop=$this->_tpl_vars['product_info']['product_extra'][$this->_sections['i']['index']]['values_to_select']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['j']['show'] = true;
$this->_sections['j']['max'] = $this->_sections['j']['loop'];
$this->_sections['j']['step'] = 1;
$this->_sections['j']['start'] = $this->_sections['j']['step'] > 0 ? 0 : $this->_sections['j']['loop']-1;
if ($this->_sections['j']['show']) {
    $this->_sections['j']['total'] = $this->_sections['j']['loop'];
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
?>
				<option value='<?php echo $this->_tpl_vars['product_info']['product_extra'][$this->_sections['i']['index']]['values_to_select'][$this->_sections['j']['index']]['variantID']; ?>
' rel='<?php echo $this->_tpl_vars['product_info']['product_extra'][$this->_sections['i']['index']]['values_to_select'][$this->_sections['j']['index']]['price_surplus']; ?>
'
				<?php if ($this->_tpl_vars['product_info']['product_extra'][$this->_sections['i']['index']]['values_to_select'][$this->_sections['j']['index']]['variantID'] == $this->_tpl_vars['product_info']['product_extra'][$this->_sections['i']['index']]['variantID']): ?>selected="selected"<?php endif; ?>>
					<?php echo $this->_tpl_vars['product_info']['product_extra'][$this->_sections['i']['index']]['values_to_select'][$this->_sections['j']['index']]['option_value']; ?>

				</option>
				<?php endfor; endif; ?>
				</select>
			</td>
		</tr>
		<?php endfor; endif; ?>
		<?php endif; ?>
		<?php endfor; endif; ?>
		</table>
	</div>
	<?php endif; ?>

	<?php if ($this->_tpl_vars['currencies_count'] != 0 && $this->_tpl_vars['product_info']['Price'] > 0): ?>
	<div class="prdbrief_price">
		<span class="totalPrice"><?php echo $this->_tpl_vars['product_info']['PriceWithUnit']; ?>
</span>
	</div>
	<?php endif; ?>

	
<?php if ($this->_tpl_vars['product_info']['ordering_available'] && $this->_tpl_vars['product_info']['Price'] > 0 && ( @CONF_SHOW_ADD2CART == 1 ) && ( @CONF_CHECKSTOCK == 0 || $this->_tpl_vars['product_info']['in_stock'] > 0 )): ?>
	<div class="prdbrief_add2cart">
		<input  <?php if (( $this->_tpl_vars['PAGE_VIEW'] == 'facebook' ) || ( $this->_tpl_vars['PAGE_VIEW'] == 'vkontakte' )): ?>type="submit" value="<?php echo 'добавить в корзину'; ?>
" <?php else: ?>type="image" src="<?php echo $this->_tpl_vars['button_add2cart_small']; ?>
" alt="<?php echo 'добавить в корзину'; ?>
"<?php endif; ?> title="<?php echo 'добавить в корзину'; ?>
"
		<?php if (@CONF_SHOPPING_CART_VIEW != @SHCART_VIEW_PAGE && ! $this->_tpl_vars['widget']): ?> class="add2cart_handler" rel="<?php if ($this->_tpl_vars['widget']): ?>widget<?php endif; ?>" <?php endif; ?>>
	</div>
<?php elseif (@CONF_SHOW_ADD2CART == 1 && @CONF_CHECKSTOCK && ! $this->_tpl_vars['product_info']['in_stock'] && $this->_tpl_vars['product_info']['ordering_available']): ?>
	<div class="prd_out_of_stock"><?php echo 'Нет на складе'; ?>
</div>
<?php endif; ?>
	
	<?php if ($this->_tpl_vars['PAGE_VIEW'] != 'mobile' && ( $this->_tpl_vars['PAGE_VIEW'] != 'vkontakte' ) && ( $this->_tpl_vars['PAGE_VIEW'] != 'facebook' ) && $this->_tpl_vars['product_info']['allow_products_comparison'] && $this->_tpl_vars['show_comparison']): ?>  	<div class="prdbrief_comparison">
		<input id="ctrl-prd-cmp-<?php echo $this->_tpl_vars['product_info']['productID']; ?>
" class="checknomarging ctrl_products_cmp" type="checkbox" value='<?php echo $this->_tpl_vars['product_info']['productID']; ?>
'>
		<label for="ctrl-prd-cmp-<?php echo $this->_tpl_vars['product_info']['productID']; ?>
"><?php echo 'Сравнить'; ?>
</label>
	</div>
	<?php endif; ?>
	
</form>
<!-- end product_brief.html -->

<?php endif; ?>