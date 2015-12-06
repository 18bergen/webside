<?php
class mysqldb extends mysqli {

	var $image_dir;

	public function __construct(){
		global $dbHost, $dbUser, $dbPass, $dbName, $dbPipe;

		parent::__construct($dbHost,$dbUser,$dbPass,$dbName, NULL, $dbPipe);

		if (!empty($this->connect_error)) {
			die("
				<div style=\"background:#FFFFFF; border:#FF0000 2px solid; margin:10px; padding:10px; font: 12px 'Arial';\">
					<img src=\"/images/warning.gif\" style=\"float:left; width: 88px; height: 77px; margin-right: 10px; margin-bottom: 10px;\" />
					<p style='margin-top:0px;'>
						<strong>Siden er midlertidig utilgjengelig.</strong>
					</p>
					<p>
						Vi får for tiden ikke kontakt med databasen vår. Dette skyldes trolig midlertidige
						driftsproblemer hos Domeneshop.<br /> ".$this->connect_error."
					</p>
					<div style='clear:both;'></div>
				</div>
			");	
			//die('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error);
			exit();
		}
		if (isset($_SERVER['TERM'])) {
			printf("    Connected to %s\n       version: %s\n",
			$this->host_info,$this->server_info);
		}
		if (method_exists($this,'set_charset')) {
			$this->set_charset('utf8'); /* This is the preferred way to change the charset.
				Using mysqli::query() to execute SET NAMES .. is not recommended */
		} else {
			// Fall back to old method:
			$this->query("SET NAMES 'utf8'");
		}
			
		//$this->query("SET NAMES 'utf8'");


		/*printf("<pre>Connected to %s\nMySQL version: %s\nClient library version: %s\nProtocol version: %s\nCharacter set is %s\n</pre>",
			$this->host_info,$this->server_info,$this->get_client_info(),$this->protocol_version,$this->character_set_name());
		*/
		//exit();
	}

	function query($str,$logError = true, $silentErrors = false, $debug = false){
		global $eventLog, $stime;
		if (isset($eventLog)){ 
			$time_string2 = explode(" ", microtime());
			$etime = $time_string2[1] . substr($time_string2[0], 1, strlen($time_string2[0]));
			$eventLog->addToQueryLog(substr($etime - $stime, 0, 8).": ".$str);
		}
		global $queryCount;
		$queryCount++;
        // DEBUG: print "<tt>$str</tt><br />";
        if ($debug) print "<tt>$str</tt><br />";
		$res = parent::query($str) or $this->onError($str,mysql_error(),$logError, $silentErrors);
		return $res;
	}

	function onError($Query, $Error, $logError = true, $silentErrors = false){
		global $eventLog, $login;
		if ($silentErrors) return;
		if ($logError == true){
			if (isset($login) && $login->isLoggedIn() && $login->getUserId() == 1){	
				print "
				<div style=\"background:#FFFFFF; border:#FF0000 2px solid; margin:10px; padding:10px; font: 12px 'Arial';\">
					<img src=\"".$this->image_dir."warning.gif\" style=\"float:left; width: 88px; height: 77px; margin-right: 10px; margin-bottom: 10px;\" />
					<p style='margin-top:0px;'>
						<strong>Det oppstod en feil under kommunikasjon med databasen.</strong>
					</p>
					<p>
						<tt>$Query</tt>
					</p>
					<p>
						<tt>$Error</tt>
					</p>
					<p>
						Feilen logges og vil bli sett på.
					</p>
					<div style='clear:both;'></div>
				</div>
				";
			} else {
				print "
				<div style=\"background:#FFFFFF; border:#FF0000 2px solid; margin:10px; padding:10px; font: 12px 'Arial';\">
					<img src=\"".$this->image_dir."warning.gif\" style=\"float:left; width: 88px; height: 77px; margin-right: 10px; margin-bottom: 10px;\" />
					<p style='margin-top:0px;'>
						<strong>Det oppstod en feil under kommunikasjon med databasen.</strong>
					</p>
					<p>
						<tt>$Query</tt>
					</p>
					<p>
						<tt>$Error</tt>
					</p>
					<p>
						Feilen logges og vil bli sett på.
					</p>
					<div style='clear:both;'></div>
				</div>
				";
			}
			if (isset($eventLog)){
				$eventLog->addToErrorLog("MySQL Error<br />\nQuery: $Query<br />\nResult: $Error");
			}
		} else {
			print "
				<div style=\"background:#FFFFFF; border:#FF0000 2px solid; margin:10px; padding:10px; font: 12px 'Arial';\">
					<img src=\"".$this->image_dir."warning.gif\" style=\"float:left; width: 88px; height: 77px; margin-right: 10px; margin-bottom: 10px;\" />
					<p style='margin-top:0px;'>
						<strong>Det oppstod en feil ved logging av feilmeldingen.</strong>
					</p>
					<p>
						Får ikke kontakt med databasen. Er den nede?<br />
						Antakelig på tide å klage til Domeneshop...
					</p>
					<div style='clear:both;'></div>
				</div>
			";
			
		}
		exit();
	}

}

?>
