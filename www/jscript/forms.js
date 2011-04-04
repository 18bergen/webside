/*******************************************************
FORMS
All code by Ryan Parman, unless otherwise noted.
(c) 1997-2003, Ryan Parman
http://www.skyzyx.com
Distributed according to SkyGPL 2.1, http://www.skyzyx.com/license/
*******************************************************/


/*******************************************************
ALLOW ONLY ONE SUBMISSION OF THE FORM
Attach an onload to the form tag.  Can use this.name.
*******************************************************/
function submitOnce(formName)
{
this.formName=(formName) ? formName:0;

if (document.forms && document.getElementsByTagName)
{
var subFields=eval('document.forms["'+this.formName+'"].getElementsByTagName("input");');

var subFieldsLen=subFields.length;
for (k=0; k<subFieldsLen; k++)
{
if (subFields[k].getAttribute("type").toLowerCase() == "submit") subFields[k].disabled=true;
if (subFields[k].getAttribute("type").toLowerCase() == "reset") subFields[k].disabled=true;
}
}
}




/*******************************************************
SELECT ALL (HIGHLIGHT FIELD)
*******************************************************/
function selectAll(formField)
{
temp=eval(formField);
temp.focus();
temp.select();
}




/*******************************************************
SINGLE-SELECT CHECKBOXES
This gives checkboxes the "one-at-a-time" functionality of radio buttons.
*******************************************************/
function radioButton(formName)
{
this.formName=(formName) ? formName:0;

this.select=function(what)
{
if (document.forms && document.getElementsByTagName)
{
var raFields=eval('document.forms["'+this.formName+'"].getElementsByTagName("input");');

var raFieldsLen=raFields.length;
for (i=0; i<raFieldsLen; i++)
{
if (raFields[i].getAttribute("type").toLowerCase() == "checkbox") raFields[i].checked=false;
what.checked=true;
}
}
}
}



/*******************************************************
CHECKBOXES
By Ryan Parman
- Click and Drag functionality by Peter Bailey, http://www.peterbailey.net
- Must be initialized as an object, then (optionally) attach an onload to the BODY tag, 
      either objName.enableMouseover(), or objName.enableClickDrag(), but not both.
- Use objName.check(), objName.clear(), or objName.toggle().
*******************************************************/
function checkbox(nameOfForm)
{
// Store variables.
this.nameOfForm=(nameOfForm) ? nameOfForm:0;
this.toggleOnOff=1;

// Can be used externally, but meant for internal use only.
this.set=function(bool)
{
if (document.forms && document.getElementsByTagName)
{
var theFields=eval('document.forms["'+this.nameOfForm+'"].getElementsByTagName("input");');

var theFieldsLen=theFields.length;
for (i=0; i<theFieldsLen; i++)
{
if (theFields[i].getAttribute("type").toLowerCase() == "checkbox") theFields[i].checked=bool;
}
}
else alert('Not Supported');
}

// Basic functions to use externally.
this.check=function() { this.set(true); }
this.clear=function() { this.set(false); }

// Just a nifty little feature.
this.toggle=function()
{
if (this.toggleOnOff) { this.set(true); this.toggleOnOff=0; }
else if (!this.toggleOnOff) { this.set(false); this.toggleOnOff=1; }
}

// Call this during BODY onLoad to add mouseover functionality to the form.
this.enableMouseover=function()
{
if (document.forms && document.getElementsByTagName)
{
var moFields=eval('document.forms["'+this.nameOfForm+'"].getElementsByTagName("input");');

var moFieldsLen=moFields.length;
for (i=0; i<moFieldsLen; i++)
{
if (moFields[i].getAttribute("type").toLowerCase() == "checkbox")
{
onMO=moFields[i];
onMO.onmouseover=this.change;
}
}
}
}

this.change=function() { this.checked=(this.checked) ? false:true; }

// By Peter Bailey, www.peterbailey.net
// Slight modifications by Ryan Parman, www.skyzyx.com
this.enableClickDrag=function(sameNameOnly)
{
// Abort if browser can't do script
if (document.all && !document.attachEvent) return true;
else if (!document.all && !document.addEventListener) return true;
//else if (document.all && !document.addEventListener) return true; // For retarded-ass Opera 7...

// Initialize Variables/properties
var f=eval('document.forms["'+this.nameOfForm+'"]');
var dragObj=this;
this.checked=false;
this.mousedown=false;
this.validClick=false;
this.same=Boolean( sameNameOnly );

// Attach Events
if ( document.attachEvent )
{
f.attachEvent( "onmousedown", function() { downHandler( event.srcElement, dragObj ) } );
f.attachEvent( "onmouseover", function() { overHandler( event.srcElement, dragObj ) } );
document.attachEvent( "onmouseup", function() { upHandler( event.srcElement, dragObj ) } );
}
else
{
f.addEventListener( "mousedown", function( e ) { downHandler( e.target, dragObj ) }, false );
f.addEventListener( "mouseover", function( e ) { overHandler( e.target, dragObj ) }, false );
document.addEventListener( "mouseup", function( e ) { upHandler( e.target, dragObj ) }, false );
}

// Handler for form.onMouseDown event
function downHandler(elem, o)
{
if (elem.type=="checkbox")
{
o.validClick=true;
o.firstCB=elem;
o.mousedown=true;
o.checked=!elem.checked
if (o.same) o.name=elem.name;
}
}

// Handler for document.onMouseUp event
function upHandler(elem, o)
{
if (o.validClick && o.firstCB)
{
o.firstCB.checked=o.checked;
o.mousedown=false;
o.checked=!o.checked;
o.validClick=false;
if (elem===o.firstCB) elem.checked=o.checked;
}
}

// Hanlder for form.onMouseOver event
function overHandler(elem, o)
{
if (elem.type=="checkbox" && o.mousedown)
{
if (o.same && elem.name == o.name) elem.checked=o.checked;
else if (!o.same) elem.checked=o.checked;
}
}
}
}