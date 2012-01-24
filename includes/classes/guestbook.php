<?

class guestbook extends base {

	var $getvars = array("gb_delete","gb_save","gb_saved","p","delconf");
	var $tablename = "guestbook";
	
	var $allow_delete = false;
	var $allow_addentry = false;
	
	var $fieldnames = array(
		'name' => 'etp-Sc-1',
		'email' => 'etp-Sc-2',
		'scoutgroup' => 'etp-Sc-3',
		'homepage' => 'etp-Sc-4',
		'body' => 'etp-Sc-e',
		'checkcode' => 'tor-Sd-C'
	);
	
	var $bannedWords = "porno,teen,sex,viagra,blowjob,dating,pregnant,sexy,shemale,porn,vagina,xxx,softcore,lastov.net,anal,lesbian,fuck,marrowe.net,gay,masturbate,masturbation,mereq.net,mygisor.net,nanamer.net,ncako.net,onlinevir.net";
	
	var $label_error_intro;
	var $label_error_emptyname;
	var $label_error_emptyemail;
	var $label_error_emptybody;
	var $label_error_invalidname;
	var $label_error_invalidemail;
	var $label_error_invalidbody;
	var $label_error_bannedword;
	var $label_error_toofastorslow;
	var $label_error_invalidcheckcode;

	var $label_noentries;
	var $label_name;
	var $label_email;
	var $label_scoutgroup;
	var $label_homepage;
	var $label_comment;
	var $label_submit;
	var $label_signourguestbook;
	var $label_readourguestbook;
	var $label_writtenby;
	var $label_writtenat;
	var $label_hiddenemail;
	var $label_delete_entry;
	var $label_thankyou_header;
	var $label_thankyou_paragraph;
	var $label_confirmdelete_header;
	var $label_confirmdelete_paragraph;
	var $label_entrydeleted;
	var $label_maybeleftblank;
	var $label_checkcode;
	var $label_checkcodedesc;
	var $label_newer = "&lt;&lt; Nyere innlegg";
	var $label_older = "Eldre innlegg &gt;&gt;";
	
	function guestbook() {
		$this->tablename = DBPREFIX.$this->tablename;
	}
		
	function run(){	
		$this->initialize_base();
		
		$this->errorMessages = array(
			'empty_name'		=> $this->label_error_emptyname,
			'empty_email'		=> $this->label_error_emptyemail,
			'empty_body'		=> $this->label_error_emptybody,
			'invalid_name'		=> $this->label_error_invalidname,
			'invalid_email'		=> $this->label_error_invalidemail,
			'invalid_body'		=> $this->label_error_invalidbody,
			'banned_word'		=> $this->label_error_bannedword,
			'toofastorslow'		=> $this->label_error_toofastorslow,
			'invalid_checkcode' => $this->label_error_invalidcheckcode
		);

		
		if (isset($_GET['gb_delete'])){
			return $this->deleteEntry($_GET['gb_delete']);	
		} else if (isset($_GET['gb_save'])){ 
			return $this->newEntry();	
		} else {
			return $this->printBook();
		}
	}
	
	function newEntry(){
	
		/** CHECK PERMISSION **/
		if (!$this->allow_addentry) return $this->permissionDenied();
		$output = "";
		
		$today = date('myd',time());
		$fieldnames = array(
			'name' => $this->fieldnames['name'].$today,
			'email' => $this->fieldnames['email'].$today,
			'scoutgroup' => $this->fieldnames['scoutgroup'].$today,
			'homepage' => $this->fieldnames['homepage'].$today,
			'body' => $this->fieldnames['body'].$today,
			'checkcode' => $this->fieldnames['checkcode'].$today
		);
	
		/** WE DON'T LIKE SPAMMERS **/
		$errors = array();
		
		if (!isset($_POST[$fieldnames['name']])) exit();
		if (!isset($_POST[$fieldnames['email']])) exit();
		if (!isset($_POST[$fieldnames['scoutgroup']])) exit();
		if (!isset($_POST[$fieldnames['homepage']])) exit();
		if (!isset($_POST[$fieldnames['body']])) exit();
		if (!isset($_POST[$fieldnames['checkcode']])) exit();

		if (!isset($_SESSION['gb_expiration'])) {
			$this->notSoFatalError("Meldingen ble IKKE lagret. Gå tilbake og prøv på nytt",array('logError' => false));
			print "<pre>";
			print_r($_POST);
			print "</pre>";
			exit();			
		}
		$e = explode("-",$_SESSION['gb_expiration']);
		$gid = $e[1];
		if (!is_numeric($gid)) return false;
		if (($gid < time()-7200) || ($gid > time()-15)) {
			array_push($errors,"toofastorslow");
		}

		/** PARSE **/
			
		$name 		= $_POST[$fieldnames['name']];
		$email 		= $_POST[$fieldnames['email']];
		$scoutgroup = $_POST[$fieldnames['scoutgroup']];
		$homepage 	= $_POST[$fieldnames['homepage']];
		$body 		= $_POST[$fieldnames['body']];
		$checkcode 	= strtoupper($_POST[$fieldnames['checkcode']]);
		
		/** VALIDATE **/
		
		if (empty($name)) array_push($errors,"empty_name");
		if (empty($email)) array_push($errors,"empty_email");
		else if (!call_user_func($this->isValidEmail,$email)) array_push($errors,"invalid_email");
		if (empty($body)) array_push($errors,"empty_body");
		if (preg_match('/<.*>.*<\/.*>/s',$name)) array_push($errors,"invalid_name");
		if (preg_match('/<.* \/>/',$name)) array_push($errors,"invalid_name");
		if (preg_match('/<.*>.*<\/.*>/s',$email)) array_push($errors,"invalid_email");
		if (preg_match('/<.* \/>/',$email)) array_push($errors,"invalid_email");
		if (preg_match('/<.*>.*<\/.*>/s',$body)) array_push($errors,"invalid_body");
		if (preg_match('/<.* \/>/',$body)) array_push($errors,"invalid_body");

		if (!isset($_SESSION['stv18bg']) || ($checkcode != $_SESSION['stv18bg'])) {
			array_push($errors,"invalid_checkcode");			
		}

		$this->bannedWords = explode(",",$this->bannedWords);
		foreach ($this->bannedWords as $bword) {
			if (strpos($body,$bword) !== false) {
				array_push($errors,"banned_word");
				break;
			} else if (strpos($homepage,$bword) !== false) {
				array_push($errors,"banned_word");
				break;
			}
		}		
		if (count($errors) > 0){
			$_SESSION['errors'] = array_unique($errors);
			$_SESSION['postdata'] = $_POST;
			$error_url = $this->generateURL("");
			$this->redirect(
				$error_url,
				'Innlegget ble ikke lagret pga. en eller flere feil.',
				'error'
			);
		}
		
		
		$timestamp = time();
		
		/** FIX LINEBREAKS AND SECURE **/

		$body = str_replace("\r\n","\n",$body);
		$body = str_replace("\r","\n",$body);
		
		$body = addslashes(strip_tags($body));

		$name = addslashes(strip_tags($name));
		$email = addslashes(strip_tags($email));
		$scoutgroup = addslashes(strip_tags($scoutgroup));
		$homepage = addslashes(strip_tags($homepage));

		
		$this->query(
			"INSERT INTO $this->tablename (page,name,email,url,scoutgroup,body,timestamp) 
			VALUES ('".$this->page_id."','$name','$email','$homepage','$scoutgroup','$body','$timestamp')"
		);

		$this->addToActivityLog("skrev et nytt innlegg i <a href='".$this->generateCoolUrl("/")."'>".$this->header."</a>.");
		
		unset($_SESSION['gb_expiration']);
		$this->redirect($this->generateURL("gb_saved"));

	}

	function deleteEntry($id){

		if (!is_numeric($id)) $this->fatalError("invalid input!");
		
		if (!$this->allow_delete) return $this->permissionDenied(); 


		$Result = $this->query("SELECT name FROM $this->tablename WHERE id='".addslashes($id)."'");
		$Row = $Result->fetch_assoc();
		if (!is_array($Row)) $this->fatalError("Innlegget eksisterer ikke!"); 
		$author = stripslashes($Row['name']);

		if (isset($_GET['delconf'])){
			$this->query("DELETE FROM $this->tablename WHERE id='".addslashes($id)."'");			
			$this->addToActivityLog("slettet et innlegg i <a href='".$this->generateCoolUrl("/")."'>".$this->header."</a> skrevet av $author.");
			$this->redirect($this->generateURL(""),$this->label_entrydeleted);
		} else {
			return '
				<h2>'.$this->label_confirmdelete_header.'</h2>
				<p>
					'.str_replace("%author%",$author,$this->label_confirmdelete_paragraph).'
				</p>
				<form method="post" action="'.$this->generateURL(array("noprint=true","gb_delete=$id","delconf=true")).'">
					<input type="submit" value="     '.$this->label_yes.'      " /> 
					<input type="button" value="     '.$this->label_no.'      " onclick=\'window.location="'.$_SERVER['HTTP_REFERER'].'"\' />
				</form>
			';
		}
	}

	

	function printBook(){
		global $login, $memberdb, $months;
		if (isset($_GET['g'])){ $grpstr = "&amp;g=".$_GET['g']; } else { $grpstr = ""; }
		
		$output = "";
		//print "<h1>$this->page_header</h1>";

		$today = date('myd',time());
		$fieldnames = array(
			'name' => $this->fieldnames['name'].$today,
			'email' => $this->fieldnames['email'].$today,
			'scoutgroup' => $this->fieldnames['scoutgroup'].$today,
			'homepage' => $this->fieldnames['homepage'].$today,
			'body' => $this->fieldnames['body'].$today,
			'checkcode' => $this->fieldnames['checkcode'].$today
		);
		
		if (isset($_GET['gb_saved'])){
			$output .= "<h2>$this->label_thankyou_header</h2>
				$this->label_thankyou_paragraph";
		} else if ($this->allow_addentry) {
			$_SESSION['gb_expiration'] = rand()."-".time();

			$value_name = "";
			$value_email = "";
			$value_scoutgroup = "";
			$value_homepage = "";
			$value_body = "";
			$value_checkcode = "";

			if (isset($_SESSION['errors'])){
				$errstr = "<ul>";
				foreach ($_SESSION['errors'] as $s){
					$errstr.= "<li>".$this->errorMessages[$s]."</li>";
				}
				$errstr .= "</ul>";
				$output .= $this->notSoFatalError($errstr,array('logError'=>false,'customHeader'=>$this->label_error_intro));
			
				if (isset($_SESSION['postdata'][$fieldnames['name']]))	
					$value_name = htmlentities($_SESSION['postdata'][$fieldnames['name']]);
				if (isset($_SESSION['postdata'][$fieldnames['email']])) 	
					$value_email = htmlentities($_SESSION['postdata'][$fieldnames['email']]);
				if (isset($_SESSION['postdata'][$fieldnames['scoutgroup']])) 	
					$value_scoutgroup = htmlentities($_SESSION['postdata'][$fieldnames['scoutgroup']]);
				if (isset($_SESSION['postdata'][$fieldnames['homepage']])) 	
					$value_homepage = htmlentities($_SESSION['postdata'][$fieldnames['homepage']]);
				if (isset($_SESSION['postdata'][$fieldnames['body']]))		
					$value_body = htmlentities($_SESSION['postdata'][$fieldnames['body']]);
				if (isset($_SESSION['postdata'][$fieldnames['checkcode']]))		
					$value_checkcode = htmlentities($_SESSION['postdata'][$fieldnames['checkcode']]);
				
				unset($_SESSION['errors']);
				unset($_SESSION['postdata']);
			}
			
			$output .= "
			<h2>$this->label_signourguestbook</h2>
			<form method=\"post\" action=\"".$this->generateURL(array("noprint=true","gb_save"))."\"> 
				<table>
					<tr>
						<td align='right'>
							$this->label_name:
						</td><td>
							<input type=\"text\" name=\"".$fieldnames['name']."\" value=\"$value_name\" size=\"30\" />
						</td>
					</tr><tr>
						<td align='right'>
							$this->label_email:
						</td><td>
							<input type=\"text\" name=\"".$fieldnames['email']."\" value=\"$value_email\" size=\"30\" />
						</td>
					</tr><tr>
						<td valign='top' align='right'>
							$this->label_scoutgroup:
						</td><td>
							<input type=\"text\" name=\"".$fieldnames['scoutgroup']."\" value=\"$value_scoutgroup\" size=\"30\" /> 
							<span style='color: #888888;font-size:80%;'>$this->label_maybeleftblank</span>
						</td>
					</tr><tr>
						<td valign='top' align='right'>
							$this->label_homepage:
						</td><td>
							<input type=\"text\" name=\"".$fieldnames['homepage']."\" value=\"$value_homepage\" size=\"30\" /> 
							<span style='color: #888888;font-size:80%;'>$this->label_maybeleftblank</span>
						</td>
					</tr><tr>
						<td valign=\"top\" align='right'>
							$this->label_comment:
						</td><td>
							<textarea name=\"".$fieldnames['body']."\" cols=\"40\" rows=\"6\">$value_body</textarea>
						</td>
					</tr><tr>
						<td valign=\"top\" align='right'>
							$this->label_checkcode:
						</td><td>
							<img src=\"/verify.php\" alt='Verification image' /><br />
							".$this->label_checkcodedesc."<br />
							<input name=\"".$fieldnames['checkcode']."\" value=\"$value_checkcode\" size=\"20\" />
						</td>
					</tr><tr>
						<td></td>
						<td>
							<input type=\"submit\" value=\"     $this->label_submit      \" /> 
						</td>
					</tr>
				</table>
			</form>
			";
			
		}
		$output .= "<h2>$this->label_readourguestbook</h2>";
		$Result = $this->query("SELECT COUNT(*) FROM $this->tablename WHERE page=".$this->page_id);
		$Row = $Result->fetch_array();
		$gbCount = $Row[0];
		$gbsPerPage = $this->entriesPerPage;
		$totalPages = ceil($gbCount / $gbsPerPage);
		if ($totalPages == 0) $totalPages = 1;
		if (isset($_GET["p"])){
			$currentPage = $_GET["p"];
		} else {
			$currentPage = 1;
		}
		$startAt = ($currentPage-1)*$gbsPerPage;
		$Result = $this->query("SELECT id,name,email,url,body,scoutgroup,timestamp FROM $this->tablename WHERE page=".$this->page_id." ORDER BY timestamp DESC LIMIT $startAt,$gbsPerPage");
		$b = 0;

		if ($currentPage > 1) 
			$this->document_title = 'Side '.$currentPage;
		
		/*
		$okToDelete = false;
		$ansprof = $memberdb->userByTask("webmaster");
		if ($login->ident == $ansprof->ident){
			$okToDelete = true;
		}
		*/
		
		if ($Result->num_rows == 0) {
			$output .= "<p><i>$this->label_noentries</i></p>";
			return $output;
		}
		
		while ($Row = $Result->fetch_assoc()){
			if (round($b/2) == ($b/2)){
				$bs = "border-bottom: 1px solid #EEEEEE; border-right:1px solid #EEEEEE;";
			} else {
				$bs = "border-bottom:1px solid #EEEEEE; border-left: 1px solid #EEEEEE;";
			}
			$output .= "
				<div class='gbentry' id='gbentry".$Row['id']."' style=\"background-color:#ffffff;\">
					<div style=\"padding:20px;\">
						<div style='padding: 0px 0px 10px 0px;'>
							<table style=\"width: 460px; border:0px solid #CCCCCC; font-size: 90%;\">
								<tr>
									<td width='100' style='font-weight: bold; text-align:right;'>$this->label_writtenby:</td>
									<td>".stripslashes($Row['name'])."</td>
								</tr><tr>
									<td style='font-weight: bold; text-align:right;'>$this->label_writtenat:</td>
									<td>".date("d",$Row['timestamp']).". ".strtolower($months[date("n",$Row['timestamp'])-1])." ".date("Y",$Row['timestamp'])."</td>
								</tr>".
								(empty($Row['scoutgroup']) ? "" : 
									"<tr>
										<td style='font-weight: bold; text-align:right;'>$this->label_scoutgroup:</td>
										<td>".$Row['scoutgroup']."</td>
									</tr>").
								(empty($Row['email']) ? "" : 
									"<tr>
										<td style='font-weight: bold; text-align:right;'>$this->label_email:</td>
										<td>
										".($this->isLoggedIn() ? "
										<a href=\"mailto:".$Row['email']."\">".$Row['email']."</a>
										" : "<i>$this->label_hiddenemail</i>")."
										</td>
									</tr>").
								((empty($Row['url']) || ($Row['url'] == 'http://'))  ? "" : 
									"<tr>
										<td style='font-weight: bold; text-align:right;'>$this->label_homepage:</td>
										<td><a href=\"".$Row['url']."\" target=\"_blank\">".$Row['url']."</a></td>
									</tr>").
								"
							</table>
						</div>
						<div style='padding:10px 0px 0px 0px; border-top: 1px dashed #aaa;'>".str_replace("\n","<br />\n",stripslashes($Row['body']))."</div>";
						if ($this->allow_delete) {
							$output .= '<p align="right" class="footerLinks">
								<a href="'.$this->generateURL("gb_delete=".$Row['id']).'" class="delete">'.$this->label_delete_entry.'</a>
								</p>';
						}
						$b++;
			$output .= "
					</div>
				</div>
			";
			$output .= "<p>&nbsp;</p>";
		}
		$output .="<br /><br />\n";
		$output .= "<table width='100%'><tr><td>";
		if ($currentPage == 1){
			$output .= $this->label_newer;
		} else {
			$pp = $currentPage-1;
			$output .= "<a href=\"".$this->generateURL("p=$pp")."\">$this->label_newer</a>";
		}
		$output .= "</td><td align='center'>";
		$output .= str_replace(array("%x%","%y%"),array($currentPage,$totalPages),$this->label_pagexofy);
		$output .= "</td><td align='right'>";
		if ($currentPage == $totalPages){
			$output .= $this->label_older;
		} else {
			$np = $currentPage+1;
			$output .= "<a href=\"".$this->generateURL("p=$np")."\">$this->label_older</a>";
		}
		$output .= "</td></tr></table>\n";
		

		$output .= "
			
			<script type=\"text/javascript\">
			//<![CDATA[
				YAHOO.util.Event.onDOMReady(function() {
					Nifty('div.gbentry');					
				});
			//]]>
			</script>
			<div style='height:1px; clear:both;'><!-- --></div>
		";			
		
		return $output;
	}

}

?>