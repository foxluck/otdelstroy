<?php /* Smarty version 2.6.26, created on 2011-08-31 14:48:06
         compiled from category.tpl.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'category.tpl.html', 7, false),array('modifier', 'set_query', 'category.tpl.html', 10, false),array('modifier', 'set_query_html', 'category.tpl.html', 13, false),array('modifier', 'default', 'category.tpl.html', 13, false),array('function', 'math', 'category.tpl.html', 70, false),)), $this); ?>
<script type="text/javascript" src="<?php echo @URL_JS; ?>
/category.js"></script>
<div class="clearfix" id="cat_path">
<table cellpadding="0" border="0" class="cat_path_in_productpage">
	<tr>
	<?php if ($this->_tpl_vars['selected_category']['picture']): ?>
	<td><img src="<?php echo @URL_PRODUCTS_PICTURES; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['selected_category']['picture'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); ?>
" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['selected_category']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" title="<?php echo ((is_array($_tmp=$this->_tpl_vars['selected_category']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" >
	</td>
	<?php endif; ?>
	<td><a href="<?php echo ((is_array($_tmp="?")) ? $this->_run_mod_handler('set_query', true, $_tmp) : smarty_modifier_set_query($_tmp)); ?>
"><?php echo 'Главная страница'; ?>
</a>&nbsp;<?php echo $this->_tpl_vars['BREADCRUMB_DELIMITER']; ?>

		<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['product_category_path']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
		<?php if ($this->_tpl_vars['product_category_path'][$this->_sections['i']['index']]['categoryID'] != 1): ?>
		<a href='<?php echo ((is_array($_tmp="?categoryID=".($this->_tpl_vars['product_category_path'][$this->_sections['i']['index']]['categoryID'])."&category_slug=".($this->_tpl_vars['product_category_path'][$this->_sections['i']['index']]['slug']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
'><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['product_category_path'][$this->_sections['i']['index']]['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')))) ? $this->_run_mod_handler('default', true, $_tmp, "(no name)") : smarty_modifier_default($_tmp, "(no name)")); ?>
</a><?php if (! $this->_sections['i']['last']): ?>&nbsp;<?php echo $this->_tpl_vars['BREADCRUMB_DELIMITER']; 
 endif; ?>
		<?php endif; ?>
		<?php endfor; endif; ?>
	</td>
	</tr>
	</table>
	</div>
	<div class="clearfix" id="cat_top_tree">
		<?php if ($this->_tpl_vars['allow_products_search']): ?>
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "advanced_search_in_category.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		<?php endif; ?>
		
		<div id="cat_info_left_block">
				<?php echo $this->_tpl_vars['selected_category']['description']; ?>

		<?php if ($this->_tpl_vars['subcategories_to_be_shown']): ?>
				<p><?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['subcategories_to_be_shown']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
		<?php if ($this->_tpl_vars['subcategories_to_be_shown'][$this->_sections['i']['index']][3]): ?>
			<?php $this->assign('_sub_category_url', ((is_array($_tmp="?categoryID=".($this->_tpl_vars['subcategories_to_be_shown'][$this->_sections['i']['index']][0])."&category_slug=".($this->_tpl_vars['subcategories_to_be_shown'][$this->_sections['i']['index']][3]))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp))); ?>
		<?php else: ?>
		<?php $this->assign('_sub_category_url', ((is_array($_tmp="?categoryID=".($this->_tpl_vars['subcategories_to_be_shown'][$this->_sections['i']['index']][0]))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp))); ?>
		<?php endif; ?>
		 <a href="<?php echo $this->_tpl_vars['_sub_category_url']; ?>
"><?php echo $this->_tpl_vars['subcategories_to_be_shown'][$this->_sections['i']['index']][1]; ?>
</a>
		 (<?php echo $this->_tpl_vars['subcategories_to_be_shown'][$this->_sections['i']['index']][2]; ?>
)<br>
		<?php endfor; endif; ?></p>
		<?php endif; ?>
		</div>

</div>

<center>
<?php if ($this->_tpl_vars['products_to_show']): ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "comparison_products_button.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>


<?php if ($this->_tpl_vars['string_product_sort']): ?><p id="cat_product_sort"><?php echo $this->_tpl_vars['string_product_sort']; ?>
</p><?php endif; ?>


<?php if ($this->_tpl_vars['catalog_navigator']): ?><p><?php echo $this->_tpl_vars['catalog_navigator']; ?>
</p><?php endif; ?>
	
<table cellspacing="15" border="0">
  <?php $_from = $this->_tpl_vars['products_to_show']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['product_brief'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['product_brief']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['product_item']):
        $this->_foreach['product_brief']['iteration']++;
?>
	<?php if (!(($this->_foreach['product_brief']['iteration']-1) % @CONF_COLUMNS_PER_PAGE)): ?><tr><?php endif; ?>
<td style="background: url(images/bg_root.jpg); border: 1px solid #7988ae; padding: 15px;" width="600" valign="top" width="<?php echo smarty_function_math(array('equation' => "100 / x",'x' => @CONF_COLUMNS_PER_PAGE,'format' => "%d%%"), $this);?>
">
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "product_brief.html", 'smarty_include_vars' => array('product_info' => $this->_tpl_vars['product_item'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	</td>
	<?php if (!(( ($this->_foreach['product_brief']['iteration']-1)+1 ) % @CONF_COLUMNS_PER_PAGE)): ?></tr><?php elseif (($this->_foreach['product_brief']['iteration'] == $this->_foreach['product_brief']['total'])): ?></tr><?php endif; ?>
  <?php endforeach; endif; unset($_from); ?>
</table> 
 
<?php if ($this->_tpl_vars['catalog_navigator']): ?><p><?php echo $this->_tpl_vars['catalog_navigator']; ?>
</p><?php endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "comparison_products_button.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php else: ?>
<p>
	<?php if ($this->_tpl_vars['search_with_change_category_ability'] && ! $this->_tpl_vars['advanced_search_in_category']): ?>
		&nbsp;
	<?php else: ?>
		<?php if ($this->_tpl_vars['advanced_search_in_category']): ?>
			&nbsp;&nbsp;&nbsp;&nbsp;< <?php echo 'Ничего не найдено'; ?>
 >
		<?php else: ?>
			&nbsp;&nbsp;&nbsp;&nbsp;< <?php echo 'Нет продуктов'; ?>
 >
		<?php endif; ?>
	<?php endif; ?>
</p>
<?php endif; ?>

</center>