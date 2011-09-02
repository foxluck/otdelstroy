<?php /* Smarty version 2.6.26, created on 2011-08-31 14:55:32
         compiled from product_params_selectable.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'counter', 'product_params_selectable.html', 4, false),array('modifier', 'escape', 'product_params_selectable.html', 10, false),)), $this); ?>
<?php if (! $this->_tpl_vars['CPT_CONSTRUCTOR_MODE']): ?>

<table>
<?php echo smarty_function_counter(array('name' => 'select_counter','start' => 0,'skip' => 1,'print' => false,'assign' => 'select_counter_var'), $this);?>

<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['product_extra']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
	<?php if ($this->_tpl_vars['product_extra'][$this->_sections['i']['index']]['option_type'] != 0): ?>
	<?php unset($this->_sections['k']);
$this->_sections['k']['name'] = 'k';
$this->_sections['k']['loop'] = is_array($_loop=$this->_tpl_vars['product_extra'][$this->_sections['i']['index']]['option_show_times']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['k']['show'] = true;
$this->_sections['k']['max'] = $this->_sections['k']['loop'];
$this->_sections['k']['step'] = 1;
$this->_sections['k']['start'] = $this->_sections['k']['step'] > 0 ? 0 : $this->_sections['k']['loop']-1;
if ($this->_sections['k']['show']) {
    $this->_sections['k']['total'] = $this->_sections['k']['loop'];
    if ($this->_sections['k']['total'] == 0)
        $this->_sections['k']['show'] = false;
} else
    $this->_sections['k']['total'] = 0;
if ($this->_sections['k']['show']):

            for ($this->_sections['k']['index'] = $this->_sections['k']['start'], $this->_sections['k']['iteration'] = 1;
                 $this->_sections['k']['iteration'] <= $this->_sections['k']['total'];
                 $this->_sections['k']['index'] += $this->_sections['k']['step'], $this->_sections['k']['iteration']++):
$this->_sections['k']['rownum'] = $this->_sections['k']['iteration'];
$this->_sections['k']['index_prev'] = $this->_sections['k']['index'] - $this->_sections['k']['step'];
$this->_sections['k']['index_next'] = $this->_sections['k']['index'] + $this->_sections['k']['step'];
$this->_sections['k']['first']      = ($this->_sections['k']['iteration'] == 1);
$this->_sections['k']['last']       = ($this->_sections['k']['iteration'] == $this->_sections['k']['total']);
?>
	<tr>					
		<td>
			<?php echo ((is_array($_tmp=$this->_tpl_vars['product_extra'][$this->_sections['i']['index']]['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); 
 if ($this->_tpl_vars['product_extra'][$this->_sections['i']['index']]['option_show_times'] > 1): ?>(<?php echo smarty_function_counter(array('name' => 'option_show_times'), $this);?>
):<?php else: ?>:<?php endif; ?>
		</td>
		<td>
			<?php echo smarty_function_counter(array('name' => 'select_counter','assign' => '_cnt'), $this);?>

			<select name='option_<?php echo $this->_tpl_vars['_cnt']; ?>
' class="product_option">
			<option value='' rel="0"><?php echo 'Не определено'; ?>
</option>
			<?php unset($this->_sections['j']);
$this->_sections['j']['name'] = 'j';
$this->_sections['j']['loop'] = is_array($_loop=$this->_tpl_vars['product_extra'][$this->_sections['i']['index']]['values_to_select']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['j']['show'] = true;
$this->_sections['j']['max'] = $this->_sections['j']['loop'];
$this->_sections['j']['step'] = 1;
$this->_sections['j']['start'] = $this->_sections['j']['step'] > 0 ? 0 : $this->_sections['j']['loop']-1;
if ($this->_sections['j']['show']) {
    $this->_sections['j']['total'] = $this->_sections['j']['loop'];
    if ($this->_sections['j']['total'] == 0)
        $this->_sections['j']['show'] = false;
} else
    $this->_sections['j']['total'] = 0;
if ($this->_sections['j']['show']):

            for ($this->_sections['j']['index'] = $this->_sections['j']['start'], $this->_sections['j']['iteration'] = 1;
                 $this->_sections['j']['iteration'] <= $this->_sections['j']['total'];
                 $this->_sections['j']['index'] += $this->_sections['j']['step'], $this->_sections['j']['iteration']++):
$this->_sections['j']['rownum'] = $this->_sections['j']['iteration'];
$this->_sections['j']['index_prev'] = $this->_sections['j']['index'] - $this->_sections['j']['step'];
$this->_sections['j']['index_next'] = $this->_sections['j']['index'] + $this->_sections['j']['step'];
$this->_sections['j']['first']      = ($this->_sections['j']['iteration'] == 1);
$this->_sections['j']['last']       = ($this->_sections['j']['iteration'] == $this->_sections['j']['total']);
?>
			<option value='<?php echo $this->_tpl_vars['product_extra'][$this->_sections['i']['index']]['values_to_select'][$this->_sections['j']['index']]['variantID']; ?>
' rel='<?php echo $this->_tpl_vars['product_extra'][$this->_sections['i']['index']]['values_to_select'][$this->_sections['j']['index']]['price_surplus']; ?>
'
			<?php if ($this->_tpl_vars['product_extra'][$this->_sections['i']['index']]['values_to_select'][$this->_sections['j']['index']]['variantID'] == $this->_tpl_vars['product_extra'][$this->_sections['i']['index']]['variantID']): ?>selected="selected"<?php endif; ?>>
				<?php echo $this->_tpl_vars['product_extra'][$this->_sections['i']['index']]['values_to_select'][$this->_sections['j']['index']]['option_value']; ?>

			</option>
			<?php endfor; endif; ?>
			</select>
		</td>
	</tr>
	<?php endfor; endif; ?>
	<?php endif; ?>
<?php endfor; endif; ?>
</table>

<?php else: ?>
	<table>
	<tr>					
	<td>Color:			</td>
	<td>
	
	<select name='option_select_1_71' onchange='GetCurrentCurrency_71()'>
	<option value='0.00:8'>
	white
	</option>
	<option value='0.00:5'>
	
	yellow
	</option>
	<option value='0.00:6'>
	green
	</option>
	</select>
	</td>
	</tr>
	</table>
<?php endif; ?>