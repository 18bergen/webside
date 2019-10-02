
// Layer Utility Routines
// Written by Dan Michael O. Heggø <danm@start.no>
// Some code parted from developer.apple.com

function dispError(theText){
	alert("Ops! An error occured while executing the javascript on this page.\r\n\r\n"+theText+"\r\n\r\nMake sure you use the latest version of you'r browser!\r\nIf you do, I have a baaaad feeling I've done something seriously wrong...."); 
	return true;
}

function getStyleObject(objectId) {
    if(document.getElementById && document.getElementById(objectId)) {
		// W3C DOM
		return document.getElementById(objectId).style;
    } else if (document.all && document.all(objectId)) {
		// MSIE 4 DOM
		return document.all(objectId).style;
    } else if (document.layers && document.layers[objectId]) {
		// NN 4 DOM.. note: this won't find nested layers
		return document.layers[objectId];
    } else {
		return false;
    }
}

function getObject(objectId){
	if(document.getElementById && document.getElementById(objectId)) {
		return document.getElementById(objectId); // W3C DOM
    } else if (document.all && document.all(objectId)) {
		return document.all(objectId); // MSIE 4 DOM
    } else if (document.layers && document.layers[objectId]) {
		return document.layers[objectId]; // NN 4 DOM.. note: this won't find nested layers
    } else {
		return false;
    }
}


function changeObjectVisibility(objectId, newVisibility) {
    var styleObject = getStyleObject(objectId);
    if(styleObject) {
		styleObject.visibility = newVisibility;
		return true;
    } else {
		return false;
    }
}

function moveObject(objectId, newXCoordinate, newYCoordinate) {
    var styleObject = getStyleObject(objectId);
    if(styleObject) {
		styleObject.left = newXCoordinate;
		styleObject.top = newYCoordinate;
		return true;
    } else {
		return false;
    }
}

function toggleVisibility(layername){
	var styleObject = getStyleObject(layername);
	if (!styleObject){ 
		dispError('Could not dynamicly toggle item visibility.');
		return 0; 
	}
	if (styleObject.visibility == 'visible'){
		changeObjectVisibility(layername, 'hidden');
	} else {
		changeObjectVisibility(layername, 'visible')
	}
	return true;
}

function changeClass(layername, newClass){
	var theObject = getObject(layername);
	if (!theObject){ 
		dispError('Could not dynamicly change className.');
		return 0; 
	}
	theObject.className = newClass;
	return true;
}

function setText(layername, newtext){
	var theObject = getObject(layername);
	theObject.innerHTML = newtext;
	return true;
}

function initializeImages(okToToggle){
	for (i = 1; i<=imageCount; i++){
		changeObjectVisibility('bilde'+i, 'hidden');
		if (okToToggle == true){
			getObject('bilde'+i).style.cursor = 'hand';
		}
	}
	changeObjectVisibility('bilde1', 'visible');
	return true;
}


// AJAX Routines
// Written by Dan Michael O. Heggø <danm@start.no>

function AjaxRequestData(target, url){
  	// places result text in the browser object with the id 'mydiv': <div id='mydiv'></div>
	$.get(url).then(function(res) {
		console.log('Got', target);
		$('#' + target).html(res)
	});
}

function AjaxFormSubmit(target_div, url, form) {
	/* Ajax.Updater can be used to submit a form and insert the results into the page, all without a refresh. 
	   This works for all form elements except files. */
	$.post(url, $(form).serialize()).then(function(res) {
		$('#' + target_div).html(res)
	});
}

function validateField(field, target, url){
	var pars = field+"="+escape($('#' + field).val());
	$.post(url, pars).then(function(res) {
		feedbackReceived(res, target);
	});
}

function trim(str) {
   return str.replace(/^\s*|\s*$/g,"");
}
function stripHTML(str){
	var re= /<\S[^><]*>/g
	return str.replace(re, "")
}
function feedbackReceived(t, obj){
	// getObject and setText are functions in objectbasics.js

	var oldText = trim(stripHTML(getObject(obj).innerHTML.toLowerCase()));
	var newText = trim(stripHTML(t.toLowerCase()));

	if (newText == "ok" && oldText != "" && oldText != "ok") {
		$('#' + obj).hide();
	} else if (oldText == "" || oldText == "ok") {
		setText(obj,t);
		$('#' + obj).show();
	} else if (oldText != newText) {
		$('#' + obj).show();
		setText(obj,t);
	}
}
function perm_select(self, extras) {
	var i = self.options[self.selectedIndex].value;
	if (i == 'loggedin') {
		changeObjectVisibility(extras, 'visible');
	} else {
		changeObjectVisibility(extras, 'hidden');
	}
}
function showOptions(obj) {
	getStyleObject(obj).display = "block";
}
function hideOptions(obj) {
	getStyleObject(obj).display = "none";
}



/* Toggles display:block display:none */
function blocking(nr) {

	if (document.layers) {
		current = (document.layers[nr].display == 'none') ? 'block' : 'none';
		document.layers[nr].display = current;

	} else if (document.all) {
		current = (document.all[nr].style.display == 'none') ? 'block' : 'none';
		document.all[nr].style.display = current;
	
	} else if (document.getElementById) {
		vista = (document.getElementById(nr).style.display == 'none') ? 'block' : 'none';
		document.getElementById(nr).style.display = vista;
	}
	
	return true;
}


// ----------------------------------------------------------------------
// Javascript form validation routines.
// ----------------------------------------------------------------------

var proceed = 2;  

function setFocusDelayed() {
  //glb_vfld.focus()
}

function setfocus(vfld) {
  // save vfld in global variable so value retained when routine exits
  glb_vfld = vfld;
  setTimeout( 'setFocusDelayed()', 100 );
}

function validatePresent(field, infoDivID) {
  var stat = commonCheck(field, infoDivID, true);
  if (stat != proceed) return stat;
  setText(infoDivID, "");
  return true;
}

function commonCheck(field, infoDivID, isRequired) {	
  
  if (isRequired && (field.value == "")) {
      setText(infoDivID, " (må fylles inn)");
      setfocus(field);
      return false;
  } else if (field.value == ""){
	  setText(infoDivID, "");
      setfocus(field);
      return true;
  } else {
      setText(infoDivID, "");
  }
  return proceed;
}

function validateEmail (field, infoDivID, isRequired) {
  var stat = commonCheck (field, infoDivID, isRequired);
  if (stat != proceed) return stat;
  
  var tfld = field.value;  // value of field with whitespace trimmed off
  
  var email = /^[^@]+@[^@.]+\.[^@]*\w\w$/
  if (!email.test(tfld)) {
    setText(infoDivID, " (e-postaddressen er ikke gyldig)");
    setfocus(field);
    return false;
  }

  var email2 = /^[A-Za-z][\w.-]+@\w[\w.-]+\.[\w.-]*[A-Za-z][A-Za-z]$/
  if (!email2.test(tfld)) 
   setText(infoDivID, " (e-postaddressen er uvanlig. Sjekk at den er korrekt.)");
  else
    setText(infoDivID, "");
  return true;
};



function validatePhone (field, infoDivID, isRequired) {
  var stat = commonCheck(field, infoDivID, isRequired);
  if (stat != proceed) return stat;

  var tfld = field.value;
  
  var telnr = /^\+?[0-9 ()-]+[0-9]$/
  if (!telnr.test(tfld)) {
    setText(infoDivID, " (ikke et gyldig telefonnr.)");
    setfocus(field);
    return false;
  }
  var numdigits = 0;
  for (var j=0; j<tfld.length; j++)
    if (tfld.charAt(j)>='0' && tfld.charAt(j)<='9') numdigits++;

  if (numdigits < 8) {
    setText(infoDivID, " (for kort telefonnr.)");
    setfocus(field);
    return false;
  }

  if (numdigits > 10) {
    setText(infoDivID, " (for langt telefonnr.)");
    return false;
  }

  setText(infoDivID, "");
  return true;
};

// -----------------------------------------
//             validateAge
// Validate person's age
// Returns true if OK 
// -----------------------------------------

function validatePostNo (field, infoDivID, isRequired) {
  var stat = commonCheck (field, infoDivID, isRequired);
  if (stat != proceed) return stat;

  var ageRE = /^[0-9]{4}$/
  if (!ageRE.test(field.value)) {
    setText(infoDivID, " (må være et firesifret tall)");
    setfocus(field);
    return false;
  }

  setText(infoDivID, "");
  return true;
};
