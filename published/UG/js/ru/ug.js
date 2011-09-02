function init () {
	var last_block = getCookie("last_block");
	var act_last_block = last_block;

	if (!last_block || jQuery("#" + last_block).length == 0) {
		last_block = jQuery("#nav-bar div.acc-block:first").attr('id');
	}
	jQuery("#" + last_block).show();
	if (document.app.navBar) {
		document.app.navBar.setActiveBlock(last_block, true);
	}
	if (document.app.navBarBlockActivated) {
		if (!act_last_block || act_last_block == 'null') act_last_block = last_block;
		document.app.navBarBlockActivated({id: act_last_block});
	}
	
	document.app.page = 0;
}

function openFrame(obj) {
	document.app.openSubframe($(obj).attr('href'), 1);
	return false;
}

function loadPage(url) {
	document.app.openSubframe(url);
}

function openFolder (id) {
	document.app.foldersTree.selectNode(id);
}

function openGroup (id) {
	document.app.groupsList.selectNode(id);	
}

var currentViewChanged = false;

var UGControlPanel = newClass(WbsObservable, {
	constructor: function(app, config) {
		this.app = app;
		this.config = config;
		this.contentEl = document.getElementById("control-panel");
		this.items = [];
		
		var titleWrapElem = createDiv("title-wrapper");
		document.getElementById("group-title-container").insertBefore(titleWrapElem, document.getElementById("group-title-container").firstChild);
		var titleElem = createDiv("title");
		titleWrapElem.appendChild(titleElem);
		
		this.titleControl = this.app.currentFolder.createTitleControl(titleElem);
			
		this.setTitleControl(this.titleControl);		
		
		this.viewmodeSelector =  this.createViewmodeSelector ();
		
		if (this.app.right.contacts) {
			var addContact =  new WbsMenuButton({
				el: "add-new-contact",
				url: "?mod=contacts&act=add&type=1",
				label: "Добавить новый контакт",
				getMenu: this.getAddContactMenu.bind(this)
			});
		}
		this.usersActionsBtn = new WbsMenuButton({el: "users-actions-btn", getMenu: this.getUsersMenu.bind(this)});
	
		this.folderInfoElem = createDiv("folder-info-block");
		document.getElementById("group-title-container").appendChild(this.folderInfoElem);
			
	},
	
	addItem: function(item) {
		this.items.push(item);		
	},
		
	
	createViewmodeSelector: function() {
		var viewModeSelector = new WbsViewmodeSelector(null, {elem: document.getElementById("viewmode-selector-wrapper"), modes: ["columns", "detail", "tile"]});

		viewModeSelector.addListener("viewmodeChanged", function(mode) {
			var elem = document.app.mode + ":" + document.app.getCurrentFolder().Id; 
			jQuery.post("?mod=users&act=viewmode&ajax=1", {elem: elem, mode: mode}, function (response) {
			}, "json");		
		});
		
		this.addItem(viewModeSelector);
		return viewModeSelector;
	},
		
	setTableToControl: function(table) {
		this.viewmodeSelector.setTable(table);
	},
		
	groupChanged: function() {
		var group = this.app.currentFolder;
		this.titleControl.setViewMode();
		this.titleControl.setValue(group.Name);
		if (group.Data && group.Data.viewMode) {
			this.viewmodeSelector.setMode(group.Data.viewMode);
		}
	},
	
	folderChanged: function() {
		var folder = this.app.currentFolder;
		this.titleControl.setViewMode();
		this.titleControl.setValue(folder.Name);
		
		if (folder.Data && folder.Data.viewMode) {
			this.viewmodeSelector.setMode(folder.Data.viewMode);
		}
	},	
	
	setTitleControl: function(c) {
		this.titleControl = c;
		this.activeTitleControl = c;
	},
		
	render: function() {
		for (var i = 0; i < this.items.length; i++) {
			this.items[i].render();
		}
		if(this.activeTitleControl) {
			this.activeTitleControl.render();
		}
	},
		
	getUsersMenu: function() {
		var selectedUsers = this.app.table.getSelectedRecords();
		return selectedUsers.getMenu();
	},
	getAddContactMenu: function() {
		return new UGAddContactMenu();
	},
	
	getGroupMenu: function() {
		return this.app.getCurrentFolder().getMenu();
	}	
});

var UGUser = newClass (WbsRecord, {
	constructor: function(recordData) {
		this.folder = document.app.getCurrentFolder();
		this.superclass().constructor.call(this, recordData);
	},
	getFields: function() {
		var fields = [
		    {name: "U_ID"},
		    {name: "U_STATUS"},
		    {name: "C_ID"},
		    {name: "CT_ID"},
		    {name: "ENC_ID"},
		    {name: "C_NAME"}
		];
		
		for (var i in document.dbfields) {
			
			fields.push({name: i});	
		}
		return fields;
	},

	showMenu: function(e, type) {
		var menu = new UGUsersMenu(this, type);
		menu.show(e);
	},

	canWrite: function() {
		return this.folder.canWrite();
	}
});

var UGColumnsView = newClass(WbsColumnsView, {

	constructor: function(table, config) {
		this.superclass().constructor.call(this, table, config);
		this.superclass = function() {return WbsColumnsView.prototype; }
	},


	getCellHeaderValueElem: function(column) {
		if (column.name == "IS_USER") {
			var icon = createDiv("icon");
			icon.title = "User status";
			return icon;
		} else if (column.name == "COLUMNS") {
			var icon = $('<div id="view-settings-btn" class="' + column.cls + '" title="Настроить колонки..."></div>')
			jQuery(icon).click(function (e) {
				var viewSettingsWindow = new UGViewSettingsPopwindow({el: document.getElementById("view-settings-btn"), closeMode: "hide"});
				viewSettingsWindow.setViewSettings(document.app.getViewSettings());
				viewSettingsWindow.addListener("currentViewChanged", function() {
					document.app.table.columns = document.app.table.actualColumns();
					document.app.table.reloadView();
				});
				viewSettingsWindow.show(e);
			});
			return icon.get(0);
		}
		return this.superclass().getCellHeaderValueElem.call(this, column);
	},

		
	getCellValue: function(column, record) {

		if (column.name == "COLUMNS") 
			return "";
		if (column.name == "IS_USER") {
			if (record.U_ID) {

				if (record.U_STATUS == 3) {
					return '<img src="../UG/img/icon-invite.gif" width="16" />';
				}
				else if (record.U_STATUS == 2) {
					return '<img src="../UG/img/icon-inactive.gif" width="16" />';
				} else {
					return '<img src="../UG/img/icon-user.gif" width="16" />';
				}
			} else {
				return '<img src="../common/html/res/images/s.gif" width="15" />';
			}
		}
		if (column.type == "IMAGE") {
			if (record[column.name] == "") {
				if (column.name == document.photoField[record.CT_ID]) {
					var img = '../UG/img/empty-contact' + record.CT_ID + '.gif';
				} else {
					return '<div class="emptyimg">' + 'нет изображения' + '</div>';
				}
			} else if (record[column.name] == null) {
				return "";
			} else { 
				var img = record[column.name] + '&size=96';
			}
			return '<img src="' + img + '" />';
		}

		record[column.name] = this.table.outputValue(record[column.name], column.name == 'C_NAME');
		
		if (column.name == "U_ID") {
			if (record.U_STATUS == 3) {
				return '<i style="color:#666; font-size:80%">приглашён</i>';
			} else {
				return record[column.name];
			}
		}
		if (column.name == "C_NAME") {
			return '<a href="?mod=users&C_ID='+ record.ENC_ID + '&mode=' + document.app.mode + "&id="  + document.app.getCurrentFolder().Id + '" onclick="return openFrame(this)">' + record[column.name] + '</a>';
		} 
		
		if (column.type == "URL") {
			if (record[column.name]) {
				return '<a target="_blank" href="' + record[column.name] + '">' + record[column.name] + '</a>'; 
			}
		}
		
		if (column.name == "C_EMAILADDRESS") {
			if (record.C_EMAILADDRESS) {
				var es = record.C_EMAILADDRESS.split(', ');
				var res = new Array();
				for (var k = 0; k < es.length; k++) {
					var e = es[k];
					var em = e.replace(/<span\sclass="highlight">([^<]*)<\/span>/gi, "$1");
					res.push('<a onclick="return openFrame(this)" href="?mod=users&act=email&mode=' + document.app.mode + '&id=' + record.C_ID + '&email=' + em + '">' + e + '</a>');
				}
				return res.join(', ');
			}
		}

		return this.superclass().getCellValue.call(this, column, record) ;
	}	
	});


var UGListView = newClass (WbsListView, {
	constructor: function (table, config) {
		config.header = true;
		this.superclass().constructor.call(this, table, config); 
	},

	getClassName: function() {
		if (this.config.iconType)
			return this.modeName + " " + this.config.iconType;
		return this.modeName;
	},

	getThumbnailHtml: function (record) {
		var photo = document.photoField[record['CT_ID']];
		if (!photo) {
			return false;
		}
		var src = record[photo] == '' ? "img/empty-contact" + record.CT_ID + ".gif" : record[photo] + "&size=96";
		return "<div onClick='this.parentNode.click()' class='thumbnail'><table><tr><td>" + "<img src='" + src + "'>" + "</td></tr></table></div>";
	},

	buildRecordBlock: function(block, record) {
		
		record.EDIT_URL = '?mod=users&C_ID='+ record.ENC_ID + '&mode=' + document.app.mode + "&id="  + document.app.getCurrentFolder().Id +'&p='+ (document.app.table.pager.currentPage);
		var resHTML = "";
		
		resHTML += "<div class='controls'><SAMP class='selector'></SAMP></div>";
		var html = this.getThumbnailHtml(record);
		if (html) {
			resHTML += "<a class='wrap-image' href='" + record.EDIT_URL + "' onclick='return openFrame(this)'>" + html + "</a>";
		}
		resHTML += "<div class='content' " + (!html ? 'style="margin-left:25px"' : '') + ">";
			resHTML += "<div class='name'>" + "<a href='" + record.EDIT_URL + "' onclick='return openFrame(this)'>" + this.table.outputValue(record.C_NAME, true) + "</a>" + "</div>";
			resHTML += "<SAMP id='desc'></SAMP>";
			resHTML += "<div class='small-gray'>";
			for (var i = 0; i < document.listfields[record.CT_ID].length; i++) {
				var section = document.listfields[record.CT_ID][i];		
				var section_content = "";
				for (var j = 0; j < section.fields.length; j++) {
					if (record[section.fields[j][0]]) {
						if (section_content.length > 0) {
							section_content += ', ';
						}
						var v = record[section.fields[j][0]];
						if (section.fields[j][0] == 'C_EMAILADDRESS') {
							var es = v.split(', ');
							var res = new Array();
							for (var k = 0; k < es.length; k++) {
								var e = es[k];
								res.push('<a onclick="return openFrame(this)" href="?mod=users&act=email&mode=' + document.app.mode + '&id=' + record.C_ID + '&email=' + e + '">' + e + '</a>');
							}
							v = res.join(', ');
						} else if (document.dbfields[section.fields[j][0]] == 'URL') {
							v = '<a target="_blank" href="' + v + '">' + v + '</a>'; 
						}
						section_content += '<span class="field_title" title="' + section.fields[j][1] + '">' + v + '</span>';
					}
				}
				if (section_content.length > 0) {
					resHTML += '<span class="section_title">' + section.name + ':</span>' + section_content + '<br />';
				}
			}
			resHTML += "</div>";
			
		resHTML += "</div>";
		
		block.innerHTML = resHTML;
		
	}
});


var UGTileView = newClass(WbsTileView, {

	constructor: function(table, config) {
		config.header = true;
		this.superclass().constructor.call(this, table, config); 
	},

	getThumbnailHtml: function (record) {
		var photo = document.photoField[record['CT_ID']];
		var src = record[photo] == '' ? "img/empty-contact" + record.CT_ID + ".gif" : record[photo] + "&size=96"; 
		return "<div onClick='this.parentNode.click()' class='thumbnail'><table><tr><td>" + (photo ? "<img src='" + src + "' />" : '') + "</td></tr></table></div>";
	},
	
	buildRecordBlock: function(block, record) {
		var resHTML = "";
		
		var controlsHTML = "";
		if (record.CT_ID == "1") {
			controlsHTML += record.C_COMPANY;
		}
			
		record.EDIT_URL = '?mod=users&C_ID='+ record.ENC_ID + '&mode=' + document.app.mode + "&id="  + document.app.getCurrentFolder().Id +'&p='+ (document.app.table.pager.currentPage);
		
		resHTML += "<br style='display: none'/>"; // IE bugfix
		resHTML += "<a style='margin-bottom: 0px' class='wrap-image' href='" + record.EDIT_URL + "' onclick='return openFrame(this)'>" + this.getThumbnailHtml(record) + "</a>";
		resHTML += "<div class='content' style='margin: 0px; position:relative'>";
			var title = this.table.outputValue(record.C_NAME, true);
			//title = title.replace(/<\/?(?!\!)[^>]*>/gi, ''); 
			resHTML += "<SAMP class='selector'></SAMP><div class='name'>" + "<a href='" + record.EDIT_URL + "' title='" + title + "'>" + title + "</a>" + "</div>";
			//resHTML += "<div class='controls'><SAMP class='control-icon'></SAMP>" + controlsHTML + "</div>" ;
		resHTML += "</div>";
			
		block.innerHTML = resHTML;
	}
});


var UGViewSettingsPopwindow = newClass(WbsPopwindow, {
	constructor: function() {
		var config = {
			width: 555, 
			height: 280,
			cls: "view-settings-window"	
		};
		this.viewSettings = null;
		this.superclass().constructor.call(this, config);
		
		this.addEvents({
			currentViewChanged : true
		});
	},
		
	render: function() {
		this.buildVisibleFields();		
	
		var elem = this.createFieldBlock("buttons");
		
		var saveBtn = createElem("input", null, {type:"button", value: "Сохранить"});
		saveBtn.onclick = this.save.bind(this);
		elem.appendChild(saveBtn);
		
		var cancelBtn = createElem("input", null, {type:"button", value: "Отмена"});
		cancelBtn.onclick = this.cancel.bind(this);
		elem.appendChild(cancelBtn);
	},
		
	save: function() {
		var _this = this;
		var f = new Array();
		$("#in option").each(function () {
			//if (jQuery(this).val() > 0) {
				f.push(jQuery(this).val());
			//}
		});
		var params = {save: 1, "fields[]": f, mode: document.app.mode};
		if ($("#apply-to-current").is(":checked")) {
			params.elem = document.app.getCurrentFolder().Id;
		}
		$.post("?mod=users&act=viewsettings&ajax=1",
			params,
			function (response) {
				if (response.status == "OK") {
					var showFields = response.data.fields;
					document.fields = new Array();
					for (var i = 0; i < showFields.length; i++) {
						document.fields[i] = {
								name: showFields[i][0],
								label: showFields[i][1],
								sorting: showFields[i][2] ? true : false,				
								type: showFields[i][3] ? showFields[i][3] : "string"
						};
						if (i == 0) {
							document.fields[i].sortingMenu = true;
						}
					}		
				}
				_this.fireEvent("currentViewChanged");
				_this.close();
		}, "json");
	},
		
	cancel: function() {
		this.close();		
	},
		
	onAfterShow: function() {
	},
	
	compareArray: function (a1, s2) {
		if (a1.length() != a2.length()) {
			return false;
		}
		var v;
		v = true;
		for (var i = 0; i < a1.length(); i++) {
			v = v | (a1[i] != a2[i]);
		}
		return v;
	},
		
	createFieldBlock: function(cls) {
		var elem = this.getInnerElem();
		var field = createDiv("field");
		if (cls) {
			addClass(field, cls);
		}
		elem.appendChild(field);
		return field;
	},
		
	setViewSettings: function(viewSettings) {
		this.viewSettings	= viewSettings;
	},
		
	
	buildVisibleFields: function(elem) {
		var elem = this.createFieldBlock("visible-fields");
		var f = new Array();
		for (var i = 0; i < document.fields.length; i++) {
			f.push(document.fields[i].name);
		}
		f = f.join(',');
		jQuery(elem).width("100%").css('text-align', 'center').append('Loading... <img src="../common/img/loading.gif" />').load("?mod=users&act=viewsettings&mode=" + document.app.mode + "&elem=" + document.app.getCurrentFolder().Id+"&f=" + f);
	}	
});

var ItemsOnPageMenu = newClass (WbsPopmenu, {
	constructor: function(obj) {
		var config = {};
		config.items = new Array();
		for (var i = 30; i <= 70; i = i + 10) {
			config.items.push({label: i, onClick: function () {document.app.setItemsCount(this.n)}.bind({n:i})});	
		}
	            
		this.superclass().constructor.call(this, config);
	}
});


var UGFolder = newClass(WbsObservable, {
	constructor: function() {
		this.addEvents({"changed" : true, "modified" : true});
		this.reset();
	},
		
	reset: function() {
		$("div.contacts-info").hide();
		this.Name = "Загрузка...";
		this.Id = null;
		this.Rights = 0;
		this.fireEvent("changed", this);
	},
	
	load: function(data) {
		var oldId = this.Id;
		var oldName = this.Name; 

		this.Data = data;
		this.Name = data.NAME;
		this.Id = data.ID;
		this.Rights = data.RIGHTS;

		if (oldId != this.Id) {
			this.fireEvent("changed", this);
		} else if (oldName && oldName != this.Name) {
			this.fireEvent("modified", this);
		}
		if (this.Rights == 7 || (document.app.mode == 'groups' && this.Id == parseInt(this.Id))) {
			this.titleControl.setEditable(true);
		} else {
			this.titleControl.setEditable(false);
		}
	
		if (this.Rights || document.app.mode == 'search' || document.app.mode == 'groups') {
			$("div.contacts-info").show();
		} else {
			$("div.contacts-info").hide();
		}
	},
		
	renameFolder: function (newValue, onSuccess, onFail) {
		this.Name = newValue;
		this.titleControl.setValue(newValue);
		if (onSuccess) {
			onSuccess();
		}
		this.fireEvent("modified", this);			
	}, 
		
	editList: function () {
		if (this.Data.SEARCH) {
			document.app.mode = "search";
			document.app.openSubframe("?mod=contacts&act=search&type=list&id=" + this.Id +"&list=1");			
		} else {
			document.app.openSubframe("?mod=lists&act=edit&id=" + this.Id);
		}
	},

	doCopyMove: function(folderId, action, callback) {
		if (action != "move") {
			throw "Error action: " + action;
		}
		jQuery.post("index.php?mod=folders&act=move&ajax=1",
			{from: this.Id, to: folderId, action: action},
			function (response) {
				if (response.status == "OK") {
					if (callback) {
						callback(response.data);
					}
				} else {
					alert(response.error);
				}		
			}, "json"
		);
	},

	getRights: function() {
		return this.Rights;
	},
	
	canWrite: function() {
		return true;
	},
	
	showRename: function() {
		this.titleControl.setEditMode();
	},
		
	createTitleControl: function(elem) {
		var titleControl = new WbsEditableLabel({elem: elem, clickToEdit: false});
		
		titleControl.addListener("changeMode", function() {
			if (document.app) {
				document.app.resize();
			}
		}, this);
	  
	  titleControl.saveHandler = function(newValue, saveSuccessHandler, saveFailedHandler) {
			this.rename(newValue, saveSuccessHandler, saveFailedHandler);
		}.bind(this);
		this.titleControl = titleControl;
	  return titleControl;
	}, 
	
	rename: function (newValue, onSuccess, onFail) {
		Ext.Ajax.request ({
			url: "?mod="+ document.app.mode +"&act=rename&ajax=1",
			params: {id: this.Id, newName: newValue},
			success: function (response) {
				var result = Ext.decode(response.responseText);
				if (result.status == 'OK') {
					this.Name = newValue;
					if (onSuccess) onSuccess();
					this.fireEvent("modified", this);
				} else if (result.status = 'ERR') {
					if (onFail) onFail(result.error);
				}		
			}.bind(this)
		});	
	},
	
	tryDelete: function(onSuccess, onFail) {
		var str = "Вы уверены, что хотите удалить \"%s\"?".replace("%s", this.Name);
		if (document.app.mode == 'folders' && this.Id == 'PUBLIC') {
			str += '\r\n\r\nПРИМ.: "Общая" — это специальная папка, доступная всем пользователям. Если вы сейчас удалите "Общую", то сможете потом создать ее вновь.';
		}
		if (document.app.mode == 'lists') {
			str += "\r\n\r\nПРИМ.: Контакты не будут удалены.";
		}
		if (document.app.mode == 'groups') {
			str += "\r\n\r\nПРИМ.: Пользователи не будут удалены.";
		}
		if (!confirm(str)) 
			return false;
		
	  	var folder_id = this.Id;
	  	this.Name = "Загрузка...";
	  	document.app.startLoading();
	  	document.app.table.view.showLoading();
		$.post("?mod=" + document.app.mode + "&act=delete&ajax=1", {id: folder_id}, function (response) {
			document.app.finishLoading();
			if (response.status == 'OK') {
				this.fireEvent("deleted", this);
			} else if (response.status == 'ERR') {
				document.app.selectFolder(folder_id);
				$("#list_info div.info-message").remove();
				var message = '<div class="info-message-close"><a href="javascript:void(0)" onclick="jQuery(\'#list_info div.info-message\').remove(); document.app.resize()">Закрыть</a></div>' + response.error;
				$("#list_info").append('<div class="info-message with-close">' + message + '</div>');
			}
		}.bind(this), "json");
	},
	
	showMoveDlg: function() {
		this.showDlg("move");
	},

	showDlg: function(action) {
		var title = "Переместить папку";
		var description = "Выберите новую родительскую папку";
		var dlg = new UGCopyMoveDlg({
			selectedFolderId: this.Id, action: action, actionObject: this,
			contentElemId: "dlg-move-content", title: title, height: 340, width: 360, description: description
		});
		dlg.show();
	}	

});

var UGUsersList = newClass (WbsRecordset, {
	constructor: function(records){
		this.superclass().constructor.call(this, records);
		this.app = document.app;
	},
	
	getMenu: function() {
		if (document.app.mode == 'groups') {
			return new UGUsersListMenu(this);
		} else {
			return new UGContactsListMenu(this);
		}
	},
		
	showAddToGroupDlg: function() {
		this.showDlg("add_to_group");		
	},
		
	showChangeStatusDlg: function() {
		this.showDlg("change_status");
	},
	
	showMoveToFolderDlg: function() {
		this.showDlg("move_to_folder");
	},
		
	showAddToListDlg: function() {
		this.showDlg("add_to_list");
	},
	
	setDisabled: function () {
		this.setStatus(2);
	},
	
	setEnabled: function () {
		this.setStatus(0);
	},
	
	setStatus: function (status) {
		if (this.app.mode != 'groups') {
			return false;
		}
		$.post("?mod=users&act=disable&ajax=1", {"ids[]": this.getIds(), status: status}, function (response) {
			if (response.status == 'OK') {
				document.app.store.load();
				if (status == 2) {
					var message = "Доступ был закрыт для %s пользователей";
				} else {
					var message = "Доступ был открыт для %s пользователей";
				}
				message = message.replace("%s", response.data.users);
				document.app.showInfoMessage(message);
			}
		}, "json");
	},

	tryDelete: function() {
		if (this.app.mode == "groups") {
			var message = "%s пользователей будут удалены. Вы уверены?";
		} else {
			var message = "Вы уверены что хотите удалить %s контактов?";
		}
		if (!confirm(message.replace("%s", this.getCount())))
			return false;
		jQuery.post("?mod=users&act=delete&ajax=1", {"ids[]": this.getIds()}, function (response) {
			if (response.status == 'OK') {
				document.app.store.load();
				var message = '';
				if (response.data.deleted) {
					if (document.app.mode == 'groups') {
						message += response.data.deleted + ' пользователей были успешно удалены<br />';
					} else {
						message += response.data.deleted + ' контактов были успешно удалены<br />';
					}
				}
				if (response.data.users) {
					message += response.data.users + ' контакты не были удалены, потому что у них есть аккаунты пользователей<br />';
				}
				if (response.data.noaccess) {
					message += response.data.noaccess + ' контакты не были удалены: недостаточно прав<br />' 
				}
				if (response.data.self) {
					message += response.data.self + " не может быть удален. Вы не можете удалить себя!";
				}
				$("#list_info div.info-message").remove();
				$("#list_info").append('<div class="info-message">' + message + '</div>');

				$("#list_info div.info-message").fadeIn("slow", function () {
					jQuery("#list_info div.info-message").fadeOut(5000, function () {jQuery(this).remove()});
				});
				
			} else if (response.status == 'ERR') {
				alert(response.error);
			}
		}, "json");
	},
	
	exportContactsDlg: function () {
//		location.href = "?mod=users&act=csv&export=1&user_ids=" + this.getIds().join(',')+"&group_id=" + this.app.getCurrentFolder().Id;

		var title = "Экспорт контактов";
		var dlg = new UGExportContactsDlg({
			selectedFolderId: this.app.getCurrentFolder().Id,
			actionObject: this,
			contentElemId: "dlg-export-content", title: title, height: 400, width: 700
		});
		dlg.show();

	},

	sendEmailScreen: function () {
		document.app.openSubframe("?mod=users&act=email&mode=" + document.app.mode + "&user_ids=" + this.getIds().join(','), 1);
	},

	sendsmsDlg: function () {

		var title = "Отправить SMS";
		var dlg = new UGSendsmsDlg({
			selectedFolderId: this.app.getCurrentFolder().Id,
			actionObject: this,
			contentElemId: "dlg-sendsms-content", 
			title: title, 
			height: 410, 
			width: 600
		});
		dlg.show();
	},

	showDlg: function(action) {
		switch(action) {
			case "move_to_folder":
				var title = "Переместить контакты";
				var description = "Выберите папку";
				var dlg = new UGCopyMoveDlg({
					systemFolders: true,
					selectedFolderId: this.app.getCurrentFolder().Id,
					action: action,
					actionObject: this,
					contentElemId: "dlg-move-content", title: title, height: 340, width: 360, description: description
				});
				dlg.show();
				break;
			case "add_to_list":
				var title = "Добавить контакты в список";
				var description = "Выберите список";
				var dlg = new UGAddToListDlg({
					action: action,
					actionObject: this,
					contentElemId: "dlg-move-content", title: title, height: 'auto', width: 360, description: description
				});
				dlg.show();
				break;
		}
	},
	
	doCopyMove: function(folderId, action, callback) {
		if (action != "move_to_folder") {
			throw "Error action: " + action;
		}
		jQuery.post("index.php?mod=contacts&act=move&ajax=1",
			{folderId: folderId, action: action, "contacts[]": this.getIds()},
			function (response) {
				if (response.status == 'OK') {
					if (callback) {
						callback();
					}
					var message = '';
					if(response.data['success']) {
						message = response.data['success'] + ' контакты были перемещены в папку "' + response.data['folderName'] + '"<br />';
					}
					if(response.data['errors']) {
						message += response.data['errors'] + ' контакты не были перемещены: недостаточно прав';
						jQuery("#list_info div.info-message").remove();
						message = '<div class="info-message-close"><a href="javascript:void(0)" onclick="' +
							'jQuery(\'#list_info div.info-message\').remove(); document.app.resize()">Закрыть</a></div>' + message;
						jQuery("#list_info").append('<div class="info-message with-close">' + message + '</div>');
					} else {
						jQuery("#list_info").append('<div style="display:none" class="info-message">' + message + '</div>');
						jQuery("#list_info div.info-message").fadeIn("slow", function () {
							jQuery("#list_info div.info-message").fadeOut(3000, function () {
								jQuery(this).remove();
							});
						});
					}
				} else {
					alert(response.error);
				}		
			}, "json"
		);
	},

	doSendsms: function(callback) {

		if (!jQuery("#To").val().replace(/^\s*(.*?)\s*$/,"$1")) {
			jQuery('#error_block').html("Введите номер телефона");
			jQuery('#error_block').show();
			jQuery('#To').focus();
		} else if (!jQuery("#message").val().replace(/^\s*(.*?)\s*$/,"$1")) {
			jQuery('#error_block').html("Пожалуйста, введите текст сообщения");
			jQuery('#error_block').show();
			jQuery('#message').focus();
		} else {
			jQuery('#loading-block', parent.document).css('visibility', '');
			jQuery.post("?mod=contacts&act=sendsms&mode=" + document.app.mode,
				{"To": jQuery("#To").val() + "", "message": jQuery("#message").val() + ""},
				function (response) {
					jQuery('#loading-block', parent.document).css('visibility', 'hidden');
					try {
						var r = eval("(" + response + ")");
						if (r.Error) {
							jQuery('#error_block').html(r.Error);
							jQuery('#error_block').show();
						} else {
							if (callback) {
								callback();
							}
							jQuery("#list_info").append('<div style="display:none" class="info-message">SMS было отправлено ' +
								r.Count + ' получателей</div>');
							jQuery("#list_info div.info-message").fadeIn("slow", function () {
								jQuery("#list_info div.info-message").fadeOut(3000, function () {jQuery(this).remove()});
							});
						}
					} catch (e) {
						jQuery('#error_block').html(response);
						jQuery('#error_block').show();
					}
				}
			);
		}
	},
	excludeFromList: function () {
		var list_id = this.app.getCurrentFolder().Id;
		$.post("?mod=contacts&act=exclude&ajax=1", {list_id: list_id, "contacts[]":this.getIds()}, function (response) {
			if (response.status == 'OK') {
				var n = response.data.contacts;
				if (n) {
					document.app.store.load();
					var message = n + ' контактов были исключены из этого списка';
					document.app.showInfoMessage(message, true);
				}
			} else if (response.status == 'ERR') {
				alert(response.error);
			}
		}, "json");
	},

	doAddToList: function(listId, listName, callback) {
		var n = this.getIds().length;
		jQuery.post("index.php?mod=contacts&act=addtolist&ajax=1",
			{listId: listId, listName: listName, "contacts[]": this.getIds()},
			function (response) {
				if (response.status == "OK") {
					if (callback) {
						callback();
					}
					var list = response.data.list;
					var listName = list.name;
					if (response.data.add) {
						document.app.listsList.addList(list, false, true);
					}
					$("#list_info").append('<div style="display:none" class="info-message">' + 
						n + ' контакты были добавлены в список "' + listName + '"</div>');
					$("#list_info div.info-message").fadeIn("slow", function () {
						$("#list_info div.info-message").fadeOut(3000, function () {jQuery(this).remove()});
					});
				} else {
					alert(response.error);
				}		
			}, "json"
		);
	},

	getRights: function() {
		return document.app.getCurrentFolder().getRights();
	}
});

var UGShowErrorDlg = newClass(WbsDlg, {
	constructor: function(config) {
		config.title = 'Ошибка';
		config.buttons = [
			{label: "Ok", onClick: this.close, scope: this}
		];
		config.height = 'auto';
		config.width = 600;
		this.superclass().constructor.call(this, config);
	},

	buildContent: function(contentElem) {
		contentElem.innerHTML = '<div style="padding: 10px 0">' + this.config.error + '</div>';
	}	
});


