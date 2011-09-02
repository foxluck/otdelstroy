<?php /* Smarty version 2.6.26, created on 2011-08-31 14:55:32
         compiled from product_discuss_link.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'set_query_html', 'product_discuss_link.html', 9, false),array('modifier', 'escape', 'product_discuss_link.html', 15, false),array('modifier', 'linewrap', 'product_discuss_link.html', 15, false),array('modifier', 'nl2br', 'product_discuss_link.html', 19, false),array('modifier', 'translate', 'product_discuss_link.html', 27, false),array('modifier', 'replace', 'product_discuss_link.html', 27, false),)), $this); ?>
<?php if (! $this->_tpl_vars['printable_version']): ?>

<h2><?php echo 'Отзывы'; 
 if ($this->_tpl_vars['rss_link']): ?>&nbsp;<a href="<?php echo @URL_ROOT; ?>
/<?php echo $this->_tpl_vars['rss_link']; ?>
"><img src="<?php echo @URL_IMAGES_COMMON; ?>
/rss-feed.png" alt="RSS 2.0" style="padding-left:10px;"></a><?php endif; ?></h2>

<?php if ($this->_tpl_vars['product_reviews_count'] == 0): ?>

	<p><?php echo 'Нет отзывов об этом продукте'; ?>
</p>
	<p>
		<a href='<?php echo ((is_array($_tmp="?product_slug=".($this->_tpl_vars['product_info']['slug'])."&productID=".($this->_tpl_vars['product_info']['productID'])."&ukey=discuss_product")) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
'><?php echo 'Написать отзыв'; ?>
</a>
	</p>
<?php else: ?>
	<?php $_from = $this->_tpl_vars['product_last_reviews']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['_review']):
?>
	<div class="review_block">
	
		<h3 class="review_title"><a name="<?php echo $this->_tpl_vars['_review']['DID']; ?>
"></a><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['_review']['Topic'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')))) ? $this->_run_mod_handler('linewrap', true, $_tmp, '\n', 50) : smarty_modifier_linewrap($_tmp, '\n', 50)); ?>
</h3>
		
		<div class="review_date"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['_review']['Author'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')))) ? $this->_run_mod_handler('linewrap', true, $_tmp) : smarty_modifier_linewrap($_tmp)); ?>
 (<?php echo $this->_tpl_vars['_review']['add_time_str']; ?>
)</div>
		
		<div class="review_content"><?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['_review']['Body'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)))) ? $this->_run_mod_handler('linewrap', true, $_tmp) : smarty_modifier_linewrap($_tmp)); ?>
</div>
		
	</div>
	<?php endforeach; endif; unset($_from); ?>
	
	<p class="review_showall">
	<a href='<?php echo ((is_array($_tmp="?product_slug=".($this->_tpl_vars['product_info']['slug'])."&productID=".($this->_tpl_vars['product_info']['productID'])."&ukey=discuss_product")) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
'>
	<?php if ($this->_tpl_vars['product_reviews_count'] > 2): ?>
	<?php echo ((is_array($_tmp=((is_array($_tmp='lnk_reviewproduct')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)))) ? $this->_run_mod_handler('replace', true, $_tmp, '%REVIEWS_NUM%', $this->_tpl_vars['product_reviews_count']) : smarty_modifier_replace($_tmp, '%REVIEWS_NUM%', $this->_tpl_vars['product_reviews_count'])); ?>

	<?php else: ?>
	<?php echo 'Написать отзыв'; ?>

	<?php endif; ?>
	</a>
	</p>
<p>
<?php endif; ?>

<?php endif; ?>