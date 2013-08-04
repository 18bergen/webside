<?
class sms extends base {

	var $getvars = array('sms_action', 'sms_id');

	var $current_row = 0;	
	
	var $allow_viewarchive = false;
	var $allow_send = false;
		
	var $table_sms = "sms";
	var $table_sms_status = "sms_status";
	var $tm4b_user;
	var $tm4b_pass;
	var $tm4b_gateway;
	var $creditsPerMsg;
	var $GBPperCredit;
	var $NOKperGBP;
	var $maxCharsPerMsg;
	
	function sms() {
		$this->table_sms = DBPREFIX.$this->table_sms;
		$this->table_sms_status = DBPREFIX.$this->table_sms_status;

	}

	function initialize(){
		$this->initialize_base();
	}
	
	function run() {
		$this->initialize();
		
		$action = "default";
		if (isset($_GET['sms_action'])) $action = $_GET['sms_action'];
		
		switch ($action) {
		
			case 'add_recipient':
				return $this->addRecipient();
				break;

			case 'save_recipient':
				return $this->saveRecipient();
				break;

			case 'cancel_recipient':
				return $this->cancelRecipient();
				break;
				
			case 'remove_recipient':
				return $this->removeRecipient();
				break;
		
			case 'new':
				return $this->newSmsForm();
				break;

			case 'calculate_price':
				return $this->calculatePrice();
				break;

			case 'send':
				return $this->sendSms();
				break;
				
			case 'delivery_report':
				return $this->deliveryReport();
				break;
				
			case 'details':
				return $this->broadcastDetails();
				break;
				
			default:
				return $this->smsStatus();
				break;
		}
	}
	
	function newSmsForm() {
	
		if (!$this->allow_send) return $this->permissionDenied();
		
		$this->query("INSERT INTO $this->table_sms (sender) VALUES ('".$this->login_identifier."')");
		$this->current_row = $this->insert_id();
		
		$rcpt_list = $this->makeEditableRcptList(array());
		
		$url_post = $this->generateURL("sms_action=calculate_price");
		
		return '
			<h2>Send SMS</h2>
			<form method="post" action="'.$url_post.'">
				<input type="hidden" name="current_row" id="current_row" value="'.$this->current_row.'" />
				<table>
					<tr>
						<td style="font-weight: bold; vertical-align: top;">Mottakere: </td>
						<td>'.$rcpt_list.'</td>
					</tr><tr>
						<td style="font-weight: bold; vertical-align: top;">Melding: </td>
						<td>
							<textarea 
								name="sms_melding" 
								id="sms_melding"
								rows="5" 
								cols="40"
								onKeyDown="updateCounter();"
								onKeyUp="updateCounter();"
							></textarea>
							<div id="antalltegn" style="text-align:right; color: #888; font-size:10px;">-</div>
						</td>
					</tr>
				</table>
				<input type="submit" value="Beregn pris" />
			</form>
			
			<h2>Info</h2>
			<p>
				En SMS kan maks inneholde 158 tegn. Hvis du overstiger dette vil meldingen bli sendt som to SMS. De fleste nyere mobiler
				vil slå meldingene sammen når de mottas, slik at de fremstår som en - men prisen vil bli dobbel.
			</p>
			<p>
				Duplikatnumre lukes ut. Dvs. at hvis f.eks. to foreldre har registrert seg med det samme mobilnr. vil bare en melding bli sendt.
			</p>
			<p>
				Du kan bruke alle "normale" tegn inkl. æ, ø og å. Alle tillatte tegn er listet her: <a href="http://www.tm4bhelp.com/kb/a-241.php#241">GSM 7-Bit Alphabet</a>.
			</p>
			
			<script type="text/javascript">
			//<![CDATA[
				function updateCounter() {
					var charsPerMsg = '.$this->maxCharsPerMsg.';
					var currentChars = $("#sms_melding").val().length;
					var msgs = Math.ceil(currentChars/charsPerMsg);
					$("#antalltegn").html(currentChars + " tegn = "+msgs+" melding(er)");
				}
			//]]>
			</script>
			
		';
	
	}
	
	function calculatePrice() {		

		if (!$this->allow_send) return $this->permissionDenied(); 
		
		$id = $this->current_row = $_POST['current_row'];
		if (!is_numeric($id)) $this->fatalError("invalid input .0");
		
		$groups = trim($_POST['recipients']);
		if (empty($groups)) $this->fatalError("Du må angi minst en mottakergruppe!");
		$groups = explode(",",$groups);
		$recipients = array();
		foreach ($groups as $gr) {
			$g = call_user_func($this->lookup_group, $gr);
			foreach ($g->members as $me) {
				$m = call_user_func($this->lookup_member, $me);
				if (mb_strlen($m->cellular) == 8){
					$recipients[$m->ident] = "47".$m->cellular;
				}
			}
		}
		$recipients = array_unique($recipients);
		$msg = $_POST['sms_melding'];
		$msg_len = mb_strlen($msg);
		$rcpt_len = count($recipients);
		
		$msg_count = ceil($msg_len/$this->maxCharsPerMsg);
		$price = $msg_count * $rcpt_len * $this->creditsPerMsg * $this->GBPperCredit * $this->NOKperGBP;

		$url_post = $this->generateURL(array("noprint=true","sms_action=send"));
		$output = '
			<h2>Bekreft sending</h2>
			<p style="border: 1px dashed black; background: #fff; padding: 10px;">'.nl2br($msg).'</p>
			<p>
				Meldingen vil bli sendt til '.$rcpt_len.' mottakere.<br />
				Meldingen er på '.$msg_len.' tegn og vil bli sendt som '.$msg_count.' melding(er) per mottaker.
			</p>
			<p>
				Prisen for sendingen blir '.$price.' kr. (forutsatt at 1 GBP = '.$this->NOKperGBP.' NOK).
			</p>
			<p>
				<form method="post" action="'.$url_post.'" onsubmit="sms_form_submit(); return true;">
					<input type="hidden" name="current_row" id="current_row" value="'.$id.'" />
					<input type="submit" id="sms_submit_btn" value="Send meldingen" />
				</form>
			</p>
			<script type="text/javascript">
			//<![CDATA[
			
				function sms_form_submit() {
					$("#sms_submit_btn").prop("disabled", true);
				}
			
			//]]>
			</script>
		';
		
		$this->query("UPDATE $this->table_sms SET msg=\"".addslashes($msg)."\" WHERE id='$id'");

		// To avoid duplicates
		$this->query("DELETE FROM $this->table_sms_status WHERE sms_id='$id'");
		
		foreach ($recipients as $ident => $number) {
			$this->query("INSERT INTO $this->table_sms_status 
				(sms_id, rcpt_ident, rcpt_number, status, timestamp) VALUES 
				($id,$ident,'$number','NOT_SENT',".time().")"
			);
		}
		
		return $output;
		
	}
	
	function sendSms() {

		if (!$this->allow_send) return $this->permissionDenied(); 

		$id = $this->current_row = $_POST['current_row'];
		if (!is_numeric($id)) $this->fatalError("invalid input .01");
		
		$res = $this->query("SELECT msg FROM $this->table_sms WHERE id='$id'");
		if ($res->num_rows != 1) $this->fatalError("invalid input .02");
		$row = $res->fetch_assoc();
		$msg = stripslashes($row['msg']);

		$recipients = array();
		$res = $this->query("SELECT rcpt_number FROM $this->table_sms_status WHERE sms_id='$id' AND status='NOT_SENT'");
		while ($row = $res->fetch_assoc()) $recipients[] = $row['rcpt_number'];

		$request = ""; 											//initialize the request variable		
		$param["username"] 		= $this->tm4b_user;
		$param["password"]	 	= $this->tm4b_pass;
		$param["type"] 			= "broadcast";
		$param["to"] 			= implode("|",$recipients); 	// these are the recipients of the message
		$param["from"] 			= "18BergenVS";					// this is our sender 
		$param["msg"] 			= utf8_decode($msg); 			// this is the message that we want to send
		$param["route"] 		= "GD01"; 						// we want to send the message via GLOBAL I
		$param["split_method"] 	= "7"; 							// we want long messages split by SMS Numbering (strict)
		$param["version"] 		= "2.1";						
		$param["sim"] 			= "no";							// we are only simulating a broadcast
		
		foreach($param as $key=>$val) //traverse through each member of the param array
		{ 
		  $request.= $key."=".urlencode($val); //we have to urlencode the values
		  $request.= "&"; //append the ampersand (&) sign after each paramter/value pair
		}
		$request = mb_substr($request, 0, mb_strlen($request)-1); //remove the final ampersand sign from the request
		
		$url = $this->tm4b_gateway; //this is the url of the gateway's interface
		$ch = curl_init(); //initialize curl handle 
		curl_setopt($ch, CURLOPT_URL, $url); //set the url
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); //return as a variable 
		curl_setopt($ch, CURLOPT_POST, 1); //set POST method 
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request); //set the POST variables
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); 
		$response = curl_exec($ch); //run the whole process and return the response
		curl_close($ch); //close the curl handle
		
		$xml= new DOMDocument();
		$xml->preserveWhiteSpace=false;
		$xml->loadXML($response);
		$response = $this->xml2array($xml);

		$broadcast_id = $response['result']['broadcastID'];
		
		$this->query("UPDATE $this->table_sms 
			SET 
				broadcast_id='$broadcast_id',
				timestamp='".time()."'
			WHERE id='$id'"
		);
		$this->query("UPDATE $this->table_sms_status 
			SET 
				broadcast_id='$broadcast_id',
				timestamp='".time()."',
				status='SENT'
			WHERE sms_id='$id'"
		);
		
		$neglected = trim($response['result']['neglected']);
		if (!empty($neglected) && ($neglected != "-")) {
			$neglected = explode(",",$neglected);
			foreach ($neglected as $neglected_id) {
				$num = $recipients[$neglected_id-1];
				$this->query("UPDATE $this->table_sms_status 
					SET 
						status='NEGLECTED'
					WHERE sms_id='$id'
					AND rcpt_number='$num'
					"
				);
			}
		}
		
		$redir_uri = $this->generateURL(array("sms_action=details","sms_id=$id"),true);
		$this->addToActivityLog("sendte ut <a href=\"$redir_uri\">en sms-melding</a>.");
		$this->redirect($redir_uri, "Meldingen ble sendt!");
		
	}
	
	function xml2array($n) {
		$return=array();
		foreach($n->childNodes as $nc)
		($nc->hasChildNodes())
		?($n->firstChild->nodeName== $n->lastChild->nodeName&&$n->childNodes->length>1)
		?$return[$nc->nodeName][]=$this->xml2array($item)
		:$return[$nc->nodeName]=$this->xml2array($nc)
		:$return=$nc->nodeValue;
		return $return;
	}
	
	function smsStatus() {

		if (!$this->allow_viewarchive) return $this->permissionDenied(); 

		$request = ""; //initialize the request variable

		$param["username"] 	= $this->tm4b_user;
		$param["password"] 	= $this->tm4b_pass;
		$param["type"] 		= "check_balance";
		$param["version"] 	= "2.1";
		
		foreach($param as $key=>$val) //traverse through each member of the param array
		{ 
		  $request.= $key."=".urlencode($val); //we have to urlencode the values
		  $request.= "&"; //append the ampersand (&) sign after each paramter/value pair
		}
		$request = mb_substr($request, 0, mb_strlen($request)-1); //remove the final ampersand sign from the request
		
		$url = $this->tm4b_gateway; //this is the url of the gateway's interface
		$ch = curl_init(); //initialize curl handle 
		curl_setopt($ch, CURLOPT_URL, $url); //set the url
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); //return as a variable 
		curl_setopt($ch, CURLOPT_POST, 1); //set POST method 
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request); //set the POST variables
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); 
		$response = curl_exec($ch); //run the whole process and return the response
		curl_close($ch); //close the curl handle
		
		if (empty($response)) {
			return $this->notSoFatalError("Det oppstod en feil under tilkoblingen til TM4B!");
		}
		$xml= new DOMDocument();
		$xml->preserveWhiteSpace=false;
		$xml->loadXML($response);
		$response = $this->xml2array($xml);
				
		$credits = $response['result']['GBP'];
		$creditsPerMsg = 3.4; // as of 1. January 2006
		$GBPperCredit = 0.01; // GBP
		$NOKperGBP = 12; // GBP
		
		$messages = floor($credits / $this->creditsPerMsg);
		$NOK = round($credits * $this->GBPperCredit * $this->NOKperGBP);
		$NOKperMsg = $this->GBPperCredit * $this->NOKperGBP * $this->creditsPerMsg;

		$output = "";
		if ($this->allow_send){
			$output .= '
			<p>
				<a href="'.$this->generateURL('sms_action=new').'">Send SMS-melding</a>
			</p>
			';
		}		
		$output .= "
			<p>
				Velkommen til SMS-tjenesten. Vi benytter oss av <a href='http://www.tm4b.com/' target='_blank'>TM4B</a> sin SMS gateway.
				SMS-tjenesten er først og fremst ment som et tilbud til å sende ut påminnelser og viktig informasjon i forkant av arrangementer. 
				Prisen vi betaler er for tiden $NOKperMsg kr per utsendte melding. Alle sendte meldinger logges.
			</p>
			<p>
				Vi får statusmeldinger tilbake fra TM4B. Ved å trykke på \"Detaljer\" kan du se hvem som har mottatt meldingene og hvem som ikke har det.
			</p>
			<h2>Kontostatus</h2>
			<p>
				Vi har akkurat nå $credits kreditter, som tilsvarer ca. $NOK kr / $messages meldinger.
			</p>
		";
		$output .= "
			<h2>Arkiv</h2>			
			<table class='forum' cellpadding='2' cellspacing='0' width='100%'>
				<tr>
					<th>Dato</th>
					<th>Melding</th>
					<th>Detaljer</th>
				</tr>
		";
		$res = $this->query("SELECT id, timestamp, msg, broadcast_id FROM $this->table_sms WHERE timestamp != '' ORDER BY timestamp DESC");
		$n = 1;
		while ($row = $res->fetch_assoc()) {
			$n = ($n == 1) ? 2 : 1;
			$id = $row['id'];
			$msg = stripslashes($row['msg']);
			if (mb_strlen($msg) > 60) $msg = mb_substr($msg,0,50)."...";
			$url_details = $this->generateURL(array("sms_action=details","sms_id=$id"));
			$output .= "
				<tr class='forum$n'>
					<td>".date("d.m.Y",$row['timestamp'])."</td>
					<td>$msg</td>
					<td><a href=\"$url_details\">Detaljer</a></td>
				</tr>
			";			
		}
		$output .= "</table>";
		
		return $output;
		
	}
	
	function broadcastDetails() {

		if (!$this->allow_viewarchive){ $this->permissionDenied(); return; }
	
		$id = $_GET['sms_id'];
		if (!is_numeric($id)) $this->fatalError("invalid input .03");

		$res = $this->query("SELECT id, timestamp, msg, broadcast_id FROM $this->table_sms WHERE id='$id'");
		$row = $res->fetch_assoc();
		$broadcast_id = $row['broadcast_id'];
		$msg = nl2br(stripslashes($row['msg']));
		$datesent = date("d. M Y, H:i", $row['timestamp']);
		
		$res = $this->query("SELECT COUNT(id) FROM $this->table_sms_status WHERE broadcast_id='$broadcast_id'");
		$row = $res->fetch_row();
		$msg_count = $row[0];

		$res = $this->query("SELECT COUNT(id) FROM $this->table_sms_status WHERE broadcast_id='$broadcast_id' AND status='DELIVRD'");
		$row = $res->fetch_row();
		$delivered_count = $row[0];

		$output = "
			<p>
				<a href=\"".$this->generateURL("")."\">Tilbake til oversikt</a>
			</p>
			
			<h2>Detaljer for sending</h2>
			<p>
				<strong>Broadcast ID: </strong> $broadcast_id<br />
				<strong>Sendt: </strong> $datesent<br />
				<strong>Levert: </strong> $delivered_count / $msg_count<br />
				<strong>Melding: </strong> $msg<br />
			</p>

			<table class='forum' cellpadding='2' cellspacing='0' width='100%'>
				<tr>
					<th>ID</th>
					<th>Mottaker</th>
					<th>Status</th>
					<th>Status sist oppdatert</th>
				</tr>
		";
		$res = $this->query("SELECT id, rcpt_ident, message_id, status, timestamp FROM $this->table_sms_status WHERE broadcast_id='$broadcast_id'");
		$n = 1;
		while ($row = $res->fetch_assoc()) {
			$n = ($n == 1) ? 2 : 1;
			$message_id = $row['message_id'];
			$status = $row['status'];
			$member = call_user_func($this->make_memberlink, $row['rcpt_ident']);
			$output .= "
				<tr class='forum$n'>
					<td>$message_id</td>
					<td>$member</td>
					<td>$status</td>
					<td>".date("d.m.Y, H:i",$row['timestamp'])."</td>
				</tr>
			";	
		}
		$output .= "
			</table>
			<h2>Possible delivery states:</h2>
			<ul>
				
				<li>NOT_SENT<br />
				Not sent yet.
				</li>

				<li>SENT<br />
				Sent, but no delivery reports received yet.
				</li>
				
				<li>DELIVRD<br />
				The message has been delivered to the destination.
				</li>
				
				<li>EXPIRED<br />
				The message has failed to be delivered within its validity period and/or retry period.
				</li>
				
				<li>DELETED<br />
				The message has been cancelled or deleted from the MC.
				</li>
				
				<li>UNDELIV<br />
				The message has encountered a delivery error and is deemed permanently undeliverable. Certain network or MC internal errors result in the permanent non-delivery of a message.
				</li>
				
				<li>ACCEPTD<br />
				This state is used to depict intervention on the MC side. Sometimes a malformed message can cause a mobile to power-off or experience problems. The result is that all messages to that mobile may remain queued until the problem message is removed or expires. In certain circumstances, a mobile network support service or administrator may manually accept a message to prevent further deliveries and allow other queued messages to be delivered.
				</li>
				
				<li>UNKNOWN<br />
				The message state is unknown. This may be due to some internal message centre problem which may be intermediate or a permanent. An MC experiencing difficulties that prevents it from returning a message state would use this state.
				</li>
				
				<li>REJECTD<br />
				The message has been rejected by a delivery interface. The reasons for this rejection are vendor and network specific.
				</li>
				
			</ul>
		";		
		
		return $output;		
	}
	
	function deliveryReport() {
	
		$this->addToActivityLog("Mottok statusrapport fra TM4B!");
	
		$full_id = addslashes($_GET['id']);
		$status = addslashes($_GET['status']);
		$timestamp = addslashes($_GET['date']); // YYMMDDhhmm
		$recipient = $_GET['recipient'];
		
		$year = mb_substr($timestamp,0,2);
		$month = mb_substr($timestamp,2,2);
		$day = mb_substr($timestamp,4,2);
		$hour = mb_substr($timestamp,6,2);
		$min = mb_substr($timestamp,8,2);
		$sec = 0;
		$timestamp = mktime ($hour,$min,$sec,$month,$day,$year);
		
		$timezone_offset = date("Z",time());
		$timestamp += $timezone_offset;

		
		list($broadcast_id, $message_id) = explode("-",$full_id);
		
		$res = $this->query("SELECT id FROM $this->table_sms_status WHERE broadcast_id='$broadcast_id' AND rcpt_number='$recipient'");
		$row = $res->fetch_assoc();
		$id = $row['id'];
		if (is_numeric($id) && $id > 0) {
			$this->query("UPDATE $this->table_sms_status 
				SET 
					full_id=\"$full_id\",
					message_id=\"$message_id\",
					status=\"$status\", 
					timestamp=\"$timestamp\" 
				WHERE id=\"$id\""
			);
		}	
		print " ";
		exit();
		
	}
	
	function makeEditableRcptList($recipients) {
		$ml = '
			
			<script type="text/javascript">
					
				function addRecipient(){
					var pars = new Array();
					pars.push("current_row='.$this->current_row.'");
					pars.push("recipients="+escape(jQuery("#recipients").val()));
					pars = pars.join("&");
					setText("memberlist_s", "Vent litt...");
					jQuery.ajax({
					    url: "'.$this->generateURL(array("noprint=true","sms_action=add_recipient"),true).'",
					    type: "POST",
					    data: pars, 
					    dataType: "html",
					    success: function(responseText){ 
						    setText("memberlist_s",responseText);
					    }
					});
				}
				
				function saveRecipient() {
					var pars = new Array();
					pars.push("current_row='.$this->current_row.'");
					pars.push("recipients="+escape(jQuery("#recipients").val()));
					pars.push("newrecipient="+escape(jQuery("#newrecipient").val()));
					pars = pars.join("&");
					setText("memberlist_s", "Vent litt...");
					jQuery.ajax({
					    url: "'.$this->generateURL(array("noprint=true","sms_action=save_recipient"),true).'",
					    type: "POST",
					    data: pars, 
					    dataType: "html",
					    success: function(responseText){ 
						    setText("memberlist_s",responseText);
					    }
					});					
				}
				
				function cancelRecipient() {
					var pars = new Array();
					pars.push("current_row='.$this->current_row.'");
					pars.push("recipients="+escape(jQuery("#recipients").val()));
					pars = pars.join("&");
					setText("memberlist_s", "Vent litt...");
					jQuery.ajax({
					    url: "'.$this->generateURL(array("noprint=true","sms_action=cancel_recipient"),true).'",
					    type: "POST",
					    data: pars, 
					    dataType: "html",
					    success: function(responseText){ 
						    setText("memberlist_s",responseText);
					    }
					});				
				}
				
				function removeRecipient(id) {
					var pars = new Array();
					pars.push("current_row='.$this->current_row.'");
					pars.push("recipients="+escape(jQuery("#recipients").val()));
					pars.push("removerecipient="+id);
					pars = pars.join("&");
					setText("memberlist_s", "Vent litt...");
					jQuery.ajax({
					    url: "'.$this->generateURL(array("noprint=true","sms_action=remove_recipient"),true).'",
					    type: "POST",
					    data: pars, 
					    dataType: "html",
					    success: function(responseText){ 
						    setText("memberlist_s",responseText);
					    }
					});				
				}
				
			</script>
		';
		
		$ml .= "
			<div id='memberlist_s' class='memberlist_s'>
				".$this->makeRecipientList($recipients)."			
			</div>
		
		";
		return $ml;
	}
	
	function makeRecipientList($recipients) {
		$alist = "
				<input type='hidden' name='recipients' id='recipients' value=\"".implode(",",$recipients)."\" />
				<ul class='memberlist_s'>\n";
		foreach ($recipients as $gid) {
			$g = call_user_func($this->lookup_group, $gid);
			if (count($recipients) > 1)
				$fjernLink = "<a href='#' onclick=\"removeRecipient($gid); return false;\">[ Fjern ]</a>";
			else 
				$fjernLink = "";
			
			$alist .= "					<li>$g->caption $fjernLink</li>\n";		
		}
		$alist .= "
				</ul>
				<a href='#' onclick=\"addRecipient(); return false;\">[ Legg til ]</a>
				";
		return $alist;
	}
	
	function addRecipient() {
		global $memberdb;
		
		$this->current_row = $_POST['current_row'];
		if (!is_numeric($this->current_row)) $this->fatalError('invalid input .2');
		$res = $this->query(
			"SELECT recipients_groups FROM $this->table_sms WHERE id='$this->current_row'"
		);
		if ($res->num_rows != 1) $this->fatalError("invalid input .3");
		$row = $res->fetch_assoc();
		$recipients = explode(",",trim($row['recipients_groups']));
		$flat_rcpts = implode(",",$recipients);
		
		header("Content-Type: text/html; charset=utf-8");
		print "
				<input type='hidden' name='recipients' id='recipients' value=\"$flat_rcpts\" />
				<ul>\n";
		if (!empty($recipients)) {
			foreach ($recipients as $gid) {
				if (is_numeric($gid)) {
					$g = call_user_func($this->lookup_group, $gid);
					print "					<li>$g->caption</li>\n";		
				}
			}
		}
		$okurl = $this->generateURL(array("noprint=true","sms_action=save_recipient"),true);
		$groups = $memberdb->generateGroupSelectBox("newrecipient");
		print "
					<li>
						$groups
						<a href='#' onclick='cancelRecipient(); return false;'>[ Avbryt ]</a>
						<a href='#' onclick='saveRecipient(); return false;'>[ Ok ]</a>
					</li>\n
				</ul>
		";
		exit();
	}
	
	function saveRecipient() {
		$newRecipient = $_POST['newrecipient'];
		$this->current_row = $_POST['current_row'];
		if (!is_numeric($newRecipient)) $this->fatalError('invalid input .1');
		if (!is_numeric($this->current_row)) $this->fatalError('invalid input .2');
		
		$res = $this->query(
			"SELECT recipients_groups FROM $this->table_sms WHERE id='$this->current_row'"
		);
		if ($res->num_rows != 1) $this->fatalError("invalid input .3");
		$row = $res->fetch_assoc();
		$rcpts = trim($row['recipients_groups']);
		if (empty($rcpts)) {
			$flat_recipients = $newRecipient;
			$recipients = array($newRecipient);
		} else {
			$recipients = explode(",",$rcpts);
			$recipients[] = $newRecipient;
			$flat_recipients = implode(",",$recipients);
		}
		$this->query(
			"UPDATE $this->table_sms SET recipients_groups='$flat_recipients' WHERE id='$this->current_row'"
		);
		
		header("Content-Type: text/html; charset=utf-8");
		print $this->makeRecipientList($recipients);
		exit();
	}
	
	function cancelRecipient() {
		$this->current_row = $_POST['current_row'];
		if (!is_numeric($this->current_row)) $this->fatalError('invalid input .2');
		
		$res = $this->query(
			"SELECT recipients_groups FROM $this->table_sms WHERE id='$this->current_row'"
		);
		if ($res->num_rows != 1) $this->fatalError("invalid input .3");
		$row = $res->fetch_assoc();
		$recipients = explode(",",$row['recipients_groups']);
		
		header("Content-Type: text/html; charset=utf-8");
		print $this->makeRecipientList($recipients);
		exit();	
	}
	
	function removeRecipient() {
		$removed = $_POST['removerecipient'];
		$this->current_row = $_POST['current_row'];
		if (!is_numeric($removed)) $this->fatalError('invalid input .1');
		if (!is_numeric($this->current_row)) $this->fatalError('invalid input .2');
		
		$res = $this->query(
			"SELECT recipients_groups FROM $this->table_sms WHERE id='$this->current_row'"
		);
		if ($res->num_rows != 1) $this->fatalError("invalid input .3");
		$row = $res->fetch_assoc();
		$recipients_old = explode(",",$row['recipients_groups']);
		$recipients = array();
		foreach ($recipients_old as $a)
			if ($a != $removed) $recipients[] = $a;
	
		$flat_recipients = implode(",",$recipients);
		
		$this->query(
			"UPDATE $this->table_sms SET recipients_groups='$flat_recipients' WHERE id='$this->current_row'"
		);
		
		header("Content-Type: text/html; charset=utf-8");
		print $this->makeRecipientList($recipients);
		exit();
	}
	
	
}
?>