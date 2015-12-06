<?php
 function loadFullMemberlist() {
 	global $memberdb,$login,$db;
 	require_once("$classpath/memberlist_actions.php");
	unset($memberdb);
	$memberdb = new memberlist_actions($db);
	$memberdb->login_identifier = $login->getUserId();
	$memberdb->eventlog_function = "addToEventLog";
	$memberdb->permission_denied_function = "permissionDenied";
	$memberdb->image_dir = ROOT_DIR.'/images';
	$memberdb->initialize();
	return $memberdb;
 }
?>