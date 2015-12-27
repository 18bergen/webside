<?php
/*
if (($_SERVER['REMOTE_ADDR'] == "::1") || ($_SERVER['REMOTE_ADDR'] == "84.208.96.226") || ($_SERVER['REMOTE_ADDR'] == "128.39.226.149")){
 
 } else {
    header("Content-Type: text/html; charset=utf-8"); 
	readfile("upgrading.php");
	exit();
 }
 */
require_once(__DIR__ . '/vendor/autoload.php');
$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

define('BG_INC_PATH',dirname(dirname(__FILE__)).'/includes/');
require_once(BG_INC_PATH.'head.php');

if ($printTemplate) header("Content-Type: text/html; charset=utf-8"); 

require_once(BG_CLASS_PATH.'prefs.php');
require_once(BG_CLASS_PATH.'cms_basic.php');
require_once(BG_CLASS_PATH.'calendar_basic.php');
require_once(BG_CLASS_PATH.'TemplateService.php');

$template = new TemplateService();
//$template->determineLanguage();
 
//require_once("../includes/pages.php");
if ($dp0->pagenotfound) {
	header("HTTP/1.0 404 Not Found");
}

$dp0->checkIfSpecialUrl();

function myErrorHandler($errno, $errstr, $errfile, $errline){
	 global $login;

	 if (!(error_reporting() & $errno)) {
        // This error code is not included in error_reporting
        return;
    }
    
	 $errType = "E_UNKNOWN";
	 switch ($errno) {
		case E_USER_ERROR:
			$errType = "E_USER_ERROR"; break;
		case E_ERROR:
			$errType = "E_ERROR"; break;
		case E_USER_WARNING:
			$errType = "E_USER_WARNING"; break;
		case E_WARNING:
			$errType = "E_WARNING"; break;			
		case E_USER_NOTICE:
			$errType = "E_USER_NOTICE"; break;
		case E_NOTICE:
			$errType = "E_NOTICE"; break;
		case E_STRICT:
			$errType = "E_STRICT"; break;
		default:
			$errType = $errno; break;
	}
	if ($errType != "E_STRICT"){
		addToErrorLog("[$errType] $errstr. Line $errline of file $errfile.");
		if (($login->isLoggedIn()) && ($login->getUserId() == 1)){
			printError("<b>Debug info for webmaster:</b><br />[$errType][$errno] $errstr. Line $errline of file $errfile.");
		}
	}
}
set_error_handler("myErrorHandler");

// Sjekk om standardmalen skal sendes. Sider som f.eks. bruker redirect ønsker ikke dette.
/*
if (!$printTemplate){ 
	$dp0->initializePage();
	$dp0->run();
	exit();
}*/

$request_uri = rtrim($_SERVER['REQUEST_URI'],'/');
if (ROOT_DIR != '') $request_uri = substr($request_uri,strlen(ROOT_DIR));

$template->setRequestUri($request_uri);
// Ikke indekser:
if ((strpos($_SERVER['REQUEST_URI'],"/medlemsliste/") !== false) 	// medlemsliste
	OR (isset($_GET['sendlogininfo']))
	OR (isset($_GET['lost_pass']))								// glemt brukernavn/passord side
	OR (isset($_GET['loginfailed']))){							// feil innlogging
	$template->setAllowIndexing(false);
}

$template->setCMS($dp0);
$template->outputPage();  

track($template->getDocumentTitle(),$login->getUserId());

?>
