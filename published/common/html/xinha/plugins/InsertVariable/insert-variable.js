// JavaScript Document
function InsertVariable(editor)
{
	this.editor = editor;
	var cfg = editor.config;
	var self = this;
	cfg.registerButton({
		id      : 'insertvariable',
		tooltip : HTMLArea._lc('Insert variable'),
		image   : editor.imgURL('insert_vars.gif', 'InsertVariable'),
		textMode: false,
		action  : function(editor){self.buttonPress(editor)}
	})

	cfg.addToolbarElement('insertvariable', 'inserthorizontalrule', 1);

	InsertVariable._pluginInfo = {
		name         : 'InsertVariable',
		version      : '1.0',
		developer    : 'Vlad Pen',
		developer_url: 'http://webasyst.net/',
		sponsor      : 'VNT',
		sponsor_url  : 'http://webasyst.net/',
		c_owner      : 'VNT',
		license      : 'none'
	};

	InsertVariable.prototype.buttonPress = function(editor)
	{
		var el = editor._toolbarObjects.insertvariable.imgel;
		var pos = getAbsolutePos(el);

		var obj = document.getElementById('insertVariableDiv');
		obj.style.position = 'absolute';
		obj.style.left = pos.x - 2 + 'px';
		obj.style.top = pos.y + el.offsetHeight + 'px';
		obj.style.display = '';

		var obj = document.getElementById('uploadImageDiv');
		if(obj)
			obj.style.display = 'none';
	}
}
