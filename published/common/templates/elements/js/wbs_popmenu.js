WbsPopmenu = newClass(WbsPopwindow, {
	constructor: function (config) {
		this.items = new Array ();
		this.superclass().constructor.call(this, config);
	},
		
	render: function() {
		var ul = document.createElement("ul");
		for (var i = 0; i < this.config.items.length; i++) {
			var item = this.config.items[i];
			
			var li = this.createItem(item);
			if (item.id) 
				this.items[item.id] = li;
			else
				this.items[this.items.length] = li;
			
			ul.appendChild(li);
		}
		this.elem.appendChild(ul);
	},
		
	getWindowCls: function() {
		if (this.config.withImages)
			return "wbs-popmenu wbs-popmenu-images";
		else
			return "wbs-popmenu";
	},
	
	getItem: function(id) {
		return this.items[id];
	},
	
	setItems: function(items) {
		this.config.items = items;
	},
	
	refreshItem: function (li, item) {
		var newLi = this.createItem(item);
		if (item.id)
			this.items[item.id] = li;
		li.parentNode.replaceChild(newLi, li);
	},
	
	hideItem: function (id) {
		addClass(this.items[id], "hidden");
	},
	
	showItem: function (id) {
		removeClass(this.items[id], "hidden");
	},
	
	createItem: function(item) {
		var li = document.createElement("li");
		if (item.cls)
			addClass(li, item.cls);
		
		if (item.iconCls) {
			addClass(li, "with-image");
			addClass(li,item.iconCls);
		}
		
		if (item == "-") {
			li.className = "separator";
			li.innerHTML = "<div></div>";
		} else if (item.html) {
			li.innerHTML = item.html;
		} else {
			var anchor = document.createElement("a");
			anchor.href = "javascript:void(0)";
			var text = document.createTextNode(item.label);
			anchor.appendChild(text);
			li.appendChild(anchor);
			
			if (!item.hidden)
				addEmptyImg(li);
		}
		
		if (item.onClick && !item.disabled) {
			li.onclick = function(item) { 
				return function() {
					var scope = item.scope ? item.scope : this;
					var val = item.onClick.bind(scope)();
					if (!item.onClickNoHide)
						this.close();
					return val;
				}; 			
			} (item).bind(this);
		}
		
		li.onmouseover = function() {
			if (!this.disabled)
				addClass(this,"highlight");
		}
		li.onmouseout = function() {
			if (!this.disabled)
				removeClass(this, "highlight");
		}
		
		if(item.hidden) {
			addClass(li, "hidden");
		}
		if(item.disabled) {
			addClass(li, "disabled");
			li.disabled = true;
		}
		li.setText = function(text) {this.innerHTML = text;}
		return li;
	}
});