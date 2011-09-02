var GenericItemsSelector = function (config) {
	this.idFrom = config.idFrom;
	this.idTo = config.idTo;
	this.items = (config.items) ? config.items : new Array ();
	
	this.fromSelect = null;
	this.toSelect = null;
	this.btnLeftId = config.btnLeftId;
	this.btnRightId = config.btnRightId;
	this.config = config;
}
	
GenericItemsSelector.prototype = {
	initialize: function () {
		
		var fromSelect = document.getElementById (this.idFrom);
		var toSelect = document.getElementById (this.idTo);
		
		var btnLeft = document.getElementById (this.btnLeftId);
		var btnRight = document.getElementById (this.btnRightId);
		
		var itemsSelector = this;
		
		btnLeft.onclick = function () {
			itemsSelector.moveOptions (itemsSelector.fromSelect, itemsSelector.toSelect);
		}
		
		btnRight.onclick = function () {
			itemsSelector.moveOptions (itemsSelector.toSelect, itemsSelector.fromSelect);
		}
		
		fromSelect.ondblclick = function () {
			itemsSelector.moveOptions (itemsSelector.fromSelect, itemsSelector.toSelect);
		}
		
		toSelect.ondblclick = function () {
			itemsSelector.moveOptions (itemsSelector.toSelect, itemsSelector.fromSelect);
		}
		
		this.toSelect = toSelect;
		this.fromSelect = fromSelect;
		
		this.restoreValues();
		
		this.initialized = true;
	},
		
	loadItems: function (items) {
		this.items = items;
	},
	
	addOption: function(theSel, theText, theValue) {
	  var newOpt = new Option(theText, theValue);
	  var selLength = theSel.length;
	  theSel.options[selLength] = newOpt;
	},

	deleteOption: function(theSel, theIndex) { 
	  var selLength = theSel.length;
	  if(selLength>0)
	  {
	    theSel.options[theIndex] = null;
	  }
	},

	moveOptions: function(theSelFrom, theSelTo)
	{		  
	  var selLength = theSelFrom.length;
	  var selectedText = new Array();
	  var selectedValues = new Array();
	  var selectedCount = 0;
	  
	  var i;
	  
	  for(i=selLength-1; i>=0; i--) {
	    if(theSelFrom.options[i].selected)
	    {
	      selectedText[selectedCount] = theSelFrom.options[i].text;
	      selectedValues[selectedCount] = theSelFrom.options[i].value;
	      this.deleteOption(theSelFrom, i);
	      selectedCount++;
	    }
	  }
	  
	  for(i=selectedCount-1; i>=0; i--) {
	    this.addOption(theSelTo, selectedText[i], selectedValues[i]);
	  }
	},
	
	restoreValues: function (values) {
		this.toSelect.options.length = 0;
		this.fromSelect.options.length = 0
		for (var key in this.items) {
		//for (var i = 0; i < this.items.length; i++) {
			//var item = this.items[i];
			var item  = this.items[key];
			this.fromSelect.options[this.fromSelect.options.length] = new Option(item[1], item[0]);
		}
	},
	
	setToValue: function (value) {
		this.restoreValues();
		if (!value || value.length < 1)
			return;
		var ids = value.split(",");
		for (var j =0; j < ids.length; j++) {
			var id = ids[j];
			for (var i = 0; i < this.fromSelect.options.length; i++) {
				var opt = this.fromSelect.options[i];
				if (opt.value == id) {
					this.toSelect.options[this.toSelect.options.length] = new Option(opt.text, opt.value);
					this.fromSelect.remove(i);
				}
			}
		}
	},
	
	
	getToValue: function() {
		var result = new Array ();
		for (i = 0; i < this.toSelect.options.length; i++) {
			result.push (this.toSelect.options[i].value);
		}
		return result;
	}
}