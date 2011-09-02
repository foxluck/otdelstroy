function InputAutocompete(){
	
	this.beforeReg = /[^\s,][^,]+$/;
	this.afterReg = /^[^,]+/;
	this.fieldElem = null;
	
	this.all_variants = new Array();
	this.last_word = '';
	this.menu = null;
	
	this.init = function (fieldElem){
		
		fieldElem.inpautocomp = this;
		
		fieldElem.onkeyup = function(event){
	
			event = event? event:window.event
			switch (event.keyCode ? event.keyCode : event.which ? event.which : null)
			{
				case 0x28://down
				case 0x26://up
				case 0x1B://exit
				case 0xD://enter
					return false;
			}
		}

		fieldElem.onkeydown = function(event){
			
			event = event? event:window.event
			var keyCode = event.keyCode ? event.keyCode : event.which ? event.which : null;
			switch (keyCode)
			{
				case 0x28://down
				case 0x26://up
				case 0x1B://exit
				case 0xD://enter
					return false;
			}
		}
		fieldElem.onkeyup = function(event){
	
			event = event? event:window.event
			var keyCode = event.keyCode ? event.keyCode : event.which ? event.which : null;
			switch (keyCode)
			{
//				case 0x25:
//				case 0x27:
//					return true;
				case 0x28://down
				case 0x26://up
				case 0x1B://exit
				case 0xD://enter
					return false;
			}
			
			var inpautocomp = this.inpautocomp;
			var input_word = inpautocomp.matchWord('');

			if(input_word == inpautocomp.last_word)return;
			
			var menu = new _DropMenu({pid:this.id});
			if(input_word.length<2)return;
			
			inpautocomp.last_word=input_word;
			
			var tags = new Array();
			var pattern = new RegExp(input_word, "i");
			var flag = false;
			var all_variants = inpautocomp.all_variants;
			menu.__inputautocomp = inpautocomp;
			
			for(var i=0, len=all_variants.length;i<len;i++){
				
				if(!pattern.test(all_variants[i]))continue;
				menu.addPoint({title:all_variants[i],href:"#",onclick:"_dropMenu.__inputautocomp.setWord('"+all_variants[i]+"', _dropMenu);v_return=false;"});
				flag=true;
			}
			
			inpautocomp = null;
			all_variants= null;
			
			if(!flag)return;
			menu.init();
			menu.display(true);
		}
		
		
		if(document.onkeyup){
			
			var old_onkeyup = document.onkeyup;
		}else{
			var old_onkeyup = function(){};
		}
		
		document.onkeyup = function (event){
			
			event = event? event:window.event
		
			if(_dmManager.focus){
				var res = _dmManager.focus.onKey(event.keyCode ? event.keyCode : event.which ? event.which : null)
				if(!res)return false;
				
			}
			
			old_onkeyup(event);
		}		
		
		this.fieldElem = fieldElem;
	}
	
	this.old_onkeyup = function (event){
		
	}
	
	this.matchWord = function (add_char){

		var fieldElem = this.fieldElem;
		if( document.selection ){ // The current selection 
			fieldElem.selectionStart = Math.abs(document.selection.createRange().moveStart("character", -1000000)); 
			fieldElem.selectionEnd = Math.abs(document.selection.createRange().moveEnd("character", -1000000)); 
		 }
		if (fieldElem.selectionStart || fieldElem.selectionStart == '0'){//MOZILLA/NETSCAPE support
			
			var after_text = fieldElem.value.substring(fieldElem.selectionEnd, fieldElem.value.length);
			if(after_text.match(/^\w/))return '';
			
			var before_text = fieldElem.value.substring(0, fieldElem.selectionStart)+add_char;
			before_text = before_text.match(this.beforeReg);
			return before_text?before_text.toString():'';
		}
		fieldElem = null;
		return '';
	}

	this.setWord = function (word, dropMenu){
		
		word += ',';
		var fieldElem = this.fieldElem;
		
		if (fieldElem.selectionStart || fieldElem.selectionStart == '0'){//MOZILLA/NETSCAPE support
			
			var before_text = fieldElem.value.substring(0, fieldElem.selectionStart);
			var after_text = fieldElem.value.substring(fieldElem.selectionEnd, fieldElem.value.length);
			
			before_text = before_text.replace(this.beforeReg,"");
			after_text = after_text.replace(this.afterReg,"");
			fieldElem.value = before_text.toString()+word+after_text.toString();
		}
		
		dropMenu.display(false);
		
		fieldElem = null;
	}
}