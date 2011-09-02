Toogles = {
	img_open: '../UG/img/darr.gif',
	img_close: '../UG/img/rarr.gif',
	image_xpath: 'img',
	isClose: function(id) {
		var toogleValue = getCookie('UG_toogle_value') || '';
		return new RegExp('.*'+id+'.*', "g").test(toogleValue);
	},
	imageOpen: function(idButton) {
		$(idButton).find(Toogles.image_xpath).attr('src', Toogles.img_open);
	},
	imageClose: function(idButton) {
		$(idButton).find(Toogles.image_xpath).attr('src', Toogles.img_close);
	},
	addToogleValue: function(idButton, idBlock) {
		var reg =new RegExp('.*'+idBlock+'.*', "g")
		var toogleValue = getCookie('UG_toogle_value') || '';
		if ( !reg.test(toogleValue) )
			toogleValue += ','+idBlock;
		setCookie('UG_toogle_value', toogleValue);
		Toogles.imageClose(idButton);
	},
	delToogleValue: function(idButton, idBlock) {
		var reg =new RegExp('(.*),'+idBlock+'(.*)', "g")
		var toogleValue = getCookie('UG_toogle_value') || '';
		toogleValue = toogleValue.replace(reg, "$1$2");
		setCookie('UG_toogle_value', toogleValue);
		Toogles.imageOpen(idButton);
	},
	openBlock: function (idButton, idBlock) {
		if ( $(idBlock).is(":hidden") ) {
			$(idBlock).show();
			Toogles.delToogleValue(idButton, idBlock);
		}		
	},
	init_toogle: function (idButton, idBlock) {
		$(idButton).click(function(){
			if ( $(idBlock).is(":hidden") ) {
				$(idBlock).show();
				Toogles.delToogleValue(idButton, idBlock);
			}
			else {
				$(idBlock).hide();
				Toogles.addToogleValue(idButton, idBlock);
			}
		});
		if ( this.isClose(idBlock) ) {
			$(idBlock).hide();
			Toogles.imageClose(idButton);
		}
		else {
			Toogles.imageOpen(idButton);
		}
	}
}

function backToAnalytics()
{
	if (document.app.analyticsUrl) {
		document.app.openSubframe(document.app.analyticsUrl);		
	} else {
		document.app.openSubframe('?mod=analytics');
	}	
}

var UGApplication = newClass(WbsApplication, {
	constructor: function(config) {

	this.config = config;
	this.right = config.right;
		this.page = this.config.page | 0;
		this.mode = "folders";
		this.search_type = "";
				
		this.currentFolder = new UGFolder();
		this.currentFolder.addListener("changed", function(folder) {this.folderChanged();}, this);
		this.currentFolder.addListener("modified", function(folder) {this.folderModified();}, this);
		this.currentFolder.addListener("deleted", function(folder) {this.folderDeleted();}, this);
	
		this.listsList = new UGListList({elemId: "lists-list", nodes: document.listNodes}, this);
		this.newListBtn = this.createNewListBtn();

		if (this.right.admin) {
			this.widgetsList = new UGWidgetList({elemId: "widgets-list", nodes: document.widgetNodes}, this);
			this.newWidgetBtn = this.createNewWidgetBtn();
		}
		this.foldersTree = new UGFolderList({elemId: "folders-list", nodes: document.folderNodes}, this);
		this.newFolderBtn = this.createNewFolderBtn();
		this.newFolderBtn.setDisabled(!this.foldersTree.rightsCreateFolder);		
		
		var self = this;
		$("#folders a.select-folder").click(function () {
			self.selectFolder($(this).attr('id').replace(/-CONTACTS/, ''));
			return false;
		});		
		
		this.viewSettings = {};

		// Create navigation bar
		this.navBar = new WbsNavBar({id: "UG", saveSize: true,  contentElemId: "nav-bar", expanderElemId: "nav-bar-expander"});	
		this.navBar.addListener("blockActivated", this.navBarBlockActivated, this);
			
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
		
		this.superclass().constructor.call(this);
		this.init();
		$('.records-page-count').click(function(e){
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
	
	getItemById: function (id, tree, nodeMap) {
		if (tree[nodeMap.id] == id) {
			return tree;
		}
		if (!tree[nodeMap.children]) {
			return false;
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
		
	init: function() {
		this.selectFolder(null);
		this.resize();
	},
	
	searchByName: function (string) {
		  if (string.length == 0) {
			  return false;
		  }
		  document.app.setSearchType('simple');
		  document.app.doSearch(string, 1);
		  $("#list_info").empty();
		  document.createList = function () {
			document.app.doSearch(string, -1);
		  };		  
	},
		
	setSearchType: function(type, params, load) {
		$("#folders a.select-folder").removeClass('selected');
		if (this.foldersTree) this.foldersTree.unSelect();
		if (this.listsList) this.listsList.unSelect();
		if (this.widgetsList) this.widgetsList.unSelect();			
		
		this.mode = 'search';
		this.table.config.selection = true;
		if (this.search_type != type || load === true || load === 2) {
			this.search_type = type;
			if (this.search_type != 'simple' || load == 2) {
				loadPage('?mod=contacts&act=search&type=' + type + (params == undefined || params == null || !params? '' : '&' + params));
			}
		}
	},
	
	showMessage: function (message, fade) {
		$('#main-container').hide();
		var elem = $('<div class="subframe" style="width:100%; height: 100%"></div>');
		elem.append('<div class="info-block">' + message + '</div>');
	  	$("#screen-content-block").prepend(elem);
	  	$('#main-container').show();
	},
	
	showInfoMessage: function (message, fade) {
		if (fade == undefined || !fade) {
			$("#list_info div.info-message").remove();
			message = '<div class="info-message-close"><a href="javascript:void(0)" onclick="$(\'#list_info div.info-message\').remove(); document.app.resize()">Закрыть</a></div>' + message;
			$("#list_info").append('<div class="info-message with-close">' + message + '</div>');			
			
		} else {
			$("#list_info").append('<div style="display:none" class="info-message">' + message + '</div>');
			$("#list_info div.info-message").fadeIn("slow", function () {
				$("#list_info div.info-message").fadeOut(3000, function () {$(this).remove();});
			});	
		}
	},

	openSubframe: function(url, frame, saveKey) {
	  	this.closeSubframe();
		$('#main-container').hide();
  		var elem = createElem(frame ? "iframe" : "div", "subframe");
	  	elem.style.width = "100%";
	  	elem.style.height = "100%";
	  	if (frame) {
	  	  	elem.frameBorder = "no";
	  	  	elem.setAttribute("SCROLLING", "NO");  		
	  	} else if (this.subFrame) {
	  		$(this.subFrame).remove();
	  	}
	  	$("#screen-content-block").prepend(elem);
	  	
	  	this.startLoading();
	  	var _this = this;
	  	if (frame) {
	  		$(elem).load(function () {
	  			_this.finishLoading();
	  		});
	  	  	if (Ext.isOpera && elem.location) {
	  	  		elem.location.href = url;
	  	  	} else {
	  	  		elem.src = url;
	  	  	}  		
	  	} else {
			$(elem).load(url, false, function (response) {
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
					$('#main-container').show();
			});
	  	}
	  	this.subFrame = elem;
	  },
	
	
	setViewSettings: function(settings) {
		$.extend(this.viewSettings, settings);
		this.table.pager.setItemsOnPage(this.viewSettings.itemsOnPage);
		$(".records-page-count").html(this.viewSettings.itemsOnPage);
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
		$(".records-page-count").html(n);
		$.post("?mod=users&act=items&ajax=1", {n: n}, function (response) {}, "json");
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
			if (responseData.list && responseData.list.id) {
				this.mode = 'lists';
				this.navBarBlockActivated({id:'lists'}, true);
			}
				
			if (this.mode == 'search' && this.search_type != 'simple') {
				$("#group-title-container .folder-info-block").hide();
				$("#group-title-container .title-wrapper").hide();
				if ($("#search_content").parent().attr('id') != 'group-title-container') {
					$("#group-title-container #search_content").remove();
					$("#group-title-container").append($("#screen-content-block div.subframe #search_content"));
				}
			} else {
				$("#group-title-container .title-wrapper").show();
				$("#group-title-container .folder-info-block").show();
				$("#search_content").remove();
			}
			
			
			this.closeSubframe();
			if (responseData.viewMode)
				responseData.folder.viewMode = responseData.viewMode;
			
			if (responseData.sorting) 
				this.table.currentSorting = responseData.sorting; 

			if (responseData.itemsOnPage)
				responseData.folder.itemsOnPage = responseData.itemsOnPage;
			
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
					if (i == 0) {
						//document.fields[i].sortingMenu = true;
					}
				}		
			}
			this.table.columns = this.table.actualColumns();			
				
			if (responseData.list && responseData.list.id && responseData.add) {
				$("#nav-bar").parent().show();					
				//this.navBar.setActiveBlock('lists', true);
				this.listsList.addList(responseData.list,  responseData.add == 2);
			}
			this.currentFolder.load(responseData.folder);
			this.resize();			
		}, 
		this);
		
		// Create data store
		var store = new WbsDataStore({reader: reader, idProperty: "C_ID", recordClass: UGUser});
		return store;
	},
		
	selectFolder: function(folderId, fromTree) {
		if (this.mode != 'folders' && this.mode != 'analytics') {
			if (this.listsList) this.listsList.unSelect();
			if (this.widgetsList) this.widgetsList.unSelect();			
			this.mode = 'folders';
			this.navBarBlockActivated({id:'folders'}, true);
		}
		
		this.currentFolder.reset();
		if (folderId != null) {		
			if (folderId == 'ALL' || folderId == 'ANALYTICS') {
				$("#folders a.select-folder").removeClass('selected');
				$("a#" + folderId + '-CONTACTS').addClass('selected');
				this.foldersTree.unSelect();
			} 
			if (folderId == 'ANALYTICS') {
				document.app.openSubframe('?mod=analytics');
			} else {
				this.mode = 'folders';
				$("#main-header div.backlink").remove();
				$("#search_content").remove();
				this.table.resetPage(this.page);
				this.table.resetHighlightWord();	
				this.store.setParams({mode: "folders", folderId: folderId});
				this.store.setSorting("", "");
				this.store.load();
			}
		} 
	},
	
	selectList: function(listId) {
		if (this.mode != 'lists') {
			$("#folders a.select-folder").removeClass('selected');
			if (this.widgetsList) this.widgetsList.unSelect();
			if (this.foldersTree) this.foldersTree.unSelect();
			this.navBarBlockActivated({id:'lists'}, true);
		}
		$(this.newListBtn.getElem()).parent().removeClass('selected');		
		if (listId != null) {			
			this.table.resetPage(this.page);
			this.table.resetHighlightWord();					
			this.store.setParams({mode: "lists", listId: listId});
			this.store.setSorting("", "");
			this.store.load ();			
		} else {
			this.currentFolder.reset();
		}
	},	
	
	folderChanged: function() {
		//this.closeSubframe();
		this.controlPanel.folderChanged();
		this.table.folderChanged();
	},
	
	folderModified: function() {
		if (this.mode == "lists") {
			this.listsList.listModified();
		} else {
			this.foldersTree.folderModified();
		}
	},
	
	folderDeleted: function () {
		if (this.mode == "lists") {
			this.listsList.listDeleted();
			$("#lists-list a.create-list").click();
		} else {
			this.foldersTree.folderDeleted();
		}
	},
	
	getCurrentFolder: function() {
		return this.currentFolder;
	},
		
	
	navBarBlockActivated: function (block, hide) {
		setCookie("last_block", block.id);
		this.mode = block.id;
		$("#main-header div.backlink").remove();
		$("#search_header").hide();
		document.app.mode = block.id;
		this.table.pager.config.nameElements = 'Контакты';

		if (block.id != 'lists') {
			$('#main-container').show();
			this.table.config.selection = true;
			if ($("#list_info div.onload").length == 0) {
				$("#list_info").empty();
			}
			
			$("#group-title-container div.wbs-menu-btn.list").removeClass("list").insertBefore($("#view-settings-block"));
		} 
		
		if (block.id == "folders") {
			$('#main-container').show();
			this.controlPanel.usersActionsBtn.setDisplayed(true);
			if (!hide) {
				if (this.foldersTree.getSelectedNode() && this.foldersTree.getSelectedNode().Id != 'ALL') {
					this.foldersTree.selectNode(this.foldersTree.getSelectedNode().Id);
				} else {
					if (!this.config.currentFolderId) {
						this.config.currentFolderId = 'ALL';
					}
					this.foldersTree.getNode('ROOT').expand();
					if (this.config.currentFolderId == 'ALL' || this.config.currentFolderId == 'ANALYTICS') {
						this.selectFolder(this.config.currentFolderId);
					} else {
						this.foldersTree.selectNode(this.config.currentFolderId);
					}				
				}
			}
		} else if (block.id == "lists") {
			this.controlPanel.usersActionsBtn.setDisplayed(true);
			if (hide == undefined || hide !== true) {
				var l = this.listsList.getSelectedNode();
				if (l && l.Id != 'ROOT') {
					this.listsList.selectNode(l.Id);
				} else { 
					if (this.config.currentListId && this.listsList.getNode(this.config.currentListId) != undefined) {
						this.listsList.selectNode(this.config.currentListId);
					} else {
						$(this.newListBtn.btnElem).click();
					}
				}
			}
		} else if (block.id == "search") { 
			$('#main-container').show();
			this.controlPanel.usersActionsBtn.setDisplayed(true);
			if (this.search_type) {
				this.setSearchType(this.search_type, null, true);
			} else {
				this.setSearchType('', null, true);
			}
		} else if (block.id == "widgets") {
			$('#main-container').show();
			if (this.widgetsList.getSelectedNode()) {
				this.widgetsList.selectNode(this.widgetsList.getSelectedNode().Id);
			} else {
				if (this.config.currentFormId && this.widgetsList.getNode(this.config.currentFormId) != undefined) {
					this.widgetsList.selectNode(this.config.currentFormId);
				} else {
					$(this.newWidgetBtn.getElem()).click();
				}
			}
		}
		
	},

	refreshData: function() {
	},
	
	doAnalytics: function (f, v, title, save) {
		this.currentFolder.Id = 0;
		this.mode = 'analytics';
		document.createList = function () {
			document.app.doAnalytics(string, title, 1);
		};
		if ($("#main-header .backlink").length == 0) {
			$("#main-header").prepend('<div class="backlink lbg"><a href="#" onclick="return backToAnalytics()"><span>&larr;</span>Назад</a></div>');
		}		
		this.closeSubframe();
		this.table.resetPage(this.page);
		this.store.setParams({mode: "analytics", query: f, value: v, title: title, save: (save == undefined || !save ? 0 : 1)});
		this.store.load();
		this.currentFolder.reset();				
	},
	
	doSearch: function(searchString, noSave, list) {
		if (list != undefined && list) {
			noSave = 1;
			this.table.resetHighlightWord();
		} else {
			this.table.setHighlightWord(searchString);
			list = 0;
		}
		var list_title = $("#list_name").val();
		if (list && list_title) {
			this.currentFolder.Name = list_title;
			this.listsList.listModified();
		}		
		this.table.resetPage(this.page);		
		this.store.setParams({mode: "search", searchString: searchString, noSave: noSave, list: list, list_title: list_title});
		this.store.load();
		
		this.currentFolder.reset();
	},
	
	doSmartSearch: function (query, noSave) {
		var s = new Array();
		for (var i in query) {
			if (i.substring(i.length - 5) == '[val]') {
				s.push(query[i]);
			}
		}
		query.mode = "smartsearch";
		query.noSave = noSave;
		this.table.resetHighlightWord();
		var list_title = $("#list_name").val();
		if (list_title) {
			query.list_title = list_title;
		}
		if (query.list && list_title) {
			this.currentFolder.Name = list_title;
			this.listsList.listModified();
		}		
		this.table.resetPage(this.page);
		this.store.setParams(query);
		this.store.load();
		this.currentFolder.reset();					
	},

	doAdvancedSearch: function (query, noSave, list) {
		query.mode = "advancedsearch";
		query.noSave = noSave;
		query.list = list;
		var list_title = $("#list_name").val();
		this.table.resetHighlightWord();
		if (list_title) {
			query.list_title = list_title;
		}		
		if (list && list_title) {
			this.currentFolder.Name = list_title;
			this.listsList.listModified();
		}				
		this.table.resetPage(this.page);
		this.store.setParams(query);
		this.store.load();
		this.currentFolder.reset();					
	},

	createNewListBtn: function () {
		var btn = new WbsLinkButton({
			el: 'new-list-btn',
			label: 'Добавить'
		});
		$(btn.getElem()).click(function () {
			Toogles.openBlock('#lists-list-toogle div.h', '#lists-list');
			loadPage('?mod=lists&act=create');
			document.app.listsList.unSelect();
			$(btn.getElem()).parent().addClass('selected');
		});
		return btn;
	},
	
	createNewWidgetBtn: function () {
		var btn = new WbsLinkButton({
			el: 'new-widget-btn',
			label: 'Добавить'
		});
		$(btn.getElem()).click(function () {
			Toogles.openBlock('#widgets-list-toogle div.h', '#widgets-list');
			loadPage('?mod=widgets&act=create');
			document.app.widgetsList.unSelect();
			$(btn.getElem()).parent().addClass('selected');			
		});
		return btn;
	},
	
	createNewFolderBtn: function() {	
		var btn = new WbsLinkButton({
			el: "new-folder-btn", 
			label: "Добавить", 
			onClick: this.foldersTree.showCreateNewDlg,
			scope: this.foldersTree
		});		
		return btn;
	}	
});


var UGListMenu = newClass (WbsPopmenu, {
	constructor: function(config) {
		this.list = config.list;
		var share_title = "Дать доступ к этому списку другим пользователям (только по чтению)";
		if (this.list.Data.SHARED) {
			var cls = 'item-list-shared';
		} else {
			var cls = '';
		}
		config.items = [
		    {label: this.list.Data.SEARCH ? "Редактировать критерии поиска" : "Редактировать", disabled: false, onClick: this.list.editList},		                
			{label: "Переименовать список", disabled: this.list.Rights < 7, onClick: this.list.showRename.bind(this.list)},	
			{label: "Удалить этот список", disabled: this.list.Rights < 7, onClick: this.list.tryDelete.bind(this.list)},
			{label: share_title, iconCls: cls, disabled: this.list.Rights < 7, onClick: this.list.share.bind(this.list)},
		];	
		
		this.superclass().constructor.call(this, config);
	}
});


var UGListList = newClass(WbsTree, {
	constructor: function(config, app) {
		this.app = app;
		var config = {elemId: config.elemId, nodes: config.nodes, rootVisible: false};
		this.superclass().constructor.call(this, config);
		
		this.nodeMap = {
			id : 0,
			text: 1,
			children: 2,
			search: 3,
			shared: 4
		};
		
		this.init();
	},
			
	onAfterRender: function() {
	},
	
	getListItemById: function(id, tree) {
		if (tree == undefined) {
			tree = document.listNodes;
		}
		return document.app.getItemById(id, tree, this.nodeMap);		
	},
	
	listModified: function() {
		var list = this.app.currentFolder;
		var node = this.getNode(list.Id);
		if (node) {
			node.setText(list.Name.htmlSpecialChars());
			var item = this.getListItemById(list.Id);
			item[this.nodeMap.text] = list.Name;					 
		}
	},	
	
	onNodeClick: function(node, e) {
		$("#folders .content").scrollTop();
		if (this.app.getCurrentFolder().Id != null) {
			this.app.getCurrentFolder().reset();
		}
		$(".subframe").hide();	
		this.app.selectList(node.id, true);
	},
	
	addNode: function(nodeData) {
		var addConfig = null;
		if (nodeData[this.nodeMap.id] == -1) {
			addConfig = {iconCls: "icon-list-sc"};
		} else if (nodeData[this.nodeMap.search] === 2) {
			addConfig = {iconCls: "icon-list-wa"};
		} else if (nodeData[this.nodeMap.search]) {
			if (nodeData[this.nodeMap.shared]) {
				addConfig = {iconCls: "icon-list-search-shared"};
			} else {
				addConfig = {iconCls: "icon-list-search"};
			}
		} else {
			if (nodeData[this.nodeMap.shared]) {
				addConfig = {iconCls: "icon-list-shared"};
			} else {
				addConfig = {iconCls: "icon-list"};
			}
		}
		var node = this.superclass().addNode.call(this, nodeData, addConfig);
		return node;
	},
	
	
	addList: function (data, rename, hide) {
		Toogles.openBlock('#lists-list-toogle div.h', '#lists-list');
		var nodeData = {};
		nodeData[this.nodeMap.id] = data.id;
		nodeData[this.nodeMap.text] = data.name;
		nodeData[this.nodeMap.children] = null;
		nodeData[this.nodeMap.search] = data.search;
		nodeData[this.nodeMap.shared] = data.shared;

		var newNode = this.addNode(nodeData);
		this.getNode('ROOT').appendChild(newNode);
		
		var p = this.getListItemById('ROOT');
		p[this.nodeMap.children].push(nodeData);		
		if (hide == true) return;
		this.selectNode(newNode.Id, true);

		var r = false;
		if (r) {
			var currentFolder = this.app.getCurrentFolder();
			var showRename = function() {
				currentFolder.showRename();
				currentFolder.removeListener("changed", showRename);
			};						
			currentFolder.addListener("changed", showRename, currentFolder);
		}
		
		document.app.store.setParams({mode: "lists", listId: newNode.Id});
	},
	
	listDeleted: function() {
		var list = this.app.currentFolder;
		var node = this.getNode(list.Id);
		var cur = node.previousSibling || node.nextSibling;		
		if (node) {
			var f = this.getListItemById(node.parentNode.Id);
			for (var i = 0; i < f[this.nodeMap.children].length; i++) {
				if (f[this.nodeMap.children][i][this.nodeMap.id] == list.Id) {
					f[this.nodeMap.children].splice(i, 1);
					break;
				}
			}
			this.removeNode(node);
		}
		if (cur) {
			this.selectNode(cur.Id);
		} else {
			$(this.app.newListBtn.btnElem).click();
		}
	}	
});

var UGWidgetList = newClass(WbsTree, {
	constructor: function(config, app) {
		this.app = app;
		var config = {elemId: config.elemId, nodes: config.nodes, rootVisible: false};
		this.superclass().constructor.call(this, config);
		
		this.nodeMap = {
			id : 0,
			text: 1,
			children: 2,
			encId: 3
		};
		
		this.init();
	},
			
	onAfterRender: function() {
		var rootNode = this.treePanel.getRootNode();
	},
	
	setTitle: function (title) {
		var widget = this.getSelectedNode();
		widget.Name = title;
		var node = this.getNode(widget.Id);
		if (node)
			 node.setText(title);
	},
	
	widgetModified: function() {
		var widget = this.app.currentFolder;
		var node = this.getNode(widget.Id);
		if (node)
			 node.setText(widget.Name);
	},	
	
	onNodeClick: function(node, e) {
		if (this.app.mode != 'widgets') {
			$("#folders a.select-folder").removeClass('selected');
			if (this.app.listsList) this.app.listsList.unSelect();
			if (this.app.foldersTree) this.app.foldersTree.unSelect();			
			setCookie("last_block", 'widgets');
		}
		this.app.mode = "widgets";
		this.app.openSubframe("?mod=widgets&act=edit&id=" + node.Id, true);
	},
	
	addNode: function(nodeData) {
		var addConfig = {encId: nodeData[this.nodeMap.encId]};
		var node = this.superclass().addNode.call(this, nodeData, addConfig);
		return node;
	},
	
	
	addWidget: function (data) {
		var nodeData = {};
		nodeData[this.nodeMap.id] = data.id;
		nodeData[this.nodeMap.text] = data.name;
		nodeData[this.nodeMap.children] = null;
		nodeData[this.nodeMap.encId] = data.enc_id;

		var newNode = this.addNode(nodeData);
		this.getNode('ROOT').appendChild(newNode);

		this.selectNode(newNode.Id);
	},
	
	widgetDeleted: function() {
		var node = this.getSelectedNode();

		var cur = node.previousSibling || node.nextSibling;		
		if (node) {
			this.removeNode(node);
		}

		if (cur) {
			this.selectNode(cur.Id);
		} else {
			$(this.app.newWidgetBtn.btnElem).click();
		}	
	
	}	
});

var UGFolderList = newClass(WbsTree, {
	constructor: function(config, app) {
		this.app = app;
		var config = {elemId: config.elemId, iconCls: "my-folder", nodes: config.nodes, rootVisible: false};
		this.superclass().constructor.call(this, config);
		
		this.nodeMap = {
			id : 0,
			text: 1,
			right: 2,
			children: 3
		};
		this.rightsCreateFolder = this.checkCreateRights();
		this.init();
	},
	
	checkCreateRights: function (folder) {
		if (folder == undefined) {
			var folder = document.folderNodes;
		}
		if (folder[2] == 7) return true;
		for (var i = 0; i < folder[3].length; i++) {
			if (this.checkCreateRights(folder[3][i])) {
				return true;
			}
		}
		return false;
	},
	
	showCreateNewDlg: function () {
		Toogles.openBlock('#folders-list-toogle div.h', '#folders-list');
		var dlg = new UGNewFolderDlg();
		dlg.show();
	},
	
	getFolderItemById: function(id, tree) {
		if (tree == undefined) {
			tree = document.folderNodes;
		}
		var r = document.app.getItemById(id, tree, this.nodeMap);
		return r;
	},
	
	unSelect: function () {
		this.superclass().unSelect.call(this);
	},
		
	addNode: function(nodeData) {
		var addConfig = null;
		
		if (nodeData[this.nodeMap.right] == 0 && nodeData[this.nodeMap.id] != "ROOT") {
			addConfig = {iconCls: "folder-norights"};
		} else if (nodeData[this.nodeMap.id].substring(0, 7) == 'PRIVATE') {
			addConfig = {iconCls: "folder-private"};			
		} else if (nodeData[this.nodeMap.id] == 'PUBLIC') {
			addConfig = {iconCls: "folder-public"};
		} else if (nodeData[this.nodeMap.right] == 1) {
			addConfig = {iconCls: "folder-readonly"}; 
		}
					
		var node = this.superclass().addNode.call(this, nodeData, addConfig);
		node.Rights = nodeData[this.nodeMap.right];
		return node;		
	},
		
	onAfterRender: function() {
		
	},
	
	onNodeClick: function(node, e) {
		$("#folders a.select-folder").removeClass('selected');
		if (node.id == "ROOT") {
			this.app.selectFolder('ALL');
			return;
		} 
		if (this.app.getCurrentFolder().Id != null) {
			this.app.getCurrentFolder().reset();
		}

		this.app.selectFolder(node.id, true);		
	}, 
	
	folderModified: function() {
		var folder = this.app.currentFolder;
		var node = this.getNode(folder.Id);
		if (node) {
			node.setText(folder.Name.htmlSpecialChars());
			var item = this.getFolderItemById(folder.Id);
			item[this.nodeMap.text] = folder.Name;		
		}
	},
	
	folderDeleted: function() {
		var folder = this.app.currentFolder;
		var node = this.getNode(folder.Id);
		var cur = node.previousSibling || node.parentNode || this.rootNode;		
		if (node) {
			var f = this.getFolderItemById(node.parentNode.Id);
			for (var i = 0; i < f[this.nodeMap.children].length; i++) {
				if (f[this.nodeMap.children][i][this.nodeMap.id] == folder.Id) {
					f[this.nodeMap.children].splice(i, 1);
					break;
				}
			}
			this.removeNode(node);
		}
		if (cur) {
			this.selectNode(cur.Id);
		}	
		this.rightsCreateFolder = this.checkCreateRights();
		this.app.newFolderBtn.setDisabled(!this.rightsCreateFolder);
	},
	
	getNewFolderMenu: function() {	
		var node = this.getSelectedNode();
		if (node) {
			var subfolderDisabled = !WbsRightsMask.canFolder(node.Rights);
		} else {
			var subfolderDisabled = true;
		}

		var items = [
			{label: "Корневую папку", onClick: this.createRootFolder, scope: this, disabled: !this.app.right.createRootFolder},
			{label: "Подпапку", onClick: this.createSubFolder, scope: this, disabled: subfolderDisabled}
		];				
		return new WbsPopmenu({items: items});
	},
	
	createFolder: function (dlg, params) {
		var self = this;
		$.post('?mod=folders&act=add&ajax=1', params, function (response) {
			if (response.status == 'ERR') {
				alert(result.error);
				return;
			} else {
				var nodeData = {};
				nodeData[self.nodeMap.id] = response.data.id;
				nodeData[self.nodeMap.text] = response.data.name;
				nodeData[self.nodeMap.right] = response.data.id != 'PUBLIC' ? 7 : 3;
				nodeData[self.nodeMap.children] = new Array();
				var newNode = self.addNode(nodeData);
				var parentNode = self.getNode(response.data.parentId);
				if (response.data.id == 'PUBLIC') {
					var after = self.getNode(response.data.after);
					if (after) {
						after = after.nextSibling;
					} else {
						after = parentNode.firstChild;
					}
					parentNode.insertBefore(newNode, after);
				} else {
					parentNode.appendChild(newNode);
				}
				var p = self.getFolderItemById(parentNode.Id);
				p[self.nodeMap.children].push(nodeData);
				var currentFolder = self.app.getCurrentFolder();
				if (response.data.access == 1) {
					self.selectNode(newNode.Id, true);
					document.app.openSubframe('?mod=folders&act=rights&folder_id=' + newNode.Id, 1);
				} else {
					self.selectNode(newNode.Id);
				}
				dlg.close();		
			}
		}, "json");
	}
	
});

var UGAddToListDlg = newClass(WbsDlg, {
	constructor: function(config) {
		var actionBtnLabel = "Добавить";
		config.buttons = [
			{label: actionBtnLabel, onClick: this.doAction, scope: this, disabled: true},
			{label: "Отмена", onClick: this.hide, scope: this}
		];

		this.superclass().constructor.call(this, config);
		
		$("#dlg-move-content .add-to-list").show();
		this.folderSelector = document.getElementById("dlg-folders-select");
		this.folderSelector.onchange = this.enableButton;
		document.getElementById("add-to-new-list").onfocus = function () {
			this.enableButton();
			$('#dlg-folders-select option:selected').removeAttr('selected');
		}.bind(this);
		
		
		this.action = config.action;
		this.actionObject = config.actionObject;
		
		this.descElem = document.getElementById("dlg-move-desc");
		this.descElem.innerHTML = (config.description ? config.description : "") + ":";

		clearNode(this.folderSelector);
		this.buildFoldersSelect();
	},

	enableButton: function() {
		if($('#dlg-folders-select').val() != 0) {
			$('.dsabled-btn').removeAttr("disabled");
		} else {
			$('.dsabled-btn').attr("disabled", true);
		}
	},

	doAction: function() {
		var listId = this.folderSelector.value;
		if (!listId) {
			var listName = $("#add-to-new-list").val(); 
		} else {
			var listName = '';
		}
		this.actionObject.doAddToList(listId, listName, this.afterAction.bind(this));
	},

	buildFoldersSelect: function() {
		for (var i = 0; i < document.listNodes[2].length; i++) {
			this.addList(document.listNodes[2][i]);
		}
	},

	addList: function(ListData) {
		var option = createElem("option");
		if(!ListData[3]) {
			if (document.app.mode == 'lists' && document.app.currentFolder.Id == ListData[0]) {
				option.disabled = "disabled";
			}
			option.value = ListData[0];
			option.innerHTML = ListData[1].truncate(50);
			this.folderSelector.appendChild(option);
		}
	},
	
	afterAction: function() {
		this.hide();
		document.app.table.renderData();
	}

});

var UGExportContactsDlg = newClass(WbsDlg, {

	constructor: function(config) {
		var actionBtnLabel = "Экспорт";
		config.buttons = [
			{label: actionBtnLabel, onClick: this.doAction, scope: this, disabled: false, id:"doExportBtn"},
			{label: "Отмена", onClick: this.hide, scope: this}
		];

		this.superclass().constructor.call(this, config);
		this.actionObject = config.actionObject;

		this.buildExportContent();
	},

	buildExportContent: function(elem) {
		var users = this.config.actionObject.getIds();
		//console.log(document.app.store);
		$("#dlg-export-content").load("?mod=contacts&act=csv&export=1&mode=" + document.app.mode, document.app.store.params, function() {
			$("#contacts").val(users);
			$("#current").val(document.app.getCurrentFolder().Id);
			$("#selected_count").html(users.length + "");
			$("#total_count").html(document.app.table.getStore().getTotal() + "");
			if(users.length) {
				$("#input_selected").attr("checked", true);
			} else {
				$("#input_selected").attr("disabled", true);
				$("#input_selected_wrapper").addClass("disable");
				$("#input_total").attr("checked", true);
			}
		});
	},

	doAction: function() {
		$('#ex_in').children().attr('selected', 'selected');
		document.csvExport.submit();
		this.hide();
	}

});

var UGSendsmsDlg = newClass(WbsDlg, {

	constructor: function(config) {
		var actionBtnLabel = "Отправить";
		config.buttons = [
			{label: actionBtnLabel, onClick: this.doAction, scope: this, disabled: false, id:"doSendsmsBtn"},
			{label: "Отмена", onClick: this.hide, scope: this}
		];

		this.superclass().constructor.call(this, config);
		this.actionObject = config.actionObject;

		this.buildSendsmsContent();
		
		var qwe = 'asd';
	},

	buildSendsmsContent: function(elem) {
		var users = this.config.actionObject.getIds();

		$("#dlg-sendsms-content").load("?mod=contacts&act=sendsms&users=" + users + "&mode=" + document.app.mode);
	},

	doAction: function() {
		this.actionObject.doSendsms(this.afterAction.bind(this));
	},

	afterAction: function() {
		this.hide();
	}

});

var UGCopyMoveDlg = newClass(WbsDlg, {
	constructor: function(config) {
		var actionBtnLabel = "Переместить";
		config.buttons = [
			{label: actionBtnLabel, onClick: this.doAction, scope: this, disabled: true},
			{label: "Отмена", onClick: this.hide, scope: this}
		];

		this.superclass().constructor.call(this, config);
		
		this.folderSelector = document.getElementById("dlg-folders-select");
		this.folderSelector.onchange = this.enableButton;

		this.descElem = document.getElementById("dlg-move-desc");
		$("#dlg-move-content .add-to-list").hide();
		
		this.action = config.action;
		this.actionObject = config.actionObject;
		
		this.descElem = document.getElementById("dlg-move-desc");
		this.descElem.innerHTML = (config.description ? config.description : "") + ":";

		clearNode(this.folderSelector);
		this.buildFoldersSelect();
	},

	enableButton: function() {
		if($('#dlg-folders-select').val() != 0) {
			$('.dsabled-btn').removeAttr("disabled");
		} else {
			$('.dsabled-btn').attr("disabled", true);
		}
	},

	doAction: function() {
		var folderId = this.folderSelector.value;
		this.actionObject.doCopyMove(folderId, this.action, this.afterAction.bind(this));
	},

	buildFoldersSelect: function() {
		if (this.config.action == 'move') {
			this.addFolder(document.folderNodes, 0);
		} else {
			for (var i = 0; i < document.folderNodes[3].length; i++) {
				this.addFolder(document.folderNodes[3][i], 0);
			}
		}
	},

	addFolder: function(folderData, level) {

		if ((folderData[0] == 'PUBLIC' || folderData[0].substring(0, 7) == 'PRIVATE' ) && !this.config.systemFolders) {
			return;
		}

		var selectedParent = 0;
		if(this.config.selectedFolderId) {
			selectedParent = this.config.selectedFolderId.replace(/(.*)\.\d+\.$/, "$1.");
		}
		selectedParent = (selectedParent && selectedParent != this.config.selectedFolderId) ? selectedParent : "ROOT";
		var re = new RegExp("^" + this.config.selectedFolderId);
		var disabled = false;
		if (this.action == "move") {
			if (folderData[0] == 'ROOT') {
				disabled = folderData[2] < 7 || folderData[0] == selectedParent;
			} else if (folderData[0] == selectedParent || re.test(folderData[0])) {
				disabled = true;
			}
		} else if (folderData[0] == "ROOT") {
			disabled = true;
		}
		if (folderData[0] == "ROOT") {
			folderData[1] = "&lt;корень&gt;";
		}
		disabled = disabled || folderData[0] == this.config.selectedFolderId || folderData[2] < 3;
		
		var paddingLeft = "&nbsp;";
		for (var k = 0; k < level; k++) {
			paddingLeft += "&nbsp;";
		}
		var option = createElem("option");

		option.value = folderData[0];
		option.innerHTML = (paddingLeft + folderData[1]).truncate(50);
		if (disabled) {
			option.disabled = true;
		}
		this.folderSelector.appendChild(option);

		if (folderData[3]) {
			for (var i = 0; i < folderData[3].length; i++) {
				this.addFolder(folderData[3][i], level+1);
			}
		}
	},

	afterAction: function(ids) {
		this.hide();
		if(this.action != "move") {
			document.app.table.reloadView();
		} else if(ids) {
			document.app.selectFolder(ids[2], true);
			window.location = "index.php";
		}
	}
});


var UGNewFolderDlg = newClass(WbsDlg, {
	constructor: function() {
		var config = {};
		var folder = document.app.foldersTree.getSelectedNode();
		config.title = 'Создать новую папку';
		config.buttons = [
			{label: "Добавить папку", onClick: this.doAction, scope: this},
			{label: "Отмена", onClick: this.close, scope: this}
		];
		config.height = 'auto';
		config.width = 600;
		this.superclass().constructor.call(this, config);
	},

	doAction: function() {
		var params = $("#form-new-folder").serialize();
		document.app.foldersTree.createFolder(this, params);
	},
	
	buildSelectFolder: function (folder_id, folder, prefix) {
		if (folder == undefined) {
			var folder  = document.folderNodes; 
		}
		if (prefix == undefined) {
			prefix = '';
		}
		var result = '';
		if (!folder[3]) {
			return '';
		}
		for (var i = 0; i < folder[3].length; i++) {
			this.subfolder_right = this.subfolder_right | (folder[3][i][2] == 7);  
			result += '<option value="' + folder[3][i][0] + '"' + (folder[3][i][0] == folder_id || (!folder_id && folder[3][i][2] > 3) ? 'selected="selected" ' : '') +  (folder[3][i][2] < 7 ? 'disabled="disabled"' : '') + '>' + prefix + folder[3][i][1].substr(0, 45) + '</option>';
			if (folder[3][i][3]) {
				if (!folder_id && folder[3][i][2] > 3) folder_id = "no";
				result += this.buildSelectFolder(folder_id, folder[3][i], prefix + "&nbsp;&nbsp;");
			}
		}
		return result;
	},

	buildContent: function(contentElem) {
		var folder = document.app.foldersTree.getSelectedNode();
		var folder_title = folder ? folder.Name : false;
		var root_right = document.app.foldersTree.getNode('ROOT').Rights > 3;
		this.subfolder_right = false;
		var nested_right = folder ? folder.Rights > 3 : 0;
		var select_folder = '<select class="fix-width" onchange="$(\'#new-folder-parent-nested\').click()" name="subfolder">' + this.buildSelectFolder(nested_right && folder ? folder.Id : false) + '</select>'; 
		var html = '<div class="folder-title">Имя папки: <input name="name" id="new-folder-name" type="text" value="Новая папка" /></div>';
		html += '<div style="padding-bottom: 10px">Выберите место в дереве папок:</div>' +
			'<label><input id="new-folder-parent-root" type="radio" value="ROOT" name="parentId" ' + (!root_right ? 'disabled="disabled"' : '') + (!nested_right && root_right ? ' checked="checked"' : '') + ' /> Корневая</label><br />' + 
			'<label><input id="new-folder-parent-nested" type="radio" value="FOLDER" name="parentId" ' + (!this.subfolder_right ? 'disabled="disabled"' : '') + (nested_right || !root_right ? ' checked="checked"' : '') + ' /> Вложенная в:</label>' + select_folder + '<br />';	
		if (document.app.right.users) {
			html += '<div class="folder-access-rights">Права доступа:</div>' + 
			'<label><input type="radio" onchange=\'$("#new-folder-name,#new-folder-parent-nested").removeAttr("disabled")\' name="access" value="0" checked="checked" /> Только у меня <span>(можно настроить доступ другим позднее)</span></label><br />' + 
			'<label><input type="radio" onchange=\'$("#new-folder-name,#new-folder-parent-nested").removeAttr("disabled")\' name="access" value="1" /> Совместные <span>(настроить доступ другим сейчас)</span></label><br />';
		}
		if (document.app.right.admin && !document.app.foldersTree.getNode('PUBLIC')) {
			html += '<label><input onchange=\'$("#new-folder-parent-nested").attr("disabled","disabled");$("#new-folder-name").attr("disabled", "disabled").val("Общая"); $("#new-folder-parent-root").attr("checked", "checked")\' type="radio" name="access" value="2" /> Общая <span>(доступна всем пользователям)</span></label><br />'; 
		}
	contentElem.innerHTML = '<div class="folder-new-dlg"><form id="form-new-folder">' + html + '</form></div>';
	},
	
	onAfterShow: function () {
		$("#new-folder-name").select();
	}
});


var UGAddContactMenu = newClass (WbsPopmenu, {
	constructor: function(record, type) {
		var items = [];
	
		var types = document.contactTypes;
		for ( var type in types ) {
			if ( typeof types[type] == 'string' )
				items.push( {label: types[type], onClick: this.click.bind(type) });
		}
		
		items = items.concat([
	       "-",
		   {label: "Импорт контактов", onClick: function () {
	    	  var url = 'index.php?mod=contacts&act=csv&import=1';
	    	  document.app.openSubframe(url, 1);
	       }}
		]);
		this.superclass().constructor.call(this, {items: items, withImages: true});
	},
	click: function() {
		var url = "?mod=contacts&act=add&type="+String(this) + "&mode="+document.app.mode+"&id="+document.app.getCurrentFolder().Id;
		document.app.openSubframe(url, 1);
	},
		
	onAfterShow: function() {
	},
		
	onClose: function() {
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
		var app = document.app;
		
		if (app != undefined) {
			var folder = app.getCurrentFolder();
			if (app.mode == "lists" || app.mode == "search" || (app.mode == 'folders' && folder.Id == 'ALL')) {
				this.rightsElem.innerHTML = "";
			} else {
				this.rightsElem.innerHTML = "<label>Ваши права доступа:</label> <b>" + WbsRightsMask.getRightsStr(folder.Rights) + "</b>";
			}
			if (app.mode == "lists") {
				if (folder.Data.SHARED && folder.Data.RIGHTS != 7) {
					var html = '<div class="info-message"' + (!folder.Data.SEARCH ? ' style="margin-right:300px"' : '') + '>Данный список предоставлен вам в пользование администратором. Контакты из этого списка могут быть недоступны для редактирования, поиска, а также в секции "Все контакты", если они хранятся в недоступных для вас папках.</div>';
					if (folder.Data.SEARCH) {
						html += '<div class="small-gray">Этот список основан на следующем критерии поиска:</div>';
						html += '<div class="search_info">' + folder.Data.SEARCH + '</div>';
					}
					$("#list_info").html(html);
				} else if (folder.Data.SEARCH) {
					$("#list_info").html('<div class="small-gray">Этот список основан на следующем критерии поиска:</div><div class="search_info" onclick="document.app.getCurrentFolder().editList()">' + folder.Data.SEARCH + '</div>');				
				} else {
					$("#list_info").html("");
				}
			} else {
				$("#view-settings-block").css('padding-top', '0');
			}
		}
	},
	
	actualColumns: function () {
		var columns = [];
		if (document.manageUsers == 1) {
			columns.push({
				name: "IS_USER", 
				width: "20", 
				cls: "icon-user",
				label: "", 
				custom: true, 
				html: "<img src='../common/html/res/ext/resources/images/default/tree/user-group.gif' />", 
				sorting: false
			});
		}
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
	
	outputValue: function(value, hl) {
		if (value == undefined) {
			return '';
		}
	  	if (!this.highlightWord || !hl)
	  		return value;
	  	var v = $("<div>" + value + "</div>");
	  	if (value.indexOf('<span class="highlight">') != -1) {
	  		return value;
	  	}
	  	var regex = new RegExp("(" + this.highlightWord.replace(/\s/ig, "|") +")", "ig");
	  	var s = "";
	  	v.contents().each(function () {
	  		if (this.nodeType == 3) {
	  			s += $(this).parent().html().replace(regex, '<span class="highlight">$1</span>');
	  		} else {
	  			$(this).html($(this).html().replace(regex, '<span class="highlight">$1</span>'));
	  			s += $("<div></div>").append(this).html();
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
	var mode = document.app.mode;
	if (mode == "lists") {
		return "<нет контактов в этом списке>";
	} else 	if (mode == "folders") {
		if (!document.app.getCurrentFolder().Rights) {
			return 'У вас нет прав в этой папке';
		}
		return "<нет контактов в этой папке>";		
	} else 	if (mode == "search") {
		return 'Контакты не найдены';
	} else {
		return "<нет контактов>";
	}
  },
  
  onLoad: function () {
	  if (!this.getStore() || !this.getStore().hasRecords()) {
		  if (document.app.mode == "search") {
			  $("#search_header").remove();
		  }
	  } else {
		  if (document.app.mode == "search") {
			  if ($("#search_header").length) {
				  $("#search_header").html(this.getStore().getTotal() + ' контактов найдено');
			  } else {
				  var str = '<div id="search_header">' + this.getStore().getTotal() + ' контактов найдено</div>';
				  $("div.contacts-info").prepend(str);
			  }
		  } else {
			  this.resetHighlightWord();
			  $("#search_header").remove();
		  }
	  }
	  
	if (typeof(results_load) == 'function') {
		results_load();
	}
		
	// Contacts not found
	if (document.app.mode == 'search' && (!this.getStore() || !this.getStore().hasRecords())) {
		// Hide button
		$("#view-settings-block").hide();
	} else {
		// Show buttons
		$("#view-settings-block").show();
	}
  }
});

var UGContactsListMenu = newClass(WbsPopmenu, {
	constructor: function(recordsList) {
		this.recordsList = recordsList;
		this.folder = document.app.getCurrentFolder();
		var config = {};
		
		var disabled = this.recordsList.getCount() == 0;
		
		if (document.app.mode == 'search') {
			this.folder.Rights = 3;
			config.items = [
				{label: "Сохранить как список", disabled: false, iconCls: 'item-save-as-list', onClick: document.createList}
			];
		} else if (document.app.mode == 'lists' && this.folder.Id == -1) {
			config.items = [
			    {label: "Обновить", disabled: false, onClick: function () {
			    	var params = document.app.store.params;
			    	params.force = 1;
			    	document.app.store.load();
			    }}
			];
		} else {
			config.items = [];
		}
		config.items = config.items.concat([
		    {label: 'Действия с выбранными контактами (' + recordsList.getCount() + "):", cls: "unactive"}
		]);
		
		if (document.app.mode != 'lists' || this.folder.Rights == 7) {
			config.items = config.items.concat([
				{label: "Мульти-редактирование", iconCls: "item-bulk-edit",
					 disabled: disabled || this.folder.Rights < 3, 
					 onClick: function () {
						var f = new Array();
						for (var i = 0; i < document.fields.length; i++) {
							f.push(document.fields[i].name);
						}
						f = f.join(',');
						document.app.openSubframe('?mod=contacts&act=multiedit&contacts='+recordsList.getIds()+'&f='+f, 1);
						}
				},
				"-",
				{label: (document.app.mode != 'folders' ? "Переместить в папку" : "Переместить в другую папку"), iconCls: 'item-contact-move', disabled: (disabled || this.folder.Rights < 3), onClick: recordsList.showMoveToFolderDlg.bind(recordsList) },
				{label: document.app.mode == 'lists' ? "Добавить в другой список" : "Добавить в список", disabled: disabled, onClick: recordsList.showAddToListDlg.bind(recordsList) }
			]);
		}
		if (document.app.mode == 'lists' && !this.folder.Data.SEARCH && this.folder.Rights == 7) {
			config.items = config.items.concat([
			    {label: "Исключить из этого списка", disabled: disabled, onClick: recordsList.excludeFromList.bind(recordsList) }
			]);
		}
		if (document.app.mode != 'lists' || this.folder.Rights == 7) {
			config.items = config.items.concat([
				{label: "Удалить", iconCls: 'item-contact-delete', disabled: (disabled || this.folder.Rights < 3), onClick: recordsList.tryDelete.bind(recordsList)}
			]);
		}
		config.items = config.items.concat([
		    "-",
			{label: "Экспорт", onClick: recordsList.exportContactsDlg.bind(recordsList)}
		]);
		config.items = config.items.concat([
		    "-",
			{label: "Отправить email", onClick: recordsList.sendEmailScreen.bind(recordsList)},
			{label: "Отправить SMS", onClick: recordsList.sendsmsDlg.bind(recordsList)}
		]);
		if (document.app.mode == 'folders' && (this.folder.Rights == 7 || (document.app.right.users && this.folder.Id && this.folder.Id.indexOf('.') != -1))) {
			config.items = config.items.concat([
			    "-",
			    {label: 'Действия с папкой:', cls: "unactive"}
			]);
			if (document.app.right.users) {
				config.items = config.items.concat([
				    {label: "Настроить права доступа", iconCls: 'item-folder-customize', disabled: false, onClick:  function () {document.app.openSubframe('?mod=folders&act=rights&folder_id=' + this.folder.Id, 1)}}
				]);
			}
			if (this.folder.Rights == 7) {
				config.items = config.items.concat([
					{label: "Переименовать", disabled: this.folder.Rights < 7, onClick: this.folder.showRename.bind(this.folder)},
					{label: "Переместить", iconCls: 'item-folder-move', disabled: this.folder.Rights < 7, onClick: this.folder.showMoveDlg.bind(this.folder)},
					{label: "Удалить папку", iconCls: 'item-folder-delete', disabled: this.folder.Rights < 7, onClick: this.folder.tryDelete.bind(this.folder)}
				]);
			}
		}
		if (document.app.mode == 'folders' && this.folder.Id == 'PUBLIC' && document.app.right.admin) {			
			config.items = config.items.concat([
			    "-",
			    {label: 'Действия с папкой:', cls: "unactive"},
			    {html: 'Удалить папку "Общая"', iconCls: 'item-folder-delete', disabled: false, onClick: this.folder.tryDelete.bind(this.folder)}
			]);

		}
		if (document.app.mode == 'lists' && this.folder.Rights == 7) {
			this.list = document.app.getCurrentFolder();
			
			config.items = config.items.concat([
			    "-",
			    {label: 'Действия со списком:', cls: "unactive"},
			    {label: this.list.Data.SEARCH ? "Редактировать критерии поиска" : "Редактировать", disabled: false, onClick: this.list.editList.bind(this.list)},		                
				{label: "Переименовать список", disabled: false, onClick: this.list.showRename.bind(this.list)},	
				{label: "Удалить этот список", disabled: false, onClick: this.list.tryDelete.bind(this.list)}
				
			]);		
			
			if (document.app.right.admin) {
				var share_title = "Дать доступ к этому списку другим пользователям (только по чтению)";
				if (this.list.Data.SHARED) {
					var cls = "item-list-shared";
				} else {
					var cls = "";
				}
				var shareList = function () {
					this.Data.SHARED = this.Data.SHARED ? 0 : 1;
					jQuery.post("?mod=lists&act=share&ajax=1", {id: this.Id, share: this.Data.SHARED});
					
					var app = document.app;
					var cls = this.Data.SEARCH ? 'icon-list-search' : 'icon-list';
					
					app.listsList.addClass(this.Data.SHARED ? cls + '-shared' : cls);
					app.listsList.removeClass(this.Data.SHARED ? cls : cls + '-shared');
					
				}				
				config.items = config.items.concat([
				{label: share_title, iconCls: cls, disabled: false, onClick: shareList.bind(this.list)}
				]);
			}
		}
		config.withImages = true;
		this.superclass().constructor.call(this, config);			
	}
});
