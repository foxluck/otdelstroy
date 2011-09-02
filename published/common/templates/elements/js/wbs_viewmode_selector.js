WbsViewmodeSelector = newClass(WbsObservable, {
	constructor: function (table, config) {
		this.renderElem = config.elem;
		this.rendered = false;
		this.table = table;
		if (config.modes) this.modes = config.modes;
		else this.modes = new Array ("columns", "list", "detail", "tile");
		
		modeSelector = this;
		this.modesElems = {};
		this.onclick = null;
		
		
		this.superclass().constructor.call(this);
		
		this.addEvents ({
			"viewmodeChanged"	: true
		});
	},
	
	render: function() {
		this.container = document.createElement("div");
		this.container.className = "viewmode-selector";
		
		for (var i = 0; i < this.modes.length; i++) {
			var elem = this.createModeElem(this.modes[i]);
			this.container.appendChild(elem);
		}
		this.renderElem.appendChild(this.container);
		this.rendered = true;
	},
	
	setSelectedElem: function(modeElem) {
		if (this.selectedModeElem != null) {
			removeClass(this.selectedModeElem, "selected");
			this.selectedModeElem.img.src = this.getModeImgSrc(this.selectedModeElem.mode, false);
		}
		if (!modeElem)
			return;
		addClass(modeElem, "selected");
		modeElem.img.src = this.getModeImgSrc(modeElem.mode, true);
		this.selectedModeElem = modeElem;
	},
	
	getModeImgSrc: function(mode, selected) {
		var addPart = (selected) ? "_on" : "";			
		return WbsCommon.getPublishedUrl("common/templates/img/viewmode_" + mode + addPart + ".gif");
	},
		
	isRendered: function() {
		return this.rendered;
	},
		
	selectMode: function(mode) {
		this.setMode(mode);
		this.fireEvent("viewmodeChanged", mode);
	},
		
	setMode: function(mode) {
		this.selectedMode = mode;
		if (this.isRendered())
			this.setSelectedElem(this.modesElems[mode]);
		if (this.table)
			this.table.setViewMode(mode);
		if (this.onClick)
			this.onClick(this, mode);
	},
		
	setTable: function(table) {
		this.table = table;
		this.setMode(this.selectedMode);
	},
	
	
	createModeElem: function(mode) {
		var modeElem = createDiv("mode-elem");
		
		this.modesElems[mode] = modeElem;
		modeElem.mode = mode;
		modeElem.img = createElem("img");
		modeElem.img.src = this.getModeImgSrc(mode);
		modeElem.appendChild(modeElem.img);
		if (mode == this.selectedMode) {
			this.setSelectedElem(modeElem);
		}
		
		var table = this.table;
		modeElem.onclick = function () {
			modeSelector.selectMode(mode);
		};
		return modeElem;
	}
});
