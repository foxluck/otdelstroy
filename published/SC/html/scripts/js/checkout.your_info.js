var checkoutYourInfo_handlers = {
	'.hndl_show_login': function(elem){
		elem.onclick = function(){
		
			getLayer('block-customerinfo').style.display = getElementComputedStyle('block-customerinfo', 'display')=='none'?"block":"none";
			getLayer('block-auth').style.display = getLayer('block-customerinfo').style.display=='none'?"block":"none";
			return false;
		}
	},
	'#hndl-show-billing-address': function(e){
		e.onclick = function(){
			getLayer('block-billing-address').style.display = this.checked?"none":"block";
		}
	},
	'#hndl-show-loginpass-fields': function(e){
		e.onclick = function(){
			getLayer('block-loginpass-fields').style.display = this.checked?"block":"none";
		}
	},
	'.country_box': function(e){
		e.onchange = function(){
			var objForm = getFormByElem(this);
			objForm['action'].value = 'update_form';
			objForm.submit();
			return false;
		}
	},
	'.autofill': function(e){
		e.onfocus = function(){
			if(this.value)return;
			
			var obj = getLayer(this.getAttribute('rel'));
			if(!obj)return;
			
			this.value = obj.value;
		}
	}
}

Behaviour.register(checkoutYourInfo_handlers);