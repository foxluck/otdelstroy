function closeComment(commentId){

	var objBlock = getLayer(commentId);
	objBlock.style.display = "none";
	setCookie( commentId, 1, 1000, '/');
}

function checkGroupBoxState(group_box){

	group_box.checked = false;
	var boxes = getElementsByClass(group_box.getAttribute('rel'), document, 'input');
	for(var i_max = boxes.length-1; i_max>=0; i_max--){
	
		if(boxes[i_max].checked) continue;
		group_box.checked = false;
		return;
	}
	group_box.checked = true;
}

function getCountCheckGroupBox(rel){
	var count = 0;
	var boxes = getElementsByClass(rel, document, 'input');
	for(var i_max = boxes.length-1; i_max>=0; i_max--){
		if(!boxes[i_max].checked) continue;
		count++;
	}
	return count;
}

function sc_submitAjaxForm(objForm){

    var req = new JsHttpRequest();
    req.onreadystatechange = function() {
        if (req.readyState == 4) {
       		if(req.responseText)alert(req.responseText);

			if(is_null(req.responseJS))return;

       		if(req.responseJS._AJAXMESSAGE){
       			
       			var msgEntry = new Message();
       			msgEntry.init(req.responseJS._AJAXMESSAGE);
       			msgEntry.showMessage();
       		}
        }
    }
	try {
		req.open('POST', set_query("&caller=1&initscript=ajaxservice"), true);
		req.send( { q: objForm } );
	} catch ( e ) { ; } finally {	;}
	return false;
}

Behaviour.register({

	'input.input_message': function(e){
	
		e.onfocus = function(){
			var value = this.getAttribute('rel');
			if(!value){
				value = this.getAttribute('title');
			}
			this.className = this.className.replace(/input_message/ ,'')+' input_message_focus';
			if(this.value != value)return;

			this.value='';
		}
		
		e.onblur = function(){
			if(this.value!='')return;
			this.className = this.className.replace(/input_message_focus/ ,'')+' input_message';
			var value = this.getAttribute('rel');
			if(!value){
				value = this.getAttribute('title');
			}
			this.value=value;
		}
	},

	'.confirm_action': function(element){
		
		element.onclick = function(){ 
			
			return window.confirm(this.getAttribute('title')); 
		}
	},
	
	'.new_window': function(element){
		
		element.onclick = function(){
			
			var wnd_width = this.getAttribute('wnd_width');
			var wnd_height = this.getAttribute('wnd_height');
			
			open_window(this.href?this.href:this.getAttribute('rel'), wnd_width, wnd_height);
			return false;
		}
	},
	'.ajaxform': function(e){
		e.onsubmit = function(){
		
			sc_submitAjaxForm(this);
			return false;
		}
	},
	'tr.gridline': function(e){
		e.onmouseover = function(){
			this.style.background = '#f5f0bb';
		}
		e.onmouseout = function(){
			this.style.background = '#fafae7';
		}
	},
	'tr.gridline1': function(e){
		e.onmouseover = function(){
			this.style.background = '#f5f0bb';
		}
		e.onmouseout = function(){
			this.style.background = '#ffffff';
		}
	},
	'input.goto': function(e){
		e.onclick = function(){var onPreClick =this.getAttribute('onpreclick');if(onPreClick != null);eval(onPreClick);
			if(this.className.search(/confirm/) !== -1 && !window.confirm(this.getAttribute('title')))return
			document.location.href = this.getAttribute('rel');
		}
	},
	'input.groupcheckbox': function(e){
		e.onclick = function(){
		
			var boxes = getElementsByClass(this.getAttribute('rel'), document, 'input');
			for(var i_max = boxes.length-1; i_max>=0; i_max--){
			
				boxes[i_max].checked = this.checked;
			}
		}
	},
	'input.checkbox': function(e){
		e.onclick = function(){
			checkGroupBoxState(getLayer(this.getAttribute('rel')));
			return;
		}
	},
	'.cancel_contentchanged': function(e){
		e.onclick = function(){
			beforeUnloadHandler_contentChanged = false;
			return true;
		}
	},
	'.expand_languages': function(e){
		e.onclick = function(){
			getLayer(this.getAttribute('rel')).style.display = 'block';
			this.style.visibility = 'hidden';
		}
	},
	'.fade_div': function (e){
		e.onclick = function(){
			if(!sswgt_CartManager)return;
			
			sswgt_CartManager.shop_url = (window.WAROOT_URL != null) ? window.WAROOT_URL : conf_full_shop_url;
			sswgt_CartManager.showLayer(this.getAttribute('rel'), this.getAttribute('wnd_width'), this.getAttribute('wnd_height'));
		}
	}
});