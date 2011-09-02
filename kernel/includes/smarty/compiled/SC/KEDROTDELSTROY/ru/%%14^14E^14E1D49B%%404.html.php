<?php /* Smarty version 2.6.26, created on 2011-08-31 17:21:37
         compiled from 404.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', '404.html', 4, false),)), $this); ?>
<div id="all-container" style="padding:0px 20px;">
	<h1 style="font-size: 200%; margin-top: 10px;">
	<span style="background-color: #ddeeff;font-size: 150%; padding: 20px 10px 5px 10px;">404</span> &mdash; <?php echo 'Не найдено'; ?>
</h1>
	<h4><?php echo 'Извините, запрашиваемый документ не был найден на сервере'; ?>
: <span><?php echo ((is_array($_tmp=$this->_tpl_vars['link404'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</span></h4>
	<p><?php echo 'Перейдите по ссылке:'; ?>
</p>
	<ul>
<li style="padding-bottom: 5px;"><a href="<?php echo @CONF_FULL_SHOP_URL; ?>
" style="font-weight: bold; font-size: 110%"><?php echo ((is_array($_tmp=@CONF_SHOP_NAME)) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</a> <span style="color: #999; font-weight: bold"> — <?php echo 'Главная'; ?>
</span></li>
</ul>
</div>