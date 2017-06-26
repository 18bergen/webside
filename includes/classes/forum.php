<?php
class forum extends base {

	var $threads;
	var $posts;
	
	var $threads_per_page = 15;
	var $posts_per_page = 10;

	var $template_dir 					= "../includes/templates/email/";
	var $template_newthread 			= "forum_newthread.txt";
	var $template_threadupdated 		= "forum_threadupdated.txt";
	
	var $table_posts = "forum_posts";
	var $table_threads = "forum_threads";
	var $table_unread = "forum_unread";
	var $table_members = "members";
	var $table_useroptions;
	
	var $mail_newthreads = false;
	var $mail_onreply = false;
	
	var $table_threads_field_id = "id";
	var $table_threads_field_page = "page";
	var $table_threads_field_timestamp = "timestamp";
	var $table_threads_field_caption = "caption";
	var $table_threads_field_sticky = "sticky";

	var $table_posts_field_id = "id";
	var $table_posts_field_thread = "thread";
	var $table_posts_field_timestamp = "timestamp";
	var $table_posts_field_author = "author";
	var $table_posts_field_subject = "subject";
	var $table_posts_field_body = "body";
	var $table_posts_field_editbymod = "editbymod";

	var $table_unread_field_id = "id";
	var $table_unread_field_user = "bruker";
	var $table_unread_field_thread = "traad";
	var $table_unread_field_post = "innlegg";

	var $memberlookup_function;
	var $increasememberpostcount_function;
	var $getmemberidarray_function;

	var $allow_read = false;
	var $allow_modifyownentries = false;
	var $allow_modifyothersentries = false;
	var $allow_reply = false;
	var $allow_startthreads = false;
	var $allow_editthreadsettings = false;

	var $str_errorintro = "Det oppstod en eller flere feil:";
	var $errorMessages = array(
		'empty_subject'	=> "Du fylte ikke inn noe emne",
		'empty_body'	=> "Du fylte ikke inn noen tekst"
	);
	
	var $threads_start_template = "
		<table width='100%' cellpadding='2' cellspacing='0' class='forum'>
		 	<tr>
		 		<th style='width:18px;'>&nbsp;</th>
		 		<th>Emne</th>
		 		<th style='text-align:center; width:60px;'>Innlegg</th>
		 		<th style='text-align:right; width:80px;'>Siste</th>
		 	</tr>
	";
	var $threads_row_template = '
		<tr class="%trclass%" onclick=\'location="%link%"\'>
			<td class="td1">
				<a class="%aclass%" href="%link%">%sticky%</a>
			</td>
			<td class="td2">
				<a class="%aclass%" href="%link%">%title%</a>
			</td>
			<td class="td3" align="center">
				%postcounttemplate%%unreadpoststemplate%
			</td>
			<td class="td4" align="right">
				%lastposted%
			</td>
		</tr>
	';
	var $threads_end_template = "</table>";

	var $thread_start_template = "<table class='forumposts' cellpadding='2' cellspacing='0' width='100%'>";
	var $thread_row_template = "
		<tr class='%trclass%'>
			<td valign='top' class='%tdleftclass%'>
				%memberinfo%
				%timestampstr%
			</td>
			<td valign='top' class='%tdrightclass%'>
				<div class='%subjectclass%'>%subject%</div>
				%body%
				<p class='smallright'>
					%quotelinktemplate%
					%editlinktemplate%
					%deletelinktemplate%
				</p>
				%editbymodstr%
			</td>
		</tr>
	";
	var $thread_end_template = "</table>";

	var $postcount_template = "%postcount%";
	var $unreadposts_template = " (%unreadposts% nye)";
	
	var $quotelink_template = '<a href="%quotelink%">[Sitér]</a>';
	var $editlink_allowed_template = '<a href="%editlink%">[Endre]</a>';
	var $deletelink_allowed_template = '<a href="%deletelink%">[Slett]</a>';

	var $replyform_template = '
		<h2><a name="respond">Svar på denne tråden</a></h2>
		%errors%
		<div class="whiteInfoBox">
			<form method="post" action="%posturl%" id="forum_reply_form">
				<table width="100%" class="forum_reply" cellpadding="0" cellspacing="10"><tr><td>
					<input type="hidden" name="thread" value="%thread_id%" />
					Emne: <input type="text" name="subject" id="subject" value="%subject%" /><br />
					Innlegg:<br />
					<textarea name="body" id="body">%body%</textarea><br />
					<table width="100%">
						<tr><td>
							<input type="submit" id="submitbtn" value="  Svar  " />
						</td><td align="right" class="smalltext">
							<a href="/hjelp/bbcode/" target="_blank">Hjelp</a> | 
							<a href="#" onclick="toggleSmileys(); return false;">Vis smileys</a>
						</td></tr>
					</table>
				</td></tr></table>
				<div id="kosmotarSmileys" style="display:none;">
					%smileysTable%
				</div>
			</form>
		</div>
		<script type="text/javascript">
		//<![CDATA[
			
			$(document).ready(function() {
				Nifty("div.whiteInfoBox");
			});
			
			function toggleSmileys() {
				if ($("#kosmotarSmileys").is(":visible")) {
					$("#kosmotarSmileys").hide();
				} else {
					$("#kosmotarSmileys").show();
				}
			}

			function insertCommentSmiley(addSmilie) {
				$("#body").val($("#body").val() + " " + addSmilie); 
				$("#body").focus();
				return false;
			}
			
		//]]>
		</script>
	';

	var $newthreadform_template = '
		<h2>Start ny diskusjon</h2>
		%errors%
		<div class="whiteInfoBox">
			<form method="post" action="%posturl%" id="forum_reply_form">
				<table width="100%" class="forum_reply" cellpadding="0" cellspacing="10"><tr><td>
					<input type="hidden" name="thread" value="_new" />
					Emne: <input type="text" name="subject" id="subject" value="%subject%" /><br />
					Innlegg:<br />
					<textarea name="body" id="body">%body%</textarea><br />
					<table width="100%">
						<tr><td>
						<input type="checkbox" name="sticky" id="sticky" /> <label for="sticky">Sticky</label><br /><br />
							<input type="submit" id="submitbtn" value="  Lagre  " />
						</td><td align="right" class="smalltext" valign="top">
							Formatere teksten eller sette inn bilder / linker..?
							<a href="/hjelp/bbcode/">Les formateringshjelpen</a>
						</td></tr>
					</table>
				</td></tr></table>
				<p>
				<b>Tips:</b> Krysser du av for "Sticky", vil innlegget holde seg på toppen av forumlisten og få et lite utropstegn-ikon ved siden av seg. Brukes normalt
				om viktige tråder som alle bør lese.
				</p>
			</form>
		</div>
		<script type="text/javascript">
		//<![CDATA[
			$(document).ready(function() {
				Nifty("div.whiteInfoBox");
			});
		//]]>
		</script>
	';

	var $editpostform_template = '
		<div class="whiteInfoBox">
			<form method="post" action="%posturl%" id="forum_reply_form">
				<table width="100%" class="forum_reply" cellpadding="0" cellspacing="10"><tr><td>
					<div class="reply_header">Rediger innlegg:</div>
					<input type="hidden" name="post" value="%id%" />
					Emne: <input type="text" name="subject" id="subject" value="%subject%" /><br />
					Innlegg:<br />
					<textarea name="body" id="body">%body%</textarea><br />
					<table width="100%">
						<tr><td>
							<input type="submit" id="submitbtn" value="  Lagre  " />
						</td><td align="right" class="smalltext">
							Formatere teksten eller sette inn bilder / linker..?
							<a href="/hjelp/bbcode/">Les formateringshjelpen</a>
						</td></tr>
					</table>
				</td></tr></table>
		</form>
		</div>
		<script type="text/javascript">
		//<![CDATA[
			$(document).ready(function() {
				Nifty("div.whiteInfoBox");
			});
		//]]>
		</script>
	';

	var $deletepostform_template = '
		<h2>Bekreft</h2>
		<p>
			Er du sikkert på at du vil slette innlegget "%subject%"?
		</p>
		<form method="post" action="%posturl%">
			<input type="hidden" name="delete" value="%id%" />
			<input type="submit" value="     Ja      " /> 
			<input type="button" value="     Nei      " onclick=\'window.location="%referer%"\' />
		</form>
	';

	var $pagexofy_template = "Side %x% av %y%";

	var $months = array("januar","februar","mars","april","mai","juni","juli","august","september","oktober","november","desember");
	var $smonths = array("Jan","Feb","Mars","Apr","Mai","Juni","Juli","Aug","Sept","Okt","Nov","Des");

	var $str_editbymod_true = "<p align='right' class='smallright' style='color:#888888'>[ Denne posten har blitt redigert av forum admin ]</p>";
	var $str_editbymod_false = "";

	var $str_datetimeprefix = "<div class='smalltext'>";
	var $str_datetimesuffix = "</div>";


	var $str_newentry = "Start ny diskusjon";
	var $str_markallread = "Merk alle som lest";
	var $str_backtolist = "Tilbake til forumlisten";
	var $str_settings = "Innstillinger";
	var $str_day = "dag";
	var $str_days = "dager";
	var $str_hour = "time";
	var $str_hours = "timer";

	var $label_newer = "&lt;&lt; Nyere diskusjoner";
	var $label_older = "Eldre diskusjoner &gt;&gt;";
	var $label_newermsg = "Nyere meldinger  &gt;&gt;";
	var $label_oldermsg = "&lt;&lt; Eldre meldinger";


	var $page_no;
	var $thread_count;
						
	
	/* Constructor */
	function forum(){
		$this->table_posts = DBPREFIX.$this->table_posts;
		$this->table_threads = DBPREFIX.$this->table_threads;
		$this->table_unread = DBPREFIX.$this->table_unread;
		$this->table_members = DBPREFIX.$this->table_members;

		if ((isset($_GET['forum_page'])) && (is_numeric($_GET['forum_page']))){
			$this->page_no = intval($_GET['forum_page']); 
		} else { 
			$this->page_no = "default"; 
		}
	}
	
	function initialize() {

		@parent::initialize();

		array_push($this->getvars,"errs","forum_edit","forum_delete","forum_page","forum_thread",
			"forum_quote","savethreadsettings","markallread","forum_savesettings","forumsettings");
				
	}

	function run(){

		$this->initialize();		
		
		if (isset($_POST['thread'])){
			return $this->saveNewPost();
			return;
		} else if (isset($_POST['post'])){
			return $this->updatePost();
			return;
		} else if (isset($_POST['delete'])){
			return $this->deletePost($_POST['delete']);
			return;
 		} else if (isset($_GET['savethreadsettings'])){
			return $this->saveThreadSettings($_GET['savethreadsettings']);
			return;
		} else if (isset($_GET['markallread'])){
			return $this->markAllRead();
			return;
		}

		//print "<h2>$this->page_header</h2>";

		if (isset($_GET['forum_edit'])){
			return $this->editPostForm($_GET['forum_edit']);
		} else if (isset($_GET['forum_delete'])){
			return $this->deletePostForm($_GET['forum_delete']);
		} else if (isset($_GET['forum_thread'])){
			if ($_GET['forum_thread'] == "_new"){
				return $this->newThreadForm();
			} else {
				return $this->outputThread($_GET['forum_thread']);
			}
		} else if (isset($_GET['forum_savesettings'])){
			return $this->saveSettings();
		} else if (isset($_GET['forumsettings'])){
			return $this->outputSettings();
		} else {
			return $this->outputThreads();
		}
	}

	function timeSince($ts){
		$diff = time()-$ts;
		$oneday = 3600*24;
		if ($diff > $oneday){
			$days = floor($diff/$oneday);
			if ($days == 1){
				$formattedstr = "1 $this->str_day";
			} else {
				$formattedstr = "$days $this->str_days";
			}
		} else {
			$hours = floor($diff/3600);
			if ($hours == 0){
				$formattedstr = "< 1 $this->str_hour";
			} else if ($hours == 1){
				$formattedstr = "1 $this->str_hour";
			} else {
				$formattedstr = "$hours $this->str_hours";
			}
		}
		return $formattedstr;
	}

	function formatDateTime($ts){
		if (date("d.m.Y",$ts) == date("d.m.Y",time())){
			return $this->str_datetimeprefix."Skrevet i dag".$this->str_datetimesuffix;
		} else if (date("d.m.Y",$ts) == date("d.m.Y",time()-86400)){
			return $this->str_datetimeprefix."Skrevet i går".$this->str_datetimesuffix;
		} else if (date("Y",$ts) == date("Y",time())){
			return $this->str_datetimeprefix."Skrevet ".date("d.",$ts)." ".strtolower($this->smonths[date("n",$ts)-1]).$this->str_datetimesuffix;
		} else {
			return $this->str_datetimeprefix."Skrevet ".date("d.",$ts)." ".strtolower($this->smonths[date("n",$ts)-1])." ".date("Y",$ts).$this->str_datetimesuffix;
		}
	}
	
	function outputForumList(){
		if (empty($this->login_identifier)) return $this->permissionDenied();
		$output = "";

		$tablename = $this->table_prefix.$this->table_list_suffix;
		$res = $this->query("SELECT * FROM $tablename");
		$output .= "<ul>";
		while ($row = $res->fetch_assoc()){ 
			$table = stripslashes($row['tablename']);
			$tablename = $this->table_prefix.$table.$this->table_unread_suffix;
			$res2 = $this->query("SELECT COUNT(id) FROM $tablename WHERE bruker='$this->login_identifier'");
			$rowcnt = $res2->fetch_array();
			$nye = $rowcnt[0];
		
			$tablename = $this->table_prefix.$table.$this->table_posts_suffix;
			$res2 = $this->query("SELECT COUNT(id) FROM $tablename");
			$rowcnt = $res2->fetch_array();
			$totalt = $rowcnt[0];
			
			// Callbackfunction
			if (show_forum($table,$this->login_identifier)){
				$output .= "
					<li>
						<a href='".$this->generateCoolURL("/$table/")."'><strong>".stripslashes($row['name'])."</strong></a> 
							(<i>$totalt innlegg, $nye ulest".(($nye == 1) ? "":"e")."</i>)<br />
						".stripslashes($row['description'])."
						<br />&nbsp;
					</li>
				";
			}
		}
		$output .= "
			<li>
				<a href='".$this->generateCoolURL("/innstillinger/")."'><strong>Innstillinger</strong></a> 
					<br />
				Endre dine innstillinger for forumet.
				<br />&nbsp;
			</li>
		";
		$output .= "</ul>";
		
		return $output;
	
	}
	
	function outputSettings(){
		
		$user_id = $this->login_identifier;
		if (empty($user_id)) return $this->permissionDenied();
		$output = "";		
		$mail_newthreads_checked = $this->mail_newthreads ? "checked='checked' " : "";
		$mail_onreply_checked = $this->mail_onreply ? "checked='checked' " : "";
		$post_uri = $this->generateURL(array("noprint=true","forum_savesettings"));
		$output .= '
			<h2>Mine innstillinger</h2>
			<form method="post" action="'.$post_uri.'">
				<p>
					Mitt forumbilde:<br />
					<img src="'.call_user_func($this->lookup_forumimage,$this->login_identifier).'" style="border: 1px solid #000000; margin:2px;"/><br />
					<a href="/medlemsliste/medlemmer/'.$this->login_identifier.'/?editprofile">Endre</a><br /><br />
				</p>
				<p>
					<label for="mail_newthreads">
						<input type="checkbox" name="mail_newthreads" id="mail_newthreads" '.$mail_newthreads_checked.'/>
						Motta e-post når det opprettes nye diskusjoner.
					</label>
				</p>
				<p>
					Du vil kun motta en e-post per diskusjon inntil du leser diskusjonen. 
					Det vil si at hvis det kommer 120 innlegg i en diskusjon vil du kun motta én e-post. 
					Kommer det derimot nye innlegg i to forskjellige diskusjoner vil du motta to e-poster.
				</p>
				<p>
					<label for="mail_onreply">
						<input type="checkbox" name="mail_onreply" id="mail_onreply" '.$mail_onreply_checked.'/>
						Motta e-post når noen svarer på diskusjoner jeg deltar i.
					</label>
				</p>
				<p>
					At du <i>deltar</i> i diskusjonen vil si at du har skrevet et innlegg i den.
				</p>
				<p>
					<input type="submit" value="Lagre innstillinger" />
				</p>
			</form>
		';
		
		call_user_func(
			$this->add_to_breadcrumb, 
			'<a href="'.$this->generateURL("forumsettings").'"><strong>Innstillinger</strong></a>'
		);
		
		return $output;
		
	}
	
	function saveSettings() {
		$page_id = $this->page_id;
		$user_id = $this->login_identifier;
		if (empty($user_id)) return $this->permissionDenied();
		
		// Save settings
		$opts = array('mail_newthreads','mail_onreply'); 
		foreach ($opts as $opt_name) {
			$value = (isset($_POST[$opt_name]) && ($_POST[$opt_name] == 'on')) ? 1 : 0;
			$res = $this->query("SELECT id FROM $this->table_useroptions 
				WHERE page='$page_id' AND user='$user_id' AND name='$opt_name'");
			if ($res->num_rows == 1) {
				$row = $res->fetch_assoc();
				$id = $row['id'];
				$this->query("UPDATE $this->table_useroptions SET value='$value' WHERE id='$id'");
			} else if ($res->num_rows == 0) {
				$this->query("INSERT INTO $this->table_useroptions (page,user,name,value)
					VALUES ('$page_id','$user_id','$opt_name','$value')");
			} else {
				$this->fatalError("Kunne ikke lagre innstillingen $opt_name fordi duplikat eksisterer for bruker $user_id på siden $page_id.");
			}
		}
 		 		
 		// Redirect
		$this->redirect($this->generateCoolURL("/"),'Innstillingene ble lagret');
	}
		
	function makeMemberInfo($id) {
		$member = call_user_func($this->lookup_member, $id);
		$rs = $this->query("SELECT count(id) FROM $this->table_posts WHERE author='$member->ident'");
		$forumpost_count = $rs->fetch_row(); $forumpost_count = $forumpost_count[0];

		$author = empty($member->nickname) ? $member->firstname : $member->nickname;
		$pstr = "";
		foreach ($member->memberof as $mid){
			$g = call_user_func($this->lookup_group,$mid);
			if ($g->kategori == "SP" || $g->kategori == "SM") $pstr = " i ".$g->caption;
		}
		return '
			<div class="smalltext">
				<strong>'.call_user_func($this->make_memberlink, $member->ident, $author).'</strong><br />
				'.$member->tittel.$pstr.'
				<img src="'.call_user_func($this->lookup_forumimage,$member->ident).'" alt="'.$member->firstname.'" style="width:100px; height: 100px; display: block; padding: 0px 15px 0px 15px;" />
				'.$forumpost_count.' innlegg
			</div>
		';
	}
	
	function outputThreads(){

		if (!$this->allow_read) return $this->permissionDenied();
		$output = "";

		if ($this->page_no == "default") $this->page_no = 1;

		if (($this->threads_per_page == "") || ($this->threads_per_page == "0") || !is_numeric($this->threads_per_page)){
			$this->notSoFatalError("invalid threads_per_page value! resets to default");
			$this->threads_per_page = 15;
		}
		
		$output .= "<p id='forum_newentry'>";
		if ($this->allow_startthreads) 
			$output .= '<a href="'.$this->generateURL("forum_thread=_new").'" class="icn" style="background-image:url(/images/icns/comment_add.png);">'.$this->str_newentry.'</a> ';
		$output .= '<a href="'.$this->generateURL(array("noprint=true","markallread=true")).'" class="icn" style="background-image:url(/images/icns/tick.png);">'.$this->str_markallread.'</a>
			<a href="'.$this->generateURL("forumsettings").'" class="icn" style="background-image:url(/images/icns/cog.png);">'.$this->str_settings.'</a>
			</p>
		';
		
		// Shorten the variables a bit...
		$tt = $this->table_threads;
		$tp = $this->table_posts;
		$tu = $this->table_unread;
		
		$res = $this->query("SELECT COUNT(*) FROM $tt WHERE $this->table_threads_field_page=".$this->page_id);
		$count = $res->fetch_array(); 
		$this->thread_count = $count[0];
		$total_pages = ceil($this->thread_count/$this->threads_per_page);
		
		if ($this->page_no > $total_pages){
			$this->page_no = $total_pages;
		}
		if ($this->page_no < 1){
			$this->page_no = 1;
		}

		
		if ($this->thread_count == 0){
			$output .= "<p><i>Ingen innlegg er lagt inn</i></p>";
			$this->page_no = 0;
		}
		
		if ($this->page_no != 0){
			$res = $this->query(
				"SELECT 
					count($tu.$this->table_unread_field_id) as unreadposts, 
					$tt.$this->table_threads_field_id as id,
					$tt.$this->table_threads_field_timestamp as timestamp,
					$tt.$this->table_threads_field_caption as caption,
					$tt.$this->table_threads_field_sticky as sticky,
					count($tp.$this->table_posts_field_id) as postcount
				FROM 
					$tt,
					$tp
				LEFT JOIN $tu 
					ON 
						$tp.$this->table_posts_field_id=$tu.$this->table_unread_field_post
					AND 
						$tu.$this->table_unread_field_user=$this->login_identifier
				WHERE 
					$tt.$this->table_threads_field_id=$tp.$this->table_posts_field_thread
					AND
					$tt.$this->table_threads_field_page=$this->page_id
				GROUP BY 
					$tt.$this->table_threads_field_id
				ORDER BY 
					$tt.$this->table_threads_field_sticky DESC, 
					$tt.$this->table_threads_field_timestamp DESC 
				LIMIT 
					".(($this->page_no-1)*$this->threads_per_page).",$this->threads_per_page"
			);
		
			$output .= $this->threads_start_template;
			$classNo = 1;
			while ($row = $res->fetch_assoc()){
				$classNo = !$classNo;
				$new = ($row['unreadposts'] > 0);
				$lastPost = $this->timeSince($row['timestamp']);
				$r1a = array(); 					$r2a = array();
				$r1a[] = "%id%";					$r2a[] = $row['id'];
				$r1a[] = "%title%";					$r2a[] = stripslashes($row['caption']);
				$r1a[] = "%lastposted%";			$r2a[] = $lastPost;
				$r1a[] = "%tableclass%";			$r2a[] = "forum";
				$r1a[] = "%trclass%";				$r2a[] = $new ? "forum3" : "forum".($classNo+1);
				$r1a[] = "%tdclass%";				$r2a[] = $new ? "forum3" : "forum".($classNo+1);
				$r1a[] = "%aclass%";				$r2a[] = $new ? "forum3" : "forum".($classNo+1);
				$r1a[] = "%postcount%";				$r2a[] = ($row['postcount']);
				$r1a[] = "%unreadposts%";			$r2a[] = $row['unreadposts'];
				$r1a[] = "%sticky%";				$r2a[] = (($row['sticky'] == 1) ? 
					'<img src="'.$this->image_dir.'icns/star.png" alt="Sticky" width="16" height="16" style="border: none; float:left; margin-right: 3px;" /> ' 
					: (($row['unreadposts'] > 0) ? 
						'<img src="'.$this->image_dir.'icns/new.png" alt="Normal" width="16" height="16" style="border: none; float:left; margin-right: 3px;" />' 
						: '<img src="'.$this->image_dir.'icns/comments.png" alt="Diskusjon" style="width:16px; height:16px; border: none; float:left; margin-right: 3px;" />'
					));
				$r1a[] = "%link%"; 					$r2a[] = str_replace($r1a, $r2a, $this->generateURL("forum_thread=%id%"));
				$r1a[] = "%postcounttemplate%"; 	$r2a[] = str_replace($r1a, $r2a, $this->postcount_template);
				$r1a[] = "%unreadpoststemplate%";	$r2a[] = ($row['unreadposts'] > 0) ? str_replace($r1a, $r2a, $this->unreadposts_template) : "";
				$output .= str_replace($r1a, $r2a, $this->threads_row_template);
			}
			$output .= $this->threads_end_template;
		}
		$cp = $this->page_no;
		$tp = ceil($this->thread_count/$this->threads_per_page);
		$xofy = str_replace(array("%x%","%y%"),Array($cp,$tp),$this->pagexofy_template);
		$lp = ($cp <= 1)   ? $this->label_newer : '<a href="'.$this->generateURL("forum_page=".($cp-1)).'">'.$this->label_newer.'</a>';
		$np = ($cp == $tp) ? $this->label_older   : '<a href="'.$this->generateURL("forum_page=".($cp+1)).'">'.$this->label_older.'</a>';
		$output .= '<table width="100%"><tr><td>'.$lp.'</td><td><p align="center">'.$xofy.'</p></td><td><p align="right">'.$np.'</p></td></tr></table>';
		
		return $output;
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
	
	function outputThread($thread_no){

		if (!$this->allow_read) return $this->permissionDenied();
		$output = "";

		// Validate input
		if (!is_numeric($thread_no)){ $this->fatalError("incorrect input"); }
		
		// Short vars
		$tt = $this->table_threads;
		$tp = $this->table_posts;
		$tu = $this->table_unread;

		// Finn sideinfo
		$res = $this->query(
			"SELECT COUNT(*) FROM $tp WHERE $this->table_posts_field_thread='$thread_no'"
		);
		$row = $res->fetch_array();
		$post_count = $row[0];
		$total_pages = ceil($post_count/$this->posts_per_page);
		
		if ($this->page_no == "default"){
			$res = $this->query(
				"SELECT $this->table_unread_field_post 
				FROM $tu 
				WHERE $this->table_unread_field_thread='$thread_no' 
				AND $this->table_unread_field_user='$this->login_identifier' 
				ORDER BY $this->table_unread_field_id LIMIT 1"
			);
			if ($res->num_rows == 1){
				$row = $res->fetch_array();
				$first_unread = $row[0];
				$res = $this->query(
					"SELECT COUNT($this->table_posts_field_id) FROM $tp 
					WHERE $this->table_posts_field_thread='$thread_no'
					AND $this->table_posts_field_id <= $first_unread"
				);
				$row = $res->fetch_array();
				$read_count = $row[0];
				$this->page_no = ceil($read_count/$this->posts_per_page);
			} else {
				$this->page_no = $total_pages;
			}
		} else {
			if ($this->page_no > $total_pages){
				$this->page_no = $total_pages;
			}
		}

		// Hent databasedata
		
		$res = $this->query(
			"SELECT 
				$tp.$this->table_posts_field_id as id,
				$tp.$this->table_posts_field_subject as subject,
				$tp.$this->table_posts_field_author as author,
				$tp.$this->table_posts_field_timestamp as timestamp,
				$tp.$this->table_posts_field_body as body,
				$tp.$this->table_posts_field_editbymod as editbymod,
				$tu.$this->table_unread_field_post as unread
			FROM 
				$tp 
			LEFT JOIN $tu ON 
				$tp.$this->table_posts_field_id=$tu.$this->table_unread_field_post
				AND 
				$tu.$this->table_unread_field_user=$this->login_identifier
			WHERE 
				$tp.$this->table_posts_field_thread='$thread_no'
			ORDER BY 
				$tp.$this->table_posts_field_timestamp
			LIMIT 
				".(($this->page_no-1)*$this->posts_per_page).",$this->posts_per_page"
		);
		if ($res->num_rows == 0){
			$this->fatalError("The thread or page doesn't exist!");
		} 
		$output .= '<table width="100%"><tr><td><a href="'.$this->generateURL("").'">Tilbake til oversikten</a></td><td align="right">Gå til side: ';
		for ($i = 1; $i <= $total_pages; $i++){
			$output .= ($i == $this->page_no) ? " $i" : ' <a href="'.$this->generateURL(array("forum_thread=$thread_no","forum_page=$i")).'">'.$i.'</a>';
			if ($i < $total_pages) $output .= ', ';
		}
		$output .= '</td></tr></table>
		';
		$output .= $this->thread_start_template;
		$classNo = 1;
		$idsprinted = array();
		while ($row = $res->fetch_assoc()){
			$classNo = !$classNo;
			$unread = ($row['unread'] == $row['id']);	
			
			
			$editdelete_allowed = ($this->allow_modifyothersentries || ($this->allow_modifyownentries && $row['author'] == $this->login_identifier));
			$r1a = array(); 					$r2a = array();
			$r1a[] = "%id%";					$r2a[]  = $row['id'];
			$r1a[] = "%subject%";				$r2a[]  = stripslashes($row['subject']);
			$r1a[] = "%memberinfo%";			$r2a[]  = $this->makeMemberInfo($row['author']);
			$r1a[] = "%body%";					$r2a[]  = parse_bbcode(parse_emoticons(stripslashes($row['body'])));
			$r1a[] = "%timestampstr%";			$r2a[]  = $this->formatDateTime($row['timestamp']);
			$r1a[] = "%subjectclass%";			$r2a[]  = $unread ? "forum_subject_unread" : "forum_subject";
			$r1a[] = "%tableclass%";			$r2a[]  = "forumpost".($classNo+1);
			$r1a[] = "%trclass%";				$r2a[]  = "forumpost".($classNo+1);
			$r1a[] = "%tdleftclass%";			$r2a[]  = "forumpost".($classNo+1)."_left";
			$r1a[] = "%tdrightclass%";			$r2a[]  = "forumpost".($classNo+1)."_right";
			$r1a[] = "%editbymodstr%";			$r2a[] = $row['editbymod'] ? $this->str_editbymod_true : $this->str_editbymod_false;
			$r1a[] = "%editlink%";				$r2a[] = $this->generateURL("forum_edit=".$row['id']);
			$r1a[] = "%deletelink%";			$r2a[] = $this->generateURL("forum_delete=".$row['id']);
			$r1a[] = "%quotelink%";				$r2a[] = $this->generateURL(array("forum_thread=$thread_no","forum_page=$this->page_no","forum_quote=".$row['id'])).'#respond';
			$r1a[] = "%editlinktemplate%";		$r2a[] = ($editdelete_allowed ? str_replace($r1a, $r2a, $this->editlink_allowed_template) : "");
			$r1a[] = "%deletelinktemplate%";	$r2a[] = ($editdelete_allowed ? str_replace($r1a, $r2a, $this->deletelink_allowed_template) : "");
			$r1a[] = "%quotelinktemplate%";		$r2a[] = str_replace($r1a, $r2a, $this->quotelink_template);
			$output .= str_replace($r1a, $r2a, $this->thread_row_template);
			$subj = stripslashes($row['subject']);
			array_push($idsprinted,$row['id']);
		}
		$output .= $this->thread_end_template;
		
		$xofy = str_replace(array("%x%","%y%"),Array($this->page_no,$total_pages),$this->pagexofy_template);
		$lp = ($this->page_no == 1)   ? $this->label_oldermsg : "<a href='".$this->generateURL(array("forum_thread=$thread_no","forum_page=".($this->page_no-1)))."'>$this->label_oldermsg</a>";
		$np = ($this->page_no == $total_pages) ? $this->label_newermsg   : "<a href='".$this->generateURL(array("forum_thread=$thread_no","forum_page=".($this->page_no+1)))."'>$this->label_newermsg</a>";
		$output .= '<table width="100%"><tr><td>'.$lp.'</td><td><p align="center">'.$xofy.'</p></td><td><p align="right">'.$np.'</p></td></tr></table>
		';
		
		if (isset($_GET['errs'])){
			$errs = explode("|",$_GET['errs']);
			$errstr = $this->str_errorintro."<ul>";
			foreach ($errs as $s){
				$errstr.= "<li>".$this->errorMessages[$s]."</li>";
			}
			$errstr .= "</ul>";
			$output .= $this->notSoFatalError($errstr);
		}

		if (substr($subj,0,3) != "Re:") $subj = "Re: $subj";

		$body = '';
		$subject = $subj;
		
		if (isset($_GET['forum_quote'])){
			if (!is_numeric($_GET['forum_quote'])){
				return $this->notSoFatalError("quote id not int!");
			}
			$quoteid = $_GET['forum_quote'];
			$res = $this->query(
				"SELECT 
					$this->table_posts_field_id as id,
					$this->table_posts_field_subject as subject,
					$this->table_posts_field_author as author,
					$this->table_posts_field_timestamp as timestamp,
					$this->table_posts_field_body as body
				FROM 
					$tp 
				WHERE 
					$this->table_posts_field_id='$quoteid'
					AND $this->table_posts_field_thread='$thread_no'"
			);
			if ($res->num_rows == 0){
				return $this->notSoFatalError("Innlegget du ville sitere fantes ikke eller det befant seg i en annen tråd!");
			}
			$row = $res->fetch_assoc();
			$body = stripslashes($row['body']);
			//$body = str_replace("<br />\r\n","\r\n",$body);
			//$body = str_replace("<br />\n","\n",$body);
			$body = "[quote=".$row['author']."]\n$body\n[/quote]";
		}
		
		// Reply form:

		$errstr = '';
		if (isset($_SESSION['errors'])){
			$errstr = $this->str_errorintro."<ul>";
			foreach ($_SESSION['errors'] as $s){
				$errstr.= "<li>".$this->errorMessages[$s]."</li>";
			}
			$errstr .= "</ul>";
			$errstr = $this->notSoFatalError($errstr, array('logError' => false));
			
			if (isset($_SESSION['postdata']['subject']))
				$subject = $_SESSION['postdata']['subject'];
			if (isset($_SESSION['postdata']['body']))
				$body = $_SESSION['postdata']['body'];
			
			unset($_SESSION['errors']);
			unset($_SESSION['postdata']);			
		}
		
		if ($this->allow_reply){
			$r1a = array(); $r2a = array();
			$r1a[]  = "%thread_id%";		$r2a[]  = $thread_no;
			$r1a[]  = "%subject%";			$r2a[]  = $subject;
			$r1a[]  = "%posturl%";			$r2a[]  = $this->generateURL("noprint=true");
			$r1a[]  = "%body%";				$r2a[]  = $body;
			$r1a[] = "%smileysTable%";		$r2a[] = $this->makeSmileysTable("insertCommentSmiley");
			$r1a[]  = "%errors%";			$r2a[]  = $errstr;

			$output .= str_replace($r1a, $r2a, $this->replyform_template);
		}
		
		
		// Thread settings
		if ($this->allow_editthreadsettings){
			$res = $this->query(
				"SELECT 
					$this->table_threads_field_sticky as sticky
				FROM 
					$tt
				WHERE 
					$this->table_threads_field_id='$thread_no'"
			);
			$row = $res->fetch_assoc();
			$sticky = stripslashes($row['sticky']);
			$output .= '
				<div>&nbsp;</div>
				<h2>Trådvalg</h2>
				<div class="whiteInfoBox">
				<form method="post" action="'.$this->generateURL(array("noprint=true","savethreadsettings=$thread_no","forum_page=$this->page_no")).'" id="forum_sticky_form" style="padding:15px;">
					<input type="checkbox" name="sticky" id="sticky" '.($sticky ? 'checked="checked"' : '').' /><label for="sticky">Tråden er sticky</label>
					<br /><br />
					Advarsel: Hvis du har skrevet et innlegg over som ikke er lagret, vil du miste dette ved å trykke "Lagre trådvalg"!<br />
					<input type="submit" value="Lagre trådvalg" />
				</form>
				</div>
				';
		}
		
		$this->query(
			"DELETE FROM 
				$tu 
			WHERE
				$this->table_unread_field_user='$this->login_identifier' 
				AND (
				$this->table_unread_field_post='".
				implode("' OR $this->table_unread_field_post='",$idsprinted).
				"')"
		);
		
		return $output;
	}

	function newThreadForm(){
		if (!$this->allow_startthreads) return $this->permissionDenied();

		$subject = '';
		$body = '';
		
		$errstr = '';
		if (isset($_SESSION['errors'])){
			$errstr = $this->str_errorintro."<ul>";
			foreach ($_SESSION['errors'] as $s){
				$errstr.= "<li>".$this->errorMessages[$s]."</li>";
			}
			$errstr .= "</ul>";
			$errstr = $this->notSoFatalError($errstr, array('logError' => false));
			
			if (isset($_SESSION['postdata']['subject']))
				$subject = $_SESSION['postdata']['subject'];
			if (isset($_SESSION['postdata']['body']))
				$body = $_SESSION['postdata']['body'];
			
			unset($_SESSION['errors']);
			unset($_SESSION['postdata']);			
		}
		
		$r1a = array(); $r2a = array();
		$r1a[] = "%subject%";			$r2a[] = $subject;
		$r1a[] = "%posturl%";			$r2a[] = $this->generateURL('');
		$r1a[] = "%body%";				$r2a[] = $body;
		$r1a[] = "%errors%";			$r2a[] = $errstr;

		return str_replace($r1a, $r2a, $this->newthreadform_template);
	}

	function editPostForm($post_id){

		// Validate input
		if (!is_numeric($post_id)){ $this->fatalError("incorrect input"); }
		
		if (isset($_GET['errs'])){
			$errs = explode("|",$_GET['errs']);
			$errstr = $this->str_errorintro."<ul>";
			foreach ($errs as $s){
				$errstr.= "<li>".$this->errorMessages[$s]."</li>";
			}
			$errstr .= "</ul>";
			return $this->notSoFatalError($errstr);
		}
		
		// Short vars
		$tt = $this->table_threads;
		$tp = $this->table_posts;
		$tu = $this->table_unread;

		$res = $this->query(
			"SELECT  
				$this->table_posts_field_subject as subject,
				$this->table_posts_field_body as body,
				$this->table_posts_field_author as author
			FROM 
				$tp 
			WHERE 
				$this->table_posts_field_id='$post_id'"
		);
		if ($res->num_rows != 1) $this->fatalError("Post doesn't exist!");
		$row = $res->fetch_assoc();
		$allowed = ($this->allow_modifyothersentries || ($this->allow_modifyownentries && $row['author'] == $this->login_identifier));
		if (!$allowed)  $this->fatalError("Access denied to this operation!");
		$body = stripslashes($row['body']);
		//$body = str_replace("<br />\r\n","\r\n",$body);
		//$body = str_replace("<br />\n","\n",$body);

		$r1a = array();						$r2a = array();
		$r1a[1]  = "%id%";					$r2a[1]  = $post_id;
		$r1a[2]  = "%posturl%";				$r2a[2]  = $this->generateURL("noprint=true");
		$r1a[3]  = "%subject%";				$r2a[3]  = isset($_GET['subject']) ? $_GET['subject'] : stripslashes($row['subject']);
		$r1a[4]  = "%body%";				$r2a[4]  = isset($_GET['body']) ? $_GET['body'] : $body;

		return str_replace($r1a, $r2a, $this->editpostform_template);
	}

	function invalidInput($errors,$querystr1,$querystr2 = 0){
		$errs = implode('|',array_unique($errors));
		$getdata = array();
		foreach ($_POST as $n => $v){
			array_push($getdata,"$n=".urlencode($v));
		}
		array_push($getdata,$querystr1,"errs=$errs");
		if ($querystr2 != 0) array_push($getdata,$querystr2);
		$this->redirect($this->generateURL($getdata));
	}

	function preparePostData(){
		
		// Check for errors in the input
		$errors = array();
		if (empty($_POST['subject'])) array_push($errors,"empty_subject");
		if (empty($_POST['body'])) array_push($errors,"empty_body");
		
		if (count($errors) > 0){
			$_SESSION['errors'] = array_unique($errors);
			$_SESSION['postdata'] = $_POST;
			if (isset($_GET['forum_page'])){
				$error_url = $this->generateURL(array(
					'forum_thread='.$_POST['thread'],
					'forum_page='.$_GET['forum_page']
				)).'#respond';
			} else {
				$error_url = $this->generateURL(array(
					'forum_thread='.$_POST['thread']
				)).'#respond';
			}				
			$this->redirect(
				$error_url,
				'Innlegget ble ikke lagret pga. en eller flere feil.',
				'error'
			);
		}
		
		// Prepare data
		$post_data->subject = addslashes(strip_tags($_POST['subject']));
		$post_data->body = htmlspecialchars($_POST['body']);
		$post_data->body = str_replace("\r\n","\n",$post_data->body);
		$post_data->body = str_replace("\r","\n",$post_data->body);
		$post_data->body = addslashes($post_data->body);
		$post_data->timestamp = time();
		$post_data->author = $this->login_identifier;
		if (isset($_POST['sticky']) && ($_POST['sticky'] == 'on')){
			$post_data->sticky = 1;
		} else {
			$post_data->sticky = 0;
		}

		if (isset($_POST['post'])){
			$post_data->post = $_POST['post'];
			if (!is_numeric($post_data->post)) $this->fatalError("incorrect input (01)");
		}
		if (isset($_POST['thread'])){
			$post_data->thread = $_POST['thread'];
			if ($_POST['thread'] == "_new"){
				$post_data->thread = $this->createNewThread($post_data->timestamp,$post_data->subject,$post_data->sticky);
			} else {
				if (!is_numeric($post_data->thread)) $this->fatalError("incorrect input (02)"); 
				$this->updateThreadTimestamp($post_data->thread,$post_data->timestamp);
			}
		}
		return $post_data;
	}

	function createNewThread($timestamp,$caption,$sticky){
		global $memberdb;
		
		if (!$this->allow_startthreads) return $this->permissionDenied();

		require_once("../htmlMimeMail5/htmlMimeMail5.php");
		
		// Short vars
		$tt = $this->table_threads;
		$tp = $this->table_posts;
		$tu = $this->table_unread;
		
		$page_id = $this->page_id;
		
		$this->query(
			"INSERT INTO 
				$tt (
					$this->table_threads_field_page,
					$this->table_threads_field_timestamp,
					$this->table_threads_field_caption,
					$this->table_threads_field_sticky
				) 
				VALUES (
					'$page_id',
					'$timestamp',
					'$caption',
					'$sticky'
				)"
		);
		$id = $this->insert_id();
		$url = "https://".$_SERVER['SERVER_NAME'].$this->generateURL("forum_thread=$id");

		$this->logDebug("[forum] New thread: \"".addslashes($caption)."\"");
		
		// Fetch the setting 'mail_newthreads' for all users:
		$user_settings = call_user_func($this->get_useroptions, $this, 'mail_newthreads');
		
		// Traverse the user-array:
		foreach ($user_settings as $ident => $mail_newthreads) {
			
			// Check if the user with id $ident wants notifications - and that the user is not 
			// the author of the new topic:
			if (($mail_newthreads) && ($ident != $this->login_identifier)) { 
				$m = call_user_func($this->lookup_member, $ident);
				if (call_user_func($this->is_allowed,"r",$this->page_id,$ident)) {
					$this->logDebug(" --> Sending new thread notification to $m->firstname [$m->email]");
					$this->mailNotificationNewThread($caption, $url, $m);
				}
			}
		}
		return $id;
	}

	function updateThreadTimestamp($thread,$timestamp){
		if (!$this->allow_reply) $this->permissionDenied();
		
		// Short vars
		$tt = $this->table_threads;
		$tp = $this->table_posts;
		$tu = $this->table_unread;
		
		$this->query(
			"UPDATE 
				$tt 
			SET 
				$this->table_threads_field_timestamp='$timestamp'
			WHERE 
				id='$thread'"
		);
	}
	
	function mailNotification($topic, $url, $member){
		
		$from_name = $this->mailSenderName;
		$from_addr = $this->mailSenderAddr;
		
		$to_name = $member->fullname;
		$to_addr = $member->email;
		$recipients = array($to_name => $to_addr);
	
		$server = "https://".$_SERVER['SERVER_NAME'];

		$template = file_get_contents($this->template_dir.$this->template_threadupdated);
		
		$r1a = array(); $r2a = array();
		$r1a[] = '%recipient_name%';	$r2a[] = $member->firstname;
		$r1a[] = '%topic%';				$r2a[] = $topic;
		$r1a[] = '%site_name%';			$r2a[] = $_SERVER['SERVER_NAME'];
		$r1a[] = '%topic_url%';			$r2a[] = $url;
		$r1a[] = '%settings_url%';		$r2a[] = $server.$this->generateURL("forumsettings");
		$plainBody = str_replace($r1a, $r2a, $template);

		// Send mail		
		$mail = new htmlMimeMail5();
		$mail->setFrom("$from_name <$from_addr>");
		$mail->setReturnPath($from_addr);
		$mail->setSubject("[Forum] Varsel om svar på emne - $topic");
		$mail->setText($plainBody);
		//$mail->setHTML($htmlBody);
		$mail->setSMTPParams($this->smtpHost,$this->smtpPort,null,true,$this->smtpUser,$this->smtpPass);
		$mail->send($recipients,$type = 'smtp');		
	}
	
	function mailNotificationNewThread($topic,$url,$member){
		
		$from_name = $this->mailSenderName;
		$from_addr = $this->mailSenderAddr;
		
		$to_name = $member->fullname;
		$to_addr = $member->email;
		$recipients = array($to_name => $to_addr);
		
		$server = "https://".$_SERVER['SERVER_NAME'];
		
		$template = file_get_contents($this->template_dir.$this->template_newthread);
		
		$r1a = array(); $r2a = array();
		$r1a[] = '%recipient_name%';	$r2a[] = $member->firstname;
		$r1a[] = '%topic%';				$r2a[] = $topic;
		$r1a[] = '%site_name%';			$r2a[] = $_SERVER['SERVER_NAME'];
		$r1a[] = '%topic_url%';			$r2a[] = $url;
		$r1a[] = '%settings_url%';		$r2a[] = $server.$this->generateURL("forumsettings");
		$plainBody = str_replace($r1a, $r2a, $template);

		// Send mail		
		$mail = new htmlMimeMail5();
		$mail->setFrom("$from_name <$from_addr>");
		$mail->setReturnPath($from_addr);
		$mail->setSubject("[Forum] Varsel om nytt tema");
		$mail->setText($plainBody);
		//$mail->setHTML($htmlBody);
		$mail->setSMTPParams($this->smtpHost,$this->smtpPort,null,true,$this->smtpUser,$this->smtpPass);
		$mail->send($recipients,$type = 'smtp');		
	}

	function saveNewPost(){
		if (!$this->allow_reply){
			print $this->permissionDenied();
			print "Spytter ut POST-data i tilfelle du vil kopiere det du skrev:<br /><br />";
			print_r($_POST);
			return 0;
		}

		require_once("../htmlMimeMail5/htmlMimeMail5.php");

		// 1) First fetch and prepare the POST data:
		
		// Short vars
		$tt = $this->table_threads;
		$tp = $this->table_posts;
		$tu = $this->table_unread;
		
		$post_data = $this->preparePostData();
		
		$this->logDebug("[forum] New post");
		
		// 2) Check which users have unread posts in this thread before inserting the post 
		//    (else everybody will have unread posts)
		
		$res = $this->query(
			"SELECT 
				$tp.$this->table_posts_field_author AS author,
				$tt.$this->table_threads_field_caption AS thread,
				$tu.$this->table_unread_field_id AS unreadexists
			FROM 
				$tt, $tp
			LEFT JOIN $tu
				ON $tu.$this->table_unread_field_thread = '$post_data->thread'
				AND $tu.$this->table_unread_field_user = $tp.$this->table_posts_field_author
			WHERE 
				$tp.$this->table_posts_field_thread = $tt.$this->table_threads_field_id
				AND $tp.$this->table_posts_field_thread = '$post_data->thread'				
			GROUP BY $tp.$this->table_posts_field_author"
		);

		$members = array();
		$topic = "";
		$url = "https://".$_SERVER['SERVER_NAME'].$this->generateURL("forum_thread=$post_data->thread");
		while ($row = $res->fetch_assoc()){
			$topic = stripslashes($row['thread']);
			if (empty($row['unreadexists'])){
				if ($row['author'] != $this->login_identifier) {
					$members[] = $row['author'];
				}
			}
		}		
		
		// 3) Insert the post before sending mail notifications just to be safe. This also gives
		//    us the post id, which can be handy to have :)

		$this->query(
			"INSERT INTO 
				$tp (
					$this->table_posts_field_thread,
					$this->table_posts_field_timestamp,
					$this->table_posts_field_author,
					$this->table_posts_field_subject,
					$this->table_posts_field_body
				)
				VALUES (
					'$post_data->thread',
					'$post_data->timestamp',
					'$post_data->author',
					'$post_data->subject',
					'$post_data->body'
			)"
		);
		$post_id = $this->insert_id();

		// 4) Send mail notifications
		
		// DEBUG: $members[] = 1;

		if (!empty($members)) {
			$user_settings = call_user_func($this->get_useroptions, $this, 'mail_onreply', $members);
			foreach ($user_settings as $ident => $mail_onreply) {
				if ($mail_onreply) {
					$m = call_user_func($this->lookup_member, $ident);
					if (call_user_func($this->is_allowed,"r",$this->page_id,$ident)) {
						$this->logDebug(" --> Sending thread notification to $m->firstname [$m->email]");
						$this->mailNotification($topic,$url,$m);
					}
				}
			}
		}
		
		// 5) Update unread-table
		
		$allmembers = call_user_func($this->list_members);
		foreach ($allmembers as $member){
			$this->query(
				"INSERT INTO 
					$tu (
						$this->table_unread_field_user,
						$this->table_unread_field_post,
						$this->table_unread_field_thread
					) 
					VALUES (
						'$member->ident',
						'$post_id',
						'$post_data->thread'
					)"
			);
		}
		if (!empty($this->increasememberpostcount_function)){
			$ipf = $this->increasememberpostcount_function;
			$ipf($post_data->author);
		}
		$res = $this->query(
			"SELECT COUNT(*) FROM $tp WHERE $this->table_posts_field_thread='$post_data->thread'"
		);
		$row = $res->fetch_array();
		$postCount = $row[0];
		$totalPages = ceil($postCount / $this->posts_per_page);

		$url = $this->generateURL(array("forum_thread=$post_data->thread","forum_page=$totalPages"));
		$this->redirect($url, "Innlegget ble lagret");
	}

	function updatePost(){
		$post_data = $this->preparePostData();
		
		// Short vars
		$tt = $this->table_threads;
		$tp = $this->table_posts;
		$tu = $this->table_unread;
		
		$res = $this->query(
			"SELECT 
				$this->table_posts_field_thread as thread,
				$this->table_posts_field_author as author 
			FROM 
				$tp 
			WHERE 
				$this->table_posts_field_id='$post_data->post'"
		);
		$row = $res->fetch_assoc();
		$thread = $row['thread'];
		if ($this->allow_modifyownentries && $row['author'] == $this->login_identifier){
			$this->query(
				"UPDATE $tp
				SET $this->table_posts_field_subject='$post_data->subject', 
					$this->table_posts_field_body='$post_data->body',
					$this->table_posts_field_editbymod='0'
				WHERE $this->table_posts_field_id='$post_data->post'"
			);
		} else if ($this->allow_modifyothersentries){
			$this->query(
				"UPDATE $tp
				SET $this->table_posts_field_subject='$post_data->subject', 
					$this->table_posts_field_body='$post_data->body',
					$this->table_posts_field_editbymod='1'
				WHERE $this->table_posts_field_id='$post_data->post'"
			);
		}
		$res = $this->query(
			"SELECT COUNT(*) FROM $tp WHERE $this->table_posts_field_thread='$thread' AND $this->table_posts_field_id <= $post_data->post"
		);
		$row = $res->fetch_array();
		$postCount = $row[0];
		$totalPages = ceil($postCount / $this->posts_per_page);
		$url = $this->generateURL(array("forum_thread=$thread","forum_page=$totalPages"));
		$this->redirect($url, "Innlegget ble lagret");
	}

	function saveThreadSettings($thread){
		if (!$this->allow_editthreadsettings){ $this->permissionDenied(); exit; }
		if (!is_numeric($thread)){ $this->fatalError("incorrect input"); }
		if (!isset($_GET['forum_page'])){ $this->fatalError("incorrect input"); }
		if (!is_numeric($_GET['forum_page'])){ $this->fatalError("incorrect input"); }
		if (isset($_POST['sticky']) && ($_POST['sticky'] == 'on')){
			$sticky = 1;
		} else {
			$sticky = 0;
		}
		
		// Short vars
		$tt = $this->table_threads;
		$tp = $this->table_posts;
		$tu = $this->table_unread;
		
		$this->query(
			"UPDATE $tt
			SET $this->table_threads_field_sticky='$sticky'
			WHERE id='$thread'"
		);
		
		$url = $this->generateURL(array("forum_thread=$thread","forum_page=".$_GET['forum_page']));
		$this->redirect($url, "Innstillinger for tråden ble lagret!");
	}

	function markAllRead(){
	
		// Short vars
		$tt = $this->table_threads;
		$tp = $this->table_posts;
		$tu = $this->table_unread;
		
		$this->query(
			"DELETE FROM 
				$tu 
			WHERE
				$this->table_unread_field_user='$this->login_identifier'"
		);

		$msg = "Alle innlegg ble markert som lest.";
		$url = $this->generateUrl("");
		$this->redirect($url,$msg);
	}

	function deletePostForm($post_no){
		if (!is_numeric($post_no)){ $this->fatalError("incorrect input 011"); }
		
		// Short vars
		$tt = $this->table_threads;
		$tp = $this->table_posts;
		$tu = $this->table_unread;
		
		$res = $this->query(
			"SELECT 
				$this->table_posts_field_author as author,
				$this->table_posts_field_subject as subject
			FROM $tp
			WHERE $this->table_posts_field_id='$post_no'"
		);
		if (!$res->num_rows == 1){
			return $this->notSofatalError("The post doesn't exist!");
		}
		$row = $res->fetch_assoc();
		if (($row['author'] == $this->login_identifier) || ($this->allow_modifyothersentries)){
			$r1a = array();						$r2a = array();
			$r1a[1]  = "%id%";					$r2a[1]  = $post_no;
			$r1a[2]  = "%posturl%";				$r2a[2]  = $this->generateURL("noprint=true");
			$r1a[3]  = "%subject%";				$r2a[3]  = stripslashes($row['subject']);
			$r1a[4]  = "%referer%";				$r2a[4]  = $_SERVER['HTTP_REFERER'];
	
			return str_replace($r1a, $r2a, $this->deletepostform_template);
		} else {
			return $this->permissionDenied();
		}
	}

	function deletePost($post_no){
		if (!is_numeric($post_no)){ $this->fatalError("incorrect input 021"); }
		
		// Short vars
		$tt = $this->table_threads;
		$tp = $this->table_posts;
		$tu = $this->table_unread;
		
		$res = $this->query(
			"SELECT 
				$this->table_posts_field_author as author,
				$this->table_posts_field_subject as subject,
				$this->table_posts_field_thread as thread
			FROM $tp
			WHERE $this->table_posts_field_id='$post_no'"
		);
		if (!$res->num_rows == 1) $this->fatalError("The post doesn't exist!");
		$row = $res->fetch_assoc();
		$thread = $row['thread'];
		if (($row['author'] == $this->login_identifier) || ($this->allow_modifyothersentries)){
			$res = $this->query(
				"SELECT COUNT(*) 
				FROM $tp
				WHERE $this->table_posts_field_thread='$thread'"
			);
			$row = $res->fetch_array();

			$this->query(
				"DELETE FROM 
						$tu
				WHERE
					$this->table_unread_field_post='$post_no'"
			);
			
			if ($row[0] == 1){ // delete entire thread	
				$this->query(
					"DELETE FROM $tp WHERE id='$post_no'"
				);
				$this->query(
					"DELETE FROM $tt WHERE id='$thread'"
				);
				$this->redirect($this->generateURL("",true)); 

			} else { // delete post 
				
				$res = $this->query(
					"SELECT COUNT(*) FROM $tp WHERE $this->table_posts_field_thread='$thread' AND $this->table_posts_field_id <= $post_no"
				);
				$row = $res->fetch_array();
				$postCount = $row[0];
				$totalPages = ceil($postCount / $this->posts_per_page);
				$this->query(
					"DELETE FROM $tp WHERE id='$post_no'"
				);

				$this->redirect($this->generateURL(array("forum_thread=$thread","forum_page=$totalPages"),true));
			}
		} else {
			return $this->permissionDenied();
		}
	}

}

?>
