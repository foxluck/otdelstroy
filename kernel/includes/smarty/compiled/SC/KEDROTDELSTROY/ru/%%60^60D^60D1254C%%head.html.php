<?php /* Smarty version 2.6.26, created on 2011-08-31 14:43:46
         compiled from head.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'default', 'head.html', 1, false),array('modifier', 'escape', 'head.html', 1, false),)), $this); ?>
<title><?php echo ((is_array($_tmp=((is_array($_tmp=@$this->_tpl_vars['page_title'])) ? $this->_run_mod_handler('default', true, $_tmp, @CONF_DEFAULT_TITLE) : smarty_modifier_default($_tmp, @CONF_DEFAULT_TITLE)))) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</title>
<?php echo $this->_tpl_vars['page_meta_tags']; ?>

<script type="text/javascript" src="<?php echo @URL_JS; ?>
/niftycube.js"></script>
<meta name='yandex-verification' content='608a17bf35fd6643' />