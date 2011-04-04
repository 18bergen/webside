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
