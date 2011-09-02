var UGApplication = newClass(WbsApplication, {
	constructor: function(config) {

	this.config = config;
	this.right = config.right;
		this.page = this.config.page | 0;
		this.mode = "groups";
				
		this.currentFolder = new UGFolder();
		this.currentFolder.addListener("changed", function(folder) {this.folderChanged();}, this);
		this.currentFolder.addListener("modified", function(folder) {this.folderModified();}, this);
		this.currentFolder.addListener("deleted", function(folder) {this.folderDeleted();}, this);
	
		this.groupsList = new UGList({elemId: "groups-list", nodes: document.groupNodes}, this);
		
		var self = this;
		$("#groups a.select-group").click(function () {
			self.selectGroup(jQuery(this).attr('id'));
		});
		
		$("a.create-group").click(function () {
			$(this).addClass('selected');
			self.groupsList.unSelect();
			loadPage('index.php?mod=groups&act=add');
		});
		
		$("a#add-user").click(function () {
			self.openPopup({url: "index.php?mod=users&act=add"});
		});
		
		this.viewSettings = {};

		// Create navigation bar
		this.navBar = new WbsNavBar({id: "UG", saveSize: true,  contentElemId: "nav-bar", expanderElemId: "nav-bar-expander"});	
		this.store = this.createStore();

		// Page container (for center scrollable part)
		this.container = new WbsFlexContainer ({
			elem: document.getElementById("main-container"), 
			headerElem: document.getElementById("main-header"), 
			contentElem: document.getElementById("main-content")
		});

		// Create control panel
		this.controlPanel = new UGControlPanel(this, {});
		this.controlPanel.render();

		// Create table
		this.table = new UGTable({
			//id: "ug-table", 
			elem: document.getElementById("main-content"), 
			store: this.store, 
			pager: true, 
			selection: true, 
			sortingToCookie: true,
			sortItems: document.sortItems,
			defaultSorting: {column: "C_NAME", direction: "asc"}
		});
		this.table.render();
		this.controlPanel.setTableToControl(this.table);
		
		this.setViewSettings({itemsOnPage: this.config.itemsOnPage, viewmodeApplyTo: this.config.viewmodeApplyTo});
		
		this.superclass().constructor.call(this);
		this.init();
		jQuery('.records-page-count').click(function(e){
		     var menu = new ItemsOnPageMenu();
		     menu.show(e);
		     return false;
		});
		var _this = this;
		$("#viewmode-print").click(function () {
			url = '?mod=print';
			url += '&' + $.param(_this.store.params);
			url += '&offset=' + _this.store.offset;
			url += '&limit=' + _this.table.pager.itemsOnPage;			
			var w = window.open(url, 'print', 'width=700,height=400,top=0,left=0,location=no,menubar=yes,status=no,scrollbars=yes,resizable=yes');
			w.focus();
		});				
	},
	
	openPopup: 	function (settings) {
		var settings = jQuery.extend({
	       width: 600,
	       height: 'auto',
	       backgroundColor: '#000000',
	       opacity: 0.1,
	       loadComplite: function () {
				jQuery('#popup').wbsPopupRender();
				jQuery("#popup .close-btn").click(function () {
					jQuery("#popup").wbsPopupClose();
				});
		   }
		}, settings);
		jQuery('#popup').wbsPopup(settings);
	},
	
	getItemById: function (id, tree, nodeMap) {
		if (tree[nodeMap.id] == id) {
			return tree;
		}
		for (var i = 0; i < tree[nodeMap.children].length; i++) {
			var child = tree[nodeMap.children][i];
			var result = this.getItemById(id, child, nodeMap);
			if (result) {
				return result;
			}
		}
		return false;
	},		
		
	showInfoMessage: function (message) {
		$("#list_info div.info-message").remove();
		message = '<div class="info-message-close"><a href="javascript:void(0)" onclick="$(\'#list_info div.info-message\').remove(); document.app.resize()">Закрыть</a></div>' + message;
		$("#list_info").append('<div class="info-message with-close">' + message + '</div>');
	},
	
	init: function() {	
		this.resize();	
		this.table.pager.config.nameElements = 'Пользователи';
		if (parseInt(this.config.currentGroupId) == this.config.currentGroupId) {
			this.selectGroup(null);
			this.groupsList.selectNode(this.config.currentGroupId);
		} else {
			this.selectGroup(this.config.currentGroupId);
		}		
	},
	
	showMessage: function (message) {
		jQuery('#main-container').hide();
		var elem = jQuery('<div class="subframe" style="width:100%; height: 100%"></div>');
		elem.append('<div class="info-block">' + message + '</div>');
	  	jQuery("#screen-content-block").prepend(elem);
	  	jQuery('#main-container').show();
	},

	openSubframe: function(url, frame) {
		document.getElementById('main-container').style.display = 'none';
	  	this.closeSubframe();
	  	
	  	var elem = createElem(frame ? "iframe" : "div", "subframe");
	  	elem.style.width = "100%";
	  	elem.style.height = "100%";
	  	if (frame) {
	  	  	elem.frameBorder = "no";
	  	  	elem.setAttribute("SCROLLING", "NO");  		
	  	}
	  	jQuery("#screen-content-block").prepend(elem);
	  	
	  	this.startLoading();
	  	var _this = this;
	  	if (frame) {
	  		addHandler(elem, "load", this.finishLoading, this);
	  	  	if (Ext.isOpera && elem.location) {
	  	  		elem.location.href = url;
	  	  	} else {
	  	  		elem.src = url;
	  	  	}	  		
	  	} else {
			jQuery(elem).load(url, false, function (response) {
					try {
						var r = eval("(" + response + ")");
						if (r.errorCode == 'SESSION_TIMEOUT') {
							if (window && window.parent) {
								var d = window.parent.document;
							} else {
								var d = document;
							}
							d.location.href = r.redirectUrl;
						}
					} catch (e) {
						
					}
					_this.finishLoading();
					jQuery('#main-container').show();
			});
	  	}
	  	this.subFrame = elem;
	  },
	
	
	setViewSettings: function(settings) {
		jQuery.extend(this.viewSettings, settings);
		this.table.pager.setItemsOnPage(this.viewSettings.itemsOnPage);
		jQuery(".records-page-count").html(this.viewSettings.itemsOnPage);
	},
	
	getViewSettings: function() {
		return this.viewSettings;
	},
	
	resize: function() {
		this.container.resize();
		this.table.container.resize();
	},		

	setItemsCount: function (n) {
		this.viewSettings.itemsOnPage = n;
		this.table.pager.setItemsOnPage(this.viewSettings.itemsOnPage);
		jQuery(".records-page-count").html(n);
		jQuery.post("?mod=users&act=items&ajax=1", {n: n}, function (response) {}, "json");
		this.table.reloadView();
	},
	
	createStore: function() {
		// Create data reader
		var reader = new WbsReader ({
			url: "?mod=users&act=lists&ajax=1",
			baseParams: {},
			recordsProperty: 'users',
			totalProperty: 'total'
		});
	
		reader.addListener("success", function(responseData) {
			this.closeSubframe();
			if (responseData.viewMode)
				responseData.folder.viewMode = responseData.viewMode;

			if (responseData.itemsOnPage)
				responseData.folder.itemsOnPage = responseData.itemsOnPage;
			
			if (responseData.folder.ID == 'online') {
				jQuery("a#online span").html(responseData.total);
			}
			
			if (responseData.fields) {
				var showFields = responseData.fields;
				document.fields = new Array();
				for (var i = 0; i < showFields.length; i++) {
					document.fields[i] = {
							name: showFields[i][0],
							label: showFields[i][1],
							sorting: showFields[i][2] ? true : false,				
							type: showFields[i][3] ? showFields[i][3] : "string"
					};
				}		
			}
			this.table.columns = this.table.actualColumns();			
				
			this.currentFolder.load(responseData.folder);
			
			this.resize();
		}, 
		this);
		
		// Create data store
		var store = new WbsDataStore({reader: reader, idProperty: "C_ID", recordClass: UGUser});
		return store;
	},
		
	selectGroup: function(groupId) {
		jQuery('#main-container').show();
		this.currentFolder.reset();
		jQuery("#groups a.select-group").removeClass('selected');
		if (groupId != null) {
			if (parseInt(groupId) != groupId) {
				jQuery("a#" + groupId).addClass('selected');
				this.groupsList.unSelect();
			}
			this.table.resetPage(this.page);
			this.table.resetHighlightWord();
			this.store.setParams({mode: "groups", groupId: groupId});
			this.store.load ();
		}
	},

	folderChanged: function() {
		//this.closeSubframe();
		this.controlPanel.folderChanged();
	},
	
	folderModified: function() {
		this.groupsList.groupModified();
	},
	
	folderDeleted: function () {
		this.groupsList.groupDeleted();
	},
		
	getCurrentFolder: function() {
		return this.currentFolder;
	}
});

var UGList = newClass(WbsTree, {
	constructor: function(config, app) {
		this.app = app;
		var config = {elemId: config.elemId, iconCls: "user-group", nodes: config.nodes, rootVisible: false};
		this.superclass().constructor.call(this, config);
		
		this.nodeMap = {
			id : 0,
			text: 1,
			children: 2
		};
		
		this.init();
	},
		
	onNodeClick: function(node, e) {
		if (this.app.getCurrentFolder().Id != null) {
			this.app.getCurrentFolder().reset();
		}		
		
		jQuery("a.create-group").removeClass('selected');
		this.app.selectGroup(node.id, true);
	}, 
	
	groupModified: function() {
		var folder = this.app.currentFolder;
		var node = this.getNode(folder.Id);
		if (node)
			 node.setText(folder.Name);
	},	
	
	groupDeleted: function() {
		var folder = this.app.currentFolder;
		var node = this.getNode(folder.Id);
		var cur = node.previousSibling || node.nextSibling;
		if (node) {
			this.removeNode(node);
		}
		if (cur) {
			this.selectNode(cur.Id);
		} else {
			document.app.selectGroup('all');
		}
	},
		
	createGroup: function() {
		var node = this.getNode("ROOT");
		this.tryCreateGroup(node);
	},
	
	edit: function () {
		this.app.openSubframe("?mod=contacts&act=search&type=list&id=" + node.id);
	},
	
	addGroup: function (group) {
		jQuery("#groups a.create-group").removeClass('selected');
		var nodeData = {};
		nodeData[this.nodeMap.id] = group.id;
		nodeData[this.nodeMap.text] = group.name;
		nodeData[this.nodeMap.children] = null;
		var newNode = this.addNode(nodeData);
		this.rootNode.appendChild(newNode);
		this.selectNode(newNode.Id, true);
	}		
});

var UGTable = newClass(WbsTable, {
	constructor: function(config) {
		if (!config)
			config = {};
		config.nameElements  = "";
		config.columns = this.actualColumns();
		this.sortItems = config.sortItems;
		this.superclass().constructor.call(this, config);
	}, 
	
	folderChanged: function () {
	},
	
	actualColumns: function () {
		var columns = [{
			name: "IS_USER", 
			width: "20", 
			cls: "icon-user",
			label: "", 
			custom: true, 
			html: "<img src='../common/html/res/ext/resources/images/default/tree/user-group.gif' />", 
			sorting: false
		}];
		columns = columns.concat(document.fields);
		columns.push({
			name: "COLUMNS",
			width: "20",
			cls: "icon-custom-view",
			sorting: false
		});
		return columns;
	},
		
	setHighlightWord: function(word) {
		this.highlightWord = word;
	},
		
	resetHighlightWord: function() {
		this.highlightWord = null;
	},
	
	outputValue: function(value) {
		if (value == undefined) {
			return '';
		}
	  	if (!this.highlightWord)
	  		return value;
	  	var v = jQuery("<div>" + value + "</div>");
	  	if (value.indexOf('<span class="highlight">') != -1) {
	  		return value;
	  	}
	  	var regex = new RegExp("(" + this.highlightWord.replace(/\s/ig, "|") +")", "ig");
	  	var s = "";
	  	v.contents().each(function () {
	  		if (this.nodeType == 3) {
	  			s += jQuery(this).parent().html().replace(regex, '<span class="highlight">$1</span>');
	  		} else {
	  			jQuery(this).html(jQuery(this).html().replace(regex, '<span class="highlight">$1</span>'));
	  			s += jQuery("<div></div>").append(this).html();
	  		}
	  	});
	  	return s;
	},	
	
	render: function() {		
		this.superclass().render.call(this);	
	},
		
	resetPage: function(page) {
		page = page | 0;
		this.pager.resetPage(page);
	},
		
	createView: function (viewMode) {
	  	var config = {};
	  	switch (viewMode) {
	  		case "detail":
	  			config.iconType = "bigicons";
	  			return new UGListView(this, config);
	  			break;	
	  		case "tile":
	  			return new UGTileView(this, config);
	  			break;
	  		case "columns":
	  			return new UGColumnsView(this, config);
	  			break;
	  	}
	  	return null;
  },
  	
  updateItemBlock: function (block, record) {
  	this.superclass().updateItemBlock.call(this, block, record);
  	
  	// Find control icon
  	var sampObjects = block.getElementsByTagName("SAMP");
  	var controlIconPlace = false;
  	for (var i = 0; i < sampObjects.length; i++)
  		if (sampObjects[i].className=="control-icon")
  			controlIconPlace = sampObjects[i];
  	if (!controlIconPlace) 
  		return;
  	
  	var icon = createDiv("control-icon");
  	controlIconPlace.parentNode.replaceChild(icon, controlIconPlace);
  	addHandler(icon, "click", function(e) {record.showMenu(e)}, record );
  	
  },
  	
  createRecordsList: function() {
  	return new UGUsersList();
  },
  	
  getNoRecordsMessage: function() {
	return "<нет пользователей в этой группе>";
  },
  
  onLoad: function () {
  }
});

var UGUsersListMenu = newClass(WbsPopmenu, {
	constructor: function(recordsList) {
		this.recordsList = recordsList;
		var config = {};
		
		var disabled = this.recordsList.getCount() == 0;
		config.items = [];
		config.items = config.items.concat([
			{label: "Действия с выбранными пользователями (" + recordsList.getCount() + '):' , cls: "unactive"},
			"-",
			{label: "Закрыть доступ", disabled: disabled, onClick: recordsList.setDisabled.bind(recordsList)},
			{label: "Открыть доступ", disabled: disabled, onClick: recordsList.setEnabled.bind(recordsList)},
			"-",
			{label: "Удалить пользователей", disabled: disabled, onClick: recordsList.tryDelete.bind(recordsList)}
		]);
		this.group = document.app.getCurrentFolder();
		if (this.group.Id > 0) {
			config.items = config.items.concat([
			    {label: "Действия с группой:", cls: "unactive"},
				{label: "Переименовать", disabled: this.group.Id <= 0, onClick: this.group.showRename.bind(this.group)}, 
				{label: "Изменить состав и права доступа", disabled: this.group.Id <= 0, onClick: function() {document.app.openSubframe('index.php?mod=groups&act=edit&id='+this.group.Id, true);}},
				{label: "Удалить", disabled: this.group.Id <= 0, onClick: this.group.tryDelete.bind(this.group)}
			]);
		}
				
		config.withImages = true;
		
		this.superclass().constructor.call(this, config);
	}
});

var UGAddUserDlg = newClass(WbsDlg, {

	constructor: function(config) {
		jQuery("#dlg-content").load("?mod=users&act=add");
		this.superclass().constructor.call(this, config);
	}
});
