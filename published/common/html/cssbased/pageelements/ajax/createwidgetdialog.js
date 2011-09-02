var CreateWidgetDialog = function(){
	var checkedRadio = null;
	var wgId = null;
	var nextBtn = null;
	var wgName = "";
	var urlStart = "";
	var urlHash = "";
	var dlgDom;
	var selectedTypeId = null;
	var selectedSubtypeId = null;
	
	return {
		init: function () {
			if (document.getElementById("create-widget-dlg") != null)
				document.body.removeChild(document.getElementById("create-widget-dlg"));
			
			dlgDom = document.createElement("div");
			dlgDom.className = "PopupDialog";
			dlgDom.id = "create-widget-dlg";
			
			document.body.appendChild(dlgDom);
			
			var dialogContent = Ext.get("createwidget-dlg-content");
			//dlgDom.appendChild(dialogContent.dom);
			//dialogContent.dom.style.display = "block";
							
      var dialog = new Ext.Window({el: dlgDom, 
        contentEl: dialogContent,
        closeAction: 'hide',
        autoTabs:false, shadow:true,
        width:582, height:395,
        minWidth:420, minHeight:285, resizeHandles: "se", 
        title: CreateWidgetDialog.title, modal: true, collapsible: false
      });
      //dialog.addKeyListener(27, dialog.hide, dialog);
      CreateWidgetDialog.nextBtn = dialog.addButton(CreateWidgetDialog.strings.createBtn, CreateWidgetDialog.nextClicked, dialog);
      //CreateWidgetDialog.nextBtn.getEl().dom.id="shareDialogLink";
      //CreateWidgetDialog.nextBtn.disable();
      //dialog.body.appendChild (dialogContent.dom);
      //dialogContent.dom.style.display = "block";
      dialog.addButton(CreateWidgetDialog.strings.cancelBtn, dialog.hide, dialog);
      
      dialogContent.dom.dialog = dialog;
      return dialog;
		},
		
		show: function (fromElem) {
			//if (CreateWidgetDialog.wgId == null) {
			//	alert ("Unsetted widget id"); 
			//	return false;
			//}
			dialogContentDom = document.getElementById("createwidget-dlg-content");
			var dialog = dialogContentDom.dialog;
			
			if(dialog == null) {
				dialog = CreateWidgetDialog.init ();
			}
				
			CreateWidgetDialog.selectedTypeId = null;
			CreateWidgetDialog.selectedSubtypeId = null;
			CreateWidgetDialog.nextBtn.disable();
			if (CreateWidgetDialog.checkedRadio != null)
				CreateWidgetDialog.checkedRadio.checked = false;
			
	    
	    dialog.show(fromElem);
		},
		
		nextClicked: function () {
			CreateWidgetDialog.trySelect ();
		},
		
		/****
		* Try Send widget data
		****/
		trySelect: function () {
  		var type = null;
  		var dialog = document.getElementById("createwidget-dlg-content").dialog;
  		
  		type = CreateWidgetDialog.selectedTypeId;
  		subtype = CreateWidgetDialog.selectedSubtypeId;
  		if (type == null) {
  			alert(CreateWidgetDialog.strings.noselectError);
  			return;
  		}
  		
  		
  		dialog.hide ();
  		myDlg = document.dialogs[type+subtype];
  		if (myDlg == null)
  			alert("Cannot find dialog form: " + type+subtype);
  		else {
  			myDlg.subtype = subtype;
  			myDlg.showDialog(null, type, subtype, {});
  		}
  	},
  	
  	
  	selectCreateWidgetType: function(typeId, subtypeId, radio) {
  		CreateWidgetDialog.checkedRadio = radio;
			CreateWidgetDialog.selectedTypeId = typeId;
			CreateWidgetDialog.selectedSubtypeId = subtypeId;
			CreateWidgetDialog.nextBtn.enable ();				
		}
	};	  
}();
	
function createWidgetDialog(btn) {
	CreateWidgetDialog.show (btn);
}