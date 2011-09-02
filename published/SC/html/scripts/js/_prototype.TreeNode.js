function TreeNode(_Settings, _ParentNode){
	
	this.expand_bullet = '&#9658;';
	this.colapse_bullet = '&#9660;';
	
	this.getSetting = function(_SettingName){
		
		return this.Settings[_SettingName]
	}
	
	this.setSetting = function(_SettingName,_SettingValue){
		
		this.Settings[_SettingName] = _SettingValue;
	}
//constructor
	this.Settings = _Settings
	this.ParentNode = _ParentNode
	this.ChildNodes = new Array()
	this.newChildIndex = 0
	
	if(!this.ParentNode){
		
		this.Level = 1
	}else{
		
		this.Level = this.ParentNode.getLevel()+1
		if(!this.getSetting('LIClass')){
			
			this.setSetting('LIClass', this.ParentNode.getSetting('LIClass'));
		}
		if(!this.getSetting('ULClass')){
			
			this.setSetting('ULClass', this.ParentNode.getSetting('ULClass'));
		}
		if(!this.getSetting('TitleClass')){
			
			this.setSetting('TitleClass', this.ParentNode.getSetting('TitleClass'));
		}
		this.ParentNode.assignChildNode(this);
	}
	
	if(this.Settings['id']){
		
		this.ID = this.Settings['id']
	}else{
		
		if(!window.TreeNodeUnicCnt){
			
			window.TreeNodeUnicCnt = 0
		}
		window.TreeNodeUnicCnt++
		this.ID = window.TreeNodeUnicCnt
	}
	
	if(!window.TreeNodeCollection){
		
		window.TreeNodeCollection = new Array()
	}
	window.TreeNodeCollection[this.ID] = this
	
/**
 * Methods
*/
	
	/**
	 * Create new node
	 *
	 * Setting description
	 * 	id - unique node id
	 * 	name - node title
	 */
	this.addChildNode = function(_Settings){
		
		var Node = new TreeNode(_Settings, this)
		return Node;
	}
	this.assignChildNode = function(Node){
		
		this.ChildNodes[this.newChildIndex] = Node;
		this.newChildIndex++;
	}
	
	this.deleteChildNode = function(_ChildNodeID){
		
		var ChildNodeIndex = this.findChildNode(_ChildNodeID)
		if(ChildNodeIndex){
			
			delete this.ChildNodes[ChildNodeIndex]
		}
	}
	
	this.findChildNode = function(_ChildNodeID){
		
		for (var _i in this.ChildNodes){
			
			if(this.ChildNodes[_i].getID()==_ChildNodeID){
				
				return _i
			}
		}
		return null
	}
	
	this.getChildNode = function(_ChildNodeID){
		
		for (var _i in this.ChildNodes){
			
			if(this.ChildNodes[_i].getID()==_ChildNodeID){
				
				return this.ChildNodes[_i]
			}
		}
		return null
	}
	
	this.getLevel = function(){
		
		return this.Level
	}
	
	this.getChildNodes = function(){
		
		return this.ChildNodes;
	}
	
	this.getID = function(){
		
		return this.ID
	}
	
	this.hasChildren = function(){
		
		return this.ChildNodes.length;
	}
	
	this.drawTree = function(parentObj){
	
		if(typeof(parentObj)=='undefined'){
			
			parentObj = getLayer(this.ParentNode.getID()+'_ul');
			if(!parentObj){
				alert('No ul');
			}
			
		}
		var liObj = createTag('li',parentObj);
		liObj.className = this.getSetting('LIClass')?this.getSetting('LIClass'):'tree_li';
		liObj.id = this.ID;
		
		var expanderObj = createTag('span',liObj);

		expanderObj.className = 'expander';
		expanderObj.id = this.ID+'_expander';
		expanderObj.innerHTML = this.getSetting('Expanded')?this.colapse_bullet:this.expand_bullet;
		if(!this.hasChildren())expanderObj.style.visibility = 'hidden';
		if(this.hasChildren()){
			expanderObj.setAttribute('onclick', 'expand("'+this.ID+'")');
		}
		
		liObj.innerHTML += '<span id="'+this.ID+'_begin'+'"></span><span class="'+(this.getSetting('TitleClass')?this.getSetting('TitleClass'):'tree_title')+'" onclick="window.'+this.getID()+'.onClickNode(this)" onmouseover="window.'+this.getID()+'.onMouseOverNode(this)" onmouseout="window.'+this.getID()+'.onMouseOutNode(this)">'+this.getSetting('name')+'</span><span id="'+this.ID+'_end'+'"></span>';
		
		liObj.setAttribute('node',this.ID);

		if(this.hasChildren()){
			
			var ulObj = createTag('ul',liObj);
			ulObj.className = this.getSetting('ULClass')?this.getSetting('ULClass'):'tree_ul';
			ulObj.id = this.ID+'_ul';
			
			if(!this.getSetting('Expanded')){
				
				this.expand(false);
			}
			for (var _i in this.ChildNodes){
				
				this.ChildNodes[_i].drawTree(ulObj);
			}
		}
		this.expanderObj = expanderObj;
		
		if(this.afterDrawTree)this.afterDrawTree();
	}
	
	this.getNextChildAfter = function(_AfterID){
		
		var AfterIndex = this.findChildNode(_AfterID)
		
		if(AfterIndex==(this.ChildNodes.length-1)){
			
			return null
		}
		return this.ChildNodes[parseInt(AfterIndex)+1]
	}
	
	this.expand = function(State){
		
		this._expand(State);
	}
	
	this._expand = function(State){
		
		if(typeof(State) == 'undefined'){
			
			State = this.getSetting('Expanded')?false:true;
		}
		this.setSetting('Expanded', State);
		getLayer(this.ID+'_expander').innerHTML = this.getSetting('Expanded')?this.colapse_bullet:this.expand_bullet;
		getLayer(this.ID+'_ul').style.display = this.getSetting('Expanded')?'block':'none';
	}
}

function expand(LI_ID){
	
	eval('var Node = '+getLayer(LI_ID).getAttribute('node'));
	if(!Node){
		alert('Doesnt set '+LI_ID);
	}
	Node.expand();
}