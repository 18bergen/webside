#!/usr/local/php5/bin/php -q
<?php
//print "   # make_sitemap.php executed";
$memberlist_class = 9;
$memberlist_page = 85;
$wordbox_class = 21;
$wordbox_page = 131;

define('ROOT_DIR','');

if (!isset($_SERVER['HTTP_REFERER'])) $_SERVER['HTTP_REFERER'] = "/";
if (!isset($_SERVER['REQUEST_URI'])) $_SERVER['REQUEST_URI'] = "";
if (!isset($_SERVER['SERVER_NAME'])) $_SERVER['SERVER_NAME'] = "www.18bergen.org";

//echo '  User: '.$_SERVER['USER'].', term: '.$_SERVER['TERM']."\n";

require_once("includes/config.php");
require_once("includes/whoisonline.php");
require_once("includes/functions/parse_bbcode.php");
require_once("includes/functions/parse_emoticons.php");
require_once("includes/functions/errormessage.php");
require_once("includes/functions/printr.php");
//require_once("includes/functions/url_shorten.php");

$classpath = "includes/classes";
require_once("$classpath/base.php");
require_once("$classpath/crypto.php");
require_once("$classpath/mysqldb.php");
require_once("$classpath/memberlist.php");
require_once("$classpath/innlogging.php");
require_once("$classpath/eventlog.php");
//require_once("$classpath/prefs.php");
require_once("$classpath/cms_basic.php");
require_once("$classpath/calendar_basic.php");
require_once("includes/languagebar.php");
require_once("$classpath/wordbox.php");
require_once("$classpath/TemplateService.php");

$template = new TemplateService();
$template->determineLanguage();

function printError($errStr){
	print("<div style=\"border: 1px solid #FF0000; color: #FF0000; padding: 5px; margin: 5px;\">$errStr</div>");
}
 
function addToEventLog($str){
	global $eventLog;
	$eventLog->addToActivityLog($str);
}
function addToErrorLog($str){
	global $eventLog;
	$eventLog->addToErrorLog($str);
}
function permissionDenied(){
	global $login;
	$login->printNoAccess();
}
function isValidEmail($email_address) {
	$regex = '/^[A-z0-9][\w.-]*@[A-z0-9][\w\-\.]+\.[A-z0-9]{2,6}$/';
	return (preg_match($regex, $email_address));
}
 
$crypto	= new crypto();
$db		= new mysqldb();
$db->image_dir = ROOT_DIR."/images/";
unset($dbHost, $dbUser, $dbPass, $dbName);
$eventLog  = new eventlog();
//$valg = new prefs();

$dp0 = new cms_basic();
$dp0->setDbLink($db);
$dp0->table_pages = "cms_pages";
$dp0->table_classes = "cms_classes";
$dp0->table_languages = "languages";

$dp0->lookup_group = "lookupGroup";
$dp0->lookup_member = "lookupMember";
$dp0->lookup_memberimage = "lookupMemberImage";
$dp0->lookup_forumimage = "lookupForumImage";
$dp0->lookup_webmaster = "lookupWebmaster";
$dp0->get_useroptions = "getUserOptions";
$dp0->make_memberlink = "make_memberlink";
$dp0->make_grouplink = "make_grouplink";
$dp0->add_to_breadcrumb = "add_to_breadcrumb";
$dp0->isValidEmail = "isValidEmail"; // declared in index.php
$dp0->memberlookup_function = "dpmemlookup";
$dp0->webmasterlookup_function = "dpwebmasterlookup";
$dp0->list_groups = "listGroups";
$dp0->list_members = "listMembers";
$dp0->permission_denied_function = "permissionDenied";
$dp0->prepare_classinstance = "prepareClassInstance";
$dp0->eventlog_function = "dpeventlogentry";
$dp0->htmleditdir = ROOT_DIR.'/htmlarea';
$dp0->preferred_lang = 1;
$dp0->default_lang = 1;
$dp0->image_dir = '/images/';
$dp0->useCoolUrls = true;

$dp0->initialize();
$dp0->generateSitemap();





function dpeventlogentry($str, $unique = false, $type = "minor"){

}
function listMembers(){
	return array();
}
function listGroups(){
	return array();
}
function lookupGroup($id) {

}
function lookupMember($id) {

}
function lookupMemberImage($id) {

}
function lookupForumImage($id) {

}
function make_memberlink($id, $customText = "", $customQuery = "") {

}
function make_grouplink($id, $customText = "", $customQuery = "") {

}
function lookupWebmaster() {

}

function is_allowed($arg1,$arg2,$arg3 = "DEFAULT") {

}
function add_to_breadcrumb($str) {
}
function prepareClassInstance($instance, $page_id = -1, $class_id = -1) {
	global $dp0;
	$dp0->prepareClassInstance($instance,$page_id,$class_id);
}
function getUserOptions($instance, $option_name, $users) {

}

?>
