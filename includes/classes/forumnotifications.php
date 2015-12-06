<?php
class ForumNotifications {
	
	function getUnread() {
		global $memberdb,$login,$db;

		if ($this->isLoggedIn()){ 
			$userId = $login->getUserId();
		
			$pageIdGruppeForum = 77;
			$pageIdInternForum = 79;
			
			$visInternForum = true;
			$memof = $memberdb->getMemberById($userId)->memberof;
			if (count($memof) == 1){
				$g = $memberdb->getGroupById($memof[0]);
				if ($g->kategori == "FO") $visInternForum = false;
			}
			
			$res = $db->query("SELECT COUNT(*) 
				FROM ".DBPREFIX."forum_unread, ".DBPREFIX."forum_threads 
				WHERE ".DBPREFIX."forum_unread.traad=".DBPREFIX."forum_threads.id 
				AND ".DBPREFIX."forum_unread.bruker='$userId'
				AND ".DBPREFIX."forum_threads.page=$pageIdGruppeForum");
			$rowcnt = $res->fetch_array();
			$nye1 = $rowcnt[0];
			
			$res = $db->query("SELECT COUNT(*) 
				FROM ".DBPREFIX."forum_unread, ".DBPREFIX."forum_threads 
				WHERE ".DBPREFIX."forum_unread.traad=".DBPREFIX."forum_threads.id 
				AND ".DBPREFIX."forum_unread.bruker='$userId'
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
				
				$output = "";
				if ($nye1 > 0) $output .= '
						<span class="notification">
							<a href="/forum/gruppe/" title="'.$nye1.' uleste innlegg i gruppeforumet" class="icn" style="background-image:url(/images/icns/comments.png);">'.$nye1.'</a>
						</span>
					';
				if (!empty($str2)) $output .= '
						<span class="notification">
							<a href="/forum/intern/" title="'.$nye2.' uleste innlegg i internforumet" class="icn" style="background-image:url(/images/icns/comments.png);">'.$nye2.'</a>
						</span>				
					';
				return $output;
			} else {
				return '';
			}			
		} else {
			return '';
		}
	}
	
}

?>