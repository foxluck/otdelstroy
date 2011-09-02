var currencyBlock = {
	
	objCurrencyBlock: null,

	getCID: function(){
		
		return this.objCurrencyBlock.getAttribute('cid');
	},
	
	initByChild: function(childElement){
		
		this.objCurrencyBlock = null;
		var max_i = 30;
		var p = childElement;
		var r = /^currency_block/;
		while(p && (!p.className || !r.test(p.className)) && --max_i){
			
			p = p.parentNode;
		};
		
		if(!r.test(p.className))return false;
		this.objCurrencyBlock = p;
	},
	
	displayForm: function(state){
		
		objForm = getElementByClass('currency_edit_form', this.objCurrencyBlock);
		
		if(is_null(state))state = objForm.style.display == 'none';
		
		objForm.style.display = state?'':'none';
		this.highlight(state);
	},
	
	highlight: function(state){
		
		if(state){
			this.objCurrencyBlock.className += " highlight";
		}else{
			this.objCurrencyBlock.className = this.objCurrencyBlock.className.replace(/\s?highlight/, '');
		}
	},
	
	getElement: function(className){
		
		return getElementByClass(className, this.objCurrencyBlock)
	},
	
	updateCurrencyInfo: function(data){

		for(var key in data){
			
			var r_objElems = getElementsByClass('ff_'+key, this.objCurrencyBlock);
			for(var l=r_objElems.length-1; l>=0; l--){
				
				r_objElems[l].innerHTML = data[key];
			}
		}
	}
}

var currenciesViewHandlers = {
	
	'.edit_currency_handler': function(element){
		element.onclick = function(){
			
			currencyBlock.initByChild(this);
			currencyBlock.displayForm();
		}
	},
	'.close_form_handler': function(element){
		element.onclick = function(){
			
			currencyBlock.initByChild(this);
			currencyBlock.displayForm(false);
		}
	},
	'.save_currency_handler': function(element){
		element.onclick = function(){
			
			currencyBlock.initByChild(this);
			if(!parseInt(currencyBlock.getCID())){
				
				var newCurrencyBlock = getLayer("new-currency").cloneNode(true);
				var emptyForm = getElementByClass('currency_edit_form', newCurrencyBlock);
			}
			
			
			var objButton = this;
			var objImageProcessing = currencyBlock.getElement('processing_image');
			objButton.style.display = "none";
			objImageProcessing.style.display = "";
			
	    var req = new JsHttpRequest();
	    req.onreadystatechange = function() {

        if (req.readyState == 4) {

       		if(req.responseText)alert(req.responseText);
					if(is_null(req.responseJS))return;

      		if(req.responseJS._AJAXMESSAGE){
      			var msgEntry = new Message();
      			msgEntry.init(req.responseJS._AJAXMESSAGE);
      			if(msgEntry.isSuccess()){
      				
	      			msgEntry.showMessage();
							currencyBlock.initByChild(objButton);
							currencyBlock.displayForm(false);
							if(!parseInt(currencyBlock.getCID())){							

								newCurrencyBlock.appendChild(currencyBlock.getElement('currency_edit_form'));
								currencyBlock.objCurrencyBlock.appendChild(emptyForm);
								
								currencyBlock.initByChild(newCurrencyBlock);
								currencyBlock.displayForm(false);
								
								getLayer("new-currencies-container").appendChild(newCurrencyBlock);
								newCurrencyBlock.style.display = "";
								currencyBlock.objCurrencyBlock.setAttribute('cid', req.responseJS.form_data.CID);
								currencyBlock.getElement('currency_edit_form').CID.value = req.responseJS.form_data.CID;
								Behaviour.apply();
							}
							currencyBlock.updateCurrencyInfo(req.responseJS.form_data);
      			}else{
      				
      				alert(msgEntry.getMessage());
      			}
      		}
     			
					objButton.style.display = "";
					objImageProcessing.style.display = "none";
				}
	    }
	    
			try {
				req.open(null, document.location.href.replace(/\#.*$/, '')+"&caller=1&initscript=ajaxservice", true);
				req.send( { q: currencyBlock.getElement('currency_edit_form') } );
			} catch ( e ) {
				catchResult(e);
			} finally {	;}
			
			return false;
		}
	},
	'.save_exchange_rate_handler': function(element){
		element.onclick = function(){
			
			currencyBlock.initByChild(this);
			
			var objButton = this;
			var objImageProcessing = currencyBlock.getElement('save_exchange_processing_image');
			objButton.style.display = "none";
			objImageProcessing.style.display = "";
			
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
     			
					objButton.style.display = "";
					objImageProcessing.style.display = "none";
				}
	    }
	    
			try {
				req.open('GET', document.location.href.replace(/\#.*$/, '')+"&caller=1&initscript=ajaxservice", true);
				req.send( { q:'', 'action': 'save_exchange_rate', 'CID': currencyBlock.getCID(), 'new_rate':currencyBlock.getElement('exchange_rate').value } );
			} catch ( e ) {
				catchResult(e);
			} finally {	;}
			
			return false;
		}
	},
	'.delete_currency_handler': function(element){
		element.onclick = function(){
			
			if(!window.confirm(translate.loc_del_confirmation))return;
			currencyBlock.initByChild(this);
			
	    var req = new JsHttpRequest();
	    req.onreadystatechange = function() {

        if (req.readyState == 4) {

       		if(req.responseText)alert(req.responseText);
				}
	    }
	    
			try {
				req.open('GET', document.location.href.replace(/\#.*$/, '')+"&caller=1&initscript=ajaxservice", true);
				req.send( { 'action': 'delete_curreny', 'CID': currencyBlock.getCID()} );
			} catch ( e ) {
				catchResult(e);
			} finally {	
				deleteTag(currencyBlock.objCurrencyBlock);
			}
			return false;
		}
	}

};

Behaviour.register(currenciesViewHandlers);