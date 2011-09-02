WbsTable = newClass(WbsObservable, {

	constructor: function (config) {
		this.config = config;
		
		if (config.store)
			this.setStore(config.store);
		
		this.id = config.id;
		if(!this.id)
			this.id = "commontable";
		
		this.configureSorting();
		
		this.view = null;
		this.views = new Array ();
		this.columns = config.columns;
		
		if (this.config.elem)
			this.elem = this.config.elem;
		else
			throw "No exists elem for table";
		
		this.tableElem = createDiv("wbs-table");
		this.elem.appendChild(this.tableElem);
		
		// Table data rendered to this elem
		this.renderElem = createDiv("wbs-table-content");
		this.tableElem.appendChild(this.renderElem);
		
		if (this.config.pager) {
			this.pagerElem = createDiv("wbs-table-pager");
			this.getFooterElem().appendChild(this.pagerElem);
			this.pager = new WbsPager({elem: this.pagerElem, table: this, nameElements: config.nameElements});
		}
		
		if (this.config.statusBar) {
			this.statusBarElem = createDiv("wbs-table-status-bar");
			this.getFooterElem().appendChild(this.statusBarElem);
		}
		
		if (this.config.autoHeight == false) {
			var container = createDiv("wbs-container");
			if (this.config.dock)
				this.tableElem.style.height = "100%";
			this.tableElem.appendChild(container);
			container.appendChild(this.renderElem);
			if(this.footerElem)
				container.appendChild(this.footerElem);
			container.resize = function(){};
			this.container = container;
		} else {
			this.container = new WbsFlexContainer({elem: this.tableElem, contentElem: this.renderElem,  footerElem: this.footerElem});
		}
		
		this.items = new Array ();
		this.checkboxes = new Array ();
			
		this.superclass().constructor.call(config);
		
		this.addEvents ({
			"afterRender": true,
			"afterLoad": true
		});
	},
		
	getColumns: function() {
		return this.columns;
	},
		
	getFooterElem: function() {
		if (!this.footerElem) {
			this.footerElem = createDiv("wbs-table-footer");
			this.tableElem.appendChild(this.footerElem);
		}
		return this.footerElem;
	},
		
	configureSorting: function() {
		var sorting = null;
		if (this.config.sortingToCookie) {
			sortingStr = getCookie(this.id + '-sorting');
			if (sortingStr) {
				var parts = sortingStr.split(":");
				sorting = {column: parts[0], direction: parts[1]};
			}
		} 
		if (!sorting && this.config.defaultSorting) {
			sorting = this.config.defaultSorting;
		}
		if (!sorting)
			sorting = {column: null, direction: null};
		
		this.currentSorting = sorting;
		this.store.setSorting(sorting.column, sorting.direction);
	},
	
	setStore: function (store) {
		this.store = store;
		store.addListener("recordModified", function(record) {this.storeRecordModified(record)}, this);
		store.addListener("dataChanged", function() {this.renderData(); this.fireEvent("afterLoad"); }, this);
		store.addListener("startLoading", function() {this.showLoading();}, this);
		
	},
		
	storeRecordModified: function(record) {
		this.refreshRecordBlock(record.id);
	},
	
	getStore: function () {
		return this.store;
	},
		
	setView: function (view) {
		this.view = view;
	},
		
	showLoading: function() {
		if (this.view && !this.config.hideLoading)
			this.view.showLoading();
	},
	
	addItem: function (item) {
		this.items[this.items.length] = item;
	},
		
	focusRecord: function(recordId) {
		this.view.focusRecordBlock(recordId);
	},
	
	getRenderElem: function() {
		return this.renderElem;
	},
		
	getColumnById: function(name) {
		var column = null;
		for (var i = 0; i < this.columns.length; i++)
			if (this.columns[i].name == name) {
				column = this.columns[i];
				break;
			}
		return column;
	},
		
	setSortingColumn: function(columnId, direction) {
		var column = this.getColumnById(columnId);
		if (!column)
			return false;
		var realColumnName = (column.realSorting) ? column.realSorting : column.name;
		
		if (this.config.sortingToCookie)
			setCookie(this.id + '-sorting', realColumnName + ":" + direction);
		this.currentSorting = {column: columnId, direction: direction};
		if (this.pager)
			this.pager.resetPage();
		this.store.setSorting(realColumnName, direction);
		this.store.load ();
	},
		
	render: function() {
		this.renderData();
		this.container.resize();
	},
		
	resize: function() {
		this.container.resize();
	},
		
	renderData: function() {
		this.checkboxes = new Array();
		if (this.view) {
			this.view.render();
		}
		if (this.pager)
			this.pager.render();
		
		this.fireEvent("afterRender");
	},
		
	reload: function() {
		this.store.load();
	},
		
	setViewMode: function (viewMode) {
		if (this.currentViewMode == viewMode)
			return;
		
		var view = null;
		if (this.views[viewMode] != null) {
			view = this.views[viewMode];
		} else {
			view = this.createView(viewMode);
			this.views[viewMode] = view;
		}
		
		this.currentViewMode = viewMode;
		this.setView(view);
		this.renderData();
	},

	getCommonCheckbox: function() {
		var checkbox = createElem("input");
		checkbox.setAttribute("type", "checkbox");
		checkbox.table = this;
		checkbox.toggleAll = function(){
			var allChecked = true;
			for (var checkboxId in this.table.checkboxes) {
				var childCheckbox = this.table.checkboxes[checkboxId];
				if (!childCheckbox.checked)
					allChecked = false;
			}
			var needCheckAll = !allChecked;
			for (var checkboxId in this.table.checkboxes)
				this.table.checkboxes[checkboxId].checked = needCheckAll;
			this.checked = needCheckAll;
		}
		checkbox.onclick = function() {this.toggleAll()};
		return checkbox;
	},

	updateItemBlock: function(block, record) {
		if (this.config.selection) {
			var selectorPlace = null;
			var sampObjects = block.getElementsByTagName("SAMP");
			for (var i = 0; i < sampObjects.length; i++)
	  		if (sampObjects[i].className=="selector")
	  			selectorPlace = sampObjects[i];
	  	if (selectorPlace) {
	  		var checkbox = document.createElement("input");
	  		checkbox.setAttribute("type", "checkbox");
	    	checkbox.className = "wbs-table-checkbox";
	    	checkbox.record = record;
	    	this.checkboxes.push(checkbox);
	    	selectorPlace.parentNode.replaceChild(checkbox, selectorPlace);
	  	}
		}
	},

	refreshRecordBlock: function (recordId) {
		var record = null;
		for (var i= 0; i < this.store.records.length; i++) {
			if (this.store.records[i].id == recordId) {
				record = this.store.records[i];
				break;
			}
		}
		this.view.refreshRecordBlock (recordId, record);
	},

	openSortingMenu: function(e, column) {
		var table = this;
		var typesLabels = {
			"string" : ["A-Z", "Z-A"],
			"int" : ["1-999", "999-1"],
			"date" : ["1-31", "31-1"]
		};
		var labels = (column.type && typesLabels[column.type]) ? typesLabels[column.type] : typesLabels["string"];
		
		var menu = new SortingMenu({items: [
			{label: labels[0], onClick: function() {table.setSortingColumn(column.name, "asc")}, iconCls: "sorting-menu-asc"},
			{label: labels[1], onClick: function() {table.setSortingColumn(column.name, "desc")}, iconCls: "sorting-menu-desc"}
		]});
		menu.show(e);
	},

	createSortLabel: function (column, parentEl, labelValueEl) {
		var sortEl = createDiv("sort-header");
		
		sortEl.columnId = column.name;
		
		var label = createElem("label");
		if (labelValueEl)
			label.appendChild(labelValueEl);
		else
			label.innerHTML = column.label;
		if (label.innerHTML.length == 0)
			sortEl.className = "sort-header-empty";
		sortEl.appendChild(label);
		
		var sortingCallback = (column.sortingMenu) ?
			function(e) {table.labelClick = true ; table.openSortingMenu(e, column);} :
			function(e) {if (table.labelClick) {table.labelClick = false; return; }; table.setSortingColumn(column.name, (table.currentSorting.direction == "asc") ? "desc" : "asc")};
		
		addHandler(sortEl, "click", sortingCallback);
		
		addEmptyImg(sortEl);
		
		//sortEl.innerHTML += "<img src='img/lock.gif'>";
		
		var table = this;
		if (column.name == this.currentSorting.column) {
			addClass(sortEl, "current-" + this.currentSorting.direction);
			//addHandler(sortEl, "click", function(e) {if (table.labelClick) {table.labelClick = false; return; }; table.setSortingColumn(column.name, (table.currentSorting.direction == "asc") ? "desc" : "asc")});
		}
					
		parentEl.appendChild(sortEl);
	},

	getSortingColumns: function() {
		var result = new Array ();
		for (var i = 0; i < this.columns.length; i++) {
			if (this.columns[i] && this.columns[i].sorting)
				result.push(this.columns[i]);
		}
		return result;
	},
		
	getNoRecordsMessage: function() {
		return "<no records>";
	},
		
	createRecordsList: function() {
		return new WbsRecordsList();
	},
		
	getSelectedRecords: function() {
		var recordsList = this.createRecordsList();
		for (var checkboxId in this.checkboxes) {
			if (this.checkboxes[checkboxId].checked && this.checkboxes[checkboxId].record) {
				recordsList.add(this.checkboxes[checkboxId].record);
			}
		}
		return recordsList;
	}
});


SortingMenu = newClass (WbsPopmenu, {
	constructor: function(config) {
		config.withImages = true;
		this.superclass().constructor.call(this, config);
	}	
});