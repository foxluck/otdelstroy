WbsRightsMask = {
	canRead: function(rightsValue) {
		return rightsValue >= 1;
	},
		
	canWrite: function(rightsValue) {
		return rightsValue >= 3;
	},
	
	canFolder: function (rightsValue) {
		return rightsValue >= 7;
	},
		
	getRightsStr: function(rightsValue) {
		switch (parseInt(rightsValue)) {
			case 0:
				return WbsLocale.getCommonStr("rights_no");
			case 1:
				return WbsLocale.getCommonStr("rights_read");
			case 3:
				return WbsLocale.getCommonStr("rights_write");
			case 7:
				return WbsLocale.getCommonStr("rights_full");
			default: 
				return "Unknown rights: " + rightsValue;
		}
	}
}