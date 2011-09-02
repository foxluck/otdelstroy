<?php /* Smarty version 2.6.26, created on 2011-08-31 17:23:17
         compiled from change_address.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'set_query_html', 'change_address.html', 13, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "checkout.progress.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php if ($this->_tpl_vars['PAGE_VIEW'] == 'noframe'): ?>
	
	<div class="paddingblock">
	
	<h2><?php echo 'Изменить адрес'; ?>
</h2>
<?php else: ?>
	<h1><?php echo 'Изменить адрес'; ?>
</h1>
<?php endif; ?>

<?php echo $this->_tpl_vars['MessageBlock']; ?>


<form method="post" action="<?php echo ((is_array($_tmp='')) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
" name='address_selection_form'>
	<input id="action" name="action" value="select_address" type="hidden" >

	<table border="0" cellspacing="1" cellpadding="4">
	<?php $_from = $this->_tpl_vars['addresses']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['_address']):
?>
	<tr>
		<td>
			<input name="addressID" class="address_id_radio" id="address-id-<?php echo $this->_tpl_vars['_address']['addressID']; ?>
" value="<?php echo $this->_tpl_vars['_address']['addressID']; ?>
" <?php if ($this->_tpl_vars['addressID'] == $this->_tpl_vars['_address']['addressID']): ?>checked<?php endif; ?> type="radio" >
		</td>
		<td><label for="address-id-<?php echo $this->_tpl_vars['_address']['addressID']; ?>
"><?php echo $this->_tpl_vars['_address']['strAddress']; ?>
</label></td>
	</tr>
	<?php endforeach; endif; unset($_from); ?>
	<tr>
		<td valign="top">
			<input id="address-id-0" class="address_id_radio" name="addressID" value="0" <?php if (! $this->_tpl_vars['addressID']): ?>checked<?php endif; ?> type="radio" >
		</td>
		<td>
			<label for="address-id-0"><strong><?php echo 'Другой адрес'; ?>
:</strong></label>
			<br />
			<div id="blck-new-address">
			<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "address_form.html", 'smarty_include_vars' => array('name_space' => 'address','address' => $this->_tpl_vars['address'],'form_name' => 'address_selection_form','ukey' => 'address_editor')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
			</div>
		</td>
	</tr>
	<tr>
		<td></td>
		<td><input type="submit" value='<?php echo 'Выбрать'; ?>
' ></td>
	</tr>
	</table>
</form>
<?php echo '<font color=red>*</font> обязательны для заполнения'; ?>


<?php if ($this->_tpl_vars['PAGE_VIEW'] == 'noframe'): ?>
	</div>
<?php endif; ?>

<?php if ($this->_tpl_vars['PAGE_VIEW'] != 'mobile'): ?>
<script type="text/javascript">
<?php echo '
function blockAddressForm(e){
	if(!e || e.className != \'address_id_radio\')e = this;
	var elems = getElementsByClass(\'address_elem\', getLayer(\'blck-new-address\'));
	for(var i=0,i_max=elems.length; i<i_max; i++){
		elems[i].disabled = e.value != 0; 
	}
}
	/*\'.country_box\': function(e){
		e.onchange = function(){
			
			var objForm = getFormByElem(this);
			getLayer(\'action\').value = \'change_country\';
			objForm.submit();
		}
	},*/
var hndls = {

	\'.address_id_radio\': function(e){
		e.onclick = blockAddressForm;
	}
};
Behaviour.register(hndls);
'; ?>


if(getLayer('address-id-<?php echo $this->_tpl_vars['addressID']; ?>
'))blockAddressForm(getLayer('address-id-<?php echo $this->_tpl_vars['addressID']; ?>
'));;
</script>
<?php endif; ?>