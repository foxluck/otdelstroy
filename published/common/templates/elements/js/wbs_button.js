WbsButton = newClass (null, {
	constructor: function(config) {
		this.config = config;
		this.disabled = false;
		if (!this.cls)
			this.cls = "wbs-btn";
		this.render();
	},
	
	build: function(mainElem, advElem) {
		this.btnElem.appendChild(advElem);
		this.btnElem.appendChild(mainElem);
	},
		
	setDisplayed: function (value) {
		this.btnElem.style.display = (value != false) ? "" : "none";
	},
	
	render: function() {
		var contentElem = this.getContentElem();
		
		var btnElem = createDiv(this.cls);
		this.btnElem = btnElem;
		
		var advElem = createDiv("wbs-btn-adv");
		var mainElem = createElem("a", "wbs-btn-content");
		mainElem.setAttribute("href", "javascript:void(0)");
		
		if (this.config.iconUrl) {
			mainElem.style.backgroundImage = "URL(" + this.config.iconUrl + ")";
			addClass(mainElem, "wbs-btn-withicon");
		}
		
		if (this.config.advIconUrl) {
			advElem.style.backgroundImage = "URL(" + this.config.advIconUrl + ")";
			addClass(advElem, "wbs-btn-left-icon");
		}
		
		var label = this.config.label;
		if (!label && contentElem.value)
			label = contentElem.value;
		if (!label)
			label = "";
		mainElem.innerHTML = label;
		this.build(mainElem, advElem);
		
		if(this.config.onClick)
			addHandler(btnElem, "click", this.onClick, this);
		
		contentElem.parentNode.replaceChild(btnElem, contentElem);
	},
	
	getContentElem: function() {
		var contentElem = document.getElementById(this.config.el);
		if (!contentElem )
			throw "No content element for WbsButton: " + this.config.el;
		return contentElem;		
	},
		
	getElem: function() {
		return this.btnElem;
	},
		
	onClick: function(e) {
		if (this.disabled)
			return false;
		if (this.config.onClick)
			this.config.onClick(e);
	},
		
	setDisabled: function(value) {
		this.disabled = value;
		if (value)
			addClass(this.btnElem, "wbs-btn-disabled");
		else
			removeClass(this.btnElem, "wbs-btn-disabled");
	},
		
	disable: function() {
		this.setDisabled(true);
	}, 
		
	enable: function() {
		this.setDisabled(false);
	}
});

WbsLinkButton = newClass(WbsButton, {
	constructor: function (config) {
		this.cls = "wbs-link-btn";
		this.superclass().constructor.call(this,config);	
	},
	
	build: function(mainElem, advElem) {
		this.btnElem.appendChild(mainElem);
	}	
});

WbsMenuButton = newClass(WbsButton, {
	constructor: function (config) {
		this.cls = "wbs-menu-btn";
		this.superclass().constructor.call(this,config);	
		
		if (this.config.getMenu)
			addHandler(this.btnElem, "click", function(e) {
				var getMenu = (this.config.scope) ? this.config.getMenu.bind(this.config.scope) : this.config.getMenu;
					
				var menu = getMenu(e);
				if (menu) {
					menu.show(e);
				}
			}
			, this);
	},
	
	build: function(mainElem, advElem) {
		this.btnElem.appendChild(mainElem);
		this.btnElem.appendChild(advElem);
	}
});