<?php /* Smarty version 2.6.26, created on 2011-08-31 17:26:06
         compiled from order_notification.txt */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'order_notification.txt', 9, false),)), $this); ?>
<h1><?php echo 'Спасибо за ваш выбор'; ?>
 <?php echo @CONF_SHOP_NAME; ?>
!</h1>

<h2><?php echo 'Номер заказа'; ?>
: <?php echo $this->_tpl_vars['order']['orderID_view']; ?>
</h2>

<p><?php echo 'Заказанные продукты'; ?>
:</p>

<blockquote><?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['content']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
<div><?php echo ((is_array($_tmp=$this->_tpl_vars['content'][$this->_sections['i']['index']]['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
 (x<?php echo $this->_tpl_vars['content'][$this->_sections['i']['index']]['Quantity']; ?>
): <?php echo $this->_tpl_vars['content'][$this->_sections['i']['index']]['Price']; ?>

<?php if ($this->_tpl_vars['content'][$this->_sections['i']['index']]['eproduct_filename']): ?>
<blockquote><?php echo 'Скачать'; ?>
 : <a href="<?php echo @CONF_FULL_SHOP_URL; 
 if (! @MOD_REWRITE_SUPPORT): ?>published/SC/html/scripts/<?php endif; ?>get_file.php?getFileParam=<?php echo $this->_tpl_vars['content'][$this->_sections['i']['index']]['getFileParam']; ?>
"><?php echo @CONF_FULL_SHOP_URL; 
 if (! @MOD_REWRITE_SUPPORT): ?>published/SC/html/scripts/<?php endif; ?>get_file.php?getFileParam=<?php echo $this->_tpl_vars['content'][$this->_sections['i']['index']]['getFileParam']; ?>
</a> (<?php echo $this->_tpl_vars['content'][$this->_sections['i']['index']]['file_size_str']; ?>
)
<?php if ($this->_tpl_vars['content'][$this->_sections['i']['index']]['eproduct_available_days']): ?>
- <?php echo 'Файл доступен '; ?>
 <?php echo $this->_tpl_vars['content'][$this->_sections['i']['index']]['eproduct_available_days']; ?>
 <?php echo 'дней'; ?>

<?php endif; ?>
<?php if ($this->_tpl_vars['content'][$this->_sections['i']['index']]['eproduct_download_times']): ?>
- <?php echo 'осталось'; ?>
 <?php echo $this->_tpl_vars['content'][$this->_sections['i']['index']]['eproduct_download_times']; ?>
 <?php echo 'скачиваний'; ?>

<?php endif; ?>
</blockquote>
<?php endif; ?>
</div>
<?php endfor; endif; ?></blockquote>

<?php if ($this->_tpl_vars['discount'] != ''): ?><p><?php echo 'скидка'; ?>
: <?php echo $this->_tpl_vars['discount']; ?>
</p><?php endif; ?>

<p><?php echo 'Общий налог на заказ'; ?>
: <?php echo $this->_tpl_vars['order_total_tax']; ?>
</p>

<h2><?php echo 'Итого'; ?>
 <?php echo $this->_tpl_vars['order_amount']; ?>
</h2>

<p><?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['customer_add_fields']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
<br /><?php echo ((is_array($_tmp=$this->_tpl_vars['customer_add_fields'][$this->_sections['i']['index']]['reg_field_name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['customer_add_fields'][$this->_sections['i']['index']]['reg_field_value'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>

<?php endfor; endif; ?></p>

<?php if ($this->_tpl_vars['shipping_type'] != ""): ?>
<h2><?php echo 'Доставка заказа'; ?>
:</h2>
<p><?php echo 'Доставка'; ?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['shipping_type'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); 
 if ($this->_tpl_vars['order']['shippingServiceInfo']): ?> (<?php echo ((is_array($_tmp=$this->_tpl_vars['order']['shippingServiceInfo'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
)<?php endif; ?>
<br /><?php echo 'Получатель'; ?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['shipping_firstname'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['shipping_lastname'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>

<br /><?php echo 'Адрес доставки заказа'; ?>
: <?php if ($this->_tpl_vars['shipping_address'] != ""): 
 echo ((is_array($_tmp=$this->_tpl_vars['shipping_address'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
, <?php endif; 
 if ($this->_tpl_vars['shipping_city'] != ""): 
 echo ((is_array($_tmp=$this->_tpl_vars['shipping_city'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
,<?php endif; 
 if ($this->_tpl_vars['shipping_state'] != ""): 
 echo ((is_array($_tmp=$this->_tpl_vars['shipping_state'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
 <?php endif; 
 if ($this->_tpl_vars['shipping_zip'] != ""): 
 echo ((is_array($_tmp=$this->_tpl_vars['shipping_zip'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
 <?php endif; 
 if ($this->_tpl_vars['shipping_country'] != ""): 
 echo ((is_array($_tmp=$this->_tpl_vars['shipping_country'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</p><?php endif; ?>

<p><?php echo 'Стоимость доставки'; ?>
: <?php echo $this->_tpl_vars['shipping_cost']; ?>

<?php if ($this->_tpl_vars['shipping_comments'] != ""): ?><br /><?php echo 'Информация по доставке'; ?>
: <?php echo $this->_tpl_vars['shipping_comments']; 
 endif; ?>
<?php endif; ?></p>

<?php if ($this->_tpl_vars['payment_type'] != ""): ?>
<h2><?php echo 'Оплата заказа'; ?>
:</h2>
<p><?php echo 'Оплата'; ?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['payment_type'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>

<br /><?php echo 'Плательщик'; ?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['billing_firstname'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['billing_lastname'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>

<br /><?php echo 'Адрес плательщика'; ?>
: <?php if ($this->_tpl_vars['billing_address'] != ""): 
 echo ((is_array($_tmp=$this->_tpl_vars['billing_address'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
, <?php endif; 
 if ($this->_tpl_vars['billing_city'] != ""): 
 echo ((is_array($_tmp=$this->_tpl_vars['billing_city'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
,<?php endif; 
 if ($this->_tpl_vars['billing_state'] != ""): 
 echo ((is_array($_tmp=$this->_tpl_vars['billing_state'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
 <?php endif; 
 if ($this->_tpl_vars['billing_zip'] != ""): 
 echo ((is_array($_tmp=$this->_tpl_vars['billing_zip'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
 <?php endif; 
 if ($this->_tpl_vars['billing_country'] != ""): 
 echo ((is_array($_tmp=$this->_tpl_vars['billing_country'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</p><?php endif; ?>

<?php if ($this->_tpl_vars['payment_comments'] != ""): ?><p><?php echo 'Информация об оплате'; ?>
: <?php echo $this->_tpl_vars['payment_comments']; ?>
</p><?php endif; ?>
<?php endif; ?>

<p><?php echo $this->_tpl_vars['order_status_url']; ?>
</p>

<p><?php echo 'Мы свяжемся с вами в ближайшее время.'; ?>
</p>

<p><?php echo 'С наилучшими пожеланиями'; ?>
, <?php echo @CONF_SHOP_NAME; ?>

<br /><?php echo @CONF_SHOP_URL; ?>
</p>