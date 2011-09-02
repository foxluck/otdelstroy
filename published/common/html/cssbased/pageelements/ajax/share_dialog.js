CommonShareDialog = function(dialogContentId) {
	this.dialogContentId = dialogContentId;
	this.strings = new Array ();
}

CommonShareDialog.prototype = new CommonDialog;

CommonShareDialog.prototype.start = function (prefix) {
	this.tabs = new Array ();
	this.prefix = prefix;
	
	this.tabs.push (
		{'id' : prefix+'-select', 
		 'nextBtnText': this.strings.nextBtn,
		 onShow: function (dialog) {
		 		dialog.nextBtn.enable ();
		 		Ext.getDom(prefix+"-files-count").innerHTML = dialog.params.selectedFiles.length ;
		 		Ext.getDom(prefix+"-type-files").checked = false;
		 		Ext.getDom(prefix+"-type-folder").checked = false;
		 		Ext.getDom(prefix+"-type-files").disabled = (dialog.params.selectedFiles.length == 0);
		 		Ext.getDom(prefix+"-type-folder").disabled = (dialog.params.folderId == null);		 			 
		 },
		 onNext: function(dialog) {
		 		if (Ext.getDom(prefix+"-type-files").checked)
		 			dialog.shareType = "files";
		 		else if (Ext.getDom(prefix+"-type-folder").checked)
		 			dialog.shareType = "folder";
		 		else {
		 			alert("Please select share type");
		 			return;
		 		}		 		
		 		dialog.gotoNextTab ();
			}		 
		}
	);

	this.tabs.push (
		{'id' : prefix+'-create', 
		 'nextBtnText': this.strings.createBtn,
		 onShow: function (dialog) {
		 		var shareNameInput = document.getElementById(dialog.prefix+"-name-input");
		 		if (dialog.shareType == "folder") {
					shareNameInput.value = dialog.params.folderName;
				}
				if (dialog.shareType == "files") {
					var files = dialog.params.selectedFiles;
					if (files.length > 2)
						shareNameInput.value = files[0].filename + " " + dialog.strings.andLabel + " " + (files.length-1) + " " + dialog.strings.moreItemsLabel;
					if (files.length == 2)
						shareNameInput.value = files[0].filename + " " +  dialog.strings.andLabel + " 1 " + dialog.strings.moreItemLabel;
					if (files.length == 1)
						shareNameInput.value = files[0].filename;
				}	
		 		dialog.nextBtn.enable ();
		 },
		 onNext: function(dialog) {
		  var wgName = document.getElementById(dialog.prefix+"-name-input").value;
			if (wgName == null || wgName == "")
				return alert(dialog.strings.emptyNameError);
		
			var folderId = dialog.params.folderId;
			var selectedFilesIds = new Array ();
			
			if (dialog.params.selectedFiles != null)
				for (i = 0; i < dialog.params.selectedFiles.length; i++)
					selectedFilesIds.push (dialog.params.selectedFiles[i].id);
			if (dialog.shareType == "folder")
				selectedFilesIds = new Array ();
			if (dialog.shareType == "files")
				folderId = null;
			
			AjaxLoader.doRequest ("../ajax/share_create.php", this.onShareCompleted,
				{type : dialog.params.type, subtype : dialog.params.subtype, folder: folderId, 'files[]': selectedFilesIds, wgName: wgName, viewMode: this.viewMode}, {scope: dialog});
		 },

		onShareCompleted: function (response, options) {
			var result = Ext.decode(response.responseText);
			if(result.success) {
				if (result.embType == "link") {
					sharesNode = foldersTree.getSharesNode();
				}
				else
					sharesNode = foldersTree.getWidgetsNode();
				sharesNode.loader.on ("load", function() {
					sharesNode.expand ();
					
					var newNode = foldersTree.getNodeById("wg-" + result.wgId);
					
					if (newNode != null) { 
						newNode.select ();
						foldersTree.loadSelectedNode();
					}
					sharesNode.loader.purgeListeners();
				}, this);
				sharesNode.reload ();
			} else {
				alert(result.errorStr);
			}
			this.hide ();
		}
	 }
	);
};


/***
* Show share dialog function
***/
CommonShareDialog.prototype.showDialog = function (fromElem, type, subtype, config) {
	this.config.modal = true;
	if (this.onShowDialog)
		this.onShowDialog(type, subtype,config);
	
	hasFolderRight = Ext.get("canShareFolder").dom.value == 1;
	selectedFiles = new Array ();
	
	thisForm = document.forms[0];
	for ( i = 0; i < thisForm.elements.length; i++ ) {
		cElem = thisForm.elements[i];
		if (cElem.type == 'checkbox' && cElem.checked  && cElem.getAttribute("filename") != null) {
			selectedFiles.push ({filename: cElem.getAttribute("filename"), id: cElem.value});
		}				
	}
	
	if (this.startSelectedFiles != null)
		selectedFiles = this.startSelectedFiles;
				
	if (!(hasFolderRight || selectedFiles.length > 0)) {
		alert ("Please select files");
		return false;
	}
	
	var folderId = (hasFolderRight) ? Ext.getDom("currentFolderId").value : null;
	var folderName = (hasFolderRight) ? Ext.getDom("currentFolderName").value : null;
	
	params = {type: type, subtype: subtype,folderName:folderName, folderId: folderId, selectedFiles: selectedFiles, viewMode:config.viewMode};
	this.show(fromElem, params);
	
	if (!hasFolderRight && selectedFiles.length) {
		Ext.getDom(this.prefix+"-type-files").checked = true;
		this.tabs[0].onNext (this);
	}
};