function listDoubleClickHandler(ev, id){
	
	var cpt_tpl_id = id.replace("cpt-tpl-id-", "");
    
	url = set_query('?ukey=cpt_settings&cpt_tpl_id='+cpt_tpl_id+'&theme_id='+theme_id);

	sswgt_CartManager.shop_url = (window.WAROOT_URL != null) ? window.WAROOT_URL : './';
	sswgt_CartManager.show(url, 500, 300);
	 	
    return false;
}

function setActiveStyleSheet(title) {
  title = 'overridestyles';
  var i, a, main;
  for(i=0; (a = document.getElementsByTagName("link")[i]); i++) {
    if(a.getAttribute("rel").indexOf("style") != -1 && a.getAttribute("title")) {
      a.disabled = true;
      if(a.getAttribute("title") == title){ a.disabled = false;}
    }
  }
}

function applyOverrideStyle(cache_cssfile){
	getLayer('orig-overridestyles').disabled = true;
	getLayer('overridestyles').setAttribute('href', cache_cssfile);
	setActiveStyleSheet('overridestyles');
}

function loadContainer(cptHTML, pos){
	
      // Make a copy of the current item and put it in our drag helper.
	var tblElem = document.createElement('TABLE');
	var rowElem = tblElem.insertRow(tblElem.rows.length);
	var cellElem = rowElem.insertCell(rowElem.cells.length);
	cellElem.innerHTML = cptHTML;
	cellElem.className = "ssTmpContainer";
	
	var page_size = getPageSize();
	tblElem.style.position="absolute";
	tblElem.style.left=(page_size[0]-210)+"px";
	tblElem.style.top=pos.y+"px";
	tblElem.style.zIndex = '1000';

	document.body.appendChild(tblElem);
	
	cpt_cleanupSubContainers();
	
	addToDragList(dragList, cellElem);
}

function checkTrash(){
	
	getLayer("ssTrashBin").innerHTML = getLayer("ssTrashBin").getAttribute('rel');
}

function cpt_saveSlip(temp_saving, result_handler){

	var slip = {};
	var containers = getElementsByClass("cpt_container", null, "td");
	for(var i=0,len=containers.length;i<len;i++){
		
		slip[containers[i].id] = new Array('-1');
		var cpts = getElementsByClass("cpt_wrapper", containers[i], "div");
		for(var k=0,lenk=cpts.length;k<lenk;k++){
			slip[containers[i].id].push(cpts[k].id);
		}	
	}	

	var req = new JsHttpRequest();
	var _result_handler = temp_saving===true?result_handler:null;
    req.onreadystatechange = function() {

        if (req.readyState == 4) {
        	
       		beforeUnloadHandler_contentChanged = false;
       		if(req.responseText)alert(req.responseText);
       		if(req.responseJS && req.responseJS._AJAXMESSAGE){
       			
       			var msgEntry = new Message();
       			msgEntry.init(req.responseJS._AJAXMESSAGE);
       			if(_result_handler !== null){
       				_result_handler(msgEntry);
       			}else{
	       			msgEntry.showMessage();
       			}
       		}
       		
        }
    };
	try {
		req.open("POST", document.location.href+"&caller=1&initscript=ajaxservice", true);
		req.send( { 'action': 'CPT_SAVE_SLIP', 'slip': slip, 'temp_saving': temp_saving === true, 'contentChanged': (window.__templateChanged?1:0) } );
	} catch ( e ) {;} finally {	;}
}

function cpt_cleanupSubContainers(){
	
	var sub_containers = document.getElementsBySelector("div.cpt_wrapper div.cpt_wrapper");
	for(var i=0,len=sub_containers.length;i<len;i++){
		sub_containers[i].className = sub_containers[i].className.replace(/cpt_wrapper/, '');
	}
	
	var sub_containers = document.getElementsBySelector("td.cpt_container td.cpt_container");
	for(var i=0,len=sub_containers.length;i<len;i++){
		sub_containers[i].className = sub_containers[i].className.replace(/cpt_container/, '');
	}
}

Behaviour.addLoadEvent(function(){
	
	cpt_cleanupSubContainers();
	
	document.onmousemove = mouseMove;
	document.onmouseup   = mouseUp;

	dragList = new DragList();
/*	
	dragList.unselectedColor = "#FFFFFF";
	dragList.selectedBorderColor = "#EEEEEE";
	dragList.selectedColor = "blue";
*/	

	dragList.unselectedColor = "";
	dragList.selectedBorderColor = "";
	dragList.selectedColor = "";
	
	dragList.instanceName = "requestList";
	dragList.current = "";

	// Create our helper object that will show the item while dragging
	dragHelper = document.createElement('DIV');
	dragHelper.id = "ssDragHelper";
	dragHelper.style.cssText = 'position:absolute;display:none;minwidth:150px;';

	CreateDragList(dragList);

	var containers = getElementsByClass("cpt_container", null, "td");
	for(var i=0,len=containers.length;i<len;i++){

		addToDragList(dragList, containers[i]);
	}	

	document.body.appendChild(dragHelper);
	
}
);

Behaviour.register({
	/* Expand Embed and Settings button*/

	'a': function(element){
		
		if(element.className.search(/cpt_dontblock/) == -1){
			element.onclick = function(){ return false; };
		}
	}, 
	'input': function(element){
		
		if(element.className.search(/cpt_dontblock/) == -1){
			element.onclick = function(){ return false; };
		}
	},
	'select': function(element){
		
		if(element.className.search(/cpt_dontblock/) == -1){
			element.onchange = function(){ return false; };
		}
	},
	'form': function(element){
		
		if(element.className.search(/cpt_dontblock/) == -1){
			element.onsubmit = function(){ return false; };
		}
	},

	'.cpt_addcomponent_hndl': function(element){
		
		element.onclick = function(){

			var cpt_id = this.id.replace("cpt-component-", "");
			changeState("cpt-lsettings-"+cpt_id);
			return false;
		};
	},
	
	"input.cptlsettings_submit": function(elem){
		
		element.onclick = function(){

			var cpt_id = this.id.replace("cptlsettings-submit-", "");
			var submitElem = this;
			submitElem.disabled = true;
			
		    var cptPos = getPosition(getLayer("cpt-component-"+cpt_id));
		    cptPos.y += this.offsetHeight;
		    
		    // Create new JsHttpRequest object.
		    var req = new JsHttpRequest();
		    // Code automatically called on load finishing.
		    req.onreadystatechange = function() {
		
		        if (req.readyState == 4) {
		        	
					submitElem.disabled = false;
		        		if(req.responseText)alert(req.responseText);
					if(is_null(req.responseJS))return;

		        		if(req.responseJS._AJAXMESSAGE){
		        			
		        			var msgEntry = new Message();
		        			msgEntry.init(req.responseJS._AJAXMESSAGE);
		        			
		        			msgEntry.showMessage();
		        			
		        			if(!msgEntry.isSuccess()){
		        				return;
		        			}
		        		}
					loadContainer(req.responseJS.cptHTML, cptPos);
		        }
		    };
			try {
				req.open("GET", document.location.href+"&caller=1&initscript=ajaxservice", true);
				req.send( { q: getLayer("cptlsettings-form-"+cpt_id) } );
			} catch ( e ) {
				submitElem.disabled = false;
			} finally {	;}
		    return false;
		};
	},

	'#fm-save-template': function(elem){
		
		elem.onclick = cpt_saveSlip;
	},
	
	'#lsettings-mod-form-submit': function(elem){
		
		elem.onclick = function(){

			var submitElem = this;
			submitElem.disabled = true;

		    var req = new JsHttpRequest();
		    req.onreadystatechange = function() {
		
		        if (req.readyState == 4) {
		        	
					submitElem.disabled = false;
		        	if(req.responseText){alert(req.responseText);}
					if(is_null(req.responseJS)){return;}

	        		if(req.responseJS._AJAXMESSAGE){
	        			
	        			var msgEntry = new Message();
	        			msgEntry.init(req.responseJS._AJAXMESSAGE);
	        			msgEntry.showMessage();
	        			
	        			if(msgEntry.isSuccess()){
	        				
	        				var params = msgEntry.getParams();
	        				
	        				var component = getLayer('cpt-tpl-id-'+params.cpt_tpl_id);

	        				component.innerHTML = params.cptHTML;
	        				hideLSettingsModForm();
	        			}
	        		}
		        }
		    };

			try {
				req.open(null, document.location.href+"&caller=1&initscript=ajaxservice", true);
				req.send( { q: getLayer("lsettings-mod-form") } );
			} catch ( e ) {
				submitElem.disabled = false;
			} finally {	;}
		    return false;
		};
	}
});

function updateComponentView(cpt_id, new_html){
	var component = getLayer(cpt_id);
   	component.innerHTML = new_html;
	beforeUnloadHandler_contentChanged = true;
	makeShield(component);
}

function onDropElement(){
	
	beforeUnloadHandler_contentChanged = true;
}

attachEventEx(document,"mousedown", function(){
	var obj = getLayer('dnd-dblckick-tooltip');
	obj.style.display = "none";
});