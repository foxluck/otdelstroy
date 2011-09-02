UsersSelector = function (config) {
	config.idFrom = "availableUsersSelect";
	config.idTo = "assignedUsersSelect";
	config.btnLeftId = "usersSelectorLeftBtn";
	config.btnRightId = "usersSelectorRightBtn";
	if (!config.selectSize)
		config.selectSize = 5;
	this.toTitle = config.toTitle;
	this.fromTitle = config.fromTitle;
	
	UsersSelector.superclass.constructor.call (this,
		config);
	//new GenericItemsSelector("availableUsersSelect", "assignedUsersSelect", availableUsers, "usersSelectorLeftBtn", "usersSelectorRightBtn");
}

Ext.extend(UsersSelector, GenericItemsSelector, {
	getHTML: function () {
		return "<table width='100%' cellspacing=0 cellpadding=0><tr><td width='48%'><label class='x-form-item-label x-form-item'>" + this.toTitle + "</label><select id='assignedUsersSelect' style='width: 100%; overflow-x: hidden' size=" + this.config.selectSize + " multiple=true></select></td><td style='vertical-align: middle'><a href='#' onClick='' id='usersSelectorLeftBtn' class='IconButton'><span class='Left'></span></a><a href='#' onClick='' id='usersSelectorRightBtn'  class='IconButton'><span class='Right'></span></a></td><td width='45%'><label class='x-form-item-label x-form-item'>" + this.fromTitle + "</label><select id='availableUsersSelect' style='width: 97%; overflow-x: hidden' multiple size=" + this.config.selectSize + "></select></td></tr></table>"
	},
			
	initialize: function () {
		document.getElementById(this.config.renderTo).innerHTML = this.getHTML ();
		
		UsersSelector.superclass.initialize.call (this);
	}
});