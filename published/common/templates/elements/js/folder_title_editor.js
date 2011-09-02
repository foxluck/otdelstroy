function TitleEditor (config, titleElem) {
	this.config = config;
	
	this.folderData = config.folderData;
	
	this.titleElem = titleElem;
	this.parentElem = titleElem.parentNode;
	var editor = this;
	
	this.editElem = createDiv("title-editor");
	var input = createElem("input");
	input.type = "text";
	input.value = this.folderData.NAME;
	this.editElem.appendChild(input);
	this.editElem.input = input;
	
	this.createLinks = function() {
		var saveLink = createElem("a");
		saveLink.href = "javascript:void(0)";
		saveLink.innerHTML = "Save";
		saveLink.onclick = function(){editor.save()};
		this.editElem.appendChild(saveLink);
		
		var cancelLink = createElem("a");
		cancelLink.href = "javascript:void(0)";
		cancelLink.innerHTML = "Cancel";
		cancelLink.onclick = function(){editor.cancel()};
		this.editElem.appendChild(cancelLink);
	}
	
	this.createLinks();
	this.parentElem.replaceChild(this.editElem, this.titleElem);
	
	this.save = function() {
		var newValue = editor.editElem.input.value;
		
		Ext.Ajax.request ({
			url: this.config.saveUrl,
			params: {"folderID": editor.folderData.ID, newName: editor.editElem.input.value},
			success: function (response) {
				var result = Ext.decode(response.responseText);
				if (!result.success) {
					alert(result.errorStr);
					return;
				}
				if (editor.config.tree) {
					var node = editor.config.tree.getNode(editor.folderData.ID);
					node.setText(result.name);
				}
				editor.titleElem.textElem.innerHTML = newValue;
				editor.folderData.NAME = newValue;
				editor.close();
			}
		});
	}
	
	this.cancel = function() {
		editor.close();
	}
	
	this.close = function() {
		editor.parentElem.replaceChild(this.titleElem, this.editElem);
		delete editor;
	}
}