<?

class ForumNotifications extends base {

	function ForumNotifications() {
	
	}
	
	function getUnread() {
		global $memberdb;

		if ($this->isLoggedIn() == true){ 
		
			$pageIdGruppeForum = 77;
			$pageIdInternForum = 79;
			
			$visInternForum = true;
			$memof = $memberdb->getMemberById($this->login_identifier)->memberof;
			if (count($memof) == 1){
				$g = $memberdb->getGroupById($memof[0]);
				if ($g->kategori == "FO") $visInternForum = false;
			}
			
			$res = $db->query("SELECT COUNT(*) 
				FROM ".DBPREFIX."forum_unread, ".DBPREFIX."forum_threads 
				WHERE ".DBPREFIX."forum_unread.traad=".DBPREFIX."forum_threads.id 
				AND ".DBPREFIX."forum_unread.bruker='$this->login_identifier'
				AND ".DBPREFIX."forum_threads.page=$pageIdGruppeForum");
			$rowcnt = $res->fetch_array();
			$nye1 = $rowcnt[0];
			
			$res = $db->query("SELECT COUNT(*) 
				FROM ".DBPREFIX."forum_unread, ".DBPREFIX."forum_threads 
				WHERE ".DBPREFIX."forum_unread.traad=".DBPREFIX."forum_threads.id 
				AND ".DBPREFIX."forum_unread.bruker='$this->login_identifier'
				AND ".DBPREFIX."forum_threads.page=$pageIdInternForum");
			$rowcnt = $res->fetch_array();
			$nye2 = $rowcnt[0];
		
			if ($nye1 || $nye2) {
			
				$str1 = ($nye1 > 0) ? "$nye1 ulest".(($nye1 == 1) ? "":"e")." innlegg i gruppeforumet" : "";
				
				if ($visInternForum){
					$str2 = ($nye2 > 0) ? "$nye2 ulest".(($nye2 == 1) ? "":"e")." innlegg i internforumet" : "";
				} else {
					$str2 = "";
				}
				
				print("  <h2 class='small'>Forumet</h2>
					<div class='calendarItem1Mini'>
						".
						(empty($str1) ? "" : "
							<div>
								<img src='$site_rootdir/images/document.gif' alt='Nye meldinger på forumet' width='13' height='13' /> 
								<a href=\"$site_rootdir/forum/gruppe/\" class=\"calendarSubjectMini\">$str1</a>
							</div>
						").
						(empty($str2) ? "" : "
							<div>
								<img src='$site_rootdir/images/document.gif' alt='Nye meldinger på forumet' width='13' height='13' /> 
								<a href=\"$site_rootdir/forum/intern/\" class=\"calendarSubjectMini\">$str2</a>
							</div>
						")."
						
					</div>"
				);
			}
		}
	}
}

?>