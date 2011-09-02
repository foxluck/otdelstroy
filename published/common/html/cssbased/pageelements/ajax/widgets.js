function keydown (e, fieldName, value) {
	if (e.keyCode == 13) {
		fieldChanged (fieldName, value);
	}
}

function showHideEmbedOptions () {
	if (document.getElementById("EmbedOptions").style.display == "none") {
		document.getElementById("EmbedOptions").style.display = "inline";
		//document.getElementById("EmbedOptionsLink").innerHTML = "<? $wgStrings.amwg_hidemoreemb_label ?>";
		document.getElementById("EmbedOptionsLink").style.display = "none";
	} else {
		document.getElementById("EmbedOptions").style.display =  "none";
		//document.getElementById("EmbedOptionsLink").innerHTML = "<? $wgStrings.amwg_moreemb_label ?>";
		document.getElementById("EmbedOptionsLink").style.display = "inline";
	}
}

function previewWidget () {
	window.open("<? $widgetSrc ?>","mywindow");
	return false;
}

function fieldChanged (fieldName, value) {
	return true;
	if (value == false)
		value = 0;
	params = new Array ();
	if (value == null) {
		var str = "";
		var form = document.getElementById("customizeWidgetForm");
		var off1 = "fields[".length;
		
		var elements = form.getElementsByTagName("input");
		for (i = 0; i < elements.length; i++) {
			elem = elements[i];
			if(elem.name.substring(off1,off1+fieldName.length+1) == fieldName+"]") {
				if (!(elem.type == "checkbox" && !elem.checked))
					params[elem.name] = elem.value;
			}
		}
	} else {
		params[fieldName] = value;
	}
	
	//if (window.frames["previewFrame"].fieldChanged)
	//	window.frames["previewFrame"].fieldChanged(fieldName, value);
	
	//var dom = document.getElementById('customizeWidgetForm');
	//dom.dialog.trySaveParams(params, dom.dialog);
}

function fieldColorChanged (fieldName) {
	document.getElementById (fieldName + "Prev").style.background = document.getElementById (fieldName).value;
	fieldChanged (fieldName, document.getElementById (fieldName).value);
}

	
	
	
	/*** NEW CODE ***/























var deletedWgId;
function deleteWidget (wgId, type) {
	deletedWgId = wgId;
	deleteWidgetHandler.type = type;
	AjaxLoader.doRequest ("../../../WG/html/ajax/wg_delete.php", deleteWidgetHandler, {wgId: wgId});
}

function deleteWidgetHandler (response, options) {
	var result = Ext.decode(response.responseText);
	if(result.success) {
		if (window.parent && window.parent.WidgetsManager) {
			window.parent.WidgetsManager.widgetDeleted(result.wgId, deleteWidgetHandler.type);
			return;
		}
		
		var deletedNode = foldersTree.getNodeById("shdd-" + result.wgId);
		if (deletedNode == null)
			deletedNode = foldersTree.getNodeById("wg-" + result.wgId);
		var widgetsNode = deletedNode.parentNode;
				
		var siblingNode = deletedNode.previousSibling;
		if (!siblingNode)
			siblingNode = deletedNode.nextSibling;
		if (siblingNode)
			siblingNode.select();
		deletedNode.parentNode.removeChild(deletedNode);
			
		if (widgetsNode.childNodes.length == 0) {
			foldersTree.root.childNodes[0].select ();
			//foldersTree.removeSharesNode ();
		}
		
		foldersTree.loadSelectedNode ();
	} else {
		alert(result.errorStr);
	}
}

function showCustomizeDialog (btn, wgIdEnc, config) {
	var dom = document.getElementById('customizeWidgetForm');
	if (dom.dialog != null) {
		dom.dialog.show (btn);
		return;
	}
	
	//document.getElementById("myCustomizeWidgetFormContent").innerHTML = dom.innerHTML;
	
	var startX = 0;
	if (document.getElementById("MainMenu") != null) {
		startX = document.getElementById("MainMenu").offsetWidth;
	}
	
	var startY = 250;
	if (config != null && config.left != null)
		startX = config.left;
	if (config != null && config.top != null)
		startY = config.top;
	
	var tabsEls = Ext.get("customizeTabs").query(".x-dlg-tab");
	var items = new Array ();
	for (var i = 0; i < tabsEls.length; i++) {
		items.push ({title: tabsEls[i].getAttribute("title"), contentEl: tabsEls[i]});
	}
	
	var tabsPanel = new Ext.TabPanel({
      //margins:'3 3 3 0', 
      activeTab: 0,
      defaults:{autoScroll:true},
      autoScroll: true,
      height: 235,
      items: items, 
      autoHeight: false
  });
	
	var dialog = new Ext.Window({contentEl:"customizeWidgetForm",
        title: CommonStrings.wg_customizedialog_title,
        width:510,
        height:300,
        y: startY,
        x: startX + 20,
        autoTabs:true,
        closeAction: 'hide',
        shadow:false,
        resizable: false,
        resizeHandles: "se",
        items: tabsPanel,
        modal: true
	});
				
				
	//dialog.addKeyListener(27, dialog.hide, dialog);
	dom.dialog = dialog;
	dialog.wgIdEnc = wgIdEnc;
	
	
	dialog.trySave = function () {
		var elements = new Array();
		var formParams = new Array();		
		
		var inputElems = document.getElementsByTagName("input");
		var selectElems = document.getElementsByTagName("select");
		var textareaElems = document.getElementsByTagName("textarea");
		for (i = 0; i < inputElems.length; i++)
			elements[elements.length] = inputElems[i];
		for (i = 0; i < selectElems.length; i++)
			elements[elements.length] = selectElems[i];
		for (i = 0; i < textareaElems.length; i++)
			elements[elements.length] = textareaElems[i];
		for ( j = 0; j < elements.length; j++ ) {
			var elem = elements[j];
			if (elem.type == "checkbox" && !elem.checked)
				continue;
			formParams[elem.name] = elem.value;
		}
		formParams.wgId = this.wgIdEnc;
		oldSrc = document.getElementById("previewFrame").src;
		document.getElementById("previewFrame").src = "";
		
		var saveUrl = "../../../WG/html/ajax/wg_save.php";
		if (config != null && config.saveUrl != null)
			saveUrl = config.saveUrl;
		
		AjaxLoader.doRequest (saveUrl, changeWidgetParamsHandler, formParams);
	}
	
	
	dialog.trySaveParams = function (params, dlg) {
		params.wgId = dlg.wgIdEnc;
		//window.needRefresh = false;
		AjaxLoader.doRequest ("../../../WG/html/ajax/wg_save.php", changeWidgetParamsHandler , params);
	}
	
	dialog.saveBtn = dialog.addButton(saveBtnLabel, dialog.trySave, dialog);
	dialog.cancelBtn = dialog.addButton(cancelBtnLabel, function() {this.hide()}, dialog);
	
	dialog.show (btn);
}

var oldSrc = null;
function changeWidgetParams (wgId, params, needRefreshPreview) {
	params.wgId = wgId;
	var callback = null;
	if (needRefreshPreview == true) {
		oldSrc = document.getElementById("previewFrame").src;
		//document.getElementById("previewFrame").src= "";
		callback = changeWidgetParamsHandler;
	}
	AjaxLoader.doRequest ("../../../WG/html/ajax/wg_save.php", callback, params);
}


function changeWidgetParamsHandler (response, options) {
	var result = Ext.decode(response.responseText);
	if(result.success) {
		if (window.needRefresh) {
			window.frames["previewFrame"].location.href = window.frames["previewFrame"].location.href;
			window.needRefresh = false;
		}
		else if (oldSrc)
			document.getElementById("previewFrame").src = oldSrc;			
		if (result.embCode && document.getElementById("widgetEmbCode"))
			document.getElementById("widgetEmbCode").value = result.embCode;
		if (result.WG_DESC) {
			document.getElementById("caption_label").innerHTML = result.WG_DESC;
			document.getElementById("caption_edit_input").value = result.WG_DESC;
			if (parent.document.getElementById("wg-" + result.wgId)) {
				parent.document.getElementById("wg-" + result.wgId).innerHTML = result.WG_DESC;
			}
			foldersTree.getNodeById("wg-" + result.wgId).setText(result.WG_DESC);
		}
			
	} else
		alert(result.errorStr);
}




function showEmbedDialog (btn, wgIdEnc, config) {
	var dom = document.getElementById('embedWidgetForm');
	if (dom.dialog != null) {
		dom.dialog.show (btn);
		return;
	}
	
	var startX = 0;
	if (document.getElementById("MainMenu") != null) {
		startX = document.getElementById("MainMenu").offsetWidth;
	}
	
	var startY = 250;
	if (config != null && config.left != null)
		startX = config.left;
	if (config != null && config.top != null)
		startY = config.top;
	
	var dialog = new Ext.Window({
				title: CommonStrings.wg_embeddialog_title,
				contentEl:"embedWidgetForm",
				closeAction: 'hide',
        width:510,
        height:270,
        shadow:false,
        resizable: false,
        resizeHandles: "se"
	});
				
				
	//dialog.addKeyListener(27, dialog.hide, dialog);
	dom.dialog = dialog;
	dialog.wgIdEnc = wgIdEnc;
	
	dialog.cancelBtn = dialog.addButton(closeBtnLabel, function() {this.hide()}, dialog);
	
	dialog.show (btn);
}