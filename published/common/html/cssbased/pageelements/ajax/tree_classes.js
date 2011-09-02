function TreeDDManager () {
	this.config = null;
	this.movedNode = null;
	this.movedNodeOldParent = null;
	this.manualMove = false;
	this.newParentNode = null;
	this.tree = null;
	
	this.init = function (tree, config) {
		
		this.config = config;
		this.tree = tree;
		
		//this.tree.addListener('move', this.onNodeMoved, this);
		this.tree.ddManager = this;
	}
	
	this.copyMoveNode = function (action, tree, node, oldParent, newParent) {
		//if (action == "move") {
			//alert(node.text + ": " + oldParent.text + " -> " + newParent.text);
			//oldParent.removeChild(node);
			//newParent.appendChild(node);
			//alert("D");
		//}
		//return;	
		
		AjaxLoader.doRequest(this.config.nodeMovedUrl, this.onNodeMovedHandler, {action: action, folderID: node.id, parentFolderID: newParent.id}, {scope: this}); 
		this.movedNode = node;
		this.newParentNode = newParent;
		this.movedNodeOldParent = oldParent;
		this.movedNode.collapse ();
		this.movedNode.select ();
		
		copyMoveDlg.processElem.style.display = "";
		for (i = 0; i < copyMoveDlg.dialog.buttons.length; i++)
				copyMoveDlg.dialog.buttons[i].disable ();
		copyMoveDlg.processTextElem.innerHTML = foldersTree.config.strings.actions[action];
	}
	
	/*this.onNodeMoved = function (tree, node, oldParent, newParent, index) {
		if (this.manualMove)
			return;
		
		AjaxLoader.doRequest(this.config.nodeMovedUrl, this.onNodeMovedHandler, {folderID: node.id, parentFolderID: newParent.id}, {scope: this}); 
		this.movedNode = node;
		this.movedNodeOldParent = oldParent;
		this.movedNode.collapse ();
		this.movedNode.select ();
	}*/
	
	this.onBeforeNodeMove = function (tree, node, oldParent, newParent) {
		if (!this.manualMove) {
			copyMoveDlg.params = {tree: tree, node: node, oldParent: oldParent, newParent: newParent};
			document.getElementById("tree-copy-move-toname").innerHTML = newParent.text;
			copyMoveDlg.showDialog ();
			copyMoveDlg.processElem.style.display = "none";
			for (i = 0; i < copyMoveDlg.dialog.buttons.length; i++)
				copyMoveDlg.dialog.buttons[i].enable ();
			return false;
		}
		return true;
	}
	
	this.onNodeMovedHandler = function (response, options) {
		var result = Ext.decode(response.responseText);
		var movedNode = this.movedNode;
				 
     if(result.success) {
     	
     	var leaf = (movedNode.childNodes.length < 1);
     	var nodeClass = leaf ? Ext.tree.TreeNode : Ext.tree.AsyncTreeNode;
     	var iconCls = (result.iconCls) ? result.iconCls : movedNode.attributes.iconCls;
     	newNode = new nodeClass ({id: result.newID, text: movedNode.text, link: this.config.linkPrefix + result.encNewID, iconCls: iconCls, allowDrag: movedNode.attributes.allowDrag, allowDrop: movedNode.attributes.allowDrop, editable: movedNode.attributes.editable});
     	
     	if (options.params.action == "copy") {
     		this.newParentNode.appendChild (newNode);
     	} else {
     		this.manualMove = true;
     		this.newParentNode.appendChild(newNode);
     		this.movedNodeOldParent.removeChild (this.movedNode);
     		this.manualMove = false;
     		//movedNode.parentNode.removeChild(movedNode);
     	}
     	
     	newNode.loader = new Ext.tree.TreeLoader({dataUrl:this.config.treeLoaderUrl});
			
     	newNode.select ();
     	
     	this.tree.loadSelectedNode ();
     } else {     	
     	//this.manualMove = true;
     	//this.movedNodeOldParent.appendChild (this.movedNode);
     	this.movedNode.ensureVisible();
     	movedNode.select();
     	Ext.MessageBox.alert('Error', result.errorStr);
     	this.manualMove = false;
     }
     copyMoveDlg.dialog.hide ();
	}
}


function TreeEditManager() {
	this.changedManually = false;
  this.renamedNode;
  this.renamedNodeOldText;
  this.textChangedSourceData = new Array ();
  this.config = null;
  this.tree = null;
  
  this.init = function (tree, config) {
  	this.tree = tree;
  	this.config = config;
  	
  	this.tree.addListener('textchange', this.onTextChanged, this);
  }
  
  this.onTextChanged = function (node, text, oldText) {
  	if (this.changedManually) {
  		this.changedManually = false;
  		return;
  	}
  	if (text == oldText || (node.attributes.editable != true))
  		return;
  	
		this.textChangedSourceData.renamedNode = node;
		this.textChangedSourceData.renamedNodeOldText = oldText;
		
		var url = null;
		if (node.attributes.type != null) {
			if (this.config.typesUrl == null)
				return false;
			url = this.config.typesUrl[node.attributes.type];
		} else {
			url = this.config.renameUrl;
		}
		
		if (url == null)
			return false;
		
			
		AjaxLoader.doRequest(url, this.onTextChangedHandler, {folderID: node.id, newName: text}, {scope: this});
	}
	
	this.onTextChangedHandler = function (response, options) {
		var result = Ext.decode(response.responseText);
     if(result.success) {
     	this.tree.loadSelectedNode();
     } else {
     	alert(result.errorStr);	     
     	this.changedManually = true;
     	this.textChangedSourceData.renamedNode.setText(this.textChangedSourceData.renamedNodeOldText) ;
     }
	}
}



var copyMoveDlg = new SimpleDialog ("tree-copy-move");
copyMoveDlg.config.title = cf.strings.confirm;
copyMoveDlg.config.modal = true;
copyMoveDlg.config.width = 350;
copyMoveDlg.config.height = 220;
copyMoveDlg.config.buttonAlign = "right"
copyMoveDlg.config.closable = false;
copyMoveDlg.onAfterInit = function () {
	this.doCopy = function () {
		var p = this.params;
		foldersTree.ddManager.copyMoveNode ("copy", p.tree, p.node, p.oldParent, p.newParent);
		//this.dialog.hide ();
	}
	
	this.doMove = function () {
		var p = this.params;
		foldersTree.ddManager.copyMoveNode ("move", p.tree, p.node, p.oldParent, p.newParent);
		//this.dialog.hide ();
	}
	
	this.processElem = document.getElementById("tree-copy-move-process");
	this.processTextElem = document.getElementById("tree-copy-move-process-text");
	
	this.dialog.addButton (foldersTree.config.strings.copy, this.doCopy, this);
	this.dialog.addButton (foldersTree.config.strings.move, this.doMove, this);
	this.dialog.addButton (foldersTree.config.strings.cancel, function() {this.hide()}, this.dialog);
}