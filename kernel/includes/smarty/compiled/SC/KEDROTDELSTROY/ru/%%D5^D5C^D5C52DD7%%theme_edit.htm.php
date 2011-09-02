<?php /* Smarty version 2.6.26, created on 2011-08-31 14:46:24
         compiled from backend/theme_edit.htm */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'set_query_html', 'backend/theme_edit.htm', 6, false),array('modifier', 'translate', 'backend/theme_edit.htm', 8, false),array('modifier', 'comment', 'backend/theme_edit.htm', 67, false),)), $this); ?>
<table style="width:100%; height:100%;" cellpadding="0" cellspacing="0">
<tr>
	<td>
	<?php if (! $_GET['fullscreen']): ?>
	<h1 class="breadcrumbs">
		<a href="<?php echo ((is_array($_tmp='?ukey=themes_list')) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
"><?php echo 'Редактор дизайна'; ?>
</a>
		&raquo;
		<?php echo ((is_array($_tmp=$this->_tpl_vars['CurrentDivision']['name'])) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['theme']['title'])) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>

		&nbsp;
		<input value="<?php echo 'На весь экран'; ?>
" type="button" id="btn-open-fullscreen" class="small" />
		&nbsp;
		<a class="h1" href='<?php echo ((is_array($_tmp="?ukey=theme_preview&theme_id=".($this->_tpl_vars['theme_id']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
' target="_blank"><?php echo 'Посмотреть тему'; ?>
</a>
		| <a href="<?php echo ((is_array($_tmp='reset=true')) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
" title="<?php echo 'Вы уверены, что хотите сбросить все изменения, произведенные с этой темой? Действие необратимо.'; ?>
" class="h1 confirm_action"><?php echo 'Сбросить все изменения (вернуть к первоначальному виду)'; ?>
</a>
	</h1>
	<?php else: ?>
	<h1 class="breadcrumbs">
		<?php echo ((is_array($_tmp=$this->_tpl_vars['CurrentDivision']['name'])) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['theme']['title'])) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>

		&nbsp;
		<input value="<?php echo 'Закрыть'; ?>
" type="button" id="btn-close-fullscreen" class="small" />
	</h1>
	<?php endif; ?>
	
	<div id="hidden1">
	<?php echo $this->_tpl_vars['MessageBlock']; ?>


	<script type="text/javascript" src="<?php echo @URL_JS; ?>
/niftycube.js"></script>

	<ul id="edmod">
		<li class="tab <?php if ($this->_tpl_vars['tpl_id'] == @TPLID_GENERAL_LAYOUT): ?>current<?php endif; ?>"><a href='<?php echo ((is_array($_tmp="tpl_id=".(@TPLID_GENERAL_LAYOUT))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
'><?php echo 'Основная разметка'; ?>
</a></li>
		<li class="tab <?php if ($this->_tpl_vars['tpl_id'] == @TPLID_HOMEPAGE): ?>current<?php endif; ?>"><a href='<?php echo ((is_array($_tmp="tpl_id=".(@TPLID_HOMEPAGE))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
'><?php echo 'Витрина'; ?>
</a></li>
		<li class="tab <?php if ($this->_tpl_vars['tpl_id'] == @TPLID_PRODUCT_INFO): ?>current<?php endif; ?>"><a href='<?php echo ((is_array($_tmp="tpl_id=".(@TPLID_PRODUCT_INFO))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
'><?php echo 'Продукт'; ?>
</a></li>
		
		<li class="tab right_tab <?php if ($this->_tpl_vars['tpl_id'] == @TPLID_HEAD): ?>current<?php endif; ?>"><a href='<?php echo ((is_array($_tmp="tpl_id=".(@TPLID_HEAD))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
'><?php echo 'Head'; ?>
</a></li>
		<li id="tab-css<?php if ($this->_tpl_vars['tpl_id'] == @TPLID_CSS): ?>-current<?php endif; ?>" class="tab right_tab"><a href='<?php echo ((is_array($_tmp="tpl_id=".(@TPLID_CSS))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
'><?php echo 'Стили (CSS)'; ?>
</a></li>
	</ul>
	
	</div>
	<script type="text/javascript" src="<?php echo @URL_JS; ?>
/theme_edit.js"></script>
	<?php if ($_GET['fullscreen']): 
 echo '
	<script type="text/javascript">
	window.onunload = function(){
		if(window.opener && !window.opener.closed && window.dontcheck_onclose!==true){
			window.opener.checkFullScreen(true); 
		}
	};
	</script>
	'; 
 endif; ?>
	</td>
</tr>
<tr>
	<td height="100%">
		<div id="hidden2">
		
		<table style="width:100%; height:100%;" cellpadding="0" cellspacing="0">
		<?php if ($this->_tpl_vars['templates_info'][$this->_tpl_vars['tpl_id']]['simple_editor']): ?>
		<tr>
			<td>
			<ul id="advanced_navigation">
				<li <?php if ($this->_tpl_vars['edmod'] == 'simple'): ?>class="current"<?php endif; ?>><a href="<?php echo ((is_array($_tmp='edmod=simple')) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
"><?php echo 'Конструктор (WYSIWYG)'; ?>
</a></li>
				<li <?php if ($this->_tpl_vars['edmod'] == 'advanced'): ?>class="current"<?php endif; ?>><a href="<?php echo ((is_array($_tmp='edmod=advanced')) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
"><?php echo 'Редактировать HTML-код'; ?>
</a></li>
			</ul>
			</td>
		</tr>
		<?php endif; ?>
		<tr>
			<td>
			<?php echo ((is_array($_tmp=$this->_tpl_vars['comment_str'])) ? $this->_run_mod_handler('comment', true, $_tmp) : smarty_modifier_comment($_tmp)); ?>

			</td>
		<tr>
			<td height="100%" valign="top">
			<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => $this->_tpl_vars['edmod_file'], 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
			</td>
		</tr>
		</table>
		
		</div>
		
		<script type="text/javascript">
		translate.thm_open_fullscreen = "<?php echo 'На весь экран'; ?>
";
		translate.thm_close_fullscreen = "<?php echo 'Закрыть'; ?>
";
		translate.thm_allow_popups = "<?php echo 'Разрешите popup-окна в настройках браузера и нажмите по кнопке &quot;На весь экран&quot; еще раз.'; ?>
";
		Nifty("li.tab","top same-height");
		</script>
	</td>
</tr>
</table>