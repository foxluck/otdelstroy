<?php /* Smarty version 2.6.26, created on 2011-08-31 14:55:32
         compiled from product_related_products.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'set_query_html', 'product_related_products.html', 13, false),array('modifier', 'escape', 'product_related_products.html', 14, false),)), $this); ?>
<?php if (! $this->_tpl_vars['CPT_CONSTRUCTOR_MODE']): ?>
	<?php if (! $this->_tpl_vars['printable_version']): ?>

	<?php if ($this->_tpl_vars['product_related_number'] > 0): ?>
		<h2><?php echo 'Рекомендуем посмотреть'; ?>
</h2>
		
		<table <?php if ($this->_tpl_vars['PAGE_VIEW'] == 'mobile'): ?>align="center"<?php endif; ?>>
		<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['product_related']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
		<?php if ($this->_tpl_vars['PAGE_VIEW'] == 'mobile'): ?>
			<td align="center">
			<?php if ($this->_tpl_vars['product_related'][$this->_sections['i']['index']]['pictures']['default']['thumbnail']): ?>
			<a href='<?php echo ((is_array($_tmp="?productID=".($this->_tpl_vars['product_related'][$this->_sections['i']['index']]['productID'])."&product_slug=".($this->_tpl_vars['product_related'][$this->_sections['i']['index']]['slug']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
'>
			<img border="0" src="<?php echo @URL_PRODUCTS_PICTURES; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['product_related'][$this->_sections['i']['index']]['pictures']['default']['thumbnail'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); ?>
" />
			</a>
			<br />
			<?php endif; ?>
			<a href='<?php echo ((is_array($_tmp="?productID=".($this->_tpl_vars['product_related'][$this->_sections['i']['index']]['productID'])."&product_slug=".($this->_tpl_vars['product_related'][$this->_sections['i']['index']]['slug']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
'>
			<?php echo $this->_tpl_vars['product_related'][$this->_sections['i']['index']][1]; ?>

			</a>
			<br />
			<span style="color: brown;"><?php echo $this->_tpl_vars['product_related'][$this->_sections['i']['index']][2]; ?>
</span>
			</td>
		<?php else: ?>
			<td align="center">
			<?php if ($this->_tpl_vars['product_related'][$this->_sections['i']['index']]['pictures']['default']['thumbnail']): ?>
			<a href='<?php echo ((is_array($_tmp="?productID=".($this->_tpl_vars['product_related'][$this->_sections['i']['index']]['productID'])."&product_slug=".($this->_tpl_vars['product_related'][$this->_sections['i']['index']]['slug']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
'>
			<img border="0" src="<?php echo @URL_PRODUCTS_PICTURES; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['product_related'][$this->_sections['i']['index']]['pictures']['default']['thumbnail'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); ?>
" />
			</a>
			<?php endif; ?>
			</td>
			<td>
			<a href='<?php echo ((is_array($_tmp="?productID=".($this->_tpl_vars['product_related'][$this->_sections['i']['index']]['productID'])."&product_slug=".($this->_tpl_vars['product_related'][$this->_sections['i']['index']]['slug']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
'>
			<?php echo $this->_tpl_vars['product_related'][$this->_sections['i']['index']][1]; ?>

			</a>
			</td>
			<td>
			<span style="color: brown; white-space: nowrap;">&nbsp;<?php echo $this->_tpl_vars['product_related'][$this->_sections['i']['index']][2]; ?>
</span>
			</td>
		<?php endif; ?>
		</tr>
		<?php endfor; endif; ?>
		</table>
	<?php endif; ?>
	<?php endif; 
 else: ?>

		<h2><?php echo 'Рекомендуем посмотреть'; ?>
</h2>
		<table border=0>
		<tr>
			<td align="center">
				<a href="#"><img border="0" src="<?php echo @URL_DEMOPRD_IMAGES; ?>
/related_product1.jpg" /></a>
			</td>
			<td>
				<a href="#">	COWON iAudio G3</a>
			</td>
			<td nowrap>
				&nbsp;<span style="color: brown;">$160.00</span>
			</td>
		</tr>
		</table>
<?php endif; ?>
