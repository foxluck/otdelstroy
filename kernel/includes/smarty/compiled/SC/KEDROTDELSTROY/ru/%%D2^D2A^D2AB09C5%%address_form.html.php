<?php /* Smarty version 2.6.26, created on 2011-08-31 17:18:28
         compiled from address_form.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'counter', 'address_form.html', 2, false),array('function', 'cycle', 'address_form.html', 6, false),array('modifier', 'escape', 'address_form.html', 9, false),)), $this); ?>
<?php if ($this->_tpl_vars['intable'] !== 0): ?>
<?php echo smarty_function_counter(array('name' => '__af_cnt','assign' => '__af_cnt','print' => false), $this);?>

<table cellspacing="0" class="cellpadding addressform">
<?php endif; ?>
<?php if ($this->_tpl_vars['asknames'] !== 0): ?>
<tr class="row_<?php echo smarty_function_cycle(array('name' => "__afcyycle".($this->_tpl_vars['__af_cnt']),'values' => 'odd,even'), $this);?>
">
	<td><span class="asterisk">*</span><?php echo 'Имя'; ?>
</td>
	<td>
		<input name='<?php echo $this->_tpl_vars['name_space']; ?>
[first_name]' value='<?php echo ((is_array($_tmp=$this->_tpl_vars['address']['first_name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
' type="text" rel="chk_first_name" class="autofill address_elem inputtext" >
	</td>
</tr>
<tr class="row_<?php echo smarty_function_cycle(array('name' => "__afcyycle".($this->_tpl_vars['__af_cnt']),'values' => 'odd,even'), $this);?>
">
	<td><span class="asterisk">*</span><?php echo 'Фамилия'; ?>
</td>
	<td>
		<input name='<?php echo $this->_tpl_vars['name_space']; ?>
[last_name]' value='<?php echo ((is_array($_tmp=$this->_tpl_vars['address']['last_name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
' type="text" rel="chk_last_name" class="autofill address_elem inputtext" >
	</td>
</tr>
<?php endif; ?>
<?php if (@CONF_ADDRESSFORM_ADDRESS != 2): ?>
<tr class="row_<?php echo smarty_function_cycle(array('name' => "__afcyycle".($this->_tpl_vars['__af_cnt']),'values' => 'odd,even'), $this);?>
">
	<td><?php if (@CONF_ADDRESSFORM_ADDRESS == 0): ?><span class="asterisk">*</span><?php endif; 
 echo 'Адрес'; ?>
</td>
	<td>
		<textarea name="<?php echo $this->_tpl_vars['name_space']; ?>
[address]" class="address_elem" rows="4"><?php echo ((is_array($_tmp=$this->_tpl_vars['address']['address'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</textarea>
	</td>
</tr>
<?php endif; ?>
<?php if (@CONF_ADDRESSFORM_CITY != 2): ?>
<tr class="row_<?php echo smarty_function_cycle(array('name' => "__afcyycle".($this->_tpl_vars['__af_cnt']),'values' => 'odd,even'), $this);?>
">
	<td><?php if (@CONF_ADDRESSFORM_CITY == 0): ?><span class="asterisk">*</span><?php endif; 
 echo 'Город'; ?>
</td>
	<td>
		<input name="<?php echo $this->_tpl_vars['name_space']; ?>
[city]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['address']['city'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" class="address_elem inputtext" type="text" >
	</td>
</tr>
<?php endif; ?>
<?php if (@CONF_ADDRESSFORM_STATE != 2): ?>
<tr class="row_<?php echo smarty_function_cycle(array('name' => "__afcyycle".($this->_tpl_vars['__af_cnt']),'values' => 'odd,even'), $this);?>
">
	<td><?php if (@CONF_ADDRESSFORM_STATE == 0): ?><span class="asterisk">*</span><?php endif; 
 echo 'Область'; ?>
</td>
	<td>
	<?php if (! $this->_tpl_vars['zones'][$this->_tpl_vars['name_space']]): ?>
		<input name="<?php echo $this->_tpl_vars['name_space']; ?>
[state]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['address']['state'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" type="text" class="address_elem inputtext" >
	<?php else: ?>
		<select name="<?php echo $this->_tpl_vars['name_space']; ?>
[zoneID]" class="address_elem">
		<?php $_from = $this->_tpl_vars['zones'][$this->_tpl_vars['name_space']]; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['_zone']):
?>
			<option value="<?php echo $this->_tpl_vars['_zone']['zoneID']; ?>
"<?php if ($this->_tpl_vars['_zone']['zoneID'] == $this->_tpl_vars['address']['zoneID']): ?> selected<?php endif; ?>><?php echo ((is_array($_tmp=$this->_tpl_vars['_zone']['zone_name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</option>
		<?php endforeach; endif; unset($_from); ?>
		</select>
	<?php endif; ?>
	</td>
</tr>
<?php endif; ?>
<?php if (@CONF_ADDRESSFORM_ZIP != 2): ?>
<tr class="row_<?php echo smarty_function_cycle(array('name' => "__afcyycle".($this->_tpl_vars['__af_cnt']),'values' => 'odd,even'), $this);?>
">
	<td><?php if (@CONF_ADDRESSFORM_ZIP == 0): ?><span class="asterisk">*</span><?php endif; 
 echo 'Почтовый индекс'; ?>
</td>
	<td>
		<input name="<?php echo $this->_tpl_vars['name_space']; ?>
[zip]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['address']['zip'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" class="address_elem inputtext" >
	</td>
</tr>
<?php endif; ?>
<?php if ($this->_tpl_vars['countries']): ?>
<tr class="row_<?php echo smarty_function_cycle(array('name' => "__afcyycle".($this->_tpl_vars['__af_cnt']),'values' => 'odd,even'), $this);?>
">
	<td><span class="asterisk">*</span><?php echo 'Страна'; ?>
</td>
	<td>
		<select name="<?php echo $this->_tpl_vars['name_space']; ?>
[countryID]" class="country_box address_elem" <?php if ($this->_tpl_vars['form_name'] && $this->_tpl_vars['ukey']): ?>onChange="changeStates(this)"<?php endif; ?>>
		<?php $_from = $this->_tpl_vars['countries']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['_country']):
?>
			<option value="<?php echo $this->_tpl_vars['_country']['countryID']; ?>
"
			<?php if ($this->_tpl_vars['address']['countryID']): ?>
				<?php if ($this->_tpl_vars['_country']['countryID'] == $this->_tpl_vars['address']['countryID']): ?>selected<?php endif; ?>
			<?php else: ?>
				<?php if ($this->_tpl_vars['_country']['countryID'] == @CONF_DEFAULT_COUNTRY): ?>selected<?php endif; ?>
			<?php endif; ?>
			><?php echo ((is_array($_tmp=$this->_tpl_vars['_country']['country_name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</option>
		<?php endforeach; endif; unset($_from); ?>
		</select>
	</td>
</tr>
<?php endif; ?> 
<?php if ($this->_tpl_vars['intable'] !== 0): ?>
</table>
<?php endif; ?>
<?php if ($this->_tpl_vars['form_name'] && $this->_tpl_vars['ukey']): ?>
<script type="text/javascript" src="<?php echo @URL_JS; ?>
/JsHttpRequest.js"></script>
<?php echo '
<script type="text/javascript">
function changeStates(country_el)
{
    var country_id  = country_el.value;
    var req = new JsHttpRequest();
    
    req.onreadystatechange = function()
    {
    	
        if (req.readyState != 4) return;
        if(req.responseText) alert(req.responseText);
        
        var states = req.responseJS.states;
        var states_el;
        try{
			states_el = document.forms[\''; 
 echo $this->_tpl_vars['form_name']; 
 echo '\'].elements[\''; 
 echo $this->_tpl_vars['name_space']; 
 echo '[zoneID]\'];
			if(!states_el)throw "err";
		}catch(e){// IE fix
			var form_el = document.forms[\''; 
 echo $this->_tpl_vars['form_name']; 
 echo '\'];
    	    for (var i=0;i<form_el.length;i++){
				if(form_el.elements[i].name == \''; 
 echo $this->_tpl_vars['name_space']; 
 echo '[zoneID]\'){
			  		states_el = form_el.elements[i];
					break;
				 }
			}	
		}

        if(states.length > 0){
            if(!states_el)
            {
	            var pn;
            try{
		            states_el = document.forms[\''; 
 echo $this->_tpl_vars['form_name']; 
 echo '\'].elements[\''; 
 echo $this->_tpl_vars['name_space']; 
 echo '[state]\'];
        		    pn = states_el.parentNode;
	            	pn.removeChild(states_el);
                
				}
				catch(e){// IE fix
					var form_el = document.forms[\''; 
 echo $this->_tpl_vars['form_name']; 
 echo '\'];
    	            for (var i=0;i<form_el.length;i++)
					{
						if(form_el.elements[i].name == \''; 
 echo $this->_tpl_vars['name_space']; 
 echo '[state]\'){
					  		states_el = form_el.elements[i];
					  		pn = states_el.parentNode;
			            	pn.removeChild(states_el);
							break;
						 }
					}	
				}
                
                var dd = document.createElement(\'SELECT\');
                dd.name =\''; 
 echo $this->_tpl_vars['name_space']; 
 echo '[zoneID]\';
                dd.className = \'address_elem\';
                
                pn.appendChild(dd);
                states_el = dd;//document.forms[\''; 
 echo $this->_tpl_vars['form_name']; 
 echo '\'].elements[\''; 
 echo $this->_tpl_vars['name_space']; 
 echo '[zoneID]\'];
            }
            else
            {
                while(states_el.options.length > 0)
                {
                    states_el.remove(0);
                };
            };
            
            for(i=0; i<states.length; i++)
	        {
	            var opt = new Option();
	            opt.value = states[i].zoneID;
	            opt.text = states[i].zone_name;
	            try
	            {
	                states_el.add(opt,null); // standards compliant
	            }
	            catch(ex)
	            {
	                states_el.add(opt); // IE only
	            };        
	        };
        }
        else
        {
            if(states_el)
            {
                var pn = states_el.parentNode;
                pn.removeChild(states_el);
                
                var inp = document.createElement(\'INPUT\');
                inp.type = \'text\';
                inp.className = \'address_elem inputtext\';
                inp.name = \''; 
 echo $this->_tpl_vars['name_space']; 
 echo '[state]\';
                
                pn.appendChild(inp);
                //states_el = document.forms[\''; 
 echo $this->_tpl_vars['form_name']; 
 echo '\'].elements[\''; 
 echo $this->_tpl_vars['name_space']; 
 echo '[state]\'];
            }
        };
        
    };

    try
    {
        req.open(null, set_query(\'?ukey='; 
 echo $this->_tpl_vars['ukey']; 
 echo '&caller=1&initscript=ajaxservice\'), true);
        req.send({\'action\': \'ajax_get_states\', \'country_id\': country_id});
    }
    catch ( e )
    {
      catchResult(e);
    }
    finally { ;}
};
</script>
'; ?>

<?php endif; ?>