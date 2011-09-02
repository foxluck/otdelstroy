/**
  * @name  editableGrid
  * @type  jQuery
  */
(function($) {
	$.fn.editableGrid = function(options){
		
		// config
		var defaults = {	
			containerId: 'test',
			show_delete_column: true,
			callback: function(){},
			saveCallback: function(){},
			"visible_fields":{},
			"hidden_fields":{},
			"all_fields":[],
			"contacts":{}
		}
		
		var options = $.extend(defaults, options);
		
		//create additional options
		for (var field_key in options.visible_fields){
			options.all_fields[field_key] = options.visible_fields[field_key];
		}
		for (var field_key in options.hidden_fields){
			options.all_fields[field_key]= options.hidden_fields[field_key];
		}
		
		//private functions
		
		function appendDeleteField(){
			if (options.show_delete_column) {
				var delete_field = {
						name:'',
						dbname:'DELETE',
						type: 'DELETE',
						width: '2%'
					};
				options.visible_fields["delete"] = delete_field;
			}
		}

		appendDeleteField();
		
		function getLength(obj) {
		    var size = 0;
		    for (key in obj) {
		        size++;
		    }
		    return size;
		};
		
		function checkEmail(el){
			val = $(el).val();
			var re = /^(?:[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])/i
			if (val=='' || re.test(val)) {
				return 0;
			} else {
				$(el).focus();
				return options.invalid_email_msg;
			}
		}
		
		function checkDate(el){
			val = $(el).val();
			var re = new RegExp();
			if (val=='' || re.test(val)) {
				return 0;
			} else {
				$(el).focus();
				return options.invalid_date_msg;
			}
		}
		
		function checkUrl(el){
			val = $(el).val();
			var re = new RegExp();
			re.compile("^((http|https)+://)?[A-Za-z0-9-_]+\\.[A-Za-z0-9-_%&\?\/.=]+$"); 
			if (val=='' || re.test(val)) {
				return 0;
			} else {
				$(el).focus();
				return options.invalid_url_msg;
			}
		}
		
		function checkRequired(el, field){
			val = $(el).val();
			if (val=='') {
				$(el).focus();
				return options.required_field_msg;
			} else {
				return 0;
			}
		}
		
		function addErrorMsg(el, msg){
			$(el).addClass('error');
			$('.tooltip', $(el).parent()).remove();
			$(el).parent().append('<span class="tooltip">'+msg+'</span>');
		}
		
		function removeErrorMsg(el){
			$(el).removeClass('error');
			$('.tooltip', $(el).parent()).remove();
		}
		
		function generateRowContent(values, length){
			var rowContent = $('<tr></tr>');
			var fields = options.visible_fields;
			var field_counter = 0;
			for (var field_key in fields){
				var field = fields[field_key];
				var ceilContent = $('<td class="'+field.type+'"></td>');
				var value = "";
				var id = 0;
				var disabled = false;
				if (values != null) {
					id = values[0];
					value = values[field_counter+1];
					if (value == undefined) disabled = true;
					if (value == null) value = "";
				} else {
					id = "new-"+(length+1);
				}
				
				switch(field.type) {
					case "TEXT": 
					case "URL": 
					case "EMAIL": 
					case "MOBILE":
					case "NUMERIC": 
					case "VARCHAR": 
						var input = $('<input value="'+value+'" size="'+field.options+'" type="text"></input>');
						input.attr('name','CONTACTS['+id+']['+field.id+']');
						input.attr('id','CONTACTS-'+id+'-'+field.id);
						switch(field.type) {
							case "EMAIL": 
								$(input).blur(function(){
									check = checkEmail(this);
									if (check!=0){
										addErrorMsg(this,check);
									} else {
										removeErrorMsg(this);
									}
								});
								$(input).keyup(function(){
									check = checkEmail(this)
									if (check==0){
										removeErrorMsg(this);
									}
								});
								
							break;
							case "URL": 
								$(input).blur(function(){
									check = checkUrl(this)
									if (check!=0){
										addErrorMsg(this,check);
									} else {
										removeErrorMsg(this);
									}
								});
								
								$(input).keyup(function(){
									check = checkUrl(this)
									if (check==0){
										removeErrorMsg(this);
									}
								});
							break;
						}

						/* check for required fields
						$(input).blur(function(){
							check = checkRequired(this, field);
							if (check!=0){
								addErrorMsg(this,check);
							}
						})
						*/
						ceilContent.append(input);
						break;
					case "DATE":
						var input = $('<input value="'+value+'" class="datepicker" type="text"></input>');
						input.attr('name','CONTACTS['+id+']['+field.id+']');
						input.attr('id','CONTACTS-'+id+'-'+field.id);
						input.addClass('DateField');
						ceilContent.append(input);

						$(input).keydown(function(){
							if ($(this).val()=='' && $(this).hasClass('error')){
								removeErrorMsg(this);
							}
						})
						
						break;
					case "CHECKBOX":
						var input = $('<input value="1" '+(value==0 ? '' : 'checked')+' type="checkbox"></input>');
						input.attr('name','CONTACTS['+id+']['+field.id+']');
						input.attr('id','CONTACTS-'+id+'-'+field.id);
						input.change(function () {
							var n = $(this).attr('name');
							var id = $(this).attr('id');
							var v = $(this).is(":checked") ? 1 : 0;
							if (!$('#' + id + '-h').length) {
								$(this).parent().append('<input type="hidden" name="' + n + '" id="' + id + '-h" value="' + v + '" />');
							} 
							$('#' + id + '-h').val(v);							
						});
						ceilContent.append(input);
						break;
					case "MENU":
						var select = $('<select></select>');
						select.attr('name','CONTACTS['+id+']['+field.id+']');
						select.attr('id','CONTACTS-'+id+'-'+field.id);
						select.append('<option value=""></option>')
						for (var option_cnt in field.options){
							var option = $('<option></option>');
							option.attr('value',field.options[option_cnt]);
							option.html(field.options[option_cnt]);
							if (value == field.options[option_cnt]) {
								option.attr('selected','selected');
								option.addClass('selected');
							}
							select.append(option)
							select.change(function(){
								$('option', this).removeClass('selected');
								$('option[value='+$(this).val()+']', this).addClass('selected');
							});
						}
						ceilContent.append(select);
						break;
					case "DELETE":
						if (values != null) {
							var deleteCheckbox = $('<input name="DELETE[]" id="DELETE['+id+']" class="delete_chbox" value="'+id+'" type="checkbox"></input>');
							deleteCheckbox.change(function(){
								var tr = $(this).parent().parent().parent();
								if (!tr.hasClass('disabled')){
									tr.addClass('disabled');
									$(':input',tr).attr('disabled','disabled');
									$(this).removeAttr('disabled');
								} else {
									tr.removeClass('disabled');
									$(':input',tr).removeAttr('disabled');
								}
							})
							ceilContent.append(deleteCheckbox);
							var div = $('<div style="width:80px;"></div>');
							div.append(deleteCheckbox);
							div.append('<label for="DELETE['+id+']"><small>'+options.delete_msg+'</small></label>');
							ceilContent.append(div);
						} else{
							var deleteCheckbox = $('<input type="checkbox"></input>');
							deleteCheckbox.click(function(){
								$(this).parent().parent().parent().remove()
								options.callback();
							})
							ceilContent.append(deleteCheckbox);
						}
						break;
				}
				
				if (disabled) {
					$(ceilContent).children().attr('disabled', 'disabled');
				}
				
				$(ceilContent).click(
					function() {
						$('input', this).focus();
					}
				);
				
				$(":input",ceilContent).focus(function(){
					if (!$(this).hasClass('delete_chbox')){
						$('.focused').removeClass('focused');
						$(this).addClass('focused');
					}
				});
				
				rowContent.append(ceilContent);
				field_counter++;
			}
			return rowContent;
		}
		
		function renderFields(fields){
			$('.replace_row').remove();
			$('#multi_edit_button').show();
			var content = $([]);
			var thead = $('thead', $('#'+options.containerId));
			if ($(thead).length>0) $(thead).remove();
			var tbody = $('tbody', $('#'+options.containerId));
			if ($(tbody).length>0) $(tbody).remove();
			var thead_wrap = $('<thead></thead>');
			var thead = $('<tr></tr>');
			for (var field_key in fields){
				var field_name = fields[field_key].name.split(' — ');
				if (field_name[1]) {
					field_name = field_name[1];
				} else {
					field_name = field_name[0];
				}
				var field = $('<th>'+field_name+'</th>');
				if (fields[field_key].type == "DELETE") {
					field.addClass('{sorter: false}');
				}
				if (fields[field_key].type == "DATE") {
						field.addClass("{sorter: false}");
					}
				if (fields[field_key].width != null) {
					field.css('width',fields[field_key].width);
				} else {
					field.css('width',Math.round(100 / (getLength(fields)-1))+'%');
				}
				thead.append(field);
			}
			thead_wrap.append(thead);
			
			var tbody = $('<tbody></tbody>');
			var contacts = options.contacts;
			for (var contact_id in contacts){
				var rowArray = [];
				rowArray.push(contact_id);
				for (var field_key in fields){
					rowArray.push(contacts[ contact_id ][ fields[field_key].dbname ])
				}
				var rowContent = generateRowContent(rowArray, 0);
				if (contacts[ contact_id ]['right_delete'] != 1) {
					$('.DELETE', rowContent).html('<div style="width:80px;"></div>');
				}
				if (contacts[ contact_id ]['right'] != 1) {
					$(':input', rowContent).attr('disabled','disabled');
				}
				tbody.append(rowContent);
			}
			content['thead'] = thead_wrap;
			content['tbody'] = tbody;
			return content;
		}
		
		/* public functions */
		 
		this.addRow = /* public */ function(){
			var tbody = $('tbody', $('#'+options.containerId));
			var rowContent = generateRowContent(null, $('tbody tr', $(this)).length)
			tbody.prepend(rowContent);
			options.callback();
		}
		
		this.multiEdit = /* public */ function(){
			$('#multi_edit_button').hide();
			$('.replace_row').remove();
			if ($('.focused').length > 0) {
				var row = $('.focused').parent().parent().clone(true);
			} else {
				var row = $('tbody tr:first', '#'+options.containerId).clone(true);
			}
			$('td.DELETE div', row).html('');
			$(':input',row).each(function(i){
				$(this).removeAttr('disabled');
				if ($(this).hasClass('DateField')) {
					$('img', $(this).parent()).remove();
					var input = $('<input id="'+('multiedit-'+i)+'" value="" type="text"></input>');
					input.addClass('DateField');
					$(this).replaceWith(input);
				}
				$(this).attr('name','');
				$(this).attr('id','multiedit-'+i);
				if(! $(this).hasClass('focused')) $(this).val('');
				$('option[class=selected]', this).attr('selected','selected');
			})
			row.removeClass('even');
			row.removeClass('odd');
			row.addClass('replace_row');
			
			var apply_btn = $('<input type="button" class="button" value="'+options.apply_msg+'"></input>');
			var close_btn = $('<input type="button" class="button cancel" value="'+options.close_msg+'"></input>');
			
			apply_btn.click(function(){
				$('table tbody tr', '#'+options.containerId).each(function(){
					$(':input',this).each(function(i, obj) {
						if ($("#multiedit-"+i).attr('type') == 'checkbox' || ($("#multiedit-"+i).val()!='' && !$("#multiedit-"+i).hasClass('error'))){
							if ($(this).attr('type') == 'checkbox') {
								if ($("#multiedit-"+i).is(":checked")) {
									$(this).attr('checked', 'checked');
								} else {
									$(this).removeAttr('checked');
								}
							} else if (!$(this).attr('disabled')) {
								$(this).val($("#multiedit-"+i).val());
							}
						}
					});
				})
			});
			close_btn.click(function(){
				/*$('.replace_row').remove();
				var content = renderFields(options.visible_fields);
				$('table.grid', '#'+options.containerId).append(content['tbody']);
				$('table.grid', '#'+options.containerId).append(content['thead']);
				$('#multi_edit_button').show();
				options.callback();*/
				$('.replace_row').remove();
				$('#multi_edit_button').show();
			});
			
			var buttons_row = $('<tfoot></tfoot>');
			var buttons_row_tr = $('<tr class="multiedit_tfoot"></tr>');
			var buttons_row_td = $('<td colspan="100" align="left"></td>');
			var buttons_row_td_div = $('<div class="multiedit_tfoot_div"></div>');
			buttons_row_td_div.append(apply_btn);
			buttons_row_td_div.append(close_btn);
			buttons_row_td.append(buttons_row_td_div);
			buttons_row_tr.append(buttons_row_td);
			buttons_row.append(buttons_row_tr);
			var multi_edit = $('<table cellpadding="0" cellspacing="0" border="0" width="100%" class="datatable replace_row"></table>');
			var multi_edit_thead = $('thead', '#'+options.containerId).clone();
			multi_edit_thead.css({'visibility': 'hidden', 'overflow':'hidden', 'height':'0px','line-height':'0px'});
			multi_edit.append(multi_edit_thead);
			multi_edit.append('<tr class="multiedit_thead"><td colspan="100">'+options.replace_info_msg+'</td></tr>');
			multi_edit.append(row);
			multi_edit.append(buttons_row);
			$('#'+options.containerId).prepend(multi_edit);
			options.callback();
			$('.focused', row).focus();
			$('td', row).each(function(){
				$(this).attr('name','');
			});
		}
		
		this.changeFields = /* public */function(){
			var select1 = $('<select id="select1" style="width: 300px;" size="8" multiple></select>');
			var select2 = $('<select id="select2" style="width: 300px;" size="8" multiple></select>');
			var visible_fields = options.visible_fields;
			if (options.show_delete_column) delete visible_fields['delete'];

			for (var field_key in visible_fields){
				var field = visible_fields[field_key];
				var option = $('<option></option>');
				option.attr('value',field_key);
				option.html(field.name);
				select1.append(option)
			}
			var hidden_fields = options.hidden_fields;
			for (var field_key in hidden_fields){
				var field = hidden_fields[field_key];
				var option = $('<option></option>');
				option.attr('value',field_key);
				option.html(field.name);
				select2.append(option)
			}

			var up = $('<div id="btn-up" class="updown wbs-move-btn disable"><a href="javascript:void(0)" id="link_up">↑</a></div>');
			var down = $('<div id="btn-down" class="updown wbs-move-btn disable"><a href="javascript:void(0)" id="link_down">↓</a></div>');
			
			up.click(function() {  
					$("#select1 option:selected").insertBefore($("#select1 option:selected:first").prev());
			});  			
			down.click(function() {  
					$("#select1 option:selected").insertAfter($("#select1 option:selected:last").next());
			} );
			
			var add = $('<div id="btn-left" class="wbs-move-btn disable"><a href="javascript:void(0)" id="link_left">←</a></div>');
			var remove = $('<div id="btn-right" class="wbs-move-btn disable"><a href="javascript:void(0)" id="link_right">→</a></div>');
			
			add.click(function() {  
					$('#select1').change();
					$('#btn-left').addClass('disable');
					$('#btn-right').removeClass('disable');
					return !$('#select2 option:selected').remove().appendTo('#select1');  
			});  			
			remove.click(function() {  
				//if (!$('#btn-left').hasClass('disable')) {
					$('#select2').change();
					$('#btn-right').addClass('disable');
					$('#btn-left').removeClass('disable');
					$('#select1 option:selected').remove().appendTo('#select2'); 
					if ($('#select1 option').length == 0) $('#changeFields').attr('disabled','disabled');
				//} 
			} );
			
			var container_dlg = $('<div class="wbs-dlg-content" class="field visible-fields" id="changefields"></div>');		
			var container = $('<div id="dlg-move-content" class="hidden wbs-dlg-content-inner" style="display: block;"></div>');		
			var table = $('<table></table>');
			var tr = $('<tr></tr>');
			var select1_div = $('<div class="select-container"></div>');
			select1_div.append(select1);
			var select2_div = $('<div class="select-container"></div>');
			select2_div.append(select2);
			
			var column1 = $('<td></td>');
			column1.append('<h4>'+options.visible_columns_msg+'</h4>');
			column1.append(select1_div);
			
			var column2 = $('<td align="middle" align="center" width="30" style="vertical-align: middle;"></td>');
			var column2_div = $('<div class="control-btns" style="padding-top:45px" id="control"></div>');
			column2_div.append(up);
			column2_div.append(down);
			column2_div.append(remove);
			column2_div.append(add);
			column2.append(column2_div);
			
			var column3 = $('<td></td>');
			column3.append('<h4>'+options.hidden_columns_msg+'</h4>');
			column3.append(select2_div);
			tr.append(column1);
			tr.append(column2);
			tr.append(column3);
			table.append(tr);
			container.append(table);

			select1.change(function(){
				$('#select1').addClass('select_focused');
				$('#select2').removeClass('select_focused');
				$('#changeFields').removeAttr('disabled');
				$('#btn-right').removeClass('disable');
				$('#select1').focus();
			});

			select1.focus(function(){
				$('.updown','#control').removeClass('disable');
			});
			
			select2.focus(function(){
				$('.updown','#control').addClass('disable');
			});
			
			select2.change(function(){
				$('#select2').addClass('select_focused');
				$('#select1').removeClass('select_focused');
				$('#changeFields').removeAttr('disabled');
				$('#btn-left').removeClass('disable');
				$('#select2').focus();
			});
			
			select2_div.dblclick(function() {  
				$('#select1').change();
				$('#btn-left').addClass('disable');
				$('#btn-right').removeClass('disable');
				 return !$('#select2 option:selected').remove().appendTo('#select1');  
			}); 
			select1_div.dblclick(function() {  
				$('#select2').change();
				$('#btn-right').addClass('disable');
				$('#btn-left').removeClass('disable');
				 return !$('#select1 option:selected').remove().appendTo('#select2');  
			}); 

			$('#popup').wbsPopup({
				width: 690,
				height: 280
			});
			$('#popup').prepend(
				'<div class="wbs-dlg-header">'+
					'<div class="label">'+options.customize_columns_msg+'</div>'+
				'</div>'
			);				
			container_dlg.append(container);
			$('#popup').append(container_dlg);
			$('#popup').append(
				'<div class="wbs-dlg-footer">'+
				'<div class="setting-options">'+
					'<input id="changeFields" class="wbs-dlg-button" disabled="disabled" type="button" value="'+options.save_fields_msg+'"/>'+
					'<input class="wbs-dlg-button" type="button" value="'+options.cancel_msg+'" onclick="$(\'#popup\').wbsPopupClose()" />'+
				'</div>'
			);
			$('#changeFields').click(function(){
				options.visible_fields = [];
				$('#select1 option').each(function(sort){
					var id = $(this).val();
					for (var field_key in options.all_fields){
						if (field_key==id) {
							options.visible_fields[sort] = options.all_fields[id];
							options.visible_fields[sort]['real_id'] = id;
							if (options.hidden_fields[id]) {
								delete options.hidden_fields[id];
							}
						}
					}
				})
				$('#select2 option').each(function(){
					var id = $(this).val();
					for (var field_key in options.all_fields){
						if (field_key==id) {
							options.hidden_fields[id] = options.all_fields[id];
						}
					}
				})
				appendDeleteField();
				var content = renderFields(options.visible_fields)
				$('table.grid', '#'+options.containerId).append(content['thead']);
				$('table.grid', '#'+options.containerId).append(content['tbody']);
				var tmp_arr = options.visible_fields;
				if (options.show_delete_column) delete tmp_arr['delete'];
				options.visible_fields = new Object();
				var real_id = 0;
				var cnt = 0;
				for (var field_key in tmp_arr){
					real_id = tmp_arr[field_key].real_id;
					if (real_id) {
						cnt++;
						var item = options.all_fields[real_id];
						item.sort = cnt;
						options.visible_fields[real_id] = item;
					}
				}
				appendDeleteField();
				options.callback();
				$('#popup').wbsPopupClose();
			})
		}
		
		this.init =  /* public */ function() {  
			var obj = $(this);
			var wrapper = $('<div class="datatable"></div>');
			var table = $('<table cellpadding="0" cellspacing="0" border="0" class="grid"></table>');
			table.css('width','100%');
			var fields = options.visible_fields;
			var content = renderFields(fields);
			table.append(content['thead']);
			table.append(content['tbody']);

			var tfoot = $('<tfoot></tfoot>');
			var tfoot_tr = $('<tr></tr>');
			var tfoot_td = $('<td colspan="100" ></td>');
			var msg = $('<div class="msg" style="display:none;"></div>');
			tfoot_td.append(msg);
			var tfoot_div = $('<div></div>');
			
			var save_button = $('<input type="button" class="button" value="'+options.save_msg+'"></input>');
			save_button.click(function(){
				var request = $('#'+options.containerId+'_form').serializeArray();
				$.post(options.save_action_url, request , function (response) {
					if (response.status == 'OK') {
						msg.html(options.redirect_msg);
						if (!msg.hasClass('msg-yellow')) {
							msg.addClass('msg-yellow');
							msg.removeClass('msg-error');
						}
						msg.show();
						
						var message = "Saved: " + response.data.saved;
						if (response.data.deleted) {
							message += ", deleted: " + response.data.deleted;
						}
						options.saveCallback(message)
					} else {
						msg.html(options.error_msg);
						if (!msg.hasClass('msg-error')) {
							msg.addClass('msg-error');
							msg.removeClass('msg-yellow');
						}
						var errors = response.error;
						for (var contact_id in errors){
							for (var error_id in errors[contact_id]){
								var error = errors[contact_id][error_id];
								if (error.type == 'delete') {
									var checkboxes = $(':input[name=delete]');
									checkboxes.each(function(){
										if ($(this).val()==error.id) {
											$(this).parent().css('background-color', '#F7CBCA');
											$(this).parent().append('<div class="tooltip">'+error.text+'</div>');
										}
									});
								} else {
									if (error.id != undefined) {
										var input_name = 'CONTACTS-'+contact_id+'-'+error['id'];
										var el = $('#'+input_name);
										$(el).addClass('error');
										$('.tooltip', $(el).parent()).remove();
										$(el).parent().append('<div class="tooltip">'+error.text+'</div>');
									} else {
										var ids = error.ids.toString().split (',');
										for (var id in ids){
											var input_name = 'CONTACTS-'+contact_id+'-'+ids[id];
											var el = $('#'+input_name);
											$(el).addClass('linked');
											$(el).addClass('error');
											$(el).keydown(function(){
												if ($(this).val()=='' && $(this).hasClass('error')){
													$('.linked').each(function(){
														$('.tooltip', $(this).parent()).remove();
														$(this).removeClass('linked');
														$(this).removeClass('error');
													});
												}
											})
											$('.tooltip', $(el).parent()).remove();
											$(el).parent().append('<div class="tooltip">'+error.text+'</div>');
										}
									}
								}
							}
						}
						
						msg.show();
					}
				}, "json");
			});
			
			var cancel_button = $('<input type="reset" class="button" value="'+options.cancel_msg+'"></input>');
			cancel_button.click(function(){
				/*
				var content = renderFields(options.visible_fields);
				$('.msg', '#'+options.containerId).hide();
				$('table.grid', '#'+options.containerId).append(content['tbody']);
				$('table.grid', '#'+options.containerId).append(content['thead']);
				options.callback();
				*/
				options.saveCallback(false);
			});
			
			tfoot_div.append(save_button);
			tfoot_div.append(cancel_button);
			tfoot_td.append(tfoot_div);
			tfoot_tr.append(tfoot_td);
			tfoot.append(tfoot_tr);
			table.append(tfoot);
			
			wrapper.append(table);
			$('#'+options.containerId).append(wrapper);
			options.callback();
		}
		
		return {
			init: this.init,
			callback: this.callback,
			saveCallback: this.saveCallback,
			multiEdit: this.multiEdit,
			addRow: this.addRow,
			changeFields: this.changeFields
		}
	};
})(jQuery);