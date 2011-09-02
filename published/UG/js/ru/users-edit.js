$.fn.setHeight = function (h, min, max) {
	h = h < min ? min : h;
	h = h > max ? max : h;
	return $(this).height(h);
}

$.fn.resizeImage = function(w, h) {
	var iw = $(this).width();
	var ih = $(this).height();
	if (iw <= w && ih <= h) {
		return $(this);
	}
	if (!ih || !iw) {
		return $(this);
	}
	var k = w/iw < h/ih ? (w/iw) : (h/ih) ;
	return $(this).width(k * iw).height(k * ih);
}

$.fn.loadImage = function (src) {
	var i = $(this);
	if (i.attr('src') == src) {
		return i;
	}
	i.attr('src', WbsData.get('url.common', '/common/') + 'img/loading.gif');
	var img = $('<img src="' + src + '" />');
	img.load(function () {
		i.attr('src', $(this).attr('src'));
		
	});
	return i;
}

function AddInput(obj) {
	$('<input type = "text" /><a class="delete_email" href="javascript:void(0)" onClick="DeleteInput(this)">Удалить</a><br />').insertBefore(obj);
	$(obj).prevAll("input:first").select();
}

function DeleteInput(obj) {
	$(obj).prev().remove();
	$(obj).next().remove();
	$(obj).remove();
}

var WbsEditUser = newClass(null, {
	constructor: function(config) {
		this.config = config;
		if (this.config.editable == undefined) {
			this.config.editable = true;
		}
		this.main_fields = config.main_fields || new Array();
		this.showAll = config.showAll || false;
		this.requiredOnly = config.requiredOnly || false;
		this.editAll = config.editAll || false;
		this.noImage = config.noImage || false;
		this.contact = config.contact;
		document.contact = config.contact;
		this.groups = new Array();
		
		for (var i = 0; i < config.groups.length; i++) {
			this.groups[i] = {
					id: config.groups[i][0],
					fields: new Array(),
					edit: this.config.editable ? (config.edit || false) : false
			};
			for (var j = 0; j < config.groups[i][1].length; j++) {
				var fields = config.groups[i][1][j];
				this.groups[i].fields[j] = {
						id: fields[0], 
						name: fields[1], 
						type: fields[2], 
						value: fields[3], 
						required: fields[4]
				};
				if (fields[2] == 'MENU') {
					this.groups[i].fields[j].items = fields[5];
				} else if (fields[2] == 'IMAGE') {
					this.groups[i].fields[j].size = fields[5];
				} else if (fields[2] == 'TEXT' || fields[2] == 'VARCHAR'){
					this.groups[i].fields[j].max_length = fields[5] != undefined ? fields[5] : 0;
				} else {
					this.groups[i].fields[j].max_length = 0;
				}
				if (fields[6]) {
					this.groups[i].fields[j].comment = fields[6]; 
				}
			}
		}
		
		for (i = 0; i < this.groups.length; i++) {
			this.prepareGroup(this.groups[i]);
		}
		
		if (this.config.editable) {
			$("#display_name").click(function () {
				this.groups[0].edit = true;
				this.groups[0].showAll = true;
				this.renderGroup(this.groups[0]);				
				$("#edit_" + this.main_fields[0] + " .edit input").select();
			}.bind(this));
		}
		
		this.render();
	},

	prepareGroup: function(group) {
		var g = $("#" + group.id);
		for (i = 0; i < group.fields.length; i++) {
			var f = group.fields[i];
			var div = $("#" + f.id);
			if (!div.length) {
				div = $('<div id=' + f.id + ' class="field"></div>');
				if (f.id != this.config.photoField) {
					div.append('<div class="label">' + (f.name ? f.name + ':' : '&nbsp;') + '</div>');
				}
				var field = $('<div class="edit"></div>');
				if (this.isMain(f.id, true)) {
					
				} else if (this.isMain(f.id, false)){
					div.removeClass('field').addClass('l');
					if (f.type == 'EMAIL') {
						div.addClass('email');
						div.find("div.label").css('width', 'auto');
					}
					div.append(field).insertBefore("#editMain");
				} else {
					g.append(div.append(field));
				}
			} else {
				var field = div.children(".edit");
				if (div.children("div.label").length > 0) {
					div.children("div.label").html(f.name + ':');
				}
			}
			field = this.prepareField(field, f, group);
			var _this = this;
			if (this.config.editable && (f.type != 'EMAIL' || !this.config.disableEmail)) {
				var divEdit = $('<div id="edit_' + f.id + '" class="e field' + (this.isMain(f.id) ? ' large' : '') + '" style="display:none"></div>');
				if (f.id != this.config.photoField) {
					divEdit.append('<div class="label">' +  f.name  + (f.required == 1 ? '<font color="red">*</font>' : "") + (f.name ? ':' : '&nbsp;') + '</div>');
				} 
				fieldEdit = this.createEditField(f, group);
				divEdit.append(fieldEdit).hide();
				var links = $('<div class="field"><div class="label">&nbsp;</div></div>');
				var buttons = $('<div class="field_controls"></div>');
				
				var save = $('<input type="button" value="Сохранить" />').click(function(){
					_this.saveField(this.group, this.field);
				}.bind({group: group, field: f}));
				var cancel = $('<input type="button" value="Отмена" />').click(function () {
					_this.cancelField(this.group, this.field);
				}.bind({group: group, field: f}));
				if (this.isMain(f.id)) {
					$("#editMain").append(divEdit);
				} else {
					if (f.type != 'IMAGE') {
						links.hide().addClass('buttons').append(buttons.append(save).append(cancel));;
					}
					divEdit.append(links).insertAfter(div);									
				}
			}
			if ($("#" + f.id).hasClass("email")) divEdit.find("div.edit > input").addClass("email");			
		}
		if (this.config.editable) {
			$(g).find(".click").click(function () {
					group.edit = true;
					group.showAll = true;
					_this.renderGroup(group);
					if (typeof(_this.config.onEditGroup) == 'function') {
							_this.config.onEditGroup();	
					}
			});
		}
		var links = $('<div class="field"><div class="label">&nbsp;</div></div>');
		var buttons = $('<div class="field_controls"></div>');
		var save = $('<input type="button" value="Сохранить" />').click(function(){this.save(group);}.bind(this));
		var cancel = $('<input type="button" value="Отмена" />').click(function () {
			group.edit = false;
			this.cancelGroup(group);
		}.bind(this));
		
		links.hide().addClass('buttons');
		links.append(buttons.append(save).append(cancel));
		g.append('<div class="errors" style="display:none; clear: left;"><div class="label">&nbsp;</div><div class="edit" /></div>');
		g.append(links);
	},
	
	cancelGroup: function (group) {
		group.edit = false;
		this.renderGroup(group);
		var g = $("#" + group.id);
		if (group.id == 'CONTACT') {
			$("#editMain").hide();
		}
		g.find("div.errors > div.edit").html("");
		g.find("div.errors").hide();
		g.children(".error").removeClass("error");
		g.find("div.field > div.buttons").hide();
		if (this.config.onCancelGroup) {
			if (this.config.onCancelGroup[group.id]) {
				this.config.onCancelGroup[group.id]();
			} else if (typeof(this.config.onCancelGroup) == 'function') {
				this.config.onCancelGroup();
			}
		}		
	},
	
	cancelField: function (group, field) {
		if (this.isMain(field.id)) {
			for (var j = 0; j < group.fields.length; j++) {
				if (this.isMain(group.fields[j].id)) {
					group.fields[j].edit = false;
					this.renderField(group, group.fields[j]);
				}
			}
			$("#editMain").hide();
		} else {
			field.edit = false;
			this.renderField(group, field);			
		}
	},	
	
	cancelAll: function () {
		for (var i = 0; i < this.groups.length; i++) {
			this.cancelGroup(this.groups[i]);
		}
		$("#editMain").hide();
		$("#bottom_save").hide();
	},
	
	createEditField: function (f, group) {
		if (f.type == 'VARCHAR' && (f.value && f.value.length > 75)) {
			f.type = 'TEXT';
		}
		var div = $('<div class="edit"></div>');
		var edit = "";
		if (f.type == 'IMAGE') {
			edit = $('<img src="' + WbsData.get('url.common', '/common/') + 'img/loading.gif" />');
		} else if (f.type == 'MENU') {
			edit = $('<select class="fix-width"></select>');
			if (f.id == 'CF_ID') {
				if (document.isContactAdmin) {
					edit.append('<option value="">&lt;никакая&gt;</option>');
				}
			} else if (!this.config.noSelect && !f.required) { 
				edit.append('<option value="">&lt;выбрать&gt;</option>');
			}
			for (var i = 0; i < f.items.length; i++) {
				var item = f.items[i];
				var item_value = item.value ? item.value.replace(/</g, '&lt;').replace(/>/g, '&gt;') : item;
				if (item.offset) {
					for (var k = 0; k < item.offset; k++) {
						item_value = "&nbsp;" + item_value;
					}
				}
				item_value = item_value.substr(0, 50);
				edit.append('<option value="' + (item.key ? item.key : item) + '"' + (item.disabled ? ' disabled="disabled"' : '') + '>' + item_value + "</option>");
			}
		} else if (f.type == 'TEXT') {
			div.width("70%");
			edit = $("<textarea></textarea>");
		} else if (f.type == 'COUNTRY') {
			edit = $('<select class="fix-width"></select>');
			if (!f.required) {
				edit.append('<option value="">&lt;выбрать&gt;</option>');
			}
			var countries = WbsData.get('countries');
			for (var code in countries) {
				edit.append('<option value="' + code + '">' + countries[code] + "</option>");
			}
		} else if (f.type == 'CHECKBOX') {
			edit = $('<input type = "checkbox" />').css("width", "auto").css("margin-left", "0");
		} else if (f.type == 'SPAN') {
			edit = $('<span></span>');
		} else if (f.type == 'EMAIL') {
			var ss = '<input type = "text" /><br />';
			for (i = 1; i < f.value.length; i++) {
				ss += '<input type = "text" /><a class="delete_email" href="javascript:void(0)" onClick="DeleteInput(this)">Удалить</a><br />';
			}
			ss += '<a onfocus="blur(this)" href="javascript:void(0)" class="add_email" onClick="AddInput(this)">Добавить еще</a>';
			edit = ss;
		} else {
			edit = $('<input type = "text" />');
			
			if (f.type == 'DATE') {
				edit.addClass('DateField');
			}
		}
		var _this = this;
		if (f.max_length != undefined && f.max_length) {
			if (edit.blur) {
				edit.blur(function () {
					var l = edit.val().length;
					if (l > f.max_length) {
						_this.showFieldError({id: f.id, text: 'Количество символов не может превышать ' + f.max_length + '.'});
					}
				});
			}
		}
		div.append(edit);
		if (f.comment) {
			div.append('<span class="small">' + f.comment + '</span>')
		}
		return div;
	},
	
	viewAll: function(o) {
		this.requiredOnly = !this.requiredOnly;
		this.render();
	},
	
	prepareField: function(field, f, group) {
		if (f.type == 'IMAGE') {
			field.append('<img src="' + WbsData.get('url.common', '/common/') + 'img/loading.gif" /><p><br /></p>');	
		} else {
			if (this.config.editable) {
				$(field).attr("title", "Click to edit");
			}
			if (f.type == 'TEXT' || (f.type == 'VARCHAR' && f.value != null && f.value.length > 75)) {
				$(field).css('width', '70%');
			}
			var _this = this; 
			if (f.type != 'EMAIL' && this.config.editable) {
				$(field).click(function (e) {
					var t = e.target || e.srcElement;
					if ((t.tagName.toLowerCase() == 'img' && t.className != 'e') || t.tagName.toLowerCase() == 'a') {
						return true;
					}					
					if (f.type == 'SPAN') {
						return false;
					}
					if (f.edit == 'undefined') {
						f.edit = true;
					}
					f.edit = true;
					_this.renderField(group, f);
					if (_this.isMain(f.id)) {
						group.edit = true;
						group.showAll = true;
						_this.renderGroup(group);				
					}
					$("#edit_" + f.id + " div.buttons").show();
					$("#edit_" + f.id + " div.edit").children().select();
				});
			}
		}
		return field;
	},
	
	render: function (group) {
		if (group != null && group.id) {
			this.renderGroup(group);
		} else {
			for (var i = 0; i < this.groups.length; i++) {
				this.renderGroup(this.groups[i]);	
			}
		}
	},
	
	renderField: function (group, field) {
		if ((group.edit && !group.showAll) && this.requiredOnly && !(field.required > 0) && group.id != this.groups[0].id) {			
			$("#edit_" + field.id).hide();
			$("#" + field.id).hide();
			return ;
		}
		
		if (this.noImage && field.type == "IMAGE") {
			$("#edit_" + field.id).hide();
			$("#" + field.id).hide();
			return;
		}
		
		if (field.edit) {
			if (group.edit) {
				$("#edit_" + field.id).children('div.buttons').hide();
			}
			if ($("#edit_" + field.id).is(":visible")) {
				return;
			}
			this.setValue(group, field);
			if (this.isMain(field.id)) {
				$("#editMain").show();
			}
			if (field.type != 'EMAIL' || !this.config.disableEmail) {
				$("#" + field.id).hide();
			}
			
			$("#edit_" + field.id).show();
			if (field.type == 'IMAGE') {
				this.setUpload(group, field);
			}			
		} else {
			this.setValue(group, field);
			$("#edit_" + field.id).hide();
			if (this.showAll == false && (field.value == null || field.value.length == 0)) {
				if (field.id != this.config.photoField && field.type != 'CHECKBOX' && field.id != 'CF_ID') {		
					$("#" + field.id).hide();
				} else {
					$("#" + field.id).show();	
				}
			} else {
				$("#" + field.id).show();
			}			
		}
	},
	
	renderGroup: function(group) {
		if (group == null || !group) {
			return false;
		}
		var g = $("#" + group.id);
		if (group.fields.length == 0) {
			g.hide();
			return true;
		}
		if (!group.edit) {
			var empty = 0;
			for (var i = 0; i < group.fields.length; i++) {
				group.fields[i].edit = group.edit;
				this.renderField(group, group.fields[i]);
				if (!group.fields[i].value) {
					empty++;
				}
			}
			
			if (this.requiredOnly == true && empty == group.fields.length) {
			//	$("#" + group.id).hide();
			}
			
			$(g).children(".buttons").hide();
		} else {
			var empty = 0;
			for (var i = 0; i < group.fields.length; i++) {
				group.fields[i].edit = group.edit;
				this.renderField(group, group.fields[i]);
				if (i == 0) {
					$("#edit_" + group.fields[i].id + " div.edit").children().select();
				}
				if (!group.fields[i].value && group.fields[i].required == "0") {
					empty++;
				} 
			}
			if (this.requiredOnly == true && empty == group.fields.length) {
				//$("#" + group.id).hide();
			} else {
				$("#" + group.id).show();
			}
			if (!this.editAll) {
				$(g).children(".buttons").show();
			} else {
				$(g).children(".buttons").hide();
			}
			if (this.config.onEditGroup && this.config.onEditGroup[group.id]) {
				this.config.onEditGroup[group.id]();
			}
		}
	},
	
	isMain: function (id, super_main) {
		var s = super_main | false;
		var a = s ? this.config.super_main_fields : this.main_fields;
		if (!a) {
			return false;
		}
		for (var i = 0; i < a.length; i++) {
			if (a[i] == id) return true;
		}
		return false;
	},
	
	truncate: function (str, n) {
		var a = str.toString().split(" ");
		for (var i = 0; i < a.length; i++) {
			if (a[i].length > n) {
				var s = "";
				while (a[i].length > n) {
					s = s + htmlspecialchars(a[i].substr(0, n)) + ($.browser.mozilla ? "<wbr />" : "&shy;");
					a[i] = a[i].substr(n); 
				}
				a[i] = s + htmlspecialchars(a[i]);
			} else {
				a[i] = htmlspecialchars(a[i]);
			}
		}
		return a.join(" ");
	},
	
	setValue: function (g, f) {
		var v = f.value ? f.value : "";
		if (f.type == 'TEXT') {
			setTimeout(function () {
				$("#" + f.id + " > div.edit").html(this.truncate(v, 20));
			}.bind(this), 10);

			$("#" + f.id).width("100%");
			$("#" + f.id + " > .edit").width("70%");
			$("#edit_" + f.id + "> div.edit > textarea").html(v.replace(/</gi, "&lt;"));
			$("#edit_" + f.id + "> div.edit > textarea").setHeight($("#" + f.id + " > div.edit").outerHeight() + 5, 50, 500);
			if ($("#" + f.id + " > .edit").width() > 0) {
				$("#edit_" + f.id + "> div.edit > textarea").width($("#" + f.id + " > .edit").width());
			}
		}
		else if (f.type == 'VARCHAR') {
			setTimeout(function () {
				$("#" + f.id + " > div.edit").html(this.truncate(v, 20));
			}.bind(this), 10);
			if (g.id != 'CONTACT') {
				$("#" + f.id).width("100%");
				if (f.value != null && f.value.length > 75) {
					$("#" + f.id + " > .edit").width("70%");
				}
			}
			$("#edit_" + f.id + "> div.edit > input").val(v);			
		}
		else if (f.type == 'MENU') {
			if (f.id == 'CF_ID' && !v) {
				$("#" + f.id + " > div.edit").html('&lt;никакая&gt;');
			} else {
				$("#" + f.id + " > div.edit").html(v);
			}
			for (var j = 0; j < f.items.length; j++) {
				if (f.items[j].key != null && f.items[j].key == v) {
					$("#" + f.id + " > div.edit").text(f.items[j].value);
				}
			}
			$("#edit_" + f.id + "> div.edit > select").val(v);
		}
		else if (f.type == 'COUNTRY') {
			if (v) {
				var countries = WbsData.get('countries');
				$("#" + f.id + " > div.edit").html('<img class="e" src="' + WbsData.get('url.common', '/common/') + 'img/flag/' + v + '.png" /> ' + countries[v]);
			} else {
				$("#" + f.id + " > div.edit").html('');
			}
			$("#edit_" + f.id + "> div.edit > select").val(v);
		}		
		else if (f.type == 'IMAGE') {
			var full_v = v;
			if (v != null && v.length > 0) {
				v = v + "&size=" + (f.size != undefined ? f.size : "96");
				
				if (v == $("#edit_" + f.id + " > div.edit img").attr('src')) {
					return;
				}
			}
			
			if (f.id == this.config.photoField) {
				if (this.config.editable) {
					$("#" + f.id).parent().mouseover(function () {
						$("#edit_" + f.id).show();
						$("#" + f.id).hide();
					}).bind('mouseleave', function () {
						if (!g.edit) {
							setTimeout(function () {
								$("#" + f.id).show();
								$("#edit_" + f.id).hide();
							}, 2000);
						}
					});
				}

				var div = $("#edit_" + f.id + " > div.edit");
				div.unbind();
				div.find('p').remove();
				
				if (v && v != null && v.length > 0) {
					var _this = this;
					$("#" + f.id + " > div.edit img").removeAttr("width").removeAttr("height").attr('src', v).show();
					var img = new Image();
					img.src = v;
					$(img).load(function () {
						var i = $("#edit_" + f.id + " > div.edit img");
						i.removeAttr("width").removeAttr("height").attr('src', $(this).attr('src')).show();						
						if ($("#edit_" + f.id + "> div.edit a.link").length) {
							$("#edit_" + f.id + "> div.edit a.link").attr('href', full_v);
						} else {
							var a = $('<a class="link" target="_blank" href="?mod=users&act=thumb&fid=' + f.id + '&id=' + _this.config.contact + '"></a>').append(i.clone(true));
							i.replaceWith(a);
						}
					});
					div.append($('<p style="width:105px;text-align:center"><a href="javascript:void(0)">Изменить фото</a></p>')
					.click(function(){
						$('#popup2').wbspopup({
							url: 'index.php?mod=users&act=image&C_ID='+document.contact+'&CF_ID='+f.id+'&type=change',
							width: 590,
							height: 440,
							iframe: true,
							
							onClose: function(param){
								if (param && param.deleteImage) {
									this.f.value = '';
									this.self.setValue(this.g, this.f);
								}
							}.bind({self:this, 'f': f, 'g': g}),
							onSuccess: function(response) {
								if (response.url) {
									this.f.value = response.url.replace(/amp;/, '').replace(/amp;/, '');
									this.self.setValue(this.g, this.f);
								}
							}.bind({self:this, 'f': f, 'g': g})
							
						});
						return false;
					}.bind(this)));
				} else {
					$("#" + f.id + " > div.edit > img").attr('src',WbsData.get('url.img') + 'empty-contact' + (this.config.type >=2 ? this.config.type : '') + '.gif');
					if ($("#edit_" + f.id + " > div.edit a.link").length) {
						$("#edit_" + f.id + " > div.edit a.link").replaceWith($("#edit_" + f.id + " > div.edit img"));
					}
					$("#edit_" + f.id + " > div.edit > img").attr('src', WbsData.get('url.img') + 'empty-contact' + (this.config.type >=2 ? this.config.type : '') + '.gif');
					div.append($('<p class="add-photo"><a href="javascript:void(0)">Добавить фото</a></p>'))
					.click(function(){
						$('#popup2').wbspopup({
							url: 'index.php?mod=users&act=image&C_ID='+document.contact+'&CF_ID='+f.id+'&type=add',
							width: 590,
							height: 440,
							opacity: 0.1,
							iframe: true,
							onOpenComplite: function(){
							},
							onSuccess: function(response) {
								if (response.url) {
									this.f.value = response.url.replace(/amp;/, '').replace(/amp;/, '');
									this.self.setValue(this.g, this.f);
								}
							}.bind({self:this, 'f': f, 'g': g}),

							onClose: function(){
							}
							
						});
						return false;
					}.bind(this));					
				}
				return ;
			}
			
			$("#" + f.id + " > div.edit").unbind();
			$("#edit_" + f.id + " > div.edit").unbind();
			$("#edit_" + f.id + " > div.edit img").unbind();
			$("#edit_" + f.id + " > div.edit img").nextAll().remove();		
			$(document).unbind('click');
			if (v && v != null && v.length > 0) {			
				$("#" + f.id + " > div.edit img").removeAttr("width").removeAttr("height").attr('src', v).show();
		
				$("#" + f.id + " > div.edit").mouseover(function () {
					$("#" + f.id).hide();
					$("#edit_" + f.id).show();
					if (f.id != this.config.photoField) {
						this.setUpload(g, f);
					}
				}.bind(this));
				
				var img = new Image();
				img.src = v;
				$(img).load(function () {
					$("#edit_" + f.id + " > div.edit img").removeAttr("width").removeAttr("height").attr('src', $(this).attr('src')).show();
					if (img.width >= f.size || img.height >= f.size) {
						$("#edit_" + f.id + " > div.edit img").attr('_id', f.id).attr('_url', f.value).click(zoom);
						$("#edit_" + f.id + " > div.edit").css("cursor", "pointer");
					} else {
						$("#edit_" + f.id + " > div.edit").css("cursor", "default");
					}
					
					var i = $("#edit_" + f.id + " > div.edit img");
					var a = $('<a class="link" target="_blank" href="?mod=users&act=thumb&fid=' + f.id + '&id=' + document.contact + '"></a>').append(i.clone(true));
					if ($("#edit_" + f.id + " > div.edit > a.link").length) {
						$("#edit_" + f.id + " > div.edit > a.link").replaceWith(a);
					} else {
						$("#edit_" + f.id + " > div.edit > img").replaceWith(a);
					}
				});
				
				var div = $("#edit_" + f.id + " > div.edit");
				if (div.children().length == 1) {
					var del = $('<a id="delete_' + f.id + '" href="#">Удалить</a>').click(function () {
							this.deleteImage(g, f);
					}.bind(this));
					var replace = $('<a class="upload" id="upload_' + f.id + '" href="#">Заменить</a>');
					if (f.id == this.config.photoField) {
						div.append($('<p style="width:100px;text-align:center"><a href="javascript:void(0)">Заменить</a></p>'));
					} else {
						div.append($('<p></p>').append(replace).append('<span style="color:#0043A7">&nbsp;|&nbsp;</span>').append(del));
					}
					div.ready(function () {
						if ($("#edit_" + f.id).is(":visible")) {
							this.setUpload(g, f);
						}
					}.bind(this));
				}
			} else {
				if (f.id == this.config.photoField) {
					$("#" + f.id + " > div.edit > img").attr('src','img/empty-contact' + (this.config.type >=2 ? this.config.type : '') + '.gif');
					if (this.config.editable) {
						$("#edit_" + f.id + " > div.edit > img").attr('src', 'img/empty-contact' + (this.config.type >=2 ? this.config.type : '') + '.gif');
						$("#" + f.id + " > div.edit").mouseover(function () {
							$("#edit_" + f.id).show();
							$("#" + f.id).hide();
							this.setUpload(g, f);					
						}.bind(this));
					}
				} else {
					$("#edit_" + f.id + " > div.edit a.link").replaceWith($("#edit_" + f.id + " > div.edit a.link img"));
					$("#" + f.id + " > div.edit img").hide().attr('src', WbsData.get('url.common', '/common/') + 'img/loading.gif');
					$("#edit_" + f.id + " > div.edit img").hide().attr('src', WbsData.get('url.common', '/common/') + 'img/loading.gif');
				}
				var div = $("#edit_" + f.id + " > div.edit");
				if (div.children().length == 1) {
					div.append($('<p style="padding-top:0" align="center"><a class="upload" id="upload_' + f.id + '" href="#">Загрузить изображение</a></p>'));
				} else {
					div.find("p").replaceWith($('<p align="center"><a class="upload" id="upload_' + f.id + '" href="#">Загрузить изображение</a></p>'));
				}
				if ($("#edit_" + f.id).is(":visible")) {
					this.setUpload(g, f);
				}
			}
		}
		else if (f.type == 'CHECKBOX') {
			$("#" + f.id + " > div.edit").html(v > 0 ? "Да" : "Нет");
			$("#edit_" + f.id + "> div.edit > input").attr("checked", (v > 0 ? "checked" : ""));
		}
		else if (f.type == 'SPAN') {
			$("#" + f.id + " > div.edit").html(v);
			$("#edit_" + f.id + " > div.edit > span").html(v);
		}
		else if (f.type == 'EMAIL'){
			var _this = this;
			if ((!v || v == '0') && this.config.emptyValue) {
				var h = this.config.emptyValue;
			} else {
				var h ='';
				for (var k = 0; k < v.length; k++) {
					h += '<div>' + v[k];
					if (this.config.compose_mail) {
						h += ' <a class="compose-message" href="?mod=users&act=email&mode=contacts&contact_id=' + this.config.contact + '&email=' + v[k] + '">Написать письмо</a>';
					}
					h += '</div>';
				}
			}
			$("#" + f.id + " > div.edit").html(h);
			if (this.config.editable && !this.config.disableEmail) {
				$("#" + f.id + " > div.edit > div").each(function (i) {
					$(this).click(function (e) {
						var t = e.target || e.srcElement;
						if (t.tagName.toLowerCase() == 'a') {
							location.href = t.href;
							return false;
						}
						f.edit = true;
						_this.renderField(g, f);
						if (_this.isMain(f.id)) {
							group.edit = true;
							group.showAll = true;
							_this.renderGroup(g);				
						}
						$("#edit_" + f.id + " div.buttons").show();
						$("#edit_" + f.id + " div.edit input:eq(" + i + ")").select();
					});
				});
			
				$("#edit_" + f.id + "> div.edit input").each(function (i) {
					if (i > 0 && i >= v.length) {
						$(this).prev().remove();
						$(this).next().remove();
						$(this).remove();
					} else {
						$(this).val(v[i]);
					}
				})
			}
		}
		// MOBILE
		else if (f.type == 'MOBILE') {
			var vv = ((!v || v == '0') && this.config.emptyValue) ? this.config.emptyValue : htmlspecialchars(v);
			if (v) {
				var a = $('<a class="send-sms" href="javascript:void(0)" title="Отправить SMS">Отправить SMS</a>');
				a.click(function () {
					$('#popup').wbsPopup({
				       width: 600,
				       height: 'auto',
				       backgroundColor: '#000000',
				       opacity: 0.1,
				       url: "?mod=contacts&act=sendsms&mode=folders&users=",
				       loadComplite: function () {
							$('#popup').wbsPopupRender();
							$('.sendsms_wrapper').wrap('<div class="wbs-dlg-content"></div>');
							$('#popup').prepend(
								'<div class="wbs-dlg-header">'+
									'<div class="label">Отправить SMS</div>'+
									'<div class="close-btn"><img width="16" height="16" src="../common/templates/img/close.gif" /></div>'+
								'</div>'
							);
							$('#popup').append(
								'<div class="wbs-dlg-footer">'+
									'<input id="doSendsmsBtn" class="wbs-dlg-button" type="button" value="Отправить"/>'+
									'<input class="wbs-dlg-button" type="button" value="Отмена" onclick="$(\'#popup\').wbsPopupClose()" />'+
								'</div>'
							);
							$("#To").val(v);
							$('#doSendsmsBtn').click(function(){
								//alert($("#message").val());
								if (!$("#To").val().replace(/^\s*(.*?)\s*$/,"$1")) {
									$('#error_block').html("Введите номер телефона").show();
									$('#To').focus();
								} else if (!$("#message").val().replace(/^\s*(.*?)\s*$/,"$1")) {
									$('#error_block').html("Пожалуйста, введите текст сообщения");
									$('#error_block').show();
									$('#message').focus();
								} else {
									$.post("?mod=contacts&act=sendsms&mode=folders",
										{"To": $("#To").val() + "", "message": $("#message").val() + ""},
										function (response) {
											try {
												var r = eval("(" + response + ")");
												if (r.Error) {
													$('#error_block').html(r.Error).show();
												} else {
													$("#onload-message").show().html('SMS было успешно отправлено');
													$("#onload-message").fadeOut(5000, function () {$(this).hide()});
													$("#popup").wbsPopupClose();
												}
											} catch (e) {
												$('#error_block').html(response);
												$('#error_block').show();												
											}
										}
									);
								}
							});
							
							$("#popup .close-btn").click(function () {
								$("#popup").wbsPopupClose();
							});					
				       }		       
					});
					return false;	
				});	
				$("#" + f.id + " > div.edit").html(vv);
				if (!this.config.disableEmail) {
					$("#" + f.id + " > div.edit").append(' ');
					$("#" + f.id + " > div.edit").append(a);
				}
			} else {
				$("#" + f.id + " > div.edit").html(vv);
			}

			//$("#" + f.id + " > div.edit").html(vv);			
			$("#edit_" + f.id + "> div.edit > input").val(v);
		}	
		else if (f.type == 'URL') {
			var vv = ((!v || v == '0') && this.config.emptyValue) ? this.config.emptyValue : htmlspecialchars(v);
			if (v) {
				vv += '<a class="open-url" target="_blank" href="' + v + '"><img src="../UG/img/new_window_icon.gif" /></a>';
			}
			$("#" + f.id + " > div.edit").html(vv);			
			$("#edit_" + f.id + "> div.edit > input").val(v);
		}			
		else {
			$("#" + f.id + " > div.edit").html(((!v || v == '0') && this.config.emptyValue) ? this.config.emptyValue : htmlspecialchars(v));
			$("#edit_" + f.id + "> div.edit > input").val(v);
		}
	
	},
	
	setUpload: function (g, f) {
			
			if (f.id == this.config.photoField) {
				var elem = $("#CURRENT_PHOTO"); 
			} else {
				var elem = $("#edit_" + f.id);
			}
			elem.bind("mouseleave", function (e) {
				if (!g.edit && !$.browser.safari) {
					setTimeout(function () {
							if ((f.value != null && f.value.length > 0) || (f.id == this.config.photoField)) {
								$("#" + f.id).show();
							}
							$("#edit_" + f.id).hide();
					}.bind(this), 1000);
				}
			}.bind(this));
			if ($("#upload_" + f.id).attr("upload") != "1") {
				var uploadComplete = function (file, response) {
						response = eval('(' + response + ')');
						if (response.status == 'OK') {
							f.value = response.files[0].url.replace(/&amp;/g, '&');
							this.setValue(g, f);
						} else {
							this.showError(response.error[0], g);
							this.setValue(g, f);
						}
				}.bind(this);						
				new Ajax_upload("#upload_" + f.id, {
					flt: "left",
					action: "?mod=users&act=image&ajax=1&C_ID=" + encodeURIComponent(document.contact) + (f.id == this.config.photoField ? "&avatar=1" : ""), 
					name: f.id,
					onSubmit: function () {
						$("#edit_" + f.id + " > div.edit > img").removeAttr("width").removeAttr("height").show().attr('src', WbsData.get('url.common', '/common/') + "img/loading.gif");
					}.bind(this),
					onComplete: uploadComplete
				});
				$("#upload_" + f.id).attr("upload", "1");
			}		
	},
	
	showError: function(error, group) {
		if (error.ids)  {
			var g = $("#edit_" + error.ids[0]).parents(".group");
		} else if (group == null) {
			var g = $("#" + error.id).parent();
		} else {
			var g = $("#" + group.id);
		}
		g.find("> div.errors").show();
		g.find("> div.errors > div.edit").append(error.text + "<br />");
		if (error.ids) {
			for (var i = 0; i < error.ids.length; i++) {
				$("#edit_" + error.ids[i]).addClass("error").click(function () {
					g.find("> div.errors").hide();
					g.find("> div.errors > div.edit").html("");
					for (var j = 0; j < error.ids.length; j++) {
						$("#edit_" + error.ids[j]).removeClass("error");
					}
				});
			}
		} else {
			$("#edit_" + error.id).addClass("error").click(function () {
				g.find("> div.errors").hide();
				g.find("> div.errors > div.edit").html("");
				$(this).removeClass("error");
			});
		}
		if (this.editAll) {
			var id = $("#bottom_save").length ? "#bottom_save" : "#block_save_main";
			if ($(id + " div.error").length) {
				$(id + " div.error").show();
			} else {
				$(id).prepend('<div class="error">Пожалуйста, исправьте ошибки.</div>');
			}
		}
	},
	
	showFieldError: function(error) {
		if (error.ids) {
			for (var i = 0; i < error.ids.length; i++) {
				$("#edit_" + error.ids[i] + " > div.edit :input").addClass("error");
				$("#edit_" + error.ids[i]).addClass("error").click(function () {
					var g = $("#edit_" + error.ids[error.ids.length - 1]);
					g.find("> div.errors").hide();
					g.find("> div.errors > div.edit").html("");
					for (var j = 0; j < error.ids.length; j++) {
						$("#edit_" + error.ids[j]).removeClass("error");
					}
				});
			}			
			error.id = error.ids[error.ids.length - 1];
		}		
		
		var f = $("#edit_" + error.id);
		var er = $("#edit_" + error.id + " > div.errors > div.edit");
		if (er.length > 0) {
			er.html(error.text).parent().show();
		} else {
			$('<div class="field errors" style="padding:0"><div class="label">&nbsp;</div><div class="edit">' + error.text + '</div>').insertAfter($("#edit_" + error.id + " > div.edit"));
		}	
		if (error.i != null) {
			$("#edit_" + error.id + " > div.edit > input:eq(" + error.i + ")").addClass("error");
		} else {
			$("#edit_" + error.id + " > div.edit :input").addClass("error");
		}
		f.addClass("error").click(function () {
			f.find("div.errors").hide();
			$(this).removeClass("error");
		});
		
		if (this.editAll) {
			var id = $("#bottom_save").length ? "#bottom_save" : "#block_save_main";
			if ($(id + " div.error").length) {
				$(id + " div.error").show();
			} else {
				$(id).prepend('<div class="error">Пожалуйста, исправьте ошибки.</div>');
			}
		}		
	},

	
	getValue: function (f) {
		if (f.type == 'IMAGE') {
			return null;
		} else if (f.type == 'TEXT') {
			return $("#edit_" + f.id + " > div.edit > textarea").val();
		} else if (f.type == 'CHECKBOX') {
			return $("#edit_" + f.id + " > div.edit > input").is(":checked") ? 1 : 0;
		} else if (f.type == 'MENU' || f.type == 'COUNTRY') {
			return $("#edit_" + f.id + " > div.edit > select").val();
		} else if (f.type == 'EMAIL') {
			var a = new Array();
			$("#edit_" + f.id + " > div.edit > input").each(function (i) {
				a[i] = $(this).val();
			});
			return a;
		} else {
			return $("#edit_" + f.id + " > div.edit > input").val();
		}						
		
	},
	
	deleteImage: function (g, f) {
		params = new Array();
		params.push("G_ID=" + ($("#" + f.id).parents("div.group").attr("id") || "CONTACT"));
		params.push("C_ID=" + encodeURIComponent(this.contact));
		params.push("info[" + f.id + "]=");
		params = params.join("&");		
		$.post(this.config.saveUrl, params, function(response) {
			if (response.status == 'OK') {
				f.value = null;
				this.setValue(g, f);
			}
		}.bind(this), "json");
	},
	
	setEditAll: function(button) {
		this.editAll = !this.editAll;
		if (this.editAll) {
			
			for (var i = 0; i < this.groups.length; i++) {
				this.groups[i].edit = true;
			}
			var save = $('<input id="save_all" type="button" value="Сохранить" />').css('margin-right', '5px').click(function () {
				this.save(null);
				if (this.editAll == false) {
					$(button).show();
					$(button).parent().next().show();
					$(button).nextAll().remove();
				}
			}.bind(this));
			var cancel = $('<input id="cancel_all" type="button" value="Отмена" />').click(function () {
				if (this.config.onCancelAll) {
					this.config.onCancelAll();
				} else {
					this.cancelAll();
					this.editAll = false;				
					$(button).show();
					$(button).parent().next().show();
					$(button).nextAll(":visible").remove();
					$(button).nextAll(".nd").show();
				}
			}.bind(this));
			$(button).parent().next().hide();			
			save.insertAfter(button);
			cancel.insertAfter(save);
			$("#bottom_save").show().empty().append(save.clone(true)).append(cancel.clone(true));
			$(button).hide();
		} 
		this.render();
	},
	
	setShowAll: function () {
		this.showAll = !this.showAll;
		this.render();
	},
	
	saveField: function(group, field) {
		var params = new Array();
		var is_main = false;
		if (group && this.isMain(field.id)) {
			var is_main = true;
			for (var i = 0; i < group.fields.length; i++) {
				if (this.isMain(group.fields[i].id)) {
					if (group.fields[i].type == 'EMAIL') {
						var a = this.getValue(group.fields[i]);
						for (var k = 0; k < a.length; k++) {
							params.push('info[' + group.fields[i].id + "][]=" + encodeURIComponent(a[k]));
						}
					} else  {					
						params.push('info[' + group.fields[i].id + "]=" + encodeURIComponent(this.getValue(group.fields[i])));
					}						
				}
			}
		} else {
			if (field.type == 'EMAIL') {
				var a = this.getValue(field);
				for (var k = 0; k < a.length; k++) {
					params.push('info[' + field.id + "][]=" + encodeURIComponent(a[k]));
				}
			} else  {					
				params.push('info[' + field.id + "]=" + encodeURIComponent(this.getValue(field)));
			}
		}
		params.push("G_ID=" + (group ? group.id : ""));
		params.push("C_ID=" + encodeURIComponent(this.contact));
		params = params.join("&");
		var _this = this;
		$.post(this.config.saveUrl, params, function (response) {
			if (response.status == 'OK') {
				if ($("#display_name > div.edit").length) {
					$("#display_name > div.edit").html(response.data['display_name']);
				} else {
					$("#display_name").html(response.data['display_name']);
				}
				if (!is_main && !_this.config.updateAll) {
					field.value = response.data[field.id].value;
					field.edit = false;
					_this.renderField(group, field);
				} else if (_this.config.updateAll) {
					field.edit = false;
					for (var i = 0; i < group.fields.length; i++) {
						if (response.data[group.fields[i].id] != undefined) {
							group.fields[i].value = response.data[group.fields[i].id].value;
							_this.renderField(group, group.fields[i]);
						}
					}
				} else {
					for (var i = 0; i < group.fields.length; i++) {
						if (_this.isMain(group.fields[i].id)) {
							group.fields[i].edit = false;
							group.fields[i].value = response.data[group.fields[i].id].value;
							_this.renderField(group, group.fields[i]);
						}
					}
					$("#editMain").hide();
					var g = $("#" + group.id);
					g.find("> div.errors").hide();
					g.find("> div.errors > div.edit").html("");					
				}
			} else if (response.status == 'ERR') {
				for (var i = 0; i < response.error.length; i++) {
					if (response.error[i].id == group.id) {
						_this.showError(response.error[i], group);
					} else {
						_this.showFieldError(response.error[i]);
					}
				}
			}
		},"json");
		
	},
		
	save: function(group) {
		var params = new Array();
		if (!group) {
			for (var i = 0; i < this.groups.length; i++) {
				var g = this.groups[i];
				$("#" + g.id).find("div.errors > div.edit").html("");
				for (var j = 0; j < g.fields.length; j++) {
					if (g.fields[j].type == 'EMAIL') {
						var a = this.getValue(g.fields[j]);
						for (var k = 0; k < a.length; k++) {
							params.push('info[' + g.fields[j].id + "][]=" + encodeURIComponent(a[k]));
						}
					} else if (g.fields[j].type != 'IMAGE') {
						params.push('info[' + g.fields[j].id + "]=" + encodeURIComponent(this.getValue(g.fields[j])));
					}
					$("#" + g.id).find("div.errors > div.edit").html("");
				}
			}
		} else {
			$("#" + group.id).find("div.errors > div.edit").html("");
			for (var i = 0; i < group.fields.length; i++) {
				if (group.fields[i].type == 'EMAIL') {
					var a = this.getValue(group.fields[i]);
					for (var k = 0; k < a.length; k++) {
						params.push('info[' + group.fields[i].id + "][]=" + encodeURIComponent(a[k]));
					}
				} else if (group.fields[i].type != 'IMAGE') {
					params.push('info[' + group.fields[i].id + "]=" + encodeURIComponent(this.getValue(group.fields[i])));
				}
			}
			$("#" + group.id).find("div.errors > div.edit").html("");
			params.push("G_ID=" + (group ? group.id : ""));
		}
		
		
		params.push("C_ID=" + encodeURIComponent(this.contact));
		params.push("typeId="+document.typeId);
		params = params.join("&");
		var result = false;

		var success = function(response) {
			var responseData = response;
			if (responseData.status == 'OK') {
				if (this.config.saveSuccess) {
					this.config.saveSuccess(response);
					return true;
				}
				$("#editMain").hide();
				$("#bottom_save").hide().empty();
				if ($("#display_name > div.edit").length) {
					$("#display_name > div.edit").html(responseData.data['display_name']);
				} else {
					$("#display_name").html(responseData.data['display_name']);
				}
				if (!group) {
					for (var i = 0; i < this.groups.length; i++) {
						group = this.groups[i];
						for (j = 0; j < group.fields.length; j++) {
							if (responseData.data[group.fields[j].id] != undefined) {
								group.fields[j].value = responseData.data[group.fields[j].id].value;
							}
						}
						group.edit = this.config.edit ? true : false;
					}
					this.editAll = false;
					this.render();
				}
				else {
					for (i = 0; i < group.fields.length; i++) {
						if (group.fields[i].type != 'IMAGE') {
							try {
								group.fields[i].value = responseData.data[group.fields[i].id].value;
							} catch (e) {
								console.log(i);
								console.log(responseData.data);
								console.log(group.fields);
							}
						}
					}
					group.edit = this.config.edit ? true : false;
					this.renderGroup(group);
				}
				if (this.config.onSave) {
					this.config.onSave();
				}
			}
			else if (responseData.status == 'ERR') {
				for (i = 0; i < responseData.error.length; i++) {
					
					if (responseData.error[i].id != null || responseData.error[i].ids != null) {
						this.showFieldError(responseData.error[i], null);
					} else {
						this.showError(responseData.error[i]);
					}
				} 
			}
		}.bind(this);
		
		$.ajax({
			type: "POST",
			url: this.config.saveUrl,
			data: params,
			dataType: "json",
			success: success,
			async: false
		});
		
	}
});
