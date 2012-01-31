<?
class mailer extends base {

    var $tablename = "mailqueue";

	function mailer() {
		$this->tablename = DBPREFIX.$this->tablename;
	}
	
	function initialize(){
        #require_once '../www/libs/swift-latest/lib/swift_required.php';
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
                (NOW(),\"$sender_name\",\"$sender_email\",\"$rcpt_name\",\"$rcpt_email\",\"$subject\",\"".addslashes($plain_body)."\",\"".addslashes($html_body)."\",\"".addslashes($attachments)."\")"
            );
            $mailqueue_id = $this->insert_id();
		}
		return array(
		    'id' => $mailqueue_id,
		    'errors' => $errors		
		);
	}
	
	function send_from_queue($attachment_dir = '') {
	    global $bergen18globalconfig;

        require_once '../www/libs/swift-latest/lib/swift_required.php';        
$mailer_working = array();
for ($i = 0; $i < count($bergen18globalconfig['smtpServers']); $i++) {
	$mailer_working[] = true;
}
        	        
		$res = $this->query("SELECT id, sender_name, sender_email, rcpt_name, rcpt_email, subject, plain_body, html_body, attachments FROM $this->tablename WHERE time_sent=0 ORDER BY time_added");
		while ($row = $res->fetch_assoc()) {
		    $id = intval($row['id']);
		    $sender_name = stripslashes($row['sender_name']);
		    $sender_email = stripslashes($row['sender_email']);
		    $rcpt_name = stripslashes($row['rcpt_name']);
		    $rcpt_email = stripslashes($row['rcpt_email']);
		    $subject = stripslashes($row['subject']);
		    $plain_body = stripslashes($row['plain_body']);
		    $html_body = stripslashes($row['html_body']);
		    $attachments = explode("|",stripslashes($row['attachments']));

            $message = Swift_Message::newInstance();
            $message->setSubject($subject);
            $message->setFrom(array($sender_email => $sender_name));
            $message->setTo(array($rcpt_email => $rcpt_name));
            $message->setReplyTo(array($sender_email => $sender_name));
            $message->setBody($plain_body);
    
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
            
            $mailer_id = 0;
            $mailer_limit_reached = true;
$mail_sent = false; 
            while (!$mail_sent) {
                $mailer_id++;
                if ($mailer_id > count($bergen18globalconfig['smtpServers'])) {
                    $this->fatalError("Oi, vi har overskredet sendingskvoten vår. Meldingen din er lagt i kø, og blir sendt ved neste anledning.");
                }
		if ($mailer_working[$mailer_id-1]) {
                
		$mailer = $bergen18globalconfig['smtpServers'][$mailer_id-1];
                $res2 = $this->query("SELECT COUNT(id) FROM bg_mailqueue WHERE time_sent > (NOW() - INTERVAL 1 HOUR) AND mailer=$mailer_id");
                $n = $res2->fetch_row();
                $n = intval($n[0]);
		print "$n / ".$mailer['send_limit_per_hour']." mail sent from $mailer_id<br />";
                $mailer_limit_reached = ($n >= $mailer['send_limit_per_hour']);
                
		$res2 = $this->query("SELECT COUNT(id) FROM bg_mailqueue WHERE time_sent > (NOW() - INTERVAL 1 DAY) AND mailer=$mailer_id");
                $n = $res2->fetch_row();
                $n = intval($n[0]);
		print "$n mail/day sent from $mailer_id<br />";
		if ($mailer_limit_reached) {
		$mailer_working[$mailer_id-1] = false;
		}
		}

		if ($mailer_working[$mailer_id-1]) {
		    print "<br />Trying mailer ".$mailer['user']."...";
            
		    $this->query("UPDATE $this->tablename SET mailer=$mailer_id, attempts = attempts + 1 WHERE id=$id");
    
		    $transport = Swift_SmtpTransport::newInstance($mailer['host'], $mailer['port'], $mailer['transport']);
		    $transport->setUsername($mailer['user']);
		    $transport->setPassword($mailer['pass']);
		    
		    $swiftmailer = Swift_Mailer::newInstance($transport);
            
		    try {
		        $numSent = $swiftmailer->send($message);
		    } catch (Exception $e) {
	                $numSent = 0;
                        echo 'Caught exception: ',  $e->getMessage(), "\n";
			$mailer_working[$mailer_id-1] = false;
                    }
                    if ($numSent) {
		        $this->query("UPDATE $this->tablename SET time_sent=NOW(), plain_body='HIDDEN', html_body='HIDDEN' WHERE id=$id");
                        $mail_sent = true;
print "SENT";
                    }
print "<br />";
              }
	}
   }
  }	
}
?>
