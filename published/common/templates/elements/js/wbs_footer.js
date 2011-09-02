function WbsFooter (config) {
	this.config = config;
	this.contentElem = document.getElementById(config.contentElemId);
	this.wrapElem = this.contentElem.parentNode;
	
	var footer = this;
	this.resize = function () {
		footer.contentElem.style.height = (footer.wrapElem.offsetHeight - footer.footerElem.offsetHeight) + "px";
	}
	
	this.getElem = function() {
		return this.footerElem;
	}
	
	this.footerElem = document.createElement("div");
	this.footerElem.className = config.cls;
	this.footerElem.innerHTML = "&nbsp;";
	
	this.wrapElem.appendChild(this.footerElem);
	this.resize();
	
	addHandler(window,"resize",this.resize,false);
}