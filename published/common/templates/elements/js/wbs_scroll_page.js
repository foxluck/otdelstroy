function WbsScrollPage (config) {
	this.config = config;
	this.contentElem = document.getElementById(config.contentElemId);
	this.contentWrapperElem = createDiv("wbs-scroll-page-content");
	this.wrapperElem = document.getElementById(config.wrapperElemId);
	this.elem = createDiv("wbs-scroll-page");
	this.childElems = new Array ();
	this.items = new Array ();
	
	var scrollPage = this;
	
	this.addChildElem = function(childElem) {
		this.elem.appendChild(childElem);
		this.childElems[this.childElems.length] = childElem;
	}
	
	this.buildPage = function () {
		if (this.config.header) {
			this.headerElem = createDiv("wbs-header");
			this.headerElem.innerHTML = "&nbsp;";
			this.addChildElem(this.headerElem);
		}		
		
		this.contentWrapperElem.appendChild(this.contentElem);
		this.addChildElem(this.contentWrapperElem);
		
		if (this.config.footer) {
			this.footerElem = createDiv("wbs-footer");
			this.footerElem.innerHTML = "&nbsp;";
			this.addChildElem(this.footerElem);
		}
		
		this.wrapperElem.appendChild(this.elem);
	}
	
	this.addItem = function (item) {
		this.items[this.items.length] = item;
	}
	
	this.resize = function () {
		var minusHeight = 0;
		for (var i = 0; i < scrollPage.childElems.length; i++) {
			var childElem = scrollPage.childElems[i];
			if (childElem == scrollPage.contentWrapperElem)
				continue;
			minusHeight += childElem.offsetHeight;
		}
		scrollPage.contentElem.style.height = (scrollPage.wrapperElem.offsetHeight - minusHeight) + "px";
	}
	
	this.render = function() {
		for (var i = 0; i < this.items.length; i++)
			this.items[i].render();
		this.resize();
	}
	
	this.buildPage();
	addHandler(window,"resize",this.resize,false);
}