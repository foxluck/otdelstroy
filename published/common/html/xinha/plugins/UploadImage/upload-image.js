// JavaScript Document
function UploadImage(editor)
{
	this.editor = editor;
	var cfg = editor.config;
	var self = this;
	cfg.registerButton({
		id      : 'uploadimage',
		tooltip : HTMLArea._lc('Upload image'),
		image   : editor.imgURL('image.gif', 'UploadImage'),
		textMode: false,
		action  : function(editor){self.buttonPress(editor)}
	})

	cfg.addToolbarElement('uploadimage', 'inserthorizontalrule', 1);

	UploadImage._pluginInfo = {
		name         : 'UploadImage',
		version      : '1.0',
		developer    : 'Vlad Pen',
		developer_url: 'http://webasyst.net/',
		sponsor      : 'VNT',
		sponsor_url  : 'http://webasyst.net/',
		c_owner      : 'VNT',
		license      : 'none'
	};

	UploadImage.prototype.buttonPress = function(editor)
	{
		var el = editor._toolbarObjects.uploadimage.imgel;
		var pos = getAbsolutePos(el);

		var obj = document.getElementById('uploadImageDiv');

		if(obj && typeof(uploadImageBuffer) == 'undefined')
			uploadImageBuffer = obj.innerHTML;

		if(obj)
			document.body.removeChild(obj);

		obj = document.createElement('div');
		obj.id = 'uploadImageDiv';
		obj.style.position = 'absolute';
		obj.style.left = pos.x - 2 + 'px';
		obj.style.top = pos.y + el.offsetHeight + 'px';
		obj.innerHTML = uploadImageBuffer;

		document.body.appendChild(obj);

		obj.style.display = '';

		var obj = document.getElementById('insertVariableDiv');
		if(obj)
			obj.style.display = 'none';

		_editor = editor;
	}
}