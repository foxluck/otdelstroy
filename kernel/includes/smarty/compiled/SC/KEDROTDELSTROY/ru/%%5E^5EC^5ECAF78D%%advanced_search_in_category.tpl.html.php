<?php /* Smarty version 2.6.26, created on 2011-08-31 14:46:34
         compiled from advanced_search_in_category.tpl.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'set_query_html', 'advanced_search_in_category.tpl.html', 11, false),array('modifier', 'string_repeat', 'advanced_search_in_category.tpl.html', 33, false),array('modifier', 'escape', 'advanced_search_in_category.tpl.html', 33, false),)), $this); ?>
<div id="cat_advproduct_search">
	<h3>
	<?php if ($this->_tpl_vars['categories_to_select']): ?>
		<?php echo 'Расширенный поиск'; ?>

	<?php else: ?>
		<?php echo 'Поиск продукта в этой категории'; ?>

	<?php endif; ?>
	</h3>
	
	<form name='AdvancedSearchInCategory' method='get' action='<?php echo ((is_array($_tmp="?ukey=category_search")) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
'>

	<?php if (! @FURL_ENABLED || ! @MOD_REWRITE_SUPPORT): ?>	
		<input name='ukey' value='category_search' type="hidden" >
	<?php endif; ?>
	
	<?php if ($this->_tpl_vars['categories_to_select']): ?>
		<input type='hidden' name='search_with_change_category_ability' value='yes' >
	<?php else: ?>
		<input type='hidden' name='search_with_change_category_ability' value='1' >
		<input name='categoryID' value='<?php echo $this->_tpl_vars['categoryID']; ?>
' type="hidden" >
	<?php endif; ?>
	
	<table>
	<?php if ($this->_tpl_vars['categories_to_select']): ?>
	<tr>
		<td><?php echo 'Категория'; ?>
</td>
		<td>
			<select name='categoryID'>
			<?php if (! $this->_tpl_vars['categoryID']): ?><option value='0'><?php echo 'Пожалуйста, выберите'; ?>
</option><?php endif; ?>
			<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['categories_to_select']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
				<option value='<?php echo $this->_tpl_vars['categories_to_select'][$this->_sections['i']['index']]['categoryID']; ?>
' <?php if ($this->_tpl_vars['categories_to_select'][$this->_sections['i']['index']]['categoryID'] == $this->_tpl_vars['categoryID']): ?> selected="selected"<?php endif; ?> />
					<?php echo ((is_array($_tmp="&nbsp;&nbsp;&nbsp;")) ? $this->_run_mod_handler('string_repeat', true, $_tmp, $this->_tpl_vars['categories_to_select'][$this->_sections['i']['index']]['level']) : smarty_modifier_string_repeat($_tmp, $this->_tpl_vars['categories_to_select'][$this->_sections['i']['index']]['level'])); 
 echo ((is_array($_tmp=$this->_tpl_vars['categories_to_select'][$this->_sections['i']['index']]['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>

				</option>
			<?php endfor; endif; ?>
			</select>
		</td>
	</tr>
	<?php endif; 
 if ($this->_tpl_vars['categoryID']): ?>
	<tr>
		<td><?php echo 'Название'; ?>
</td>
		<td><input type="text" name="search_name" value="<?php echo $this->_tpl_vars['search_name']; ?>
" size="16" ></td>
	</tr>
	<tr> 
		<td valign="bottom"><?php echo 'Цена'; ?>
</td>
		<td nowrap="nowrap">
			<table cellpadding="0" cellspacing="0">
			<tr>
				<td><?php echo 'от'; ?>
</td>
				<td><?php echo 'до'; ?>
</td>
			</tr>
			<tr>
				<td><input name="search_price_from" type="text" size="7" value="<?php echo $this->_tpl_vars['search_price_from']; ?>
" >&nbsp;</td>
				<td><input name="search_price_to" type="text" size="7" value="<?php echo $this->_tpl_vars['search_price_to']; ?>
" ></td>
			</tr>
			</table>
		</td>
	</tr>
	<?php endif; ?>	
	<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['params']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
	<tr>
		<td><?php echo ((is_array($_tmp=$this->_tpl_vars['params'][$this->_sections['i']['index']]['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</td>
		<td>
			<?php if ($this->_tpl_vars['params'][$this->_sections['i']['index']]['controlIsTextField'] == 1): ?>
			<input type="text" name='param_<?php echo $this->_tpl_vars['params'][$this->_sections['i']['index']]['optionID']; ?>
' value="<?php echo ((is_array($_tmp=$this->_tpl_vars['params'][$this->_sections['i']['index']]['value'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" size="16" >
			<?php else: ?>
			<select name='param_<?php echo $this->_tpl_vars['params'][$this->_sections['i']['index']]['optionID']; ?>
'>
				<option value='0'><?php echo 'не имеет значения'; ?>
</option>
				
				<?php unset($this->_sections['j']);
$this->_sections['j']['name'] = 'j';
$this->_sections['j']['loop'] = is_array($_loop=$this->_tpl_vars['params'][$this->_sections['i']['index']]['variants']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
				<option value='<?php echo $this->_tpl_vars['params'][$this->_sections['i']['index']]['variants'][$this->_sections['j']['index']]['variantID']; ?>
' <?php if ($this->_tpl_vars['params'][$this->_sections['i']['index']]['value'] == $this->_tpl_vars['params'][$this->_sections['i']['index']]['variants'][$this->_sections['j']['index']]['variantID']): ?>selected<?php endif; ?>>
					<?php echo ((is_array($_tmp=$this->_tpl_vars['params'][$this->_sections['i']['index']]['variants'][$this->_sections['j']['index']]['value'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>

				</option>
				<?php endfor; endif; ?>
			</select>
			<?php endif; ?>
		</td>
	</tr>
	<?php endfor; endif; ?>
	
	<?php if ($this->_tpl_vars['show_subcategory_checkbox']): ?>
	<tr>
		<td colspan='2'>
			<?php if ($this->_tpl_vars['show_subcategories_products']): ?><input type='hidden' value='1' name='search_in_subcategory' >
			<?php else: ?>
				<input value='1' name='search_in_subcategory' id="cat_search_in_subcategory" <?php if ($this->_tpl_vars['search_in_subcategory']): ?> checked="checked"<?php endif; ?> type='checkbox' class="checknomarging" > <label for="cat_search_in_subcategory"><?php echo 'искать в подкатегориях'; ?>
</label>
			<?php endif; ?>
		</td>
	</tr>
	<?php endif; ?>
	<?php if ($this->_tpl_vars['categoryID']): ?>
	<tr>
		<td colspan="2"><input type='submit' value='<?php echo 'Найти'; ?>
' name='advanced_search_in_category' ></td>
	</tr>
	<?php endif; ?>
	</table>
	
	</form>
</div>