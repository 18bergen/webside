<?

class install extends base {
	
	function initialize() {
		$this->initialize_base();
	}
	
	function run() {	
		print "
			<p>
		";
		if (empty($this->login_identifier)) {
			print "Du er ikke logget inn. ";		
		} else {
			print "Du er logget inn som ".call_user_func($this->make_memberlink, $this->login_identifier);
		}
		print "
			</p>
			<p> 
				Administreringsverktøy:
				<ul>
					<li><a href='cms/'>CMS</a></li>
					<li><a href='medlemmer/'>Medlemsliste</a></li>
				</ul>
			</p>
		";
	}

}


?>