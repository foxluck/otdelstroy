function getWndParent(wnd){
	
	return wnd.parent?wnd.parent:(wnd.top?wnd.top:null); 
}
function gb_getParent(){
	
	return parent?parent:(top?top:null); 
}

function adjust_window(){
	
	var pWindow = gb_getParent();

	var wndSize = getWindowSize(pWindow);
	var scr_h = wndSize[1] - 100;
	var wnd_h =  getLayer('wnd-content').offsetHeight+20;
	
	pWindow.sswgt_CartManager.resizeFrame(null, Math.max(Math.min(scr_h, wnd_h),300));
}

Behaviour.register({
	'#enabled-override': function(e){
		e.onclick = function(){
			if(this.checked){
				getLayer('ovst-description').style.display = 'none';
				getLayer('styles-block').style.display = 'block';
			}else{
				getLayer('styles-block').style.display = 'none';
				getLayer('ovst-description').style.display = 'block';
			}
			adjust_window();
		}
	},
	'#hndl-save-settings': function(e){
		e.onclick = function(){
		
		    var req = new JsHttpRequest();
		    req.onreadystatechange = function() {
		
		        if (req.readyState == 4) {
		        	
	        		if(req.responseText)alert(req.responseText);
					if(is_null(req.responseJS))return;

	        		if(req.responseJS._AJAXMESSAGE){
	        			
	        			var msgEntry = new Message();
	        			msgEntry.init(req.responseJS._AJAXMESSAGE);
	        			
	        			if(!msgEntry.isSuccess()){
		        			alert(msgEntry.getMessage());
	        				return;
	        			}
        				var params = msgEntry.getParams();
        				var pWindow = gb_getParent();
						pWindow.updateComponentView('cpt-tpl-id-'+params.cpt_tpl_id, params.cptHTML);
        				pWindow.applyOverrideStyle(params.overridecache_cssfile);

						getLayer('cpt-tpl-settings').innerHTML = params.cpt_tpl_settings;
						
						//getWndParent(pWindow).__alert(msgEntry.getMessage());
						pWindow.sswgt_CartManager.hide();
	        		}
		        }
		    }
			try {
				req.open('POST', set_query("&caller=1&initscript=ajaxservice"), true);
				req.send( { q: getFormByElem(this) } );
			} catch ( e ) { alert(e);
			} finally {	;}
			
		}
	}
});