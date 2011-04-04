/*
    class: BG18.datePicker
    
*/

/*
    Constructor: datePicker
    Initializes the object.
*/
/*
YUI().use('node','yui2-overlay', 'yui2-calendar', function(Y) {

	//This will make your YUI 2 code run unmodified
	var YAHOO = Y.YUI2;

	BG18.datePicker = function(containerId, options) {
	
		this._className = "BG18.datePicker",
		
		this._day = 0,
		this._month = 0,
		this._year = 0,
		this._currentSelection = [],
		this._maxDate = 0,
		this._btnLabel = 'Velg dato',
		
		this._oButton = null, 			// Button instance
		this._oCalendarMenu = null,  	// Calendar menu instance
		this._oCalendar = null,			// Calendar instance
		
		this._containerId = containerId;
	
		//console.log(options)
		if (options && options.selectedDate) {
			this._day = options.selectedDate.day;
			this._month = options.selectedDate.month;
			this._year = options.selectedDate.year;
	
	
			var nMonth = this._month;
			if (nMonth < 10) nMonth = "0" + nMonth;
			var nDay = this._day;
			if (nDay < 10) nDay = "0" + nDay;
	
			this._btnLabel = nDay+'.'+nMonth+'.'+this._year;
	
			this._currentSelection = nMonth+'/'+nDay+'/'+this._year;
	
		}
	
		if (options && options.maxDate) {
			this._maxDate = options.maxDate;
		}
		
		this.init = function() {
			//console.log('-> initialize calendar '+this._containerId);
			this.onContentReady(); // ok with YUI3
			//YAHOO.util.Event.onContentReady(this._containerId, this.onContentReady, this, true);
		}
		
		 
		//	Function: onSelectDate
		//	Empty function to be overriden by the user
		
		this.onSelectDate = function(nDay,nMonth,nYear) {
		
		}
		
		
		//	Function: setDate
		//	Public function
		
		this.setDate = function(nDay,nMonth,nYear) {
			var dateS = nMonth+"/"+nDay+"/"+nYear;
			if (this._oCalendar) {
				this._oCalendar.setYear(nYear);
				this._oCalendar.setMonth(nMonth-1);
				this._oCalendar.select(dateS);
				this._oCalendar.render();
			} else {
				this._currentSelection = nMonth+'/'+nDay+'/'+this._year;		
				this._oButton.set("label", (nDay + "." + nMonth + "." + nYear));	
				Y.one('#'+this._containerId+'_month').set('selectedIndex', nMonth-1);
				Y.one('#'+this._containerId+'_day').set('selectedIndex', nDay-1);
				Y.one('#'+this._containerId+'_year').set('value', nYear);
				//YAHOO.util.Dom.get(this._containerId+'_day').selectedIndex = (nDay - 1);
				//YAHOO.util.Dom.get(this._containerId+'_year').value = nYear;
			}
		}
		
		this._onSelectDate = function (p_sType, p_aArgs) {
	
			var aDate, nMonth, nDay, nYear;
	
			if (p_aArgs) {
				
				aDate = p_aArgs[0][0];
	
				nMonth = aDate[1];
				if (nMonth < 10) nMonth = "0" + nMonth;
				nDay = aDate[2];
				if (nDay < 10) nDay = "0" + nDay;
				nYear = aDate[0];
	
				this._oButton.set("label", (nDay + "." + nMonth + "." + nYear));	
	
				// Sync the Calendar instance\'s selected date with the date form fields
	
				Y.one('#'+this._containerId+'_month').set('selectedIndex', nMonth-1);
				Y.one('#'+this._containerId+'_day').set('selectedIndex', nDay-1);
				Y.one('#'+this._containerId+'_year').set('value', nYear);

				//YAHOO.util.Dom.get(this._containerId+'_month').selectedIndex = (nMonth - 1);
				//YAHOO.util.Dom.get(this._containerId+'_day').selectedIndex = (nDay - 1);
				//YAHOO.util.Dom.get(this._containerId+'_year').value = nYear;
	
			}
			
			this._oCalendarMenu.hide();
			
			this.onSelectDate(nDay,nMonth,nYear);
		
		}
		
		this.focusDay = function (p_sType, p_aArgs) {
	
			var oCalendarTBody = YAHOO.util.Dom.get(this._containedId+'_cal').tBodies[0],
				aElements = oCalendarTBody.getElementsByTagName("a"),
				oAnchor;
	
			//console.log('elements: '+aElements.length);
			
			if (aElements.length > 0) {
			
				YAHOO.util.Dom.batch(aElements, function (element) {
				
					if (YAHOO.util.Dom.hasClass(element.parentNode, "today")) {
						oAnchor = element;
					}
				
				});
							
				//console.log('anchor: '+oAnchor);
				
				if (!oAnchor) {
					oAnchor = aElements[0];
				}
	
				// Focus the anchor element using a timer since Calendar will try 
				// to set focus to its next button by default
				
				YAHOO.lang.later(0, oAnchor, function () {
					try {
						oAnchor.focus();
					}
					catch(e) {}
				});
			
			}
			
		}
		
		this.onContentReady = function() {
			
			//console.log('-> content ready: '+this._containerId);
			var Event = YAHOO.util.Event, Dom = YAHOO.util.Dom;	
			
			var oDateFields = Dom.get(this._containerId);
				oMonthField = Dom.get(this._containerId+'_month'),
				oDayField = Dom.get(this._containerId+'_day'),
				oYearField = Dom.get(this._containerId+'_year');
	
			// Hide the form fields used for the date so that they can be replaced by the 
			// calendar button.
			oMonthField.style.display = "none";
			oDayField.style.display = "none";
			oYearField.style.display = "none";
	
			// Create a Overlay instance to house the Calendar instance
			this._oCalendarMenu = new YAHOO.widget.Overlay(this._containerId+'_calendarmenu', { visible: false });
	
			// Create a Button instance of type "menu"
			this._oButton = new YAHOO.widget.Button({ 
				type: "menu", 
				id: "calendarpicker", 
				label: this._btnLabel, 
				menu: this._oCalendarMenu, 
				container: this._containerId 
			});
	
			this._oButton.on("appendTo", function () {
			
				// Create an empty body element for the Overlay instance in order 
				// to reserve space to render the Calendar instance into.	
				this._oCalendarMenu.setBody("&#32;");	
				this._oCalendarMenu.body.id = this._containerId+"_calendarcontainer";
				
			},this,true);
			
			// Add a listener for the "click" event.  This listener will be
			// used to defer the creation the Calendar instance until the 
			// first time the Button's Overlay instance is requested to be displayed
			// by the user.
			
	
			this._oButton.on("click", this.createCalendarOnDemand, this, true);
		
		}
		
		this.createCalendarOnDemand = function () {
			//console.log('create calendar '+this._containerId+', selection: '+this._currentSelection);
	
			// Create a Calendar instance and render it into the body 
			// element of the Overlay.
				
			this._oCalendar = new YAHOO.widget.Calendar(this._containerId+'_cal', this._oCalendarMenu.body.id, {
				navigator: {
					strings: {
						month:"Måned",
						year:"År",
						submit: "Ok",
						cancel: "Avbryt",
						invalidYear: "Ugyldig år"
					},
					monthFormat: YAHOO.widget.Calendar.LONG,
					initialFocus: "year"
				},
				MONTHS_LONG: ["januar", "februar", "mars", "april", "mai", "juni", "juli", "august", "september", "oktober", "november", "desember"],
				START_WEEKDAY: 1,
				WEEKDAYS_SHORT: ["Sø", "Ma", "Ti", "On", "To", "Fr", "Lø"],
				selected: this._currentSelection
	//			maxdate: "'.strftime('%m/%d/%Y',time()).'"
			});
			
			if (this._maxDate != 0) this._oCalendar.cfg.setProperty('maxdate', this._maxDate);	
			
			this._oCalendar.render();
			
			if (this._year > 0) {
				this._oCalendar.setYear(this._year);
				this._oCalendar.setMonth(this._month-1);
				this._oCalendar.previousYear();	
				this._oCalendar.nextYear();	
			}		
						
			// Subscribe to the Calendar instance's "select" event to 
			// update the Button instance's label when the user
			// selects a date.
			this._oCalendar.selectEvent.subscribe(this._onSelectDate,this,true);
				
			// Pressing the Esc key will hide the Calendar Menu and send focus back to 
			// its parent Button
			
			//YAHOO.util.Event.on(this._oCalendarMenu.element, "keydown", function (p_oEvent) {		
		//		if (YAHOO.util.Event.getCharCode(p_oEvent) === 27) {
			//		this._oCalendarMenu.hide();
			//		this._oButton.focus();
			//	}		
			//}, this, true);
			
			// Set focus to either the current day, or first day of the month in 
			// the Calendar	when it is made visible or the month changes
			//this._oCalendarMenu.subscribe('show', this.focusDay, this, true);
			//this._oCalendar.renderEvent.subscribe(this.focusDay, this, true);
	
			// Give the Calendar an initial focus		
			//this.focusDay();
	
			// Re-align the CalendarMenu to the Button to ensure that it is in the correct
			// position when it is initial made visible		
			this._oCalendarMenu.align();
	
			// Unsubscribe from the "click" event so that this code is 
			// only executed once
			this._oButton.unsubscribe("click", this.createCalendarOnDemand, this);
			
			//console.log('finished');
		}
		
	}

	//Y.extend(BG18.datePicker, BG18.base);
	
});
*/