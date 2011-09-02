<?php /* Smarty version 2.6.26, created on 2011-08-31 17:20:56
         compiled from register_successful.txt */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'translate', 'register_successful.txt', 7, false),array('modifier', 'replace', 'register_successful.txt', 7, false),array('modifier', 'escape', 'register_successful.txt', 10, false),)), $this); ?>
<h1><?php echo 'Вы успешно зарегистрировались в'; ?>
 <?php echo @CONF_SHOP_NAME; ?>
</h1>

<h2><?php echo 'Ваша регистрационная информация:'; ?>
</h2>

<?php if (@CONF_ENABLE_REGCONFIRMATION): ?>
<p><?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp='email_regconfirmation')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)))) ? $this->_run_mod_handler('replace', true, $_tmp, "[code]", "<b>".($this->_tpl_vars['ActCode'])."</b>") : smarty_modifier_replace($_tmp, "[code]", "<b>".($this->_tpl_vars['ActCode'])."</b>")))) ? $this->_run_mod_handler('replace', true, $_tmp, "[codeurl]", $this->_tpl_vars['ActURL']) : smarty_modifier_replace($_tmp, "[codeurl]", $this->_tpl_vars['ActURL'])); ?>
</p>

<?php endif; ?>
<p><?php echo 'Логин'; ?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['login'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>

<br /><?php echo 'Пароль'; ?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['cust_password'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>

<br /><?php echo 'Имя'; ?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['first_name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>

<br /><?php echo 'Фамилия'; ?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['last_name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>

<br /><?php echo 'Email'; ?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['Email'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>

<?php if ($this->_tpl_vars['additional_field_values']): ?>
<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['additional_field_values']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>
<br /><?php echo ((is_array($_tmp=$this->_tpl_vars['additional_field_values'][$this->_sections['i']['index']]['reg_field_name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['additional_field_values'][$this->_sections['i']['index']]['reg_field_value'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>

<?php endfor; endif; ?>	
<?php endif; ?>
<?php if ($this->_tpl_vars['addresses']): ?>
<br /><?php echo 'Следующие адреса успешно добавлены в адресную книгу'; ?>
:
<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['addresses']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>
<br /><?php echo $this->_tpl_vars['addresses'][$this->_sections['i']['index']]['addressStr']; ?>

<?php endfor; endif; ?>
<?php endif; ?></p>

<p><?php echo 'С наилучшими пожеланиями'; ?>
, <?php echo @CONF_SHOP_NAME; ?>

<br /><?php echo @CONF_SHOP_URL; ?>
</p>