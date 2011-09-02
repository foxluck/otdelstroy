function checkboxGroup(_GroupBoxID){
	
	this.GroupBoxID = _GroupBoxID
	this.BoxCollection = Array();
	
	this.addBox = function(_ID, _Settings){
		
		_Obj = document.getElementById(_ID)
		this.BoxCollection.push(_Obj)
		_Obj.spNum = _Settings["spNum"]
		_Obj.GroupObj = this
		eval(_Settings["evalCode"])
	}
	
	this.changeState = function(){
		
		var pObj  = document.getElementById(this.GroupBoxID)
		for(var i=0; i<this.BoxCollection.length; i++){
			
			this.BoxCollection[i].checked = !pObj.checked
			this.BoxCollection[i].click()
		}
	}
	
	this.checkState = function(){
		
		var noChecked = true
		for(var i=0; i<this.BoxCollection.length; i++){
			
			if(this.BoxCollection[i].checked){
				noChecked = false
				break
			}
		}
		if(noChecked){
			
			var pObj  = document.getElementById(this.GroupBoxID)
			pObj.checked = false
		}
	}
}