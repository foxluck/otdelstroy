<?php /* Smarty version 2.6.26, created on 2011-08-31 14:46:18
         compiled from frame.htm */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'wbs_initLayout', 'frame.htm', 4, false),array('function', 'wbs_errorBlock', 'frame.htm', 10, false),array('block', 'wbs_pageLayout', 'frame.htm', 9, false),)), $this); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<?php echo smarty_function_wbs_initLayout(array('splitter' => true,'toolbar' => true), $this);?>

</head>
<body bgcolor="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" class=noOverflow>
<?php echo ''; 
 echo ''; 
 $this->_tag_stack[] = array('wbs_pageLayout', array('tabbar' => "sc_tabbar.htm")); $_block_repeat=true;smarty_block_wbs_pageLayout($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); 
 echo ''; 
 echo smarty_function_wbs_errorBlock(array(), $this);
 echo ''; 
 if (! $this->_tpl_vars['fatalError']): 
 echo '<iframe width="100%" height="100%" name="sc_frame" id="sc_frame" src="index.php?'; 
 if ($_GET['did']): 
 echo 'did='; 
 echo $_GET['did']; 
 echo ''; 
 else: 
 echo 'ukey=admin'; 
 endif; 
 echo '" frameborder="0"></iframe>'; 
 endif; 
 echo ''; 
 $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_wbs_pageLayout($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); 
 echo ''; ?>

</body>
</html>