<?php /* Smarty version 2.6.26, created on 2011-08-31 14:46:24
         compiled from product_list.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'set_query_html', 'product_list.html', 7, false),array('modifier', 'escape', 'product_list.html', 14, false),)), $this); ?>
<table align="center" cellpadding="0" cellspacing="0">
<tr>
<td>
<ul class="product_list">
<?php $_from = $this->_tpl_vars['__products']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['_product']):
?>
<?php if ($this->_tpl_vars['_product']['slug']): ?>
<?php $this->assign('_product_url', ((is_array($_tmp="?productID=".($this->_tpl_vars['_product']['productID'])."&product_slug=".($this->_tpl_vars['_product']['slug']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp))); ?>
<?php else: ?>
<?php $this->assign('_product_url', ((is_array($_tmp="?productID=".($this->_tpl_vars['_product']['productID']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp))); ?>
<?php endif; ?>
<li style="width: <?php echo @CONF_PRDPICT_THUMBNAIL_SIZE+20; ?>
; height: <?php if ($this->_tpl_vars['__block_height']): 
 echo $this->_tpl_vars['__block_height']; 
 elseif ($this->_tpl_vars['_product']['thumbnail']): 
 echo @CONF_PRDPICT_THUMBNAIL_SIZE+45; 
 else: ?>55<?php endif; ?>;">
<?php if ($this->_tpl_vars['_product']['thumbnail']): ?>
<table cellpadding="0" cellspacing="0" style="width: <?php echo @CONF_PRDPICT_THUMBNAIL_SIZE; ?>
; height: <?php echo @CONF_PRDPICT_THUMBNAIL_SIZE; ?>
;"><tr><td valign="bottom" align="center">
<a href="<?php echo $this->_tpl_vars['_product_url']; ?>
"><img src="<?php echo @URL_PRODUCTS_PICTURES; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['_product']['thumbnail'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); ?>
" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['_product']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
"></a>
</td></tr></table>
<?php endif; ?>
<a href="<?php echo $this->_tpl_vars['_product_url']; ?>
"><?php echo $this->_tpl_vars['_product']['name']; ?>
</a>
<?php if ($this->_tpl_vars['_product']['Price']): ?><div class="totalPrice"><?php echo $this->_tpl_vars['_product']['price_str']; ?>
</div><?php endif; ?>
</li>
<?php endforeach; endif; unset($_from); ?>
</ul>

</td>
</tr>
</table>