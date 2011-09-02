<?php /* Smarty version 2.6.26, created on 2011-08-31 14:43:54
         compiled from product_discussion.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'product_discussion.html', 6, false),array('modifier', 'set_query_html', 'product_discussion.html', 11, false),array('modifier', 'translate', 'product_discussion.html', 23, false),array('modifier', 'replace', 'product_discussion.html', 23, false),array('modifier', 'linewrap', 'product_discussion.html', 31, false),array('modifier', 'nl2br', 'product_discussion.html', 35, false),)), $this); ?>
<table cellpadding="0" cellspacing="0">
<tr>
	<?php if ($this->_tpl_vars['selected_category'][3] && $this->_tpl_vars['PAGE_VIEW'] != 'mobile'): ?>
	<td rowspan=2 valign=top width="1%">
		<img src="<?php echo @URL_PRODUCTS_PICTURES; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['selected_category'][3])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); ?>
" alt="<?php echo $this->_tpl_vars['selected_category'][1]; ?>
">
	</td>
	<?php endif; ?>

	<td width="70%">
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
'><?php echo $this->_tpl_vars['product_category_path'][$this->_sections['i']['index']]['name']; ?>
</a>
		<?php endif; ?>
	<?php endfor; endif; ?>
	</td>
</tr>
</table>

<?php $this->assign('_product_url', ((is_array($_tmp="ukey=product&productID=".($this->_tpl_vars['productID']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp))); ?>
<?php $this->assign('_product_name', $this->_tpl_vars['product_name']); ?>
<h1><?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp='prddiscussion_title')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)))) ? $this->_run_mod_handler('replace', true, $_tmp, '%PRODUCT_NAME%', $this->_tpl_vars['_product_name']) : smarty_modifier_replace($_tmp, '%PRODUCT_NAME%', $this->_tpl_vars['_product_name'])))) ? $this->_run_mod_handler('replace', true, $_tmp, '%PRODUCT_URL%', $this->_tpl_vars['_product_url']) : smarty_modifier_replace($_tmp, '%PRODUCT_URL%', $this->_tpl_vars['_product_url'])); 
 if ($this->_tpl_vars['rss_link']): ?>&nbsp;<a href="<?php echo @URL_ROOT; ?>
/<?php echo $this->_tpl_vars['rss_link']; ?>
"><img src="<?php echo @URL_IMAGES_COMMON; ?>
/rss-feed.png" alt="RSS 2.0" style="padding-left:10px;"></a><?php endif; ?></h1>

<?php if ($this->_tpl_vars['GridRows']): ?>


	<?php $_from = $this->_tpl_vars['GridRows']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
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
	<p><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "../backend/lister.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></p>
<?php else: ?>
	<p><?php echo 'Нет отзывов об этом продукте'; ?>
</p>
<?php endif; ?>

<a name="add-review"></a>
<h2><?php echo 'Написать отзыв'; ?>
</h2>

<?php echo $this->_tpl_vars['MessageBlock']; ?>


<form action="<?php echo ((is_array($_tmp='')) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
" method="post" name="formD" onSubmit="return validate_disc(this);">
<table cellspacing="0" cellpadding="6">
<tr>
	<td align="right"><?php echo 'Имя'; ?>
:</td>
	<td><input type="text" name="nick" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['new_topic']['nick'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" ></td>
</tr>
<tr>
	<td align=right><?php echo 'Тема'; ?>
:</td>
	<td><input type="text" name="topic" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['new_topic']['topic'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" ></td>
</tr>
<tr>
<tr>
	<td align="right" valign="top"><?php echo 'Ваш отзыв'; ?>
:</td>
	<td>
		<textarea name="body" cols="50" rows="10"><?php echo ((is_array($_tmp=$this->_tpl_vars['new_topic']['body'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</textarea>
	</td>
</tr>
<?php if (@CONF_ENABLE_CONFIRMATION_CODE): ?>
<tr>
	<td colspan="2">&nbsp;</td>
</tr>
<tbody class="background1">
<tr>
	<td colspan="2"><?php echo 'Введите число, изображенное на рисунке'; ?>
</td>
</tr>
<tr>
	<td align="right">
		<img src="<?php echo $this->_tpl_vars['conf_image']; ?>
" alt="code" align="right" />
	</td>
	<td align="left">
		<input name="fConfirmationCode" type="text" >
	</td>
</tr>
</tbody>
<?php endif; ?>
<tr>
	<td>&nbsp;</td>
	<td>
		<input type="submit" value="<?php echo 'Отправить сообщение'; ?>
" >
		<input type="hidden" name="add_topic" value="yes" >
		<input type="hidden" name="productID" value="<?php echo $this->_tpl_vars['productID']; ?>
" >
		<input type="hidden" name="discuss" value="yes" >
	</td>
</tr>
</table>
</form>