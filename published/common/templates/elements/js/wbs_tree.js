WbsTree = newClass(null, {
	constructor: function (config) {
		this.config = config;
		this.treePanel = null;
		this.rootNode = null;
		this.nodes = new Array ();
		
		this.nodeMap = {
			id : 0,
			text: 1,
			children: 3
		}
	}, 

	init: function() {
		var cf = this.config;
		this.treePanel = new Ext.tree.TreePanel ({el: cf.elemId, autoHeight: true, border: false, animate: false, rootVisible: cf.rootVisible});
		
		this.treePanel.addListener("click", this.onNodeClick, this);
		
		if (this.onBeforeNodeSelect)
			this.treePanel.getSelectionModel().on ("beforeselect", this.onBeforeNodeSelect);
	},
		
	loadNodes: function (nodesData, isRootNode) {
		
		var dataRoot = this.addNode(nodesData);
		
		if (isRootNode) {
			this.treePanel.setRootNode(dataRoot);
			this.rootNode = dataRoot;
		} else {
			var rootNode = new Ext.tree.TreeNode ({text:"Root"});
			rootNode.appendChild(dataRoot);
			this.treePanel.setRootNode(rootNode);
			this.rootNode = rootNode;
		}
	},
	
	addNode: function(nodeData, addConfig) {
		var config = {id: nodeData[this.nodeMap.id], text: nodeData[this.nodeMap.text], iconCls: this.config.iconCls};
		if (addConfig)
			config = config.extend(addConfig);
		var extNode = new Ext.tree.TreeNode(config);
		extNode.Id = config.id;
		extNode.Name = config.text;
		this.nodes[nodeData[this.nodeMap.id]] = extNode;
		var childrenData = nodeData[this.nodeMap.children];
		if (childrenData) {
			for (var i = 0; i < childrenData.length; i++) {
				var childNodeData = childrenData[i];
				var extChildNode = this.addNode(childNodeData);
				extNode.appendChild(extChildNode);
			}
		}
		return extNode;
	},
	
	removeNode: function (node) {
		this.rootNode.removeChild(node);
		delete this.nodes[node.Id];
	},
	
		
	render: function () {
		if (this.onBeforeRender)
			this.onBeforeRender();			
		
		
		this.treePanel.render();
		//this.treePanel.expandAll();
		//this.treePanel.collapseAll();
		this.treePanel.getRootNode().expand();
		
		
		if (this.onAfterRender)
			this.onAfterRender();			
	},
		
	selectNode: function(nodeId) {
		var node = this.nodes[nodeId];//this.treePanel.getNodeById(nodeId);
		if (!node) {
			return false;
		}
		node.ensureVisible();
		node.select();
		this.onNodeClick(node);
		//if(nodeId == "ROOT")
		if (node.ui && node.ui.elNode) {
			//node.ui.elNode.focus();			
		}
			node.expand();
		
		return true;
	},
	
	getNode: function(nodeId) {
		var node = this.treePanel.getNodeById(nodeId);
		return node;
	},
		
	getSelectedNode: function() {
		var node = this.treePanel.getSelectionModel().getSelectedNode();
		return node;
	}
});