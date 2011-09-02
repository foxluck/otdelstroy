Behaviour.register({
	'.chckrpl_module_switch': function(e){
		e.onclick = function(){
		
			var enable = this.value == translate.btn_enable;
			var req = new JsHttpRequest();
			
			req.onreadystatechange = function(){
	
				if (req.readyState != 4)return;
				if(req.responseText){
					alert(req.responseText);
				}
			};
 
			try {
				req.open(null, set_query('&caller=1&initscript=ajaxservice'), true);
				req.send({'action': 'change_module_state','module_id':this.getAttribute('rel'), 'enable': enable?1:0});
			} catch ( e ) {
				catchResult(e);
			} finally {	;}
			
			getLayer('chckrpl-settings-'+this.getAttribute('rel')).style.display = enable?'':'none';
			this.value = enable?translate.btn_disable:translate.btn_enable;
			var state_label = getLayer('chckrpl-settings-'+this.getAttribute('rel')+'-state');
			if(state_label){
				state_label.innerHTML = enable?translate.state_enabled:translate.state_disabled;
				state_label.style.color = (enable ? 'green' : 'red');
			}
		}
	}
});

Nifty("div.chckrpl_module_block","");