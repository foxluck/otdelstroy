<?php /* Smarty version 2.6.26, created on 2011-08-31 14:46:24
         compiled from root_categories.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'set_query_html', 'root_categories.html', 4, false),array('modifier', 'escape', 'root_categories.html', 8, false),array('modifier', 'default', 'root_categories.html', 20, false),)), $this); ?>
<table width="100%" border="0" cellpadding="5">
<?php $_from = $this->_tpl_vars['root_categories']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['_fr'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['_fr']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['_cat']):
        $this->_foreach['_fr']['iteration']++;
?>
	<?php if (($this->_foreach['_fr']['iteration']-1)%$this->_tpl_vars['columnCount'] == 0): ?><tr><?php endif; ?>
	<?php $this->assign('_cat_url', ((is_array($_tmp="?categoryID=".($this->_tpl_vars['_cat']['categoryID'])."&category_slug=".($this->_tpl_vars['_cat']['slug']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp))); ?>
	<?php if ($this->_tpl_vars['_cat']['picture'] != "" && $this->_tpl_vars['PAGE_VIEW'] != 'mobile'): ?>
	<td width="1%" align="center" class="cat_image">
	<a href='<?php echo $this->_tpl_vars['_cat_url']; ?>
'>
		<img border="0" src="<?php echo @URL_PRODUCTS_PICTURES; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['_cat']['picture'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); ?>
" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['_cat']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
">
	</a>
	</td>
	<?php else: ?>
	<td width="1%" align="center" class="cat_image">
	<a href='<?php echo $this->_tpl_vars['_cat_url']; ?>
' class="home_page_category_logo">
		
	</a>
	</td>
	<?php endif; ?>

	<td class="cat_name">
		<a href='<?php echo $this->_tpl_vars['_cat_url']; ?>
' class="rcat_root_category"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['_cat']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')))) ? $this->_run_mod_handler('default', true, $_tmp, '(no name)') : smarty_modifier_default($_tmp, '(no name)')); ?>
</a> <span class="rcat_products_num">[<?php echo $this->_tpl_vars['_cat']['products_count']; ?>
]</span>
		<div class="rcat_child_categories">
		<?php $_from = $this->_tpl_vars['root_categories_subs'][$this->_tpl_vars['_cat']['categoryID']]; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['sub_cat_list'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['sub_cat_list']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['_subcat']):
        $this->_foreach['sub_cat_list']['iteration']++;
?>
	<?php if ($this->_tpl_vars['subcategories_numberlimit'] && ( ! ($this->_foreach['sub_cat_list']['iteration'] <= 1) ) && ( $this->_foreach['sub_cat_list']['iteration'] == $this->_tpl_vars['subcategories_numberlimit'] )): ?>
		&nbsp;...
	<?php elseif (! $this->_tpl_vars['subcategories_numberlimit'] || $this->_tpl_vars['subcategories_numberlimit'] && ( $this->_foreach['sub_cat_list']['iteration'] < $this->_tpl_vars['subcategories_numberlimit'] )): ?>	
		<?php if (! ($this->_foreach['sub_cat_list']['iteration'] <= 1)): 
 echo ((is_array($_tmp=$this->_tpl_vars['subcategories_delimiter'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); 
 endif; ?>
		<a href='<?php echo ((is_array($_tmp="?categoryID=".($this->_tpl_vars['_subcat']['categoryID'])."&category_slug=".($this->_tpl_vars['_subcat']['slug']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
'><?php echo ((is_array($_tmp=$this->_tpl_vars['_subcat']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</a>
	<?php endif; ?>
<?php endforeach; endif; unset($_from); ?>
		</div>
	</td>
	<?php if (( ($this->_foreach['_fr']['iteration']-1)+1 ) % $this->_tpl_vars['columnCount'] == 0): ?></tr><?php endif; ?>
<?php endforeach; endif; unset($_from); ?>
</table>