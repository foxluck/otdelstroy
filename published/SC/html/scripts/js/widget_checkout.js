if(!sswgt_CartManager){

var sswgt_CartManager = {
	'prefix': 'my_',
	'mode': 'frame',
	'add2cart': function(objA){
		this.shop_url = objA.getAttribute('rel');
		sizes = getPageSize();
		this.show(objA.href+'&widgets=1', sizes[2]*0.7, sizes[3]*0.7);
		return false;
	},
	'go2cart': function(objA){
		this.shop_url = objA.getAttribute('rel');
		sizes = getPageSize();
		this.show(objA.href, sizes[2]*0.7, sizes[3]*0.7);
		return false;
	},
	'_show': function(width, height){
	
		this.hide(true);
		hideSelectBoxes();
		hideFlash();
		this.div();
		this.base();
		
		if(this.mode == 'frame'){
			this.frame(this.params.url, width, height);
		}else if(this.mode == 'layer'){
			this.layer(this.params.layer_id, width, height);
		}
		
		this.border(this.objMain);
		this.closeButton(this.objBorder);
		
		var old_onresize= window.onresize;
		window.onresize = function(){
			
			with(sswgt_CartManager){
			
				if(!(objOverlay && objDiv)){return;}

				var arrayPageSize = getPageSize();
				objOverlay.style.width = arrayPageSize[2]+'px';
				objOverlay.style.height = arrayPageSize[3]+'px';
				objBase.style.width = arrayPageSize[2]+'px';
				objBase.style.height = arrayPageSize[3]+'px';
				if(sswgt_CartManager.mode == 'frame'){
					sswgt_CartManager.frame(null, frameWidth, frameHeight);
				}else{ if(sswgt_CartManager.mode == 'layer'){
					this.layer(sswgt_CartManager.params.layer_id, frameWidth, frameHeight);
				}}
				border(sswgt_CartManager.objMain);
				closeButton(objBorder);
			};
			if(old_onresize){old_onresize();}
		};
		var old_onkeydown = document.onkeydown;
		document.onkeydown = function (event){
			
			event = event?event:window.event;
			if(event){
			switch (event.keyCode ? event.keyCode : event.which ? event.which : null){
				case 0x1B: sswgt_CartManager.hide(true);
			}}
			if(old_onkeydown){old_onkeydown();}
		};
	},
	'showLayer': function(layer_id, width, height){
		this.mode = 'layer';
		this.params = {'layer_id': layer_id};
		this._show(width, height);
		showSelectBoxes();
	},
	'show': function(url, width, height){
		this.mode = 'frame';
		this.params = {'url': url};
		this._show(width, height);
	},
	'resizeFrame': function(width, height){
		if(width != null){this.frameWidth = width;}
		if(height != null){this.frameHeight = height;}
		
		this.frame(null, this.frameWidth, this.frameHeight);
		this.border(this.objFrame);
		this.closeButton(this.objBorder);
	},
	'div': function(){
	
		this.objDiv = document.createElement('div');
		setStyle(this.objDiv, 'zIndex:100; position: absolute; left:0; top: 0;');// background:url("n.gif") no-repeat; backgroundAttachment:fixed');
		var objBody = document.getElementsByTagName("body").item(0);
		objBody.appendChild(this.objDiv);
		this.objDiv.style.backgroundAttachment = 'fixed';
	},
	'layer': function(layer_id, width, height){
	
		var objLayer = getLayer(layer_id);
		setStyle(objLayer,'visibility:hidden; display: block');
		if(!width){width = objLayer.offsetWidth;}
		if(!height){height = objLayer.offsetHeight;}

		this.frameWidth = width;
		this.frameHeight = height;
		var page = getPageSize();
		var left = (page[2] - width)/2;
		if(left<0){left=0;}
		var top = (page[3] - height)/2;
		if(top<10){top=10;}
		
		this.objBase.appendChild(objLayer);
		setStyle(objLayer, 'top:'+top+'px; left:'+left+'px; width:'+width+'px; height:'+height+'px;overflow: auto;zIndex:121; position:absolute;visibility:visible');
		
		this.objBase.style.visibility = 'visible';		
		this.objMain = objLayer;
	},
	'frame': function(url, width, height){
		this.frameWidth = width;
		this.frameHeight = height;
		var page = getPageSize();
		var left = parseInt((page[2] - width)/2);if(left<0){left=0;}
		var top = parseInt((page[3] - height)/2);if(top<10){top=10;}
		var objFrame = document.getElementById(this.prefix+'frame');
		if(!objFrame){

			var objT = document.createElement('div');
			objT.style.display = 'none';
			objT.innerHTML = '<iframe id="'+this.prefix+'frame" frameborder="0"></iframe>';
			this.objBase.appendChild(objT);
			objFrame = document.getElementById(this.prefix+'frame');
			this.objBase.appendChild(objFrame);
			this.objBase.removeChild(objT);
			setStyle(objFrame, 'zIndex:120; position:absolute; backgroundColor:#ffffff');
	
			var objLoading = document.createElement('div');
			this.objBase.appendChild(objLoading);
			setStyle(objLoading, 'backgroundColor:#ffffff; zIndex:121; position:absolute; padding:20px; left:'+parseInt(page[2]/2-50+20)+'px; top:'+parseInt(page[3]/2-50)+'px;visibility: visible;');
			var objImgLoading = document.createElement('img');
//			objImgLoading.src = this.shop_url+'published/SC/html/scripts/images_common/loading.gif';
			objImgLoading.src = ((window.CONF_ON_WEBASYST||(this.shop_url.search('webasyst.net')!=-1))?(this.shop_url.replace(/shop\//,'')+'shop/'):(this.shop_url+'published/SC/html/scripts/'))+'images_common/loading.gif';
			objLoading.appendChild(objImgLoading);
	
			setTimeout(
					function(){
						objFrame.src = url;
						}
					,100);
			var objBase = this.objBase;
						
			function objFrame_onload(){
				if(objFrame.style.visibility != 'visible'){
					var objLoading1 = objLoading;
					var objBase1 = objBase;
					setTimeout(function(){
						if(objLoading1 && objLoading1.parentNode){
							objLoading1.parentNode.removeChild(objLoading1);
							objLoading1 = null;
						}
						objBase1.style.visibility = 'visible';}, 800);
				}
			};

			if (objFrame.addEventListener) objFrame.addEventListener("load",objFrame_onload,false);
			else if (objFrame.attachEvent) objFrame.attachEvent("onload", objFrame_onload);

			this.objFrame = objFrame;
			this.objMain = this.objFrame;	
		}
		setStyle(objFrame, 'top:'+top+'px; left:'+left+'px; width:'+width+'px; height:'+height+'px');
	},
	'base': function(){

		if(!/MSIE/.test(navigator.userAgent)){
			var objBase = document.createElement("div");
			objBase.style.visibility = 'hidden';
		}else{
			this.objDiv.innerHTML += '<div'+' id="myBase" style=\'z-index:95; position:absolute; visibility:hidden; top: expression(parseInt(document.documentElement.scrollTop || document.body.scrollTop, 10)+"px"); left: expression(parseInt(document.documentElement.scrollLeft || document.body.scrollLeft, 10)+"px");\'></div>';
			var objBase = document.getElementById('myBase');
		}
		
		setStyle(objBase, 'zIndex:95');
		if(!/MSIE/.test(navigator.userAgent)){setStyle(objBase, 'top:0; left:0; position: fixed');}
	
		var arrayPageSize = getPageSize();
		objBase.style.width = arrayPageSize[2]+'px';
		objBase.style.height = arrayPageSize[3]+'px';
		
		objBase.onclick = function(ev){if(getEventObject(ev).target.id && getEventObject(ev).target.id == this.id){sswgt_CartManager.hide(true);}};
		
		this.objDiv.insertBefore(objBase, this.objDiv.firstChild);
		this.objBase = objBase;
		this.overlay();
	},
	'overlay':function(){
		var objOverlay = document.getElementById(this.prefix+'overlay');
		if(!objOverlay){
			objOverlay = document.createElement('div');
			objOverlay.id = this.prefix+'overlay';
			this.objBase.appendChild(objOverlay);
		}
 
		var left = 0;
		var top = 0;
		var width = this.objBase.offsetWidth;
		var height = this.objBase.offsetHeight;
		setStyle(objOverlay, 'position:absolute; visibility: visible; top:'+top+'; left:'+left+'; width:'+width+'px; height:'+height+'px; backgroundColor:#000000');
		setOpacity(objOverlay, 0.7);
		objOverlay.onclick = function(ev){if(getEventObject(ev).target.id && getEventObject(ev).target.id == this.id){sswgt_CartManager.hide(true);}};

		this.objOverlay = objOverlay;
	},
	'hide': function(remove){
		if(this.objDiv && this.objDiv.parentNode){
			if(this.objOverlay){
				this.objDiv.appendChild(this.objOverlay);
			}
			this.objDiv.style.display = "none";
			
			if(remove){
				if(this.mode == 'layer'){
					setStyle(this.objMain, 'display:none');
					setStyle(this.objMain, 'visibility:hidden');
					document.body.appendChild(this.objMain);
				}
				this.objDiv.parentNode.removeChild(this.objDiv);
				this.objDiv = null;
				this.objOverlay = null;
			}
			showSelectBoxes();
			showFlash();
		}
	},
	'closeButton': function(parentObject){
	
		with(this){
			var objCloseButton = document.getElementById(prefix+'closeButton');
			if(!objCloseButton){
				objCloseButton = document.createElement('img');
				objCloseButton.id = prefix+'closeButton';
			}
			
			var objFrame =  this.objFrame||document.getElementById(this.prefix+'frame');
			//TODO check it
/*
			var left = parentObject.offsetLeft+parentObject.offsetWidth-22;
			var top = parentObject.offsetTop-25;
			*/
			if(objFrame){
				var left = objFrame.offsetLeft+this.objFrame.offsetWidth-22;
	            var top = objFrame.offsetTop-25;
	           	setStyle(objCloseButton, 'position:absolute; top:'+top+'px; left:'+left+'px; cursor:hand;');//cursor:pointer; 
				//DEBUG:
				//alert(this.shop_url);
				//objCloseButton.src = ((window.CONF_ON_WEBASYST||(this.shop_url.search('webasyst.net')!=-1))?(this.shop_url.replace(/shop\//,'')):(this.shop_url+'published/SC/html/scripts/'))+'images_common/close.gif';
				objCloseButton.src = ((window.CONF_ON_WEBASYST||(this.shop_url.search('webasyst.net')!=-1))?(this.shop_url.replace(/shop\//,'')+'shop/'):(this.shop_url+'published/SC/html/scripts/'))+'images_common/close.gif';
	//			objCloseButton.src = this.shop_url+'published/SC/html/scripts/images_common/close.gif';
				objCloseButton.onclick = function(){sswgt_CartManager.hide(true);return false;};
				objBase.appendChild(objCloseButton);
			}
		};
	},
	'border': function(parentObj){
		var objBorder = document.getElementById(this.prefix+'border');
		if(!objBorder){
			objBorder = document.createElement('div');
			objBorder.id = this.prefix+'border';
			this.objBase.appendChild(objBorder);
		}

		var border_width = 0;
		var left = parseInt(parentObj.style.left,10)-border_width;
		var top = parseInt(parentObj.style.top,10)-border_width;
		var width = parentObj.offsetWidth;
		var height = parentObj.offsetHeight;
		if(/MSIE/.test(navigator.userAgent)){
			width += border_width*2;
			height += border_width*2;
		}		
		setStyle(objBorder, 'position:absolute; top:'+top+'; left:'+left+'; width:'+width+'px; height:'+height+'px; border: '+border_width+'px solid #efefef');
		this.objBorder = objBorder;
	}
};

function showSelectBoxes(){
	var selects = document.getElementsByTagName("select");
	for (i = 0; i != selects.length; i++) {
		selects[i].style.visibility = "visible";
	}
}

function hideSelectBoxes(){
	var selects = document.getElementsByTagName("select");
	for (i = 0; i != selects.length; i++) {
		if(!selects[i].className.match(/div_fade_select/)){
			selects[i].style.visibility = "hidden";
		}
	}
}

function showFlash(){
	var flashObjects = document.getElementsByTagName("object");
	for (i = 0; i < flashObjects.length; i++) {
		flashObjects[i].style.visibility = "visible";
	}
	var flashEmbeds = document.getElementsByTagName("embed");
	for (i = 0; i < flashEmbeds.length; i++) {
		flashEmbeds[i].style.visibility = "visible";
	}
}

function hideFlash(){
	var flashObjects = document.getElementsByTagName("object");
	for (i = 0; i < flashObjects.length; i++) {
		flashObjects[i].style.visibility = "hidden";
	}
	var flashEmbeds = document.getElementsByTagName("embed");
	for (i = 0; i < flashEmbeds.length; i++) {
		flashEmbeds[i].style.visibility = "hidden";
	}
}

function getPageSize(){
	
	var xScroll, yScroll;
	
	if (window.innerHeight && window.scrollMaxY) {	
		xScroll = window.innerWidth + window.scrollMaxX;
		yScroll = window.innerHeight + window.scrollMaxY;
	} else if (document.body.scrollHeight > document.body.offsetHeight){ // all but Explorer Mac
		xScroll = document.body.scrollWidth;
		yScroll = document.body.scrollHeight;
	} else { // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and Safari
		xScroll = document.body.offsetWidth;
		yScroll = document.body.offsetHeight;
	}

	var windowWidth, windowHeight;
	
	if (self.innerHeight) {	// all except Explorer
		if(document.documentElement.clientWidth){
			windowWidth = document.documentElement.clientWidth; 
		} else {
			windowWidth = self.innerWidth;
		}
		windowHeight = self.innerHeight;
	} else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
		windowWidth = document.documentElement.clientWidth;
		windowHeight = document.documentElement.clientHeight;
	} else if (document.body) { // other Explorers
		windowWidth = document.body.clientWidth;
		windowHeight = document.body.clientHeight;
	}	
	
	// for small pages with total height less then height of the viewport
	if(yScroll < windowHeight){
		pageHeight = windowHeight;
	} else { 
		pageHeight = yScroll;
	}

	// for small pages with total width less then width of the viewport
	if(xScroll < windowWidth){	
		pageWidth = xScroll;		
	} else {
		pageWidth = windowWidth;
	}

	arrayPageSize = new Array(pageWidth,pageHeight,windowWidth,windowHeight);
	return arrayPageSize;
}

function setOpacity(element, value){  
	
  if (value == 1){
  	element.style.opacity = (/Gecko/.test(navigator.userAgent) && !/Konqueror|Safari|KHTML/.test(navigator.userAgent)) ? 0.999999 : null;
    if(/MSIE/.test(navigator.userAgent)){  
      element.style.filter = element.style.filter.replace(/alpha\([^\)]*\)/gi, '');
     }  
  } else {  
    if(value < 0.00001){ value = 0;}  
  	element.style.opacity = value;
    if(/MSIE/.test(navigator.userAgent)){  
      element.style.filter = element.style.filter.replace(/alpha\([^\)]*\)/gi, '')+ 'alpha(opacity='+value*100+')';
    }  
  }   
}

function setStyle(obj, style_str){

	var styles = style_str.split(";");
	with(obj){
		for(var k=styles.length-1; k>=0; k--){
			var _style = styles[k].split(':', 2);
			if(!_style[1]){continue;}
			_style[0] = _style[0].replace(/^\s|\s$/, '');
			_style[1] = _style[1].replace(/^\s|\s$/, '');
			style[_style[0]] = _style[1];
		}
	};
}

function getEventObject(ev){
	
	var my_ev = {};
	ev = ev?ev:window.event;
	if(ev.srcElement){
		my_ev.target = ev.srcElement;
	}else{
		my_ev.target = ev.target;
	}
	
	my_ev.ev = ev;
	return my_ev;
}


}
