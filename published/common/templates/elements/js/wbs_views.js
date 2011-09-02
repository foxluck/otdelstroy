WbsView = newClass(null, {
	constructor: function(table, config) {
		this.config = config;
		this.table = table;
	},
	
	outputRendered: function(outputObj) {
		var renderedElem = this.table.getRenderElem();
		
		if (this.loading) {
			outputObj = this.renderLoading();
			this.loading = false;
		} else if (!this.table.getStore() || !this.table.getStore().hasRecords()) {
			delete outputObj;
			var outputObj = this.renderNoRecords();
		}
		
		if (renderedElem.listBody)
			renderedElem.replaceChild(outputObj, renderedElem.listBody);
		else
			renderedElem.appendChild(outputObj);
		
		renderedElem.listBody = outputObj;
	},
	
	getClassName: function() {
		return this.modeName;
	},
	
	selectionEnabled: function() {
		return this.table.config.selection;
	},
		
	renderLoading: function() {
		var block = createDiv("wbs-loading");
		block.innerHTML = "Loading&nbsp; <div class='wbs-loading-icon'></div>";
		return block;
	},
	
	renderNoRecords: function () {
		var block = createDiv("no-records");
		block.appendChild(document.createTextNode(this.table.getNoRecordsMessage()));
		return block;
	},
		
	showLoading: function() {
		this.loading = true;
		this.outputRendered();
	},		
		
	getCellHeaderValueElem: function(column) {
		return document.createTextNode(column.label);
	}
});


/********
	“абличный вид (дл€ вывода используетс€ table)
	¬ подклассах дл€ кастомизации следует править методы getCellValue и getCellHeaderValueElem
	 олонки пока берет из WbsTable, это нужно немного поправить
********/
WbsColumnsView = newClass(WbsView, {
	constructor: function (table, config) {
		this.modeName = "columns";
		this.superclass().constructor.call(this, table, config);
	},
	
	render: function() {
		var tableElem = document.createElement("table");
		tableElem.className = this.getClassName();
		this.trElems = new Array ();
		
		if (this.config.header != false) {
			var headerElem = this.renderHeader(tableElem);
			tableElem.appendChild(headerElem);
		}
		
		var tbodyElem = document.createElement("tbody");
		tableElem.appendChild(tbodyElem);
		
		for (var i = 0; i < this.table.getStore().getLength(); i++) {
			var record = this.table.getStore().getRecordByIndex(i);
			
			var trElem = document.createElement("tr");
			trElem.className = "item " + ((i % 2) ? " odd" : " even");
			this.renderRow (trElem, record);
			this.trElems[record.id] = trElem;
			tbodyElem.appendChild(trElem);
		}
		
		this.outputRendered(tableElem);
	},
	
	refreshRecordBlock: function(recordId, record) {
		var block = this.getRecordBlock(recordId);
		clearNode(block);
		
		this.renderRow (block, record);
	},
		
	getRecordBlock: function(recordId) {
		return this.trElems[recordId];
	},
	
	getCellValue: function(column, record) {
		return record[column.name];  	
  },
  
  getCellHeaderValueElem: function(column) {
		return document.createTextNode(column.label);
  },
	
	renderHeader: function (tableElem) {
		var theadElem = document.createElement("thead");
		var trElem = document.createElement("tr");
		
		if (this.selectionEnabled()) {
			var td = document.createElement("th");
			td.appendChild(this.table.getCommonCheckbox());
			td.style.width = "10px";
			trElem.appendChild(td);
		}
		
		var columns = this.table.getColumns();
		for (var i = 0; i < columns.length; i++) {
			var column = columns[i];
			var td = document.createElement("th");
			if (column.cls)
				td.className = column.cls;
			if (column.width) {
				td.style.width = (!isNaN(column.width)) ? column.width + "px" : column.width;
			}
			//addClass (td, "sort-column");
			var labelValueEl = this.getCellHeaderValueElem(column);
			if (column.sorting)
				this.table.createSortLabel(column, td, labelValueEl);
			else
				td.appendChild(labelValueEl);				
			//td.innerHTML = column.label;
			trElem.appendChild(td);
		}
		
		theadElem.appendChild(trElem);
		
		return theadElem;
	},
	
	renderRow: function(trElem, record) {
		if (this.selectionEnabled()) {
			var td = document.createElement("td");
			td.style.width = "10px";
			td.innerHTML = "<SAMP class='selector'></SAMP>";
			trElem.appendChild(td);
		}
		var columns = this.table.getColumns();
		for (var i = 0; i < columns.length; i++) {
			var column = columns[i];
			var td = document.createElement("td");
			if (column.width)
				td.style.width = (!isNaN(column.width)) ? column.width + "px" : column.width;
			if (column.cls)
				td.className = column.cls;
			if (column.type == "date")
				addClass(td, "date-cell");
			td.innerHTML = this.getCellValue(column, record);
			trElem.appendChild(td);
		}
		this.table.updateItemBlock(trElem, record);
	}
});



/********
	ќбщий класс дл€ видов основанных на div'ах, кажда€ запись - отдельный div
	в подклассе нужно создать метод buildRecordBlock	
********/
WbsDivView = newClass(WbsView, {
	
	render: function () {
		var itemsListObj = document.createElement("div");
		itemsListObj.className = this.getClassName();
		this.itemElems = new Array ();
		
		if (this.config.header) {
			this.headerElem = createDiv("header");
			if (this.selectionEnabled()) {
				this.headerElem.appendChild(this.table.getCommonCheckbox());
				this.createSortingPanel(this.headerElem);
			}			
			itemsListObj.appendChild(this.headerElem);
		}
		
		for (var i = 0; i < this.table.getStore().getLength(); i++) {
			var record = this.table.getStore().getRecordByIndex(i);
			
			var block = document.createElement("div");
			block.className = "item";
			this.buildRecordBlock (block, record);
			this.table.updateItemBlock(block, record);
			this.itemElems[record.id] = block;
			
			itemsListObj.appendChild(block);
		}
		this.outputRendered(itemsListObj);
	},
	
	createSortingPanel: function(headerElem) {
		var sortingPanel = createDiv("sorting-panel");
		sortingPanel.innerHTML = "<div class='label'>" + WbsLocale.getCommonStr("lbl_table_sorting") + ": </label>";
		var sortingColumns = this.table.getSortingColumns();
		for (var i = 0; i < sortingColumns.length; i++) {
			var column = sortingColumns[i];
			var sortColumn = createDiv("sort-column");
			if (i == sortingColumns.length - 1)
				addClass(sortColumn, "last");
			this.table.createSortLabel(column, sortColumn);
			sortingPanel.appendChild(sortColumn);
		}
		headerElem.insertBefore(sortingPanel, headerElem.firstChild);
	},
	
	/********
		ћетод должен заполнить block на основе record. Ќужно определ€ть в подклассах	
	********/
	buildRecordBlock: function(block, record) {
		throw "Not implemented buildRecordBlock in the view";
	},
	
	refreshRecordBlock: function(recordId, record) {
		var block = this.getRecordBlock(recordId);
		block.innerHTML = "";
		this.buildRecordBlock (block, record);
		this.table.updateItemBlock(block, record);
	},
		
	getRecordBlock: function(recordId) {
		return this.itemElems[recordId];
	}
});


/********
	ќбщий класс дл€ видов представл€ющих простой список, наследуетс€ от WbsDivView
********/
WbsListView = newClass(WbsDivView, {
	constructor: function (table, config) {
		this.superclass().constructor.call(this, table, config); 
		this.modeName = "list";
	}
});


/********
	ќбщий класс дл€ видов представл€ющих записи в виде карточек (tiles), наследуетс€ от WbsDivView
********/
WbsTileView = newClass(WbsDivView, {
	constructor: function (table, config) {
		this.superclass().constructor.call(this, table, config); 
		this.modeName = "tile";
	}
});