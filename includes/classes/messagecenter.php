<?php
class messagecenter extends base {

	var $getvars = array('check_all','recipients','confirm_delete','page');

	var $table_messages = 'messages';
	var $table_user = 'messages_users';	
	var $table_attachments = 'messages_attachments';
    var $table_useroptions = 'cms_useroptions';

    var $table_images = 'images';
	
	var $template_dir 					= "templates/email/";
	var $template_safe_plain 			= "mailschema_safe.txt";
	var $template_safe_html 			= "mailschema_safe.html";
	var $template_anon_plain 			= "mailschema_anonymous.txt";
	var $template_anon_html 			= "mailschema_anonymous.html";
	var $template_newsletter_plain 		= "newsletter_simple.txt";
	var $template_newsletter_html 		= "newsletter_simple.html";
	
	var $label_newmessage = "Send ny melding";
	var $label_reply = "Svar på melding";
	var $label_inbox = "Inbox";
	var $label_sentmessages = "Sent messages";
	var $label_trash = "Trash";
	var $label_conversations = "Conversations";	
	var $label_subject = "Subject";
	var $label_from = "From";
	var $label_to = "To";
	var $label_datesent = "Date sent";
	var $label_datereceived = "Date received";
	var $label_datelast = "Siste";
	var $label_datedeleted = "Dato slettet";
	var $label_conv_starter = "Første fra";
	var $label_num_messages = "Meldinger";
	var $label_recipients = "recipients";
	var $label_recipient = "recipient";
	var $label_newer = "&lt;&lt; Nyere meldinger";
	var $label_older = "Eldre meldinger &gt;&gt;";
	var $label_sendmail;
	var $label_body;
	var $label_attachments;
	var $label_sendmsginfo = 'Du kan ikke skrive HTML eller BBCode i feltet. Internett-adresser blir klikkbare.<br />
						Maks størrelse på hvert vedlegg er %maxfilesize_kb% kB.';

	
	var $image_unread = "mail30.gif";
	var $image_read = "f_norm_no.gif";
	var $image_unreadNewsletter = "mail40.gif";
	var $image_readNewsletter = "mail50.gif";
	var $action = "";
	
	var $mail_incoming = true;
	
	var $allow_sendSingleMail = false;
	var $allow_sendGroupMail = false;
	var $items_per_page = 20;

	var $recipients;
	
	var $postUrl;
	var $mailSentUrl;
	var $page_no = 1;
	
	var $attachment_maxsize = 1024000;
	
	var $imginstanceinited = false;
	
	var $attachment_dir = "/Users/danmichael/Sites/18bergen/bergenvs/attachments";
	
	var $errorMessages = array(
		'empty_subject' => "Du må skrive noe i emnefeltet",
		'empty_body' => "Du må skrive noe i meldingsfeltet",
		'empty_name' => "Du må fylle inn navnet ditt",
		'email_notworking' => "Epostadressen virker ikke lenger (epost kommer i retur)",
		'empty_email' => "Du må fylle inn e-postadressen din",
		'no_recipients' => "Du må velge minst en mottaker",
		'toomany_recipients' => "Som gjest har du kun tilgang til å sende mail til en adresse om gangen. Logg inn for å sende til flere.",
		'no_permission' => "Du har ikke tilgang til å sende e-post",
		'invalid_email' => "Epostadressen din er ikke gyldig",
		'html_name' => "Navnet ditt inneholder HTML-kode",
		'html_email' => "Epostadressen din inneholder HTML-kode",
		'html_subject' => "Emnet inneholder HTML-kode",
		'html_body' => "Meldingen din inneholder HTML-kode",

		'empty_rcpt_name' => "Mangler navn",
		'empty_rcpt_email' => "Mangler epostadresse"
	);

	var $schema_template = '
		%header%
		
		%infomsg% 
		<form enctype="multipart/form-data" method="post" action="%posturl%" onsubmit=\'$("#nsubtn").val("Meldingen sendes..."); $("#nsubtn").prop("disabled",true); return true;\'>
			<input type="hidden" name="replyto" value="%replyto%" />
			%errors%
			
			<!-- MAX_FILE_SIZE must precede the file input field -->
    		<input type="hidden" name="MAX_FILE_SIZE" value="%maxfilesize%" />
    		
			<table cellpadding="2" cellspacing="0" class="skjema">
				<tr><td width="70" valign="top" style="font-weight:bold;">%label_to%: </td><td>
					%recipients%		
				</td></tr>
				<tr style="%vis_nyhetsbrev%">
					<td style="font-weight:bold;">Nyhetsbrev: </td>
					<td>
						<input name="newsletterHeading" value="%newsletterHeading%" style="width:100%;" />
					</td>
				</tr>
				
				%begincommentifloggedin%
				<tr>
					<td style="font-weight:bold;">%label_yourname%: </td>
					<td><input type="text" size="50" name="sendarnamnj" value="%sendername%" /></td>
				</tr><tr>
					<td style="font-weight:bold;">%label_youremail%: </td>
					<td><input type="text" size="50" name="sendaradress" value="%sendermail%" /></td>
				</tr>
				%endcommentifloggedin%
				
				<tr style="%vis_emne%">
					<td style="font-weight:bold;">Emne: </td>
					<td><input type="text" size="50" name="emne" value="%emne%" style="width:100%;" /></td>
				</tr>
				<tr style="%vis_vedlegg%">
					<td style="font-weight:bold;">%label_attachments%: </td>
					<td>
						%vedlegg%
					</td>
				</tr>
				<tr>
					<td valign="top" style="font-weight:bold;">%label_body%: </td>
					<td><textarea name="melding" style="width:100%; height: 150px;">%melding%</textarea>
					<div style="color:#444;">
						%label_sendmsginfo%
					</div></td>
				</tr>
				<tr>
					<td></td>
					<td>
					 <input type="submit" id="nsubtn" value="%label_sendmail%" class="button" /> 
					 %cancelbtn%
					</td>
				</tr>
			</table>
		</form>
	';
	
	function __construct() {
		$this->table_messages = DBPREFIX.$this->table_messages;
		$this->table_user = DBPREFIX.$this->table_user;
		$this->table_attachments = DBPREFIX.$this->table_attachments;
        $this->table_useroptions = DBPREFIX.$this->table_useroptions;
		$this->table_images = DBPREFIX.$this->table_images;
	}
	
	function initialize(){
	
		@parent::initialize(); //$this->initialize_base();		

		//$this->template_dir = BG_INC_PATH.$this->template_dir;
		$this->template_dir = "../includes/".$this->template_dir;
		
		if (count($this->coolUrlSplitted) > 0) 
			$this->action = $this->coolUrlSplitted[0];
		else 
			$this->action = "";

		if ((isset($_GET['page'])) && (is_numeric($_GET['page']))){
			$this->page_no = ($_GET['page']); 
		} else { 
			$this->page_no = 1;
		}
	
	}

	function run(){
		$this->initialize();

		switch ($this->action) {

			case 'attachments':
				if (count($this->coolUrlSplitted) > 1)
					return $this->fetchAttachment($this->coolUrlSplitted[1]);
				break;
				
			case 'reply':
				if (count($this->coolUrlSplitted) > 1)
					return $this->replyToMessage($this->coolUrlSplitted[1]);
				break;
		
			case 'add_recipient':
				return $this->addRecipient();
				break;

			case 'remove_recipient':
				return $this->removeRecipient();
				break;
				
			case 'newmessage':
				return $this->newMessageForm();
				break;

			case 'sendmessage':
				return $this->sendMessage();
				break;
			
			case 'sender':
				if (count($this->coolUrlSplitted) > 1)
					return $this->printQueue($this->coolUrlSplitted[1]);
			    break;

			case 'send':
			    return $this->ajaxSendFromQueue();
			    break;
				
			case 'readmessage':
				if (count($this->coolUrlSplitted) > 1)
					list($m_id,$output) = $this->printMessage($this->coolUrlSplitted[1],false);
					return $output;
				break;

			case 'readthread':
				if (count($this->coolUrlSplitted) > 1)
					return $this->printThread($this->coolUrlSplitted[1],false);
				break;

			case 'delete':
				if (count($this->coolUrlSplitted) > 1)
					return $this->deleteMessage($this->coolUrlSplitted[1]);
				break;

			case 'recover':
				if (count($this->coolUrlSplitted) > 1)
					return $this->recoverMessage($this->coolUrlSplitted[1]);
				break;

			case 'prefs':
				return $this->viewPrefs();
				break;

			case 'saveprefs':
				return $this->savePrefs();
				break;
			
			default:
				return $this->viewMessageCenter();
				break;
		
		}
	}

	function fetchAttachment($fname) {
		$fname = addslashes($fname);
		$a = $this->table_attachments;
		$res = $this->query("SELECT
				$a.id as id,
				$a.filename as file,
				$a.mime as mime,
				$a.friendlyname as name,
				$a.filesize as size
			FROM 
				$a
			WHERE 
				$a.filename = \"$fname\"
			"
		);	
		if ($res->num_rows != 1) {
			$this->fatalError("vedlegget eksisterer ikke");
		}
		$row = $res->fetch_assoc();
		header("Content-Type: ".$row['mime']);
	  	header("Content-Disposition: inline; filename=\"".($row['name'])."\"");
		
		if (isset($_GET['thumb'])) {
			$imgthumb = explode(".",$row['file']);
			$ext = array_pop($imgthumb);
			$imgthumb = implode(".",$imgthumb)."_thumb.".$ext;
			readfile($this->attachment_dir."/".$imgthumb);
		} else {
			readfile($this->attachment_dir."/".$row['file']);
		}
		exit();
	}
	
	function replyToMessage($id) {
		if (empty($this->login_identifier)) return $this->permissionDenied();

		list($m_id,$output) = $this->printMessage($id,false);
		return $output . 
			$this->newMessageForm($id);

	}
	
	function newMessageForm($replyTo = 0) {
		global $memberdb;
				
		$rcptCode = array();
		$ids = array();
		$subject = "";
		$attachment = "";
		$recipients = array();
		
		$newsletterHeading = "";

		if (!$this->isLoggedIn()) {
			return $this->permissionDenied();
		}

		// Lag liste over mottakere
        
		if (isset($_SESSION['errors'])){
			$i = 1;
			if (isset($_SESSION['postdata']['rcpts'])) {
				$rc = explode(',',$_SESSION['postdata']['rcpts']);
			}
			if (isset($_SESSION['postdata']['recipients'])) {
			    $rc = explode(',',$_SESSION['postdata']['recipients']);
			    $groupId = $_SESSION['postdata']['groupId'];
			}
		} else if (isset($_GET['recipients'])) {
		    $rc = explode(',',$_GET['recipients']);
		    if (isset($_GET['groupId'])) {
    		    $groupId = intval($_GET['groupId']);
    		}
		}
		if (isset($rc)) {
            if (count($rc) > 1) {
                // pass
            } else if (is_numeric($rc[0])) {
                // pass
            } else {
                if (!$this->isLoggedIn() || !isset($groupId)) {
                    return $this->permissionDenied();
                }
                /*                
                Temp solution
                */
                global $memberdb;
                $group = $memberdb->getGroupById($groupId);
                $members = $group->members;
                
                $guardians = array();
                foreach ($members as $mid) {
                    foreach ($memberdb->getMemberById($mid)->guardians as $g) {
                        $guardians[] = $g;
                    }
                }
	    	    $guardians = array_unique($guardians);
		        $membersAndGuardians = array_merge($members, $guardians);
                switch ($rc[0]) {
                    case 'members':
                        $rc = $members;
                        $rcptMsg = "Medlemmer i ".$group->caption." (totalt ".count($members)." personer)
                            <input type='hidden' name='recipients' value='members'/><input type='hidden' name='groupId' value='$groupId'/>";
                        $newsletterHeading = "Informasjon til medlemmer i ".$group->caption;
                        break;
                    case 'guardians':
                        $rc = $guardians;
                        $rcptMsg = "Foresatte til medlemmer i ".$group->caption." (totalt ".count($guardians)." personer)
                            <input type='hidden' name='recipients' value='guardians'/><input type='hidden' name='groupId' value='$groupId'/>";
                        $newsletterHeading = "Informasjon til foresatte for medlemmer i ".$group->caption;
                        break;
                    case 'members;guardians':
                        $rcptMsg = "Medlemmer i ".$group->caption." og deres foresatte (totalt ".count($membersAndGuardians)." personer)
                            <input type='hidden' name='recipients' value='members;guardians'/><input type='hidden' name='groupId' value='$groupId'/>";
                        $newsletterHeading = "Informasjon til medlemmer i ".$group->caption;
                        $rc = $membersAndGuardians;
                        break;
                    default:
                        return "ukjent mottakerliste";
                }
            }
            if (count($rc) > 1 && empty($this->login_identifier)) {
                return "
                    <div style='display:block; border: 1px solid red; padding: 5px; margin-bottom: 10px; font-size:11px; background: white;'>
                        Så lenge du ikke er logget inn kan du sende til maks én person om gangen.
                    </div>
                ".$this->permissionDenied();
            }
            foreach ($rc as $r) {
                if (!is_numeric($r)) return $this->notSoFatalError("Ugyldig mottakerliste"); 
                if ($memberdb->isUser($r)){ 
        		    if (isset($_SESSION['postdata']['rcpts'])) {
                        if (isset($_SESSION['postdata']['rcpt'.$r]) && ($_SESSION['postdata']['rcpt'.$r] == 'on')){ 
                            $recipients[$r] = 1;
                        } else {
                            $recipients[$r] = 0;                        
                        }
                    } else {
                        $recipients[$r] = 1;
                    }
                } else {
                    return $this->notSoFatalError("Det eksisterer intet medlem med id $r");
                }	
            }
		} else {
			if (empty($this->login_identifier)) {
				return $this->permissionDenied();
			}
		}
		
		if (!empty($replyTo)) {
			if (!is_numeric($replyTo)) return $this->notSoFatalError("Meldingen finnes ikke"); 
			
			$uid = $this->login_identifier;
			$u = $this->table_user;
			$g = $this->table_messages;
			$res = $this->query("SELECT
					$g.id,
					$g.sender,
					$g.sender_email,
					$g.recipients,
					$g.subject
				FROM 
					$g,$u
				WHERE 
					$g.id = $replyTo
					AND $u.message_id = $g.id
					AND $u.owner = $uid"
			);
			$row = $res->fetch_assoc();
			$subject = stripslashes($row['subject']);
			$sender = stripslashes($row['sender']);
			$sender_email = stripslashes($row['sender_email']);
			if (empty($sender)) {
				return $this->notSoFatalError("Denne meldingen ble sendt fra en 
				ikke-innlogget bruker. Du kan dessverre ikke svare på den
				via ".$this->site_name."s mail-system. Du kan isteden svare ved 
				å sende en epost til <a href=\"mailto:$sender_email\">$sender_email</a>.",
				array('logError' => false));
			}
			
			$replyToMsgId = $row['id'];
			$rc = explode(",",stripslashes($row['recipients']));
			$recipients = array(
				$sender => 1
			);
			foreach ($rc as $r) {
				if ($r != $this->login_identifier){
					if ($r != $sender) {
						if (isset($_GET['check_all'])) 
							$recipients[$r] = 1;
						else
							$recipients[$r] = 0;
					}
				}
			}
		} else {
			$replyToMsgId = 0;
		}
		
		if (!empty($recipients)) {
			$rcptCode = array();
			$hiddenMembers = array();
			if (empty($this->login_identifier)) {
				$userOpts = call_user_func($this->get_useroptions, $this, 'receive_guestmail', array_keys($recipients));
				foreach ($userOpts as $u => $receive) {
					if (!$receive) $hiddenMembers[] = $u;
				}
			}
			foreach ($recipients as $r => $checked){
				$m = call_user_func($this->lookup_member,$r);
				if (in_array($r,$hiddenMembers)) {
					return "
						<div style='display:block; border: 1px solid red; padding: 5px; font-size:11px; background: white;'>
							".$m->fullname." kunne ikke legges til som mottaker, fordi han/hun ikke ønsker e-post fra ikke-innloggede brukere.
						</div>
					";
				} else {
				    if (isset($rcptMsg)) {
						array_push($rcptCode,"
							<input type='hidden' name='rcpt$r' id='rcpt$r' value='on' />
						");				    
				    } else if (count($recipients) == 1) {
						array_push($rcptCode,"
							<label for='rcpt$r' style='display:block;float:left;width:200px;'>
								<input type='hidden' name='rcpt$r' id='rcpt$r' value='on' />
								".$m->fullname."
							</label>
						");
					} else {
						$c = $checked ? " checked='checked'" : "";
						array_push($rcptCode,"
							<label for='rcpt$r' style='display:block;float:left;width:200px;'>
								<input type='checkbox' name='rcpt$r' id='rcpt$r'$c />
								".$m->fullname."
							</label>
						");
					}
				}
			}
			if (empty($rcptCode)) {
				$recipients = array();
			} else {
			    if (isset($rcptMsg)) $rcptCode = $rcptMsg;
				else $rcptCode = "<input type='hidden' name='rcpts' value='".implode(",",array_keys($recipients))."' />".implode("",$rcptCode);
			}
		}
		if (empty($recipients)) {
			$hiddenMembers = array();
			if (empty($this->login_identifier)) {
				$userOpts = call_user_func($this->get_useroptions,$this, 'receive_guestmail');
				foreach ($userOpts as $u => $receive) {
					if (!$receive) $hiddenMembers[] = $u;
				}
			} else {
				$hiddenMembers[] = $this->login_identifier;
			}
			$rcptCode = $memberdb->generateMemberSelectBox("recipient1",-1,$hiddenMembers).
				" <span id='rcpt2'><a href='#' id='addRcpt_2'>Legg til</a></span>";
		}
		
		$melding = ""; 
		$sendername = ""; 
		$sendermail = ""; 
		$attachments = array();
		
		if (isset($_SESSION['errors'])){
			
			$erroutp = "<ul>";
			foreach ($_SESSION['errors'] as $e){
				if (in_array($e, array_keys($this->errorMessages))) 
					$erroutp .= "<li>".$this->errorMessages[$e]."</li>\n";
				else
					$erroutp .= "<li>".$e."</li>\n";
			}
			$erroutp .= "</ul>";
			$erroutp = $this->notSoFatalError($erroutp,array('logError'=>false,'customHeader'=>'Din melding ble <strong>ikke</strong> sendt pga. følgende:'));
			$postdata = $_SESSION['postdata'];
			if (isset($postdata['emne'])) $subject = htmlspecialchars($postdata['emne']);
			if (isset($postdata['melding'])) $melding = htmlspecialchars($postdata['melding']);
			if (isset($postdata['sendarnamnj'])) $sendername = htmlspecialchars($postdata['sendarnamnj']);
			if (isset($postdata['sendaradress'])) $sendermail = htmlspecialchars($postdata['sendaradress']);
			if (isset($postdata['newsletterHeading'])) $newsletterHeading = htmlspecialchars($postdata['newsletterHeading']);
			if (isset($postdata['groupId'])) $groupId = htmlspecialchars($postdata['groupId']);
			$attachments = $_SESSION['attachments'];
			unset($_SESSION['errors']);
			unset($_SESSION['postdata']);
			unset($_SESSION['attachments']);
		} else if ((count(array_keys($recipients)) > 1) && (!$this->allow_sendGroupMail)){
			$erroutp = "<p class='cal_notice'>NB! Du må være innlogget for å sende e-post til mer enn en adresse om gangen. Du må derfor enten krysse vekk så det kun står en adresse igjen eller logge inn. Ellers vil e-posten ikke bli sendt.</p>";
		} else {
			$erroutp = "";
		}
		if (isset($_SESSION['msgcenter_infomsg'])){
			$infomsg = '<p class="info">'.$_SESSION['msgcenter_infomsg'].'</p>';
			unset($_SESSION['msgcenter_infomsg']);
		} else {
			$infomsg = "";
		}

		$r1a = array(); $r2a = array();
		if (empty($replyTo)) {
			$r1a[]  = "%header%";						$r2a[]  = '<h2>'.$this->label_newmessage.'</h2>';
		} else {
			$r1a[]  = "%header%";						$r2a[]  = '<h2>'.$this->label_reply.'</h2>';
		}
		
		if (!empty($attachments)) {
			$attachment = " <div id='att'>";
			$i = 0;
			foreach ($attachments as $id => $obj) {
				$i++;
				$attachment .= '<div id="att'.$i.'" style="border: 1px solid #ddd; padding:4px;"><input type="hidden" name="vedlegg'.$id.'" value="'.$id.'" /><table><tr><td><img src="'.$this->image_dir.'file.png" /></td><td> '.$obj["friendly"].' ('.round($obj["size"]/1024).' kB) &nbsp; &nbsp;</td><td><a href="#" onclick="removeAttachment('.$i.'); return false;"><img src="'.$this->image_dir.'delete.png" border="0" /> Fjern</a></td></tr></table></div>';
			}
			$i++;
			$attachment .= " <a id='addAttLink' href='#' class='icn' style='background-image:url(/images/icns/attach.png);'>Legg ved fil</a>";		
			$attachment .= "</div>";
		} else {
			$attachment = " <div id='att'><a id='addAttLink' href='#' class='icn' style='background-image:url(/images/icns/attach.png);'>Legg ved fil</a></div>";
		}
		if (empty($this->login_identifier)) {
			$attachment = "<em>Logg inn for å sende vedlegg</em>";
		}

		$r1a[] = "%label_to%";						$r2a[] = $this->label_to;
		$r1a[] = "%label_from%";					$r2a[] = $this->label_from;
		$r1a[] = "%label_subject%";					$r2a[] = $this->label_subject;
		$r1a[] = "%label_yourname%";				$r2a[] = $this->label_yourname;
		$r1a[] = "%label_youremail%";				$r2a[] = $this->label_youremail;
		$r1a[] = "%label_body%";					$r2a[] = $this->label_body;
		$r1a[] = "%label_attachments%";				$r2a[] = $this->label_attachments;
		$r1a[] = "%label_sendmsginfo%";				$r2a[] = $this->label_sendmsginfo;
		$r1a[] = "%label_sendmail%";				$r2a[] = $this->label_sendmail;
		$r1a[] = "%posturl%";						$r2a[] = $this->generateCoolUrl("/sendmessage");
		$r1a[] = "%recipients%";					$r2a[] = $rcptCode;
		$r1a[] = "%begincommentifloggedin%";		$r2a[] = (!empty($this->login_identifier) ? "<!--" : "");
		$r1a[] = "%endcommentifloggedin%";			$r2a[] = (!empty($this->login_identifier) ? "-->" : "");
		$r1a[] = "%vis_emne%";						$r2a[] = $replyTo ? 'display:none;' : '';
		$r1a[] = "%emne%";							$r2a[] = $subject;
		$r1a[] = "%vis_vedlegg%";					$r2a[] = true;
		$r1a[] = "%vedlegg%";						$r2a[] = $attachment;
		$r1a[] = "%sendername%";					$r2a[] = $sendername;
		$r1a[] = "%sendermail%";					$r2a[] = $sendermail;
		$r1a[] = "%melding%";						$r2a[] = $melding;
		$r1a[] = "%errors%";						$r2a[] = $erroutp;
		$r1a[] = "%infomsg%";						$r2a[] = $infomsg;
		$r1a[] = "%replyto%";						$r2a[] = $replyToMsgId;
		$r1a[] = "%maxfilesize%";					$r2a[] = $this->attachment_maxsize;
		$r1a[] = "%maxfilesize_kb%";				$r2a[] = round($this->attachment_maxsize/1024);
		$r1a[] = "%cancelbtn%";						$r2a[] = "";
		$r1a[] = "%vis_nyhetsbrev%";				$r2a[] = ($newsletterHeading == "") ? "display:none;" : "";
		$r1a[] = "%newsletterHeading%";				$r2a[] = $newsletterHeading;

		$outp = str_replace($r1a, $r2a, $this->schema_template);
		
		return '
		<script type="text/javascript"> 
		//<![CDATA[
		
		jQuery(document).ready(function(){
            jQuery("#addAttLink").click(addAttachment);
            jQuery("[id^=addRcpt_]").click(addRecipient);
        });

		function addRecipient(event){
			event.preventDefault();
			item = event.delegateTarget.id;
			var no = item.substr(item.indexOf("_")+1);
			//console.info("Adding rcpt "+no);
		
			var pars = new Array();
			pars.push("no="+no);
			pars = pars.join("&");

			jQuery("#rcpt"+no).html("Vent litt...");
			jQuery.ajax({
			    url: "'.$this->generateCoolURL('/add_recipient/','noprint=true').'",
			    dataType: "html",
			    data: pars,
			    type: "POST",
			    success: function(t){ 
				    jQuery("#rcpt"+no).html(t);	
                    jQuery("#rmRcpt_"+no).click(removeRecipient);
                    jQuery("#addRcpt_"+(no-0+1)).click(addRecipient);
			    }
			})
		}

		function removeRecipient(event){
			event.preventDefault();
			item = event.delegateTarget.id;
			var no = item.substr(item.indexOf("_")+1);
			//console.info("Removing rcpt "+no);

			var pars = new Array();
			pars.push("no="+no);
			pars = pars.join("&");

			jQuery("#rcpt"+no).html("Vent litt...");
			jQuery.ajax({
			    url: "'.$this->generateCoolURL('/remove_recipient/','noprint=true').'",
			    dataType: "html",
			    data: pars,
			    type: "POST",
			    success: function(t){ 
				    jQuery("#rcpt"+no).html(t);
                    jQuery("#rmRcpt_"+(no-1)).click(removeRecipient);
                    jQuery("#addRcpt_"+no).click(addRecipient);
			    }
			});
		}
		
		attachments = 0;
		function addAttachment(event) {
		    event.preventDefault();
			attachments = attachments + 1;
			no = attachments
			//console.info("Adding attachment no. "+no);
			
			var newDiv = "<div id=\"att"+no+"\" style=\"border: 1px solid #ddd; padding: 4px;\"> \
			  <input type=\"file\" name=\"vedlegg"+no+"\" /> \
			  <a id=\"removeAttLink_"+no+"\" href=\"#\" class=\"icn\" style=\"background-image:url(/images/delete.png);\">Fjern</a> \
			  </div><a id=\"addAttLink\" href=\"#\" class=\"icn\" style=\"background-image:url(/images/icns/attach.png);\">Legg ved enda en fil</a>";
						
			jQuery("#addAttLink").remove();
			jQuery("#att").append(newDiv);
            jQuery("#addAttLink").click(addAttachment);
			jQuery("#removeAttLink_"+no).click(removeAttachment);
		}

		function removeAttachment(event){
			event.preventDefault();
			item = event.delegateTarget.id;
			var no = item.substr(item.indexOf("_")+1);
			//console.info("Removing attachment with id: "+no);
			jQuery("#att"+no).remove();
		}
		
		//]]>
		</script>
		
		'.$outp;
		
	}
	
	function simpleReplyForm($replyTo) {
	
		if (!is_numeric($replyTo)) return $this->notSoFatalError("Meldingen finnes ikke"); 

		$melding = ""; 
		$sendername = ""; 
		$sendermail = ""; 
		$attachments = array();
		$erroutp = "";
		
		$uid = $this->login_identifier;
		$u = $this->table_user;
		$g = $this->table_messages;
		$res = $this->query("SELECT
				$g.id,
				$g.sender,
				$g.sender_email,
				$g.recipients,
				$g.subject
			FROM 
				$g,$u
			WHERE 
				$g.id = $replyTo
				AND $u.message_id = $g.id
				AND $u.owner = $uid"
		);
		$row = $res->fetch_assoc();
		$subject = stripslashes($row['subject']);
		$sender = stripslashes($row['sender']);
		$sender_email = stripslashes($row['sender_email']);
		if (empty($sender)) {
			return $this->notSoFatalError("Denne meldingen ble sendt fra en 
			ikke-innlogget bruker. Du kan dessverre ikke svare på den
			via ".$this->site_name."s mail-system. Du kan isteden svare ved 
			å sende en epost til <a href=\"mailto:$sender_email\">$sender_email</a>.",
			array('logError' => false));
		}
		
		$replyToMsgId = $row['id'];
		$rc = explode(",",stripslashes($row['recipients']));
		$recipients = array(
			$sender => 1
		);
		foreach ($rc as $r) {
			if ($r != $this->login_identifier){
				if ($r != $sender) {
					$recipients[$r] = 0;
				}
			}
		}
			
		if (!empty($recipients)) {
			$rcptCode = array();
			$hiddenMembers = array();

			foreach ($recipients as $r => $checked){
				$m = call_user_func($this->lookup_member,$r);
				if (count(array_keys($recipients)) == 1) {
					array_push($rcptCode,"
						<label for='rcpt".$replyTo."_$r' style='display:block;float:left;width:200px;'>
							<input type='hidden' name='rcpt$r' id='rcpt".$replyTo."_$r' value='on' />
							".$m->fullname."
						</label>
					");
				} else {
					$c = $checked ? " checked='checked'" : "";
					array_push($rcptCode,"
						<label for='rcpt".$replyTo."_$r' style='display:block;float:left;width:200px;'>
							<input type='checkbox' name='rcpt$r' id='rcpt".$replyTo."_$r'$c />
							".$m->fullname."
						</label>
					");
				}
			}
			$rcptCode = "<input type='hidden' name='rcpts' id='rcpts_".$replyTo."' value='".implode(",",array_keys($recipients))."' />".implode("",$rcptCode);
			
		}
		
		$r1a = array(); $r2a = array();
		$r1a[]  = "%header%";						$r2a[]  = '<div style="padding:5px 5px 10px 25px;font-weight:bold;background:url(/images/icns/email.png) 3px 5px no-repeat;">'.$this->label_reply.'</div>';
		
		if (!empty($attachments)) {
			$attachment = " <div id='att_".$replyTo."'>";
			$i = 0;
			foreach ($attachments as $id => $obj) {
				$i++;
				$attachment .= '<div id="att_'.$replyTo.'_'.$i.'" style="border: 1px solid #ddd; padding:4px;"><input type="hidden" name="vedlegg'.$id.'" value="'.$id.'" /><table><tr><td><img src="'.$this->image_dir.'file.png" /></td><td> '.$obj["friendly"].' ('.round($obj["size"]/1024).' kB) &nbsp; &nbsp;</td><td><a href="#" onclick="removeAttachment('.$i.'); return false;"><img src="'.$this->image_dir.'delete.png" border="0" /> Fjern</a></td></tr></table></div>';
			}
			$i++;
			$attachment .= " <a id='addAttLink_$replyTo_$i' href='#' class='icn' style='background-image:url(/images/icns/attach.png);'>Legg ved fil</a>";		
			$attachment .= "</div>";
		} else {
			$attachment = " <div id='att_".$replyTo."'><a id='addAttLink_$replyTo' href='#' class='icn' style='background-image:url(/images/icns/attach.png);'>Legg ved fil</a></div>";
		}
		if (empty($this->login_identifier)) {
			$attachment = "<em>Logg inn for å sende vedlegg</em>";
		}
		$r1a[] = "%label_to%";						$r2a[] = $this->label_to;
		$r1a[] = "%label_from%";					$r2a[] = $this->label_from;
		$r1a[] = "%label_subject%";					$r2a[] = $this->label_subject;
		$r1a[] = "%label_yourname%";				$r2a[] = $this->label_yourname;
		$r1a[] = "%label_youremail%";				$r2a[] = $this->label_youremail;
		$r1a[] = "%label_body%";					$r2a[] = $this->label_body;
		$r1a[] = "%label_attachments%";				$r2a[] = $this->label_attachments;
		$r1a[] = "%label_sendmsginfo%";				$r2a[] = $this->label_sendmsginfo;
		$r1a[] = "%label_sendmail%";				$r2a[] = $this->label_sendmail;
		$r1a[] = "%posturl%";						$r2a[] = $this->generateCoolUrl("/sendmessage");
		$r1a[] = "%recipients%";					$r2a[] = $rcptCode;
		$r1a[] = "%begincommentifloggedin%";		$r2a[] = (!empty($this->login_identifier) ? "<!--" : "");
		$r1a[] = "%endcommentifloggedin%";			$r2a[] = (!empty($this->login_identifier) ? "-->" : "");
		$r1a[] = "%vis_emne%";						$r2a[] = $replyTo ? 'display:none;' : '';
		$r1a[] = "%emne%";							$r2a[] = $subject;
		$r1a[] = "%vis_vedlegg%";					$r2a[] = true;
		$r1a[] = "%vedlegg%";						$r2a[] = $attachment;
		$r1a[] = "%sendername%";					$r2a[] = $sendername;
		$r1a[] = "%sendermail%";					$r2a[] = $sendermail;
		$r1a[] = "%melding%";						$r2a[] = $melding;
		$r1a[] = "%errors%";						$r2a[] = $erroutp;
		$r1a[] = "%replyto%";						$r2a[] = $replyToMsgId;
		$r1a[] = "%vis_nyhetsbrev%";				$r2a[] = "display:none;";
		$r1a[] = "%maxfilesize%";					$r2a[] = $this->attachment_maxsize;
		$r1a[] = "%maxfilesize_kb%";				$r2a[] = round($this->attachment_maxsize/1024);
		$r1a[] = "%cancelbtn%";						$r2a[] = '<input type="button" value="     Avbryt     " onclick="cancelReply('.$replyTo.');" />';
		$outp = str_replace($r1a, $r2a, $this->schema_template);
		
		return $outp;
	
	}
	
	function addRecipient() {
		global $memberdb;
		if (isset($_POST['no']) && is_numeric($_POST['no'])) 
			$no = intval($_POST['no']);
		else
			$no = 1;
		$nn = $no+1;
		$hiddenMembers = array();
		if (empty($this->login_identifier)) {
			$userOpts = call_user_func($this->get_useroptions,$this, 'receive_guestmail');
			foreach ($userOpts as $u => $receive) {
				if (!$receive) $hiddenMembers[] = $u;
			}
		}
		header("Content-Type: text/html; charset=utf-8"); 
		if (empty($this->login_identifier)) {
			print "<br /><em>Kun tilgjengelig for innloggede brukere</em>";		
		} else {
			print "<br />".$memberdb->generateMemberSelectBox("recipient$no",-1,$hiddenMembers).
			"<span id='rcpt$nn'>
			    <a href='#' id='rmRcpt_$no'>Fjern</a> | 
				<a href='#' id='addRcpt_$nn'>Legg til</a></span>";
		}
		exit();
	}
	
	function removeRecipient() {
		global $memberdb;
		$no = intval($_POST['no']);
		$nn = $no-1;
		header("Content-Type: text/html; charset=utf-8"); 
		if ($nn > 1) 
			print "<a href='#' id='rmRcpt_$nn'>Fjern</a> | ";
		print "<a href='#' id='addRcpt_$no'>Legg til</a>";
		exit();
	}
	
	function file_extension($f){
		$t = explode(".",$f);
		return array_pop($t);
	}	
	
	function get_mime_type($f) {		
		$mime = '';
		if (function_exists('finfo_file')) { // PHP >= 5.3.0
			$finfo = new finfo(FILEINFO_MIME_TYPE);
			if ($finfo === false) {
				$this->logDebug("[messagecenter] finfo could not open magic database!");				
			} else {
				$mime = $finfo->file($f);
			}
			$this->logDebug("[messagecenter] Mimetype for '".basename($f)."' from finfo: $mime");
		} elseif (function_exists('mime_content_type')) { // PHP 4 >= 4.3.0, PHP 5, deprecated!
    		$mime = mime_content_type($f);			
			$this->logDebug("[messagecenter] Mimetype for '".basename($f)."' from mime_content_type: $mime");
		} else {
			$mime = trim(exec('file -bi ' . escapeshellarg($f), $output, $return_status));
			if ($return_status != 0) {
				$this->logDebug("[messagecenter] Mimetype for '".basename($f)."' could not be determined by any mean!");				
			} else {
				$this->logDebug("[messagecenter] Mimetype for '".basename($f)."' from 'file' cmd: $mime");
			}
		}
		$mime = explode(";",$mime); $mime = $mime[0]; // We may get something like "image/png; charset=binary"
		return $mime;
	}
	
	function is_image($filename) {
		$mime = $this->get_mime_type($filename);
		switch ($mime) {
			case 'image/jpeg':
			case 'image/gif':
			case 'image/png':
				return true;
			default:
				return false;
		}
	}
	
	function make_thumb($filename) {
		if (!$this->imginstanceinited) $this->initializeImagesInstance();
		$this->imginstanceinited = true;
		$this->imginstance->createThumbnail($filename, true, 140, 140);
	}
	
	function sendMessage() {
	
	    // Lag liste over mottakere
	    
	    
        if (isset($_POST['recipients'])) {
            global $memberdb;
            $rc = $_POST['recipients'];
            $groupId = $_POST['groupId'];

            $group = $memberdb->getGroupById($groupId);
            $members = $group->members;
            
            $guardians = array();
            foreach ($members as $mid) {
                foreach ($memberdb->getMemberById($mid)->guardians as $g) {
                    $guardians[] = $g;
                }
            }
            $guardians = array_unique($guardians);
            $membersAndGuardians = array_merge($members, $guardians);
            switch ($rc) {
                case 'members':
                    $recipients = $members;
                    $newsletterHeading = "Informasjon til medlemmer i ".$group->caption;
                    break;
                case 'guardians':
                    $recipients = $guardians;
                    $newsletterHeading = "Informasjon til foresatte for medlemmer i ".$group->caption;
                    break;
                case 'members;guardians':
                    $recipients = $membersAndGuardians;
                    $newsletterHeading = "Informasjon til medlemmer i ".$group->caption;
                    break;
                default:
                    $this->fatalError("ukjent mottakerliste");
            }
	    } else {    

            $recipients = array();
            if (isset($_POST['recipient1'])) {
                $no = 1;
                while (isset($_POST['recipient'.$no])) {
                    $r = intval($_POST['recipient'.$no]);
                    if ($r > 0) {
                        $recipients[] = $r;
                    }
                    $no++;
                }
            } else if (isset($_POST['rcpts'])) {
                $rc = explode(",",$_POST['rcpts']);
                foreach ($rc as $r) {
                    if (is_numeric($r)) {
                        if (isset($_POST["rcpt$r"]) && ($_POST["rcpt$r"] == 'on')) {
                            $recipients[] = $r;
                        }
                    }
                }
            } else {
                $this->fatalError("Ingen mottakere ble spesifisert");
            }
        }
		$recipients = array_unique($recipients);
		
		$errors = array();
		
		$subject = $_POST['emne'];
		$newsletterHeading = $_POST['newsletterHeading'];
		$body = $_POST['melding'];
		$body = str_replace("\r\n","\n",$body);
		$body = str_replace("\r","\n",$body);
		$timestamp = time();
		$attachments = array();
		
		if (!empty($this->login_identifier)) {

    	    // Vedlegg

			foreach ($_POST as $n => $v) {
				if (strpos($n,'vedlegg') === 0) {
					$id = intval($v);
					$res = $this->query("SELECT filename,filesize,friendlyname,mime,uploader FROM $this->table_attachments WHERE id=$id");
					if ($res->num_rows == 1) {
						$row = $res->fetch_assoc();
						$attachments[$id] = array(
							'file' => $row['filename'], 
							'friendly' => $row['friendlyname'], 
							'mime' => $row['mime'], 
							'size' => $row['filesize']
						);
					}
				}
			}

			foreach ($_FILES as $identifier => $fileobject) {
			
				
				if ($fileobject['error'] == UPLOAD_ERR_NO_FILE) {
					
					// No file was selected. Ignore...
					
				} else if ($fileobject['error'] != UPLOAD_ERR_OK) {
				
					switch($fileobject['error']) {
						case UPLOAD_ERR_INI_SIZE:
						case UPLOAD_ERR_FORM_SIZE:
							$errors[] = "Filen \"".$fileobject['name']."\" er for stor til å legges ved! Maks størrelse er ".round($this->attachment_maxsize/1024)." kB.";
							break;
						default: 
							$errors[] = "Filen \"".$fileobject['name']."\" kunne ikke legges ved pga. en ukjent feil. Feilen kan skyldes midlertidige driftsproblemer.";
							break;
					}
	
					// error handling....
	
				} else if ($fileobject['size'] > $this->attachment_maxsize) {
				
					$errors[] = "Filen \"".$fileobject['name']."\" er for stor til å legges ved! Filen er ".round($fileobject['size']/1024)." kB, mens maks størrelse er ".round($this->attachment_maxsize/1024)." kB.";
				
				} else {
	
					$tempname = $fileobject['tmp_name'];
					$realname = $fileobject['name'];					
					
					$fileExt = strtolower($this->file_extension($realname));
					if ($fileExt == "jpeg") $fileExt = "jpg";
		
					$filename = "att".rand(1,999999).".".$fileExt;
					$target = $this->attachment_dir."/".$filename;
					while (file_exists($target)){
						$filename = "att".rand(1,999999).".".$fileExt;
						$target = $this->attachment_dir."/".$filename;
					}
					if (is_uploaded_file($tempname)){
						if (!move_uploaded_file($tempname, $target)) {
							$this->addToErrorLog("Kunne ikke lagre meldingsvedlegg i ".$target);
						} else {
							$uploader = $this->login_identifier;
							$friendlyname = $realname;
							$filesize = $fileobject['size'];							
							$mime = addslashes($this->get_mime_type($target));
							$this->query("INSERT INTO $this->table_attachments (filename,filesize,friendlyname,mime,uploader)
								VALUES(\"$filename\",\"$filesize\",\"$friendlyname\",\"$mime\",\"$uploader\")");
							if ($this->affected_rows() == 0) {
								$this->fatalError("Query failed: INSERT INTO $this->table_attachments (filename,filesize,friendlyname,mime,uploader)
								VALUES(\"$filename\",\"$filesize\",\"$friendlyname\",\"$mime\",\"$uploader\"");
							}
							$att_id = $this->insert_id();
							$attachments[$att_id] = array('file' => $filename, 'friendly' => $friendlyname, 'mime' => $mime, 'size' => $filesize);
							
							if ($this->is_image($target)) {
								$this->make_thumb($target);
							}
						}
					}
				}
			}
		}
		
		if (!empty($this->login_identifier)) {
			$sender = $this->login_identifier;
			$u = call_user_func($this->lookup_member,$sender);
			$sender_name = $u->fullname;
			$sender_email = $u->email;
		} else {
			$sender = 0;
			$sender_name = addslashes($_POST['sendarnamnj']);
			$sender_email = addslashes($_POST['sendaradress']);
			if (empty($sender_name)) $errors[] = 'empty_name';			
			if (empty($sender_email)) $errors[] = 'empty_email';				
			if (empty($sender_email)) $errors[] = 'empty_email';				
			if (strip_tags($sender_name) != $sender_name) $errors[] = 'html_name';
			if (strip_tags($sender_email) != $sender_email) $errors[] = 'html_email';
		}	
		if (empty($subject)) $errors[] = 'empty_subject';				
		if (empty($body)) $errors[] = 'empty_body';				
		if (strip_tags($subject) != $subject) $errors[] = 'html_subject';
		if (strip_tags($body) != $body) $errors[] = 'html_body';
		if (count($recipients) <= 0) $errors[] = 'no_recipients';		
		if (!$this->isValidEmail($sender_email)) $errors[] = 'invalid_email';			

		$replyto = 0;
		if (isset($_POST['replyto']) && is_numeric($_POST['replyto'])) $replyto = $_POST['replyto'];
		
		if (count($errors) > 0) {
			$_SESSION['errors'] = array_unique($errors);
			$_SESSION['postdata'] = $_POST;
			if (!isset($_SESSION['postdata']['rcpts']) && empty($newsletterHeading)) {
    			$_SESSION['postdata']['rcpts'] = implode(',',$recipients);
    			foreach ($recipients as $r) {
    			    $_SESSION['postdata']['rcpt'.$r] = 'on';    			
    			}
    		}
			$_SESSION['attachments'] = $attachments;
			if ($replyto > 0) 
				$this->redirect($this->generateCoolURL("/reply/$replyto"),"Meldingen ble ikke sendt pga. et eller flere problemer.",'error');
			else
				$this->redirect($this->generateCoolURL("/newmessage"),"Meldingen ble ikke sendt pga. et eller flere problemer.",'error');
		}

		
		if (empty($subject)) $this->fatalError("Tomt emne");
		if (empty($body)) $this->fatalError("Tom melding");
		
		$rcpts = implode(",",$recipients);
		$owners = $recipients;
		$owners[] = $this->login_identifier;
		
		if (!empty($newsletterHeading)) {
		    $isnewsletter = 1;
	    	$html_body = $body;
		} else {
		    $isnewsletter = 0;
    	    // Klikkbare lenker
	    	$html_body = $this->makeHtmlUrls($body,60,"...");
		
		}
				
		if ($replyto > 0) {
			$res = $this->query("SELECT thread FROM $this->table_messages WHERE id=$replyto");
			if ($res->num_rows != 1) {
				$this->notSoFatalError("Meldingen du svarte på eksisterer ikke");
				print "<pre>";
				print_r($_POST);
				print "</pre>";				
				exit();
			}
			$row = $res->fetch_assoc();
			$thread = $row['thread'];
		
			$this->query("INSERT INTO $this->table_messages 
				(thread, sender, sender_name, sender_email, recipients, subject, body, timestamp, replyto)
				VALUES
				($thread, \"$sender\",\"$sender_name\",\"$sender_email\",\"$rcpts\",\"".addslashes($subject)."\",\"".addslashes($html_body)."\",\"$timestamp\",\"$replyto\")"
			);
			if ($this->affected_rows() == 0) {
				$this->fatalError("Message could not be saved in db!");
			}
			$message_id = $this->insert_id();
		} else {
			$this->query("INSERT INTO $this->table_messages 
				(sender, sender_name, sender_email, recipients, subject, body, timestamp, replyto, isnewsletter, newsletterheading)
				VALUES
				(\"$sender\",\"$sender_name\",\"$sender_email\",\"$rcpts\",\"".addslashes($subject)."\",\"".addslashes($html_body)."\",\"$timestamp\",\"$replyto\", \"$isnewsletter\", \"".addslashes($newsletterHeading)."\")"
			);
			if ($this->affected_rows() == 0) {
				$this->fatalError("Message could not be saved in db!");
			}
			$message_id = $this->insert_id();
			$this->query("UPDATE $this->table_messages SET thread='$message_id' WHERE id=$message_id");		
		}
		if (!empty($attachments)) {
			foreach ($attachments as $a_id => $obj) {
				$this->query("UPDATE $this->table_attachments SET message_id='$message_id' WHERE id=$a_id");						
			}
		}
		
		$owners = array_unique($owners);
		foreach ($owners as $owner) {
			$is_read = ($owner == $this->login_identifier) ? 1 : 0; 
			if ($owner == $this->login_identifier) {
				$this->query("INSERT INTO $this->table_user 
					(message_id,owner,is_read)
					VALUES
					('$message_id','$owner',1)");			
			} else {
				$this->query("INSERT INTO $this->table_user 
					(message_id,owner)
					VALUES
					('$message_id','$owner')");
				}
		}
		
		$user_mail_settings = call_user_func($this->get_useroptions,$this, 'mail_incoming', $recipients);
		
		$allerrors = array();
		$numqueued = 0;
		foreach ($recipients as $r) {
			if ($user_mail_settings[$r] == '1') {
				$errors = $this->queueMail($r,$sender,$sender_name,$sender_email,$subject,$body,$message_id,$attachments,$newsletterHeading);
				if (count($errors)>0) {
				    $allerrors[] = array(
				        'rcpt' => $r,
				        'errors' => $errors
				    );
				} else {
				    $numqueued++;
				}
			}
		}
		
		$_SESSION['message_sent_to'] = $recipients;
		$_SESSION['message_status'] = array(
		    'numqueued' => $numqueued,
		    'errors' => $allerrors
		);
		
		if (count($recipients) == 1)
			$this->addToActivityLog("sendte en melding til ".call_user_func($this->make_memberlink,$recipients[0]));
		else
			$this->addToActivityLog("sendte en melding til ".count($recipients)." mottakere");
		$this->redirect($this->generateCoolUrl("/sender/$message_id"));
		
	}
	/*
	X-Spam-Report: 1.9 hits, 4.0 required;
	* -0.5 ALL_TRUSTED            Passed through trusted hosts only via SMTP
	*  1.8 HTML_IMAGE_ONLY_20     BODY: HTML: images with 1600-2000 bytes of words
	*  0.0 HTML_MESSAGE           BODY: HTML included in message
	*  0.6 HTML_SHORT_LINK_IMG_3  HTML is very short with a linked image

	X-Spam-Report: 2.0 hits, 4.0 required;
	* -0.5 ALL_TRUSTED            Passed through trusted hosts only via SMTP
	*  0.0 HTML_MESSAGE           BODY: HTML included in message
	*  2.5 HTML_IMAGE_ONLY_16     BODY: HTML: images with 1200-1600 bytes of words
	*/
	function queueMail($recipient,$sender,$sender_name,$sender_email,$subject,$body,$message_id,$attachments,$newsletterHeading) {
		global $memberdb;
		$recipient = call_user_func($this->lookup_member, $recipient);
        $rcpt_name = $recipient->fullname;
        $rcpt_email = $recipient->email;

		$valid = $this->query("SELECT * FROM bg_mailnotworking WHERE addr=\"".addslashes($rcpt_email)."\"");
		if ($valid->num_rows == 1) {
		    return array("email_notworking");
		}
		
		$bodyHtml = nl2br($body);
		$bodyHtml = $this->makeHtmlUrls($bodyHtml,60,"...");

		$url_root = "https://".$_SERVER['SERVER_NAME'].ROOT_DIR."/";
		$url_reply = "https://".$_SERVER['SERVER_NAME'].$this->generateCoolUrl("/reply/$message_id");
        
		$headerImg = rtrim($url_root,"/")."/".trim($this->image_dir,"/")."/email_header3.png";
		$url_root = "https://".$_SERVER['SERVER_NAME'].ROOT_DIR."/";

		$r1a = array(); $r2a = array();
		$r1a[] = '%sender_name%';		$r2a[] = $sender_name;
		$r1a[] = '%sender_email%';		$r2a[] = $sender_email;
		$r1a[] = '%rcpt_name%';		    $r2a[] = $rcpt_name;
		$r1a[] = '%rcpt_email%';		$r2a[] = $rcpt_email;
		$r1a[] = '%subject%';		    $r2a[] = $subject;
		$r1a[] = '%message_plain%';		$r2a[] = $body;
		$r1a[] = '%message_html%';		$r2a[] = $bodyHtml;
		$r1a[] = '%site_name%';			$r2a[] = $this->site_name;
		$r1a[] = '%url_root%';			$r2a[] = $url_root;
		$r1a[] = '%url_reply%';			$r2a[] = $url_reply;
		$r1a[] = '%newsletter_name%';   $r2a[] = $newsletterHeading;
		$r1a[] = "%header_image%";		$r2a[] = $headerImg;
		$r1a[] = "%url_main%";			$r2a[] = $url_root;
		$r1a[] = "%timestamp%";			$r2a[] = strftime('%A %e. %B %Y',time());

		$r1a[] = '%ip%';				$r2a[] = $_SERVER['REMOTE_ADDR'];
		
		if (!empty($newsletterHeading)) {
		    // Meldingen er et nyhetsbrev
		    if (!$this->isLoggedIn()) { print $this->permissionDenied(); exit(); }

			$plainBody = str_replace($r1a, $r2a, file_get_contents($this->template_dir.$this->template_newsletter_plain));
			$htmlBody = str_replace($r1a, $r2a, file_get_contents($this->template_dir.$this->template_newsletter_html));
		
		} else if ($this->isLoggedIn()){
			
			$plainBody = str_replace($r1a, $r2a, file_get_contents($this->template_dir.$this->template_safe_plain));
			$htmlBody = str_replace($r1a, $r2a, file_get_contents($this->template_dir.$this->template_safe_html));

		} else {

			$plainBody = str_replace($r1a, $r2a, file_get_contents($this->template_dir.$this->template_anon_plain));
			$htmlBody = str_replace($r1a, $r2a, file_get_contents($this->template_dir.$this->template_anon_html));
			
		}
		
		/*
			We must minimize the use of icons to prevent a high HTML_IMAGE_ONLY spam score..
			
			<img src="https://www.18bergen.org/images/info.png" alt="Info" border="0" style="float:left; padding:3px; padding-bottom: 30px;" />
			style="background:url(https://www.18bergen.org/images/reply3.gif) left no-repeat;padding-left:18px;
		*/
				
		// Send mail
		$mail = array(
		    'sender_name' => $sender_name,
		    'sender_email' => $sender_email,
		    'rcpt_name' => $rcpt_name,
		    'rcpt_email' => $rcpt_email,
		    'subject' => $subject,
		    'plain_body' => $plainBody,
		    'html_body' => $htmlBody,
		    'attachments' => array()
		);
		if (!empty($attachments)) {
			foreach ($attachments as $f) {
			    $mail['attachments'][] = $f['file'];
			}
		}
		
		$mailer = $this->initialize_mailer();
		$res = $mailer->add_to_queue($mail);
		if (empty($res['errors'])) {
			$this->query("UPDATE $this->table_messages SET mailqueue_id=".$res['id']." WHERE id=$message_id");						
		}
		return $res['errors'];
	}
	
	function printQueue($message_id) {
	    $message_id = intval($message_id);
		$res = $this->query("SELECT thread FROM $this->table_messages where id=$message_id");
		$row = $res->fetch_assoc();
		$thread_id = intval($row['thread']);
		$mailer = $this->initialize_mailer();
		return $mailer->printQueue($this->generateCoolUrl("/send"), $this->generateCoolUrl("/readthread/$thread_id"));
	}
    
    function ajaxSendFromQueue() {
		$mailer = $this->initialize_mailer();
		$mailer->ajaxSendFromQueue($this->attachment_dir.'/');
	}
	
	function saveNewsletter($recipients, $subject, $body) {

		if (empty($this->login_identifier)) {
			$this->permissionDenied();
			return;
		}
		
		$errors = array();
		
		$subject = addslashes($subject);
		$timestamp = time();
		
		$sender = $this->login_identifier;
		$u = call_user_func($this->lookup_member,$sender);
		$sender_name = $u->fullname;
		$sender_email = $u->email;
				
		$rcpts = implode(",",$recipients);
		$owners = $recipients;
		if (!in_array($this->login_identifier, $owners))
			$owners[] = $this->login_identifier;
				
		$this->query("INSERT INTO $this->table_messages 
			(sender, sender_name, sender_email, recipients, subject, body, timestamp, replyto, isnewsletter)
			VALUES
			(\"$sender\",\"$sender_name\",\"$sender_email\",\"$rcpts\",\"$subject\",\"".addslashes($body)."\",\"$timestamp\", 0, 1)"
		);
		$message_id = $this->insert_id();
		$this->query("UPDATE $this->table_messages SET thread='$message_id' WHERE id=$message_id");		
		
		$owners = array_unique($owners);
		foreach ($owners as $owner) {			
			$this->query("INSERT INTO $this->table_user 
				(message_id,owner)
				VALUES
				('$message_id','$owner')");
		}
		
		/*
		$user_mail_settings = call_user_func($this->get_useroptions,$this, 'mail_incoming', $recipients);
		
		foreach ($recipients as $r) {
			if ($user_mail_settings[$r] == '1') {
				$this->sendMail($r,$sender,$sender_name,$sender_email,$subject,$body,$message_id);
			}
		}
		
		$_SESSION['message_sent_to'] = $recipients;
		
		if (count($recipients) == 1)
			$this->addToActivityLog("sendte en melding til ".call_user_func($this->make_memberlink,$recipients[0]));
		else
			$this->addToActivityLog("sendte en melding til ".count($recipients)." mottakere");
		$this->redirect($this->generateCoolUrl("/"),"Meldingen ble sendt!");

		*/
		
	}
	
	function viewPrefs() {

		$user_id = $this->login_identifier;
		if (empty($user_id)){ $this->permissionDenied(); exit(); }
			
		$mail_incoming = $this->mail_incoming ? "checked='checked' " : "";
		$receive_guestmail = $this->receive_guestmail ? "checked='checked' " : "";
		$post_uri = $this->generateCoolUrl('/saveprefs/');

		call_user_func(
			$this->add_to_breadcrumb, 
			"<a href='".$this->generateCoolURL("/prefs/")."'>Valg</a>"
		);

		return '
			<h2>Mine valg</h2>
			<form method="post" action="'.$post_uri.'">
				<p>
					<label for="mail_incoming">
						<input type="checkbox" name="mail_incoming" id="mail_incoming" '.$mail_incoming.'/>
						Send kopi av innkommende meldinger til min e-postadresse.
					</label>
				</p>	
				<p>
					<label for="receive_guestmail">
						<input type="checkbox" name="receive_guestmail" id="receive_guestmail" '.$receive_guestmail.'/>
						Motta e-post fra ikke-innloggede.
					</label>
				</p>	
				<p>
					<label for="items_per_page">
						Vis <input type="text" name="items_per_page" id="items_per_page" size="5" value="'.$this->items_per_page.'" />
						meldinger per side
					</label>
				</p>	
				<p>
					<input type="submit" value="Lagre valg" />
				</p>
			</form>
		';
		
	}
	
	function savePrefs() {
		$page_id = $this->page_id;
		$user_id = $this->login_identifier;
		if (empty($user_id)){ print $this->permissionDenied(); exit(); }
		
		// Save: mail_incoming
		$opt_name = 'mail_incoming';
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
 			$this->fatalError("Kunne ikke lagre fordi: Duplikat eksisterer for bruker $user_id på siden $page_id.");
 		}		
 		
 		// Save: receive_guestmail
		$opt_name = 'receive_guestmail';
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
 			$this->fatalError("Kunne ikke lagre fordi: Duplikat eksisterer for bruker $user_id på siden $page_id.");
 		}	
 		
 		// Save: items_per_page
		$opt_name = 'items_per_page';
		if (!is_numeric($_POST[$opt_name])) $this->redirect($this->generateCoolUrl('/prefs/'),'Antall meldinger per side må være et tall','error');
		if ($_POST[$opt_name] < 5) $this->redirect($this->generateCoolUrl('/prefs/'),'Antall meldinger per side må minst være 5','error');
		$value = $_POST[$opt_name];
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
 			$this->fatalError("Kunne ikke lagre fordi: Duplikat eksisterer for bruker $user_id på siden $page_id.");
 		}	

		$this->redirect($this->generateCoolUrl('/'),'Dine valg ble lagret');
	}
	
	function viewMessageCenter() {
	    
		$output = "";
            if (empty($this->login_identifier)){
                if (isset($_SESSION['message_sent_to'])) unset($_SESSION['message_sent_to']);
                $output .= $this->permissionDenied(); 
                return $output; 
            }
	
		$output .= '
			<p>
				<a href="'.$this->generateCoolUrl("/newmessage").'">Send ny melding</a> | 
				<a href="'.$this->generateCoolUrl("/prefs").'">Valg for Meldingssenter</a>
			</p>
			<p style="background:white;padding:3px; border: 1px solid #ddd; text-align:center;">
				<a href="'.$this->generateCoolUrl("/").'" id="link_conv" title="Viser både meldinger du har sendt og mottatt, gruppert etter emne">'.$this->label_conversations.'</a> |
				<a href="'.$this->generateCoolUrl("/inbox/").'" id="link_inbox" title="Viser alle meldinger du har mottatt">'.$this->label_inbox.'</a> |
				<a href="'.$this->generateCoolUrl("/sent/").'" id="link_sent" title="Viser alle meldinger du har sendt">'.$this->label_sentmessages.'</a> |
				<a href="'.$this->generateCoolUrl("/trash/").'" id="link_trash" title="Viser alle meldinger du har slettet">'.$this->label_trash.'</a>
			</p>
		';
		
		if (isset($this->coolUrlSplitted[0])) {
			switch ($this->coolUrlSplitted[0]) {
				case 'inbox':
					$output .= "<style type='text/css'>#link_inbox { font-weight: bold; }</style>\n";
					$output .=$this->viewInbox();
					break;
				case 'sent':
					$output .= "<style type='text/css'>#link_sent { font-weight: bold; }</style>\n";
					$output .=$this->viewSent();
					break;
				case 'trash':
					$output .= "<style type='text/css'>#link_trash { font-weight: bold; }</style>\n";
					$output .=$this->viewTrash();
					break;
				default:
					$output .= "<style type='text/css'>#link_conv { font-weight: bold; }</style>\n";
					$output .=$this->viewConversations();
					break;			
			}
		} else {
			$output .= "<style type='text/css'>#link_conv { font-weight: bold; }</style>\n";
			$output .= $this->viewConversations();
		}
		return $output;		
	}
	
	function viewInbox() {
		$output = "<h2>$this->label_inbox</h2>";
		
		$id = $this->login_identifier;
		$img_u = $this->image_dir.$this->image_unread;
		$img_r = $this->image_dir.$this->image_read;
		$img_nu = $this->image_dir.$this->image_unreadNewsletter;
		$img_nr = $this->image_dir.$this->image_readNewsletter;

		$u = $this->table_user;
		$g = $this->table_messages;

		$res = $this->query("SELECT
				COUNT($g.thread)
			FROM 
				$g,$u
			WHERE 
				$u.message_id = $g.id
				AND $u.owner=".$this->login_identifier."
				AND $u.deleted=0
			GROUP BY $g.thread"
		);
		$this->item_count = $res->num_rows; 
		
		if ($this->page_no > ($this->item_count/$this->items_per_page)) $this->page_no = ceil($this->item_count/$this->items_per_page);
		if ($this->page_no < 1) $this->page_no = 1;

		$res = $this->query("SELECT
				$g.id,
				$g.sender,
				$g.sender_name,
				$g.sender_email,
				$g.recipients,
				$g.subject,
				$g.timestamp, 
				$g.isnewsletter, 
				$u.is_read
			FROM 
				$g,$u
			WHERE 
				$u.message_id = $g.id
				AND $u.owner=$id AND $g.sender!=$id 
				AND $u.deleted=0
			ORDER BY $g.timestamp DESC
			LIMIT
				".(($this->page_no-1)*$this->items_per_page).",$this->items_per_page"
		);
		$output .= '
		<script type="text/javascript">
			//<![CDATA[
			jQuery(document).ready(function(){
                 jQuery("[id^=msgrow_]").mouseover(onRowOver);
                 jQuery("[id^=msgrow_]").mouseout(onRowOut);
            });

			function onRowOver(event) {
			    jQuery(event.delegateTarget).css("background","white");
			}
			
			function onRowOut(event) {
			    jQuery(event.delegateTarget).css("background","");
			}
			//]]>
		</script>
		';
		$output .= "
		<table class='msg_list' cellspacing='0'>
			<tr class='row0'>
				<th width='20' valign='top'>&nbsp;</th>
				<th width='200' valign='top'>$this->label_subject</th>
				<th width='170' valign='top'>$this->label_from</th>
				<th width='110' valign='top'>$this->label_datereceived</th>
			</tr>
		";
		while ($row = $res->fetch_assoc()) {
			if ($row['isnewsletter']) {
				$img = ($row['is_read'] == '1') ? $img_nr : $img_nu;
			} else {
				$img = ($row['is_read'] == '1') ? $img_r : $img_u;		
			}
			$subject = stripslashes($row['subject']);
			$timestamp = date("d.m.Y",$row['timestamp']);
			if ($row['sender'] == '0') {
				$sender = $row['sender_name'];
			} else {
				$sender = call_user_func($this->lookup_member,$row['sender']);
				$sender = $sender->firstname;
			}
			$id = $row['id'];
			$url_read = $this->generateCoolUrl("/readmessage/$id/");
			$output .= '
				<tr class="row1" id="msgrow_'.$id.'" onclick=\'window.location="'.$url_read.'";\'>
					<td width="20" valign="top"><img src="'.$img.'" /></td>
					<td width="200" valign="top"><a href="'.$url_read.'" style="display:block;">'.$subject.'</a></td>
					<td width="170" valign="top">'.$sender.'</td>
					<td width="90" valign="top">'.$timestamp.'</td>
				</tr>
			';
		}
		$output .= "</table>";

		$cp = $this->page_no;
		$tp = ceil($this->item_count/$this->items_per_page);
		$xofy = str_replace(array("%x%","%y%"),Array($cp,$tp),$this->label_pagexofy);
		$lp = ($cp == 1)   ? $this->label_newer : '<a href="'.$this->generateURL('page='.($cp-1)).'">'.$this->label_newer.'</a>';
		$np = ($cp == $tp) ? $this->label_older   : '<a href="'.$this->generateURL('page='.($cp+1)).'">'.$this->label_older.'</a>';
		$output .= "<table width='100%'><tr><td>$lp</td><td><p style='text-align:center;'>$xofy</p></td><td><p style='text-align:right'>$np</p></td></tr></table>\n\n";
		
		call_user_func(
			$this->add_to_breadcrumb, 
			'<a href="'.$this->generateCoolURL("/inbox/").'">'.$this->label_inbox.'</a>'
		);
		
		return $output;

	}
	
	function viewSent() {
		$output = "<h2>$this->label_sentmessages</h2>";
		
		$id = $this->login_identifier;
		$img_u = $this->image_dir.$this->image_unread;
		$img_r = $this->image_dir.$this->image_read;
		$img_nu = $this->image_dir.$this->image_unreadNewsletter;
		$img_nr = $this->image_dir.$this->image_readNewsletter;
		$u = $this->table_user;
		$g = $this->table_messages;

		$res = $this->query("SELECT
				COUNT($g.thread)
			FROM 
				$g,$u
			WHERE 
				$u.message_id = $g.id
				AND $u.owner=".$this->login_identifier."
				AND $u.deleted=0
			GROUP BY $g.thread"
		);
		$this->item_count = $res->num_rows; 
		
		if ($this->page_no > ($this->item_count/$this->items_per_page)) $this->page_no = ceil($this->item_count/$this->items_per_page);
		if ($this->page_no < 1) $this->page_no = 1;

		$res = $this->query("SELECT
				$g.id,
				$g.sender,
				$g.recipients,
				$g.subject,
				$g.timestamp, 
				$g.isnewsletter, 
				$u.is_read
			FROM 
				$g,$u
			WHERE 
				$u.message_id = $g.id
				AND $u.owner=$id AND $g.sender=$id 
				AND $u.deleted=0
			ORDER BY $g.timestamp DESC
			LIMIT
				".(($this->page_no-1)*$this->items_per_page).",$this->items_per_page"
		);
		$output .= '
		<script type="text/javascript">
			//<![CDATA[
			
			jQuery(document).ready(function(){
                 jQuery("[id^=msgrow_]").mouseover(onRowOver);
                 jQuery("[id^=msgrow_]").mouseout(onRowOut);
            });

			function onRowOver(event) {
			    jQuery(event.delegateTarget).css("background","white");
			}
			
			function onRowOut(event) {
			    jQuery(event.delegateTarget).css("background","");
			}

			//]]>
		</script>
		';
		$output .= "
		<table class='msg_list' cellspacing='0'>
			<tr class='row0'>
				<th width='20' valign='top'>&nbsp;</th>
				<th width='200' valign='top'>$this->label_subject</th>
				<th width='170' valign='top'>$this->label_to</th>
				<th width='90' valign='top'>$this->label_datesent</th>
			</tr>
		";
		while ($row = $res->fetch_assoc()) {
			if ($row['isnewsletter']) {
				$img = ($row['is_read'] == '1') ? $img_nr : $img_nu;
			} else {
				$img = ($row['is_read'] == '1') ? $img_r : $img_u;		
			}
			$id = $row['id'];
			$subject = stripslashes($row['subject']);
			$timestamp = date("d.m.Y",$row['timestamp']);			
			$rcpts = explode(",",$row['recipients']);
			if (count($rcpts) > 1) {
				$rcpts_str = count($rcpts)." ".$this->label_recipients;
			} else {
				if (empty($rcpts[0])) {
					$rcpts_str = "(ingen)";
				} else {
					$rcpt = call_user_func($this->lookup_member, $rcpts[0]);
					$rcpts_str  = $rcpt->firstname;
				}
			}
			$url_read = $this->generateCoolUrl("/readmessage/$id/");
			$output .= '
				<tr class="row1" id="msgrow_'.$id.'" onclick=\'window.location="'.$url_read.'";\'>
					<td width="20" valign="top"><img src="'.$img.'" /></td>
					<td width="200" valign="top"><a href="'.$url_read.'" style="display:block;">'.$subject.'</a></td>
					<td width="170" valign="top">'.$rcpts_str.'</td>
					<td width="90" valign="top">'.$timestamp.'</td>
				</tr>
			';
		}
		$output .= "</table>";

		$cp = $this->page_no;
		$tp = ceil($this->item_count/$this->items_per_page);
		$xofy = str_replace(array("%x%","%y%"),Array($cp,$tp),$this->label_pagexofy);
		$lp = ($cp == 1)   ? $this->label_newer : '<a href="'.$this->generateURL('page='.($cp-1)).'">'.$this->label_newer.'</a>';
		$np = ($cp == $tp) ? $this->label_older   : '<a href="'.$this->generateURL('page='.($cp+1)).'">'.$this->label_older.'</a>';
		$output .= "<table width='100%'><tr><td>$lp</td><td><p style='text-align:center;'>$xofy</p></td><td><p style='text-align:right'>$np</p></td></tr></table>\n\n";
		
		call_user_func(
			$this->add_to_breadcrumb, 
			'<a href="'.$this->generateCoolURL('/sent/').'">'.$this->label_sentmessages.'</a>'
		);
		
		return $output;
	}
	
	function viewTrash() {
		$img_info = $this->image_dir."info.png";
		$output = '<h2>'.$this->label_trash.'</h2>
		<div style="border: 1px solid #ddd; background:white;padding:4px; margin-top:10px;margin-bottom:10px;">
			<img src="'.$img_info.'" alt="info" style="float:left; padding:3px; padding-bottom:5px;" />
			Meldinger slettes automatisk når de har ligget i søppelkurven i en uke. Du kan ikke tømme søppelkurven manuelt.
			<div style="clear:both;"><!-- --></div>
		</div>
		';
		
		$id = $this->login_identifier;
		$img_u = $this->image_dir.$this->image_unread;
		$img_r = $this->image_dir.$this->image_read;
		$img_nu = $this->image_dir.$this->image_unreadNewsletter;
		$img_nr = $this->image_dir.$this->image_readNewsletter;
		$u = $this->table_user;
		$g = $this->table_messages;
		$maxAge = time() - 7*86400;
		$res = $this->query("SELECT
				$g.id,
				$g.sender,
				$g.recipients,
				$g.subject,
				$g.timestamp, 
				$g.isnewsletter, 
				$u.is_read,
				$u.deleted
			FROM 
				$g,$u
			WHERE 
				$u.message_id = $g.id
				AND $u.owner=$id 
				AND $u.deleted > $maxAge
			ORDER BY $g.timestamp DESC"
		);
		$output .= '
		<script type="text/javascript">
			//<![CDATA[
			jQuery(document).ready(function(){
                 jQuery("[id^=msgrow_]").mouseover(onRowOver);
                 jQuery("[id^=msgrow_]").mouseout(onRowOut);
            });

			function onRowOver(event) {
			    jQuery(event.delegateTarget).css("background","white");
			}
			
			function onRowOut(event) {
			    jQuery(event.delegateTarget).css("background","");
			}
			//]]>
		</script>
		';
		$output .= "
		<table class='msg_list' cellspacing='0'>
			<tr class='row0'>
				<th width='20' valign='top'>&nbsp;</th>
				<th width='200' valign='top'>$this->label_subject</th>
				<th width='170' valign='top'>$this->label_from</th>
				<th width='110' valign='top'>$this->label_datedeleted</th>
			</tr>
		";
		while ($row = $res->fetch_assoc()) {
			if ($row['isnewsletter']) {
				$img = ($row['is_read'] == '1') ? $img_nr : $img_nu;
			} else {
				$img = ($row['is_read'] == '1') ? $img_r : $img_u;		
			}
			$id = $row['id'];
			$subject = stripslashes($row['subject']);
			$timestamp = date("d.m.Y",$row['deleted']);			
						if ($row['sender'] == '0') {
				$sender = $row['sender_name'];
			} else {
				$sender = call_user_func($this->lookup_member,$row['sender']);
				$sender = $sender->firstname;
			}
			$url_read = $this->generateCoolUrl("/readmessage/$id/");
			$output .= '
				<tr class="row1" id="msgrow_'.$id.'" onclick=\'window.location="'.$url_read.'";\'>
					<td width="20" valign="top"><img src="'.$img.'" /></td>
					<td width="200" valign="top"><a href="'.$url_read.'" style="display:block;">'.$subject.'</a></td>
					<td width="170" valign="top">'.$sender.'</td>
					<td width="90" valign="top">'.$timestamp.'</td>
				</tr>
			';
		}
		$output .= "</table>";
		
		call_user_func(
			$this->add_to_breadcrumb, 
			'<a href="'.$this->generateCoolURL('/trash/').'">'.$this->label_trash.'</a>'
		);
		
		return $output;
	}
	
	function viewConversations() {
		$output = "<h2>".$this->label_conversations."</h2>";
		
		$id = $this->login_identifier;
		$img_u = $this->image_dir.$this->image_unread;
		$img_r = $this->image_dir.$this->image_read;
		$img_nu = $this->image_dir.$this->image_unreadNewsletter;
		$img_nr = $this->image_dir.$this->image_readNewsletter;
		$u = $this->table_user;
		$g = $this->table_messages;
		
		
		$res = $this->query("SELECT
				COUNT($g.thread)
			FROM 
				$g,$u
			WHERE 
				$u.message_id = $g.id
				AND $u.owner=".$this->login_identifier."
				AND $u.deleted=0
			GROUP BY $g.thread"
		);
		$this->item_count = $res->num_rows; 
		
		if ($this->page_no > ($this->item_count/$this->items_per_page)) $this->page_no = ceil($this->item_count/$this->items_per_page);
		if ($this->page_no < 1) $this->page_no = 1;
		
		# Somehow, the MIN(CONCAT( combination needs a letter first to
		# return the string as utf8....
		$res = $this->query("SELECT
				$g.thread,
				$g.isnewsletter, 
				MIN(concat('a',$g.id,'|',$g.sender,'|',$g.sender_name,'|',$g.subject)) as first_msg, 
				SUM($u.is_read) as unread_count,
				MAX($g.timestamp) as newest,
				COUNT($u.id) as msg_count
			FROM 
				$g,$u
			WHERE 
				$u.message_id = $g.id
				AND $u.owner=$id
				AND $u.deleted=0
			GROUP BY $g.thread
			ORDER BY newest DESC
			LIMIT
				".(($this->page_no-1)*$this->items_per_page).",$this->items_per_page"
		);
		$output .= '
		<script type="text/javascript">
			//<![CDATA[
			jQuery(document).ready(function(){
                 jQuery("[id^=msgrow_]").mouseover(onRowOver);
                 jQuery("[id^=msgrow_]").mouseout(onRowOut);
            });

			function onRowOver(event) {
			    jQuery(event.delegateTarget).css("background","white");
			}
			
			function onRowOut(event) {
			    jQuery(event.delegateTarget).css("background","");
			}
			//]]>
		</script>
		';
		$output .= "
		<table class='msg_list' cellspacing='0'>
			<tr class='row0'>
				<th width='20' valign='top'>&nbsp;</th>
				<th width='200' valign='top'>$this->label_subject</th>
				<th width='170' valign='top'>$this->label_conv_starter</th>
				<th width='90' valign='top'>$this->label_num_messages</th>
				<th width='90' valign='top'>$this->label_datelast</th>
			</tr>
		";
		while ($row = $res->fetch_assoc()) {
			$thread = $row['thread'];
			$timestamp = date("d.m.Y",$row['newest']);
			$msgs = $row['msg_count'];
			$unread = $msgs - $row['unread_count'];
			if ($row['isnewsletter']) {
				$img = ($unread <= 0) ? $img_nr : $img_nu;
			} else {
				$img = ($unread <= 0) ? $img_r : $img_u;		
			}


			$dat = explode("|",mb_substr(stripslashes($row['first_msg']),1));
			$message_id = array_shift($dat);
			$sender_id = array_shift($dat);
			$sender_name = array_shift($dat);
			$subject = implode("|",$dat);
			if ($sender_id == '0') {
				$sender = $sender_name;
			} else {
				$sender = call_user_func($this->lookup_member,$sender_id);
				$sender = $sender->firstname;
			}
			$url_read = $this->generateCoolUrl("/readthread/$thread/");
			$output .= '
				<tr class="row1" id="msgrow_'.$message_id.'" onclick=\'window.location="'.$url_read.'";\'>
					<td width="20" valign="top"><img src="'.$img.'" /></td>
					<td width="200" valign="top"><a href="'.$url_read.'" style="display:block;">'.$subject.'</a></td>
					<td width="170" valign="top">'.$sender.'</td>
					<td width="90" valign="top">'.$msgs.' ('.$unread.' uleste)</td>
					<td width="90" valign="top">'.$timestamp.'</td>
				</tr>
			';
		}
		$output .= "</table>";
		
		$cp = $this->page_no;
		$tp = ceil($this->item_count/$this->items_per_page);
		$xofy = str_replace(array("%x%","%y%"),Array($cp,$tp),$this->label_pagexofy);
		$lp = ($cp == 1)   ? $this->label_newer : '<a href="'.$this->generateURL('page='.($cp-1)).'">'.$this->label_newer.'</a>';
		$np = ($cp == $tp) ? $this->label_older   : '<a href="'.$this->generateURL('page='.($cp+1)).'">'.$this->label_older.'</a>';
		$output .= "<table width='100%'><tr><td>$lp</td><td><p style='text-align:center;'>$xofy</p></td><td><p style='text-align:right'>$np</p></td></tr></table>\n\n";
		
		return $output;
	}
	
	function printThread($thread) {

        $output = "";
        
        if (isset($_SESSION['message_sent_to'])) {
            $rcpts = $_SESSION['message_sent_to'];
            for ($i = 0; $i < count($rcpts); $i++) {
                $rcpts[$i] = call_user_func($this->make_memberlink, $rcpts[$i]);
            }
            $output .= "
            <div style='border:3px solid #880;padding:20px 20px 20px 20px;margin:5px; background:#ffffff;'>
            <ul style='padding:0px;margin:0px;list-style: none;'>
              <li style='background:url(\"/images/icns/accept.png\") no-repeat left;padding:2px 2px 2px 20px;'>Levert til ".count($rcpts)." personer i 18. Bergens interne meldingssystem.</li>
            ";
            if ($_SESSION['message_status']['numqueued'] > 0) {
              $output .= "<li style='background:url(\"/images/icns/accept.png\") no-repeat left;padding:2px 2px 2px 20px;'>Levert til  " . $_SESSION['message_status']['numqueued'] ." personer per epost.</li>";
            }
            $notsent = count($_SESSION['message_status']['errors']);
            if ($notsent > 0) {
                foreach ($_SESSION['message_status']['errors'] as $e) {
                    $recipient = call_user_func($this->make_memberlink, $e['rcpt']);
                    $errs = array();
                    foreach ($e['errors'] as $err) {
                        $errs[] = $this->errorMessages[$err];
                    }
                    $output .= "<li style='background:url(\"/images/icns/exclamation.png\") no-repeat left; padding:2px 2px 2px 20px;'>Kunne ikke levere epost til ".$recipient.'!<br />'.implode(', ',$errs)."</li>";
                }
                $output .= "</ul>";
            }
            $output .= "</li></ul>";
            $output .= "</div>";
            unset($_SESSION['message_sent_to']);
            unset($_SESSION['message_status']);
            if (empty($this->login_identifier)){ 
                return $output;
            }
        } 
		if (isset($_SESSION['message_sent_to'])) unset($_SESSION['message_sent_to']);
		if (isset($_SESSION['message_status'])) unset($_SESSION['message_status']);

	
		if (empty($this->login_identifier)) return $this->permissionDenied(); 
		if (!is_numeric($thread)) return $this->notSoFatalError("Meldingen finnes ikke",array('logError' => false));
		
		$uid = $this->login_identifier;
		$u = $this->table_user;
		$g = $this->table_messages;
		$res = $this->query("SELECT
				$g.id as message_id,
				$g.subject as subject,
				$g.isnewsletter as isnewsletter
			FROM 
				$g,$u
			WHERE 
				$g.thread=$thread
				AND $u.message_id = $g.id
				AND $u.owner=$uid 
				AND $u.deleted=0
			ORDER BY $g.id LIMIT 1"
		);
		if ($res->num_rows != 1) return $this->notSoFatalError("Konversasjonen eksisterer ikke");

		$row = $res->fetch_assoc();
		$message_id = $row['message_id'];
		$isNewsletter = $row['isnewsletter'];
		$subject = stripslashes($row['subject']);
		
		$output .= '
			<script type="text/javascript">
			//<![CDATA[
			
			jQuery(document).ready(function(){
                 jQuery("[id^=messageToggler_]").click(toggleMsg);
                 jQuery("[id^=replylink_]").click(reply);
                 jQuery("[id^=replylinkAll_]").click(replyAll);
                 jQuery("[id^=addAttLink_]").click(addAttachment);
            });

			function toggleMsg(event) {
			    event.preventDefault();
			    item = event.delegateTarget.id;
			    var id = item.substr(item.indexOf("_")+1);  // removing "messageToggler"
			    if (jQuery("#expanded"+id).is(":hidden")) {
			        jQuery("#expanded"+id).show();
			        jQuery("#collapsed"+id).hide();			    
			        jQuery("#msgmain"+id).slideDown();
			        jQuery("#msgimg"+id).show();
			    } else {
			        jQuery("#expanded"+id).hide();
			        jQuery("#collapsed"+id).show();
			        jQuery("#msgmain"+id).slideUp();
			        jQuery("#msgimg"+id).hide();
			    }
			}
			
			function cancelReply(msgId) {
			    jQuery("#replyForm_"+msgId).slideUp()
			}
			
			function reply(event) {
			    event.preventDefault();
			    item = event.delegateTarget.id;
			    var id = item.substr(item.indexOf("_")+1);  // removing "messageToggler"
			    if (jQuery("#replyForm_"+id).is(":hidden")) {
			        jQuery("#replyForm_"+id).slideDown();
			    }
				checkSenderOnly(id);
			}

			function replyAll(event) {
			    event.preventDefault();
			    item = event.delegateTarget.id;
			    var id = item.substr(item.indexOf("_")+1);  // removing "messageToggler"
			    if (jQuery("#replyForm_"+id).is(":hidden")) {
			        jQuery("#replyForm_"+id).slideDown();
			    }
				checkAllRcpts(id);
			}

			function checkSenderOnly(msgId) {
				rcpts = jQuery("#rcpts_"+msgId).val();
				rcpts = rcpts.split(",");
				if (rcpts.length > 1) {
					for (var i = 0; i < rcpts.length; i++) {
						if (i==0) jQuery("#rcpt"+msgId+"_"+rcpts[i]).attr("checked",true);
						else jQuery("#rcpt"+msgId+"_"+rcpts[i]).attr("checked",false);
					}
				}
			}
			
			function checkAllRcpts(msgId) {
				rcpts = jQuery("#rcpts_"+msgId).val();
				rcpts = rcpts.split(",");
				if (rcpts.length > 1) {
					for (var i = 0; i < rcpts.length; i++) {
						jQuery("#rcpt"+msgId+"_"+rcpts[i]).attr("checked",true);
					}
				}
			}
        
            function addAttachment(event) {
                event.preventDefault();
			    item = event.delegateTarget.id;
			    var tmp = item.substr(item.indexOf("_")+1);  // removing "messageToggler"
			    if (tmp.indexOf("_") != -1) {
			        threadId = tmp.substr(0,tmp.indexOf("_"));
			        attId = tmp.substr(tmp.indexOf("_")+1)-0;
			    } else {
			        threadId = tmp;
			        attId = 1;
			    }
			    // console.info("Adding att. "+attId+" to thread "+threadId);
                
                var newDiv = "<div id=\"att_"+threadId+"_"+attId+"\" style=\"border: 1px solid #ddd; padding: 4px;\"> \
                  <input type=\"file\" name=\"vedlegg"+attId+"\" /> \
                  <a id=\"removeAttLink_"+threadId+"_"+attId+"\" href=\"#\" class=\"icn\" style=\"background-image:url(/images/delete.png);\">Fjern</a> \
                  </div><a id=\"addAttLink_"+threadId+"_"+(attId+1)+"\" href=\"#\" class=\"icn\" style=\"background-image:url(/images/icns/attach.png);\">Legg ved enda en fil</a>";
                            
                jQuery("#addAttLink_"+threadId).remove();
                jQuery("#addAttLink_"+threadId+"_"+attId).remove();
                jQuery("#att_"+threadId).append(newDiv);
                jQuery("#addAttLink_"+threadId+"_"+(attId+1)).click(addAttachment);
                jQuery("#removeAttLink_"+threadId+"_"+attId).click(removeAttachment);    
            }
    
            function removeAttachment(event){
                event.preventDefault();
			    item = event.delegateTarget.id;
			    var tmp = item.substr(item.indexOf("_")+1);  // removing "messageToggler"
			    if (tmp.indexOf("_") != -1) {
			        threadId = tmp.substr(0,tmp.indexOf("_"));
			        attId = tmp.substr(tmp.indexOf("_")+1)-0;
			    } else {
			        threadId = tmp;
			        attId = 1;
			    }
			    // console.info("Removing att. "+attId+" from thread "+threadId);
			    jQuery("#att_"+threadId+"_"+attId).remove();
            }
            
			//]]>
			</script>
		';
		
		if (!isset($_GET['noprint'])) {
			$output .= "<h2>$subject</h2>";
		}
		list($message_id,$tmp) = $this->printMessage($message_id,true);
		$output .= $tmp;
		
		call_user_func(
			$this->add_to_breadcrumb, 
			"<a href=\"".$this->generateCoolURL("/readthread/$thread/")."\">$subject</a>"
		);
		
		return $output;
	}
	
	function printMessage($id, $printThread) {

		$output = "";
		if (empty($this->login_identifier)) return $this->permissionDenied(); 
		if (empty($id)) return $this->notSoFatalError("Kan ikke hente en melding uten meldings-id"); 
		if (!is_numeric($id)) return $this->notSoFatalError("Meldings-id er ugyldig"); 
		$me = $this->login_identifier;
		
		$u = $this->table_user;
		$a = $this->table_attachments;
		$g = $this->table_messages;
		$res = $this->query("SELECT
				$g.id,
				$g.sender,
				$g.sender_name,
				$g.sender_email,
				$g.recipients,
				$g.subject,
				$g.body,
				$g.isnewsletter,
				$g.newsletterheading,
				$g.timestamp,
				$g.replyto,
				$u.is_read,
				$u.deleted
			FROM 
				$g,$u
			WHERE 
				$g.id = $id
				AND $u.message_id = $g.id
				AND $u.owner = $me"
		);
		if ($res->num_rows != 1) return $this->notSoFatalError("Meldingen finnes ikke"); 
		
		$row = $res->fetch_assoc();
		
		$message_id = $row['id'];
		$isNewsletter = $row['isnewsletter'];
		$newsletterHeading = $row['newsletterheading'];
		$subject = stripslashes($row['subject']);
		$body = stripslashes($row['body']);
		if (!$isNewsletter) $body = nl2br($body);
		$sender_id = $row['sender'];
		$sender_name = stripslashes($row['sender_name']);
		$sender_email = stripslashes($row['sender_email']);
		$timestamp = date("d.m.y, H:i",$row['timestamp']);
		$recipients = $row['recipients'];
		$is_deleted = ($row['deleted'] != '0');
		$is_read = ($row['is_read'] != '0');
		$replyto = intval($row['replyto']);
		
		$attachments = array();
		$res = $this->query("SELECT
				$a.id as id,
				$a.filename as file,
				$a.friendlyname as name,
				$a.mime as mime,
				$a.filesize as size
			FROM 
				$a
			WHERE 
				$a.message_id = $id
			"
		);	
		while ($row = $res->fetch_assoc()) {
			$attachments[] = $row;
		}
		
		
		if (!$is_read) {
			$this->query("UPDATE $u SET is_read=1 WHERE $u.message_id=$id AND $u.owner=$me");
		}
		
		if (!$printThread) {
			if (!isset($_GET['noprint'])) {
				$output .= "<h2>$subject</h2>";
			}
		}
		
		if ($is_deleted) {
		
			if ($this->coolUrlSplitted[1] == $id) {
				$recover = $this->generateCoolURL("/recover/$id/");
				$output .= "
							
					<div style='background: url(".$this->image_dir."whitegreentrans.jpg) repeat-x; padding:10px;'>
						<div style='color:#666; background: #fdd; padding: 2px; font-size:80%; font-weight: bold;'>
							Meldingen er slettet
						</div>
						";
						$output .= "
						<div id='msgmain$id'>				
						<p>-</p>
						<a href='$recover'>Gjenopprett melding</a>
						<hr style='clear:both;visibility:hidden; margin:0px;' />
						</div>";
				$output .= "
					</div>
				";
			}
		
		} else {
		
			if (empty($sender_id)) {
				$author = $sender_name;
				$author_uri = "<a href=\"mailto:".$sender_email."\">$sender_name</a>";
				$author_img = "";
			} else {
				$author_img = call_user_func($this->lookup_memberimage, $sender_id);
				if ($sender_id == $me) {
					$author_uri = "du";
				} else {
					$author = call_user_func($this->lookup_member, $sender_id);
					$author_uri = call_user_func($this->make_memberlink, $sender_id, $author->firstname);
				}
			}
			$rcpts = explode(",",$recipients);
			$rcpt_count = count($rcpts);
			$rcptsN = array();
			for ($i = 0; $i < count($rcpts); $i++) {
				if ($rcpts[$i] == $me) {
					$rcpts[$i] = "deg";
				} else {
					if ($rcpts[$i] != 0) {
						$rcpt = call_user_func($this->lookup_member, $rcpts[$i]);		
						$rcpts[$i] = call_user_func($this->make_memberlink, $rcpt->ident, $rcpt->firstname); 
						$rcptsN[] = $rcpt->firstname;
					}
				}
			}
			if ($rcpt_count > 5) {
				$lastrcpt = array_pop($rcpts);
				$rcptsA = implode(", ",$rcpts);
				$rcptsA = $rcptsA." og ".$lastrcpt;
	
				$rcpts = "
				<script type='text/javascript'>
				//<![CDATA[
					function expand$id() { \$('rcpts$id').innerHTML = '$rcptsA'; }
				//]]>
				</script>
				<span id='rcpts$id'><a href='#' onclick='expand$id(); return false;' title='".implode(", ",$rcptsN)."'>".$rcpt_count." mottakere</a></span>";
			} else if (count($rcpts) > 1) {
				$lastrcpt = array_pop($rcpts);
				$rcpts = implode(", ",$rcpts);
				$rcpts = $rcpts." og ".$lastrcpt;
			} else if (count($rcpts) < 1) {
				$rcpts = "ingen mottakere";			
			} else {
				if ($rcpts[0] == '0') 
					$rcpts = "ingen mottakere";			
				else
					$rcpts = implode(", ",$rcpts);		
			}
			
			if ($replyto == 0) {
				$wrote = "skrev";
			} else {
				$wrote = "svarte";		
			}
			
			$reply = $this->generateCoolUrl("/reply/$id/");
			$replyall = $this->generateCoolUrl("/reply/$id/","check_all=true");
			$delete = $this->generateCoolUrl("/delete/$id/");
			$img_expanded = $this->image_dir."expanded.gif";
			$img_collapsed = $this->image_dir."collapsed.gif";
			
			if ($printThread) 
				$toggleMsgCode = '<a href="#" id="messageToggler_'.$id.'" title="Trykk for å slå sammen eller utvide denne meldingen"><span id="expanded'.$id.'"><img src="'.$img_expanded.'" style="width:10px; height:10px;border:0px;" alt="Expanded" /></span><span id="collapsed'.$id.'" style="display:none;"><img src="'.$img_collapsed.'" style="width:10px; height:10px; border:0px;" alt="Collapsed" /></span></a>';
			else
				$toggleMsgCode = '';
				
			$vedlegg = "";
			if (!empty($attachments)) {
				if (count($attachments) > 2) {
					$this->thumb_width = 90;
					$this->thumb_height = 90;
				} else {
					$this->thumb_width = 140;
					$this->thumb_height = 140;
				}
				
				$vedlegg = "<div style='padding:8px; margin:3px; border-top:1px solid #bbb; width:350px;'><div style='font-size:80%; font-weight:bold;'>Vedlegg:</div>";
				$isImage = false;
				foreach ($attachments as $a) {
					switch ($a['mime']) {
						case 'application/pdf':
							$icon = "pdf.gif";
							break;
						case 'application/x-zip':
							$icon = "archive.png";
							break;
						case 'application/msword':
						case 'application/octet-stream':
							switch ($this->file_extension($a['file'])) {
								case 'ppt':
									$icon = "page_white_powerpoint.png";
									break;
								case 'xls':
									$icon = "page_white_excel.png";
									break;
								case 'doc':
									$icon = "page_white_word.png";
									break;
								case 'pdf':
									$icon = "page_white_acrobat.png";
									break;
								case 'zip':
									$icon = "compress.png";
									break;
								default: 
									$icon = "page_white.png";
									break;
							}
							break;
						case 'image/jpeg':
						case 'image/gif':
						case 'image/png':
							$icon = "image.png";
							$isImage = true;
							break;
						default:
							$icon = "page_white.png";
							break;
					}
					$att_url = $this->generateCoolUrl("/attachments/".$a['file']);
										
					if (file_exists($this->attachment_dir."/".$a['file'])) {
						if ($isImage) {
							list($width, $height, $type, $attr) = getimagesize($this->attachment_dir."/".$a['file']);
							if ($width > $height) {
								$thumbW = $this->thumb_width;
								$thumbH = round($this->thumb_height / $width * $height);
								$pTop = round(($this->thumb_height - $thumbH)/2);
							} else {
								$thumbH = $this->thumb_height;
								$thumbW = round($this->thumb_width/$height*$width);
								$pTop = 0;
							}
							$dim = "border: 0px; padding-top:".$pTop."px; width:".$thumbW."px; height:".$thumbH."px;";
							$vedlegg .= '
								<div style="padding:3px; width: '.($this->thumb_width+20).'px; height: '.($this->thumb_height+20).'px; font-size:80%; float:left; text-align: center;">
									<a href="'.$att_url.'">
										<img src="'.$this->generateCoolUrl('/attachments/'.$a["file"],'thumb=true').'" style="'.$dim.'" alt="'.$a["name"].'" title="'.$a["name"].'" /><br />
									</a> ('.round($a["size"]/1024).' kB)
								</div>';					
						} else {
							$vedlegg .= '
								<div style="padding:3px;">
									<table><tr><td><img src="'.$this->image_dir.'icns/'.$icon.'" alt="attachment" style="padding-right:3px;" /></td><td>
									<a href="'.$att_url.'">'.$a["name"].'</a> ('.round($a["size"]/1024).' kB)</td></tr></table>
								</div>';
						}
					} else {
						$vedlegg .= '
								<div style="padding:3px;">
									<table><tr><td><img src="'.$this->image_dir.'icns/'.$icon.'" alt="attachment" style="padding-right:3px;" /></td><td>
									<a style="text-decoration: line-through;color:red;">'.$a["name"].'</a> ('.round($a["size"]/1024).' kB)</td></tr></table>
								</div>';
					}
				}
				$vedlegg .= "</div>";
			}
			
			if ($isNewsletter) {
    			$body = str_replace("\n","<br />\n",$body);
	    		$body = $this->makeHtmlUrls($body,60,"...");
						
				$output .= '
							
					<div class="msg">

						<div style="background: '.($is_read ? '' : '#ffd').'; padding: 2px; font-size:80%; font-weight: bold;">
							'."$toggleMsgCode $timestamp $wrote $author_uri til $rcpts:".'
						</div>
						<div id="msgmain'.$id.'">				

                            <div style="font-size: 10pt; color: #fff; letter-spacing: 1px;padding:10px 10px 3px 10px;text-align:left;background:#88b852;  border-bottom:6px solid #88b852;">
                                '.$newsletterHeading.'
                            </div>

                            <div style="padding:20px;border-bottom:6px solid #88b852;">
                                <div style="font-size: 16pt; color: #770000; letter-spacing: 2px;padding:0px 0px 15px 0px;">
                                    '.$subject.'
                                </div>
                                '.$body.'
                            </div>
                            <div style="padding-top: 20px; font-size:10px; text-align: center; color:#888888;">
            
                            </div>				

							<div class="footerLinks">
							';
							if ($sender_id != $me) {
							    $output .= '<a href="'.$this->generateCoolUrl('/newmessage/','recipients='.$sender_id).'" class="reply">Send melding til avsender</a>';
							}
							$output .= '<a href="'.$delete.'" class="delete">Slett meldingen</a>
							</div>
							<hr style="clear:both;visibility:hidden; margin:0px;" />
						</div>
					</div>
				';
			
			} else {
				$output .= '
							
					<div class="msg">
							'.(($author_img == '') ? '' : '<div style="float:right" id="msgimg'.$id.'">
								<img src="'.$author_img.'" alt="Forfatterbilde" style="width:60px; border: 1px solid #aaa; margin-left:12px;" />
							</div>').'
						<div style="color:#666; background: '.($is_read ? '' : '#ffd').'; padding: 2px; font-size:80%; font-weight: bold;">
							'."$toggleMsgCode $timestamp $wrote $author_uri til $rcpts:".'
						</div>
						<div id="msgmain'.$id.'" class="msg_main">				
							<p>'.$body.'</p>
								'.$vedlegg.'
								<hr style="clear:both;visibility:hidden; margin:0px;" />
								<div class="footerLinks">
								';
								if ($sender_id != $me) {
									$output .= '<a href="'.$reply.'" id="replylink_'.$id.'" class="reply">Svar til avsender</a> ';
									if ($rcpt_count > 1) {
										$output .= ' <a href="'.$replyall.'" id="replylinkAll_'.$id.'" class="replyall">Svar til alle</a> ';
									}
								}
								$output .= '<a href="'.$delete.'" class="delete">Slett meldingen</a>
								</div>
							<hr style="clear:both;visibility:hidden; margin:0px;" />
						</div>
					</div>
				';
			}
			$output .= '<div class="replyForm" id="replyForm_'.$id.'" style="display: none;"><div style="padding:10px;">
				'.$this->simpleReplyForm($id).'
			</div></div>';
		}
		
		if ($printThread) {
		
			$u = $this->table_user;
			$g = $this->table_messages;
			$res = $this->query("SELECT
					$g.id
				FROM 
					$g,$u
				WHERE 
					$g.replyto = $id
					AND $u.message_id = $g.id
					AND $u.owner = $me"
			);
			while ($row = $res->fetch_assoc()) {
				$id = $row['id'];
				$output .= "<div style='margin-left:5px;'>";
				list($tmp_id,$tmp) = $this->printMessage($id,$printThread);
				$output .= $tmp;
				$output .= "</div>";
			}
			
		} else {
		
			if ($is_deleted) {
				call_user_func(
					$this->add_to_breadcrumb, 
					'<a href="'.$this->generateCoolURL('/trash/').'">'.$this->label_trash.'</a>'
				);			
			} else if ($sender_id == $me) {
				call_user_func(
					$this->add_to_breadcrumb, 
					'<a href="'.$this->generateCoolURL('/sent/').'">'.$this->label_sentmessages.'</a>'
				);
			} else {
				call_user_func(
					$this->add_to_breadcrumb, 
					'<a href="'.$this->generateCoolURL('/inbox/').'">'.$this->label_inbox.'</a>'
				);
			}
			call_user_func(
				$this->add_to_breadcrumb, 
				'<a href="'.$this->generateCoolURL("/readmessage/$id/").'">'.$subject.'</a>'
			);
		
		}
		
		return array($message_id,$output);
	}
	
	function deleteMessage($id) {
	
		if (!is_numeric($id)) $this->fatalError("invalid input .3");
		if (empty($this->login_identifier)) $this->fatalError("denied");
		$me = $this->login_identifier;
		$u = $this->table_user;
		$g = $this->table_messages;		
		$res = $this->query("SELECT
				$g.id,
				$g.sender,
				$g.sender_name,
				$g.sender_email,
				$g.recipients,
				$g.subject,
				$g.body,
				$g.timestamp,
				$g.replyto,
				$u.is_read,
				$u.deleted
			FROM 
				$g,$u
			WHERE 
				$g.id = $id
				AND $u.message_id = $g.id
				AND $u.owner = $me"
		);
		if ($res->num_rows != 1) return $this->notSoFatalError("Meldingen finnes ikke"); 
		
		$row = $res->fetch_assoc();
		
		if (isset($_GET['confirm_delete'])) {
			$now = time();
			$this->query("UPDATE $this->table_user 
				SET deleted=$now 
				WHERE message_id='$id' AND owner='$me'"
			);
			$this->redirect($this->generateCoolUrl("/readmessage/$id/"),"Meldingen ble slettet");
		} else {
			$submit_url = $this->generateURL(array("confirm_delete"));
			$cancel_url = $this->generateCoolUrl("/readmessage/$id/");
			$output = '
				<h2>Bekreft sletting</h2>
				<p>Er du sikker på at du vil slette meldingen?</p>
				<form method="post" action="'.$submit_url.'">
					<input type="button" value="Avbryt" onclick=\'location="'.$cancel_url.'"\' />
					<input type="submit" value="Slett melding" />
					
				</form>
			';
			list($m_id,$tmp) = $this->printMessage($id,false);
			$output .= $tmp;
			return $output;
		}
	
	}
	
	function recoverMessage($id) {
	
		if (!is_numeric($id)) $this->fatalError("invalid input .3");
		if (empty($this->login_identifier)) $this->fatalError("denied");
		$me = $this->login_identifier;
		$u = $this->table_user;
		$g = $this->table_messages;		
		$res = $this->query("SELECT
				$g.id,
				$g.sender,
				$g.sender_name,
				$g.sender_email,
				$g.recipients,
				$g.subject,
				$g.body,
				$g.timestamp,
				$g.replyto,
				$u.is_read,
				$u.deleted
			FROM 
				$g,$u
			WHERE 
				$g.id = $id
				AND $u.message_id = $g.id
				AND $u.owner = $me"
		);
		if ($res->num_rows != 1) return $this->notSoFatalError("Meldingen finnes ikke"); 
		
		$row = $res->fetch_assoc();
		
		$this->query("UPDATE $this->table_user 
			SET deleted=0
			WHERE message_id='$id' AND owner='$me'"
		);
		$this->redirect($this->generateCoolUrl("/readmessage/$id/"),"Meldingen ble gjenopprettet");
	
	}

	function generateTimeStamp($d,$m,$y, $h,$i){
		// int mktime ( [int hour [, int minute [, int second [, int month [, int day [, int year [, int is_dst]]]]]]] )
		return $timestamp = mktime($h,$i,0,$m,$d,$y);
	}

	function isValidEmail($email_address) {
		$regex = '/^[A-z0-9][\w.-]*@[A-z0-9][\w\-\.]+\.[A-z0-9]{2,6}$/';
		return (preg_match($regex, $email_address));
	}

}


?>
