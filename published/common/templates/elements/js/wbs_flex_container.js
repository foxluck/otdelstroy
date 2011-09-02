WbsFlexContainer = newClass (WbsObservable, {
	constructor: function (config) {
		this.config = config;
		
		this.contentElem = config.contentElem;
		this.elem = config.elem;
		if (!this.contentElem)
			throw "No exists content elem for WbsFlexContainer";
		addClass(this.elem, "wbs-flexcontainer");
		this.childElems = new Array ();
		
		this.buildPage();
		WbsFlexContainer.addContainer(this);
		
		this.superclass().constructor.call(this);
		
		this.addEvents({
			"resize" : true
		});
	},
	
	addChildElem: function(childElem) {
		this.childElems[this.childElems.length] = childElem;
	},
	
	buildPage: function () {
		if (this.config.headerElem) {
			addClass(this.config.headerElem, "wbs-flexcontainer-header");
			this.addChildElem(this.config.headerElem);
		}		
		
		addClass(this.contentElem, "wbs-flexcontainer-content");
		this.addChildElem(this.contentElem);
		
		if (this.config.footerElem) {
			addClass(this.config.footerElem, "wbs-flexcontainer-footer");
			this.addChildElem(this.config.footerElem);
		}
	},
	
	resize: function () {
		var minusHeight = 0;
		for (var i = 0; i < this.childElems.length; i++) {
			var childElem = this.childElems[i];
			if (childElem == this.contentElem)
				continue;
			minusHeight += childElem.offsetHeight;
		}
		this.contentElem.style.height = (this.elem.offsetHeight - minusHeight) + "px";
		this.fireEvent("resize");
	},
		
	parentResized: function() {
		this.resize();
	}
});

WbsFlexContainer.containers = new Array ();
WbsFlexContainer.addContainer = function(container) {
	var parentFinded = false;
	for (var i = 0; i < WbsFlexContainer.containers.length; i++) {
		var otherContainer = WbsFlexContainer.containers[i];
		if (otherContainer == container)
			continue;
		if (nodeIsParentOf(container.elem, otherContainer.elem)) {
			parentFinded = true;
			otherContainer.addListener("resize", container.parentResized, container);
		}
	}
	if (!parentFinded) {
		addHandler(window,"resize",container.resize,container);
		addHandler(window,"load",container.resize,container);
	}		
	WbsFlexContainer.containers.push(container);
}
function nodeIsParentOf(node, parentNode) {
	var currentParent = node;
	while (currentParent && currentParent != window) {
		if (currentParent == parentNode)
			return true; 
		currentParent = currentParent.parentNode;
	}
	return false;
}