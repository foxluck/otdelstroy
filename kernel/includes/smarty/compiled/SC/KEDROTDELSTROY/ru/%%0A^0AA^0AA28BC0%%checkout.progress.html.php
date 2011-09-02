<?php /* Smarty version 2.6.26, created on 2011-08-31 17:14:25
         compiled from checkout.progress.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'checkout.progress.html', 5, false),array('modifier', 'set_query_html', 'checkout.progress.html', 7, false),array('modifier', 'transcape', 'checkout.progress.html', 17, false),)), $this); ?>
<?php if ($this->_tpl_vars['PAGE_VIEW'] == 'noframe'): ?>
<table width="100%" cellspacing="0" cellpadding="0">
<tr>
	<td class="background_cart_top checkout_noframe_title">
		<h1><?php echo ((is_array($_tmp=@CONF_SHOP_NAME)) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</h1>
		<?php if (! $this->_tpl_vars['WIDGET_PROCESSING']): ?>
		<div><a href='<?php echo ((is_array($_tmp="?ukey=home&view=frame")) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
'><?php echo '&laquo; перейти к витрине магазина'; ?>
</a></div>
		<?php endif; ?>
	</td>
	<td class="background_cart_top checkout_noframe_title" align="right">
		<table id="tbl-checkout-progress" align="right" cellspacing="5" cellpadding="0">
<?php else: ?>
		<table id="tbl-checkout-progress" align="center" cellspacing="5" cellpadding="0">
<?php endif; ?>
		<tr>
			<?php $_from = $this->_tpl_vars['steps_chain']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['_frchain'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['_frchain']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['_chain_link']):
        $this->_foreach['_frchain']['iteration']++;
?>
			<td align="center"><?php if ($this->_tpl_vars['_chain_link']['url']): ?><a href="<?php echo ((is_array($_tmp=$this->_tpl_vars['_chain_link']['url'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
"><?php endif; ?><img src="<?php echo ((is_array($_tmp=$this->_tpl_vars['_chain_link']['image'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['_chain_link']['title'])) ? $this->_run_mod_handler('transcape', true, $_tmp) : smarty_modifier_transcape($_tmp)); ?>
" border="0" /><?php if ($this->_tpl_vars['_chain_link']['url']): ?></a><?php endif; ?></td>
			<?php if (! ($this->_foreach['_frchain']['iteration'] == $this->_foreach['_frchain']['total'])): ?>
			<td style="vertical-align:middle!important;"><div class="checkout_steps_divider"></div></td>
			<?php endif; ?>
			<?php endforeach; endif; unset($_from); ?>
		</tr>
		<tr>
			<?php $_from = $this->_tpl_vars['steps_chain']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['_frchain'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['_frchain']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['_chain_link']):
        $this->_foreach['_frchain']['iteration']++;
?>
			<td align="center">
			<?php if ($this->_tpl_vars['_chain_link']['status'] == 'current'): ?>
			<strong><?php echo ((is_array($_tmp=$this->_tpl_vars['_chain_link']['title'])) ? $this->_run_mod_handler('transcape', true, $_tmp) : smarty_modifier_transcape($_tmp)); ?>
</strong>
			<?php elseif ($this->_tpl_vars['_chain_link']['url']): ?>
			<a href="<?php echo ((is_array($_tmp=$this->_tpl_vars['_chain_link']['url'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['_chain_link']['title'])) ? $this->_run_mod_handler('transcape', true, $_tmp) : smarty_modifier_transcape($_tmp)); ?>
</a>
			<?php else: 
 echo ((is_array($_tmp=$this->_tpl_vars['_chain_link']['title'])) ? $this->_run_mod_handler('transcape', true, $_tmp) : smarty_modifier_transcape($_tmp)); 
 endif; ?>
			</td>
			<?php if (! ($this->_foreach['_frchain']['iteration'] == $this->_foreach['_frchain']['total'])): ?>
			<td></td>
			<?php endif; ?>
			<?php endforeach; endif; unset($_from); ?>
		</tr>
		</table>
<?php if ($this->_tpl_vars['PAGE_VIEW'] == 'noframe'): ?>
	</td>
</tr>
</table>
<?php endif; ?>