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