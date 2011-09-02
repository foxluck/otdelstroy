<?php /* Smarty version 2.6.26, created on 2011-08-31 14:46:24
         compiled from cpt_constructor.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'translate', 'cpt_constructor.html', 59, false),array('modifier', 'set_query_html', 'cpt_constructor.html', 61, false),array('modifier', 'escape', 'cpt_constructor.html', 61, false),)), $this); ?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<!-- Head start -->
		<?php echo $this->_tpl_vars['tpl_head']; ?>

<!-- Head end -->
		<?php if ($this->_tpl_vars['overridestyles']): ?><link rel="stylesheet" href="<?php echo $this->_tpl_vars['URL_THEME_OFFSET']; ?>
/overridestyles.css" type="text/css" id="orig-overridestyles" /><?php endif; ?>
		
		<link rel="alternate stylesheet" type="text/css" href="<?php echo $this->_tpl_vars['URL_THEME_OFFSET']; ?>
/overridestyles.css" title="overridestyles" id="overridestyles" media="screen" />
		
		<link rel="stylesheet" href="<?php echo $this->_tpl_vars['URL_THEME_OFFSET']; ?>
/main.css" type="text/css" />
		<link rel="stylesheet" href="<?php echo @URL_CSS; ?>
/cpt_constructor.css" type="text/css">
		<link rel="stylesheet" href="<?php echo @URL_CSS; ?>
/general.css" type="text/css" />
		<script type="text/javascript">
<?php if (@CONF_WAROOT_URL): ?>var WAROOT_URL = '<?php echo @CONF_WAROOT_URL; ?>
';<?php endif; ?>

<?php if (@CONF_ON_WEBASYST): ?>var CONF_ON_WEBASYST = '<?php echo @CONF_ON_WEBASYST; ?>
';<?php endif; ?>
		var translate = {
			'msg_unsaved_changes': '<?php echo 'Если вы покинете эту страницу, ваши изменения будут утеряны.'; ?>
'
			};
		var theme_id = '<?php echo $this->_tpl_vars['theme_id']; ?>
';
		</script>

		<script type="text/javascript" src="<?php echo @URL_JS; ?>
/functions.js"></script>
		<script type="text/javascript" src="<?php echo @URL_JS; ?>
/niftycube.js"></script>
		<script type="text/javascript" src="<?php echo @URL_JS; ?>
/lists.js"></script>
		<script type="text/javascript" src="<?php echo @URL_JS; ?>
/drag.js"></script>
		<script type="text/javascript" src="<?php echo @URL_JS; ?>
/behavior.js"></script>
		<script type="text/javascript" src="<?php echo @URL_JS; ?>
/JsHttpRequest.js"></script>
		<script type="text/javascript" src="<?php echo @URL_JS; ?>
/widget_checkout.js"></script>
		<script type="text/javascript" src="<?php echo @URL_JS; ?>
/cpt_constructor.js"></script>
		<script type="text/javascript" src="<?php echo $this->_tpl_vars['URL_THEME_OFFSET']; ?>
/head.js"></script>

	</head>
	<body>
		<style type="text/css"><?php echo '
.cpt_product_images{
display:block!important;
float: none!important;
}
		'; ?>
</style>
<?php if ($this->_tpl_vars['templated_changed']): ?>
<script type="text/javascript">
beforeUnloadHandler_contentChanged = true;
</script>
<?php endif; ?>
		<table width="100%" style="height:100%" cellpadding="0" cellspacing="0">
		<tr>
			<td rowspan="3" valign="top" id="ss-template-content"><?php echo $this->_tpl_vars['tpl_content']; ?>
</td>
			<td id="cpt-constructor-right-panel">
			<div style="text-align:center;">
			<input type="button" id="fm-save-template" value="<?php echo 'Сохранить шаблон'; ?>
" >
			</div>
			<br />
			
			<ul id="components-list">
			<?php $_from = $this->_tpl_vars['components']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['component']):
?>
			<li>
				<a href="#add_component" class="cpt_dontblock cpt_addcomponent_hndl" id="cpt-component-<?php echo $this->_tpl_vars['component']['id']; ?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['component']['name'])) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</a>
				<div style="display:none" id="cpt-lsettings-<?php echo $this->_tpl_vars['component']['id']; ?>
" class="cpt_lsettings">
				<form action="<?php echo ((is_array($_tmp='')) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
" method="post" id="cptlsettings-form-<?php echo ((is_array($_tmp=$this->_tpl_vars['component']['id'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" enctype="multipart/form-data"  class="cpt_dontblock">
					<input type="hidden" name="action" value="CPT_PREPARE_HTMLCODE" >
					<input type="hidden" name="component_id" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['component']['id'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" >
					<?php echo $this->_tpl_vars['component']['__lsettings_form']; ?>

					<p><input type="submit" value="<?php echo 'Добавить в шаблон'; ?>
" id="cptlsettings-submit-<?php echo ((is_array($_tmp=$this->_tpl_vars['component']['id'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" class="cptlsettings_submit" ></p>
				</form>
				</div>
			</li>
				<?php endforeach; endif; unset($_from); ?>
			</ul>
			
			<table align="center" width="100%">
			<tr>
				<td valign="top" align="center" height="1%"><strong><?php echo 'Корзина'; ?>
</strong></td>
			</tr>
			<tr>
				<td class="cpt_container" id="ssTrashBin" rel="<?php echo 'Переместите компонент сюда для удаления'; ?>
"><?php echo 'Переместите компонент сюда для удаления'; ?>
</td>
			</tr>
			</table>
			</td>
		</tr>
		</table>
		<div id="dnd-dblckick-tooltip"><?php echo 'Перетащите или двойной клик'; ?>
</div>
	</body>
</html> 