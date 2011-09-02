function ContactsSelector () {
	this.panelElem = null;
	this.tree = null;
	this.searchNode = null;
	this.prefix = null;
	
	this.init = function (panelElem, parentDom, prefix) {
		this.panelElem = panelElem;
		if (prefix == null)
			prefix = "";
		this.prefix = prefix;
			
			var treeDom = document.createElement("div");
			treeDom.style.display = "block";
			treeDom.style.height = "100%";
			treeDom.style.overflow = "auto";
			panelElem.child(".contacts-selector-tree").appendChild(treeDom);
			this.noteDom = panelElem.child(".note-text").dom;
			
			var tree = new Ext.tree.TreePanel({el: treeDom,
        animate:false, 
        autoHeight: true, 
        border: false, 
        enableDD:false,
        ddAppendOnly: false,
        rootVisible: false,
        loader: new Ext.tree.TreeLoader({dataUrl: "../../../common/html/ajax/get_contacts_child.php"}),
        containerScroll: false
    	});
    		
    	if (window.foldersTree)
    		tree.loader.baseParams.fapp = foldersTree.getWidgetsNode ().loader.baseParams.fapp;
    		
    	panelElem.dom.contactsSelector = this;
    	
    	var node = null;
    	var rootNode = null;
    	
    	rootNode = new Ext.tree.AsyncTreeNode({text: 'ROOT', id: 'ROOT'});
    	tree.setRootNode (rootNode);
    	
    	tree.contactsSelector = this;
    	tree.on ("click", this.onClick);
    	tree.on ("dblclick", this.onDoubleClick);
    	
    	rootNode.expand ();
    	
    	tree.render();
    	this.tree = tree;
    	
    	parentDom.contactsSelector = this;
    	document.activeContactsSelector = this;
    	
    	// Add event to parent dom (for hide ContactsSelector if clicked out it)
    	Event.observe(parentDom, 'click', function(event) {
    		var element = Event.element(event);
    		var contactsSelector = document.activeContactsSelector;
    		
    		if (contactsSelector == null || contactsSelector.isVisible())
			  	return true;
			  var currentElem = element;
			  var divFinded = false;
			  while (currentElem != this && currentElem != document.body && currentElem != document) {
			  	currentElem = currentElem.parentNode;
			  	if (currentElem.id == contactsSelector.panelElem.id) {
			  		divFinded = true;
			  		break;
			  	}
			  }
			  if (!divFinded && element.id != "my-contacts-link") {
			  	contactsSelector.hide ();
			  }
			}, true);
	}
	
	
	this.isVisible = function () {
		return (this.panelElem == null || this.panelElem.dom.style.display == "none");
	}
	
	this.onDoubleClick = function (node, e) {
		contactsSelector = this.contactsSelector;
		if (node.attributes.contact != null) {
			if (contactsSelector.onEmailSelected != null)
				contactsSelector.onEmailSelected (node.attributes.contact);
		}
	}
	
	this.onClick = function (node, e) {
		var noteDom = this.contactsSelector.noteDom;
		if (noteDom != null) {
			noteDom.style.visibility = (node.attributes.contact != null) ? "visible" : "hidden";
			noteDom.innerHTML = (node.attributes.isList) ? ContactsSelector.listNote : ContactsSelector.contactNote;
		}
	}
	
	this.showHide = function (panelElem) {
		if (this.panelElem == null || this.panelElem.dom.style.display == "none") {
			this.show();
		} else {
			this.hide ();
		}				
	}
	
	this.show = function () {
		if (this.tree == null)
			this.init ();
		
  	this.panelElem.dom.style.display = "block";
	}
	
	this.hide = function () {
		if (this.panelElem != null) {
			this.panelElem.dom.style.display = "none";
		}
	}
	
	this.doSearch = function (searchStr) {
		var rootNode = this.tree.getRootNode ();
		
		this.tree.loader.baseParams.searchStr = searchStr;		
		
		if (this.searchNode == null) {
			this.searchNode = new Ext.tree.AsyncTreeNode({text: ContactsSelector.searchResultsStr, id: 'SEARCH', iconCls: "search-folder" });
			rootNode.insertBefore (this.searchNode, rootNode.firstChild);				
	    this.searchNode.expand ();
	  } else {
	  	this.searchNode.reload ();
	  }
	  this.searchNode.select ();
	}
}

function contactsSearchKeyDown (panelId,value,e) {
	var contactsSelector = document.getElementById(panelId).contactsSelector;
	
	var keynum;
	
	if(window.event) // IE
		keynum = e.keyCode
	else if(e.which) // Netscape/Firefox/Opera
		keynum = e.which;
	if (keynum == 13) {
		if (value.length > 0)
			contactsSelector.doSearch(value);
	}
}