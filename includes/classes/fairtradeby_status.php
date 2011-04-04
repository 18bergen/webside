<?

class fairtradeby_status extends base {

	var $getvars = array();

	var $table_status;
	var $table_chains;
	var $table_items;
	var $table_availability;
	var $table_brands;
	var $table_itemcats;
	
	var $allow_addentry = false;
	var $allow_editcats = false;
	var $allow_editchains = false;
	var $allow_edititems = false;
	var $allow_viewcontactdetails = false;
	
	var $default_city = "";
	var $googleMapsKey = "";
	
	var $agreementStates = array("Forslag", "Under behandling", "Inngått");
	var $agreementStatesDesc = array(
		"Dette er kun et forslag til en avtale. Det kan godt være den eksterne parten ikke en gang har kjennskap til den.",
		"Denne avtalen er under behandling",
		"Denne avtalen er inngått og kontrollert av kampanjen."
	);
	var $schema_template = "
		
		<h3>Legg til ny avtale</h3>
		<script type=\"text/javascript\">
		//<![CDATA[
		
		function saveNewPos(lat,lng) {
			\$('latitude').value = lat;
			\$('longitude').value = lng;
		}
		
		//]]>
		</script>
		
		<form method='post' action='%posturl%'>
			%errors%
			<input type='hidden' name='id' value='%id%' />
			<table cellpadding='2' cellspacing='0' class='skjema'>
				<tr><td width='70' valign='top'><strong>Kategori: </strong></td><td>
					%cats%		
				</td></tr>
				<tr><td width='70' valign='top'><strong>Kjede: </strong></td><td>
					%chains%		
				</td></tr>
				<tr><td width='70' valign='top'><strong>Status: </strong></td><td>
					%status%		
				</td></tr>
				
				<tr>
					<td><strong>Unikt navn: </strong></td>
					<td><input type=\"text\" size=\"50\" name=\"unique_name\" value=\"%unique_name%\" /></td>
				</tr><tr>
					<td valign='top'><strong>Adresse: </strong></td>
					<td>

						<table cellspacing='0'><tr><td>
						<label for='street'>
							Gate: <br />
							<input type=\"text\" size=\"40\" name=\"street\" id=\"street\" value=\"%street%\" />
						</label>
						</td><td>
						<label for='streetno'>
							Nr: <br />
							<input type=\"text\" size=\"5\" name=\"streetno\" id=\"streetno\" value=\"%streetno%\" />
						</label>
						</td></tr></table>

						<table cellspacing='0'><tr><td>
						<label for='postno'>
							Postnr.:<br />
							<input type=\"text\" size=\"7\" name=\"postno\" id=\"postno\" value=\"%postno%\" />
						</label>
						</td><td>
						<label for='city'>
							Sted:<br />
							<input type=\"text\" size=\"30\" name=\"city\" id=\"city\" value=\"%city%\" />
						</label>
						</td></tr></table>

						<table cellspacing='0'><tr><td>
						<label for='latitude'>
							Breddegrad:<br />
							<input type=\"text\" size=\"20\" name=\"latitude\" id=\"latitude\" value=\"%latitude%\" />
						</label>
						</td><td>
						<label for='longitude'>
							Lengdegrad:<br />
							<input type=\"text\" size=\"20\" name=\"longitude\" id=\"longitude\" value=\"%longitude%\" />
						</label>
						</td><td>&nbsp;<br />
						<a href=\"%get_pos_url%\" onclick=\"%get_pos_js%\">Hent fra kart</a>
						</td></tr></table>

					</td>
				</tr>
				<tr>
					<td valign='top'><strong>Kontaktperson: </strong></td>
					<td>
						<label for='contact_name'>
							Navn: <br />
							<input type=\"text\" size=\"50\" name=\"contact_name\" id=\"contact_name\" value=\"%contact_name%\" />
						</label>
						<table cellspacing='0'><tr><td>
						<label for='contact_email'>
							E-post: <br />
							<input type=\"text\" size=\"30\" name=\"contact_email\" id=\"contact_email\" value=\"%contact_email%\" />
						</label>
						</td><td>
						<label for='contact_phone'>
							Tlf.: <br />
							<input type=\"text\" size=\"20\" name=\"contact_phone\" id=\"contact_phone\" value=\"%contact_phone%\" />
						</label>
						</td></tr></table>
					</td>
				</tr><tr>
					<td valign=\"top\"><strong>Kommentar: </strong></td>
					<td>
						<textarea name=\"comment\" style=\"width: 400px; height: 150px;\">%comment%</textarea>
					</td>
				</tr><tr>
					<td></td>
					<td><input type=\"submit\" value=\"Lagre\" class=\"button\" /> </td>
				</tr>
			</table>
		</form>
	";
	
	function initialize(){
	
		$this->initialize_base();
		
		if (count($this->coolUrlSplitted) > 0) 
			$this->action = $this->coolUrlSplitted[0];
		else 
			$this->action = "";
	
	}

	function run(){
		$this->initialize();

		switch ($this->action) {

			case 'new-agreement':
				$this->editAgreement(0);
				break;

			case 'edit-agreement':
				if (count($this->coolUrlSplitted) > 1)
					$this->editAgreement($this->coolUrlSplitted[1]);				
				break;
				
			case 'save-agreement':
				$this->saveAgreement();
				break;

			case 'agreement':
				if (count($this->coolUrlSplitted) > 1)
					$this->viewAgreement($this->coolUrlSplitted[1]);				
				break;
				
			case 'map':
				$this->viewMap();
				break;

			case 'fetch-pos':
				if (count($this->coolUrlSplitted) > 1)
					$this->fetchPos($this->coolUrlSplitted[1]);
				break;
			
			case 'edit-availability':
				if (count($this->coolUrlSplitted) > 1)
					$this->editAvailability($this->coolUrlSplitted[1]);				
				break;

			case 'save-availability':
				$this->saveAvailability();				
				break;
			
			/******* CATEGORIES *******/

			case 'add-cat':
				$this->addCat();
				break;

			case 'save-cat':
				$this->saveCat();
				break;
				
			case 'edit-cats':
				$this->editCatsForm();
				break;
				
			case 'edit-cat':
				if (count($this->coolUrlSplitted) > 1)
					$this->editCat($this->coolUrlSplitted[1]);				
				break;

			case 'update-cat':
				$this->updateCat();
				break;

			case 'delete-cat':
				if (count($this->coolUrlSplitted) > 1)
					$this->deleteCatQuery($this->coolUrlSplitted[1]);				
				break;

			case 'delete-cat-do':
				$this->deleteCat();
				break;
				
			/******* CHAINS *******/

			case 'add-chain':
				$this->addChain();
				break;

			case 'save-chain':
				$this->saveChain();
				break;
				
			case 'edit-chains':
				$this->editChainsForm();
				break;

			case 'edit-chain':
				if (count($this->coolUrlSplitted) > 1)
					$this->editChain($this->coolUrlSplitted[1]);				
				break;

			case 'update-chain':
				$this->updateChain();
				break;

			case 'delete-chain':
				if (count($this->coolUrlSplitted) > 1)
					$this->deleteChainQuery($this->coolUrlSplitted[1]);				
				break;

			case 'delete-chain-do':
				$this->deleteChain();
				break;
				
			/******* ITEMS *******/

			case 'item':
				if (count($this->coolUrlSplitted) > 1)
					$this->viewItem($this->coolUrlSplitted[1]);				
				break;

			case 'add-item':
				$this->addItem();
				break;

			case 'save-item':
				$this->saveItem();
				break;

			case 'edit-items':
				$this->editItemsForm();
				break;

			case 'edit-item':
				if (count($this->coolUrlSplitted) > 1)
					$this->editItem($this->coolUrlSplitted[1]);				
				break;

			case 'update-item':
				$this->updateItem();
				break;

			case 'delete-item':
				if (count($this->coolUrlSplitted) > 1)
					$this->deleteItemQuery($this->coolUrlSplitted[1]);				
				break;

			case 'delete-item-do':
				$this->deleteItem();
				break;
				
			/******* ITEM-CATS *******/

			case 'add-itemcat':
				$this->addItemCat();
				break;

			case 'save-itemcat':
				$this->saveItemCat();
				break;

			case 'edit-itemcats':
				$this->editItemCatsForm();
				break;

			case 'edit-itemcat':
				if (count($this->coolUrlSplitted) > 1)
					$this->editItemCat($this->coolUrlSplitted[1]);				
				break;

			case 'update-itemcat':
				$this->updateItemCat();
				break;

			case 'delete-itemcat':
				if (count($this->coolUrlSplitted) > 1)
					$this->deleteItemCatQuery($this->coolUrlSplitted[1]);				
				break;

			case 'delete-itemcat-do':
				$this->deleteItemCat();
				break;
							
			/******* BRANDS *******/

			case 'add-brand':
				$this->addBrand();
				break;

			case 'save-brand':
				$this->saveBrand();
				break;

			case 'edit-brands':
				$this->editBrandsForm();
				break;

			case 'edit-brand':
				if (count($this->coolUrlSplitted) > 1)
					$this->editBrand($this->coolUrlSplitted[1]);				
				break;

			case 'update-brand':
				$this->updateBrand();
				break;

			case 'delete-brand':
				if (count($this->coolUrlSplitted) > 1)
					$this->deleteBrandQuery($this->coolUrlSplitted[1]);				
				break;

			case 'delete-brand-do':
				$this->deleteBrand();
				break;

			/******* DEFAULT *******/
			
			default:
				$this->viewList();
				break;
		
		}
	}
	
	function getCatGoal($id) {
		$res = $this->query("SELECT goal FROM $this->table_cats WHERE id='$id'");
		if ($res->num_rows != 1) {
			$this->notSoFatalError("Kategorien eksisterer ikke"); return 0;
		}
		$row = $res->fetch_assoc();
		return $row['goal'];
	
	}
	function viewList() {
	
		$url_map = $this->generateCoolUrl("/map/");
		print "
			<p>
				<a href='$url_map'>Vis kart</a>
		";

		if ($this->allow_addentry) {
			$url_add = $this->generateCoolUrl("/new-agreement/");
			$url_cats = $this->generateCoolUrl("/edit-cats/");
			$url_chains = $this->generateCoolUrl("/edit-chains/");
			$url_items = $this->generateCoolUrl("/edit-items/");
			$url_itemcats = $this->generateCoolUrl("/edit-itemcats/");
			$url_brands = $this->generateCoolUrl("/edit-brands/");
			print "
					| <a href='$url_add'>Legg til ny avtale</a> | Rediger 
					".($this->allow_editcats ? " <a href='$url_cats'>kategorier</a>," : "")."
					".($this->allow_editchains ? " <a href='$url_chains'>kjeder</a>, " : "")."
					".($this->allow_edititems ? " <a href='$url_brands'>varemerker</a>, " : "")."
					".($this->allow_edititems ? " <a href='$url_items'>varer</a>, " : "")."
					".($this->allow_edititems ? " <a href='$url_itemcats'>varekategorier</a>" : "")."
			";
		}
		print "
			</p>
		";
		
		$current_cat = 0;
		$cat_goal = 0;
		$cat_count = 0;
		$cat_agreed = 0;
		$ts = $this->table_status;
		$tc = $this->table_cats;
		$res = $this->query("SELECT 
			$ts.id,
			$ts.unique_name,
			$ts.status,
			$ts.category,
			$tc.cat_name,
			$tc.cat_name_plural
			FROM $ts,$tc 
			WHERE $ts.category=$tc.id
			ORDER BY $ts.category, $ts.lastmodified DESC"
		);
		print "<table style='width:100%' cellspacing='0'>";
		$c = 0;
		while ($row = $res->fetch_assoc()) {
			$cat_id = $row['category'];			
			if ($cat_id != $current_cat) {
				if ($current_cat != 0) {
					print "
						<tr><td> </td><td><div style='border-top: 1px solid #999; padding-top:3px;'>$cat_count foreslått, $cat_agreed inngått, målet er $cat_goal.</div></td></tr>
					";
				}
				$cat_name = stripslashes($row['cat_name']);
				$cat_name_plural = stripslashes($row['cat_name_plural']);
				$current_cat = $cat_id;
				$cat_goal = $this->getCatGoal($cat_id);
				$cat_count = 0;
				$cat_agreed = 0;
				print "
					<tr><td valign='bottom'><strong style='padding-top:20px; display: block;'>$cat_name_plural:</strong></td><td valign='bottom'><em style='color: #999;'>Status:</em></td></tr>
				";
			}
			$id = $row['id'];
			$ename = stripslashes($row['unique_name']);
			$status = $this->agreementStates[$row['status']];
			$url_this = $this->generateCoolUrl("/agreement/$id");
			$cat_count++;
			if ($row['status'] == 2) $cat_agreed++;
			$c = !$c;
			$s = $c ? " style='background:#eee;'":"";
			print "
				<tr$s><td><a href='$url_this'>$ename</a></td><td>$status</td></tr>
			";
		}
		if ($current_cat != 0) {
			print "
				<tr><td> </td><td><div style='border-top: 1px solid #999; padding-top:3px;'>$cat_count foreslått, $cat_agreed inngått, målet er $cat_goal.</div></td></tr>
			";
		}
		print "</table>";
		
	}
	
	function viewAgreement($id) {
		if (!is_numeric($id)) { $this->notSoFatalError("invalid id"); return; }
		$ts = $this->table_status;
		$tc = $this->table_cats;
		$ta = $this->table_availability;
		$ti = $this->table_items;
		$tc = $this->table_itemcats;
		$tb = $this->table_brands;
		$res = $this->query("SELECT 
				$ts.id, 
				$ts.unique_name,
				$ts.status,
				$ts.street,
				$ts.streetno,
				$ts.postno,
				$ts.city,
				$ts.contact_name,
				$ts.contact_email,
				$ts.contact_phone,
				$ts.comment,
				$ts.latitude,
				$ts.longitude,
				$ts.created,
				$ts.lastmodified,
				$tc.cat_name
			FROM 
				$ts,$tc 
			WHERE 
				$ts.id='$id'
				AND $ts.category=$tc.id"
		);
		if ($res->num_rows != 1) { $this->notSoFatalError("invalid agreement"); return; }
		$row = $res->fetch_assoc();
		
		$unique_name = stripslashes($row['unique_name']);
		$street = stripslashes($row['street']);
		$streetno = stripslashes($row['streetno']);
		$postno = stripslashes($row['postno']);
		$city = stripslashes($row['city']);
		$contact_person = stripslashes($row['contact_name']);
		$comment = stripslashes($row['comment']);
		$lat = $row['latitude'];
		$lng = $row['longitude'];
		$status = $row['status'];
		$created = $row['created'];
		$lastmod = $row['lastmodified'];
		
		if (empty($street)) {
			$address = "\n";
		} else {
			$address = "<tr><td><strong>Adresse: </strong></td><td>$street $streetno, $postno $city</td></tr>\n";
		}
		
		if (empty($contact_person)) {
			$contact_person = "\n";
		} else {
			if ($this->allow_viewcontactdetails) 
				$contact_person = "<tr><td><strong>Kontaktperson: </strong></td><td>$contact_person (".stripslashes($row['contact_phone']).", ".stripslashes($row['contact_email']).")</td></tr>\n";
			else
				$contact_person = "<tr><td><strong>Kontaktperson: </strong></td><td>$contact_person (<em>Logg inn for å vise kontakt-info</em>)</td></tr>\n";
		}		
		print "
			<h3>$unique_name</h3>
		";
		print "<p>";
		if ($this->allow_addentry) {
			$url_edit = $this->generateCoolUrl("/edit-agreement/$id");
			print "<a href='$url_edit'>Rediger avtale</a>";
		}
		if ($this->allow_edititems) {
			$url_edit = $this->generateCoolUrl("/edit-availability/$id");
			print " | <a href='$url_edit'>Rediger vareutvalg</a>";
		}
		
		$res = $this->query("SELECT 
				$ti.id as item_id,
				$ti.item_name,
				$tc.cat_name
			FROM 
				$ti,$ta,$tc
			WHERE $ta.item=$ti.id AND $ta.store=$id AND $ti.category=$tc.id ORDER BY $tc.cat_name"
		);
		if ($res->num_rows > 0) {
			$utvalg = "<ul>";
			$current_cat = "";
			while ($row = $res->fetch_assoc()) {
				$cat = stripslashes($row['cat_name']);
				if ($current_cat != $cat) {
					$current_cat = $cat;
					$utvalg .= "</ul><div style='clear:both'><!-- --></div><div style='float:left; font-weight: bold; color: #777;'>$cat: </div><ul style='float:left; margin: 0; padding: 0;'>";
				}
				$utvalg .= "<li style='list-style-type:none; float:left; background:none; padding: 0px 5px 0px 5px;'><a href=\"".$this->generateCoolUrl("/item/".$row['item_id'])."\">".stripslashes($row['item_name'])."</a></li>";
			}
			$utvalg .= "</ul>";
			$vareutvalg = "
			<tr><td valign='top'>
				<strong>Vareutvalg: </strong></td><td>$utvalg
			</td></tr>
			";
		} else {
			$vareutvalg = "";
		}

		print "
			</p>
			<table>
				<tr><td><strong>Navn: </strong></td><td>$unique_name</td></tr>
				$address
				$contact_person
				$vareutvalg
				<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
				<tr><td><strong>Avtale-status: </strong></td><td><abbr title=\"".$this->agreementStatesDesc[$status]."\">".$this->agreementStates[$status]."</abbr> (Opprettet: ".date("d.m.Y",$created).", sist endret: ".date("d.m.Y",$lastmod).")</td></tr>
			</table>
			<p>
				$comment
			</p>
		";
		
		
		$key = $this->googleMapsKey;
		if ($lat != 0) {
		print "<script src=\"http://maps.google.com/maps?file=api&amp;v=2&amp;key=$key\"
			type=\"text/javascript\"></script>
			<script type=\"text/javascript\">
    //<![CDATA[

    function load() {
      if (GBrowserIsCompatible()) {
        
        function createMarker(point,html) {
        	var marker = new GMarker(point);
        	GEvent.addListener(marker, \"click\", function() {
        		  marker.openInfoWindowHtml(html);
        	});
        	return marker;
      	}
      	
        var map = new GMap2(document.getElementById(\"map\"));
        map.setCenter(new GLatLng($lat + 0.002, $lng), 15);
		map.addControl(new GMapTypeControl());	
        
        var point = new GLatLng($lat,$lng);
        var marker = createMarker(point,'$unique_name')
        map.addOverlay(marker);
        map.openInfoWindow(point, document.createTextNode('$unique_name'));
        
      }
    }
    
    window.onload = load;
    window.onunload = GUnload;

    //]]>
    </script>
     <div style='padding-left:100px; padding-top:20px;'>
 	    <div id=\"map\" style=\"width: 500px; height: 400px;border: 1px solid #333;\"></div>
     </div>

     ";
	}
	
		call_user_func($this->add_to_breadcrumb,"
			<a href=\"".$this->generateCoolUrl("/agreement/$id")."\"><strong>$unique_name</strong></a>"
		);
	
	}

	/*************************** ENTRY MANAGEMENT ****************************/
	
	function editAgreement($id) {
		if (!$this->allow_addentry){ $this->permissionDenied(); return; }			
		
		$cat_id = 0; 
		$chain_id = 0;
		$unique_name = "";
		$street = "";
		$streetno = "";
		$postno = "";
		$city = $this->default_city;
		$contact_name = "";
		$contact_email = "";
		$contact_phone = "";
		$comment = "";
		$latitude = "";
		$longitude = "";
		$status_id = 0;
		
		if ($id != 0) {
			$res = $this->query("SELECT 
					category, chain, unique_name, street, streetno, postno, city, 
					status, contact_name, contact_email, contact_phone, comment,
					latitude, longitude
				FROM $this->table_status
				WHERE id='$id'"
			);
			if ($res->num_rows != 1) { $this->notSoFatalError("invalid agreement id"); return; }
			$row = $res->fetch_assoc();
			$cat_id = $row['category'];
			$chain_id = $row['chain'];
			$unique_name = stripslashes($row['unique_name']);
			$street = stripslashes($row['street']);
			$streetno = stripslashes($row['streetno']);
			$postno = stripslashes($row['postno']);
			$city = stripslashes($row['city']);
			$contact_name = stripslashes($row['contact_name']);
			$contact_email = stripslashes($row['contact_email']);
			$contact_phone = stripslashes($row['contact_phone']);
			$comment = stripslashes($row['comment']);
			$latitude = stripslashes($row['latitude']);
			$longitude = stripslashes($row['longitude']);
			$status_id = stripslashes($row['status']);
		}
		
		$cats = "<select name='cat_id'>\n";
		$res = $this->query("SELECT id, cat_name FROM $this->table_cats");
		while ($row = $res->fetch_assoc()) {
			$def = ($row['id'] == $cat_id) ? " selected='selected'" : "";
			$cats .= "  <option value='".$row['id']."'$def>".stripslashes($row['cat_name'])."</option>\n";
		}
		$cats .= "</select>\n";

		$chains = "<select name='chain_id'>\n";
		$chains .= "  <option value='0'>-- Ingen --</option>\n";
		$res = $this->query("SELECT id, chain_name FROM $this->table_chains");
		while ($row = $res->fetch_assoc()) {
			$def = ($row['id'] == $chain_id) ? " selected='selected'" : "";
			$chains .= "  <option value='".$row['id']."'$def>".stripslashes($row['chain_name'])."</option>\n";
		}
		$chains .= "</select>\n";
		
		$status = "<select name='status_id'>\n";
		foreach ($this->agreementStates as $i => $s) {
			$def = ($i == $status_id) ? " selected='selected'" : "";
			$status .= "  <option value='$i'$def>$s</option>\n";
		}
		$status .= "</select>\n";
		
		$get_pos_url = $this->generateCoolUrl("/fetch-pos/$id","noprint=true");
		$get_pos_js = "window.open('$get_pos_url','Hent kart-posisjon','toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=1,width=700,height=600'); return false;";
		
		$erroutp = "";
		
		$r1a   = array();							$r2a   = array();
		$r1a[] = "%errors%";						$r2a[] = $erroutp;
		$r1a[] = "%posturl%";						$r2a[] = $this->generateCoolUrl("/save-agreement/","noprint=true");
		$r1a[] = "%id%";							$r2a[] = $id;
		$r1a[] = "%cats%";							$r2a[] = $cats;
		$r1a[] = "%chains%";						$r2a[] = $chains;
		$r1a[] = "%unique_name%";					$r2a[] = $unique_name;
		$r1a[] = "%street%";						$r2a[] = $street;
		$r1a[] = "%streetno%";						$r2a[] = $streetno;
		$r1a[] = "%postno%";						$r2a[] = $postno;
		$r1a[] = "%city%";							$r2a[] = $city;
		$r1a[] = "%contact_name%";					$r2a[] = $contact_name;
		$r1a[] = "%contact_phone%";					$r2a[] = $contact_phone;
		$r1a[] = "%contact_email%";					$r2a[] = $contact_email;
		$r1a[] = "%comment%";						$r2a[] = $comment;
		$r1a[] = "%latitude%";						$r2a[] = $latitude;
		$r1a[] = "%longitude%";						$r2a[] = $longitude;
		$r1a[] = "%get_pos_url%";					$r2a[] = $get_pos_url;
		$r1a[] = "%get_pos_js%";					$r2a[] = $get_pos_js;
		$r1a[] = "%status%";						$r2a[] = $status;

		$outp = str_replace($r1a, $r2a, $this->schema_template);
		
		print $outp;

		call_user_func($this->add_to_breadcrumb,"
			<a href=\"".$this->generateCoolUrl("/agreement/$id")."\"><strong>Avtale #$id</strong></a>"
		);
	
	}
	
	function saveAgreement() {
		if (!$this->allow_addentry){ $this->permissionDenied(); return; }
		
		$id = $_POST['id'];
		$cat_id = $_POST['cat_id'];
		$chain_id = $_POST['chain_id'];
		$unique_name = addslashes($_POST['unique_name']);
		$street = addslashes($_POST['street']);
		$streetno = addslashes($_POST['streetno']);
		$postno = addslashes($_POST['postno']);
		$city = addslashes($_POST['city']);
		$contact_name = addslashes($_POST['contact_name']);
		$contact_email = addslashes($_POST['contact_email']);
		$contact_phone = addslashes($_POST['contact_phone']);
		$comment = addslashes($_POST['comment']);
		$latitude = addslashes($_POST['latitude']);
		$longitude = addslashes($_POST['longitude']);
		$status_id = addslashes($_POST['status_id']);
		
		if ($id == '0') {
			$this->query("INSERT INTO $this->table_status 
				(category,chain,unique_name,street,streetno,postno,city,contact_name,contact_email,contact_phone,latitude,longitude,status,created,lastmodified)
				VALUES (\"$cat_id\",\"$chain_id\",\"$unique_name\",\"$street\",\"$streetno\",\"$postno\",\"$city\",\"$contact_name\",\"$contact_email\",\"$contact_phone\",\"$latitude\",\"$longitude\",\"$status_id\",".time().",".time().")"
			);
		} else {
			$this->query("UPDATE $this->table_status 
				SET 
					category=\"$cat_id\",
					chain=\"$chain_id\",
					unique_name=\"$unique_name\",
					street=\"$street\",
					streetno=\"$streetno\",
					postno=\"$postno\",
					city=\"$city\",
					contact_name=\"$contact_name\",
					contact_email=\"$contact_email\",
					contact_phone=\"$contact_phone\",
					latitude=\"$latitude\",
					longitude=\"$longitude\",
					status=\"$status_id\",
					lastmodified=".time()."
				WHERE id='$id'"
			);
		}
		
		$this->redirect($this->generateCoolUrl("/"),"Avtalen ble lagret");
	
	}
	
	/****************************** CATEGORIES *******************************/
	
	function editCatsForm() {
		if (!$this->allow_editcats){ $this->permissionDenied(); return; }			
		$res = $this->query("SELECT id, cat_name FROM $this->table_cats");
		$url_add = $this->generateCoolUrl("/add-cat/");
		print "
			<h3>Rediger kategorier</h3>
			<p>
				<a href='$url_add'>Legg til ny kategori</a>
			</p>
			<ul>
		";
		while ($row = $res->fetch_assoc()) {
			$url_edit = $this->generateCoolUrl("/edit-cat/".$row['id']."/");
			$url_delete = $this->generateCoolUrl("/delete-cat/".$row['id']."/");
			print "
				<li>
					".stripslashes($row['cat_name'])."
					(<a href='$url_edit'>Rediger</a> |
					<a href='$url_delete'>Slett</a>)			
				</li>
			";
		}
		print "
			</ul>
		";
		call_user_func($this->add_to_breadcrumb,"
			<a href=\"".$this->generateCoolUrl("/edit-cats/")."\"><strong>Kategori-redigering</strong></a>"
		);
		
	}
	
	function editCat($id) {
		if (!$this->allow_editcats){ $this->permissionDenied(); return; }			
		if (!is_numeric($id)) { $this->notSoFatalError("invalid id"); return; }
		$res = $this->query("SELECT id, cat_name, cat_name_plural, goal FROM $this->table_cats WHERE id=$id");
		if ($res->num_rows != 1) { $this->notSoFatalError("invalid cat"); return; }
		$row = $res->fetch_assoc();
		$cat_id = $row['id'];
		$goal = $row['goal'];
		$cat_name = stripslashes($row['cat_name']);
		$cat_name_plural = stripslashes($row['cat_name_plural']);
		$url_save = $this->generateCoolUrl("/update-cat/","noprint=true");
		print "
			<h3>Rediger kategori</h3>
			<form method='post' action='$url_save'>
				<input type='hidden' name='cat_id' value='$cat_id' />
				Navn på kategori (entall): <input type='text' name='cat_name' value=\"$cat_name\" /><br />
				Navn på kategori (flertall): <input type='text' name='cat_name_plural' value=\"$cat_name_plural\" /><br /><br />
				Antall som trengs for å bli Fairtrade-by: <input type='text' name='goal' value=\"$goal\" /><br /><br />
				<input type='submit' value='Lagre' />
				
			</form>
		";
		call_user_func($this->add_to_breadcrumb,"
			<a href=\"".$this->generateCoolUrl("/edit-cats/")."\"><strong>Kategori-redigering</strong></a>"
		);
	}
	
	function updateCat() {
		if (!$this->allow_editcats){ $this->permissionDenied(); return; }			
		$cat_id = addslashes($_POST['cat_id']);
		$cat_name = addslashes($_POST['cat_name']);
		$cat_name_plural = addslashes($_POST['cat_name_plural']);
		$goal = addslashes($_POST['goal']);
		if (empty($cat_name)) $this->fatalError("du må fylle inn et navn");
		if (!is_numeric($cat_id)) { $this->notSoFatalError("invalid id"); return; }
		if (!is_numeric($goal)) { $this->notSoFatalError("antall må være et tall"); return; }
		$res = $this->query("SELECT id, cat_name FROM $this->table_cats WHERE id='$cat_id'");
		if ($res->num_rows != 1) { $this->notSoFatalError("invalid cat"); return; }
		$this->query("UPDATE $this->table_cats SET 
				cat_name='$cat_name', 
				cat_name_plural='$cat_name_plural', 
				goal='$goal'
			WHERE id='$cat_id'"
		);
		$this->redirect($this->generateCoolUrl("/edit-cats/"),"Kategorien ble oppdatert");
	}
	
	function deleteCatQuery($id) {
		if (!$this->allow_editcats){ $this->permissionDenied(); return; }			
		if (!is_numeric($id)) { $this->notSoFatalError("invalid id"); return; }
		$res = $this->query("SELECT id, cat_name FROM $this->table_cats WHERE id=$id");
		if ($res->num_rows != 1) { $this->notSoFatalError("invalid cat"); return; }
		$row = $res->fetch_assoc();
		$cat_id = $row['id'];
		$cat_name = stripslashes($row['cat_name']);
		$url_del = $this->generateCoolUrl("/delete-cat-do/","noprint=true");
		$url_cancel = "window.location=\"".$_SERVER['HTTP_REFERER']."\";";
		print "
			<h3>Slett kategori</h3>
			<form method='post' action='$url_del'>
				<input type='hidden' name='cat_id' value='$cat_id' />
				Er du sikker på at du vil slette kategorien \"$cat_name\"?<br /><br />
				<input type='button' value='Nei' onclick='$url_cancel' /> <input type='submit' value='Ja' />		
			</form>
		";
		call_user_func($this->add_to_breadcrumb,"
			<a href=\"".$this->generateCoolUrl("/edit-cats/")."\"><strong>Kategori-redigering</strong></a>"
		);
	}
	
	function deleteCat() {
		if (!$this->allow_editcats){ $this->permissionDenied(); return; }			
		$cat_id = addslashes($_POST['cat_id']);
		if (!is_numeric($cat_id)) { $this->notSoFatalError("invalid id"); return; }
		$res = $this->query("SELECT id, cat_name FROM $this->table_cats WHERE id='$id'");
		if ($res->num_rows != 1) { $this->notSoFatalError("invalid cat"); return; }
		$this->query("DELETE FROM $this->table_cats WHERE id='$id' LIMIT 1");
		$this->redirect($this->generateCoolUrl("/edit-cats/"),"Kategorien ble slettet");
	}
	
	function addCat() {
		if (!$this->allow_editcats){ $this->permissionDenied(); return; }			
		$url_save = $this->generateCoolUrl("/save-cat/","noprint=true");
		print "
			<h3>Legg til ny kategori</h3>
			<form method='post' action='$url_save'>
				Navn på kategori: <input type='text' name='cat_name' /><br /><br />
				<input type='submit' value='Lagre' />
			</form>
		";
		call_user_func($this->add_to_breadcrumb,"
			<a href=\"".$this->generateCoolUrl("/edit-cats/")."\"><strong>Kategori-redigering</strong></a>"
		);
	}
	
	function saveCat() {
		if (!$this->allow_editcats){ $this->permissionDenied(); return; }		
		$cat_name = addslashes(strip_tags($_POST['cat_name']));
		if (empty($cat_name)) $this->fatalError("du må fylle inn et navn");
		$this->query("INSERT INTO $this->table_cats (cat_name) VALUES ('$cat_name')");
		$this->redirect($this->generateCoolUrl("/edit-cats/"),"Kategorien ble lagt til");
	}
	
	/****************************** CHAINS *******************************/

	function editChainsForm() {
		if (!$this->allow_editchains){ $this->permissionDenied(); return; }			
		$res = $this->query("SELECT id, chain_name FROM $this->table_chains");
		$url_add = $this->generateCoolUrl("/add-chain/");
		print "
			<h3>Rediger kjeder</h3>
			<p>
				<a href='$url_add'>Legg til ny kjede</a>
			</p>
			<ul>
		";
		while ($row = $res->fetch_assoc()) {
			$url_edit = $this->generateCoolUrl("/edit-chain/".$row['id']."/");
			$url_delete = $this->generateCoolUrl("/delete-chain/".$row['id']."/");
			print "
				<li>
					".stripslashes($row['chain_name'])."
					(<a href='$url_edit'>Rediger</a> |
					<a href='$url_delete'>Slett</a>)			
				</li>
			";
		}
		print "
			</ul>
		";
		call_user_func($this->add_to_breadcrumb,"
			<a href=\"".$this->generateCoolUrl("/edit-chains/")."\"><strong>Kjede-redigering</strong></a>"
		);

	}
	
	function editChain($id) {
		if (!$this->allow_editchains){ $this->permissionDenied(); return; }			
		if (!is_numeric($id)) { $this->notSoFatalError("invalid id"); return; }
		$res = $this->query("SELECT id, chain_name FROM $this->table_chains WHERE id=$id");
		if ($res->num_rows != 1) { $this->notSoFatalError("invalid chain"); return; }
		$row = $res->fetch_assoc();
		$chain_id = $row['id'];
		$chain_name = stripslashes($row['chain_name']);
		$url_save = $this->generateCoolUrl("/update-chain/","noprint=true");
		print "
			<h3>Rediger kjede</h3>
			<form method='post' action='$url_save'>
				<input type='hidden' name='chain_id' value='$chain_id' />
				Navn på kjede: <input type='text' name='chain_name' value=\"$chain_name\" /><br /><br />
				<input type='submit' value='Lagre' />				
			</form>
		";
		call_user_func($this->add_to_breadcrumb,"
			<a href=\"".$this->generateCoolUrl("/edit-chains/")."\"><strong>Kjede-redigering</strong></a>"
		);
	}
	
	function updateChain() {
		if (!$this->allow_editchains){ $this->permissionDenied(); return; }			
		$chain_id = addslashes($_POST['chain_id']);
		$chain_name = addslashes($_POST['chain_name']);
		if (empty($chain_name)) $this->fatalError("du må fylle inn et navn");
		if (!is_numeric($chain_id)) { $this->notSoFatalError("invalid id"); return; }
		$res = $this->query("SELECT id, chain_name FROM $this->table_chains WHERE id='$chain_id'");
		if ($res->num_rows != 1) { $this->notSoFatalError("invalid chain"); return; }
		$this->query("UPDATE $this->table_chains SET 
				chain_name='$chain_name'
			WHERE id='$chain_id'"
		);
		$this->redirect($this->generateCoolUrl("/edit-chains/"),"Kjeden ble oppdatert");
	}
	
	function deleteChainQuery($id) {
		if (!$this->allow_editchains){ $this->permissionDenied(); return; }			
		if (!is_numeric($id)) { $this->notSoFatalError("invalid id"); return; }
		$res = $this->query("SELECT id, chain_name FROM $this->table_chains WHERE id=$id");
		if ($res->num_rows != 1) { $this->notSoFatalError("invalid chain"); return; }
		$row = $res->fetch_assoc();
		$chain_id = $row['id'];
		$chain_name = stripslashes($row['chain_name']);
		$url_del = $this->generateCoolUrl("/delete-chain-do/","noprint=true");
		$url_cancel = "window.location=\"".$_SERVER['HTTP_REFERER']."\";";
		print "
			<h3>Slett kjede</h3>
			<form method='post' action='$url_del'>
				<input type='hidden' name='chain_id' value='$chain_id' />
				Er du sikker på at du vil slette kjeden \"$chain_name\"?<br /><br />
				<input type='button' value='Nei' onclick='$url_cancel' /> <input type='submit' value='Ja' />				
			</form>
		";
		call_user_func($this->add_to_breadcrumb,"
			<a href=\"".$this->generateCoolUrl("/edit-chains/")."\"><strong>Kjede-redigering</strong></a>"
		);		
	}
	
	function deleteChain() {
		if (!$this->allow_editchains){ $this->permissionDenied(); return; }			
		$chain_id = addslashes($_POST['chain_id']);
		if (!is_numeric($chain_id)) { $this->notSoFatalError("invalid id"); return; }
		$res = $this->query("SELECT id, chain_name FROM $this->table_chains WHERE id='$id'");
		if ($res->num_rows != 1) { $this->notSoFatalError("invalid chain"); return; }
		$this->query("DELETE FROM $this->table_chains WHERE id='$id' LIMIT 1");
		$this->redirect($this->generateCoolUrl("/edit-chains/"),"Kjeden ble slettet");
	}
	
	function addChain() {
		if (!$this->allow_editchains){ $this->permissionDenied(); return; }			
		$url_save = $this->generateCoolUrl("/save-chain/","noprint=true");
		print "
			<h3>Legg til ny kjede</h3>
			<form method='post' action='$url_save'>
				Navn på kjede: <input type='text' name='chain_name' /><br /><br />
				<input type='submit' value='Lagre' />
			</form>
		";
		call_user_func($this->add_to_breadcrumb,"
			<a href=\"".$this->generateCoolUrl("/edit-chains/")."\"><strong>Kjede-redigering</strong></a>"
		);
	}
	
	function saveChain() {
		if (!$this->allow_editchains){ $this->permissionDenied(); return; }		
		$chain_name = addslashes(strip_tags($_POST['chain_name']));
		if (empty($chain_name)) $this->fatalError("du må fylle inn et navn");
		$this->query("INSERT INTO $this->table_chains (chain_name) VALUES ('$chain_name')");
		$this->redirect($this->generateCoolUrl("/edit-chains/"),"Kjeden ble lagt til");
	}
	
	/****************************** ITEMS *******************************/

	function editItemsForm() {
		if (!$this->allow_edititems){ $this->permissionDenied(); return; }
		$tb = $this->table_brands;
		$ti = $this->table_items;
		$res = $this->query("SELECT $ti.id, $ti.item_name, $tb.brand_name FROM $ti,$tb WHERE $ti.brand=$tb.id");
		$url_add = $this->generateCoolUrl("/add-item/");
		print "
			<h3>Rediger varer</h3>
			<p>
				<a href='$url_add'>Legg til ny vare</a>
			</p>
			<ul>
		";
		while ($row = $res->fetch_assoc()) {
			$url_edit = $this->generateCoolUrl("/edit-item/".$row['id']."/");
			$url_delete = $this->generateCoolUrl("/delete-item/".$row['id']."/");
			$item_name = stripslashes($row['item_name']);
			$brand_name = stripslashes($row['brand_name']);
			print "
				<li>
					$brand_name $item_name
					(<a href='$url_edit'>Rediger</a> |
					<a href='$url_delete'>Slett</a>)			
				</li>
			";
		}
		print "
			</ul>
		";
		call_user_func($this->add_to_breadcrumb,"
			<a href=\"".$this->generateCoolUrl("/edit-items/")."\"><strong>Vare-redigering</strong></a>"
		);

	}
	
	function editItem($id) {
		if (!$this->allow_edititems){ $this->permissionDenied(); return; }			
		if (!is_numeric($id)) { $this->notSoFatalError("invalid id"); return; }
		
		$brands = array();
		$res = $this->query("SELECT id, brand_name FROM $this->table_brands");
		while ($row = $res->fetch_assoc()) {
			$brands[] = stripslashes($row['brand_name']);
		}
		
		$cats = array();
		$res = $this->query("SELECT id, cat_name FROM $this->table_itemcats");
		while ($row = $res->fetch_assoc()) {
			$cats[] = stripslashes($row['cat_name']);
		}
		
		$ti = $this->table_items;
		$tb = $this->table_brands;
		$tc = $this->table_itemcats; 
		$res = $this->query("SELECT $ti.id, $ti.item_name, $tb.brand_name, $tc.cat_name FROM $ti,$tb,$tc WHERE $ti.id=$id AND $ti.brand=$tb.id AND $ti.category=$tc.id");
		if ($res->num_rows != 1) { $this->notSoFatalError("invalid item"); return; }
		$row = $res->fetch_assoc();
		$item_id = $row['id'];
		$item_name = stripslashes($row['item_name']);
		$brand_name = stripslashes($row['brand_name']);
		$cat_name = stripslashes($row['cat_name']);
		$url_save = $this->generateCoolUrl("/update-item/","noprint=true");
		print "
			<h3>Rediger vare</h3>
			<form method='post' action='$url_save'>
				<input type='hidden' name='item_id' value='$item_id' />
				Varekategori: <input type='text' name='cat_name' id='cat_name' value=\"$cat_name\" /><br /><br />
				Merke: <input type='text' name='brand_name' id='brand_name' value=\"$brand_name\" /><br /><br />
				Varenavn: <input type='text' name='item_name' value=\"$item_name\" /><br /><br />
				<input type='submit' value='Lagre' />				
			</form>
			<div class='auto_complete' id='brand_list' style='display:none'></div>
			<div class='auto_complete' id='cat_list' style='display:none'></div>
			<script type='text/javascript'>
				new Autocompleter.Local('brand_name', 'brand_list', [\"".implode("\",\"",$brands)."\"], {});
				new Autocompleter.Local('cat_name', 'cat_list', [\"".implode("\",\"",$cats)."\"], {});
			</script>
		";
		call_user_func($this->add_to_breadcrumb,"
			<a href=\"".$this->generateCoolUrl("/edit-items/")."\"><strong>Vare-redigering</strong></a>"
		);
	}
	
	function updateItem() {
		if (!$this->allow_edititems){ $this->permissionDenied(); return; }			
		$item_id = addslashes($_POST['item_id']);
		$item_name = addslashes($_POST['item_name']);
		$brand_name = addslashes($_POST['brand_name']);
		$cat_name = addslashes($_POST['cat_name']);
		if (empty($item_name)) $this->fatalError("du må fylle inn et navn");
		if (empty($brand_name)) $this->fatalError("du må fylle inn et varemerke");
		if (empty($cat_name)) $this->fatalError("du må fylle inn en varekategori");
		if (!is_numeric($item_id)) { $this->notSoFatalError("invalid id"); return; }

		$res = $this->query("SELECT id FROM $this->table_brands WHERE brand_name='$brand_name'");
		if ($res->num_rows != 1) { 
			$this->query("INSERT INTO $this->table_brands (brand_name) VALUES ('$brand_name')");
			$brand_id = $this->insert_id();
		} else {
			$row = $res->fetch_assoc();
			$brand_id = $row['id'];
		}
		
		$res = $this->query("SELECT id FROM $this->table_itemcats WHERE cat_name='$cat_name'");
		if ($res->num_rows != 1) { 
			$this->query("INSERT INTO $this->table_itemcats (cat_name) VALUES ('$cat_name')");
			$cat_id = $this->insert_id();
		} else {
			$row = $res->fetch_assoc();
			$cat_id = $row['id'];
		}

		$res = $this->query("SELECT id, item_name FROM $this->table_items WHERE id='$item_id'");
		if ($res->num_rows != 1) { $this->notSoFatalError("invalid item"); return; }
		$this->query("UPDATE $this->table_items SET 
				item_name='$item_name',
				category='$cat_id',
				brand='$brand_id'
			WHERE id='$item_id'"
		);
		$this->redirect($this->generateCoolUrl("/edit-items/"),"Varen ble oppdatert");
	}
	
	function deleteItemQuery($id) {
		if (!$this->allow_edititems){ $this->permissionDenied(); return; }			
		if (!is_numeric($id)) { $this->notSoFatalError("invalid id"); return; }
		$res = $this->query("SELECT id, item_name FROM $this->table_items WHERE id=$id");
		if ($res->num_rows != 1) { $this->notSoFatalError("invalid item"); return; }
		$row = $res->fetch_assoc();
		$item_id = $row['id'];
		$item_name = stripslashes($row['item_name']);
		$url_del = $this->generateCoolUrl("/delete-item-do/","noprint=true");
		$url_cancel = "window.location=\"".$_SERVER['HTTP_REFERER']."\";";
		print "
			<h3>Slett vare</h3>
			<form method='post' action='$url_del'>
				<input type='hidden' name='item_id' value='$item_id' />
				Er du sikker på at du vil slette varen \"$item_name\"?<br /><br />
				<input type='button' value='Nei' onclick='$url_cancel' /> <input type='submit' value='Ja' />				
			</form>
		";
		call_user_func($this->add_to_breadcrumb,"
			<a href=\"".$this->generateCoolUrl("/edit-items/")."\"><strong>Vare-redigering</strong></a>"
		);		
	}
	
	function deleteItem() {
		if (!$this->allow_edititems){ $this->permissionDenied(); return; }			
		$item_id = addslashes($_POST['item_id']);
		if (!is_numeric($item_id)) { $this->notSoFatalError("invalid id"); return; }
		$res = $this->query("SELECT id, item_name FROM $this->table_items WHERE id='$id'");
		if ($res->num_rows != 1) { $this->notSoFatalError("invalid item"); return; }
		$this->query("DELETE FROM $this->table_items WHERE id='$id' LIMIT 1");
		$this->redirect($this->generateCoolUrl("/edit-items/"),"Varen ble slettet");
	}
	
	function addItem() {
		if (!$this->allow_edititems){ $this->permissionDenied(); return; }			
		$url_save = $this->generateCoolUrl("/save-item/","noprint=true");
		
		$brands = array();
		$res = $this->query("SELECT id, brand_name FROM $this->table_brands");
		while ($row = $res->fetch_assoc()) {
			$brands[] = stripslashes($row['brand_name']);
		}
		
		$cats = array();
		$res = $this->query("SELECT id, cat_name FROM $this->table_itemcats");
		while ($row = $res->fetch_assoc()) {
			$cats[] = stripslashes($row['cat_name']);
		}
		
		print "
			<h3>Legg til ny vare</h3>
			<form method='post' action='$url_save'>
				Varekategori: <input type='text' name='cat_name' id='cat_name' value=\"\" /><br /><br />
				Merke: <input type='text' name='brand_name' id='brand_name' value=\"\" /><br /><br />
				Varenavn: <input type='text' name='item_name' /><br /><br />
				<input type='submit' value='Lagre' />
			</form>
			<div class='auto_complete' id='brand_list' style='display:none'></div>
			<div class='auto_complete' id='cat_list' style='display:none'></div>
			<script type='text/javascript'>
				new Autocompleter.Local('brand_name', 'brand_list', [\"".implode("\",\"",$brands)."\"], {});
				new Autocompleter.Local('cat_name', 'cat_list', [\"".implode("\",\"",$cats)."\"], {});
			</script>

		";
		call_user_func($this->add_to_breadcrumb,"
			<a href=\"".$this->generateCoolUrl("/edit-items/")."\"><strong>Vare-redigering</strong></a>"
		);
	}
	
	function saveItem() {
		if (!$this->allow_edititems){ $this->permissionDenied(); return; }		
		$item_name = addslashes(strip_tags($_POST['item_name']));
		if (empty($item_name)) $this->fatalError("du må fylle inn et navn");
		$brand_name = addslashes(strip_tags($_POST['brand_name']));
		if (empty($brand_name)) $this->fatalError("du må fylle inn et varemerkenavn");
		$cat_name = addslashes(strip_tags($_POST['cat_name']));
		if (empty($cat_name)) $this->fatalError("du må fylle inn en varekategori");
		
		$res = $this->query("SELECT id FROM $this->table_brands WHERE brand_name='$brand_name'");
		if ($res->num_rows != 1) { 
			$this->query("INSERT INTO $this->table_brands (brand_name) VALUES ('$brand_name')");
			$brand_id= $this->insert_id();
		} else {
			$row = $res->fetch_assoc();
			$brand_id = $row['id'];
		}
		
		$res = $this->query("SELECT id FROM $this->table_itemcats WHERE cat_name='$cat_name'");
		if ($res->num_rows != 1) { 
			$this->query("INSERT INTO $this->table_itemcats (cat_name) VALUES ('$cat_name')");
			$cat_id= $this->insert_id();
		} else {
			$row = $res->fetch_assoc();
			$cat_id = $row['id'];
		}
		
		$this->query("INSERT INTO $this->table_items (item_name,brand,category) VALUES ('$item_name','$brand_id','$cat_id')");
		$this->redirect($this->generateCoolUrl("/edit-items/"),"Varen ble lagt til");
	}
	
	/****************************** ITEM CATEGORIES *******************************/

	function editItemCatsForm() {
		if (!$this->allow_edititems){ $this->permissionDenied(); return; }			
		$res = $this->query("SELECT id, cat_name FROM $this->table_itemcats");
		$url_add = $this->generateCoolUrl("/add-itemcat/");
		print "
			<h3>Rediger varekategorier</h3>
			<p>
				<a href='$url_add'>Legg til ny varekategori</a>
			</p>
			<ul>
		";
		while ($row = $res->fetch_assoc()) {
			$url_edit = $this->generateCoolUrl("/edit-itemcat/".$row['id']."/");
			$url_delete = $this->generateCoolUrl("/delete-itemcat/".$row['id']."/");
			print "
				<li>
					".stripslashes($row['cat_name'])."
					(<a href='$url_edit'>Rediger</a> |
					<a href='$url_delete'>Slett</a>)			
				</li>
			";
		}
		print "
			</ul>
		";
		call_user_func($this->add_to_breadcrumb,"
			<a href=\"".$this->generateCoolUrl("/edit-itemcats/")."\"><strong>Varekategorier</strong></a>"
		);

	}
	
	function editItemCat($id) {
		if (!$this->allow_edititems){ $this->permissionDenied(); return; }			
		if (!is_numeric($id)) { $this->notSoFatalError("invalid id"); return; }
		$res = $this->query("SELECT id, cat_name FROM $this->table_itemcats WHERE id=$id");
		if ($res->num_rows != 1) { $this->notSoFatalError("invalid item"); return; }
		$row = $res->fetch_assoc();
		$item_id = $row['id'];
		$item_name = stripslashes($row['cat_name']);
		$url_save = $this->generateCoolUrl("/update-itemcat/","noprint=true");
		print "
			<h3>Rediger varekategori</h3>
			<form method='post' action='$url_save'>
				<input type='hidden' name='item_id' value='$item_id' />
				Navn: <input type='text' name='item_name' value=\"$item_name\" /><br /><br />
				<input type='submit' value='Lagre' />				
			</form>
		";
		call_user_func($this->add_to_breadcrumb,"
			<a href=\"".$this->generateCoolUrl("/edit-itemcats/")."\"><strong>Varekategorier</strong></a>"
		);
	}
	
	function updateItemCat() {
		if (!$this->allow_edititems){ $this->permissionDenied(); return; }			
		$item_id = addslashes($_POST['item_id']);
		$item_name = addslashes($_POST['item_name']);
		if (empty($item_name)) $this->fatalError("du må fylle inn et navn");
		if (!is_numeric($item_id)) { $this->notSoFatalError("invalid id"); return; }
		$res = $this->query("SELECT id, cat_name FROM $this->table_itemcats WHERE id='$item_id'");
		if ($res->num_rows != 1) { $this->notSoFatalError("invalid item"); return; }
		$this->query("UPDATE $this->table_itemcats SET 
				cat_name='$item_name'
			WHERE id='$item_id'"
		);
		$this->redirect($this->generateCoolUrl("/edit-itemcats/"),"Varekategorien ble oppdatert");
	}
	
	function deleteItemCatQuery($id) {
		if (!$this->allow_edititems){ $this->permissionDenied(); return; }			
		if (!is_numeric($id)) { $this->notSoFatalError("invalid id"); return; }
		$res = $this->query("SELECT id, cat_name FROM $this->table_itemcats WHERE id=$id");
		if ($res->num_rows != 1) { $this->notSoFatalError("invalid item"); return; }
		$row = $res->fetch_assoc();
		$item_id = $row['id'];
		$item_name = stripslashes($row['cat_name']);
		$url_del = $this->generateCoolUrl("/delete-itemcat-do/","noprint=true");
		$url_cancel = "window.location=\"".$_SERVER['HTTP_REFERER']."\";";
		print "
			<h3>Slett varekategori</h3>
			<form method='post' action='$url_del'>
				<input type='hidden' name='item_id' value='$item_id' />
				Er du sikker på at du vil slette varekategorien \"$item_name\"?<br /><br />
				<input type='button' value='Nei' onclick='$url_cancel' /> <input type='submit' value='Ja' />				
			</form>
		";
		call_user_func($this->add_to_breadcrumb,"
			<a href=\"".$this->generateCoolUrl("/edit-itemcats/")."\"><strong>Varekategorier</strong></a>"
		);		
	}
	
	function deleteItemCat() {
		if (!$this->allow_edititems){ $this->permissionDenied(); return; }			
		$item_id = addslashes($_POST['item_id']);
		if (!is_numeric($item_id)) { $this->notSoFatalError("invalid id"); return; }
		$res = $this->query("SELECT id, cat_name FROM $this->table_itemcats WHERE id='$id'");
		if ($res->num_rows != 1) { $this->notSoFatalError("invalid item"); return; }
		$this->query("DELETE FROM $this->table_itemcats WHERE id='$id' LIMIT 1");
		$this->redirect($this->generateCoolUrl("/edit-itemcats/"),"Varekategorien ble slettet");
	}
	
	function addItemCat() {
		if (!$this->allow_edititems){ $this->permissionDenied(); return; }			
		$url_save = $this->generateCoolUrl("/save-itemcat/","noprint=true");
		print "
			<h3>Legg til ny varekategori</h3>
			<form method='post' action='$url_save'>
				Navn på varekategori: <input type='text' name='item_name' /><br /><br />
				<input type='submit' value='Lagre' />
			</form>
		";
		call_user_func($this->add_to_breadcrumb,"
			<a href=\"".$this->generateCoolUrl("/edit-itemcats/")."\"><strong>Varekategorier</strong></a>"
		);
	}
	
	function saveItemCat() {
		if (!$this->allow_edititems){ $this->permissionDenied(); return; }		
		$item_name = addslashes(strip_tags($_POST['item_name']));
		if (empty($item_name)) $this->fatalError("du må fylle inn et navn");
		$this->query("INSERT INTO $this->table_itemcats (cat_name) VALUES ('$item_name')");
		$this->redirect($this->generateCoolUrl("/edit-itemcats/"),"Varekategorien ble lagt til");
	}

	/****************************** BRANDS *******************************/

	function editBrandsForm() {
		if (!$this->allow_edititems){ $this->permissionDenied(); return; }			
		$res = $this->query("SELECT id, brand_name FROM $this->table_brands");
		$url_add = $this->generateCoolUrl("/add-brand/");
		print "
			<h3>Rediger varemerker</h3>
			<p>
				<a href='$url_add'>Legg til nytt varemerke</a>
			</p>
			<ul>
		";
		while ($row = $res->fetch_assoc()) {
			$url_edit = $this->generateCoolUrl("/edit-brand/".$row['id']."/");
			$url_delete = $this->generateCoolUrl("/delete-brand/".$row['id']."/");
			print "
				<li>
					".stripslashes($row['brand_name'])."
					(<a href='$url_edit'>Rediger</a> |
					<a href='$url_delete'>Slett</a>)			
				</li>
			";
		}
		print "
			</ul>
		";
		call_user_func($this->add_to_breadcrumb,"
			<a href=\"".$this->generateCoolUrl("/edit-brands/")."\"><strong>Varemerker</strong></a>"
		);

	}
	
	function editBrand($id) {
		if (!$this->allow_edititems){ $this->permissionDenied(); return; }			
		if (!is_numeric($id)) { $this->notSoFatalError("invalid id"); return; }
		$res = $this->query("SELECT id, brand_name FROM $this->table_brands WHERE id=$id");
		if ($res->num_rows != 1) { $this->notSoFatalError("invalid item"); return; }
		$row = $res->fetch_assoc();
		$item_id = $row['id'];
		$item_name = stripslashes($row['brand_name']);
		$url_save = $this->generateCoolUrl("/update-brand/","noprint=true");
		print "
			<h3>Rediger varemerke</h3>
			<form method='post' action='$url_save'>
				<input type='hidden' name='item_id' value='$item_id' />
				Navn: <input type='text' name='item_name' value=\"$item_name\" /><br /><br />
				<input type='submit' value='Lagre' />				
			</form>
		";
		call_user_func($this->add_to_breadcrumb,"
			<a href=\"".$this->generateCoolUrl("/edit-brands/")."\"><strong>Varemerker</strong></a>"
		);
	}
	
	function updateBrands() {
		if (!$this->allow_edititems){ $this->permissionDenied(); return; }			
		$item_id = addslashes($_POST['item_id']);
		$item_name = addslashes($_POST['item_name']);
		if (empty($item_name)) $this->fatalError("du må fylle inn et navn");
		if (!is_numeric($item_id)) { $this->notSoFatalError("invalid id"); return; }
		$res = $this->query("SELECT id, brand_name FROM $this->table_brands WHERE id='$item_id'");
		if ($res->num_rows != 1) { $this->notSoFatalError("invalid item"); return; }
		$this->query("UPDATE $this->table_brands SET 
				brand_name='$item_name'
			WHERE id='$item_id'"
		);
		$this->redirect($this->generateCoolUrl("/edit-brands/"),"Varemerket ble oppdatert");
	}
	
	function deleteBrandQuery($id) {
		if (!$this->allow_edititems){ $this->permissionDenied(); return; }			
		if (!is_numeric($id)) { $this->notSoFatalError("invalid id"); return; }
		$res = $this->query("SELECT id, brand_name FROM $this->table_brands WHERE id=$id");
		if ($res->num_rows != 1) { $this->notSoFatalError("invalid item"); return; }
		$row = $res->fetch_assoc();
		$item_id = $row['id'];
		$item_name = stripslashes($row['brand_name']);
		$url_del = $this->generateCoolUrl("/delete-brand-do/","noprint=true");
		$url_cancel = "window.location=\"".$_SERVER['HTTP_REFERER']."\";";
		print "
			<h3>Slett varemerke</h3>
			<form method='post' action='$url_del'>
				<input type='hidden' name='item_id' value='$item_id' />
				Er du sikker på at du vil slette varemerket \"$item_name\"?<br /><br />
				<input type='button' value='Nei' onclick='$url_cancel' /> <input type='submit' value='Ja' />				
			</form>
		";
		call_user_func($this->add_to_breadcrumb,"
			<a href=\"".$this->generateCoolUrl("/edit-brands/")."\"><strong>Varemerker</strong></a>"
		);		
	}
	
	function deleteBrand() {
		if (!$this->allow_edititems){ $this->permissionDenied(); return; }			
		$item_id = addslashes($_POST['item_id']);
		if (!is_numeric($item_id)) { $this->notSoFatalError("invalid id"); return; }
		$res = $this->query("SELECT id, brand_name FROM $this->table_brands WHERE id='$id'");
		if ($res->num_rows != 1) { $this->notSoFatalError("invalid item"); return; }
		$this->query("DELETE FROM $this->table_brands WHERE id='$id' LIMIT 1");
		$this->redirect($this->generateCoolUrl("/edit-brands/"),"Varemerket ble slettet");
	}
	
	function addBrand() {
		if (!$this->allow_edititems){ $this->permissionDenied(); return; }			
		$url_save = $this->generateCoolUrl("/save-brand/","noprint=true");
		print "
			<h3>Legg til nytt varemerke</h3>
			<form method='post' action='$url_save'>
				Navn på varemerke: <input type='text' name='item_name' /><br /><br />
				<input type='submit' value='Lagre' />
			</form>
		";
		call_user_func($this->add_to_breadcrumb,"
			<a href=\"".$this->generateCoolUrl("/edit-brands/")."\"><strong>Varemerker</strong></a>"
		);
	}
	
	function saveBrand() {
		if (!$this->allow_edititems){ $this->permissionDenied(); return; }		
		$item_name = addslashes(strip_tags($_POST['item_name']));
		if (empty($item_name)) $this->fatalError("du må fylle inn et navn");
		$this->query("INSERT INTO $this->table_brands (brand_name) VALUES ('$item_name')");
		$this->redirect($this->generateCoolUrl("/edit-brands/"),"Varemerket ble lagt til");
	}	
	
	/******************************* MAP ************************************/
	
	function viewMap() {
	
		$ts = $this->table_status;
		$tc = $this->table_cats;
		$res = $this->query("SELECT 
				$ts.id, 
				$ts.unique_name,
				$ts.status,
				$ts.street,
				$ts.streetno,
				$ts.postno,
				$ts.city,
				$ts.contact_name,
				$ts.contact_email,
				$ts.contact_phone,
				$ts.comment,
				$ts.latitude,
				$ts.longitude,
				$tc.cat_name
			FROM 
				$ts,$tc 
			WHERE 
				$ts.category=$tc.id"
		);
		$points = "";
		while ($row = $res->fetch_assoc()) {
			$lat = $row['latitude'];
			$lng = $row['longitude'];
			if ($lat != 0) {
				$unique_name = stripslashes($row['unique_name']);
				$id = $row['id'];
				$html = "$unique_name<br /><br /><a href='".$this->generateCoolUrl("/agreement/$id")."'>Vis avtale</a>";
				$points .= "
				
		        var point = new GLatLng($lat,$lng);
	   	     	var marker = createMarker(point,\"$html\");
	   	    	map.addOverlay(marker);
				
				";
			}
		}
	
		$key = $this->googleMapsKey;
		
		$url_back = $this->generateCoolUrl("/");
		print "
			<p>
				<a href='$url_back'>Tilbake til liste</a>
			</p>
		
		<script src=\"http://maps.google.com/maps?file=api&amp;v=2&amp;key=$key\"
			type=\"text/javascript\"></script>
			<script type=\"text/javascript\">
    //<![CDATA[

    function load() {
      if (GBrowserIsCompatible()) {
        
        function createMarker(point,html) {
        	var marker = new GMarker(point);
        	GEvent.addListener(marker, \"click\", function() {
        		  marker.openInfoWindowHtml(html);
        	});
        	return marker;
      	}
            	
        var map = new GMap2(document.getElementById(\"map\"));
		map.addControl(new GLargeMapControl());
		map.addControl(new GMapTypeControl());	
		// map.addControl(new GScaleControl());	
		map.addControl(new GOverviewMapControl());
		map.setCenter(new GLatLng(60.3887, 5.3315), 15);
        
      	$points
        
      }
    }
    
    window.onload = load;
    window.onunload = GUnload;

    //]]>
    </script>

 	    <div id=\"map\" style=\"width: 700px; height: 600px;\"></div>

     ";
		call_user_func($this->add_to_breadcrumb,"
			<a href=\"".$this->generateCoolUrl("/map/")."\"><strong>Kart</strong></a>"
		);
	
	}
	
	function fetchPos($id) {
		if (!is_numeric($id)) { $this->notSoFatalError("invalid id"); return; }
		if ($id == 0) {
			$lat = 60.3887;
			$lng = 5.3315;
		} else {
			$ts = $this->table_status;
			$tc = $this->table_cats;
			$res = $this->query("SELECT 
					unique_name,
					latitude,
					longitude
				FROM 
					$ts 
				WHERE 
					id='$id'"
			);
			if ($res->num_rows != 1) { $this->notSoFatalError("invalid agreement"); return; }
			$row = $res->fetch_assoc();
			
			$unique_name = stripslashes($row['unique_name']);
			$lat = $row['latitude'];
			$lng = $row['longitude'];
		}
		
	
		$key = $this->googleMapsKey;
		print "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"
  \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\" style=\"height:100%\">
  <head>
    <meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\"/>
    <title>Velg ønsket posisjon:</title>
		<script src=\"http://maps.google.com/maps?file=api&amp;v=2&amp;key=$key\"
			type=\"text/javascript\"></script>
			<script type=\"text/javascript\">
    //<![CDATA[
	
    function load() {
      if (GBrowserIsCompatible()) {
        
        function createMarker(point) {
        	var marker = new GMarker(point);
        	return marker;
      	}
        
        var map = new GMap2(document.getElementById(\"map\"));
		GEvent.addListener(map, \"click\", function(marker, point) {
	  		if (marker) {
	  		
	  		} else {
		  		map.clearOverlays();
		  		map.addOverlay(new GMarker(point));
	  			window.opener.saveNewPos(point.lat(),point.lng());
		  	}
  		});
		map.addControl(new GLargeMapControl());
		map.addControl(new GMapTypeControl());	
		map.addControl(new GOverviewMapControl());
		
        map.setCenter(new GLatLng($lat,$lng), 15);
        
        ";
        if ($id != 0) {
        	print "
        	 	var point = new GLatLng($lat,$lng);
	   	     	var marker = createMarker(point);
	   	    	map.addOverlay(marker);
	   	    ";
        }
        print "
      }
    }
   
    //]]>
    </script>
    </head>
    <body onload=\"load()\" onunload=\"GUnload()\" style=\"height:100%;margin:0;background:#DFD;\">
     <div id=\"map\" style=\"width: 700px; height: 600px\"></div>
     <div id=\"message\"></div>
    </body>
    </html>";
     exit();
	
	}
	
	function editAvailability($id) {
		if (!$this->allow_edititems){ $this->permissionDenied(); return; }			
		if (!is_numeric($id)) { $this->notSoFatalError("invalid id"); return; }
		
		$ts = $this->table_status;
		$ta = $this->table_availability;
		$tb = $this->table_brands;
		$ti = $this->table_items;
		$tc = $this->table_itemcats;
		$res = $this->query("SELECT $ts.id, $ts.unique_name FROM $ts
			WHERE $ts.id='$id'");
		if ($res->num_rows != 1) { $this->notSoFatalError("invalid agreement"); return; }
		$row = $res->fetch_assoc();		
		$unique_name = stripslashes($row['unique_name']);
		$url_av = $this->generateCoolUrl("/agreement/$id");
		print "<p style='padding-bottom:15px;'>Rediger vareutvalg for <a href='$url_av'>$unique_name</a>:</p>";
		$js = "var categories = new Array();\n";
		$res = $this->query("SELECT 
				$ti.id,
				$ti.category
			FROM 
				$ti
			"
		);
		$cats = array();
		while ($row = $res->fetch_assoc()) {
			if (!in_array($row['category'],$cats)) {
				$cats[] = $row['category'];
				$js .= "categories[".$row['category']."] = new Array();\n";
			}
			$js .= "categories[".$row['category']."].push(".$row['id'].");\n";
		}
		print "
			<script type='text/javascript'>
			$js
			
			function checkAll(cat) {
				for (i = 0; i < categories[cat].length; i++) {
					\$('item'+categories[cat][i]).checked = true;
				}				
			}
			
			function uncheckAll(cat) {
				for (i = 0; i < categories[cat].length; i++) {
					\$('item'+categories[cat][i]).checked = false;
				}
			}
			
			</script>
		";
		
		$res = $this->query("SELECT 
				$ti.id as item_id,
				$ti.item_name,
				$tb.brand_name,
				$tc.id as cat_id,
				$tc.cat_name,
				$ta.id as av_id
			FROM 
				$ti
			LEFT JOIN 
				$ta ON $ta.item=$ti.id AND $ta.store=$id,
				$tc, $tb
			WHERE 
				$ti.category=$tc.id AND $ti.brand=$tb.id 
			ORDER BY $tc.cat_name
			"
		);
				
		$url_save = $this->generateCoolUrl("/save-availability/","noprint=true");
		print "<form method='post' action='$url_save' />
			<input type='hidden' name='id' value='$id' />";
		$current_cat = "";
		while ($row = $res->fetch_assoc()) {
			$item_id = $row['item_id'];
			$cat_id = $row['cat_id'];
			$item_name = stripslashes($row['item_name']);
			$cat_name = stripslashes($row['cat_name']);
			$brand_name = stripslashes($row['brand_name']);
			$check = (!empty($row['av_id']) ? ' checked=\"checked\"' : '');
			if ($current_cat != $cat_name) {
				print "
					<div style='clear:both;background:#eee; padding:3px;'><strong>$cat_name:</strong> 
						<span style='float:right'><a href='javascript:checkAll($cat_id); return false;'>Merk alle</a> |
						<a href='javascript:uncheckAll($cat_id); return false;'>Merk ingen</a> </span>
					</div>
				";
				$current_cat = $cat_name;
			}
			print "<label for='item$item_id' style='display: block; width: 340px; float:left;'><input type='checkbox' name='item$item_id' id='item$item_id'$check />$item_name <span style='color:#999; font-size:10px;'>($brand_name)</span></label>";
		}
		print "<p style='clear:both; padding-top:20px;'><input type='submit' value='Lagre utvalg' /></p>";
		print "</form>";
		
		
	}
	
	function saveAvailability() {
		if (!$this->allow_edititems){ $this->permissionDenied(); return; }			
		$id = $_POST['id'];
		$ts = $this->table_status;
		$ti = $this->table_items;
		$ta = $this->table_availability;
		if (!is_numeric($id)) { $this->notSoFatalError("invalid id"); return; }
		$res = $this->query("SELECT $ts.id, $ts.unique_name FROM $ts
			WHERE $ts.id='$id'");
		if ($res->num_rows != 1) { $this->notSoFatalError("invalid agreement"); return; }
		
		print_r($_POST);
		
		$res = $this->query("SELECT 
				$ti.id as item_id,
				$ta.id as av_id
			FROM 
				$ti
			LEFT JOIN $ta ON $ta.item=$ti.id AND $ta.store=$id"
		);
		while ($row = $res->fetch_assoc()) {
			$item_id = $row['item_id'];
			$av_id = $row['av_id'];
			$was_checked = !empty($av_id);
			$is_checked = (isset($_POST["item$item_id"]) && $_POST["item$item_id"] == 'on');
			// DEBUG: print "<br />$item_id: $was_checked - $is_checked";
			if ($is_checked && !$was_checked) {
				$this->query("INSERT INTO $ta (item,store) VALUES ($item_id,$id)");
				// DEBUG: print " insert";
			} else if (!$is_checked && $was_checked) {
				$this->query("DELETE FROM $ta WHERE id=$av_id");
				// DEBUG: print " delete";
			}
		}
		
		$this->redirect($this->generateCoolUrl("/agreement/$id"),"Vareutvalget ble lagret");
		
	}
	
	function viewItem($id) {
		if (!is_numeric($id)) { $this->notSoFatalError("invalid id"); return; }
		$ts = $this->table_status;
		$ti = $this->table_items;
		$ta = $this->table_availability;
		$res = $this->query("SELECT item_name FROM $ti WHERE id=$id");
		if ($res->num_rows != 1) { $this->notSoFatalError("invalid item"); return; }
		$row = $res->fetch_assoc();
		$item_name = stripslashes($row['item_name']);
		$res = $this->query("SELECT 
				$ts.id,
				$ts.unique_name
			FROM 
				$ta,$ts
			WHERE 
				$ta.item = $id 
				AND $ta.store = $ts.id"
		);
		print "
			<p>Følgende utsalgssteder fører $item_name:</p>
			<ul>
		";
		while ($row = $res->fetch_assoc()) {
			$store_id = $row['id'];
			$store_name = stripslashes($row['unique_name']);
			print "<li><a href=\"".$this->generateCoolUrl("/agreement/$store_id")."\">$store_name</a></li>\n";
		}
		print "
			</ul>
		";

		call_user_func($this->add_to_breadcrumb,"
			<a href=\"".$this->generateCoolUrl("/item/$id")."\"><strong>Varedetaljer for $item_name</strong></a>"
		);
		
	}
	
}

?>