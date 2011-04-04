<?

class eventLog {
	
	private $eventTypes = array(
		'USER_REGISTERED'               => array( 'event_text' => '%firstname% ble registrert på nettsiden' ),
		'USER_JOINED_GROUP'             => array( 'icon' => 'group_add', 'event_text' => '%firstname% ble med i [group]' ),
		'USER_LEFT_GROUP'               => array( 'icon' => 'group_delete', 'event_text' => '%firstname% forlot [group]' ),
		'PATROL_LEADER_CHANGED'         => array( 'event_text' => 'DATO ble %1% utnevnt til å overta som peff etter %2%' ),
		'PATROL_ASSISTANT_CHANGED'      => array( 'event_text' => 'DATO ble %1% utnevnt til å overta som ass etter %2%' ),
		'ACCOUNT_CREATED'               => array( 'event_text' => '%firstname% ble tildelt brukerkonto' ),
		'ACCOUNT_REMINDER_SENT'         => array( 'event_text' => 'Påminnelse ble sendt om aktivering av brukerkonto' ),
		'ACCOUNT_ACTIVATED'             => array( 'event_text' => 'Konto aktivert' ),
		'USER_LOGGED_IN'                => array( 'event_text' => '%firstname% logget inn' ),
		'USER_LOGGED_OUT'               => array( 'event_text' => '%firstname% logget ut' ),

		'USER_PROFILE_NAME_UPDATED'     => array( 'icon' => 'vcard', 'event_text' => 'Navn oppdatert fra «%1%» til «%2%».' ),   # %1% => bursdag og profilbilde
		'USER_PROFILE_EMAIL_UPDATED'    => array( 'icon' => 'vcard', 'event_text' => 'Epostadresse oppdatert fra «%1%» til «%2%».' ),   # %1% => bursdag og profilbilde
		'USER_PROFILE_ADDRESS_ADDED'    => array( 'icon' => 'vcard', 'event_text' => 'Hjemmeadresse lagt til: %1%.' ),   # %1% => bursdag og profilbilde
		'USER_PROFILE_ADDRESS_UPDATED'  => array( 'icon' => 'vcard', 'event_text' => 'Hjemmeadresse oppdatert fra «%1%» til «%2%».' ),   # %1% => bursdag og profilbilde
		'USER_PROFILE_PHONE_ADDED'      => array( 'icon' => 'vcard', 'event_text' => 'Telefonnummer lagt til: %1%.' ),   # %1% => bursdag og profilbilde
		'USER_PROFILE_PHONE_UPDATED'    => array( 'icon' => 'vcard', 'event_text' => 'Telefonnummer oppdatert %1% til %2%.' ),   # %1% => bursdag og profilbilde
		'USER_PROFILE_PHONE_REMOVED'    => array( 'icon' => 'vcard', 'event_text' => 'Telefonnummer fjernet: %1%.' ),   # %1% => bursdag og profilbilde
		'USER_PROFILE_WWW_ADDED'        => array( 'icon' => 'vcard', 'event_text' => 'Nettside lagt til: <a href="%1%">%1%</a>.' ),   # %1% => bursdag og profilbilde
		'USER_PROFILE_WWW_UPDATED'      => array( 'icon' => 'vcard', 'event_text' => 'Nettside oppdatert fra <a href="%1%">%1%</a> til <a href="%2%">%2%</a>.' ),   # %1% => bursdag og profilbilde
		'USER_PROFILE_WWW_REMOVED'      => array( 'icon' => 'vcard', 'event_text' => 'Nettside fjernet: <a href="%1%">%1%</a>.' ),   # %1% => bursdag og profilbilde
		'USER_PROFILE_PHOTO_UPDATED'    => array( 'icon' => 'vcard', 'event_text' => 'Profilbilde oppdatert:<br /> <img src="%1%" style="width:50px;" valign="top" />' ),   # %1% => bursdag og profilbilde
		'USER_FORUM_AVATAR_UPDATED'     => array( 'icon' => 'vcard', 'event_text' => 'Forumavatar oppdatert:<br /> <img src="%1%" style="width:50px;" valign="top" />' ),   # %1% => bursdag og profilbilde

		'ROLE_CHANGED'                  => array( 'event_text' => '%firstname%s rolle ble endret fra «%1%» til «%2%»' ),
		'PARENT_ADDED'                  => array( 'event_text' => '[user=%1%] ble registrert som foresatt for %firstname%.' ),
		'PARENT_REMOVED'                => array( 'event_text' => '[user=%1%] ble avregistrert som foresatt for %firstname%.' ),
		'CHILD_ADDED'                   => array( 'event_text' => '%firstname% ble registrert som foresatt for [user=%1%]' ),
		'CHILD_REMOVED'                 => array( 'event_text' => '%firstname% ble avregistrert som foresatt for [user=%1%]' ),

		'GROUP_CREATED'                 => array( 'event_text' => '%name% ble opprettet.' ),
		
		'ADDED_NEWS_ENTRY'              => array( 'icon' => 'newspaper_add', 'event_text' => 'skrev nyhetssaken «%1%».' ),
		
		'CUSTOM_EVENT'                  => array( )
	);
	
	private $_db_instance;
	private $_login_instance;
	private $_memberlist_instance;

	/* Constructor */
	function eventLog(){
		$this->queryLog = array();
	}
	
	function setLoginInstance($i) { $this->_login_instance = $i; }
	function setDbInstance($i) { $this->_db_instance = $i; }
	function setMemberlistInstance($i) { $this->_memberlist_instance = $i; }
	
	function checkLinks() {
		if (!isset($this->_db_instance)) {
		
		}
		if (!isset($this->_login_instance)) {
		
		}
	}

	function addToErrorLog($str){
		global $db, $login;
		$msg = addslashes(htmlspecialchars($str));
		$uname = 0;
		if ($login) $uname = $login->getUserId();
		$page = $_SERVER["REQUEST_URI"];
		if ((isset($_SERVER['HTTP_REFERER'])) && ($_SERVER['HTTP_REFERER'] != "/")){
			$referer = addslashes($_SERVER['HTTP_REFERER']);
		} else {
			$referer = "No referer";
		}
		$browser = (isset($_SERVER['HTTP_USER_AGENT']) ? addslashes($_SERVER['HTTP_USER_AGENT']) : "Ukjent");
		$db->query("INSERT INTO ".DBPREFIX."log_error (user, msg, page, referer, browser) VALUES ($uname,\"$msg\",\"$page\",\"$referer\",\"$browser\")",false);
	}

	function addToActivityLog($str,$unique = false,$type="minor"){
		global $db, $login;
		$activity = addslashes($str);
		$uname = 0;
		if ($login) $uname = $login->getUserId();
		if ($unique) {
			$res = $db->query("SELECT id,activity FROM ".DBPREFIX."log_activity WHERE user=$uname AND activity=\"$activity\" AND timestamp > DATE_SUB(NOW(), INTERVAL 1 DAY)");
			if ($res->num_rows == 1) {
				$row = $res->fetch_assoc();
				$id = $row['id'];
				$db->query("UPDATE ".DBPREFIX."log_activity SET timestamp=NOW() WHERE id=$id");
				return;
			}
		}
		$page = addslashes("index.php?".$_SERVER["QUERY_STRING"]);
		$browser = (isset($_SERVER['HTTP_USER_AGENT']) ? addslashes($_SERVER['HTTP_USER_AGENT']) : "Ukjent");
		$db->query("INSERT INTO ".DBPREFIX."log_activity (user, activity, page, browser,type) VALUES (\"$uname\",\"$activity\",\"$page\",\"$browser\",\"$type\")");
	}
	
	/*
	Examples:
	addToEventLog('USER_REGISTERED', $user_id, 0)
	addToEventLog('USER_JOINED_GROUP', $user_id, $group_id)
	*/
	function logEvent($event_type, $user_id, $group_id = 0, $field1 = "", $field2 = "", $field3 = ""){
		$this->checkLinks();
		$login_id = 0;
		if (isset($this->_login_instance)) $login_id = intval($this->_login_instance->getUserId());
		$user_id = intval($user_id);
		$group_id = intval($group_id);
		if (!isset($this->eventTypes[$event_type])) {
			print "Unknown event type: ".addslashes($event_type);
			exit();
		}
		$this->_db_instance->query("INSERT INTO ".DBPREFIX."events (event_type,user_id,group_id,event_field1,event_field2,event_field3,executed_by,timestamp) 
		  VALUES (\"$event_type\",$user_id,$group_id,\"".addslashes($field1)."\",\"".addslashes($field2)."\",\"".addslashes($field3)."\",$login_id,NOW())");
	}
	
	function makeUserLinkFromMatch($matches) {
		$user_id = intval($matches[1]);
		return $this->makeUserLink($user_id);
	}
	
	function makeUserLink($user_id) {
		$udata = $this->_memberlist_instance->getUserData($user_id, array('FullName','ProfileUrl'));
		return '<a href="'.$udata['ProfileUrl'].'">'.$udata['FullName'].'</a>';
	}

	function makeGroupLink($group_id) {
		$gdata = $this->_memberlist_instance->getGroupById($group_id);
		return '<a href="'.$this->_memberlist_instance->getGroupUrl($group_id).'">'.$gdata->caption.'</a>';
	}
	
	function getLatestUserEvents($user_id, $count = 10) {
		$this->checkLinks();
		$user_id = intval($user_id);
		$count = intval($count);
		$udata = $this->_memberlist_instance->getUserData($user_id, array('FirstName'));
		$user_link = $this->makeUserLink($user_id);
		$res = $this->_db_instance->query("SELECT event_type,group_id,event_field1,event_field2,event_field3,timestamp FROM ".DBPREFIX."events WHERE user_id=$user_id ORDER BY timestamp desc, id desc LIMIT $count");
		$a = array();
		while ($row = $res->fetch_assoc()) {
			$eventType = $row['event_type'];
			$icon = 'information';

			if ($eventType == 'CUSTOM_EVENT') {
				$a[] = stripslashes($row['event_field1']);				
			} else {
				$str = $this->eventTypes[$eventType]['event_text'];
				if (isset($this->eventTypes[$eventType]['icon'])) {
					$icon = $this->eventTypes[$eventType]['icon'];
				}
				$str = str_replace('%1%',$row['event_field1'],$str);
				$str = str_replace('%2%',$row['event_field2'],$str);
				$str = str_replace('%firstname%',$udata['FirstName'],$str);
				$str = preg_replace_callback('/\[user=([0-9]+)\]/', array( $this, 'makeUserLinkFromMatch' ), $str);
				if ($row['group_id'] != 0) $str = str_replace('[group]',$this->makeGroupLink($row['group_id']),$str);
				$str = str_replace('[user]',$user_link,$str);
				$a[] = array('timestamp' => strtotime($row['timestamp']), 'icon' => $icon, 'text' => $str);
			}
		}	
		return $a;
	}

	function getLatestUserEventsOfType($user_id, $event_type, $count = 10) {
	
	}

	function addToQueryLog($str){
		//array_push($this->queryLog,"querylog disabled");
		array_push($this->queryLog,$str);
	}

}

?>