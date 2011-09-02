$.fn.rightsTree = function (settings) {
	return this.each(function() {
		if(rights_tree.inst && rights_tree.inst[$(this).attr('id')]) 
			rights_tree.inst[$(this).attr('id')].destroy();
		if(settings != false) {
			var rtree = new rights_tree();
			rtree.init(this, settings);
		}
	});
}

function treeClick(elem, app_id) {
	if ($(elem).is(":hidden")) {
		return false;
	}
	var fl = $(elem).parent(); 
	if (fl.hasClass('expandable')) {
		fl.removeClass('expandable').addClass('collapsable').children('ul').show();
		$(elem).removeClass('expandable-hitarea').addClass('collapsable-hitarea');
	} else if (fl.hasClass('collapsable')) {
		fl.removeClass('collapsable').addClass('expandable').children('ul').hide();
		$(elem).addClass('expandable-hitarea').removeClass('collapsable-hitarea');
	}
}

function saveRight(input, app_id, folder_id, value, is_group) {
	if ($(input).is(":checked")) {
		$(input).prevAll().attr('checked', "checked");
		$(input).parent().addClass('access').parent().addClass('access').prev().addClass('access');
	} else {
		$(input).nextAll().attr('checked', "");
		value = value >> 1;
		if (value == 0 && !$(input).parent().prev().hasClass('access')) {
			$(input).parent().removeClass('access').parent().removeClass('access').prev().removeClass('access');	
		}
	}					
	$.post("index.php?mod="+ (is_group ? "groups" : "users") +"&act=rights&ajax=1", 
			{action: 'save', 
			 id: right_id, 
			 application_id: app_id, 
			 section: 'FOLDERS', 
			 object_id: folder_id, 
			 value: value}
	);
	
	var li;
	var ul = $(input).parent().parent();
	do {
		li = ul.parent();
		if (li.children('ul').length > 0) {
			if (li.children("ul").find('li > div > span.access').length == 0) {
				li.children('div.hitarea').show();
			} else {
				li.children('div.hitarea').hide();
			}
		}
		ul = li.parent(); 
	} while (ul.attr('id') != 'tree' + app_id);
}

function rights_tree() {
	if(typeof rights_tree.inst == "undefined") {
		rights_tree.cntr = 0;
		rights_tree.inst = new Array();	
	}
	return {
		settings: {
		},
		elem : {},
		init : function(elem, opts) {
			var _this = this;
			this.elem = $(elem);
			if(this.elem.size == 0) {
				return 
			}
			rights_tree.inst[$(elem).attr('id')] = this;
			this.settings = opts;
			this.settings.checkHitarea = function (folder) {
				if (folder[4].length == 0) return;
				var fid = folder[0].replace(/\./g, "\\.");
				var li = $("#" + this.settings.app_id + fid);
				
				if (li.children("ul").find('li > div > span.access').length == 0) {
					li.children('div.hitarea').show();
				} else {
					li.children('div.hitarea').hide();
				}
				for (var l = 0; l < folder[4].length; l++) {
					this.settings.checkHitarea(folder[4][l]);
				}
			}.bind(this);
			this.render();	
			this.attachEvents();			
		},

		render : function () {
			var str = '<ul class="filetree treeview" id="tree'+ this.settings.app_id + '">';
			for (var j = 0; j < this.settings.folders.length; j++) {
				str += this.renderFolder(this.settings.folders[j], j == this.settings.folders.length - 1);	
			}
			str += '</ul>';
			this.elem.html(str);
		},
		getRightTitle: function(code) {
			switch (code) {
				case 1: return "Чтение";
				case 3: return "Запись";
				case 7: return "Полные";
				default: return "&nbsp;"
			} 
		},
		truncate: function (str, len) {
			if (str.length > len) {
				return str.substring(0, len) + "...";
			} else {
				return str;
			}
		},
		renderFolder: function (folder, last) {
			var str = '<li class="expandable'+ (last ? " last" : "") + '" id="' + this.settings.app_id + folder[0] + '">';
			str += '<div class="hitarea expandable-hitarea" onClick="treeClick(this, \''+ this.settings.app_id +'\');"/>';
			str += '<span class="folder ' + (folder[3][0] > 0 ? "access" : "") + '">' + this.truncate(folder[2], 55) + (folder[1] == 'ROOT' && folder[5] > 0 ? ' (' + this.settings.title_inherit + ')' : '') + '</span>';
			str += '<div class="folder ' + (folder[3][0] > 0 ? "access" : "") + '">';
			if (folder[1] == 'ROOT' && folder[5] > 0) {
				str += '<span class="user" style="width:' + (this.settings.is_group ? '150' : '300') + 'px">' + '</span>';
			} else {
				if (!this.settings.is_group) {
					str += '<span class="right group '+ (folder[3][2] > 0 ? 'access' : "") +'">' + this.getRightTitle(folder[3][2]) + '</span>';
				}
				str += '<span class="right user '+ (folder[3][0] > 0 ? 'access' : "") +'">' 
					 + '<input ' + (folder[5] > 0 ? "disabled" :"") + ' type="checkbox" value="1" onclick="saveRight(this, \'' + this.settings.app_id + '\', \'' + folder[0] + '\' ,1, ' + this.settings.is_group + ')" ' + (folder[3][1] >= 1 ? "checked" : "") + '/>'
					 + '<input ' + (folder[5] > 0 ? "disabled" :"") + ' type="checkbox" value="3" onclick="saveRight(this, \'' + this.settings.app_id + '\', \'' + folder[0] + '\' ,3, ' + this.settings.is_group + ')" ' + (folder[3][1] >= 3 ? "checked" : "") + '/>' 
					 + '<input ' + (folder[5] > 0 ? "disabled" :"") + ' type="checkbox" value="7" onclick="saveRight(this, \'' + this.settings.app_id + '\', \'' + folder[0] + '\' ,7, ' + this.settings.is_group + ')" ' + (folder[3][1] >= 7 ? "checked" : "") + '/>' 	  
					 + '</span>';
			}
			str += '<br style="clear: right" /></div>';
			if (folder[4].length > 0) {			
				str += '<ul style="display:none;">';
				for (var i = 0; i < folder[4].length; i++) {
					str += this.renderFolder(folder[4][i], i == folder[4].length - 1);
				}
				str += '</ul>';
			}
			str += '</li>';
			return str;
		},

		attachEvents: function() {
			for (var i = 0; i < this.settings.folders.length; i++) {
				var f = this.settings.folders[i];
				this.attachFolderEvents(f); 
			} 	
			var ul = $("#tree" + this.settings.app_id);
			var _this = this;
			$("tr.section." + _this.settings.app_id + " td.all > input").each(function () {
				if (ul.find('input:enabled[value="' + $(this).attr('value') + '"]').not(":checked").length == 0) {
					$(this).attr('checked', 'checked');
				}
			})
			.click(function (e) {
				if ($(this).is(":checked")) {
					$(this).prevAll().attr('checked', 'checked');
					var inp = ul.find('input:enabled[value="' + $(this).attr('value') + '"]').attr("checked", "checked");
					inp.prevAll().attr('checked', 'checked');
					inp.parent().addClass('access').parent().addClass('access').prev().addClass('access');
					$.post("index.php?mod=" + (_this.settings.is_group ? "groups" : "users") + "&act=rights&ajax=1", 
							{action: 'save', 
							 id: right_id, 
							 application_id: _this.settings.app_id, 
							 section: 'FOLDERS', 
							 object_id: 'ALL', 
							 value: $(this).attr('value'),
							 max: 1}
					);	
					ul.find("div.hitarea.expandable-hitarea").click();
				} else {
					$(this).nextAll().attr('checked', "");
					var inp = ul.find('input:enabled[value="' + $(this).attr('value') + '"]').attr("checked", "");
					inp.nextAll().attr('checked', '');
					if ($(this).attr('value') > 0) {
						inp.each(function () {
							if (!$(this).parent().prev().hasClass('access')) {
								$(this).parent().removeClass('access').parent().removeClass('access').prev().removeClass('access');
							}
						});
						$.post("index.php?mod=" + (_this.settings.is_group ? "groups" : "users") + "&act=rights&ajax=1", 
								{action: 'save', 
								 id: right_id, 
								 application_id: _this.settings.app_id, 
								 section: 'FOLDERS', 
								 object_id: 'ALL',
								 value: ($(this).attr('value') >> 1)}
						);																
					}
				}
				for (var k = 0; k < _this.settings.folders.length; k++) {
						if (_this.settings.folders[k][4].length == 0) continue;
						_this.settings.checkHitarea(_this.settings.folders[k]);
				}				
			});			
			
		},
		attachFolderEvents : function (f) {
			var _this = this;			
			var li = $("#" + this.settings.app_id + f[0].replace(/\./g, "\\."));
			li.children('.folder').hover(function () {$(this).parent().children(".folder").addClass('hover')}, function () {$(this).parent().children(".folder").removeClass('hover')})
			if (f[4].length == 0) {
				li.removeClass('expandable').removeClass('collapsable');
				li.children('div.hitarea').hide();
			}					
			
			if (f[2] != 'ROOT') {
				if (f[3][0] > 0) {
					this.expandParents(li);
				}
			}
			if (!$.browser.msie || $.browser.version >= 7) {
				for (var j = 0; j < f[4].length; j++) {
					this.attachFolderEvents(f[4][j]);
				}
			}
			
		},
		expandParents: function (elem) {
			if (elem.parent().is(":hidden")) {
				var p = elem.parent().parent();
				p.children('div.hitarea').click().hide();
				p.removeClass('expandable');
				this.expandParents(p);
			}
		},
		
		destroy: function () {
			delete rights_tree[this.elem.attr("id")];
		}

	}
}

function hasAdmin(app_id)
{
	return app_id == 'CM' || app_id == 'PD' || app_id == 'AA' || app_id == 'UG'; 
}


function renderApps(apps, is_group) {
	$("#apps > table").remove();
	var t = $('<table class="rights" width="100%"></table>');
	if (!is_group) {
		t.append('<tr class="first"><td style="border:0; height: 28px">&nbsp;</td><td class="right" width="150">Персональные права доступа</td><td class="right" width="150">Унаследованные от групп</td></tr>');
	}
	for (var i = 0; i < apps.length; i++) {
		if (apps[i][0] == 'UG' && self_rights) {
			var select_html = 'администратор';
		} else {
			var select_html = '<select name="access' + apps[i][0] +  '">' +
			'<option value="0"' + (!apps[i][3][1] ? " selected" : "") + '>нет доступа</option>' +
			(hasAdmin(apps[i][0]) ? '<option value="7"' + (apps[i][3][1] == 7 ? " selected" : "") + '>администратор</option>' : '') +
			(apps[i][4].length >= 1 || apps[i][5].title ? '<option value="1"' + (apps[i][3][1] == 1 || (apps[i][3][1] && !hasAdmin(apps[i][0]))  ? " selected" : "") + '>настроить</option>' : '') + 
			'</select>';
		}
		t.append('<tr class="title' + (apps[i][3][0] ? ' access-title' : '' ) + '"><td class="title" >' + apps[i][1] + '</td>' + 
				'<td width="150" class="right" id="screen'+ apps[i][0] +'">' + select_html + '</td>' +
				(is_group ? '' : '<td width="150" class="right">' + 
				(apps[i][3][2] == 7 ? 'администратор' : (apps[i][3][2] == 1 ? 'настроить' : 'нет доступа')) + 
				'</td>') + '</tr>');
				
		if (apps[i][4].length == 0) continue;
		var rights = apps[i][4];
		for (var j = 0; j < rights.length; j++) {
			var section = rights[j];
			if (section[0] == 'QUOTA') {
				if (is_group) {
					continue;
				}
				t.append('<tr class="section ' + apps[i][0] + (!apps[i][3][0] || apps[i][3][0] == '7'  ? " dn" : "")+ '"><td class="title"><b>' + section[1] + '</b></td><td class="quota" style="position:relative;text-align:center"></td><td></td></tr>');
				var app_id = apps[i][0];
				var a = $('<a class="quota" href="#">' + (section[3] ? section[3] + ' KB' : 'неограниченно') + '</a>').click(function () {
					v = $(this).html().replace(/[^0-9]/gi, '');
					var div = $('<div class="quota-menu" style="width:' + ($(this).parent().width()-8) + 'px;background:#F2F6FA; margin-top: 3px; text-align: left; border: 2px solid #DAE8F2; padding: 3px; position:absolute"><input class="v" type="text" size="6" value="' + v + '" /> KB<br /></div>');
					div.append($('<input type="button" value="OK" />').click(function () {
						var d = $(this).parent();
						var v = d.children('input.v').val();
						$.post("?mod=users&act=rights&ajax=1", {
							action: 'save', 
							id: right_id, 
							application_id: app_id, 
							section: 'QUOTA', 
							value: v							
						}, function (response) {
							v = response.data.value;
							if (v) {
								v = v + ' KB';
							} else {
								v = 'неограниченно';
							}
							d.parent().children('a').html(v);
							d.remove();							
						}, "json");
					}));
					div.append($('<input type="button" value="Отмена" />').click(function () {
						$(this).parent().remove();
					}));					
					$(this).parent().append(div);
					div.find('input.v').select();
					return false;
				});
				t.find('.quota').append(a);
				$(document).click(function (e) {
					if ($(e.target).parent('.quota-menu').length == 0) {
						$(".quota-menu").remove();
					}
				});
			} else if (section[1]) {
				t.append('<tr class="section ' + apps[i][0] + (!apps[i][3][0] || (hasAdmin(apps[i][0]) && apps[i][3][0] == '7') ? " dn" : "")+ '"><td class="title" colspan="3"><b>' + section[1] + '</b></td></tr>');
			}
			for (var k = 0; k < section[2].length; k++) {				
				var input = $('<input type="checkbox" '+ (section[2][k][2][1] ? "checked" : "") + '/>');
				input.click(function () {
					$.post("index.php?mod=" + (is_group ? "groups" : "users") + "&act=rights&ajax=1", 
						 {action: 'save', 
						  id: right_id, 
						  application_id: this.app_id, 
						  section: this.section[0] , 
						  object_id: this.section[2][this.k][0], 
						  value: (this.input.is(":checked") ? (this.section[2][this.k][0] == 'ROOT' ? 7 : 1) : 0)},
						  function (reposnse) {
						  	if (!this.section[2][this.k][2][2]) {
							  	if (this.input.is(":checked")) {
							     	this.input.parent().addClass('access').prev().addClass('access');
							  	} else {
							  		this.input.parent().removeClass('access').prev().removeClass('access');	
							  	}
						  	}
							if ($("#onload-message.rr").length == 0) {
								var message = 'Все изменения применяются автоматически';
								$("#onload-message").addClass('rr').html(message).show().fadeOut(5000, function () {$(this).hide()});
							}						  	
						  }.bind(this),
						  "json"
					);
				}.bind({input: input, app_id: apps[i][0], section: section, k: k}));
				t.append($('<tr class="section ' + apps[i][0] + (!apps[i][3][0] || (hasAdmin(apps[i][0]) && apps[i][3][0] == '7') ? " dn" : "") + '"><td class="name ' + (section[2][k][2][0] ? "access" : "") + '">' + section[2][k][1] + '</td></tr>').append($('<td class="right user ' + (section[2][k][2][0] ? "access" : "") + '"></td>').append(input)).append(is_group ? '' : '<td width="150" class="right '+ (section[2][k][2][2] ? "access" : "") + '">' + 
						'<input type="checkbox" ' + (section[2][k][2][2] ? "checked" : "")  + ' disabled /></td>'));
			}

		}
		if (apps[i][5].title) {
			t.append('<tr class="section ' + apps[i][0] + (!apps[i][3][0] || apps[i][3][0] == '7' ? " dn" : "") + '"><td class="title comment"><b>' + apps[i][5].title + '</b>' + '</td><td align="center" width="150"><table width="150"><tr><td width="33%">Чтение</td><td width="34%">Запись</td><td width="33%">Полные</td></tr><tr><td colspan="3" class="all"><input type="checkbox" value="1" /><input type="checkbox" value="3" /><input type="checkbox" value="7" /></td></tr></table></td><td></td></tr>');
			t.append('<tr class="section ' + apps[i][0] + (!apps[i][3][0] || apps[i][3][0] == '7' ? " dn" : "") + '"><td class="folders" colspan=3 id="folders' + apps[i][0] + '"></td></tr>');
		}
	}
	$("#apps").append(t);
	for (var i = 0; i < apps.length; i++) {
		var settings = apps[i][5];
		settings.is_group = is_group;
		settings.app_id = apps[i][0];
		$("td#folders" + apps[i][0]).rightsTree(settings);
		(function () {
			var n = i;
			$("td#screen" + apps[n][0] + " > select").change(function () {
				$(this).hide();
				$(this).parent().append('<div class="loading"><img style="vertical-align:middle" width="16" height="16" src="' + WbsData.get('url.common') + 'templates/img/loading.gif" /> Загрузка...</div>');
				var r = $(this).val();
				
				if ($("#onload-message.rr").length == 0) {
					var message = 'Все изменения применяются автоматически';
					$("#onload-message").addClass('rr').html(message).show().fadeOut(5000, function () {$(this).hide()});
				}
				if (r == '0') {
					$.post("index.php?mod=" + (is_group ? "groups" : "users") + "&act=rights&ajax=1", {action: 'del', id: right_id, application_id: apps[n][0]}, function (response) {
						$("td#screen" + apps[n][0] + " > select").show();
						$("td#screen" + apps[n][0] + " .loading").remove();
						var i = this.i;
						apps[i][3][1] = 0;
						apps[i][3][0] = apps[i][3][2];
						$("td#screen" + apps[i][0]).parent().removeClass('access-title');
						var str = new Array();
						for (var j = 0; j < apps.length; j++) {
							if (apps[j][3][0]) {
								str.push(apps[j][1]);
							}
						}
						if (str.length > 0) {
							$("#access-note").hide();
							$("#user-apps").html(str.join(', '));												
						} else {
							$("#access-note").show();
							$("#user-apps").html('<span style="color:#666">нет</span>');
						}

						$("tr.section." + apps[i][0]).hide().find('td > input:enabled').attr('checked', "");
						$("tr.section." + apps[i][0] + ' > td.access').removeClass('access');
						$("td#screen" + apps[i][0]).removeClass('access').prev().removeClass('access');
						$("td#folders" + apps[i][0]).find('input:enabled').attr("checked", "");
						$("#tree" + apps[i][0]).find("div.hitarea.collapsable-hitarea").show().click();
					}.bind({i: n}), "json");
				} else {
					
					$.post("index.php?mod=" + (is_group ? "groups" : "users") + "&act=rights&ajax=1", {action: 'save', id: right_id, application_id: apps[n][0], section: 'SCREENS', object_id: apps[n][2], value: r}, function (response) {
						$("td#screen" + apps[n][0] + " > select").show();
						$("td#screen" + apps[n][0] + " .loading").remove();
						var i = this.i;
						apps[i][3][1] = r;
						apps[i][3][0] = apps[i][3][1] > apps[i][3][2] ? apps[i][3][1] : apps[i][3][2];
						$("td#screen" + apps[i][0]).parent().addClass('access-title');
						var str = new Array();
						for (var j = 0; j < apps.length; j++) {
							if (apps[j][3][0]) {
								str.push(apps[j][1]);
							}
						}
						if (str.length > 0) {
							$("#access-note").hide();
							$("#user-apps").html(str.join(', '));												
						} else {
							$("#access-note").show();
							$("#user-apps").html('<span style="color:#666">нет</span>');
						}

						if (r == '1') {
							$("tr.section." + apps[i][0]).removeClass('dn').show();
						} else {
							$("tr.section." + apps[i][0]).removeClass('dn').hide();
						}
						$("td#screen" + apps[i][0]).addClass('access').prev().addClass('access');
					}.bind({i : n}), "json");
				}
			});
		})();
	}
	$("#loading").hide();		
}
