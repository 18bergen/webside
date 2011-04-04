
// Popup Calendar Variables

function checkForUnknownTime(form, identifier, obj){
	
	if (obj.name == identifier+"_h"){
		if (obj.selectedIndex == 0){
			document[form][identifier+'_m'].selectedIndex = 0;
		} else if (document[form][identifier+'_m'].selectedIndex == 0){
			document[form][identifier+'_m'].selectedIndex = 1;
		}
	}
	
	if (obj.name == identifier+"_m"){
		if (obj.selectedIndex == 0){
			document[form][identifier+'_h'].selectedIndex = 0;
		} else if (document[form][identifier+'_h'].selectedIndex == 0){
			document[form][identifier+'_h'].selectedIndex = 1;
		}
	}


}

var weekDays = new Array('søndag','mandag','tirsdag','onsdag','torsdag','fredag','lørdag');
var monthNames = new Array('januar','februar','mars','april','mai','juni','juli','august','september','oktober','november','desember');

var cal2set = false;

var cal1 = new CalendarPopup("testdiv1");
cal1.setReturnFunction("setMultipleValues1");	
cal1.setMonthNames('Januar','Februar','Mars','April','Mai','Juni','Juli','August','September','Oktober','November','Desember');
cal1.offsetX = 0;
cal1.offsetY = 12;
cal1.setDayHeaders('S','M','T','O','T','F','L');
cal1.setWeekStartDay(1);
cal1.setTodayText("I dag");
cal1.setCssPrefix("TEST");
cal1.showNavigationDropdowns();
cal1.currentDate = new Date("May 15 2005");
cal1.setYearSelectStartOffset(10);
cal1.onDateChanged = new YAHOO.util.CustomEvent("onDateChanged"); 
function setMultipleValues1(y,m,d) {
	dObj=new Date(y,m-1,d,0,0,0);
	var newdate = y+"-"+LZ(m)+"-"+LZ(d);
	document.getElementById("cal1var").innerHTML = weekDays[dObj.getDay()]+" "+d+". "+monthNames[m-1]+" "+y +
	"<input type=\"hidden\" name=\"cal1date\" id=\"cal1date\" value=\""+newdate+"\" />";

	cal1.onDateChanged.fire(newdate);

	if (!cal2set){
		document.getElementById("cal2var").innerHTML = weekDays[dObj.getDay()]+" "+d+". "+monthNames[m-1]+" "+y +
		"<input type=\"hidden\" name=\"cal2date\" id=\"cal2date\" value=\""+y+"-"+LZ(m)+"-"+LZ(d)+"\" />";
		
		cal2.onDateChanged.fire(newdate);
	}
}	

var cal2 = new CalendarPopup("testdiv1");
cal2.setReturnFunction("setMultipleValues2");	
cal2.setMonthNames('Januar','Februar','Mars','April','Mai','Juni','Juli','August','September','Oktober','November','Desember');
cal2.offsetX = 0;
cal2.offsetY = 12;
cal2.setDayHeaders('S','M','T','O','T','F','L');
cal2.setWeekStartDay(1);
cal2.setTodayText("I dag");
cal2.setCssPrefix("TEST");
cal2.showNavigationDropdowns();
cal2.setYearSelectStartOffset(10);
cal2.onDateChanged = new YAHOO.util.CustomEvent("onDateChanged"); 
function setMultipleValues2(y,m,d) {
	cal2set = true;
	dObj=new Date(y,m-1,d,0,0,0);
	document.getElementById("cal2var").innerHTML = weekDays[dObj.getDay()]+" "+d+". "+monthNames[m-1]+" "+y +
	"<input type=\"hidden\" name=\"cal2date\" id=\"cal2date\" value=\""+y+"-"+LZ(m)+"-"+LZ(d)+"\" />";
	cal2.onDateChanged.fire();
}

var cal3 = new CalendarPopup("testdiv1");
cal3.setReturnFunction("setMultipleValues3");	
cal3.setMonthNames('Januar','Februar','Mars','April','Mai','Juni','Juli','August','September','Oktober','November','Desember');
cal3.offsetX = 0;
cal3.offsetY = 12;
cal3.setDayHeaders('S','M','T','O','T','F','L');
cal3.setWeekStartDay(1);
cal3.setTodayText("I dag");
cal3.setCssPrefix("TEST");
cal3.showNavigationDropdowns();
cal3.setYearSelectStartOffset(60);
cal3.onDateChanged = new YAHOO.util.CustomEvent("onDateChanged"); 
function setMultipleValues3(y,m,d) {
	dObj=new Date(y,m-1,d,0,0,0);
	document.getElementById("cal3var").innerHTML = d+". "+monthNames[m-1]+" "+y +
	"<input type=\"hidden\" name=\"bursdag\" id=\"bursdag\" value=\""+y+"-"+LZ(m)+"-"+LZ(d)+"\" />";
	cal3.onDateChanged.fire();
}

var cal4 = new CalendarPopup("testdiv1");
cal4.setReturnFunction("setMultipleValues4");	
cal4.setMonthNames('Januar','Februar','Mars','April','Mai','Juni','Juli','August','September','Oktober','November','Desember');
cal4.offsetX = 0;
cal4.offsetY = 12;
cal4.setDayHeaders('S','M','T','O','T','F','L');
cal4.setWeekStartDay(1);
cal4.setTodayText("I dag");
cal4.setCssPrefix("TEST");
cal4.showNavigationDropdowns();
cal4.setYearSelectStartOffset(10);
function setMultipleValues4(y,m,d) {
	cal4set = true;
	dObj=new Date(y,m-1,d,0,0,0);
	document.getElementById("cal4var").innerHTML = weekDays[dObj.getDay()]+" "+d+". "+monthNames[m-1]+" "+y +
	"<input type=\"hidden\" name=\"cal4date\" id=\"cal4date\" value=\""+y+"-"+LZ(m)+"-"+LZ(d)+"\" />";
}