<?

date_default_timezone_set('Europe/Oslo');

switch ($_SERVER['SERVER_NAME']){
	case "xanadu.local":
	case "localhost":
	case "127.0.0.1":
	case "10.37.129.2":
	case "192.168.2.185":
	case "169.254.167.169":
		define('ROOT_DIR','/18bergen');
		break;
	default:
		if (substr($_SERVER['SERVER_NAME'],0,3) == '10.')
			define('ROOT_DIR','/18bergen');
		else
			define('ROOT_DIR','');
		break;
}

define('BG_CLASS_PATH',dirname(__FILE__).'/classes/');
define('BG_WWW_PATH',dirname(dirname(__FILE__)).'/www/');
define('BG_LIB_PATH',dirname(dirname(__FILE__)).'/www/libs/');

define('LIB_CKFINDER_URI', '/libs/ckfinder-2.2.1/');
define('LIB_CKEDITOR_URI', '/libs/ckeditor-3.6.4/');
define('LIB_YUI_URI', 'http://yui.yahooapis.com/2.9.0/');

/*
 if (($_SERVER['REMOTE_ADDR'] == "::1") || ($_SERVER['REMOTE_ADDR'] == "192.168.2.144") || ($_SERVER['REMOTE_ADDR'] == "192.168.2.194") || ($_SERVER['REMOTE_ADDR'] == "10.24.124.195") || ($_SERVER['REMOTE_ADDR'] == "193.157.195.156") || ($_SERVER['REMOTE_ADDR'] == "84.48.18.118")){
 
 } else {
	readfile("upgrading.php");
	exit();
 }
*/
// For å beregne hvor lang tid serveren bruker på å behandle en side
$time_string = explode(" ", microtime()); $stime = $time_string[1] . substr($time_string[0],1,strlen($time_string[0]));
 
session_start();

/*
	Remove magic quotes 
	Good news: magic quotes is deprecated as of PHP 5.3.0, 
	and removed in PHP 6
*/
if (get_magic_quotes_gpc()) { 
  stripslashes_array($_GET); 
  stripslashes_array($_POST); 
  stripslashes_array($_REQUEST); 
  stripslashes_array($_COOKIE); 
}
function stripslashes_array(&$arr) { 
  foreach (array_keys($arr) as $k) { 
    $arr[$k] = stripslashes($arr[$k]); 
  } 
}

if (!isset($_SERVER['HTTP_REFERER'])) $_SERVER['HTTP_REFERER'] = "/";

 
// Sjekk om standardmalen skal sendes. Det er viktig at XML og DOCTYPE kommer før eventuelle feilmeldinger!
$printTemplate = !((isset($_GET["noprint"])) && ($_GET["noprint"] == 'true'));
if (strpos($_SERVER['SCRIPT_NAME'],"/rss") !== false) $printTemplate = false;

require_once(BG_INC_PATH.'config.php');
require_once(BG_INC_PATH.'whoisonline.php');
require_once(BG_INC_PATH.'functions/parse_bbcode.php');
require_once(BG_INC_PATH.'functions/parse_emoticons.php');
require_once(BG_INC_PATH.'functions/errormessage.php');
require_once(BG_INC_PATH.'functions/printr.php');

require_once(BG_CLASS_PATH.'base.php');
require_once(BG_CLASS_PATH.'crypto.php');
require_once(BG_CLASS_PATH.'mysqldb.php');
require_once(BG_CLASS_PATH.'memberlist.php');
require_once(BG_CLASS_PATH.'innlogging.php');
require_once(BG_CLASS_PATH.'eventlog.php');

require_once(BG_CLASS_PATH.'vervredigering.php');
require_once(BG_CLASS_PATH.'localization.php');

localization::determineLanguage();

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
	return $login->printNoAccess();
}
function isValidEmail($email_address) {
	$regex = '/^[A-z0-9][\w.-]*@[A-z0-9][\w\-\.]+\.[A-z0-9]{2,6}$/';
	return (preg_match($regex, $email_address));
}


$crypto = new crypto();

$db = new mysqldb();
$db->image_dir = ROOT_DIR.'/images/';
unset($dbHost, $dbUser, $dbPass, $dbName, $dbPipe);

$eventLog  = new eventlog();

/* Last inn CMS og finn side-tittel */
require_once(BG_INC_PATH.'head_cms.php'); // defined dp0

/* Last inn medlemsliste */
$memberdb = new memberlist();
$memberdb->setDbLink($db);
$memberdb->eventlog_function = "addToEventLog";
$memberdb->permission_denied_function = "permissionDenied";
$memberdb->image_dir = '/images/';

$dp0->setClassSpecificOptions($memberdb,$memberlist_class,$memberlist_page);
$dp0->setGlobalOptions($memberdb);

$memberdb->initialize();

/* Last inn login-klasse */
$login = new innlogging();
$login->setDbLink($db);
$login->setMemberDb($memberdb);
$login->image_dir = ROOT_DIR.'/images/'; 	 // base
$login->eventlog_function = "addToEventLog"; // base
$login->prepare_classinstance = "prepareClassInstance"; //base
$login->useCoolUrls = true;					 // base
$login->coolUrlPrefix = "";					 // base
$login->run();

if ($login->isLoggedIn()) $dp0->login_identifier = $login->getUserId();

?>
