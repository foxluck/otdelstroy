<?php /* Smarty version 2.6.26, created on 2011-09-02 13:22:28
         compiled from languages.htm */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'cycle', 'languages.htm', 31, false),)), $this); ?>
<!-- languages.html -->
<?php echo '<form action="'; 
 echo $this->_tpl_vars['formLink']; 
 echo '" method="post" enctype="multipart/form-data" name="form"><h2 class="page-title">'; 
 echo $this->_tpl_vars['waStrings']['lll_page_title']; 
 echo '</h2><br>'; 
 if (! $this->_tpl_vars['fatalError']): 
 echo '<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td>&nbsp;</td><td align="right"><input name="addlanguage" type="submit" value="'; 
 echo $this->_tpl_vars['waStrings']['lll_add']; 
 echo '"></td></tr><tr><td colspan="2">&nbsp; </td></tr></table><table width="100%" class="list"><tr style="background-color: #eee;"><td width=50><strong>'; 
 echo $this->_tpl_vars['waStrings']['lll_id']; 
 echo '</strong></td><td><strong>'; 
 echo $this->_tpl_vars['waStrings']['lll_name']; 
 echo '</strong></td><td><strong>'; 
 echo $this->_tpl_vars['waStrings']['lll_encoding']; 
 echo '</strong></td><td align="center"><strong>'; 
 echo $this->_tpl_vars['waStrings']['lll_properties']; 
 echo '</strong></td><td align="center" colspan="3"><strong>'; 
 echo $this->_tpl_vars['waStrings']['lll_locstr']; 
 echo '</strong></td></tr>'; 
 $_from = $this->_tpl_vars['sys_languages']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['languageList'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['languageList']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['langData']):
        $this->_foreach['languageList']['iteration']++;

 echo '<tr class="list-item '; 
 echo smarty_function_cycle(array('values' => "background1,background2"), $this);
 echo '"><td align="left">'; 
 if ($this->_tpl_vars['langData']['ID'] == 'eng'): 
 echo ' <b> '; 
 endif; 
 echo ' &nbsp; '; 
 echo $this->_tpl_vars['langData']['ID']; 
 echo ' '; 
 if ($this->_tpl_vars['langData']['ID'] == 'eng'): 
 echo ' </b> '; 
 endif; 
 echo '</td><td align="left">'; 
 if ($this->_tpl_vars['langData']['ID'] == 'eng'): 
 echo ' <b> '; 
 endif; 
 echo ' &nbsp; '; 
 echo $this->_tpl_vars['langData']['NAME']; 
 echo ' '; 
 if ($this->_tpl_vars['langData']['ID'] == 'eng'): 
 echo ' </b> '; 
 endif; 
 echo '</td><td align="left">'; 
 if ($this->_tpl_vars['langData']['ID'] == 'eng'): 
 echo ' <b> '; 
 endif; 
 echo ' &nbsp; '; 
 echo $this->_tpl_vars['langData']['ENCODING']; 
 echo ' '; 
 if ($this->_tpl_vars['langData']['ID'] == 'eng'): 
 echo ' </b> '; 
 endif; 
 echo '</td><td align="center"><a href="'; 
 echo $this->_tpl_vars['langData']['ROW_URL']; 
 echo '">'; 
 echo $this->_tpl_vars['waStrings']['lll_modify']; 
 echo '</a></td><td align="center"><a href="'; 
 echo $this->_tpl_vars['langData']['LOC_URL']; 
 echo '">'; 
 if ($this->_tpl_vars['langData']['ID'] != 'eng'): 
 echo ''; 
 echo $this->_tpl_vars['waStrings']['lll_edit']; 
 echo ''; 
 else: 
 echo ''; 
 echo $this->_tpl_vars['waStrings']['lll_view']; 
 echo ''; 
 endif; 
 echo '</a>&nbsp;</td><td align="center">'; 
 if ($this->_tpl_vars['langData']['ID'] != 'eng'): 
 echo '<a href="'; 
 echo $this->_tpl_vars['langData']['IMPORT_URL']; 
 echo '">'; 
 echo $this->_tpl_vars['waStrings']['lll_import']; 
 echo '</a>'; 
 else: 
 echo '&nbsp;'; 
 endif; 
 echo '</td><td align="center"><a href="'; 
 echo $this->_tpl_vars['langData']['EXPORT_URL']; 
 echo '">'; 
 echo $this->_tpl_vars['waStrings']['lll_export']; 
 echo '</a> &nbsp;</td></tr>'; 
 endforeach; else: 
 echo '<tr align="center"><td colspan="3">&lt;'; 
 echo $this->_tpl_vars['waStrings']['lll_no_recods']; 
 echo '&gt;</td></tr>'; 
 endif; unset($_from); 
 echo '</table><br><b>'; 
 echo $this->_tpl_vars['waStrings']['lbl_form_note']; 
 echo ':</b> '; 
 echo $this->_tpl_vars['waStrings']['lll_note']; 
 echo ''; 
 endif; 
 echo '<input type=hidden name=lang_id value="'; 
 echo $this->_tpl_vars['lang_id']; 
 echo '"><input type=hidden name=app_id value="'; 
 echo $this->_tpl_vars['app_id']; 
 echo '"><input type=hidden name=type_id value="'; 
 echo $this->_tpl_vars['type_id']; 
 echo '"></form>'; ?>

<!-- /languages.html -->