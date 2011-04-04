
/* Generic styles */

body {
 font-family    : sans-serif;
 font-size		: 12px;
 margin         : 0px;
 margin-bottom  : 10px;
 background		: #EDF0ED;
}
a:link {
 color           : #880000;
 text-decoration : none;
}
a:visited {
 color           : #880000;
  text-decoration : none;
}
a:hover {
 text-decoration : underline;
 color           : #E56D00;
}

tt.codeExample {
 display         : block;
 padding         : 5px;
 margin          : 2px;
 font-weight     : bold;
 color           : #0000FF;
}

h1 {
 font-size      : 18pt;
 
 padding		: 5px;
 font-family    : sans-serif;
 font-weight    : bold;
 text-align     : center;
 width			: 880px;
}
h2 {
 font-family    : Arial;
 letter-spacing : 2px;
 font-weight    : normal;
 font-size      : 30px;
 color			: #770000;
 margin			: 0px 0px 20px 0px;
}
h3 {
 font-size      : 12pt;
 font-weight    : bold;
/* border-bottom  : 1px solid #770000;*/
 color			: #770000;
 letter-spacing : 2px;
}
h3.small {
 font-size      : 10px;
 text-align     : center;
 font-weight    : normal;
 background		: #99bb99;
 color			: #ffffff;
 font-family    : Tahoma, Arial, serif;
 display        : block;
 margin         : 10px 0px 4px 0px;
 border-bottom  : 0;
}

.mediumtext {
 font-family    : Tahoma;
 font-size      : 12pt;
}
.smalltext {
 font-family    : Tahoma;
 font-size      : 10px;
}
p.smallright {
 font-family    : Tahoma;
 font-size      : 10px;
 text-align     : right;
 padding        : 0px;
 margin         : 0px;
}
.bildetekst {
 font-family    : Tahoma;
 font-size      : 12pt;
 font-style     : italic;
 text-align     : right;
}
.bizCard {
 float          : right;
 margin         : 0px;
 border         : 1px solid #000000;
 background     : url(images/scoutlogo100px.gif) no-repeat bottom right #FFFFFF;
 width          : 250px;
}
.bizCard h1 {
 text-align     : right;
 font-size      : 14px;
 font-weight    : bold;
 font-family    : Tahoma;
 color          : #000000;
 margin         : 0px;
 padding        : 3px;
 border-bottom  : 1px dashed #666666;
}
.bizCard p {
 font-size      : 10px;
 font-family    : Tahoma;
 color          : #000000;
 margin         : 0px;
}
.bizCard table p {
 font-size      : 12px;
 font-family    : Tahoma;
 color          : #000000;
 margin         : 0px;
}

div.code {
	display: block; 
	margin: 4px; 
	white-space: nowrap; 
	padding: 4px; 
	background: #FFFFFF; 
	border: 1px dashed #999999; 
	overflow:auto;
	width:380px;
	font-family: 'Courier New', Courier;
}
div.sitat1 div.code {
	width:350px;
}
div.sitat2 div.code {
	width:330px;
}
div.sitat3 div.code {
	width:310px;
}
/* Page Layout */

div#header {
 position: absolute;
 left: 0px;
 top: 0px;
}

div#wrapper {
 position       : absolute; 
 left           : 0px; 
 top            : 122px;
 width          : 690px;
 background     : url(images/18bergen3_05.jpg) repeat-y;
}

div#venstrekolonne {
 width          : 170px; /*167px;*/
 padding		: 0px;

}
div#venstrekolonnesub {
 background     : url(images/18bergen3_02.jpg) no-repeat;
 padding-top: 85px;
 padding-right:16px;
}

div#venstrekolonnesub h3 {
 font-size      : 10px;
 text-align     : center;
 font-weight    : normal;
 background		: #99bb99;
 color			: #ffffff;
 font-family    : Tahoma, Arial, serif;
 display        : block;
 margin         : 4px 0px 4px 17px;
 border-bottom  : 0;
}
div#linje {
 position       : absolute;
 top            : 122px;
 left           : 155px;
 height         : 25px; 
 width          : 745px;
 background     : url(images/18bergen3_03.jpg) repeat-x;
}
div#content {
 float: right;
 margin-top     : 40px;
 width          : 510px; 
 z-index        : 2; 
}

div#hoyrekolonne {
 position       : absolute; 
 width          : 200px; 
 left           : 700px; 
 top            : 150px;
}


/* Login */

div#loginfelt, #sokeform {
 text-align		: right;
}
div#loginfelt form, div#loginfelt div, #sokeform {
 font-size: 10px;
 padding: 6px;
}
div#loginfelt input, input#sokestreng {
 font-size: 10px;
 border: 0px;
 border-bottom: 1px dashed #000000;
 width: 70px;
}
div#venstrekolonne input.btn {
 background	    : #bbddbb;
 width			: 70px;
 margin-top     : 4px;
 padding        : 0px;
 font-family    : Tahoma;
 font-size      : 10px;
 border         : #777777 1px outset;
}


div#meny ul {
 margin: 8px 0px 0px 0px;
 padding: 1px 1px 1px 35px;
}

div#meny li {
 list-style-type: square;
}

div#meny a {
 color           : #770000;
}

div#bursdager {
 font-size: 10px;
}
div#statistikk {
 font-size: 10px;
}

/* Others styles */

li.peff { 
 list-style-image: url(images/peff.gif); 
 padding-left: 5px;
}
li.ass {
 list-style-image: url(images/ass.gif); 
 padding-left: 5px;
}
li.menig {
 list-style-image: url(images/menig.gif); 
 padding-left: 5px;
}
li.troppsass {
 list-style-image: url(images/troppsass.gif); 
 padding-left: 5px;
}
li.newuser {
 list-style-image: url(images/newuser.gif); 
 padding-left: 5px;
 font-style: italic;
}
.ErrorMessage {
 background     : #FFFFFF; 
 border         : #FF0000 1px solid; 
 margin         : 10px; 
 padding        : 10px; 
 color          : #FF0000;
}
.smalledit {
 background     : #88aa88;
 font-family    : Verdana, Arial, Helvetica, sans-serif;
 border         : #333333 1px inset;
 font-size      : 10px;
 padding        : 0px;
}
.smallbutton {
 background	    : #aabbaa;
 margin-top     : 4px;
 padding        : 0px;
 font-family    : Tahoma;
 font-size      : 10px;
 border         : #777777 1px outset;
}
.ProfileEdit {
 background     : #bbccbb;
 font-family    : Tahoma;
 font-size      : 12px;
 border         : #337733 1px inset;
}
.whiteedit {
 background     : #ffffff;
 height         : 18px;
 font-family    : Verdana, Arial, Helvetica, sans-serif;
 border         : #999999 1px solid;
 font-size      : 10px;
 margin         : 0px;
 padding        : 0px;
}
.whiteedit:hover {
 background     : #efefef;
}

.BorderLink {
	border      : 1px #FFFFFF solid;
}
.BorderLinkHover {
	border      : 1px #FFCC66 solid;
}

/* Positioning in index.php */

	/* Level 0 layers */


div#lenker ul {
 margin: 0px 0px 10px 5px;
 padding: 0px;
}
div#lenker li {
 margin           : 0px 0px 0px 5px;
 padding          : 0px;
 font-family      : Tahoma;
 font-size        : 10px;
 list-style-type  : square;
 list-style-image : url(images/strek.gif);
}
	/* Level 1 layers */

div#minikalender {
 width			: 200px;
 z-index        : 1;
}

	/* Level 2 layers */

td.statustext {
 font-size      : 8pt;
 font-family    : Tahoma;
 color          : #333333;
}
form.statusform {
 margin : 0px;
 padding: 0px;
}


	/* Level 3 layers */

div#testdiv1 {
 position               : absolute;
 visibility             : hidden;
 background-color       : white;
 background-color : white; 
 z-index                : 3;
}
div.bildeclear {
 clear:both;
}
