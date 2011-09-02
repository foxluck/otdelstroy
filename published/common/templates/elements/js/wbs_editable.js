/****
	Config params:
		elem - html-element contains ONLY text to edit 
		clickToEdit - use click event to edit text
		dblClickToEdit - use double click event to edit text
		emptyText - use for view mode if value is empty
		saveHandler - handler for save value
		adjustSize - adjust editor size to elemSizes
***/
WbsEditable = newClass(WbsObservable, {
	value: null, 
	mode: null,
		
	constructor: function (config) {
		this.config = config;
		this.mode = WbsEditable.VIEW_MODE;
		
		// read config
		this.elem = config.elem;
		
		this.superclass().constructor.call(this);
		
		this.addEvents({
			"changeMode": true
		});
	},
		
	setEditMode: function() {
		this.mode = WbsEditable.EDIT_MODE;
		this.render();
		this.editElem.select();
		this.fireEvent("changeMode");
	},
		
	setViewMode: function() {
		this.mode = WbsEditable.VIEW_MODE;
		this.render();
		this.fireEvent("changeMode");
	},
		
	setValue: function(value) {
		this.value = value;
		this.render();
	},
		
	render: function() {
		if (this.mode == WbsEditable.EDIT_MODE) {
			var editElem = this.createEditElem();
			
			clearNode(this.elem);
			
			editElem.setValue(this.value);
			this.elem.appendChild(editElem);
			this.editElem = editElem;
			
			var saveBtn = createElem("input",null, {type: "button", value:WbsLocale.getCommonStr("action_save")});
			addHandler (saveBtn, "click", function() {this.save()}, this);
			//saveBtn.innerHTML = WbsLocale.getCommonStr("action_save");
			
			var cancelBtn = createElem("input",null, {type: "button", value:WbsLocale.getCommonStr("action_cancel")});
			addHandler (cancelBtn, "click", function(e) {this.cancel();}, this);
			//cancelBtn.innerHTML = WbsLocale.getCommonStr("action_cancel");
			
			var linksBlock = createDiv("wbs-editable-links-block");
			linksBlock.appendChild(saveBtn);
			linksBlock.appendChild(cancelBtn);
			
			this.elem.appendChild(linksBlock);
		} else {
			clearNode(this.elem);
			var textValue = (!this.value || this.value.length == 0 && this.config.emptyText) ? this.config.emptyText : this.value;
			
			var valueSpan = createDiv();
			valueSpan.innerHTML = (textValue) ? textValue.htmlSpecialChars() : "";
			
			if (this.config.clickToEdit)
				valueSpan.onclick = function(){ this.setEditMode()}.bind(this);
			
			this.elem.appendChild(valueSpan);
		}
	},
		
	save: function() {
		if (!this.saveHandler)
			throw "No save handler for editable";
		
		var newValue = this.editElem.getValue();
		this.saveHandler(newValue, this.saveSuccess.bind(this), this.saveFailed.bind(this));
	},
		
	saveSuccess: function() {
		this.value = this.editElem.getValue();
		this.setViewMode();
	},
	saveFailed: function() {
		
	},
	
		
	cancel: function() {
		this.setViewMode();
	}
});
WbsEditable.VIEW_MODE = "view";
WbsEditable.EDIT_MODE = "edit";


WbsEditableText = newClass(WbsEditable, {
	constructor: function(config) {
		this.superclass().constructor.call(this, config);
	},
		
	createEditElem: function() {
		var elem = createDiv("wbs-editable-text");
		var textarea = createElem("textarea");
		textarea.value = this.value;
		elem.appendChild(textarea);
		
		if(this.config.adjustSize && this.elem.offsetHeight > 40)
			textarea.style.height = this.elem.offsetHeight - 20 + "px";
		if(this.config.adjustSize)
			textarea.style.width = this.elem.offsetWidth + "px";
			
		
		elem.setValue = function(value) {textarea.value = value;}		
		elem.getValue = function() { return textarea.value; }
		elem.select = function() {textarea.select(); textarea.focus(); }
		
		return elem;
	}	
});

WbsEditableLabel = newClass(WbsEditableText, {
	
	createEditElem: function() {
		var elem = createDiv("wbs-editable-label");
		var input = createElem("input");
		input.value = this.value;
		elem.appendChild(input);
		
		if(this.config.adjustSize)
			input.style.width = this.elem.offsetWidth + "px";
		
		elem.setValue = function(value) {input.value = value;}
		elem.getValue = function() { return input.value; }
		elem.select = function() {input.select(); input.focus(); }
		
		return elem;
	}
});