<?php /* Smarty version 2.6.26, created on 2011-08-31 14:55:32
         compiled from product_images.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'product_images.html', 22, false),array('function', 'counter', 'product_images.html', 35, false),)), $this); ?>
<?php if (! $this->_tpl_vars['CPT_CONSTRUCTOR_MODE']): ?>
	<link rel="stylesheet" href="<?php echo @URL_ROOT; ?>
/3rdparty/highslide/highslide.css" type="text/css" />
	<script type="text/javascript" src="<?php echo @URL_ROOT; ?>
/3rdparty/highslide/highslide.js"></script>
	<script type="text/javascript">    
	    hs.graphicsDir = '<?php echo @URL_ROOT; ?>
/3rdparty/highslide/graphics/';
		hs.registerOverlay(
	    	{
	    		thumbnailId: null,
	    		overlayId: 'controlbar',
	    		position: 'top right',
	    		hideOnMouseOut: true
			}
		);
	    hs.outlineType = 'rounded-white';
	</script>
	<?php if ($this->_tpl_vars['PAGE_VIEW'] != 'mobile' && $this->_tpl_vars['product_info']['picture']): ?>
	
		<div style="text-align:center;width: <?php echo @CONF_PRDPICT_STANDARD_SIZE+32; ?>
px;">
		<a name="anch_current_picture"></a>
		<div style="width: <?php echo @CONF_PRDPICT_STANDARD_SIZE; ?>
px;">
		<?php if (! $this->_tpl_vars['printable_version'] && $this->_tpl_vars['product_info']['big_picture'] && ( $this->_tpl_vars['product_info']['big_picture'] != $this->_tpl_vars['product_info']['picture'] )): ?>
			<a target="_blank" onclick="return hs.expand(this)" href='<?php echo @URL_PRODUCTS_PICTURES; ?>
/<?php if ($this->_tpl_vars['product_info']['big_picture']): 
 echo ((is_array($_tmp=$this->_tpl_vars['product_info']['big_picture'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); 
 else: 
 echo ((is_array($_tmp=$this->_tpl_vars['product_info']['picture'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); 
 endif; ?>' img_width="<?php echo $this->_tpl_vars['product_info']['picture_width']; ?>
" img_height="<?php echo $this->_tpl_vars['product_info']['picture_height']; ?>
">
			<img id='img-current_picture' border='0' src="<?php echo @URL_PRODUCTS_PICTURES; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['product_info']['picture'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); ?>
" title="<?php echo ((is_array($_tmp=$this->_tpl_vars['page_title'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['page_title'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" >
			</a>
		<?php else: ?>
			<img id='img-current_picture' border='0' src="<?php echo @URL_PRODUCTS_PICTURES; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['product_info']['picture'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); ?>
" title="<?php echo ((is_array($_tmp=$this->_tpl_vars['page_title'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['page_title'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" >		
		<?php endif; ?>
		</div>
		
		<?php if ($this->_tpl_vars['all_product_pictures']): ?>
		<table cellpadding="3" align="center" id="box_product_thumbnails">
		
		<?php $_from = $this->_tpl_vars['all_product_pictures']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['frpict'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['frpict']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['_picture']):
        $this->_foreach['frpict']['iteration']++;
?>
		<?php if ($this->_tpl_vars['_picture']['photoID'] != $this->_tpl_vars['product_info']['photoID']): ?>
			<?php echo smarty_function_counter(array('name' => '_pict_num','assign' => '_pict_num'), $this);?>

			<?php if (( $this->_tpl_vars['_pict_num']-1 ) % 2 == 0): ?><tr><?php endif; ?>
			<td align="center" style="width: <?php echo @CONF_PRDPICT_THUMBNAIL_SIZE; ?>
px; height: <?php echo @CONF_PRDPICT_THUMBNAIL_SIZE; ?>
px;">		
			<?php if (! $this->_tpl_vars['printable_version'] && ( $this->_tpl_vars['_picture']['width'] > @CONF_PRDPICT_THUMBNAIL_SIZE || $this->_tpl_vars['_picture']['height'] > @CONF_PRDPICT_THUMBNAIL_SIZE )): ?>
			<a onclick="return hs.expand(this)" href='<?php echo @URL_PRODUCTS_PICTURES; ?>
/<?php if ($this->_tpl_vars['_picture']['enlarged']): 
 echo ((is_array($_tmp=$this->_tpl_vars['_picture']['enlarged'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); 
 else: 
 echo ((is_array($_tmp=$this->_tpl_vars['_picture']['filename'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); 
 endif; ?>' img_width="<?php echo $this->_tpl_vars['_picture']['width']; ?>
" img_height="<?php echo $this->_tpl_vars['_picture']['height']; ?>
" img_enlarged="<?php if ($this->_tpl_vars['_picture']['enlarged']): 
 echo @URL_PRODUCTS_PICTURES; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['_picture']['enlarged'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); 
 endif; ?>" img_picture="<?php echo @URL_PRODUCTS_PICTURES; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['_picture']['filename'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); ?>
" target="_blank">
			<img src='<?php echo @URL_PRODUCTS_PICTURES; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['_picture']['thumbnail'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); ?>
' border='0' alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['product_info']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" title="<?php echo ((is_array($_tmp=$this->_tpl_vars['product_info']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" >
			</a>
			<?php else: ?>
			<img src='<?php echo @URL_PRODUCTS_PICTURES; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['_picture']['thumbnail'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); ?>
' border='0' alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['product_info']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" title="<?php echo ((is_array($_tmp=$this->_tpl_vars['product_info']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" >
			<?php endif; ?>
			</td>
			<?php if (( $this->_tpl_vars['_pict_num'] ) % 2 == 0): ?></tr><?php endif; ?>
		<?php endif; ?>
		<?php endforeach; endif; unset($_from); ?>
		<?php if (( $this->_tpl_vars['_pict_num']+1 ) % 2 == 0): ?></tr><?php endif; ?>
		</table>
		<?php endif; ?>
	<?php endif; ?>

	<?php if ($this->_tpl_vars['PAGE_VIEW'] == 'mobile' && $this->_tpl_vars['m_all_product_pictures']): ?>
		<table cellpadding="3" id="box_product_thumbnails">
		<?php $_from = $this->_tpl_vars['m_all_product_pictures']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['frpict'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['frpict']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['_picture']):
        $this->_foreach['frpict']['iteration']++;
?>
		<tr>
		<td align="center"><a href='<?php echo @URL_PRODUCTS_PICTURES; ?>
/<?php if ($this->_tpl_vars['_picture']['enlarged']): 
 echo ((is_array($_tmp=$this->_tpl_vars['_picture']['enlarged'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); 
 else: 
 echo ((is_array($_tmp=$this->_tpl_vars['_picture']['filename'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); 
 endif; ?>' img_width="<?php echo $this->_tpl_vars['_picture']['width']; ?>
" img_height="<?php echo $this->_tpl_vars['_picture']['height']; ?>
" img_enlarged="<?php if ($this->_tpl_vars['_picture']['enlarged']): 
 echo @URL_PRODUCTS_PICTURES; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['_picture']['enlarged'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); 
 endif; ?>" img_picture="<?php echo @URL_PRODUCTS_PICTURES; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['_picture']['filename'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); ?>
" target="_blank"><img src='<?php echo @URL_PRODUCTS_PICTURES; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['_picture']['thumbnail'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); ?>
' border='0' /></a></td>
		</tr>
		<?php endforeach; endif; unset($_from); ?>
		</table>

	<?php endif; ?>
	</div>
	
	<div id="controlbar" class="highslide-overlay controlbar">
		<a href="#" class="previous" onclick="return hs.previous(this)"></a>
		<a href="#" class="next" onclick="return hs.next(this)"></a>
	    <a href="#" class="close" onclick="return hs.close(this)"></a>
	</div>
<?php else: ?>
	<div style="text-align:center">
		<img border=0 src="<?php echo @URL_DEMOPRD_IMAGES; ?>
/picture1.jpg" alt="<?php echo 'Демо-продукт'; ?>
">
		<br>
		<table align="center"><tr><td><img src="<?php echo @URL_IMAGES; ?>
/enlarge.gif"></td><td>
			<a class="olive" href="#"><?php echo 'увеличить...'; ?>
</a>
		</td></tr></table>
	</div>
<?php endif; ?>