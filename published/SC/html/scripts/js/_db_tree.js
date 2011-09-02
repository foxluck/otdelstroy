if (document.layers) { // Netscape
    document.captureEvents(Event.MOUSEMOVE);
    document.onmousemove = captureMousePosition;
} else if (document.all) { // Internet Explorer
    document.onmousemove = captureMousePosition;
} else if (document.getElementById) { // Netcsape 6
    document.onmousemove = captureMousePosition;
}
// Global variables
xMousePos = 0; // Horizontal position of the mouse on the screen
yMousePos = 0; // Vertical position of the mouse on the screen
xMousePosMax = 0; // Width of the page
yMousePosMax = 0; // Height of the page

function captureMousePosition(e) {
    if (document.layers) {
        // When the page scrolls in Netscape, the event's mouse position
        // reflects the absolute position on the screen. innerHight/Width
        // is the position from the top/left of the screen that the user is
        // looking at. pageX/YOffset is the amount that the user has
        // scrolled into the page. So the values will be in relation to
        // each other as the total offsets into the page, no matter if
        // the user has scrolled or not.
        xMousePos = e.pageX;
        yMousePos = e.pageY;
        xMousePosMax = window.innerWidth+window.pageXOffset;
        yMousePosMax = window.innerHeight+window.pageYOffset;
    } else if (document.all) {
        // When the page scrolls in IE, the event's mouse position
        // reflects the position from the top/left of the screen the
        // user is looking at. scrollLeft/Top is the amount the user
        // has scrolled into the page. clientWidth/Height is the height/
        // width of the current page the user is looking at. So, to be
        // consistent with Netscape (above), add the scroll offsets to
        // both so we end up with an absolute value on the page, no
        // matter if the user has scrolled or not.
        xMousePos = window.event.x+document.body.scrollLeft;
        yMousePos = window.event.y+document.body.scrollTop;
        xMousePosMax = document.body.clientWidth+document.body.scrollLeft;
        yMousePosMax = document.body.clientHeight+document.body.scrollTop;
    } else if (document.getElementById) {
        // Netscape 6 behaves the same as Netscape 4 in this regard
        xMousePos = e.pageX;
        yMousePos = e.pageY;
        xMousePosMax = window.innerWidth+window.pageXOffset;
        yMousePosMax = window.innerHeight+window.pageYOffset;
    }
}
//Tree class
//
function dbTree(_Settings, _ParentNode){
	
//constructor	
	this.base = TreeNode;
	this.base(_Settings, _ParentNode);

	this.expand_bullet = '<img class="lnk_cursor" src="images_common/plus.gif" hspace="4" alt="+" title="+"/>';
	this.colapse_bullet = '<img class="lnk_cursor" src="images_common/minus.gif" hspace="4" alt="-" title="-"/>';
	this.expande_collapse = true
	
	this.evalAfterLoadNodes = '';
//methods
	this.addChildNode = function(_Settings){
		
		var Node = new dbTree(_Settings, this)
		return Node;
	}
	
	this.hasChildren = function(){
		
		return this.getSetting('isParent')?this.getSetting('isParent'):(this.ChildNodes.length);
	}
	
	this.onMouseOverNode = function(elem){

		elem.style.color = 'blue'
	}
	
	this.onMouseOutNode = function(elem){
		
		elem.style.color = 'black'
	}
	
	this.onClickNode = function(){
		
		if(!action)return;
		var ctManager = getCategoryTreeManager();
		ctManager.eval(action, 'onclick', this, window);
		return false;
	}
	
	this.expand = function(State){
		
		if(!this.expande_collapse)return false;
		
		if(typeof(State) == 'undefined'){
			
			State = this.getSetting('Expanded')?false:true;
		}
		
		if(!this.ChildNodes.length && State == true){
			
	    var req = new JsHttpRequest();
			var rootNode = this;

      req.onreadystatechange = function() {

          if (req.readyState == 4) { 
          	
          	if(req.responseText)alert(req.responseText);
          	if(!req.responseJS.categories)return;
          	
          	var categories = req.responseJS.categories;
          	EvalStr = '';
            for(var _d=0,max_d = categories.length; _d<max_d; _d++){

            		EvalStr += "window._nd_"+categories[_d]['categoryID']+" = _nd_"+categories[_d]['parent']+".addChildNode({'id':'_nd_"+categories[_d]['categoryID']+"','name':'"+categories[_d]['name']+"','categoryID':'"+categories[_d]['categoryID']+"','Expanded':true,'isParent':"+(categories[_d]['ExistSubCategories']?'true':'false')+"});\n";
            		EvalStr += "if(window._nd_"+categories[_d]['categoryID']+")window._nd_"+categories[_d]['categoryID']+".drawTree();\n";
            }
						eval(EvalStr);
						EvalStr = '';
						rootNode._expand(true);
						rootNode.hideLoadingMsg();
			      rootNode.expande_collapse = true;
          } 
      }

      this.expande_collapse = false;
			try {
	      req.open(null, window.url_getsubcategories+'&action=expandCategory&return_subs=1&categoryID='+this.getSetting('categoryID'), true); 
	      req.send({ q: 'query' }); 
			} catch ( e ) {
				catchResult(e);
			} finally {	;}

      this.showLoadingMsg();
		}else{
			
			if(this.getSetting('Expanded') != State){
				
		    var req = new JsHttpRequest();
				var rootNode = this;
	
	      req.onreadystatechange = function() {
	
	          if (req.readyState == 4) { 
	          	
	          	if(req.responseText)alert(req.responseText);
	          	
							rootNode._expand(rootNode.getSetting('Expanded'));
							rootNode.hideLoadingMsg();
				      rootNode.expande_collapse = true;
	          } 
	      }
	      
				this.setSetting('Expanded', State);
	      this.expande_collapse = false;
	
				try {
		      req.open(null, window.url_getsubcategories+'&action='+(this.getSetting('Expanded')?'expandCategory':'collapseCategory')+'&return_subs=0&categoryID='+this.getSetting('categoryID'), true); 
		      req.send({ q: 'query' }); 
				} catch ( e ) {
					catchResult(e);
				} finally {	;}
	
	      this.showLoadingMsg();
			}else{
				
				this._expand(State);
			}
		}
	}
	
	this.expandByChain = function(_IDChain){
		
		if(_IDChain.length){
		
			var nextNodeID = '_nd_'+_IDChain.shift();
			var evalStr = 'window.'+nextNodeID+'.expandByChain(['+_IDChain.join(',')+']);';
			this.evalAfterLoadNodes = evalStr;

		}
		this.expand(true);
	}
	
	this.showLoadingMsg = function(msg){
	
		var nodeDiv = createTag('div', document.body);
		nodeDiv.id = 'LoadingMsg_'+this.getID();
		nodeDiv.className = 'loading_msg';
		nodeDiv.style.left = xMousePos+15;
		nodeDiv.style.top = yMousePos+15;
		nodeDiv.innerHTML += '<img src="'+window.img_url+'/setup1.gif" alt=""/>'+(msg?msg:'');
	}
	
	this.hideLoadingMsg = function(){

		var objLoading = getLayer('LoadingMsg_'+this.getID());
		objLoading.parentNode.removeChild(objLoading);
	}
	
	this.afterDrawTree = function(){
	
		if(window.action == 'add_appendedcategory'){
		
			var catMan = getCategoryTreeManager();
			catMan.isAppendedCategory(this, window);
		}	
	}
}
