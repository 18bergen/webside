<?php
class socketmail {

	var $debug = false;
	var $prefs;
	var $eventlog_function;

	function sendStr($con, $str){
		if ($this->debug == true){ print("<font color=\"#0000FF\">$str</font><br />\n"); flush(); }
		fputs($con, "$str\r\n"); 
	}

	function recvStr($con){
		$alreadyWritten = false;
		$rcv = fgets($con, 1024); 
		if (strlen($rcv)>0){
			$rcvs = explode(" ",$rcv);
			if ($rcvs[0][0] == "5"){
				$alreadyWritten = true;
				if ($this->debug == true){
					print("<font color=\"#FF0000\">$rcv</font><br />\n");
				} else {
					$this->debug = true;
					print("<tt><font color=\"#FF0000\">$rcv</font><br />-- Debug autoenabled due to errors<br />\n");
				}
			}
		}
		if ($this->debug == true){ if ($alreadyWritten == false){ print("<font color=\"#009900\">$rcv</font><br />\n"); } flush(); } 
	}

	function sendMail($toArray, $subject, $message, $xtra = "", $from = "") { 
		// $toArray format --> array("Name1" => "address1", "Name2" => "address2", ...) 

		$SMTP_Server		= $this->prefs->smtpserver;
		$SMTP_Port			= $this->prefs->smtpport;
		$EmailSenderAdr		= $this->prefs->emailsenderadr;
		$EmailSenderName	= $this->prefs->emailsendername;

		if ($this->debug == true) print("<tt>-- Debug enabled<br />Connecting to $SMTP_Server on port $SMTP_Port...<br />\n");
	
		$connect = fsockopen($SMTP_Server, $SMTP_Port, $errno, $errstr, 30) or die("Could not connect to the sendmail server!");
		$rcv = fgets($connect, 1024); 
		if ($this->debug == true) print($rcv."<br />\n");
	
		$this->sendStr($connect,"HELO {$_SERVER['SERVER_NAME']}");
		$this->recvStr($connect);
	
		while (list($toKey, $toValue) = each($toArray)) { 

			if ($from == ""){
				$this->sendStr($connect,"MAIL FROM:$EmailSenderAdr");
				$this->recvStr($connect);
			} else {
				$this->sendStr($connect,"MAIL FROM:".$from);
				$this->recvStr($connect);
			}

			$this->sendStr($connect,"RCPT TO:$toValue");
			$this->recvStr($connect);
		
			$this->sendStr($connect,"DATA");
			$this->recvStr($connect);
		
			$contentTypeFound = false;
			$fromFound = false;
			if ($xtra != ""){
				$xtra2 = explode("\n",$xtra);
				foreach ($xtra2 as $xit){
					$this->sendStr($connect,$xit);
					$tmp = explode(":",$xit);
					if ($tmp[0] == "Content-Type") $contentTypeFound = true;
					if ($tmp[0] == "From") $fromFound = true;
				}
			}
			$this->sendStr($connect,"Date: ".date("D, d M Y H:i:s",time())." ".$this->prefs->timezone." (MET)");
			if ($fromFound == false){
				$this->sendStr($connect,"From: $EmailSenderName <$EmailSenderAdr>");
			}
			$this->sendStr($connect,"To: $toKey <$toValue>");
			$this->sendStr($connect,"Subject: $subject");
			$this->sendStr($connect,"Return-Path: <$EmailSenderAdr>");
			$this->sendStr($connect,"X-Priority: 3");
			$this->sendStr($connect,"X-Mailer: 18. Bergen V-S MailingSystem");
			if ($contentTypeFound == false){
				$this->sendStr($connect,"Content-Type: text/plain; charset=iso-8859-1");
			}
			$this->sendStr($connect,"");
			$this->sendStr($connect,stripslashes($message));
			$this->sendStr($connect,".");
			$this->recvStr($connect);
			$this->sendStr($connect,"RSET");
			$this->recvStr($connect);
		
		} 

		$this->sendStr($connect,"QUIT");
		$this->recvStr($connect);
	
		fclose($connect); 
		if ($this->debug == true) print("Connection closed!</tt>\n");
	
		if (count($toArray) > 1){
			if (!empty($this->eventlog_function)){
				$ef = $this->eventlog_function;
				$ef("E-post sendt til ".count($toArray)." mottakere: ".implode(", ",array_keys($toArray)));
			}
		} else {
			foreach ($toArray as $toKey => $toValue){
				if (!empty($this->eventlog_function)){
					$ef = $this->eventlog_function;
					$ef("E-post sendt til $toKey <$toValue>");
				}
			}
		}
		return true;
	}

}
?>