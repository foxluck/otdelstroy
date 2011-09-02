var SimpleDialog  = function  (contentDivId, config) {
	this.contentDivId = contentDivId;
	this.initialized = false;
	this.onAfterInit = null;
	this.onBeforeShow = null;
	this.onAfterShow = null;
	
	this.config = { 
    autoTabs:false, shadow:true, closeAction: "hide", 
    width:350, height:180, minWidth:350, minHeight:180,
    resizable: false, resizeHandles: "se", modal: false, collapsible: false
  };
  	
  this.loadConfig(config);
  
	this.dialog = null;
}

SimpleDialog.prototype = {
	loadConfig: function  (config) {
		for (var key in config)
  		this.config[key] = config[key];
	},
	
	showDialog: function (fromElem) {
		if (this.dialog == null)
			this.init ();
		if(this.onBeforeShow)
			this.onBeforeShow ();
		this.dialog.show (fromElem);
		if(this.onAfterShow)
			this.onAfterShow ();
	},
	
	init: function () {
		/*var dlgDom = document.createElement("div");
		dlgDom.id = this.contentDivId + "-dlg";
		dlgDom.className = "PopupDialog";
		document.body.appendChild(dlgDom);*/
		
		var dlgContentDom = document.getElementById(this.contentDivId);
		
		//dlgDom.appendChild(dlgContentDom);
		//dlgContentDom.style.display = "block";
		
		this.config.contentEl = dlgContentDom;
		this.dialog = new Ext.Window(this.config);
		
		if (Ext.isGecko) {
			this.dialog.on("render", function() {
				this.el.dom.style.position = "fixed";
			});	
		}
		
		if (this.onAfterInit)
			this.onAfterInit ();
		this.initialized = true;
	}
}
	


CommonDialog = function (dialogContentId) {
	this.dialog = null;
	this.strings = new Array ();
	this.currentTabNo = 0;
	this.showedTabNo = null;
	this.tabs = new Array ();
	this.prevTab = null;
	this.params = new Array ();
	this.moreButtons = new Array ();
	this.leftButtons = new Array ();
	this.addedButtons = new Array ();
	this.addClass = null;
	this.hasChanges = false;
	this.standartButtons = true;
	this.verticalScroll = false;
	
	this.config = { 
    autoTabs:false, shadow:true,
    width:350, height:180, minWidth:350, minHeight:180,
    	deferredRender: true, closeAction: 'hide',
    resizable: false, resizeHandles: "se", modal: false, collapsible: false
  };
	
	this.onAfterInit = null;
	this.onAfterShow = null;
	this.onBeforeShow = null;
}

CommonDialog.prototype = {

	/***************************************************
	* Show Share dialog
	***************************************************/
	show: function(fromElem, params) {
		if(!this.dialog){
			this.init ();
    }
    
    var handyShow = false;
    if (this.onBeforeShow != null) {
    	handyShow = this.onBeforeShow ();
    }
    
    if (params != null)
    	this.params = params;
    
    if (this.tabs.length > 0) {
	    this.hideAllTabs ();
	    this.gotoTab (0);
	  }
    //if (!handyShow) {
    	this.dialog.show(fromElem);
    	document.upDlgVisible = true;
    	
    //}
    if (this.onAfterShow != null)
    	this.onAfterShow ();
    
    if (this.verticalScroll)
    	this.dialog.body.applyStyles("overflow-y: auto; overflow-x: hidden");
	},
	
	
	/***************************************************
	* Initialize dialog
	***************************************************/	  	
	init: function () {
		this.currentTab = 0;
		
		//var dlgDom = document.createElement("div");
		//dlgDom.className = "PopupDialog";
		//dlgDom.id = this.dialogContentId + "-dlg";
		//document.body.appendChild(dlgDom);
		
		var dialogContent = Ext.get(this.dialogContentId);
		//dlgDom.appendChild(dialogContent.dom);
		dialogContent.dom.style.display = "block";
		
		this.config.title = this.strings.title;
		
		this.config.contentEl = dialogContent;
		this.dialog = new Ext.Window(this.config);
		if (Ext.isGecko) {
			this.dialog.on("render", function() {
				this.el.dom.style.position = "fixed";
			});	
		}
    
	    for (i = 0; i < this.leftButtons.length; i++)  {
	    	var btn = this.leftButtons[i];
	    	this.addedButtons[btn.id] = this.dialog.addButton (btn.text, btn.handler, btn.scope);
	    }
	    //this.dialog.addKeyListener(27, this.dialog.hide, this.dialog);
	    if (this.standartButtons) {
		    this.nextBtn = this.dialog.addButton(this.strings.nextBtn, this.nextClicked, this);
		    if (this.tabs.length > 0) {
		    	this.nextBtn.disable();
		    }
		  }
	    for (i = 0; i < this.moreButtons.length; i++)  {
	    	var btn = this.moreButtons[i];
	    	this.addedButtons[btn.id] = this.dialog.addButton (btn.text, btn.handler, btn.scope);
	    }
	    if (this.standartButtons)
	    	var cancelBtn = this.dialog.addButton(this.strings.cancelBtn, function() {this.hide()}, this.dialog);
	    
	    if (this.addClass)
	    	this.dialog.getEl().addClass (this.addClass);
	    
	    if (this.onAfterInit != null)
	    	this.onAfterInit ();
    
    /*if (this.config.y) {
			this.dialog.getEl().move ("t", 100);
			alert(this.dialog.getEl().dom.style.top);
		}*/
	},
		
	hideAllTabs: function () {
		for (i = 0; i < this.tabs.length; i++) {
			var cTab = this.tabs[i];
			if (document.getElementById(cTab.id + "-tab") == null)
				continue;
			document.getElementById(cTab.id + "-tab").style.display = "none";
		}
	},
		
	hide: function () {
		this.dialog.hide ();
	},
	
	
	/***************************************************
	* Show one of tab
	***************************************************/
	showCurrentTab: function () {
		this.hideShowedTab ();
		
		currentTab = this.tabs[this.currentTabNo];
		if (currentTab == null)
			alert("Nothing to show: tabNo=" + this.currentTabNo);
		if (currentTab.nextBtnText != null && this.nextBtn != null)
			this.nextBtn.setText(currentTab.nextBtnText);
				
		document.getElementById(currentTab.id + "-tab").style.display = "block";
		if (currentTab.onShow != null)
			currentTab.onShow (this);
		this.showedTabNo = this.currentTabNo;
	},
		
	hideShowedTab: function () {
		if (this.showedTabNo == null)
			return;
		document.getElementById(this.tabs[this.showedTabNo].id + "-tab").style.display = "none";		
	},
		
	gotoTab: function (no) {
		this.currentTabNo = 0;
		this.showCurrentTab ();
	},
	
	gotoNextTab: function () {
		this.currentTabNo++;
		this.showCurrentTab();		
	},
		
	saveDialogParams: function (url, params) {
		if (!this.hasChanges)
			return;
		if (params == null)
			params = new Array ();
		params.width = this.dialog.getEl().getWidth();
		params.height = this.dialog.getEl().getHeight();
		AjaxLoader.doRequest (url, this.saveDialogParamsHandler,
			params, {scope: this});
	},
		
	saveDialogParamsHandler: function () {
		this.hasChanges = false;
	},
		
	
		
	/***************************************************
	* Called then the 'Next' (or 'Share') button clicked
	***************************************************/
	nextClicked: function () {
		if (this.tabs.length > 0) {
			currentTab = this.tabs[this.currentTabNo];
			if (currentTab.onNext != null) {
				currentTab.onNext(this);
			}
		} else if (this.onNext) {
			this.onNext ();			
		}
	},
		
	showDialog: function (fromElem, type, subtype, config) {
		params = {type: type, subtype: subtype, config: config};
		this.show(fromElem, params);
	}
};

document.dialogs = new Array ();