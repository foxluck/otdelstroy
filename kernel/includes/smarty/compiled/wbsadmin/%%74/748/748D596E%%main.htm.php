<?php /* Smarty version 2.6.26, created on 2011-08-31 14:43:38
         compiled from main.htm */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'translate', 'main.htm', 7, false),array('modifier', 'escape', 'main.htm', 30, false),)), $this); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="robots" content="noindex, nofollow">
<?php if ($this->_tpl_vars['meta']): 
 echo $this->_tpl_vars['meta']; 
 endif; ?>
<title><?php echo ((is_array($_tmp='6')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); 
 if ($this->_tpl_vars['pageTitle']): ?> &mdash; <?php echo ((is_array($_tmp=$this->_tpl_vars['pageTitle'])) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); 
 endif; ?></title>
<link href="../../html/classic/installer-styles.css" rel="stylesheet" type="text/css" >
<script type="text/javascript" language="javascript" src="../../../common/html/classic/styles/wbscommon.js"></script>
<script type="text/javascript" language="javascript" src="../classic/updatewa.js"></script>	

</head>
<body>
<?php echo ''; 
 echo '<div style="position: absolute; top: 0px; right: 20px; color: #999;">'; 
 if ($this->_tpl_vars['mainMenu']): 
 echo '<em>'; 
 echo ((is_array($_tmp='upd_m_wa_license')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); 
 echo ''; 
 if ($this->_tpl_vars['installInfo']['LICENSE']): 
 echo ':</em> '; 
 echo $this->_tpl_vars['installInfo']['LICENSE']; 
 echo ''; 
 else: 
 echo ' '; 
 echo ((is_array($_tmp='upd_m_wa_license_not_reg')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); 
 echo '</em>'; 
 endif; 
 echo ''; 
 endif; 
 echo '</div><div class="main-navigation"><div style="margin: 7px;">'; 
 if ($this->_tpl_vars['mainMenu']): 
 echo '<a href="wbsadmin.php"'; 
 else: 
 echo '<span'; 
 endif; 
 echo ' class="installer-logo"><span style="font-size:225%;">web<i style="color: rgb(119, 204, 255);">Asyst</i> <em style="color: #777;">Installer</em></span>'; 
 if ($this->_tpl_vars['mainMenu']): 
 echo '</a>'; 
 else: 
 echo '</span>'; 
 endif; 
 echo '</div>'; 
 if ($this->_tpl_vars['mainMenu']): 
 echo '<ul>'; 
 $_from = $this->_tpl_vars['mainMenu']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['mainMenuItem']):

 echo '<li'; 
 if (! $this->_tpl_vars['mainMenuItem']['link']): 
 echo ' class="active white"'; 
 endif; 
 echo '>'; 
 if ($this->_tpl_vars['mainMenuItem']['link']): 
 echo '<a href="'; 
 echo $this->_tpl_vars['mainMenuItem']['link']; 
 echo '" title="'; 
 echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['mainMenuItem']['title'])) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); 
 echo '">'; 
 endif; 
 echo ''; 
 if ($this->_tpl_vars['mainMenuItem']['img']): 
 echo '<img src="'; 
 echo $this->_tpl_vars['mainMenuItem']['img']; 
 echo '" alt="'; 
 echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['mainMenuItem']['title'])) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); 
 echo '">'; 
 else: 
 echo ''; 
 echo ((is_array($_tmp=$this->_tpl_vars['mainMenuItem']['title'])) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); 
 echo ''; 
 endif; 
 echo ''; 
 if ($this->_tpl_vars['mainMenuItem']['link']): 
 echo '</a>'; 
 endif; 
 echo '</li>'; 
 endforeach; endif; unset($_from); 
 echo '</ul>'; 
 endif; 
 echo '</div>'; 
 echo ''; 
 echo ''; 
 if ($this->_tpl_vars['subMenu']): 
 echo '<div class="sub-navigation"><ul>'; 
 $_from = $this->_tpl_vars['subMenu']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['subMenuItem']):

 echo ''; 
 if ($this->_tpl_vars['subMenuItem']['link']): 
 echo '<li><a href="'; 
 echo $this->_tpl_vars['subMenuItem']['link']; 
 echo '" title="'; 
 if ($this->_tpl_vars['subMenuItem']['description']): 
 echo ''; 
 echo ((is_array($_tmp=$this->_tpl_vars['subMenuItem']['description'])) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); 
 echo ''; 
 else: 
 echo ''; 
 echo ((is_array($_tmp=$this->_tpl_vars['subMenuItem']['title'])) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); 
 echo ''; 
 endif; 
 echo '">'; 
 echo ((is_array($_tmp=$this->_tpl_vars['subMenuItem']['title'])) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); 
 echo '</a>'; 
 else: 
 echo '<li class="active"><strong>'; 
 echo ((is_array($_tmp=$this->_tpl_vars['subMenuItem']['title'])) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); 
 echo '</strong>'; 
 endif; 
 echo ''; 
 if ($this->_tpl_vars['subMenuItem']['warning']): 
 echo '&nbsp;<em style="color: red; font-weight: bold;">!</em>'; 
 endif; 
 echo '</li>'; 
 endforeach; endif; unset($_from); 
 echo '</ul></div>'; 
 endif; 
 echo ''; 
 echo ''; 
 if ($this->_tpl_vars['message']): 
 echo '<div id="message-block" class="'; 
 if ($this->_tpl_vars['messageType']): 
 echo 'error_block'; 
 else: 
 echo 'success_block'; 
 endif; 
 echo '">'; 
 echo ((is_array($_tmp=$this->_tpl_vars['message'])) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); 
 echo '</div>'; 
 endif; 
 echo ''; ?>

<div class="i-wrapper">

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => $this->_tpl_vars['mainTemplate'], 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

</div>
</body>
</html>