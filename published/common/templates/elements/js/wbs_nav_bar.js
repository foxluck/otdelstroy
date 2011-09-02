WbsNavBar = newClass(WbsObservable, {
	constructor: function(config) {
		
		this.superclass().constructor.call(this);		
		this.addEvents({
			"resize" : 1,
			"horizontalResize" : 1,
			"blockActivated" : 1			
		});
		
		this.config = config;
		this.collapsed = false;
		this.contentElem = document.getElementById(config.contentElemId);
		this.wrapElem = this.contentElem.parentNode;
		this.activeBlock = null;
		
		/*if (config.expanderElemId) {
			this.expanderElem = $(config.expanderElemId);
			if (this.expanderElem) {
				var a = createElem("a");
				a.href = "javascript:void(0)";
				a.innerHTML = "Show folders";
				a.onclick = this.expand.bind(this);
				this.expanderElem.appendChild(a);
			}			
		}*/
		
		var resizer = new Ext.Resizable (this.wrapElem,  {handles: 'e', pinned: false,  disableTrackOver : true});
		this.resizer = resizer;
		resizer.on("resize", function(resizer, width) {
			this.wrapElem.style.height = "100%";
			this.saveSize(width);
			this.fireEvent("horizontalResize");
		}, this);
		
		var btnCollapse = createDiv("btn-collapse");
		this.contentElem.insertBefore(btnCollapse, this.contentElem.firstChild);
		
		addHandler(btnCollapse, "click", function() {
			this.collapse();
		}, this);
		
		var btnExpand = createDiv("btn-expand");
		this.contentElem.insertBefore(btnExpand, this.contentElem.firstChild);
		
		addHandler(btnExpand, "click", function() {
			this.expand();
		}, this);
		
		this.blocks = new Array ();	
		this.blocksIds = {};
		
		for (var i = 0; i < this.contentElem.childNodes.length; i++) {
			var node = this.contentElem.childNodes[i];
			if (node.className == "acc-block") {
				var block = new WbsNavBarBlock({
					navBar: this,				
					elem: node
				});
				this.blocks[this.blocks.length] = block;
				this.blocksIds[block.id] = block;
			}
		}
		
		this.closePanel = createDiv("close-panel");
		this.contentElem.insertBefore(this.closePanel, this.contentElem.firstChild);
		this.closePanel.onclick = this.collapse.bind(this);
		
		addHandler(window,"resize",this.resize,this);
		this.setActiveBlock(this.blocks[0]);
		this.resize();
		
		if (this.config.saveSize) {
			var savedSize = getCookie("navbar-" + this.config.id);
			if (savedSize == -1)
				this.collapse();
			else if (savedSize)
				resizer.resizeTo(savedSize);
		}
	},
	
	collapse: function() {
		this.collapsed = true;
		this.resizer.resizeTo(20);
		addClass(this.contentElem, "collapsed");
		this.resizer.east.el.hide();
		/*if (this.expanderElem)
			addClass(this.expanderElem, "visible");*/
	},
	
	expand: function() {
		this.collapsed = false;
		removeClass(this.contentElem, "collapsed");
		this.resizer.resizeTo(200);
		//if (Ext.isIE)
			//resizer.resizeElement();
		/*if (this.expanderElem)
			removeClass(this.expanderElem, "visible");*/
		this.resizer.east.el.show();
		this.resize();
	},
	
	resize: function () {
		var totalHeight = this.wrapElem.offsetHeight;
		var blocksTotalHeight = 0;
		for (var i = 0; i < this.blocks.length; i++) {
			var block= this.blocks[i];
			if (block == this.activeBlock)
				continue;
			blocksTotalHeight += block.getHeight();
		}
		var activeBlockHeight = totalHeight - blocksTotalHeight - 4;
		this.activeBlock.setHeight (activeBlockHeight);
		this.fireEvent("resize");
	},
	
	
	getElem: function() {
		return this.contentElem;
	},
		
	getBlock: function(id) {
		return this.blocksIds[id];
	},
	
	setActiveBlock: function (block) {
		if (typeof block == "string")
			block = this.blocksIds[block];
		if (this.activeBlock && this.activeBlock != block)
			this.activeBlock.setActive(false);
		else if (this.activeBlock == block)
			return;
			
		this.activeBlock = block;
		this.activeBlock.setActive(true);
		this.resize();
		this.fireEvent("blockActivated", block);
	},
	
	saveSize: function(size) {
		var value = (this.collapsed) ? -1 : size;
		if (this.config.id)
			setCookie("navbar-" + this.config.id, value);
	}
});



WbsNavBarBlock = newClass(WbsObservable, {
	constructor: function (config) {
		this.navBar = config.navBar;
		this.elem = config.elem;
		this.id = config.elem.id;
		this.contentEl = null;
		this.titleEl = null;
		
		this.build();
	},
		
	getContentElem: function() {
		return this.contentEl;		
	},
	
	build: function() {
		for (var i = 0; i < this.elem.childNodes.length; i++) {
			var node = this.elem.childNodes[i];
			if (node.className == "title")
				this.titleEl = node;
			if (node.className == "content")
				this.contentEl = node;
		}
		if (this.titleEl) {
			addHandler(this.titleEl, "click", function() {
				this.activate();
			}, this);
		}
	},
	
	setHeight: function(height) {
		this.elem.style.height = height;
		if (height != "auto" && this.contentEl) {
			var changeHeight = 0;
			if (this.titleEl)
				changeHeight = this.titleEl.offsetHeight;
			this.contentEl.style.height = (height - changeHeight) + "px";
		}
	},
	
	getHeight: function() {
		return this.elem.offsetHeight;
	},
	
	activate: function() {
		this.navBar.setActiveBlock(this);		
	},
	
	deactivate: function() {
		
	},
	
	setActive: function(value) {
		if (value) 
			addClass(this.elem, "active");
		else {
			this.elem.style.height = "auto";
			removeClass(this.elem, "active");
		}
	}
});