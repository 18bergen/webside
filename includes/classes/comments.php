<?
class Comments extends base {

	var $enable_comments = true;
	
	var $lastcommentscount = 10;

	var $allow_addcomment = false;
	var $allow_editowncomments = false;
	var $allow_editotherscomments = false;
	var $allow_deleteowncomments = false;
	var $allow_deleteotherscomments = false;
	
	var $label_comments = "Kommentarer:";
	var $label_comment = "Kommentar";
	var $label_name = "Navn";
	var $label_email = "E-postadresse";
	var $label_verify = "Sikkerhetskode";

	var $label_commentsaved = "Kommentaren ble lagret.";
	var $label_commentedited = "Kommentaren ble endret.";
	var $label_newcomment = "Skriv en ny kommentar:";
	var $label_editcomment = "Endre kommentar";
	var $label_deletecomment = "Slett kommentar";
	var $label_confirmdeletecomment_header = "Bekreft sletting";
	var $label_confirmdeletecomment_paragraph = "Er du sikker på at du vil slette denne kommentaren?";
	var $label_nocomments = "Ingen kommentarer";
	var $label_lastcomments = "Siste kommentarer:";
	var $label_savecomment = "Lagre kommentar";
	var $label_writtenby = "Skrevet %timestamp% av %author%";
	
	var $label_error_emptyname = "Du fylte ikke inn noe navn";
	var $label_error_emptyemail = "Du fylte ikke inn noen epostadresse";
	var $label_error_emptybody = "Du fylte ikke inn noen kommentar";
	var $label_error_invalidname = "Du fylte inn et ugyldig navn. Tips: HTML er ikke tillatt";
	var $label_error_invalidemail = "Du fylte inn en ugyldig epostadresse";
	var $label_error_invalidbody = "Du fylte inn en ugyldig kommentar. Tips: HTML er ikke tillatt";
	var $label_error_invalidverify = "Du fylte inn feil sikkerhetskode.";
	
	var $label_yes = "Ja";
	var $label_no = "Nei";
	
	var $label_commentdoesntexist = "Kommentaren eksisterer ikke";
		
	var $label_error_intro						= "Det oppstod en eller flere feil:";
	
	var $template_dir 					= "../includes/templates/email/";
	var $template_newcomment 			= "comment_newcomment.txt";

	var $str_start_sub      = "%beginlink%Abonnér på kommentarer%endlink%";
	var $str_stop_sub       = "%beginlink%Stopp kommentarabonnement%endlink%";
	
	var $template_editcommentlink		= "| %beginlink%%edit%%endlink%";
	var $template_deletecommentlink		= "| %beginlink%%delete%%endlink%";

	var $commentListingString = "
		<div class='comment'>
			%body%
			<div class='commentFooter'>%footer%
				%editlink% %deletelink%
			</div>
		</div>";
	var $anonymous_fieldnames = array(
		'fullname' => 'fujlt_namn',
		'email' => 'paastaddress',
		'body' => 'notata',
		'verify' => 'vrijjfy'
	);
	var $commentfields1_template = "
			%errors%
			<table width='100%'>
				<tr>
					<td align='right' valign='top' width='300'>
						<textarea id='kosmotarFjelt' name='%fieldname_body%' id='body' rows='10' cols='30' class='textinput'>%body%</textarea>
					</td>
					<td valign='bottom'>
						<div style='font-size:10px;margin-bottom:8px;'>Vis/skjul: <a href='#' onclick='toggleSmileys(); return false;'>smileys</a></div>
					</td>
				</tr>
			</table>

			<div id='kosmotarSmileys' style='display:none;'>
				%smileysTable%
			</div>

			<script type=\"text/javascript\">
			
			function toggleSmileys() {
				if (YAHOO.util.Dom.getStyle('kosmotarSmileys','display') != 'block') {
					YAHOO.util.Dom.setStyle('kosmotarSmileys','display','block');
				} else {
					YAHOO.util.Dom.setStyle('kosmotarSmileys','display','none');
				}
			}

			function insertCommentSmiley(addSmilie) {
				$('kosmotarFjelt').value = $('kosmotarFjelt').value + ' ' + addSmilie ; 
				$('kosmotarFjelt').focus();
				return false;
			}

			</script>

	";
	var $commentfields0_template = "
			%errors%
			<p style='color:#666;font-size:10px;text-align:center;'>Tips: <a href='#' onclick='$(\"brukernavn\").focus();return false;'>Logg inn</a>, så slipper du å fyll inn navn, epost og sikkerhetskode.</p>
			<table width='100%'>
				<tr>
					<td valign='top' align='right'>%label_name%: </td>
					<td align='left'><input name='%fieldname_fullname%' id='%fieldname_fullname%' value=\"%fullname%\" class='textinput' /></td>
				</tr>
				<tr>
					<td valign='top' align='right'>%label_email%: </td>
					<td align='left'><input name='%fieldname_email%' id='%fieldname_email%' value=\"%email%\" class='textinput' /></td>
				</tr>
				<tr>
					<td valign='top' align='right'>%label_verify%: </td>
					<td align='left'><img src=\"/verify.php\" alt='Verification image' /><br />
					Skriv inn teksten du ser i den svarte boksen over i feltet under. 
					Du trenger ikke skille mellom store og små bokstaver. 
					Dersom du ikke klarer å lese koden kan du bare trykke «Lagre» og du vil få 
					opp en ny kode. Skriv her:<br />
					<input name='%fieldname_verify%' id='%fieldname_verify%' value=\"%verify%\" size=\"20\" class='textinput' />
					</td>
				</tr>
				<tr>
					<td valign='top' align='right'>%label_comment%: </td>
					<td align='left'><textarea name='%fieldname_body%' id='%fieldname_body%' rows='10' cols='40' class='textinput'>%body%</textarea></td>
				</tr>
			</table>
	";
	var $editcomment_template = '
		<form method="post" action="%posturl%" id="commentform" class="commentform">
			<input type="hidden" name="parent_id" value="%parent_id%" />
 			<input type="hidden" name="comment_id" value="%id%" />
 			<input type="hidden" name="parent_desc" value="%parent_desc%" />
			%fields%
			<p><input type="submit" id="submit" value="%savecomment%" /></p>
		</form>
	';
	var $deletecomment_template = '
		<p>%paragraph%</p>
		<div style="background: #FFFFCC; margin: 10px; padding: 5px;">
			%comment%
		</div>
		<form method="post" action="%posturl%">
			<input type="hidden" name="commentId" value="%id%" />
			<input type="submit" value="     %yes%      " /> 
			<input type="button" value="     %no%      " onclick="window.location=\'%referer%\'" />
		</form>
	';

	
	var $comment_id = "_new"; // do not edit
	
	function comments() {
		$this->table_comments = DBPREFIX.'comments';	
	}
		
	function initialize_comments(){

		array_push($this->getvars, 'editComment', 'deleteComment');
		
		$this->errorMessages['empty_name'] = $this->label_error_emptyname;
		$this->errorMessages['empty_email'] = $this->label_error_emptyemail;
		$this->errorMessages['empty_body'] = $this->label_error_emptybody;
		$this->errorMessages['invalid_name'] = $this->label_error_invalidname;
		$this->errorMessages['invalid_email'] = $this->label_error_invalidemail;
		$this->errorMessages['invalid_body'] = $this->label_error_invalidbody;
		$this->errorMessages['expired'] = "Expired";
		$this->errorMessages['invalid_time'] = "Du brukte for kort eller lang tid på å skrive kommentaren. For at kommentaren skal bli lagret, må du bruke mellom 4 sekunder og 2 timer på å skrive den. Dette tiltaket er iverksatt for å beskytte mot automatisert spam.";
		$this->errorMessages['invalid_checkcode'] = $this->label_error_invalidverify;
	}

	function initialize() {
		@parent::initialize(); //$this->initialize_base();
		$this->table_comments = DBPREFIX.'comments';
		$this->table_subscriptions = DBPREFIX.'subscriptions';
		$this->initialize_comments();
	}
	
	function lookupCommentUrl($comment_id) {
		$comment_id = intval($comment_id);
		
		$tc = $this->table_comments;
		$res = $this->query("SELECT page_id,parent_id FROM $tc WHERE id=$comment_id");
		if ($res->num_rows != 1) {
			$this->fatalError("Fant ikke kommentaren");
		}
		$row = $res->fetch_assoc();
		$page_id = intval($row['page_id']);
		$parent_id = intval($row['parent_id']);
		$page = $this->preparePageInstance($page_id);
		$this->redirect($page->getLinkToEntry($parent_id).'#respond');		
	}

	function run() {
		$this->initialize();
		
		$comment_id = 0;
		if (isset($this->coolUrlSplitted[0]) && is_numeric($this->coolUrlSplitted[0])) {
			$comment_id = intval($this->coolUrlSplitted[0]);
		}
		if ($comment_id > 0) {
			$this->lookupCommentUrl($comment_id);
		}

		$output = '';
			
		$tc = $this->table_comments;
		$tp = $this->table_pages;
		$tpc = $this->table_classes;
		$res = $this->query(
			"SELECT $tc.id,$tc.page_id,$tc.parent_id,$tc.author_id,$tc.author_name,$tc.author_email,$tc.timestamp,$tc.body,
				$tpc.classname
			FROM $tc,$tp,$tpc
			WHERE $tc.page_id=$tp.id AND $tp.class=$tpc.id
			ORDER BY $tc.timestamp DESC 
			LIMIT 10"
		);
		if ($res->num_rows == 0){
			$output .= $this->label_nocomments;
		}
		while ($row = $res->fetch_assoc()){
			$body = stripslashes($row['body']);
			$classname = $row['classname'];
			$s = 'ukjent';
			switch ($classname) {
				case 'noteboard': $s = 'en nyhet'; break;
				case 'imagearchive': $s = 'et foto i bildearkivet'; break;
				case 'log': $s = 'en logg'; break;
				case 'article_collection': $s = 'et speidertips'; break;
			}
			
			if ($row['author_id'] != 0){
				$author = call_user_func($this->lookup_member, $row['author_id']);
				$author = call_user_func($this->make_memberlink, $author->ident, $author->firstname);			
			} else {
				$author = $row['author_name']. " (gjest)";
			}
			//$unixtime = ;
			//if ($unixtime < strtotime('1900-01-01')) $unixtime = 0;
	
			$dateStr = strftime('%e. %B %Y',strtotime($row["timestamp"]));

			$output .= '<div style="padding:3px;">
				<a href="'.$this->generateCoolURL('/'.$row['id']).'" class="icn" style="background-image:url(/images/icns/comment.png);">'.$body."</a>
				<div style='font-size:10px;'>kommentar til $s, $dateStr av $author</div>
				</div>";

		}
		return $output;
	}

	function printLastComments($page_id = 0, $numComments = 10) {
		$page_id = intval($page_id);
		$numComments = intval($numComments);
		
		$output = "<h3>$this->label_lastcomments</h3>
			<div class='lastcomments'>";
			
		$tc = $this->table_comments;
		$whereCriterion = ($page_id > 0) ? " WHERE page_id=$page_id":"";
		$res = $this->query(
			"SELECT id,page_id,parent_id,author_id,author_name,author_email,timestamp,body
			FROM $tc
			$whereCriterion
			ORDER BY timestamp DESC 
			LIMIT $numComments"
		);
		if ($res->num_rows == 0){
			$output .= $this->label_nocomments;
		}
		while ($row = $res->fetch_assoc()){
			$body = strip_bbcode(stripslashes($row["body"]));
			$body = str_replace("<br />","",$body);
			if (strlen($body) > 30) $body = mb_substr($body,0,27,'UTF-8')."...";

			if ($row['author_id'] != 0){
				$author = call_user_func($this->lookup_member, $row['author_id']);
				$author = call_user_func($this->make_memberlink, $author->ident, $author->firstname);			
			} else {
				$author = $row['author_name']. " (gjest)";
			}
			//$unixtime = ;
			//if ($unixtime < strtotime('1900-01-01')) $unixtime = 0;
	
			$dateStr = strftime('%d.%m',strtotime($row["timestamp"]));

			$output .= '<div style="padding:3px;font-size:10px;">
				<span style="color:#777">'.$dateStr.': </span><a href="/kommentarer/'.$row['id'].'" style="background:url(/images/icns/comment.png) left no-repeat;padding-left:18px;">'.$body."</a>
				</div>";

		}
		$output .= '</div>';
		return $output;
	}	
	
	function prepare_comment_body($str){
		$str = stripslashes($str);
		$str = parse_bbcode($str);
		$str = parse_emoticons($str);
		return $str;
	}

	function commentCount($id){
		if (!is_numeric($id)){ $this->fatalError("incorrect input!"); }
		$id = intval($id);
		$res = $this->query("SELECT COUNT(id) as ccount
			FROM $this->table_comments
			WHERE page_id=$this->page_id AND parent_id=$id");
 		$row = $res->fetch_assoc();
		return intval($row['ccount']);
	}

	function printComments($post_id){
		$output = "";
		$post_id = intval($post_id);
		if (!is_numeric($post_id)){ $this->fatalError("incorrect input!"); }
        if (isset($_GET['editComment'])){
			return $this->editCommentForm($post_id);
		} else if (isset($_GET['deleteComment'])){
			return $this->deleteCommentForm();
		}
		
		$subscribecode = "";
        if ($this->isLoggedIn()) {
            $ts = $this->table_subscriptions;
            $res = $this->query(
                "SELECT timestamp FROM $ts WHERE page_id=".$this->page_id."
                    AND parent_id=".$post_id." AND user_id=".$this->login_identifier);
            if ($res->num_rows == 0) {
                $subscribecode = str_replace(array("%beginlink%","%endlink%"),array('<a href="'.$this->generateURL("action=subscribeToThread").'" class="subscribe">','</a>'),$this->str_start_sub);
            } else {
                $subscribecode = str_replace(array("%beginlink%","%endlink%"),array('<a href="'.$this->generateURL("action=unsubscribeFromThread").'" class="subscribe">','</a>'),$this->str_stop_sub);
            }
        }

		$output .= "<h3 id='respond' name='respond'>$this->label_comments</h3>";
        $output .= "<div class='footerLinks' style='margin-bottom:12px;'>$subscribecode</div>";		

		$res = $this->query("SELECT id,parent_id,author_id,author_name,author_email,timestamp,body
			FROM $this->table_comments
			WHERE page_id=$this->page_id AND parent_id=$post_id
			ORDER BY timestamp");
 		if ($res->num_rows == 0){
 			$output .= "<div style='color:#999;padding:5px;'>$this->label_nocomments</div>";
 		} else {
 			while ($row = $res->fetch_assoc()){
				$output .= $this->outputComment($row);
			}
		}
		$output .= $this->editCommentForm($post_id);
		return $output;
 	}

	function outputComment($row,$showFooterLinks = true){
		if ($row['author_id'] != 0){
			$author = call_user_func($this->lookup_member, $row['author_id']);
			$author = call_user_func($this->make_memberlink, $author->ident, $author->firstname);			
		} else {
			$author = $row['author_name']. " (gjest)";
		}
		//$unixtime = ;
		//if ($unixtime < strtotime('1900-01-01')) $unixtime = 0;

		$dateStr = strftime('%A %e. %B %Y',strtotime($row["timestamp"]));
		//$dateStr = date("d",$row["timestamp"]).". ".$this->months[date("m",$row["timestamp"])-1]." ".date("Y",$row["timestamp"]);
		
		$editlinkcode = ($this->allow_editotherscomments || (($this->allow_editowncomments) && ($row['author_id'] == $this->login_identifier))) 
			? str_replace(array("%beginlink%","%edit%","%endlink%"),array("<a href=\"".$this->generateURL(array("editComment=%id%"))."#respond\">",$this->label_editcomment,"</a>"),$this->template_editcommentlink) : "";
		$deletelinkcode = (($this->allow_deleteotherscomments) || (($this->allow_deleteowncomments) && ($row['author_id'] == $this->login_identifier))) 
			? str_replace(array("%beginlink%","%delete%","%endlink%"),array("<a href=\"".$this->generateURL(array("deleteComment=%id%"))."#respond\">",$this->label_deletecomment,"</a>"),$this->template_deletecommentlink) : "";
		$r1a[0] = "%id%";			$r2a[0] = $row['id'];
		$r1a[2] = "%body%";			$r2a[2] = $this->prepare_comment_body($row['body']);
		$r1a[1] = "%author%";		$r2a[1] = $author;
		$r1a[3] = "%timestamp%";	$r2a[3] = $dateStr;
		$r1a[4] = "%origin%";		$r2a[4] = $row['parent_id'];
		$r1a[5] = "%editlink%"; 	$r2a[5] = ($showFooterLinks ? str_replace($r1a, $r2a, $editlinkcode) : "");
		$r1a[6] = "%deletelink%"; 	$r2a[6] = ($showFooterLinks ? str_replace($r1a, $r2a, $deletelinkcode) : "");
		$r1a[7] = "%footer%";		$r2a[7] = str_replace($r1a, $r2a, $this->label_writtenby);
		$outp = str_replace($r1a, $r2a, $this->commentListingString);
		return $outp;
		
		
	}
	
	function makeSmileysTable($insertFunction) {
		$c = "
		<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"2\">
		  <tr align=\"center\" valign=\"bottom\"> 
			<td width=\"6.25%\"><a href=\"#\" onclick=\"$insertFunction(':)'); return false;\"><img src=\"/images/smileys/smiley.gif\" alt=':)' border=\"0\"></a></td>
			<td width=\"6.25%\"><a href=\"#\" onclick=\"$insertFunction(':('); return false;\"><img src=\"/images/smileys/sad.gif\" alt=':(' border=\"0\"></a></td>
			<td width=\"6.25%\"><a href=\"#\" onclick=\"$insertFunction(':P'); return false;\"><img src=\"/images/smileys/tongue.gif\" alt=':P' border=\"0\"></a></td>
			<td width=\"6.25%\"><a href=\"#\" onclick=\"$insertFunction('B)'); return false;\"><img src=\"/images/smileys/cool.gif\" alt='B)' border=\"0\"></a></td>
			<td width=\"6.25%\"><a href=\"#\" onclick=\"$insertFunction(';)'); return false;\"><img src=\"/images/smileys/wink.gif\" alt=';)' border=\"0\"></a></td>
			<td width=\"6.25%\"><a href=\"#\" onclick=\"$insertFunction(':D'); return false;\"><img src=\"/images/smileys/biggrin.gif\" alt=':D' border=\"0\"></a></td>
		  	";
		  	
		$smileys = array(
			array('rolleyes','laugh','lol','dozey','glasses','surprised','thinking','uhoh','pleased','huh'),
			
			array('eek','yes','no','upset','confused','sigh','shy','shocked',
				'bandana','grin','nono','scout','klapp','thumbup','engel','speechless'),
				
			array('stunned','sick','smug','party','pirate','sleep','book','bow','builder','chef',
				'dizzy','ears','idea','mad','elf','juggle'),

			array('baby',
			'cyclist','policeman','dead','santa','cowboy','curtain',
			'deal','goofy','sunny','heart','bulb','tup','tdown','balloon','computer')
				
		);
		$first = true;
		foreach ($smileys as $r) {
			if ($first) $first = false;
			else $c .= "		<tr align=\"center\" valign=\"bottom\">\n";
			foreach ($r as $s) {
				$c .= "		<td width=\"6.25%\"><a href=\"#\" onclick=\"$insertFunction(':$s:'); return false;\"><img src=\"/images/smileys/$s.gif\" alt=':$s:' border=\"0\"></a></td>\n";
			}
			$c .= "		</tr>\n";
		}
		$c .= "
		</table>
		";
		return $c;
	}

	
 	function editCommentForm($post_id = 0){

        $comment_id = '_new';
		if (isset($_GET['editComment'])){
			$comment_id = intval($_GET['editComment']);
		}
 	
 		$output = "";
 		
		$_SESSION['co_expiration'] = rand()."-".time();
		$default_fullname = "";
		$default_verify = "";
		$default_email = "";
		$default_body = "";
 		if ($comment_id == '_new'){
			if (!$this->allow_addcomment) return $this->permissionDenied();
			if (!is_numeric($post_id)){ $this->fatalError("incorrect input!"); }
			$post_id = intval($post_id);
 			$output .= "<h4>$this->label_newcomment</h4>"; // "<h3>Skriv ny kommentar:</h3>";
 		} else {
 			if ($comment_id <= 0){ $this->fatalError("incorrect input!"); }
 			$res = $this->query("SELECT parent_id,author_id,author_name,author_email,body
				FROM $this->table_comments
				WHERE id=$comment_id");
			if ($res->num_rows != 1) return $this->notSoFatalError($this->label_commentdoesntexist); 
			$row = $res->fetch_assoc();
			$allowed = (($this->allow_editotherscomments) || (($this->allow_editowncomments) && ($row['author_id'] == $this->login_identifier)));
			if (!$allowed) return $this->permissionDenied();
			$post_id = intval($row['parent_id']);
			$default_fullname = stripslashes($row["author_name"]);
			$default_email = stripslashes($row["author_email"]);
			$default_body = stripslashes($row["body"]);
			$default_body = str_replace("<br />\r\n","\r\n",$default_body);
			$default_body = str_replace("<br />\n","\n",$default_body);
			$output .= "<h3 id='respond' name='respond'>$this->label_comments</h3>";
			$output .= "<h4>$this->label_editcomment:</h4>"; // "<h3>Endre kommentar:</h3>";
 		}
 	
 		$errstr = '';
		if (isset($_SESSION['errors'])){
			$errstr = $this->label_error_intro."<ul>";
			foreach ($_SESSION['errors'] as $s){
				$errstr.= "<li>".$this->errorMessages[$s]."</li>";
			}
			$errstr .= "</ul>";
			$errstr = $this->notSoFatalError($errstr,array('logError'=>false));
			
			if (isset($_SESSION['postdata'][$this->anonymous_fieldnames['fullname']]))	
				$default_fullname = $_SESSION['postdata'][$this->anonymous_fieldnames['fullname']];
			if (isset($_SESSION['postdata'][$this->anonymous_fieldnames['email']])) 	
				$default_email = $_SESSION['postdata'][$this->anonymous_fieldnames['email']];
			if (isset($_SESSION['postdata'][$this->anonymous_fieldnames['body']]))		
				$default_body = $_SESSION['postdata'][$this->anonymous_fieldnames['body']];
			
			unset($_SESSION['errors']);
			unset($_SESSION['postdata']);
		}


 		$r1a   = array();					$r2a   = array();
		$r1a[] = "%id%";					$r2a[] = $comment_id;
		$r1a[] = "%comment_id%";			$r2a[] = $comment_id;
		$r1a[] = "%errors%";				$r2a[] = $errstr;
		$r1a[] = "%body%";					$r2a[] = htmlspecialchars($default_body);
		$r1a[] = "%parent_id%";				$r2a[] = $post_id;
		$r1a[] = "%posturl%";				$r2a[] = $this->generateURL('action=saveComment');
		$r1a[] = "%fullname%";				$r2a[] = htmlspecialchars($default_fullname);
		$r1a[] = "%email%";					$r2a[] = htmlspecialchars($default_email);
		$r1a[] = "%verify%";				$r2a[] = htmlspecialchars($default_verify);
		$r1a[] = "%comment%";				$r2a[] = $this->label_comment;
		$r1a[] = "%savecomment%";			$r2a[] = $this->label_savecomment;
		$r1a[] = "%parent_desc%";			$r2a[] = $this->comment_desc;
		$r1a[] = "%label_name%";			$r2a[] = $this->label_name;
		$r1a[] = "%label_email%";			$r2a[] = $this->label_email;
		$r1a[] = "%label_verify%";			$r2a[] = $this->label_verify;
		$r1a[] = "%label_comment%";			$r2a[] = $this->label_comment;
		$r1a[] = "%smileysTable%";			$r2a[] = $this->makeSmileysTable("insertCommentSmiley");
		$r1a[] = "%fieldname_fullname%";	$r2a[] = $this->anonymous_fieldnames['fullname'];
		$r1a[] = "%fieldname_email%";		$r2a[] = $this->anonymous_fieldnames['email'];
		$r1a[] = "%fieldname_body%";		$r2a[] = $this->anonymous_fieldnames['body'];
		$r1a[] = "%fieldname_verify%";		$r2a[] = $this->anonymous_fieldnames['verify'];
		$r1a[] = "%fields%";				$r2a[] = str_replace($r1a, $r2a, empty($this->login_identifier) ? 
			str_replace($r1a, $r2a, $this->commentfields0_template) : str_replace($r1a, $r2a, $this->commentfields1_template));
		$output .= str_replace($r1a, $r2a, $this->editcomment_template);
		return $output;
	}
 	 	
 	function saveComment($post_id, $context){
 		$id = $_POST['comment_id'];
 		if (!is_numeric($id) && $id != '_new') $this->fatalError("invalid input .1");
 		if (empty($this->login_identifier) && $id != '_new') $this->fatalError("invalid input .2");
 		
 		/** CHECK PERMISSION **/
 		
 		if (is_numeric($id)) {
 			$id = intval($id);
	 		$res = $this->query("SELECT author_id FROM $this->table_comments WHERE id=$id");
			if ($res->num_rows != 1) $this->fatalError($this->label_commentdoesntexist);
			$row = $res->fetch_assoc();
			$allowed = (($this->allow_editotherscomments) || (($this->allow_editowncomments) && ($row['author_id'] == $this->login_identifier)));
			if (!$allowed){ $this->permissionDenied(); exit(); }
		} else {
			if (!$this->allow_addcomment){ $this->permissionDenied(); exit(); };
 		}
 		
 		/** SPAM PROTECTION **/
 
		$errors = array();
 
 		if (empty($this->login_identifier)) {
			if (!isset($_SESSION['co_expiration'])) {
				$errors[] = 'expired';
			}
			$e = explode("-",$_SESSION['co_expiration']);
			$gid = $e[1];
			if (!is_numeric($gid)) return false;
			if (($gid < time()-7200) || ($gid > time()-4)) {
				$this->sendContentType();
				$errors[] = 'invalid_time';
			}
			
			$checkcode 	= strtoupper($_POST[$this->anonymous_fieldnames['verify']]);
			if (!isset($_SESSION['stv18bg']) || ($checkcode != $_SESSION['stv18bg'])) {
				array_push($errors,"invalid_checkcode");			
			}
			
		}
		if ((!isset($_POST['parent_desc'])) || (!is_numeric($_POST['parent_desc']))) {
			$this->fatalError("Invalid input: parent_desc");
		}
		switch ($_POST['parent_desc']) {
			case 1: 
				$parent_desc = 'denne nyheten';
				break;
			case 2:
				$parent_desc = 'dette bildet';
				break;
			case 3:
				$parent_desc = 'dette speidertipset';
				break;
			case 4:
				$parent_desc = 'denne loggen';
				break;
			case 5:
				$parent_desc = 'dette arrangementet';
				break;
			default:
				$parent_desc = 'denne siden';
				break;
		}
 		
 		/** PARSE **/
 		
 		if (isset($_POST[$this->anonymous_fieldnames['body']]))
	 		$body = $_POST[$this->anonymous_fieldnames['body']];
		else
			$body = ""; // This will cause validation error later
		
		if ($post_id <= 0) $this->fatalError("invalid input .671");
		
		$timestamp = time();
		if (empty($this->login_identifier)){
			$author_id = 0;
			if (isset($_POST[$this->anonymous_fieldnames['fullname']]))
				$fullname = $_POST[$this->anonymous_fieldnames['fullname']];
			else
				$fullname = ""; // This will cause validation error later
			if (isset($_POST[$this->anonymous_fieldnames['email']]))
				$email = $_POST[$this->anonymous_fieldnames['email']];
			else
				$email = ""; // This will cause validation error later
		} else {
			$author_id = $this->login_identifier;
			$fullname = "";
			$email = "";
		}
		
		/** VALIDATE **/
		
		if (empty($this->login_identifier) && empty($fullname)) array_push($errors,"empty_name");
		if (empty($this->login_identifier) && empty($email)) array_push($errors,"empty_email");
		else if (empty($this->login_identifier) && !call_user_func($this->isValidEmail,$email)) array_push($errors,"invalid_email");
		if (empty($body)) array_push($errors,"empty_body");
		if (empty($this->login_identifier) && preg_match('/<.*>.*<\/.*>/s',$fullname)) array_push($errors,"invalid_name");
		if (empty($this->login_identifier) && preg_match('/<.* \/>/',$fullname)) array_push($errors,"invalid_name");
		if (empty($this->login_identifier) && preg_match('/<.*>.*<\/.*>/s',$email)) array_push($errors,"invalid_email");
		if (empty($this->login_identifier) && preg_match('/<.* \/>/',$email)) array_push($errors,"invalid_email");
		if (preg_match('/<.*>.*<\/.*>/s',$body)) array_push($errors,"invalid_body");
		if (preg_match('/<.* \/>/',$body)) array_push($errors,"invalid_body");
		
		if (count($errors) > 0){
			$_SESSION['errors'] = array_unique($errors);
			$_SESSION['postdata'] = $_POST;
			$error_url = (($id == '_new') ? $this->generateURL("") : $this->generateURL("editcomment=$id")).'#respond';
			$this->redirect(
				$error_url,
				'Kommentaren ble ikke lagret pga. en eller flere feil.',
				'error'
			);
		}
		
		/** FIX LINEBREAKS AND SECURE **/
		$body = str_replace("\r\n","\n",$body);
		$body = str_replace("\r","\n",$body);
		
		$body = addslashes(strip_tags($body));

		$fullname = addslashes(strip_tags($fullname));
		$email = addslashes(strip_tags($email));

		if ($id == "_new"){
			
			$this->query("INSERT INTO $this->table_comments
				(page_id,parent_id,author_id,author_name,author_email,timestamp,body)
				VALUES ($this->page_id,$post_id,$author_id,\"$fullname\",\"$email\",NOW(),\"$body\")"
			);
			$id = $this->insert_id();
			
			$this->addToActivityLog("skrev en kommentar til <a href=\"".$this->generateURL("")."\">$parent_desc</a>",false,"comment");
			unset($_SESSION['co_expiration']);
			
			$this->notifySubscribers($id, $context, $body);
    	    comments::subscribeToThread($post_id, false);
    	    
			$this->redirect($this->generateURL('').'#respond', $this->label_commentsaved);

		} else {

			$this->query("UPDATE $this->table_comments
				SET
					author_name=\"$fullname\",
					author_email=\"$email\",
					body=\"$body\"
				WHERE id=$id"
			);
			
			//$this->addToActivityLog("Kommentaren $id i tabellen $this->table_comments ble endret.");
			unset($_SESSION['co_expiration']);
			$this->redirect($this->generateURL("").'#respond', $this->label_commentedited);

		}
 	}

	function deleteCommentDo(){
	    $id = intval($_POST['commentId']);
		if (!is_numeric($id)){ $this->fatalError("incorrect input! 041"); }
		$id = intval($id);
		if ($this->allow_deleteotherscomments){
			$this->query("DELETE FROM $this->table_comments WHERE page_id=$this->page_id AND id=$id");
			if ($this->affected_rows() == 1) 
				$this->redirect($this->generateURL(""),"Kommentaren ble slettet");
			else
				$this->redirect($this->generateURL(""),"Kommentaren ble ikke slettet","error");
		} else if ($this->allow_deleteowncomments) { 
			$this->query("DELETE FROM $this->table_comments
				WHERE page_id=$this->page_id AND id=$id AND author_id=\"$this->login_identifier\""
			);
			if ($this->affected_rows() == 1) 
				$this->redirect($this->generateURL("").'#respond',"Kommentaren ble slettet");
			else
				$this->redirect($this->generateURL(""),"Kommentaren ble ikke slettet","error");
		}
	}

	function deleteCommentForm(){
	    $id = intval($_GET['deleteComment']);
		if ($id <= 0){ $this->fatalError("incorrect input! 031"); }
		$res = $this->query("SELECT id,parent_id,author_id,author_name,author_email,timestamp,body
			FROM $this->table_comments
			WHERE page_id=$this->page_id AND id=$id"
		);
		if ($res->num_rows != 1) $this->fatalError($this->label_commentdoesntexist);
		$row = $res->fetch_assoc();
		if (($this->allow_deleteotherscomments) || (($this->allow_deleteowncomments) && ($row['author_id'] == $this->login_identifier))){ 
			$output = "<h3 id='respond' name='respond'>$this->label_comments</h3>";
			$r1a[0] = "%id%";			$r2a[0] = $id;
			$r1a[1] = "%comment%";		$r2a[1] = $this->outputComment($row,false);
			$r1a[3] = "%referer%";		$r2a[3] = $_SERVER['HTTP_REFERER'];
			$r1a[4] = "%posturl%";		$r2a[4] = $this->generateURL('action=deleteCommentDo');
			$r1a[5] = "%paragraph%";	$r2a[5] = $this->label_confirmdeletecomment_paragraph;
			$r1a[6] = "%yes%";			$r2a[6] = $this->label_yes;
			$r1a[7] = "%no%";			$r2a[7] = $this->label_no;
			$output .= str_replace($r1a, $r2a, $this->deletecomment_template);
			return $output;
		} else {
			return $this->notSoFatalError("You're not allowed to execute the operation.");
		}
	}
	
	function subscribeToThread($id, $redirect = true) {
	    $id = intval($id);
	    if (!$this->isLoggedIn()) {
    	    return "Du er ikke logga inn";
	    }
		$res = $this->query("SELECT * FROM $this->table_subscriptions 
		    WHERE user_id=$this->login_identifier AND page_id=$this->page_id AND parent_id=$id");
		if ($res->num_rows > 0) {
			if ($redirect) $this->redirect($this->generateURL(''), 'Du abbonerer allerede på kommentarer for denne posten');
			return;
		}
		$this->query("INSERT INTO $this->table_subscriptions
				(user_id,parent_id,page_id,timestamp)
				VALUES ($this->login_identifier,$id,$this->page_id,NOW())"
			);
		if ($redirect) $this->redirect($this->generateURL(''), 'Du vil nå få beskjed hvis det kommer nye kommentarer her');
	}

	function unsubscribeFromThread($id, $redirect = true) {
	    $id = intval($id);
	    if (!$this->isLoggedIn()) {
    	    return "Du er ikke logga inn";
	    }
		$res = $this->query("SELECT * FROM $this->table_subscriptions 
		    WHERE user_id=$this->login_identifier AND page_id=$this->page_id AND parent_id=$id");
		if ($res->num_rows == 0) return;
		$this->query("DELETE FROM $this->table_subscriptions 
		    WHERE user_id=$this->login_identifier AND page_id=$this->page_id AND parent_id=$id LIMIT 1");
		if ($redirect) $this->redirect($this->generateURL(''), 'Du vil ikke lenger få beskjed hvis det kommer nye kommentarer her');
	}

	function notifySubscribers($comment_id, $context, $comment_body) {
		$comment_id = intval($comment_id);
		$res = $this->query("SELECT
			page_id,parent_id,author_id,author_name,author_email,timestamp,body
			FROM $this->table_comments 
			WHERE id=$comment_id"
		);
		$row = $res->fetch_assoc();
		$parent_id = intval($row['parent_id']);
		$page_id = intval($row['page_id']);
        if ($row['author_id'] != 0){
            $author = call_user_func($this->lookup_member, $row['author_id']);
            $author_html = call_user_func($this->make_memberlink, $author->ident, $author->firstname);
            $author_plain = $author->firstname;
        } else {
            $author_html = $row['author_name']. " (gjest)";
            $author_plain = $row['author_name']. " (gjest)";
        }
        
    	$subject = '[18. Bergen] Ny kommentar til '.$context;

		$server = "http://".$_SERVER['SERVER_NAME'];

		$template = file_get_contents($this->template_dir.$this->template_newcomment);
        $r1a = array(); $r2a = array();
        $r1a[] = '%author_name%';		$r2a[] = $author_plain;
        $r1a[] = '%context%';			$r2a[] = $context;
        $r1a[] = '%comment%';			$r2a[] = $comment_body;
        $r1a[] = '%post_url%';			$r2a[] = $server.$this->generateURL('');
        $r1a[] = '%unsubscribe_url%';	$r2a[] = $server.$this->generateURL('action=unsubscribeFromThread');
        $plainBody = str_replace($r1a, $r2a, $template);

		$res = $this->query("SELECT user_id FROM $this->table_subscriptions WHERE page_id=$page_id AND parent_id=$parent_id");
		while ($row = $res->fetch_assoc()) {
		    $userId = intval($row['user_id']);
		    //if ($userId != $this->login_identifier) {
                $user = call_user_func($this->lookup_member, $userId);
                $this->sendEmailNotification($user, $subject, $plainBody);
            //}
		}
	}
	
	function sendEmailNotification($member, $subject, $body){

		$from_name = $this->mailSenderName;
		$from_addr = $this->mailSenderAddr;

        $to_name = $member->fullname;
        $to_addr = $member->email;
        $recipients = array($to_name => $to_addr);
				
		// Send mail
		require_once("../htmlMimeMail5/htmlMimeMail5.php");
		$mail = new htmlMimeMail5();
		$mail->setFrom("$from_name <$from_addr>");
		$mail->setReturnPath($from_addr);
		$mail->setSubject($subject);
		$mail->setText($body);
		//$mail->setHTML($htmlBody);
		$mail->setSMTPParams($this->smtpHost,$this->smtpPort,null,true,$this->smtpUser,$this->smtpPass);
		$mail->send($recipients, $type = 'smtp');	
		
	}

	
/*
	function printLastComments(){
		
		$tc = $this->table_comments;
		$td = $this->table_dirs;
		$tf = $this->table_files;
		$output = "<h3>$this->label_lastcomments</h3>
			<div class='lastcomments'>";
		$res = $this->query(
			"SELECT 
				$tc.body as body,
				$tc.parent_id as img,
				$td.directory as dir
			FROM 
				$tc,$td,$tf
			WHERE $tc.page_id = $this->page_id
				AND $tc.parent_id = $tf.id
				AND $tf.directory = $td.id
			ORDER BY $tc.timestamp DESC 
			LIMIT $this->lastcommentscount"
		);
		if ($res->num_rows == 0){
			$output .= $this->label_nocomments;
		}
		while ($row = $res->fetch_assoc()){
			$body = strip_bbcode(stripslashes($row["body"]));
			$body = str_replace("<br />","",$body);
			if (strlen($body) > 40) $body = mb_substr($body,0,37,'UTF-8')."...";
			$output .= '- <a href="'.$this->generateCoolURL($row['dir'].$row['img']).'">'.$body.'</a><br />';
		}
		$output .= "</div>";
		return $output;
	}*/

}
?>