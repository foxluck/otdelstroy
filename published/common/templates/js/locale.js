WbsLocale = {
	strings: [],
	
	loadStrings: function(section, strings) {
		if (!WbsLocale.strings[section])
			WbsLocale.strings[section] = {};
		WbsLocale.strings[section] = WbsLocale.strings[section].extend(strings);
		window[section + "Strings"] = WbsLocale.strings[section].extend(strings);
	},
	
	get: function (section, strName) {
		return  (WbsLocale.strings[section] && WbsLocale.strings[section][strName]) ?
			WbsLocale.strings[section][strName] : strName;
	},
		
	getCommonStr: function(strName) {
		return this.get("common", strName);
	}
};