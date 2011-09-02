<?php /* Smarty version 2.6.26, created on 2011-08-31 14:46:22
         compiled from backend/themes_list.htm */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'translate', 'backend/themes_list.htm', 1, false),array('modifier', 'escape', 'backend/themes_list.htm', 8, false),array('modifier', 'date_format', 'backend/themes_list.htm', 11, false),array('modifier', 'set_query_html', 'backend/themes_list.htm', 14, false),)), $this); ?>
	<h1><?php echo ((is_array($_tmp=$this->_tpl_vars['CurrentDivision']['name'])) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</h1>

	<?php echo $this->_tpl_vars['MessageBlock']; ?>


	<h2><?php echo 'Текущая тема'; ?>
</h2>
	<table>
	<tr>
		<td valign="top"><img src="<?php echo ((is_array($_tmp=$this->_tpl_vars['current_theme']['thumbnail_url'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" hspace="8" /></td>
		<td valign="top">
			<h2><?php echo ((is_array($_tmp=$this->_tpl_vars['current_theme']['title'])) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>

			<?php if ($this->_tpl_vars['current_theme']['last_modified']): ?><span class="thm_last_modified">(<?php echo 'последний раз изменялась'; ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['current_theme']['last_modified'])) ? $this->_run_mod_handler('date_format', true, $_tmp) : smarty_modifier_date_format($_tmp)); ?>
)</span><?php endif; ?></h2>
			<?php if ($this->_tpl_vars['current_theme']['author']): ?><div><?php echo 'Автор'; ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['current_theme']['author'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</div><br /><?php endif; ?>
			<div>
			<a href='<?php echo ((is_array($_tmp="?ukey=theme_preview&theme_id=".($this->_tpl_vars['current_theme']['id']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
' target="_blank"><?php echo 'Посмотреть тему'; ?>
</a>
			| <a href='<?php echo ((is_array($_tmp="?ukey=theme_edit&theme_id=".($this->_tpl_vars['current_theme']['id']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
'><?php echo 'Редактировать'; ?>
</a>
			</div>
		</td>
	</tr>
	</table>
	
	<h2><?php echo 'Другие темы'; ?>
</h2>
	<table>
	<?php $_from = $this->_tpl_vars['themes']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['theme']):
?>
	<tr>
		<td valign="top"><img src="<?php echo ((is_array($_tmp=$this->_tpl_vars['theme']['thumbnail_url'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" hspace="8"></td>
		<td valign="top">
			<h2><?php echo ((is_array($_tmp=$this->_tpl_vars['theme']['title'])) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>

			<?php if ($this->_tpl_vars['theme']['last_modified']): ?><span class="thm_last_modified">(<?php echo 'последний раз изменялась'; ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['theme']['last_modified'])) ? $this->_run_mod_handler('date_format', true, $_tmp) : smarty_modifier_date_format($_tmp)); ?>
)</span><?php endif; ?></h2>
			<?php if ($this->_tpl_vars['theme']['author']): ?><div><?php echo 'Автор'; ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['theme']['author'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</div><br /><?php endif; ?>
			<div>
			<a href='<?php echo ((is_array($_tmp="?ukey=theme_preview&theme_id=".($this->_tpl_vars['theme']['id']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
' target="_blank"><?php echo 'Посмотреть тему'; ?>
</a>
			| <a href='<?php echo ((is_array($_tmp="?ukey=theme_edit&theme_id=".($this->_tpl_vars['theme']['id']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
'><?php echo 'Редактировать'; ?>
</a>
			| <a href='<?php echo ((is_array($_tmp="action=apply_theme&theme_id=".($this->_tpl_vars['theme']['id']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
'><?php echo 'Сделать текущей'; ?>
</a></div>
		</td>
	</tr>
	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
	<?php endforeach; else: ?>
	<tr>
		<td colspan="2"><?php echo 'Новые варианты дизайна будут представлены скоро'; ?>
</td>
	</tr>
	<?php endif; unset($_from); ?>
	</table>