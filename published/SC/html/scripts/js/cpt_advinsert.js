Behaviour.register({
	/* Expand Embed and Settings button*/
	'a.cpt_expand_act': function(element){
		
		element.onclick = function(){
			
			var cpt_id = this.id.replace("cpt-expand-act-", "");
			changeState("cpt-lsettings-"+cpt_id);
			return false;
		}
	}
	,
	/*Init global settings submit buttons*/
	"input.cptgsettings_submit": function(elem){
		
		elem.onclick = function(){
			
			var cpt_id = this.id.replace("cptgsettings-submit-", "");
			var submitElem = this;
			submitElem.disabled = true;
			
		    // Create new JsHttpRequest object.
		    var req = new JsHttpRequest();
		    // Code automatically called on load finishing.
		    req.onreadystatechange = function() {
		
		        if (req.readyState == 4) {

		        		var hide_settings = true;
		        		if(req.responseText)alert(req.responseText);
		        		if(req.responseJS && req.responseJS._AJAXMESSAGE){
		        			
		        			var msgEntry = new Message();
		        			msgEntry.init(req.responseJS._AJAXMESSAGE);

		        			hide_settings = AjaxCptHndls.call('save_gsettings', cpt_id, req.responseJS);
		        			msgEntry.showMessage();
		        		}
					submitElem.disabled = false;
					hide_settings = false;
					
					if(hide_settings)changeState("cpt-gsettings-"+cpt_id, false);
		        }
		    }
		    // Prepare request object (automatically choose GET or POST).
			try {
				req.open(null, document.location.href+"&caller=1", true);
				// Send data to backend.
				req.send( { q: getLayer("cptgsettings-form-"+cpt_id) } );
			} catch ( e ) {
				submitElem.disabled = false;
			} finally {
				;
			}
		
			return false;
		}
	}
	,
	/*Init local settings submit buttons*/
	"input.cptlsettings_submit": function(elem){
		
		elem.onclick = function(){
			
			var cpt_id = this.id.replace("cptlsettings-submit-", "");
			var submitElem = this;
			submitElem.disabled = true;
			
		    // Create new JsHttpRequest object.
		    var req = new JsHttpRequest();
		    // Code automatically called on load finishing.
		    req.onreadystatechange = function() {
		
		        if (req.readyState == 4) {
		        	
					submitElem.disabled = false;
					
					if(req.responseText)alert(req.responseText);
					var elemTplContent = getLayer("template-content");
					if(allowInsertAtCarret(elemTplContent)){
						
			        		if(req.responseJS._AJAXMESSAGE){
			        			
			        			var msgEntry = new Message();
			        			msgEntry.init(req.responseJS._AJAXMESSAGE);
			        			
			        			if(!msgEntry.isSuccess()){
								msgEntry.showMessage();
			        				return;
			        			}
			        		}
			        		
						insertAtCarret(elemTplContent, req.responseJS.cptSmarty);
					}else{
						alert("Not supporting insertion");
					}

					changeState("cpt-lsettings-"+cpt_id, false);
					changeState("cpt-act-"+cpt_id, false);
					elemTplContent = null;
		        }
		    }
		    // Prepare request object (automatically choose GET or POST).
			try {
				req.open('GET', document.location.href+"&caller=1&initscript=ajaxservice", true);
				// Send data to backend.
				req.send( { q: getLayer("cptlsettings-form-"+cpt_id) } );
			} catch ( e ) {
				submitElem.disabled = false;
			} finally {
				;
			}
		
			return false;
		}
	},
	
	'input#save-template-hndl': function(elem){
		
		elem.onclick = function(){

			cpt_saveTemplate();			
			return false;
		}
	},
	
	'#template-content': function(elem){
		
		elem.onchange = function(){
			
			beforeUnloadHandler_contentChanged = true;
		}
	}
});

function cpt_saveTemplate(temp_saving, result_handler){

	getLayer('sending-template-content').value = getLayer('template-content').value;
	beforeUnloadHandler_contentChanged = false;
	
    var req = new JsHttpRequest();
	var _result_handler = temp_saving===true?result_handler:null;
    req.onreadystatechange = function() {
        if (req.readyState == 4) {
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
    }
	try {
		req.open('POST', set_query("caller=1&initscript=ajaxservice&temp_saving="+(temp_saving===true?1:0)+'&contentChanged='+(window.__templateChanged?1:0)), true);
		req.send( { q: getFormByElem(getLayer('sending-template-content')) } );
	} catch ( e ) {alert(e);} finally {;}
}