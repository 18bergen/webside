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

