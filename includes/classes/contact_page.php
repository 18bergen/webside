<?php
class contact_page extends base {

	var $table_vervhistorie = "vervhistorie";
	var $table_verv = "verv";
	var $table_memberlist = "members";

	var $label_phone;
	var $label_cell;
	var $label_email;
	var $label_form;
	
	function contact_page() {
		$this->table_vervhistorie = DBPREFIX.$this->table_vervhistorie;
		$this->table_verv = DBPREFIX.$this->table_verv;
		$this->table_memberlist = DBPREFIX.$this->table_memberlist;		
	}
	
	function initialize() {
		@parent::initialize();	
	}
	
	function run() {
		global $memberdb;
		
		$this->initialize();
		
		$output = "";
		
		$v = $this->table_verv;
		$m = $this->table_memberlist;
		$vh = $this->table_vervhistorie;

		if (isset($this->coolUrlSplitted[0])){
			$verv = addslashes($this->coolUrlSplitted[0]);
			$res = $this->query("SELECT 
					$v.caption as verv_tittel, $vh.person as user_id 
				FROM 
					$v,$vh
				WHERE 
					$v.slug=\"$verv\"
					AND $v.id=$vh.verv 
					AND $vh.enddate IS NULL 
					AND $v.type='gruppe' 
				GROUP BY $v.id"
			);
			if ($res->num_rows != 1) {
				return 'ukjent verv';
			}
			$row = $res->fetch_assoc();
			$url = $memberdb->messageUrl.'.?recipients='.$row['user_id'];
			$_SESSION['msgcenter_infomsg'] = "Mottaker er nåværende ".strtolower($row['verv_tittel']).".";
			$this->redirect($url);
		}
		
		$res = $this->query("SELECT 
				$v.caption, $v.id, $v.slug, $vh.person, $v.beskrivelse, $m.ident 
			FROM 
				$v,$vh,$m 
			WHERE 
				$v.id=$vh.verv 
				AND $m.ident=$vh.person 
				AND $vh.enddate IS NULL 
				AND $v.type='gruppe' 
				AND $v.synlighet=1
			GROUP BY $v.id
			ORDER BY $v.position"
		);
		
		$bg = "";

		if ($this->isLoggedIn() && $memberdb->getMemberById($this->login_identifier)->rights > 4) {
			$output .= $this->make_editlink("/verv", "Rediger verv",false);		
		}

		while ($row = $res->fetch_assoc()){
		
			$medlem = call_user_func($this->lookup_member, $row['ident']);
			$img_medlem = call_user_func($this->lookup_memberimage, $row['ident']);
			//$img_medlem = substr($img_medlem,0,strrpos($img_medlem,"."))."_thumb100".substr($img_medlem,strrpos($img_medlem,"."));
			$tittel = $row["caption"];
			$slug = $row['slug'];
			$output .= '
			<div style="float:left; width: 250px; height: 140px;">
				<div class="alpha-shadow noframe">
  					<div class="inner_div">
						<img src="'.$img_medlem.'" alt="'.$medlem->fullname.'" style="width:75px; height:100px;" />
					</div>
				</div>
				<div style="float:left; padding: 10px 5px 10px 5px;">
						<b>'.$tittel.'</b><br />
						'.call_user_func($this->make_memberlink,$medlem->ident).'<br /><br />
						<table>
							'.(empty($medlem->homephone) ? '' : '<tr><td>'.$this->label_phone.': </td><td>'.$medlem->homephone.'</td></tr>').'
							'.(empty($medlem->cellular) ? '' : '<tr><td>'.$this->label_cell.': </td><td>'.$medlem->cellular.'</td></tr>').'
							<tr><td>'.$this->label_email.': </td><td><a href="'.$this->generateCoolUrl("/$slug").'">'.$this->label_form.'</a></td></tr>
						</table>
				</div>
			</div>
			';
		
		}
		$output .= "<div style='clear:both'><!-- --></div>";
		
		return $output;
		
	}
	
}

?>