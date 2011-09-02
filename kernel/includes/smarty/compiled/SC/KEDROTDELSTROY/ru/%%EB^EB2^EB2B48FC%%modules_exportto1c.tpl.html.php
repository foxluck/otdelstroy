<?php /* Smarty version 2.6.26, created on 2011-09-01 08:16:28
         compiled from backend/modules_exportto1c.tpl.html */ ?>
<h1>Интеграция с 1С</h1>
<?php if ($this->_tpl_vars['exportto1c_errormsg'] != NULL): ?>
	<div id="error-block" class="error_block">
	<?php echo $this->_tpl_vars['exportto1c_errormsg']; ?>

	</div>
<?php endif; 
 if ($this->_tpl_vars['not_extension']): ?>
	<div id="error-block" class="error_block">
	Для работы модуля необходимо установить PHP-расширение XMLReader (<a href="http://php.net/manual/en/book.xmlreader.php" target="_blank">http://php.net/manual/en/book.xmlreader.php</a>)
	</div>
<?php endif; ?>

<?php if ($this->_tpl_vars['exportto1c_successful'] == 1): ?>
	<div id="message-block" class="success_block">Товары успешно экспортированы в CommerceML-файл для 1С.<br />Ссылка на файл представлена ниже.</div>
<?php endif; 
 if ($this->_tpl_vars['importfrom1c_successful'] == 1): ?>
	<div id="message-block" class="success_block">Товары успешно импортированы из CommerceML-файла 1С</div>
<?php endif; ?>

<div>
	<p>Интеграция с «1С: Управлением торговлей» на уровне обмена данными через
	формат <a href="http://www.1c.ru/rus/products/1c/integration/cml.htm" target="_blank">CommerceML</a>. Документация по настройке интеграции: <a target="_blank" href="http://www.webasyst.ru/support/help/shop-script-1c-integration.html">http://www.webasyst.ru/support/help/shop-script-1c-integration.html</a></p>
	
</div>

<div class="chckrpl_module_block" style="width: 650px;">
	<table>
	<colgroup>
            <col width="70%" />
            <col width="10%" />
            <col width="20%" />
    </colgroup>
    <tbody>
	<tr>
		<td><h2><img src="./images_common/1c.png" alt="" style="padding-right: 8px; vertical-align: baseline;"> Обмен данными с 1С</h2></td>
		<td id="chckrpl-settings-<?php echo $this->_tpl_vars['_module']['module_id']; ?>
-state" style="font-weight: bold; color: <?php if (@CONF_1C_ON == 1): ?>green<?php else: ?>red<?php endif; ?>;"><?php if (@CONF_1C_ON == 1): 
 echo 'Включен'; 
 else: 
 echo 'Выкл.'; 
 endif; ?></td>
		<td align="right"><input class="chckrpl_module_switch" rel="<?php echo $this->_tpl_vars['_module']['module_id']; ?>
" type="button" value="<?php if (@CONF_1C_ON != 1): 
 echo 'Включить'; 
 else: 
 echo 'Выключить'; 
 endif; ?>" /></td>
	</tr>
	<tr class="chckrpl_module_settings" <?php if (@CONF_1C_ON != 1): ?>style="display: none;"<?php endif; ?> id="chckrpl-settings-<?php echo $this->_tpl_vars['_module']['module_id']; ?>
">
		<td colspan="3">
				
					
				<h3 style="margin-top: 15px;"><span style="background: #e5e5d5; padding: 2px;">Автоматический</span> обмен данными</span></h3>
				<div style="margin-left: 20px;">
					При автоматическом обмене данными Shop-Script передает в 1С только информацию о заказах и принимает из 1С только информацию о продуктах (добавляет новые продукты, обновляет цены и характеристики).<br /><br />
					<table>
						<tr>
							<td width="30%">Адрес скрипта синхронизации</td>
							<td width="70%"><input type="text"  onclick="this.select();" onfocus="this.select();" style="width:100%; font-weight: bold;" value="<?php echo $this->_tpl_vars['url_from_1c']; ?>
" /></td>
						</tr>
						<tr>
							<td>Пользователь</td>
							<td><input type="text" onclick="this.select();" onfocus="this.select();" style="width:50%;" value="<?php echo $this->_tpl_vars['user']; ?>
"/></td>
						</tr>
						<tr>
							<td>Пароль</td>
							<td><input id="password_1c" type="text" onclick="this.select();" onfocus="this.select();" style="width:50%;" value="<?php echo $this->_tpl_vars['password']; ?>
"/></td>
						</tr>		
					</table>
				</div>

			<br/>
			
				<h3>Обмен данными <span style="background: #e5e5d5; padding: 2px;">через файл</span></h3>
				<div style="margin-left: 20px;">
			
				<h4>Экспорт из магазина (Shop-Script → 1С)</h4>
				<div style="margin-left: 20px;">
						
						
						<form action="" method=post name="form_export">
							<input type="hidden" name="exportto1c" value="exportto1c" />
							<input type="hidden" name="save" value="" />
	
							<div><label><?php echo $this->_tpl_vars['export_products']; ?>
Продукты</label></div>
							<div><label><?php echo $this->_tpl_vars['export_orders']; ?>
Заказы</label>
								<select name="export_orders_mode">
									<option value="1" selected="selected">только измененные после даты последнего экспорта</option>
									<option value="2">все заказы</option>
								</select> 
							</div>
							<br />
							
							<input type="submit" value="Экспортировать в XML-файл"/>
							<input type=hidden name=dpt value=modules>
							<input type=hidden name=sub value=exportto1c>
							<input type=hidden name=_export value="" />
						</form>
						
						<?php if ($this->_tpl_vars['exportto1c_file']): ?>
							<div style="<?php if ($this->_tpl_vars['exportto1c_successful'] == 1): ?>background: #fff; border: 3px solid #ee3;<?php else: ?>background: #eed;<?php endif; ?> padding: 15px;">
							<strong>Последний экспорт</strong>: <?php echo $this->_tpl_vars['exportto1c_file']['size']; ?>
 КБ; обновлен <?php echo $this->_tpl_vars['exportto1c_file']['mtime']; ?>
<br>
							<ol style="list-style-type:none;padding-left: 0;">
							<li>
							Адрес файла: <input type="text" onclick="this.select();" onfocus="this.select();" style="width: 100%" value="<?php echo @BASE_WA_URL; 
 if (@CONF_ON_WEBASYST != 1): ?>published/<?php endif; ?>SC/html/scripts/get_file.php?getFileParam=R2V0MUM=" />
							</li>
							<li>
							<a target="_blank" href='<?php echo @BASE_WA_URL; 
 if (@CONF_ON_WEBASYST != 1): ?>published/<?php endif; ?>SC/html/scripts/get_file.php?getFileParam=R2V0MUM=;&download=1'><strong>Скачать файл</strong></a> 
							</li>
							</ol>
														</div>
						<?php endif; ?>
				</div>
			
			
				<h4>Импорт в магазин (1С → Shop-Script)</h4>
				<div style="margin-left: 20px;">
					<p><input type="checkbox" disabled="disabled" checked="checked" />Продукты</p>
					<form action="" method="post" name="form_import" enctype="multipart/form-data">
						<input type="hidden" name="importfrom1c" value="importfrom1c" />
						<p><input type="file" name="xml"/></p>
						<p><input type="submit"  value="Импортировать из XML-файла"/></p>
					</form>
				</div>
			
				</div>
				
		</td>
	</tr>
	</tbody>
	</table>
</div>

<script type="text/javascript" src="<?php echo @URL_JS; ?>
/niftycube.js"></script>
<script type="text/javascript" src="<?php echo @URL_JS; ?>
/JsHttpRequest.js"></script>
<script type="text/javascript" src="<?php echo @URL_JS; ?>
/module_1c.js"></script>
<script type="text/javascript">
translate.btn_enable = "<?php echo 'Включить'; ?>
";
translate.btn_disable = "<?php echo 'Выключить'; ?>
";
translate.state_enabled = "<?php echo 'Включен'; ?>
";
translate.state_disabled = "<?php echo 'Выкл.'; ?>
";
</script>