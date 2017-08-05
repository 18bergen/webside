<?php
/*
    Class: TemplateService
*/

require_once('forumnotifications.php');
require_once('mailnotifications.php');
require_once("changelog.php");
require_once("birthdays.php");
require_once("menueditor.php");
require_once("cms_basic.php");
require_once("wordbox.php");
require_once("poll.php");


class TemplateService extends base {
	
	private $_documentTitle = "";
	private $_requestUri = "";
	private $_allowIndexing = true;
	private $_rootDir = "";
	
	private $_page = "";

	function setRequestUri($l) { $this->_requestUri = $l; }
	//function setDocumentTitle($l) { $this->_documentTitle = $l; }
	function setAllowIndexing($l) { $this->_allowIndexing = $l; }
	function setCMS($l) { $this->_cms = $l; $this->_cms->setTemplateService($this); }
	function getDocumentTitle() { return $this->_documentTitle; }
	
	function TemplateService() {
		$this->initialize_base();
	}
	
	function makePage() {	
		global $login,$db,$wordbox_page,$poll_page,$changelog_page;
			
		if (!localization::languageDetermined()) localization::determineLanguage();

				
		$msg = "";
		if (isset($_SESSION['msg'])) {
			$msg = htmlspecialchars($_SESSION['msg']);
			if (isset($_SESSION['success']) && $_SESSION['success'] == 'success') {
				$msg = "
				<div id='infomessage_container'>
				<div id='infomessage_container2'>
					<div id='infomessage' class='updated' onmouseover=\"jQuery('#infomessage').css('backgroundColor','#ddffdd');\" onmouseout=\"jQuery('#infomessage').css('backgroundColor','#ffffff');\" onclick=\"skjulInfoMelding();\">
						<p>$msg</p>
					</div>
				</div>
				</div>
				";			
			} else if (isset($_SESSION['success']) && $_SESSION['success'] == 'warning') {
				$msg = "
				<div id='infomessage_container'>
				<div id='infomessage_container2'>
					<div id='infomessage' class='warning' onmouseover=\"jQuery('#infomessage').css('backgroundColor','#ffffee');\" onmouseout=\"jQuery('#infomessage').css('backgroundColor','#ffffff');\" onclick=\"skjulInfoMelding();\">
						<p>$msg</p>
					</div>
				</div>
				</div>
				";							
			} else if (isset($_SESSION['success']) && $_SESSION['success'] == 'error') {
				$msg = "
				<div id='infomessage_container'>
				<div id='infomessage_container2'>
					<div id='infomessage' class='errormessage' onmouseover=\"jQuery('#infomessage').css('backgroundColor','#ff7777');\" onmouseout=\"jQuery('#infomessage').css('backgroundColor','#ff5555');\" onclick=\"skjulInfoMelding();\">
						<p>$msg</p>
					</div>
				</div>
				</div>
				";							
			} else {				
				$msg = "
				<div id='infomessage_container'>
				<div id='infomessage_container2' style='display:none'>
					<div id='infomessage' class='updated' onmouseover=\"jQuery('#infomessage').css('backgroundColor','#ddffdd');\" onmouseout=\"jQuery('#infomessage').css('backgroundColor','#ffffff');\" onclick=\"skjulInfoMelding();\">
						<p>$msg</p>
					</div>
				</div>
				</div>
				";

			}
			
			$msg .= "
				<script type='text/javascript'>
			
					function skjulInfoMelding() {
						jQuery('#infomessage_container2').slideUp(500);
					}
					
                    jQuery(document).ready(function(){
						jQuery('#infomessage_container2').slideDown(500);
					});
					
				</script>
			";
			unset($_SESSION['msg']);
			unset($_SESSION['success']);
		}	
				
		$breadcrumb = '';
		if ($this->_cms->initializePage()) {
	
			// Page initialized successfully
			
			if ($login->replaceContent == true){
				$mainContent = $login->makeContent();
			} else {
				
				// Produce main content:
				$mainContent = $this->_cms->run(true);
				$breadcrumb = $this->_cms->getBreadCrumb();

			}
			
		} else {
			
			// Page initialization failed.
			$breadcrumb = $this->_cms->getBreadcrumb();
			$mainContent = $this->_cms->getErrors();

        }

        // Index? Check after _cms->run(): 
		$meta = '';
		if (!$this->_allowIndexing) {
			$meta .= '<meta name="robots" content="noindex,nofollow" />'."\n";
		}
		if ($this->_requestUri == '') {
			$meta .= '<meta name="Keywords" content="18. bergen v-s, 18 bergen, speider, speiding, speidertropp, speidergruppe, bergen, starefossen" />'."\n".'  <meta name="Description" content="18. Bergen speidergruppe holder til på Starefossen og er en av Bergens eldste speidergrupper. Turlogger og rikt bildearkiv, historie, speidertips, medlemssider og siste nytt fra gruppen." />'."\n  ";
		}		
        

		// Must be run after $this->_cms->initializePage():
		$additional_scripts = "\n  ".'<!-- Page-specific scripts: -->';
		if (isset($_GET['history'])) $additional_scripts .= "\n  ".'<script type="text/javascript" src="https://api.simile-widgets.org/timeline/2.3.1/timeline-api.js"></script>';
		foreach ($this->_cms->js_libs as $j) {
			if (!empty($j)) $additional_scripts .= "\n  ".'<script type="text/javascript" src="/jscript/'.$j.'"></script>';
		}


		/* RSS */
		$ourRssFeed = (empty($rssUrl)) ? '': '<link rel="alternate" type="application/rss+xml" title="RSS" href="'.$rssUrl.'" />';

		
		/* Notifications */
		$notifications_mail = MailNotifications::getUnread();		
		$notifications_forum = ForumNotifications::getUnread();
		$notifications = $notifications_mail.$notifications_forum;
		
		$right_col_extras = "";
		if (in_array($this->_cms->current_class,array('imagearchive'))) {
			$right_col_extras .= '
				<div class="col_above"></div>
        		<div class="inner_col">
        		'.$this->_cms->current_instance->printLastComments($this->_cms->getPageId()).
        		'</div>
				<div class="col_below"></div>&nbsp;';
		}
		
		/* Wordbox */
		$wb = new wordbox();
		$this->_cms->prepareClassInstance($wb,$wordbox_page);
		$wb->initialize();
		$wb->allow_delete = false;
		$wb->show_pagenavigation = false;
		$wb->messages_per_page = 15;
		$wb->show_form = true;
		$wb->identifier = "snikksnakk2";
		$wordbox = $wb->run();
		unset($wb);
		
		/* Poll */
		$pollObj = new poll();
		$this->_cms->prepareClassInstance($pollObj,$poll_page);
		$pollObj->initialize();
		$poll = $pollObj->outputPoll();
		unset($pollObj);
		
		/* Updates */
		$changes = new changelog();
		$this->_cms->prepareClassInstance($changes,$changelog_page);
		$changes->itemstoprint = 6;
		$changes->typeFilter = "major";
		$changes->setDbLink($db);
		$changes->elemStyle = "margin-bottom:3px;";
		$changes->initialize();
		$updates = $changes->printActivity(false,false);		
		
		/* Birthdays */
		
		$bursdager = new birthdays();
		$birthdays = $bursdager->ouputNextBirthdays(5);
		
		/* Login field */
		$loginField = $login->outputLoginField();
		
		/* Menu */
		$menu1 = new menu();
		$menu1->setDbLink($db);
		
		$preferredLang = $_SESSION['lang'];
		$menu1->default_lang = 1;
		$menu1->preferred_lang = $preferredLang;
		$menu1->permission_denied_function = "permissionDenied";
		if ($this->isLoggedIn()){
			$menu1->login_identifier = $this->login_identifier;
			if ($this->getUserRights() >= 2) {
				$menu1->allow_addmenuitems = true;
				$menu1->allow_editmenuitems = true;
			}
		}
		$menu1->image_dir = $this->image_dir;
		
		$menu1->useCoolUrls = true;
		$menu1->initialize();
		$menu = $menu1->outputMenu();
		
		
		switch ($_SERVER['SERVER_NAME']){
			case "xanadu.local":
			case "localhost":
			case "127.0.0.1":
			case "10.37.129.2":
			case "192.168.2.185":
			case "169.254.167.169":
				$googleAnalytics = '';
				break;
			default:
				$googleAnalytics = '
				
					<script src="https://www.google-analytics.com/urchin.js" type="text/javascript"></script>
					<script type="text/javascript"> 
					//<![CDATA[
						_uacct = "UA-82140-1";
						try {
							'.($this->_cms->pagenotfound ? '
							urchinTracker("/404.html?page=" + _udl.pathname + _udl.search);':'
							urchinTracker();
							').'
						} catch(error) {
							// Most likely a connection problem to Google. No need to report.
						}
					//]]>
					</script>
				
				';
				break;
		}

		$template = file_get_contents('../includes/templates/site.php');
		if ($this->_cms->isWideMode()) {
			$template_main = file_get_contents('../includes/templates/site_main_wide.php');
		} else {
			$template_main = file_get_contents('../includes/templates/site_main_normal.php');		
		}
		
		/*
		include('yr.php');
		//Opprett en presentasjon
		$yr_xmldisplay = &new YRDisplay();

		//Gjenomfør oppdraget basta bom.
		$landsleirSpesial = '<h2>Landsleir Utopia 09</h2>
		<p align="center">
		<a href="http://www.landsleir.no/kamera/">Webkamera fra landsleiren.</a>
		</p>
		'.
		$yr_xmldisplay->generateHTMLCached($yr_url, $yr_name, $yr_xmlparse, $yr_url, $yr_try_curl, $yr_use_header, $yr_use_footer, $yr_use_banner, $yr_use_text, $yr_use_links, $yr_use_table, $yr_maxage, $yr_timeout, $yr_link_target).
		'<object type="application/x-shockwave-flash" data="http://www.fxcomponents.com/components/mp3player/MP3Player_v201.swf" style="outline: none;" width="210" height="20">
			<param name="movie" value="http://www.fxcomponents.com/components/mp3player/MP3Player_v201.swf" />
			<param name="wmode" value="transparent" />
			<param name="FlashVars" value="trackURL=http://www.visitutopia.no/lastned/Utopia.mp3&amp;title=Trang Fødsel - Utopia&amp;color1=0x9db400&amp;color2=0x9db400&amp;color3=0x0&amp;color4=0x0&amp;width=210&amp;volume=80&amp;timerMode=0&amp;autoPlay=false" />
		</object>
		';*/
		
		if (!$this->_allowIndexing) {
			$mainContent .= '<div style="margin-top:100px;color:#aaa;font-size:10px;text-align:center;">Denne siden indekseres ikke av søkemotorer.</div>';
		}

				
		$this->_documentTitle = $this->_cms->getDocumentTitle();
		
		$s1 = array();					$s2 = array();
		$s1[] = "%main_content%";		$s2[] = $template_main;
		$s1[] = "%document_title%";		$s2[] = $this->_documentTitle;
		$s1[] = "%our_rss_feed%";		$s2[] = $ourRssFeed;
		$s1[] = "%meta%";				$s2[] = $meta;
		$s1[] = "%additional_scripts%";	$s2[] = $additional_scripts;
		$s1[] = "%language_bar%";		$s2[] = $this->makeLanguageBar();
		$s1[] = "%breadcrumb%";			$s2[] = $breadcrumb;
		$s1[] = "%notifications%";		$s2[] = $notifications;
		$s1[] = "%wordbox%";			$s2[] = $wordbox;
		$s1[] = "%poll%";				$s2[] = $poll;
		$s1[] = "%whoisonline%";		$s2[] = printWhosOnline();
		$s1[] = "%updates%";			$s2[] = $updates;
		$s1[] = "%birthdays%";			$s2[] = $birthdays;
		$s1[] = "%content%";			$s2[] = $mainContent;
		$s1[] = "%field_login%";		$s2[] = $loginField;
		$s1[] = "%menu%";				$s2[] = $menu;
		$s1[] = "%infomsg%";			$s2[] = $msg;
		$s1[] = "%right_col_extras%";	$s2[] = $right_col_extras;
		$s1[] = "%analytics%";			$s2[] = $googleAnalytics;
		$s1[] = "%ckfinder_uri%";		$s2[]  = LIB_CKFINDER_URI;
		$s1[] = "%ckeditor_uri%";		$s2[]  = LIB_CKEDITOR_URI;
		$s1[] = "%timestamp%";			$s2[]  = strftime("%A %e. %B %Y, kl. %H:%M",time());
		//$s1[] = "%landsleir%";			$s2[] = $landsleirSpesial;
		
		$s1[] = "\"/";					$s2[] = '"'.ROOT_DIR.'/';
		$s1[] = "url(/";				$s2[] = 'url('.ROOT_DIR.'/';
		
		$this->_page = str_replace($s1,$s2,$template);
	
	}
	
	function outputPage() {
	
		$this->makePage();
		
		print $this->_page;
	
	}
	
	function makeLanguageBar() {
		
		$output = '';			
		$langurl = $_SERVER['REQUEST_URI'];
		if (strpos($langurl,"?") === false) 
			$langurl = "$langurl?noprint=true&amp;set_lang="; 
		else 
			$langurl = str_replace("&","&amp;",$langurl)."&amp;noprint=true&amp;set_lang=";				
		
		//if ($login->loggedin) {	
			foreach (localization::getLanguages() as $langcode => $langinfo) {
				$langstyle = ($_SESSION['lang'] == $langcode) ? ' style="background:#ffe"' : '';
				$output .= '<a href="/om-oss/?noprint=true&amp;set_lang='.$langcode.'"'.$langstyle.'><img src="/images/flags/'.$langcode.'.gif" alt="'.$langinfo['name'].'" /></a>';
			}
		//}
		return $output;
		
	}	
	

}

?>
