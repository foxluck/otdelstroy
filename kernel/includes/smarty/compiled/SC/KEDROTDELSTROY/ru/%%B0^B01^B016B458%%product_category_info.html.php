<?php /* Smarty version 2.6.26, created on 2011-08-31 14:55:32
         compiled from product_category_info.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'product_category_info.html', 5, false),array('modifier', 'set_query_html', 'product_category_info.html', 8, false),)), $this); ?>
<?php if (! $this->_tpl_vars['CPT_CONSTRUCTOR_MODE']): ?>
<table cellpadding="0" cellspacing="0">
<tr>
	<?php if ($this->_tpl_vars['PAGE_VIEW'] != 'mobile' && $this->_tpl_vars['selected_category'][3]): ?>
	<td width="1%" style="padding-right:6px;"><img src="<?php echo @URL_PRODUCTS_PICTURES; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['selected_category'][3])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); ?>
" alt="<?php echo $this->_tpl_vars['selected_category'][1]; ?>
" title="<?php echo $this->_tpl_vars['selected_category'][1]; ?>
"></td>
	<?php endif; ?>
	<td>
	<a href="<?php echo ((is_array($_tmp='?')) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
" class="cat"><?php echo 'Главная страница'; ?>
</a>
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
			<?php echo $this->_tpl_vars['BREADCRUMB_DELIMITER']; ?>
 <a class="cat" href='<?php echo ((is_array($_tmp="?categoryID=".($this->_tpl_vars['product_category_path'][$this->_sections['i']['index']]['categoryID'])."&category_slug=".($this->_tpl_vars['product_category_path'][$this->_sections['i']['index']]['slug']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
'><?php echo ((is_array($_tmp=$this->_tpl_vars['product_category_path'][$this->_sections['i']['index']]['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</a>
		<?php endif; ?>
	<?php endfor; endif; ?>
	</td>
</tr>
</table>
<?php else: ?>
	<a href="#" class="cat"><?php echo 'Главная'; ?>
</a> / <a class="cat" href="#">mp3</a>
<?php endif; ?>