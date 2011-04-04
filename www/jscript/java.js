/*******************************************************
JAVA DETECT (NETSCAPE)
All code by Ryan Parman, unless otherwise noted.
(c) 1997-2003, Ryan Parman
http://www.skyzyx.com
Distributed according to SkyGPL 2.1, http://www.skyzyx.com/license/
*******************************************************/

var java=new Object();
java.installed=navigator.javaEnabled() ? true:false;
java.version='0.0';

var numPlugs=navigator.plugins.length;
if (numPlugs) {
	for (var x=0; x<numPlugs; x++) {
		var pluginjava = navigator.plugins[x];
		if (pluginjava.name.toLowerCase().indexOf('java plug-in') != -1) {
			java.version=pluginjava.description.toLowerCase().split('java plug-in ')[1].split(' for')[0];
			break;
		}
	}
}
