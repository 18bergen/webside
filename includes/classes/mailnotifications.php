<?

class MailNotifications {
	
	static function getUnread() {
		global $memberdb,$db,$login;

		if ($login->isLoggedIn()){ 
			$userId = $login->getUserId();
	
			$visInternForum = true;
			$groups = $memberdb->getActiveGroupMemberships($userId);
			if (count($groups) == 1){
				if ($groups[0]['Category'] == "FO") $visInternForum = false;
			}
			
			$res = $db->query("SELECT
					COUNT(id)
				FROM 
					".DBPREFIX."messages_users
				WHERE 
					owner=$userId
					AND deleted=0
					AND is_read=0"
			);
			$rowcnt = $res->fetch_row();
			$nye = $rowcnt[0];	
			if ($nye) {
				$str = "Du har $nye ny".(($nye == 1) ? "":"e")." melding".(($nye == 1) ? "":"er")."!";				
				return '
							<span class="notification">
								<a href="/meldingssenter/" class="icn" style="background-image:url(/images/icns/email.png);" title="Klikk for å gå til meldingssenteret.">'.$str.'</a>
							</span>
					';
			} else return '';
		} else return '';
	}
	
}

?>