CommonSendDialog = function(dialogContentId) {
	this.dialogContentId = dialogContentId;
	this.strings = new Array ();
}

CommonSendDialog.prototype.start = function (prefix) {
	this.prefix = prefix;
}
	
	
CommonSendDialog.prototype.init = function () {
	this.defaultMessage = "<" + this.defaultMessage + ">";
	dlgDom = document.createElement("div");
	dlgDom.className = "PopupDialog";
	dlgDom.id = this.prefix+"-dlg";
	document.body.appendChild(dlgDom);
	
	
	var dialogContent = Ext.getDom(this.prefix+"-dlg-content");
	dialogContent.style.display = "block";
	//dlgDom.appendChild(dialogContent.dom);
	//dialogContent.dom.style.display = "block";
	
	this.contactsSelector = new ContactsSelector();
	this.contactsSelector.init (Ext.get(this.prefix+"contacts-selector"), dialogContent, this.prefix);
	
	this.contactsSelector.onEmailSelected = function (email) {
  	var currentValue = Ext.get(this.prefix+"to").dom.value;
  	if (currentValue != "")
  		currentValue += "; ";
  	currentValue += email;
  	Ext.get(this.prefix+"to").dom.value = currentValue;
  	Ext.get(this.prefix+"to").dom.scrollTop = 1000;
  }
	
	var height = (this.height != null) ? this.height : 400;
	
	this.dialog = new Ext.Window({ contentEl: dialogContent, 
    autoTabs:false, shadow:true,
    closeAction: 'hide',
    width:550, height:height,
    minWidth:550, minHeight:400,
    title: this.strings.title, modal: true, collapsible: false, resizeHandles: "se"
  });
  //this.dialog.addKeyListener(27, this.dialog.hide, this.dialog);
  nextBtn = this.dialog.addButton(this.strings.sendBtn, this.nextClicked, this);
  //nextBtn.disable();
  this.dialog.addButton(this.strings.cancelBtn, function() {this.hide()}, this.dialog);
},
		
CommonSendDialog.prototype.show = function (fromElem) {
	if(!this.dialog)
		this.init ();
	
  Ext.get(this.prefix+"to").dom.value = "";
	Ext.get(this.prefix+"subject").dom.value = this.subject;
	Ext.get(this.prefix+"message").dom.value = this.defaultMessage;
	
  this.contactsSelector.hide ();
  this.dialog.show(fromElem);
}

		
CommonSendDialog.prototype.showHideContacts = function () {
	this.contactsSelector.showHide ();
}

CommonSendDialog.prototype.showContacts = function () {
	this.contactsSelector.show ();
}
		
CommonSendDialog.prototype.nextClicked = function () {
	this.trySend ();
}

CommonSendDialog.prototype.getValues = function (){
	var values = new Array ();
	values.sendTo = Ext.get(this.prefix+"to").dom.value;
	values.sendSubject = Ext.get(this.prefix+"subject").dom.value;
	values.sendMessage = Ext.get(this.prefix+"message").dom.value;
	
	if (values.sendMessage == this.defaultMessage)
			values.sendMessage = "";
	
	return values;
}
		
/****
* Try Send widget data
****/
CommonSendDialog.prototype.trySend = function () {
	var sendTo = Ext.get(this.prefix+"to").dom.value;
	var sendSubject = Ext.get(this.prefix+"subject").dom.value;
	var sendMessage = Ext.get(this.prefix+"message").dom.value;
	
	if (sendTo.length < 6) {
		alert(this.strings.emailsError);
		return;
	}
	
	if (sendMessage == this.defaultMessage)
		sendMessage = "";
	
	try {
		var fapp = foldersTree.getWidgetsNode ().loader.baseParams.fapp;
	} catch (ex) {
		var fapp = "";
	}
	
	AjaxLoader.doRequest("../../../common/html/ajax/share_send.php", 
		this.trySendCompleted , 
		{"fapp": fapp, "sendData[to]": sendTo, "sendData[subject]": sendSubject, "sendData[message]": sendMessage, wgId: this.wgId}
	);
}
  	
/****
* Try Send completed
****/
CommonSendDialog.prototype.trySendCompleted = function(response, options){
	var result = Ext.decode(response.responseText);
	CommonSendDialog.showedDialog = this;
	if(result.success) {
		Ext.Msg.show({
		   title:'Result',
		   msg: result.resultStr,
		   buttons: Ext.Msg.OK,
		   fn: function() {CommonSendDialog.showedDialog.dialog.hide();}
		});
	} else {
		alert(result.errorStr);
	}
}