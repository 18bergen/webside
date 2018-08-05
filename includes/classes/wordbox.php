<?php
class wordbox extends base {

	var $getvars = array("save_shout","wb_page","delete_shout","recover_shout","noprint");
	
	var $allow_delete = false;
	var $allow_post = false;
	var $show_form = false;
	var $identifier = "snikksnakk";
	var $show_delete = false;

	var $messages_per_page = 10;
	var $increasememberpostcount_function;
	var $post_url;
	var $main_url;
	var $table_wordbox = "wordbox";
	var $table_wordbox_field_id = "id";
	var $table_wordbox_field_author = "author";
	var $table_wordbox_field_message = "message";
	var $table_wordbox_field_timestamp = "timestamp";
	var $table_wordbox_field_deleted = "deleted";
	var $table_wordbox_field_ip = "ip";
	var $show_pagenavigation = false;
	var $messagecount;
	var $bannedWords = "";
	var $bannedIPs = "";
	
	var $show_deleted = false;
	
	var $template_header = "<ul class='snikksnakk'>\n";
	var $template_message = "
				<li class='snikksnakk%rowno%' title='Postet: %timestamp%, IP logget'>
					<strong class='wordbox'>%author%:</strong>
					 %message%
					 %deletelink%
				</li>
		";
	var $template_footer = "</ul>\n";
	var $str_pagexofy			= "Side %x% av %y%";		// %x% replaced with current page, %y% with pagecount

	var $label_newer = "Nyere meldinger &gt;&gt;";
	var $label_older = "&lt;&lt; Eldre meldinger";
	
	function __construct() {
		$this->table_wordbox = DBPREFIX.$this->table_wordbox;
	}
	
	function initialize() {
		if (isset($_GET['show_deleted'])) $this->show_deleted = true;
		if (isset($_POST['show_pagenavigation'])) $this->show_pagenavigation = $_POST['show_pagenavigation'];
		if (isset($_POST['messages_per_page'])) $this->messages_per_page = $_POST['messages_per_page'];
		if (isset($_GET['save_shout'])){
			$this->saveNewShout();
		} else if (isset($_GET['delete_shout'])){
			$this->deleteShout($_GET['delete_shout']);
		} else if (isset($_GET['recover_shout'])){
			$this->recoverShout($_GET['recover_shout']);
		}
	}

	
	function run() {

		$output = "";

		$this->initialize_base();
		$this->initialize();		
		
		if ($this->allow_delete) {
			$output .= "<p>Du har tilgang til slette snikksnakkmeldinger. Merk at meldinger blir slettet umiddelbart uten bekreftelse når du trykker slett! 
				Tenk deg om to ganger før du sletter en melding og husk at snikksnakk er et sted der det skal være lov med en litt muntlig tone.</p>";
			$this->show_delete = true;
		}
		
		$output .= $this->printShouts();
		
		if ($this->allow_post && $this->show_form) {
			$output .= $this->shoutForm();
		}
		
		return $output;
	}
	
	function next_year($matches){
 		// as usual: $matches[0] is the complete match
  		// $matches[1] the match for the first subpattern
  		// enclosed in '(...)' and so on
  		return $matches[1].($matches[2]+1);
	}

	function wrapLongWords($str){
		/*return preg_replace_callback(
            "",
            "next_year",
            $str);
            */
            return $str;
	}

	function makeShouts(){

		$res = $this->query("SELECT 
					COUNT(*), MAX($this->table_wordbox_field_id)
			FROM 
				$this->table_wordbox 
			".($this->show_deleted ? "" : "WHERE $this->table_wordbox_field_deleted = 0")
		);
		$row = $res->fetch_row();
		
		$this->messagecount = $row[0];
		$max = $row[1];
		$res->close();
		
		if ($this->show_pagenavigation){
			$startAt = floor(($this->messagecount-1)/$this->messages_per_page)*$this->messages_per_page;
			if ((isset($_GET['wb_page'])) && (is_numeric($_GET['wb_page']))){
				$lastPage = $startAt;
				if ($_GET['wb_page'] < 1) $_GET['wb_page'] = 1;
				if ($_GET['wb_page'] > $lastPage) $_GET['wb_page'] = $lastPage;
				$startAt = ($_GET['wb_page']-1)*($this->messages_per_page);
			}
		} else {
			$startAt = $this->messagecount - $this->messages_per_page;
		}		
		
		$endAt = $startAt + $this->messages_per_page;
		
		$this->current_page = ($startAt/$this->messages_per_page)+1;
		$this->total_pages = ceil($this->messagecount/$this->messages_per_page);

		
		$res = $this->query("SELECT 
				$this->table_wordbox_field_id as id,
				$this->table_wordbox_field_author as author,
				$this->table_wordbox_field_message as message,
				$this->table_wordbox_field_timestamp as timestamp,
				$this->table_wordbox_field_deleted as deleted
			FROM 
				$this->table_wordbox
			".($this->show_deleted ? "" : "WHERE $this->table_wordbox_field_deleted = 0")."
			ORDER BY $this->table_wordbox_field_id
			LIMIT $startAt,$this->messages_per_page"
		);

		$shouts = "";
		
		$rNo = 2;
		$shouts .= $this->template_header;
		while ($row = $res->fetch_assoc()) {
			$author = stripslashes($row["author"]);
			$message = stripslashes($row["message"]);
			$message = parse_emoticons($this->wrapLongWords(htmlspecialchars($message)));
			$datetime = date("d.m.y H:i:s",$row["timestamp"]);
			$trNo = $rNo = ($rNo == 2) ? 1 : 2;
			if ($row['deleted']) $trNo = 3;
			if ($author == '0'){ 
				$author = "Gjest";
			} else {
				$m = call_user_func($this->lookup_member, $author);
				if (empty($m->nickname)){
					$author = call_user_func($this->make_memberlink,$author,$m->firstname);
				} else {
					$author = call_user_func($this->make_memberlink,$author,$m->nickname);
				}
			}
			$r1a = array(); $r2a = array();
			$r1a[0]  = "%rowno%";		 $r2a[0]  = $trNo;
			$r1a[1]  = "%author%";		 $r2a[1]  = $author;
			$r1a[2]  = "%message%";		 $r2a[2]  = $message;
			$r1a[3]  = "%timestamp%";	 $r2a[3]  = $datetime;
			$r1a[4]  = "%deletelink%";   $r2a[4]  = (($this->allow_delete && $this->show_delete) ? 
					($row['deleted'] ? 
						" <a href='".$this->generateURL(array("noprint=true","recover_shout=".$row['id']))."'>[Antislett]</a>" :
						" <a href='".$this->generateURL(array("noprint=true","delete_shout=".$row['id']))."'>[Slett]</a>"
					)
				: "");
			$shouts .= str_replace($r1a, $r2a, $this->template_message);
		}
		$shouts .= $this->template_footer;		
		$res->close();
		return $shouts;
	}
	
	function printShouts(){
		$output = "

			<div id='$this->identifier'>
				".$this->makeShouts()."
			</div>

		";
		if ($this->show_pagenavigation){
			$ppu = $this->generateURL("wb_page=".($this->current_page-1));
			$npu = $this->generateURL("wb_page=".($this->current_page+1));
			$pp = ($this->current_page > 1)   ? "<a href=\"$ppu\">$this->label_older</a>" : $this->label_older; 
			$np = ($this->current_page < $this->total_pages) ? "<a href=\"$npu\">$this->label_newer</a>" : $this->label_newer; 
			$xofy = str_replace(Array("%x%","%y%"),Array($this->current_page,$this->total_pages),$this->str_pagexofy);
			$output .= "
				<table width='100%' style='margin-top:10px;'>
					<tr>
						<td>$pp</td>
						<td align='center'>$xofy</td>
						<td align='right'>$np</td>
					</tr>
				</table>
			";
		}
		return $output;
	}
	
	function strlen_utf8 ($str)
	{
	 $i = 0;
	 $count = 0;
	 $len = strlen ($str);
	   while ($i < $len)
	   {
	   $chr = ord ($str[$i]);
	   $count++;
	   $i++;
	   if ($i >= $len)
		   break;
	
	   if ($chr & 0x80)
	   {
		   $chr <<= 1;
		   while ($chr & 0x80)
		   {
		   $i++;
		   $chr <<= 1;
		   }
	   }
	   }
	   return $count;
	}

	function saveNewShout(){

		if (!$this->allow_post) { $this->permissionDenied(); return; }

		$this->bannedWords = explode(",",$this->bannedWords);
		$this->bannedIPs = explode(",",$this->bannedIPs);
		
		if (isset($_POST['snikksnakkform_text'])) 
			$message = trim(urldecode($_POST['snikksnakkform_text']));
		
		if (empty($message))
			unset($message);
		
		$timestamp = time();
		$ip = ip2long($_SERVER['REMOTE_ADDR']);	// Oversetter ip adressen til skikkelig adresse. Bruk long2ip($ip) for aa faa tilbake normal ip...
		
		if (in_array($ip,$this->bannedIPs)) {
			$this->fatalError("Meldingen ble IKKE lagret fordi IPen din er bannet.");
		}
		
		if(isset($message)){
			
			$message2 = strip_tags($message);
			if ($message != $message2) {
				$this->fatalError("HTML er ikke tillatt!",array('logError' => false));			
			}
			$message = $message2;
			
			$msplit = explode(" ",$message);
			foreach($msplit as $w) {
				if ($this->strlen_utf8($w) > 20) {
					$this->fatalError("Meldingen ble IKKE lagret fordi ordet \"$w\" var på mer enn 20 tegn.",array('logError' => false));
				}
				if (in_array(strtolower($w),$this->bannedWords)) {
					$this->fatalError("Meldingen ble IKKE lagret fordi ordet \"$w\" er bannet.");
				}
			}
			if (!isset($_POST['snikksnakkform_gid'])) {
				$this->fatalError("invalid_request");			
			}
			$gid = $_POST['snikksnakkform_gid'];
			if ($gid == 'nojs') {
				$this->fatalError("Du må ha JavaScript påslått for å lagre meldinger.",array('logError' => false));
			}
			if (!is_numeric($gid)) {
				$this->fatalError("invalid gid");
			}
			if ($gid < time()-3600 || $gid > time()-2) {
				$this->fatalError("Meldingen ble IKKE lagret fordi du brukte for kort eller lang tid på å skrive den. Dette for å beskytte mot spam. For at meldingen skal bli lagret, må du bruke mellom 2 sekunder og 1 time på å skrive den.",array('logError' => false));			
			}
			
			$timelim = $timestamp - 2;
			$res = $this->query("SELECT COUNT(id) FROM $this->table_wordbox WHERE ip='$ip' AND timestamp > $timelim");
			$row = $res->fetch_array();
			if ($row[0] >= 1){
				$this->fatalError("Meldingen ble IKKE lagret. For å unngå misbruk må du vente 2 sekunder mellom hver melding. Vent et par sekunder og prøv igjen.",array('logError' => false));
			}
		
			$timelim = $timestamp - 15;
			$res = $this->query("SELECT COUNT(id) FROM $this->table_wordbox WHERE ip='$ip' AND timestamp > $timelim");
			$row = $res->fetch_array();
			if ($row[0] >= 2){
				$this->fatalError("Meldingen ble IKKE lagret. For å unngå misbruk har vi satt en grense på maks 2 meldinger på 15 sekunder. Vent et par sekunder og prøv igjen.",array('logError' => false));
			}
		
			/*
			$timelim = $timestamp - 30;
			$res = $this->query("SELECT COUNT(id) FROM wordbox WHERE ip='$ip' AND timestamp > $timelim");
			$row = $res->fetch_array();
			if ($row[0] > 4){
				$this->fatalError("Meldingen ble IKKE lagret. For å unngå misbruk har vi satt en rimelig grense på maks 4 meldinger på 60 sekunder. Vent et par sekunder og prøv igjen.");
			}
			*/
			$timelim = $timestamp - 86400;
			$res = $this->query("SELECT COUNT(id) FROM $this->table_wordbox WHERE ip='$ip' AND timestamp > $timelim");
			$row = $res->fetch_array();
			if ($row[0] > 1000){
				$this->fatalError("Meldingen ble IKKE lagret. For å unngå misbruk har vi satt en rimelig grense på maks 1000 meldinger per døgn. Gratulerer med å ha nådd den! ;)",array('logError' => false));
			}
			
		}

		if(isset($message)){
			$message = addslashes($message);
			
			if (($message == 'Unknown') || empty($message)) unset($message);
		}
		if(isset($message)){
			
			if ($this->login_identifier != NULL){
				$author = $this->login_identifier;
			} else {
				$author = "0"; // 0 = gjest
			}
			$this->query("INSERT INTO $this->table_wordbox 
				(
					$this->table_wordbox_field_author,
					$this->table_wordbox_field_message,
					$this->table_wordbox_field_ip,
					$this->table_wordbox_field_timestamp
				)
				VALUES
				(
					'$author',
					'$message',
					'$ip',
					'$timestamp'
				)"
			);
			if ($author != "0"){
				if (!empty($this->increasememberpostcount_function)){
					$ipf = $this->increasememberpostcount_function;
					$ipf($author);
				}
			}
		}
		
		if (isset($_POST['javascript_enabled'])) {
			header("Content-Type: text/html; charset=utf-8");
			print $this->makeShouts();
			exit();
		} else {
			$this->redirect($this->generateURL(""));
		}
	}
	
	function deleteShout($id) {
		if (!is_numeric($id)) $this->fatalError("Invalid entry");
		if (!$this->allow_delete){ $this->permissionDenied(); exit(); }
		$res = $this->query("SELECT $this->table_wordbox_field_message FROM $this->table_wordbox WHERE $this->table_wordbox_field_id='$id'");
		$row = $res->fetch_assoc();
		$this->addToActivityLog("Slettet snikksnakk-melding: ".stripslashes($row['message']));
		$this->query("UPDATE $this->table_wordbox SET deleted=1 WHERE $this->table_wordbox_field_id='$id' LIMIT 1");
		$this->redirect($this->generateURL(""),"Innlegget ble slettet");
	}
	
	function recoverShout($id) {
		if (!is_numeric($id)) $this->fatalError("Invalid entry");
		if (!$this->allow_delete){ $this->permissionDenied(); exit(); }
		$res = $this->query("SELECT $this->table_wordbox_field_message FROM $this->table_wordbox WHERE $this->table_wordbox_field_id='$id'");
		$row = $res->fetch_assoc();
		$this->addToActivityLog("Antislettet snikksnakk-melding: ".stripslashes($row['message']));
		$this->query("UPDATE $this->table_wordbox SET deleted=0 WHERE $this->table_wordbox_field_id='$id' LIMIT 1");
		$this->redirect($this->generateURL(""),"Innlegget ble antislettet");
	}
	
	function shoutForm(){
		$url_full = '/'.$this->fullslug;
		$url_post_js = $this->generateURL(array("noprint=true","save_shout"),true);
		return '
		
			<script type="text/javascript">
			
				function snikksnakk_post(event) {
				    event.preventDefault();
				
					var pars = new Array();
					pars.push($("#snikksnakkform_text").serialize());
					pars.push("show_pagenavigation='.$this->show_pagenavigation.'");
					pars.push("messages_per_page='.$this->messages_per_page.'");
					pars.push("javascript_enabled=true");
					pars.push("snikksnakkform_gid='.time().'");
					pars = pars.join("&");

					$("#wb_indicator").css("visibility","visible");
					$("#snikksnakkform_text").prop("disabled",true);
					$("#snikksnakkform_submitbtn").prop("disabled",true);
					$("#snikksnakkform_text").val("Lagrer...");
					var form = "form_'.$this->identifier.'";
					$.ajax({
					    url: "'.$url_post_js.'",
					    data: pars,
					    dataType: "html",
					    type: "POST",
					    success: function(responseText){  
                            $("#'.$this->identifier.'").html(responseText);
					        $("#wb_indicator").css("visibility","hidden");
                            $("#snikksnakkform_text").prop("disabled",false);
                            $("#snikksnakkform_submitbtn").prop("disabled",false);
        					$("#snikksnakkform_text").val("");
                        },
                        error: function() {
                            alert("Det oppsto en feil under lagring :(")
					        $("#wb_indicator").css("visibility","hidden");
                            $("#snikksnakkform_text").prop("disabled",false);
                            $("#snikksnakkform_submitbtn").prop("disabled",false);
        					$("#snikksnakkform_text").val("");                        
                        }
                    });
                    return false;
                }
                    
                $(document).ready(function(){
                    $("#form_'.$this->identifier.'").submit(snikksnakk_post);
                });
                
			</script>
		
		
			<div id="wordboxdiv">
				<form name="wordboxform" id="form_'.$this->identifier.'" action="'.$this->generateURL(array("noprint=true","save_shout")).'" method="post">
					<div style="margin:0px; padding:0px;">
						<div id="wb_indicator" style="text-align:center;visibility:hidden;"><img src="'.$this->image_dir.'progressbar1.gif" alt="Progressbar" /></div>
						<input type="text" name="snikksnakkform_gid" id="snikksnakkform_gid" value="nojs" style="display:none;" /> 
						<input type="text" name="snikksnakkform_text" id="snikksnakkform_text" maxlength="120" /> 
						<input type="submit" value="Snakk" name="snikksnakkform_submitbtn" id="snikksnakkform_submitbtn" />
					</div>
					<p class="smalltext" style="margin:0px; padding:0px;">
						<a href="/popups/wb_retningslinjer.php" onclick="window.open(\''.ROOT_DIR.'/popups/wb_retningslinjer.php\',\'wordboxhelp\',\'width=400,height=200\'); return false;">Retningslinjer</a> |
						<a href="/popups/wb_smileys.php" onclick="window.open(\''.ROOT_DIR.'/popups/wb_smileys.php\',\'wordboxhelp\',\'width=550,height=200\'); return false;">Smileys</a> |
						<a href="'.$url_full.'">Eldre meldinger</a>
					</p>
				</form>
			</div>
		';
	}

}

?>
