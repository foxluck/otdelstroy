function PageLink(editor)
{
	this.editor = editor;
	var cfg = editor.config;
	var self = this;

	cfg.registerButton({
		id       : "pagelink",
		tooltip  : this._lc("Insert link to another page"),
		image    : editor.imgURL("ed_pagelink.gif", "PageLink"),
		textMode : false,
		action   : function( editor ) {
			self.buttonPress(editor);
		}
	})

	cfg.addToolbarElement("pagelink", "inserthorizontalrule", 1);
};

PageLink._pluginInfo = {
	name          : "PageLink",
	version       : "1.0",
	developer     : "WebAsyst",
	developer_url : "http://www.webasyst.net/",
	c_owner       : "WebAsyst",
	sponsor       : "WebAsyst",
	sponsor_url   : "http://www.webasyst.net/",
	license       : "htmlArea"
};

PageLink.prototype._lc = function(string) {
	return HTMLArea._lc(string, 'PageLink');
}

PageLink.prototype.buttonPress = function(editor)
{
	outparam = {
		content : editor.getSelectedHTML()
	};

	editor._popupDialog( "../../../../../QP/html/scripts/getlink.php",
	function( entity )
	{
		if ( !entity )
		{
			//user must have pressed Cancel
			return false;
		}

		if (HTMLArea.is_ie)
			editor.focusEditor();

		editor.insertHTML( entity );

	}, outparam);
}