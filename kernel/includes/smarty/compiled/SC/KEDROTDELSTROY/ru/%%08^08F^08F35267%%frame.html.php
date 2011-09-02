<?php /* Smarty version 2.6.26, created on 2011-08-31 14:43:46
         compiled from frame.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'set_query_html', 'frame.html', 33, false),)), $this); ?>
<?php if ($this->_tpl_vars['PAGE_VIEW'] == 'noframe' && ( $this->_tpl_vars['CurrentDivision']['ukey'] == 'cart' || $this->_tpl_vars['CurrentDivision']['ukey'] == 'checkout' )): ?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<?php endif; ?><html dir="<?php if ($this->_tpl_vars['lang_direction']): ?>rtl<?php else: ?>ltr<?php endif; ?>">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<base href="<?php echo @CONF_FULL_SHOP_URL; ?>
">
<?php if ($this->_tpl_vars['rss_link']): ?>	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php echo @URL_ROOT; ?>
/<?php echo $this->_tpl_vars['rss_link']; ?>
"><?php endif; ?>
	<script type="text/javascript">
<?php if (@CONF_WAROOT_URL): ?>		var WAROOT_URL = '<?php echo @BASE_WA_URL; ?>
';//ok<?php endif; ?>

<?php if (@CONF_ON_WEBASYST): ?>		var CONF_ON_WEBASYST = '<?php echo @CONF_ON_WEBASYST; ?>
';<?php endif; ?>
	</script>
	
<!-- Head start -->
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "head.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<!-- Head end -->

<?php if ($this->_tpl_vars['overridestyles']): ?>	<link rel="stylesheet" href="<?php echo $this->_tpl_vars['URL_THEME_OFFSET']; ?>
/overridestyles.css" type="text/css"><?php endif; ?>
	<script type="text/javascript" src="<?php echo $this->_tpl_vars['URL_THEME_OFFSET']; ?>
/head.js"></script>
	<link rel="stylesheet" href="<?php echo $this->_tpl_vars['URL_THEME_OFFSET']; ?>
/main.css" type="text/css">
	<link rel="stylesheet" href="<?php echo @URL_CSS; ?>
/general.css" type="text/css">
<?php if (! $this->_tpl_vars['page_not_found404']): ?>
	<script type="text/javascript" src="<?php echo @URL_JS; ?>
/functions.js"></script>
	<script type="text/javascript" src="<?php echo @URL_JS; ?>
/behavior.js"></script>
	<script type="text/javascript" src="<?php echo @URL_JS; ?>
/widget_checkout.js"></script>
	<script type="text/javascript" src="<?php echo @URL_JS; ?>
/frame.js"></script>
	<script type="text/javascript">
<!--		
<?php echo $this->_tpl_vars['current_currency_js']; ?>

var ORIG_URL = '<?php echo @CONF_FULL_SHOP_URL; ?>
';
var ORIG_LANG_URL = '<?php echo ((is_array($_tmp="?")) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
';
window.currDispTemplate = defaultCurrency.display_template;
var translate = {};
translate.cnfrm_unsubscribe = '<?php echo 'Вы уверены, что хотите удалить вашу учетную запись в магазине?'; ?>
';
translate.err_input_email = '<?php echo 'Введите правильный электронный адрес'; ?>
';
translate.err_input_nickname = '<?php echo 'Пожалуйста, введите Ваш псевдоним'; ?>
';
translate.err_input_message_subject = '<?php echo 'Пожалуйста, введите тему сообщения'; ?>
';
translate.err_input_price = '<?php echo 'Цена должна быть положительным числом'; ?>
';
<?php echo 'function position_this_window(){
	var x = (screen.availWidth - 600) / 2;
	window.resizeTo(600, screen.availHeight - 100);
	window.moveTo(Math.floor(x),50);
}'; ?>
		
<?php if ($this->_tpl_vars['PAGE_VIEW'] == 'printable'): ?>Behaviour.addLoadEvent(function(){position_this_window();setTimeout(window.print(),1000);});<?php endif; ?>
//-->
</script>
<?php endif; ?>
	</head>
	<body <?php echo $this->_tpl_vars['GOOGLE_ANALYTICS_SET_TRANS']; 
 if ($this->_tpl_vars['main_body_style']): 
 echo $this->_tpl_vars['main_body_style']; 
 endif; 
 if ($this->_tpl_vars['PAGE_VIEW'] == 'printable'): ?> style="background-color:#FFFFFF;background-image:none;"<?php endif; 
 if ($this->_tpl_vars['page_not_found404']): ?> class="body-page-404"<?php endif; ?>>
<!--  BODY -->
<?php if ($this->_tpl_vars['main_body_tpl']): 
 $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => $this->_tpl_vars['main_body_tpl'], 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 
 else: ?>
<?php if ($this->_tpl_vars['page_not_found404']): 
 $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "404.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 
 else: 
 $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "index.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 
 endif; ?>
<?php endif; ?>
<?php if (! $_GET['productwidget'] && ! $this->_tpl_vars['productwidget'] && ! $this->_tpl_vars['printable_version'] && $this->_tpl_vars['show_powered_by']): ?>
<div id="powered_by">
<?php if ($this->_tpl_vars['show_powered_by_link']): ?>
	<?php echo 'Работает на основе <a href="http://www.shop-script.ru/" style="font-weight: normal">скрипта интернет-магазина</a> <em>WebAsyst Shop-Script</em>'; ?>

<?php else: ?>
	<?php echo 'Работает на основе <em>WebAsyst Shop-Script</em>'; ?>

<?php endif; ?>
</div><?php endif; ?>

<!--  END -->
<?php if (! $this->_tpl_vars['page_not_found404'] && ! $this->_tpl_vars['printable_version']): ?>
<?php echo $this->_tpl_vars['GOOGLE_ANALYTICS_CODE']; ?>

<?php endif; ?>
	</body>
</html>