<?php /* Smarty version 2.6.26, created on 2011-08-31 14:46:24
         compiled from product_search.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'set_query_html', 'product_search.html', 1, false),array('modifier', 'transcape', 'product_search.html', 2, false),array('modifier', 'default', 'product_search.html', 6, false),)), $this); ?>
<form action="<?php echo ((is_array($_tmp='?ukey=search')) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
" method="get">
<?php $this->assign('_str', ((is_array($_tmp='search_products')) ? $this->_run_mod_handler('transcape', true, $_tmp) : smarty_modifier_transcape($_tmp))); ?>
<?php if (! @MOD_REWRITE_SUPPORT): ?>
<input type="hidden" name="ukey" value="search" >
<?php endif; ?>
<input type="text" id="searchstring" name="searchstring" value='<?php echo ((is_array($_tmp=@$this->_tpl_vars['searchstring'])) ? $this->_run_mod_handler('default', true, $_tmp, @$this->_tpl_vars['_str']) : smarty_modifier_default($_tmp, @$this->_tpl_vars['_str'])); ?>
' title="<?php echo 'Поиск товаров'; ?>
" class="input_message" >
<input type="submit" value="<?php echo 'Найти'; ?>
" >
</form>