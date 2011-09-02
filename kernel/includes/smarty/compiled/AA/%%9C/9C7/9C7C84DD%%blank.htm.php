<?php /* Smarty version 2.6.26, created on 2011-08-31 14:43:52
         compiled from blank.htm */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'wbs_initLayout', 'blank.htm', 4, false),array('function', 'wbs_errorBlock', 'blank.htm', 8, false),array('block', 'wbs_pageLayout', 'blank.htm', 7, false),)), $this); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<?php echo smarty_function_wbs_initLayout(array('disableExt' => true), $this);?>

</head>
<body>
	<?php $this->_tag_stack[] = array('wbs_pageLayout', array('contentClass' => 'NotFullWidth')); $_block_repeat=true;smarty_block_wbs_pageLayout($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
		<?php echo smarty_function_wbs_errorBlock(array(), $this);?>


		<?php echo $this->_tpl_vars['kernelStrings']['app_welcomenote_text']; ?>

		
	<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_wbs_pageLayout($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
</body>
</html>