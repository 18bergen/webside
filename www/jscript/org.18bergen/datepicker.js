/*
	class: DatePicker
	
*/
function DatePicker(containerId, options) {

	this.init = function() {
		var args = {
			changeMonth: true,
			changeYear: true
		};
		if (options) {
			if (options.maxDate) args.maxDate = options.maxDate;
			if (options.minDate) args.minDate = options.minDate;
			if (options.onSelect) {
				args.onSelect = options.onSelect;
			}
		}
		$('#' + containerId).datepicker(args);
	};

}

