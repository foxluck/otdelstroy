<?php /* Smarty version 2.6.26, created on 2011-08-31 14:43:48
         compiled from login.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'translate', 'login.html', 1, false),array('modifier', 'set_query_html', 'login.html', 5, false),array('modifier', 'escape', 'login.html', 10, false),)), $this); ?>
<h1><?php echo ((is_array($_tmp=$this->_tpl_vars['CurrentDivision']['name'])) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</h1>

<?php echo $this->_tpl_vars['MessageBlock']; ?>


<form action="<?php echo ((is_array($_tmp='')) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
" method="post"><div class="form_wrapper">
<p><input name="enter" value="1" type="hidden" >

	<?php echo 'Логин'; ?>
:
	<br>
	<input type="text" name="user_login" id="ln_user_login" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['user_login'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" size="40" ></p>
	
	<p>
		<?php echo 'Пароль'; ?>
:
		<br>
		<input name="user_pw" type="password" size="40" >
	</p>
	
	<p>
		<input type="submit" value='<?php echo 'Вход'; ?>
' >
	</p>
	
	<p>
		<a rel="nofollow" href="<?php echo ((is_array($_tmp='?ukey=remind_password')) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
"><?php echo 'Забыли пароль?'; ?>
</a>
		&nbsp;
		<a rel="nofollow" href="<?php echo ((is_array($_tmp='?ukey=register')) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
"><?php echo 'Зарегистрироваться'; ?>
</a>
        &nbsp;
        <a rel="nofollow" href="<?php echo $this->_tpl_vars['admin_login_url']; ?>
"><?php echo 'Вход для администратора'; ?>
</a>
	</p>

</div></form>

<script type="text/javascript">
getLayer('ln_user_login').focus();
</script>