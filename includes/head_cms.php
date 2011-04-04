<?

function dpeventlogentry($str, $unique = false, $type = "minor"){
	global $eventLog;
	$eventLog->addToActivityLog($str, $unique, $type);
}
function listMembers(){
	global $memberdb;
	return $memberdb->members;
}
function listGroups(){
	global $memberdb;
	return $memberdb->getAllGroups();
}
function lookupGroup($id) {
	global $memberdb;
	return $memberdb->getGroupById($id);
}
function lookupMember($id) {
	global $memberdb;
	if (!$memberdb->isUser($id)) {
		ErrorMessage("Kunne ikke hente medlemsdata for medlem med id $id. Medlemmet eksisterer ikke.");
		return false;
	}
	return $memberdb->getMemberById($id);
}
function lookupMemberImage($id) {
	global $memberdb;
	return $memberdb->getProfileImage($id);
}
function lookupForumImage($id) {
	global $memberdb;
	return $memberdb->getForumImage($id);
}
function make_memberlink($id, $customText = "", $customQuery = "") {
	global $memberdb, $login;
	return $memberdb->makeMemberLink($id,$customText, $customQuery);
}
function make_grouplink($id, $customText = "", $customQuery = "") {
	global $memberdb;
	$g = $memberdb->getGroupById($id);
	if (empty($g->slug)) $url = ROOT_DIR.'/medlemsliste/grupper/'.$g->id;
	else $url = ROOT_DIR.'/medlemsliste/'.$g->slug;
	if ($customQuery != "") $url = "$url?$customQuery";
	if ($customText == "") return "<a href='$url'>$g->caption</a>";
	else return "<a href='$url'>$customText</a>";
}
function lookupWebmaster() {
	global $memberdb;
	return $memberdb->userByTask('webmaster');
}

function is_allowed($arg1,$arg2,$arg3 = "DEFAULT") {
	global $dp0;
	return $dp0->is_allowed($arg1,$arg2,$arg3);
}
function add_to_breadcrumb($str) {
	global $dp0;
	$dp0->addToBreadcrumb($str);
}
function prepareClassInstance($instance, $page_id = -1, $class_id = -1) {
	global $dp0;
	$dp0->prepareClassInstance($instance,$page_id,$class_id);
}
function getUserOptions($instance, $option_name, $users = array()) {
	global $dp0;
	return $dp0->getUserOptions($instance, $option_name, $users);
}

require_once(BG_CLASS_PATH.'cms_basic.php');

$dp0 = new cms_basic();
$dp0->setDbLink($db);
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
$dp0->preferred_lang = $_SESSION['lang'];
$dp0->default_lang = 'no';
$dp0->image_dir = '/images/';
$dp0->useCoolUrls = true;
$dp0->initialize();

?>