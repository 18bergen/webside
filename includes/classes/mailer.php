<?php
class mailer extends base {

    var $tablename = "mailqueue";

	function __construct() {
		$this->tablename = DBPREFIX.$this->tablename;
	}
	
	function initialize(){
		$this->initialize_base();
	}
	
	function run(){
		$this->initialize();
	}
	
	function add_to_queue($data) {
	    $errors = array();
		
		$sender_name = $this->mailSenderName;
		if (isset($data['sender_name'])) $sender_name = $data['sender_name'];
		
		$sender_email = $this->mailSenderAddr;
		if (isset($data['sender_email'])) $sender_email = $data['sender_email'];

		if (isset($data['rcpt_name']) && !empty($data['rcpt_name'])) $rcpt_name = $data['rcpt_name'];
		else $errors[] = 'empty_rcpt_name';

		if (isset($data['rcpt_email']) && !empty($data['rcpt_email'])) $rcpt_email = $data['rcpt_email'];
		else $errors[] = 'empty_rcpt_email';
		
		if (isset($data['subject']) && !empty($data['subject'])) $subject = $data['subject'];
		else $errors[] = 'empty_subject';
        
        $plain_body = '';
        $html_body = '';
		if (isset($data['plain_body'])) $plain_body = $data['plain_body'];
		if (isset($data['html_body'])) $html_body = $data['html_body'];
		if (empty($plain_body) && empty($html_body)) $errors[] = 'empty_body';

		$attachments = "";
		if (isset($data['attachments'])) $attachments = implode("|", $data['attachments']);
		
		$mailqueue_id = -1;
		if (empty($errors)) {
            $this->query("INSERT INTO $this->tablename
                (time_added, sender_name, sender_email, rcpt_name, rcpt_email, subject, plain_body, html_body, attachments)
                VALUES
                (NOW(),\"$sender_name\",\"$sender_email\",\"$rcpt_name\",\"$rcpt_email\",\"".addslashes($subject)."\",\"".addslashes($plain_body)."\",\"".addslashes($html_body)."\",\"".addslashes($attachments)."\")"
            );
            $mailqueue_id = $this->insert_id();
		}
		return array(
		    'id' => $mailqueue_id,
		    'errors' => $errors		
		);
	}
	
	function printQueue($ajaxUrl, $completeUrl) {	
	    return "
	        <div id='mail_progressbar'>
	            
	        </div>
	        <div id='mail_errors'>
	            
	        </div>
	        
	        <noscript>
	            Beklager, denne funksjonen krever at du har JavaScript påslått. 
	            Vennligst skru på JavaScript og last siden på nytt.
	        </noscript>
	        <script>
            
            errorsOccured = 0;
            mailDelivered = 0;
            function sendMail() {
                 $.ajax({
                    url: \"$ajaxUrl\",
                    dataType: 'json',
                    error: function(xhr_data) {
                      // terminate the script
                        jQuery('#mail_progressbar').html('<p><strong>Beklager, det oppsto en ukjent feil! Send feilmeldingen under til webmaster:</strong></p>'+xhr_data.responseText);
                    },
                    success: function(xhr_data) {
                        if (xhr_data.mail_sent == 'true') {
                            mailDelivered += 1;
                            if (xhr_data.mail_left > 0) {
                                jQuery('#mail_progressbar').html('Vennligst vent, sender epost… '+xhr_data.mail_left+' epost gjenstår.<br /><img src=\"/images/progressbar1.gif\" />');
                                sendMail();
                            } else {
                                if (errorsOccured > 0) {
                                    jQuery('#mail_progressbar').html(mailDelivered+' epost(er) er levert! '+errorsOccured+' epost(er) kunne ikke leveres! Send gjerne feilmeldingene under til webmaster.');                                  
                                } else {
                                    jQuery('#mail_progressbar').html(mailDelivered+' epost(er) er levert!');  
                                    document.location.href = \"$completeUrl\";
                                }
                            }
                        } else {
                            if (xhr_data.error_msg != '') {
                                errorsOccured += 1;
                                jQuery('#mail_errors').append('<p style=\"padding:10px;border:1px solid red;background:white;\">'+xhr_data.error_msg+'</p>');                        
                            }
                            if (xhr_data.mail_left > 0) {
                                sendMail();
                            } else {
                                if (errorsOccured > 0) {
                                    jQuery('#mail_progressbar').html(mailDelivered+' epost(er) er levert! '+errorsOccured+' epost(er) kunne ikke leveres! Send gjerne feilmeldingene under til webmaster.');
                                } else {
                                    jQuery('#mail_progressbar').html('Mailen er allerede sendt.');                        
                                    document.location.href = \"$completeUrl\";                                
                                }
                            }

                        }
                    }
                  });
             }
             
             $(document).ready(function(){
                 jQuery('#mail_progressbar').html('Vennligst vent, sender epost…<br /><img src=\"/images/progressbar1.gif\" />');
                 sendMail();              
             });
             
           </script>
	    
	    ";
    }
    
    function ajaxSendFromQueue($attachment_dir = '') {
    	header("Content-Type: text/html; charset=utf-8"); 
    	print json_encode($this->send_from_queue($attachment_dir));
        exit();
	}
	
	function send_from_queue($attachment_dir = '') {
		$res = $this->query("SELECT id, sender_name, sender_email, rcpt_name, rcpt_email, subject, plain_body, html_body, attachments FROM $this->tablename WHERE time_sent=0 AND permanent_failure=0 ORDER BY time_added");
		$queuesize = $res->num_rows;
		if ($queuesize == 0) {
            return array('mail_sent' => 'false', 'mail_left' => 0, 'error_msg' => '');		
		}
		$row = $res->fetch_assoc();
        $id = intval($row['id']);
        $sender_name = stripslashes($row['sender_name']);
        $sender_email = stripslashes($row['sender_email']);
        $rcpt_name = stripslashes($row['rcpt_name']);
        $rcpt_email = stripslashes($row['rcpt_email']);
        $subject = stripslashes($row['subject']);
        $plain_body = stripslashes($row['plain_body']);
        $html_body = stripslashes($row['html_body']);
        $attachments = explode("|",stripslashes($row['attachments']));

        $message = (new Swift_Message())
			->setSubject($subject)
			->setFrom([$sender_email => $sender_name])
			->setTo([$rcpt_email => $rcpt_name])
			->setReplyTo([$sender_email => $sender_name])
			->setBody($plain_body);

        //And optionally an alternative body
        if (!empty($html_body)) {
            $message->addPart($html_body, 'text/html');
        }

        //Optionally add any attachments
        foreach ($attachments as $f) {
            if (!empty($f)) {
                if (empty($attachment_dir)) {
                    print "attachment dir not specified";
                    exit();
                } 
                $message->attach(Swift_Attachment::fromPath($attachment_dir.$f));
            }
        }

        $mail_sent = false;
		$mailer_id = 1;

		$this->query("UPDATE $this->tablename SET mailer=$mailer_id, attempts = attempts + 1 WHERE id=$id");

		$transport = (new Swift_SmtpTransport($this->smtpHost, $this->smtpPort, 'tls'))
			->setUsername($this->smtpUser)
			->setPassword($this->smtpPass);
		$mailer = new Swift_Mailer($transport);

		$error = null;

		try {
			$numSent = $mailer->send($message);
			if ($numSent === 0) {
				$error = 'invalid_address';
			}
		} catch (Swift_TransportException $exc) {
			$numSent = 0;
			$error = $exc->getMessage();
		}
		if ($numSent) {
			$this->query("UPDATE $this->tablename SET time_sent=NOW(), plain_body='HIDDEN', html_body='HIDDEN' WHERE id=$id");
			$mail_sent = true;
			$queuesize -= 1;
			$_SESSION['msg'] = "Meldingen ble levert!";
			$_SESSION['success'] = "success";
		}

		return [
			'mail_sent' => $mail_sent ? 'true' : 'false',
			'mail_left' => $queuesize,
			'error' => $error,
		];
	}
}
?>
