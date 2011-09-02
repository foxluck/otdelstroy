WbsPager = newClass(null,{
	constructor: function(config) {
		this.config = config;
		this.table = this.config.table;
		
		this.itemsOnPage = 10;
		this.currentPage = 0;
		
		if (this.getStore())
			this.getStore().setOffset(0,this.itemsOnPage);
	},
		
	setItemsOnPage: function(value) {
		if (!value)
			value = 10;
		this.itemsOnPage = value;
		this.resetPage();
	},
	
	render: function() {
		var renderElem = this.config.elem;
		renderElem.innerHTML = "&nbsp;";
		//renderElem.innerHTML = store.getLength() + " files from " + store.getTotal() + " on ";
		
		if (!this.getStore())
			return;
		
		var listEl = createElem ("ul", "pages-list");
		var pagesCount = Math.ceil(this.getStore().getTotal() / this.itemsOnPage);
		labelEl = createElem("li", "pages-label");
		labelEl.innerHTML = "<span class='records-label'>" + (this.config.nameElements ? this.config.nameElements : "Records") + ": " + this.getStore().getTotal() + "</span> ";
		if (pagesCount > 0)
			labelEl.innerHTML += WbsLocale.getCommonStr("lbl_table_pages") + ": ";
		listEl.appendChild(labelEl);
		
		var maxShowPages = 10;
		var showPagesDelta = 4;
		var separatorOuted = false;
		
		for (var i = 0; i < pagesCount; i++) {
			var pageEl = createElem("li", "page-item");
			if (i == this.currentPage)
				addClass(pageEl, "selected");				
			pageEl.innerHTML = (i + 1);
			pageEl.pageNo = i;
			
			if (!(pagesCount < maxShowPages || Math.abs(i - this.currentPage) < showPagesDelta || i == 0 || i == pagesCount - 1)) {
				if (!separatorOuted) {
					separatorEl = createElem("li", "separator");
					separatorEl.innerHTML = "...";
					listEl.appendChild(separatorEl);
					separatorOuted = true;
				}
				continue;
			}
			separatorOuted = false;
			
			addHandler(pageEl, "click", function(pageEl, pageNo) {
				return function() {pageEl, this.onPageClick(pageEl, pageNo);}
			}(pageEl, i), this);
			
			listEl.appendChild(pageEl);
		}
		renderElem.insertBefore(listEl, renderElem.firstChild);
	},
	
	resetPage: function(pageNo) {
		this.currentPage = 0;
		this.getStore().setOffset(0, this.itemsOnPage);
	},
	
	getStore: function() {
		if (!this.table)
			return false;
		return this.table.getStore();
	},
	
	onPageClick: function(pageEl,pageNo) {
		this.currentPage = pageNo;
		WbsCommon.showLoading(pageEl);
		this.getStore().setOffset(pageNo * this.itemsOnPage, this.itemsOnPage);
		this.getStore().load();
	}
});