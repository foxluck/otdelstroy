<?php /* Smarty version 2.6.26, created on 2011-08-31 14:46:24
         compiled from customer_survey.tpl.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'math', 'customer_survey.tpl.html', 8, false),array('modifier', 'set_query_html', 'customer_survey.tpl.html', 29, false),)), $this); ?>

<div class="survey_question"><?php echo $this->_tpl_vars['survey_question']; ?>
</div>
	
<?php if ($this->_tpl_vars['show_survey_results'] == 1): ?>
	<table>
	<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['survey_answers']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
	<?php echo smarty_function_math(array('equation' => "round(100 * ".($this->_tpl_vars['survey_results'][$this->_sections['i']['index']])." / ".($this->_tpl_vars['voters_count']).")",'assign' => '_percent'), $this);?>

	<tr>
		<td><?php echo $this->_tpl_vars['survey_answers'][$this->_sections['i']['index']]; ?>
</td>
		<td width="1%">
			<?php if ($this->_tpl_vars['voters_count'] != 0): 
 echo $this->_tpl_vars['_percent']; 
 else: ?>0<?php endif; ?>%
		</td>
	</tr>
	<?php endfor; endif; ?>
	</table>


<?php else: ?>		<form action="<?php echo ((is_array($_tmp='')) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
" method="get">
	
	<input type="hidden" name="save_voting_results" value="yes" >
	<table>
	<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['survey_answers']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
	<tr>
		<td><input type="radio" name="answer" id="srv-answer-<?php echo $this->_sections['i']['index']; ?>
" value="<?php echo $this->_sections['i']['index']; ?>
" ></td>
		<td><label for="srv-answer-<?php echo $this->_sections['i']['index']; ?>
"><?php echo $this->_tpl_vars['survey_answers'][$this->_sections['i']['index']]; ?>
</label></td>
	</tr>
	<?php endfor; endif; ?>
	</table>
	
	<div class="survey_submit"><input type="submit" value="<?php echo 'OK'; ?>
" ></div>
	
	</form>
<?php endif; ?>