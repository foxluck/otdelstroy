WbsDlg = newClass(WbsObservable, {
	constructor: function (config) {
		if (!config)
			config = this.getDefaultConfig();
			
		this.config = config;
		var wnd = this;
		this.buttons = new Array ();
		this.innerContentElem = null;
		
		this.superclass.constructor.call(this);
	},
	
	render: function() {
		var windowElem = document.createElement("div");
		windowElem.className = "wbs-dlg";
		if (this.config.cls)
			addClass(windowElem, this.config.cls);
		windowElem.style.display = "none";
		
		if (this.config.width) {
			windowElem.style.width = this.config.width + "px";
		}
		if (this.config.height)
			windowElem.style.height = this.config.height + "px";
		
		this.createHeader(windowElem);
		this.createContent(windowElem);
		this.createFooter(windowElem);
			
		document.body.appendChild(windowElem);
		return windowElem;
	},
	
	setPosition: function() {
		var docSize = getDocumentSize();
		var windowElem = this.getWindowElem();
		windowElem.style.top = (docSize.height - windowElem.offsetHeight)/2 + "px";
		windowElem.style.left = (docSize.width - windowElem.offsetWidth)/2 + "px";
	},
	
	getWindowElem: function() {
		if (!this.windowElem) {
			this.windowElem = this.render();
		}
		return this.windowElem;
	},
	
	show: function() {
		var windowElem = this.getWindowElem();
		windowElem.style.display = "block";
		this.showFade();
		this.setSizes();
		this.setPosition();
		if (this.onAfterShow)
			this.onAfterShow();
	},
	hide: function() {
		var windowElem = this.getWindowElem();
		windowElem.style.display = "none";
		this.hideFade();
	},
		
	close: function() {
		this.hideFade();
		var windowElem = this.getWindowElem();		
		windowElem.parentNode.removeChild(windowElem);
		delete windowElem;
	},
		
	showLoading: function(label) {
		if (!label)
			label = "Loading";
		if (!this.loadingElem) {
			this.loadingElem = createDiv("wbs-absolute-full-size");
			this.loadingElem.innerHTML = "<span class='wbs-loading'>" + label + " <div class='wbs-loading-icon'>&nbsp;</div></span>";
			this.getContentElem().appendChild(this.loadingElem);
		}
	},
		
	hideLoading: function() {
		if (this.loadingElem) {
			this.loadingElem.parentNode.removeChild(this.loadingElem);
			this.loadingElem = null;
		}
	},
		
	
	getDefaultConfig: function() {
		return {};
	},
	
	createHeader: function (windowElem) {
		this.headerElem = createDiv("wbs-dlg-header");
		
		if (this.config.title) {
			var label = createDiv("label");
			label.innerHTML = this.config.title;
			this.headerElem.appendChild(label);
		}
		
		if(!this.config.hideCloseBtn)
			this.createCloseButton(this.headerElem);
		windowElem.appendChild(this.headerElem);
	},
	
	createContent: function(windowElem) {
		this.contentElem = createDiv("wbs-dlg-content");
		
		if (this.config.contentElemId) {
			var content = document.getElementById(this.config.contentElemId);
			this.contentElem.appendChild(content);
			content.style.display = "block";
		} else {
			this.innerContentElem = createDiv("wbs-dlg-content-inner");
			this.contentElem.appendChild(this.innerContentElem);
		}
		
		this.buildContent(this.getContentElem());
		
		windowElem.appendChild(this.contentElem);
	},
		
	buildContent: function(contentElem) {
	},
		
	buildFooter: function (footerElem) {
		if (this.config.buttons) {
			for (var i = 0; i < this.config.buttons.length; i++) {
				var button = this.config.buttons[i];
				var buttonElem = createElem("input", "wbs-dlg-button");
				buttonElem.setAttribute("type", "button");
				buttonElem.value = button.label;
				if (button.onClick)
					buttonElem.onclick = button.onClick;
				if (button.scope)
					buttonElem.onclick = buttonElem.onclick.bind(button.scope);
				footerElem.appendChild(buttonElem);
				if (button.id)
					this.buttons[button.id] = buttonElem;
			}
		}	
	},
		
	getContentElem: function() {
		if (this.innerContentElem)
			return this.innerContentElem;
		else
			return this.contentElem;
	},
		
	createFooter: function (windowElem) {
		this.footerElem = createDiv("wbs-dlg-footer");
		this.buildFooter(this.footerElem);
		windowElem.appendChild(this.footerElem);
	},
	
	setSizes: function() {
		this.contentElem.style.height = (this.getHeight() - this.headerElem.offsetHeight - this.footerElem.offsetHeight) + "px";
	},
	
	getHeight: function() {
		return this.windowElem.offsetHeight;
	},
	
	getWidth: function() {
		return this.windowElem.offsetWidth;
	},
	
	createCloseButton: function (parentElem) {
		var closeBtn = createDiv("close-btn");
		closeBtn.innerHTML = WbsLocale.getCommonStr("action_close");
		closeBtn.onclick = function(){
			if (this.config.closeMode == "close")
				this.close();
			else 
				this.hide();
		}.bind(this);
		parentElem.appendChild(closeBtn);
	},
	
	showFade: function() {
		if (this.fade == null) {
			this.fade = createDiv("black-overlay");
			document.body.appendChild(this.fade);
		}
		this.fade.style.display = "block";
	},
	hideFade: function() {
		if (this.fade)
			this.fade.style.display = "none";
	}
});