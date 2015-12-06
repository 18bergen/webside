<?php
function ErrorMessage($str){
	global $eventLog;
	// Skriv ut generisk feilmelding basert på input.
	print("<p style=\"background:#FFFFFF; border:#FF0000 1px solid; margin:10px; padding:10px; color:#FF0000\"><tt># <b>Det oppstod en feil.</b><br />&nbsp;&nbsp;$str</tt></p>");
	$eventLog->addToErrorLog($str);
}
function ErrorMessageAndExit($msg){
	// Skriv ut generisk feilmelding basert på input og stopp videre prosessering.
	ErrorMessage($msg); 
	exit; 
}
?>