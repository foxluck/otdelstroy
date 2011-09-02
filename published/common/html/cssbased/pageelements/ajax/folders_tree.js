Ext.onReady(function(){
  var Tree = Ext.tree;
  
  var config = foldersTreeConfig;
  foldersTree = new Tree.TreePanel({el: 'tree-div', autoHeight: true, border: false, 
      animate:false, enableDD:config.enableDD, containerScroll: false, ddAppendOnly: true, rootVisible: false,
      defferedRender: true, autoScroll: false
  });
  foldersTree.config = foldersTreeConfig;
  foldersTree.folderTypeStrings = new Array ();
  
  foldersTree.loadSelectedNode = function (notNeedTree, linkAdd) {
  	if (notNeedTree != true)
  		SplitterShowLeftPanel ();
  	node = this.getSelectionModel().getSelectedNode();
  	if (!node)
  		return false;
  	if (linkAdd == null)
  		linkAdd = "";
  	else
  		linkAdd = "&"+linkAdd;
  	if (node.attributes.link)
  		AjaxLoader.loadPage (node.attributes.link + linkAdd);
  	return true;
  }
  
  foldersTree.addListener("click", onTreeClick);
  foldersTree.addListener("beforemovenode", onTreeBeforeMove);
  
  var node;
  var parentNode;
  var rootNode;
  var parentNodes = new Array ();
  var selectedNode = null;
  
  var superparentNode = new Tree.TreeNode({text: 'ROOT'});
  foldersTree.setRootNode(superparentNode);
  
  createTreeNodes(parentNodes);
  
  if (parentNodes['']!= null)
  parentNodes[''].attributes.	editable = null;
  
  // render the foldersTree
  foldersTree.render();
  
  var currentNode = parentNodes[config.currentNodeId];
  
  foldersTree.lastNode = currentNode;
  if (currentNode) {
    currentNode.ensureVisible ();
    currentNode.select ();
    if (currentNode == parentNodes[''])
    	currentNode.expand ();
  }
  
  foldersEditor = new Ext.tree.TreeEditor(foldersTree, {
      allowBlank:false,
      blankText:'A name is required',
      selectOnFocus:true,
      ignoreNoChange: true
  });
  foldersEditor.addListener("beforeshow", onTreeEditorBeforeShow);
  foldersEditor.needShow = config.enableEdit;
  
  
  foldersTree.getWidgetsNode = function() {
		if (foldersTree.widgetsNode == null) {
			var widgetsNode = new Ext.tree.AsyncTreeNode({text: config.strings.widgetsLabel, iconCls: "widgets-folder", allowDrag: false, allowDrop: false, id: "WIDGETS" });
			widgetsNode.loader = new Ext.tree.TreeLoader({dataUrl:'../../../WG/html/ajax/tree.widgets.php'});
			
			foldersTree.widgetsNode = widgetsNode;
			foldersTree.getRootNode().appendChild (widgetsNode);
		}
		return foldersTree.widgetsNode;
	}
	
	foldersTree.selectWidget = function (wgId) {
		var widgetsNode = foldersTree.getWidgetsNode ();
		widgetsNode.on("expand", onWidgetSelectExpanded);
		widgetsNode.selectWidgetId = wgId;
		widgetsNode.collapse ();
		widgetsNode.expand ();
		//var widgetNode = this.getNodeById(wgId);
	}
	
	foldersTree.searchNodeIndex = 1;
	foldersTree.getSearchNode = function(searchString) {
		if (foldersTree.searchNode == null) {
			var searchNode = new Ext.tree.TreeNode({text: config.strings.searchResultsLabel + ': ' + searchString, iconCls: "search-folder", allowDrag: false, allowDrop: false, id: "SEARCH" });
			
			searchNode.attributes.link = this.searchPrefix + encodeURIComponent(searchString);
			
			foldersTree.searchNode = searchNode;
			var rootNode = foldersTree.getRootNode();
			if (rootNode.childNodes[this.searchNodeIndex] != null)
				rootNode.insertBefore(searchNode, rootNode.childNodes[this.searchNodeIndex]);
			else
				rootNode.appendChild (searchNode);
		} else {
			foldersTree.searchNode.setText (foldersTree.config.strings.searchResultsLabel + ': ' + searchString);
			foldersTree.searchNode.attributes.link = this.searchPrefix + encodeURIComponent(searchString);
		}
		return foldersTree.searchNode;
	}
	
	foldersTree.getSelectionModel().on ("beforeselect", function( selectionModel, newNode, oldNode ) {
		if (!newNode.attributes.link)
			return false;
	});
	
	if (document.onFoldersTreeLoad!= null) 
  	document.onFoldersTreeLoad();
});

function onWidgetSelectExpanded (widgetsNode) {
	widgetsNode.removeListener("expand", onWidgetSelectExpanded );
	var selectedWidgetNode = foldersTree.getNodeById("wg-" + widgetsNode.selectWidgetId);
	selectedWidgetNode.select ();
	foldersTree.loadSelectedNode ();
}

function onTreeClick (node, e) {
	selectedNode = node.getOwnerTree().getSelectionModel().getSelectedNode();
	if (selectedNode == node) {
		foldersEditor.triggerEdit(node);
		return;
	}
	if (node.attributes.link!= null) {
		foldersTree.lastNode = node;
		AjaxLoader.loadPage (node.attributes.link);
	}
}

function onTreeBeforeMove(tree, node, oldParent,newParent, index) {
	if (node.attributes.editable == false) {
		alert(foldersTree.config.strings.folderActionRightsError);
		return false;
	}
	
	if (!foldersTree.ddManager != null)
		return foldersTree.ddManager.onBeforeNodeMove (tree, node, oldParent, newParent, index);
}

function onTreeEditorBeforeShow(comp) {
	if (!this.needShow)
		return false;
	var node = foldersTree.getSelectionModel().getSelectedNode();
	if(node == null || node.attributes.editable == null) {
		return false;
	}
	
	if (node.attributes.editable == false )	{
		if (node.attributes.type == null)
			alert(foldersTree.config.folderRenameNoRightsError);
		return false;
	}
}


function createFolder () {
	hideFolderMenu ();
	currentFolderId = document.getElementById("currentFolderId").value;
	
	SplitterShowLeftPanel ();
	
	AjaxLoader.doRequest ("../ajax/folder_create.php", createFolderHandler,
		{parentId: currentFolderId}, {scope: this});
}

function  createFolderHandler (response, options) {
	var result = Ext.decode(response.responseText);
	if(result.success) {
		var parentNode = foldersTree.getNodeById(result.parentId);
		if (parentNode == null) {
			alert("Error. No parent node found: " + result.parentId);
			return;
		}			
		if (!result.newID) {
			alert("Error. Folder not created");
			return;
		}
		var iconCls = (result.iconCls) ? result.iconCls : foldersTree.config.avIconCls ;
		var newNode = new Ext.tree.TreeNode ({'text': result.name, id: result.newID, iconCls: iconCls , editable: true, allowDrag: true, allowDrop: true, encId: result.encNewID});
		newNode.id = result.newID;
		newNode.attributes.link = foldersTree.linkPrefix + newNode.attributes.encId;
		parentNode.appendChild (newNode);
		parentNode.expand ();
		newNode.select ();
		foldersEditor.triggerEdit (newNode);
		foldersTree.loadSelectedNode ();
	} else {
		alert(result.errorStr);
	}
}

function hideFolderMenu () {
	var menuObj = document.getElementById("MENU_B1");
	if (Ext.isSafari) {
		menuObj.style.visibility = "hidden";
		window.setTimeout("document.getElementById('MENU_B1').style.visibility = 'visible';", 1000);
	} else {
		menuObj.style.display = "none";
		window.setTimeout("document.getElementById('MENU_B1').style.display = '';", 1000);
	}
}

function deleteCurrentFolder () {
	if (!confirmFolderDeletion())
		return false;
}

function doDeleteFolder () {
	currentFolderId = document.getElementById("currentFolderId").value;
	
	confirmDeleteDlg.processElem.style.display = "";
	for (i = 0; i < confirmDeleteDlg.dialog.buttons.length; i++)
		confirmDeleteDlg.dialog.buttons[i].disable ();
	AjaxLoader.doRequest ("../ajax/folder_delete.php", deleteCurrentFolderHandler,
		{folderId: currentFolderId}, {scope: this});
}

function deleteCurrentFolderHandler (response, options) {
	confirmDeleteDlg.dialog.hide ();
	var result = Ext.decode(response.responseText);
	if(result.success) {
		var currentNode = foldersTree.getSelectionModel().getSelectedNode();
		foldersTree.getSelectionModel().selectPrevious ();
		currentNode.parentNode.removeChild(currentNode);
		if (foldersTree.loadSelectedNode () == false) {
			if (frames.parent != null)
				frames.parent.location.href = frames.parent.location.href;
		}
	} else {
		alert(result.errorStr);
	}
}

function renameFolder () {
	hideFolderMenu ();
	SplitterShowLeftPanel ();
	foldersEditor.triggerEdit (foldersTree.getSelectionModel().getSelectedNode());
}

function doSearch () {
	SplitterShowLeftPanel ();
	var searchString = Ext.get("Search").dom.value;
	if (searchString == "") {
		alert(foldersTree.config.strings.emptySearchString);
		return false;
	}
	var searchNode = foldersTree.getSearchNode(searchString);
	if (searchNode) {
		searchNode.select ();
		foldersTree.loadSelectedNode ();
	}
}

function createTreeNodes (parentNodes) {
	for (i = 0; i < foldersTree.config.nodes.length; i++) {
		var nob = foldersTree.config.nodes[i];
		var nparams = {id: nob[0], parentId: nob[1], text: nob[2], iconCls: nob[3], editable: nob[4], link: nob[5], encId: nob[6], canRename: nob[7], canDrag: nob[8], canDrop: nob[9]};
		var nconfig = {text: nparams.text, id: nparams.id, iconCls: nparams.iconCls};
		
		nconfig.allowEdit = nparams.editable;
			
		nconfig.allowDrag = nparams.canDrag; 
		nconfig.allowDrop = nparams.canDrop; 
		
		
		if (nparams.id == "AVAILABLEFOLDERS")
			nconfig.allowDrop = true;
		var node = new Ext.tree.TreeNode (nconfig);
		//node.editable = true;
		node.attributes.link = nparams.link;
		node.encId = nparams.encId;
		if (nparams.canRename)
			node.attributes.editable = true;
		
		parentNodes[nparams.id] = node;
		if (nparams.parentId && nparams.parentId != '0')
			parentNodes[nparams.parentId].appendChild(node);
		else
			foldersTree.getRootNode().appendChild(node);
	}
}

var confirmDeleteDlg = new SimpleDialog ("tree-folder-delete");
confirmDeleteDlg.config.title = cf.strings.confirm;
confirmDeleteDlg.config.modal = true;
confirmDeleteDlg.config.width = 400;
confirmDeleteDlg.config.height = 140;
confirmDeleteDlg.config.closable = false;
confirmDeleteDlg.config.buttonAlign = "right";
confirmDeleteDlg.onAfterInit = function () {
	this.doDelete = function () {
		var p = this.params;
		doDeleteFolder ();
		//foldersTree.ddManager.copyMoveNode ("move", p.tree, p.node, p.oldParent, p.newParent);
		//this.dialog.hide ();
	}
	
	this.questionElem = document.getElementById("tree-folder-delete-message");
	this.processElem = document.getElementById("tree-folder-delete-process");
	
	this.dialog.addButton (foldersTree.config.strings.deleteBtn, this.doDelete, this);
	this.dialog.addButton (foldersTree.config.strings.cancel, this.dialog.hide, this.dialog);
}
function confirmDeletionAjax (question) {
	confirmDeleteDlg.showDialog ();
	confirmDeleteDlg.processElem.style.display = "none";
	for (i = 0; i < confirmDeleteDlg.dialog.buttons.length; i++)
		confirmDeleteDlg.dialog.buttons[i].enable ();
	confirmDeleteDlg.questionElem.innerHTML = question;
	//return confirm(question);
}