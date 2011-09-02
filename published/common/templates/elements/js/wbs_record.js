WbsRecord = newClass(WbsObservable, {
	constructor: function(recordData) {
		this.superclass().constructor.call(this);
		
		this.addEvents({
			"modified" : true			
		});
		
		this.loadFromData(recordData);
		if (this.data.id)
			this.id = this.data.id;
	},
		
	loadFromData: function(data) {
		this.data = data;
		var fields = this.getFields();
		if (data.id)
			this.id = data.id;
		for (var i = 0; i < fields.length; i++) {
			var field = fields[i];
			var value = data[field.name];
			if (field.convert) {
				value = field.convert(value);
			}
			this[field.name] = value;
		}
	},
		
	getFields: function() {
		return[];
	},
		
	getId: function() {
		return this.id;
	}	
});