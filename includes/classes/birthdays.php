<?php
class birthdays extends base {

	function ouputNextBirthdays($count = 5){
		$bursdagsbarn = $this->getActiveMembersList(array('SortBy' => 'Birthday', 'Limit' => $count));
		$output = "<h2 class=\"small\">Bursdager</h2>\n";						
		$today = strftime("%e. %B",time());
		foreach ($bursdagsbarn as $user_id => $u) {
			$daystring = strftime("%e. %B",strtotime($u['Birthday']));
			if ($this->isLoggedIn()) $p = '<a href="'.$u['ProfileUrl'].'">'.$u['FullName'].'</a>';
			else $p = '<a href="'.$u['ProfileUrl'].'">'.$u['FirstName'].'</a>';

			$output .= '<div style="padding:1px;">';
			if ($daystring == $today) {				
				$output .= '
					<a class="icn" style="background-image:url(/images/icns/cake.png);">
						<strong>I dag: </strong>'.$p.'
				';			
			} else {
				$output .= $daystring.': '.$p;
			}
			$output .= '</div>';
		}
		return $output;
	}

}

?>