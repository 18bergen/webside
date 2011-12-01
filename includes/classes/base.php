<?

class base {

	var $getvars = array();
	public $errorMessages = array();
	var $allow_read = false;
	var $allow_write = false;

	var $userFilesDir = 'userfiles/';
	var $userFilesVirtDir = 'brukerfiler/';
    var $tempDir = '_temp/';

    var $_cms;
    public function setCMS($cms) { $this->_cms = $cms; }

	private $dblink;
	public function setDbLink($dblink) { $this->dblink = $dblink; }
	public function getDbLink() { return $this->dblink; }
	
	var $permission_denied_function;
	var $error_function;
	var $errorlog_function;
	var $eventlog_function;
	var $login_identifier;
	var $pathToImages;
	var $pathToFCKeditor;
	var $useCoolUrls = false;
	var $coolUrlPrefix;
	var $coolUrlSplitted;
	var $image_dir;
	var $imginstance;
	var $editlink_top = 150;
	var $editlink_left = 600;	
	var $_rssUrl = '';
	
	var $label_january, $label_february, $label_march, $label_april, $label_may, $label_june, $label_july, $label_august, $label_september, $label_october, $label_november, $label_december;
	var $label_monday, $label_tuesday, $label_wednesday, $label_thursday, $label_friday, $label_saturday, $label_sunday;
	var $months;
	var $weekdays;
	
	var $selfurl;						// brukes av hvaskjer.php
	var $selfurlattributes = array();	// brukes av hvaskjer.php
	
	public function initialize() {
		$this->initialize_base();
	}
	
	function initialize_base(){

		array_push($this->getvars,'noprint','msg','action');

		if ($this->useCoolUrls){
			$this->findInterestingStuffInCoolUrl();
		}
		
		$this->months = array($this->label_january,$this->label_february,$this->label_march,$this->label_april,$this->label_may,$this->label_june,$this->label_july,$this->label_august,$this->label_september,$this->label_october,$this->label_november,$this->label_december);
		$this->weekdays = array($this->label_sunday,$this->label_monday,$this->label_tuesday,$this->label_wednesday,$this->label_thursday,$this->label_friday,$this->label_saturday);
	}
	
	function setRssUrl($url) { $this->_rssUrl = $url; }
	function getRssUrl() { return $this->_rssUrl; }
	
	function isLoggedIn() {
		global $login;
		if ($login->isLoggedIn() === true) return true;
		else return false;	
	}
	
	function getUserData($users, $fields) {
		global $memberdb;
		return $memberdb->getUserData($users, $fields);
	}
	
	function getActiveMembersList($options = array()) {
		global $memberdb;
		return $memberdb->getActiveMembersList($options);
	}
	
	function getUserRights() {
		global $login;
		return $login->getUserRights();
	}

	function getUserRole($user_id = 0) {
		global $memberdb;
		if ($user_id == 0) $user_id = $this->login_identifier;
		if ($user_id == 0) return false;
		$cat = $memberdb->getUserCategory($user_id);
		$role = $memberdb->getCategoryRole($cat);
		$grpId = $memberdb->getUserMainMembership($user_id);
		return array(
			'roleAbbr' => $cat,
			'role' => $role,
			'group' => $grpId
		);
	}
	
	/*
		Function: makeHtmlUrls
		This is just the url_shorten function
	*/
	function makeHtmlUrls($text,$chr_limit = 30,$add = '...') {
		
		$end_firstpart = round(1/3*$chr_limit);
		$begin_secondpart = $chr_limit - $end_firstpart - strlen($add);

		/*
			* is greedy			*? is lazy
			{2,} is greedy		{2,}? is lazy
		*/
		
		// An url can be terminated by a dot, a whitespace, a tab, a linebreak, parenthesis, or the end of the string:
		$termination = '(\.{2,}|\. | |,|\.<|<|\n|\.\n|\t|\)|\.$|$)';

		
		/* Search for urls starting with http:// */
		$pattern = '('.   
			'http:\/{2}'.   		  			// 1) http://
			'[\w\.]{3,}?'.   		  			// 2) at least 2 "word" characters (alphanumeric plus "_") or dots
			'[\/\w\-\.\?\+\&\=\#]*?'. 			// 3) matches the rest of the url
			')'.$termination;	
		$replacement = "'<a href=\"\\1\" title=\"\\1\" target=\"_blank\">'.(strlen('\\1')>=$chr_limit ? substr('\\1',0,$end_firstpart).'$add'.substr('\\1',$begin_secondpart):'\\1').'</a>\\2'";				
		$text = preg_replace("/$pattern/ie", $replacement, $text);


		/* Search for urls starting with www. */
		$pattern = '( |\t|\n|^)'.   			// 0) prefixed by linebreak, whitespace or tab
			'(www\.'.   		  				// 1) www.
			'[\/\w\-\.\?\+\&\=\#]*?'. 			// 3) matches the rest of the url
			')'.$termination;
		$replacement = "'\\1<a href=\"http://\\2\" title=\"http://\\2\" target=\"_blank\">'.(strlen('\\2')>=$chr_limit ? substr('\\2',0,$end_firstpart).'$add'.substr('\\2',$begin_secondpart):'\\2').'</a>\\3'";
		$text = preg_replace("/$pattern/ie", $replacement, $text);
		

		/* Search for email addresses */
		$pattern = '([\w\.-]{3,}@([\w]+\.)+[a-z]{2,3})';
		$replacement = "<a href=\"mailto:\\1\">\\1</a>";
		$text = preg_replace("/$pattern/i", $replacement, $text);

        
		return $text;
	}

	
	function query($str, $debug = false) {
		if ($this->dblink) {
			return $this->dblink->query($str, true, false, $debug);
		} else {
			$this->fatalError('base.query: dblink not set at ');		
		}
	}
	
	function affected_rows() {
		if ($this->dblink) {
			return $this->dblink->affected_rows;
		} else {
			$this->fatalError('base.query: dblink not set at ');		
		}
	}
	
	function getLastMySqlError() {
		if ($this->dblink) {
			return $this->dblink->error;
		} else {
			$this->fatalError('base.query: dblink not set at ');		
		}	
	}
	
	function insert_id() {
		if ($this->dblink) {
			return $this->dblink->insert_id;
		} else {
			$this->fatalError('base.query: dblink not set at ');		
		}
	}

	function makeDateField($identifier, $value, $showWeekday = true){
		if (empty($value)) {
			$currentMonth = 0;
			$currentDay = 0; 
			$currentYear = 0; 
			$currentDateJs = '0';
		} else {
			$currentMonth = strftime('%m',$value); 
			$currentDay = strftime('%d',$value); 
			$currentYear = strftime('%Y',$value); 
			$currentDateJs = '{ day: '.$currentDay.', month: '.$currentMonth.', year: '.$currentYear.' }';
		}
		$months = '';
		for ($i = 1; $i <= 12; $i++) {
			$n = $i; if ($n < 10) $n = '0'.$n;
			$sel = ($n == $currentMonth) ? " selected='selected'" : "";
			$months .= "<option value='$n'$sel>$n</option>\n";
		}
		$days = '';
		for ($i = 1; $i <= 31; $i++) {
			$n = $i; if ($n < 10) $n = '0'.$n;
			$sel = ($n == $currentDay) ? " selected='selected'" : "";
			$days .= "<option value='$n'$sel>$n</option>\n";
		}
		$year = strftime('%Y',$value);
		return '
			<span class="field" id="'.$identifier.'" style="display:inline-block;vertical-align:middle;">
				<select id="'.$identifier.'_month" name="'.$identifier.'_month">
					'.$months.'
				</select>	
				<select id="'.$identifier.'_day" name="'.$identifier.'_day">
					'.$days.'
				</select>
				<input type="text" id="'.$identifier.'_year" name="'.$identifier.'_year" value="'.$currentYear.'" style="width:50px;">
			</span>
			
		';
	}
	
	public function initializeCalendarInstance($cal_page = 0) {
		$calObj = new calendar_basic();
		if ($cal_page == 0) {
			call_user_func($this->prepare_classinstance,$calObj);
		} else {
			call_user_func($this->prepare_classinstance,$calObj,$cal_page);
			$calObj->initialize_base();
		}
		return $calObj;
	}
	
	public function initialize_mailer() {
		$instance = new mailer();
		call_user_func($this->prepare_classinstance,$instance);
		return $instance;
	}
	
	function preparePageInstance($page_id) {
		global $dp0;
		$page_id = intval($page_id);
		$res = $this->query("SELECT 
			$this->table_classes.filename, $this->table_classes.dependencies,
			$this->table_classes.classname, $this->table_pages.class
			FROM $this->table_classes, $this->table_pages
			WHERE $this->table_pages.id=$page_id
				AND $this->table_pages.class=$this->table_classes.id");
		$row = $res->fetch_assoc();
		$classname = $row['classname'];
		$filename = $row['filename'];
		$dependencies = $row['dependencies'];
		if (!empty($dependencies)){
			$dependencies = explode(",",$dependencies);
			foreach ($dependencies as $d) {
				require_once($d);
			}
		}
		require_once($filename);
		$instance = new $classname();
		$dp0->prepareClassInstance($instance,$page_id,$row['class']);	
		return $instance;
	}
	
	function sendContentType() {
		header("Content-Type: text/html; charset=utf-8"); 
 	}
	
	function getPageOptionValue($page_id, $option_name, $class_id = -1) {
		return $this->_cms->getPageOptionValue($page_id, $option_name, $class_id);
	}
	
	function findInterestingStuffInCoolUrl(){		
		
		$request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
		$uri = explode("?",$request_uri);
		$uri = $uri[0];
	
		$this->coolUrlSplitted = array();
		$script_path = $uri;
		if (empty($this->coolUrlPrefix) && (ROOT_DIR == '')){
			$f = 0;
			$tmp_script_path = trim($script_path,"/");
			if (strlen($tmp_script_path) > 0){
				$this->coolUrlSplitted = explode("/",$tmp_script_path);
			}
		} else if (empty($this->coolUrlPrefix)){
			$f = strpos($script_path,ROOT_DIR);
			if (is_numeric($f)){
				$f = $f + strlen(ROOT_DIR);
				$tmp_script_path = substr($script_path,$f);
				$tmp_script_path = trim($tmp_script_path,"/");
				if (strlen($tmp_script_path) > 0){
					$this->coolUrlSplitted = explode("/",$tmp_script_path);
				}
			}
		} else {
			$f = strpos($script_path,$this->coolUrlPrefix);
			if ($f > 0){
				$f = $f + strlen($this->coolUrlPrefix);
				$tmp_script_path = substr($script_path,$f);
				$tmp_script_path = trim($tmp_script_path,"/");
				if (strlen($tmp_script_path) > 0){
					$this->coolUrlSplitted = explode("/",$tmp_script_path);
				}
			}
		}
	}
	
	function getUrlToPage($page_id) {
		$res = $this->query("SELECT fullslug FROM ".DBPREFIX."cms_pages WHERE id=\"$page_id\"");
		$row = $res->fetch_assoc();
		$u = '/'.$row['fullslug'];
		$e = strlen($u)-6;
		if (strrpos($u,'/start') == $e) $u = substr($u,$e);
		return $u;
	}

	function generateCoolURL($dir, $getdata = ""){ // V1.0

		$fullurl = '/';
		if (!empty($this->fullslug)) $fullurl .= $this->fullslug;
		$fullurl = rtrim($fullurl,"/");
		$fullurl .= $dir;

		if (!empty($getdata)){
			$entity = "&";
			$ps = is_array($getdata) ? implode($entity,$getdata) : $getdata;
			if (!empty($ps)) $fullurl .= "?$ps";
		}
		return $fullurl;
	}

	function generateRootURL($uri){
		return ROOT_DIR.$uri;
	}

	function generateURL($getdata, $javascriptFriendly = false){ // V6.1
	
		/*
		$uri = explode("?",$_SERVER['REQUEST_URI']);
		$uri = $uri[0];

		$this_address = $uri;

		if ($this->useCoolUrls){
			if (isset($_SERVER['REDIRECT_SCRIPT_URI'])){
				$this_address = $_SERVER['REDIRECT_SCRIPT_URI'];
			} else {
				$this->address = "/";
			}
		}

		$baseurl = empty($this->selfurl) ? $this_address : $this->selfurl;
		*/

		
		$entity = (isset($_GET['noprint']) || $javascriptFriendly) ? "&" : "&amp;";
		$ps = "";
		$notToInclude = array_keys($_POST);
		$notToInclude2 = array_keys($this->selfurlattributes);
		$notToInclude = array_merge($notToInclude,$notToInclude2);
		$notToInclude = array_merge($notToInclude,$this->getvars);
		if ($this->useCoolUrls) $notToInclude[] = "s";
		// Filter out temporary variables from $_GET
		foreach ($_GET as $n => $v){
			if (!in_array($n,$notToInclude)){
				$current = (empty($v) ? "$n" : "$n=$v");
				$ps .= ($ps == "") ? "$current" : "$entity$current";
			}
		}
		foreach ($this->selfurlattributes as $n => $v){
			$current = (empty($v) ? "$n" : "$n=$v");
			$ps .= ($ps == "") ? "$current" : "$entity$current";
		}
		if (!empty($getdata)){
			$ps .= ($ps == "") ? ((is_array($getdata)) ? implode($entity,$getdata) : $getdata) : ((is_array($getdata)) ? $entity.implode($entity,$getdata) : $entity.$getdata);
		}
		if (empty($this->fullslug)) {
			$uri = explode("?",$_SERVER['REQUEST_URI']);
			$uri = $uri[0];
			$fullurl = $uri;
		} else {
			$fullurl = '/';
			if (!empty($this->fullslug)) $fullurl .= $this->fullslug;
			if (!empty($this->coolUrlSplitted)) $fullurl .= '/'.implode("/",$this->coolUrlSplitted);
		}
		$output = ($ps == "") ? $fullurl : "$fullurl?$ps";
		if (strlen(ROOT_DIR) > 0 && strpos($output,ROOT_DIR) === 0) {
			$output = substr($output, strlen(ROOT_DIR));
		}
		// DEBUG: print($output."<br>");
		return $output;
	}

	function fatalError($errStr, $options = array()){
		header("Content-Type: text/html; charset=utf-8");
		$image_dir = (empty($this->image_dir) ? "" : $this->image_dir);
		
		$bt = "<div style='font-size:12px;'><strong>Backtrace:</strong> ";
		foreach (debug_backtrace() as $d) {
			$cl = isset($d['class']) ? "<span style='color:#f00;'>".$d['class']."</span>::" : "";
			$fi = isset($d['file']) ? " in file <span style='color:#333;'>".$d['file']."</span>" : "";
			$li = isset($d['line']) ? "  at line <span style='font-weight:bold;'>".$d['line']."</span>" : "";
			$bt .= "<div style='white-space: nowrap;'>&nbsp;$cl<span style='color:blue;'>".$d['function']."</span>$li$fi</div>\n";
		}
		$bt .= "</div>";

		if (!empty($this->error_function)){
			$errf = $this->error_function;
			$errf("[base class]: $errStr");
		} else {
			print "
				<div class='errorMessage' style=\"border: 1px solid #FF0000; color: #443333; font-family: Tahoma; font-size:10pt; background: url(".$image_dir."warning2.gif) no-repeat #EEEEEE; padding: 2px 2px 2px 70px; margin: 5px; max-width: 600px;\">
					<strong style='padding-top:8px; padding-bottom:3px; display: block;'>Følgende feil oppstod:</strong>
					$errStr<br />&nbsp;
					$bt
				</div>
			";
			if (isset($options['logError'])) $logError = ($options['logError'] == true);
			else $logError = true;
			if ($logError) $this->addToErrorLog($errStr);
		}
		exit();
	}


	// Options: $print = false, $customHeader = "", dontLog
	function notSoFatalError($errStr, $options = array()){
		$image_dir = (empty($this->image_dir) ? "" : $this->image_dir);
		if (!empty($this->error_function)){
			$errf = $this->error_function;
			$errf("[base class]: $errStr");
		} else {
			$tittel = "Følgende feil oppstod:";
			if (isset($options['customHeader'])) $tittel = $options['customHeader'];
			$errm = "
				<div class='errorMessage' style=\"border: 1px solid #FF0000; color: #FF0000; background: url(".$image_dir."warning2.gif) no-repeat #FFFFFF; padding: 10px 10px 10px 70px; margin: 5px;\">
					<strong style='padding-top:8px; padding-bottom:3px; display: block;'>$tittel</strong>
					$errStr
					<br />&nbsp;
				</div>
			";
			if (isset($options['logError'])) $logError = ($options['logError'] == true);
			else $logError = true;
			if ($logError) $this->addToErrorLog($errStr);
			if (isset($options['print'])) 
				print $errm;
			else 
				return $errm;
		}
	}
	
	function javascriptRequired(){
		$image_dir = (empty($this->image_dir) ? "" : $this->image_dir);
		print "
			<div style=\"border: 1px solid #FF0000; color: #443333; font-family: Tahoma; font-size:10pt; background: url(".$image_dir."alert.jpg) no-repeat #EEEEEE; padding: 2px 2px 2px 220px; margin: 5px; max-width: 600px; height: 265px;\">
				<strong style='padding-top:8px; padding-bottom:3px; display: block;'>Denne funksjonen krever at JavaScript er skrudd på.</strong>
				Trenger du hjelp for å skru på JavaScript?
				<p>
				Instruksjoner...
				</p>
			</div>
		";
		$this->addToErrorLog("JavaScript disabled");
	}

    function permissionDenied(){
        if (isset($this->_cms)) {
            $this->_cms->setAllowIndexing(false);
        }
		if (empty($this->permission_denied_function)){
			return $this->notSoFatalError("Permission denied!");
		} else {
			$pf = $this->permission_denied_function;
			return $pf();
		}
    }

    function pageNotFound($str = "Siden ble ikke funnet.") {
        if (isset($this->_cms)) {
            $this->_cms->setAllowIndexing(false);
        }
        return '<p class="warning">'.$str.'</p>';
    }
	
	function badenPowellSays($str) {
		
		print "
			<div style='background:url(/bergenvs/images/bp3.gif) no-repeat top left; padding-left: 163px; padding-top: 20px;'>
				<div style='background: #ffffff; border: 1px solid #000000;'>
					<div style='background:url(/bergenvs/images/b5.jpg) no-repeat top left; margin-left: -43px; margin-top: -1px;'>
						<div style='background:url(/bergenvs/images/b7.jpg) no-repeat top right; margin-right: -1px; margin-top: -1px;'>
							<div id='infomsg' style='padding-left:60px; padding-top: 20px; padding-right: 20px; padding-bottom: 20px;'>
								<p>
									$str
								</p>
							</div>
							<div style='background:url(/bergenvs/images/b9.jpg) no-repeat right; padding-left: 42px; margin-bottom: -1px;'>
								<img src='/bergenvs/images/b8.jpg' />
							</div>
						</div>
					</div>
				</div>
			</div>	
			<script type='text/javascript'><!--
					getStyleObject('infomsg').display = 'none';
					Effect.Appear('infomsg', {
						duration:6
					});
				
			--></script>
		";
	
	}
	
	function infoMessage($str, $icon = 1) {
		$leftpadd = 90;
		switch ($icon) {
			case 1: 	$img = "question.jpg";	 				break;
			case 2: 	$img = "warning.jpg";	 				break;
			case 3: 	$img = "bp.jpg";	 					break;
			case 4: 	$img = "check.jpg"; $leftpadd = 50;	 	break;
			default: 	$img = "question.jpg"; 					break;
		}
		return "
			<div style='background: url(/images/icons/$img) no-repeat top left #ffffff; 
				padding: 20px 40px 20px ".$leftpadd."px; margin: 5px 0px 5px 0px; border: 1px solid #999999;'>
				$str
			</div>	
		";	
	}
	
	function addToErrorLog($str){ $this->logError($str); }
	function addToActivityLog($str, $unique = false, $type = "minor"){ $this->logActivity($str, $unique, $type); }
	function addToChangeLog($str){ $this->logChange($str); }
	
	function logError($str) {
		if (!empty($this->errorlog_function)){
			$ef = $this->errorlog_function;
			$ef($str);
		} else {
			$elog = new eventLog();
			$elog->addToErrorLog($str);
		}	
	}
	
	function logActivity($str, $unique = false, $type = "minor"){
		if (!empty($this->eventlog_function)){
			$ef = $this->eventlog_function;
			$ef($str,$unique,$type);
		} else {
			$elog = new eventLog();
			$elog->addToActivityLog($str,$unique,$type);
		}
	}	
	
	function logChange($str){
		// Avoid duplicated
		if (empty($this->dblink)){
			$this->fatalError("DB-link not specified in base");
		}
		$res = $this->query("SELECT changes FROM ".DBPREFIX."log_changes ORDER BY timestamp DESC LIMIT 1");
		$row = $res->fetch_assoc();
		if (stripslashes($row['changes']) == $str) return false;
		
		$this->query("INSERT INTO ".DBPREFIX."log_changes 
			(changes,member) VALUES (
				'".addslashes($str)."',
				'".$this->login_identifier."'
			)"
		);
	}
	
	function logDebug($str){
		if (empty($this->dblink)){
			$this->fatalError("DB-link not specified in base");
		}
				
		$this->query("INSERT INTO ".DBPREFIX."log_debug 
			(message) VALUES (
				'".addslashes($str)."'
			)"
		);
	}
	
	// $this->redirect($url, $msg, "error");
	function redirect($url, $msg = "", $success = ""){
		//$url = $this->generateCoolURL($url);
		if ($msg != "") $_SESSION['msg'] = $msg;
		if ($success != "") $_SESSION['success'] = $success;
		$url = ROOT_DIR.$url;
		header("Location: $url\n\n"); 
		exit();
	}
	
	function generateTimeStamp($d,$m,$y,$h,$i){
		// 'dd.mm.yyyy.hh.mm'
		$sd = array($d,$m,$y); 
		$st = array($h,$i);
		$date = $sd[2]."-".$sd[1]."-".$sd[0];
		$time = $st[0].":".$st[1].":00";
		$timestamp = strtotime ("$date $time"); 
		return $timestamp;
	}
	
	function getPostedDate($fieldname) {
		$dmy = explode("-",$_POST[$fieldname]);
		return $this->generateTimeStamp($dmy[2],$dmy[1],$dmy[0],0,0);
	}
	
	function setDefaultCKEditorOptions() {
		global $memberdb;
		
		// Set role:
		$role = $this->getUserRole();
		if (!$role) {
			$_SESSION['CKFinder_UserRole'] = "guest";
		} else if (in_array($role['roleAbbr'],array('SP'))) {
			$groupSlug = $memberdb->getGroupById($role['group'])->slug;
			$_SESSION['CKFinder_UserRole'] = "patrulje_$groupSlug";			
		} elseif ($this->getUserRights() >= 4) {
			$_SESSION['CKFinder_UserRole'] = "admin";		
		} else {
			$_SESSION['CKFinder_UserRole'] = "limited";		
		}
		
		/*
		$tmpdir = explode("/",$this->coolUrlPrefix);
		array_pop($tmpdir);
		$tmpdir = implode("/",$tmpdir);
		if (!empty($tmpdir)) $tmpdir .= "/";
		//print $tmpdir;
		$_SESSION['FCKautoOpen'] = $tmpdir;
		$_SESSION['FCKdirId'] = $this->parent_dir;
		$_SESSION['AbsolutePath'] = substr($_SERVER['SCRIPT_FILENAME'],0,strrpos($_SERVER['SCRIPT_FILENAME'],"/"));
		$_SESSION['UserFilesPath'] = $this->pathToImages;
		$_SESSION['VirtualUserFilesPath'] = ROOT_DIR.$this->pathToImages;
		$_SESSION['RootDir'] = ROOT_DIR;
		*/
		/*
		print "<b>FCKeditor options set</b>";
		print "<pre>";
		print_r($_SESSION);
		print "</pre>";
		*/
	}
	
	function debug_post() {
		print "<pre>";
		print_r($_POST);
		print "</pre>";
	}
	
	function initializeImagesInstance() {
		$this->imginstance = new images($this->dblink, DBPREFIX."cms_pages", $this->table_images, $this->pathToImages, $this->login_identifier,'/'.$this->userFilesDir.$this->tempDir);
		$this->imginstance->make_memberlink = $this->make_memberlink;
		$this->imginstance->useCoolUrls = true;
		$this->imginstance->initialize_base();
		$this->imginstance->coolUrlSplitted = $this->coolUrlSplitted;
		$this->imginstance->image_dir = $this->image_dir;		
		$this->imginstance->eventlog_function = $this->eventlog_function;
		$this->imginstance->is_allowed = $this->is_allowed;
	}
	
	function make_editlink($url, $title) {
		$l = '
			<script type="text/javascript">
			//<![CDATA[
			$("edit_page_div").innerHTML = "<div style=\"width: 95px; text-align: right;\">" 
				+ "<a href=\"'.$url.'\" title=\"Rediger denne siden\" style=\"text-decoration:none;\""
				+ " onmouseover=\"$(\'edit_this_page\').style.visibility=\'visible\'\" onmouseout=\"$(\'edit_this_page\').style.visibility=\'hidden\'\">"
				+ "<span id=\"edit_this_page\" style=\"font-size:10px; visibility:hidden;\">'.$title.'</span>"
				+ "<img src=\"'.$this->image_dir.'edit.gif\" alt=\"Rediger denne siden\" style=\"vertical-align:top; border: none;\" />"
				+ "</a>"
				+ "</div>";
			//]]>
			</script>
		';
		return $l;
	}
	
}
?>
