<?php
class progress extends base {

	var $getvars = array("editpage");

	var $table_desc = "progress_pages";
	var $table_elem = "progress_elements";
	var $images_per_page = 20;

	var $FCKeditorWidth = 450;
	
	var $table_imagedirs = "cms_pages";
	var $table_images = "images";

	
	function progress() {
		$this->table_desc = DBPREFIX.$this->table_desc;
		$this->table_elem = DBPREFIX.$this->table_elem;
		$this->table_images = DBPREFIX.$this->table_images;
		$this->table_imagedirs = DBPREFIX.$this->table_imagedirs;
	}
	
	function initialize(){
	
		$this->initialize_base();
		
		if (count($this->coolUrlSplitted) > 0) 
			$this->action = $this->coolUrlSplitted[0];
		else 
			$this->action = "";
		
		if (isset($_GET['editpage'])) $this->action = "editpage";
	
	}
	
	function run(){
		$this->initialize();

		switch ($this->action) {

			case 'editpage':
				return $this->editPageForm();
				break;

			case 'savepage':
				return $this->savePage();
				break;

			case 'newcontrib':
				return $this->contributionForm();
				break;

			case 'editcontrib':
				return $this->contributionForm($this->coolUrlSplitted[1]);
				break;

			case 'savecontrib':
				return $this->saveContribution();
				break;
							
			/******* DEFAULT *******/
			
			default:
				return $this->welcome();
				break;
		
		}
		
	}
	
	
	function welcome() {
	
		$res = $this->query("SELECT intro,goal,goalunit FROM $this->table_desc WHERE page=".$this->page_id);
		if ($res->num_rows == 0) {
			return "<p>Denne siden er ikke satt opp enda. <a href='?editpage'>Sett opp siden</a></p>";
		}
		$output = "";
		$row = $res->fetch_assoc();
		$desc = stripslashes($row['intro']);
		$goal = intval($row['goal']);
		$goalUnit = stripslashes($row['goalunit']);
		
		if ($this->allow_write) {
			$output .= $this->make_editlink($this->generateURL("editpage"), "Rediger side");
		}
		
		$res = $this->query("SELECT SUM(value) as tot FROM $this->table_elem WHERE page=".$this->page_id);
		$row = $res->fetch_assoc();
		$soFar = intval($row['tot']);
		
		$p = floor($soFar/$goal*100);

		$output .= "
			<div style='padding: 10px 0px 10px 0px'>$desc</div>
			
			<div id='pbar'></div>
			
			<p style='font-weight:bold; margin-top:2px;'>
				$soFar / $goal $goalUnit samlet inn 
			</p>
			
			<script type=\"text/javascript\">
			//<![CDATA[

				$(document).ready(function() {
					$( '#pbar' ).progressbar({
					  value: $p
					});
				});
		
			//]]>			
			</script>
			
			<h2>Sponsorer så langt:</h2>
		";
		
		$output .= '<a href="'.$this->generateCoolUrl("/newcontrib").'">Registrér nytt bidrag</a>';
			
		$tl = $this->table_elem;
		$ti = $this->table_images;
		$res = $this->query("SELECT id,value,shortdesc,logo_image,link FROM $tl
			WHERE page = ".$this->page_id." ORDER BY $tl.value DESC"
		);
		
		$output .= "<table width='100%'>";
		while ($row = $res->fetch_assoc()) {
			
			$id = $row['id'];
			$logo_img = $row['logo_image'];
			$image = "";
			if (!empty($logo_img)){
				$image = "
					<div class='alpha-shadow'><div class='inner_div'>
						<img src='$logo_img' alt='Logo' />
					</div></div>
				";
			}
			
			$edit = $this->allow_write ? ' <span style="font-size:small;">[<a href="'.$this->generateCoolUrl("/editcontrib/$id").'">Rediger</a>]</span>' : '';

			$shortdesc = stripslashes($row['shortdesc']);
			if (!empty($row['link'])) {
				$shortdesc = '<a href="'.stripslashes($row['link']).'" target="_blank">'.$shortdesc.'</a>';
			}
			$output .= "<tr>
				<td style='width:180px;'>$image</td>
				<td>$shortdesc $edit</td>
				<td>".$row['value']." $goalUnit</td>
			</tr>";
		}
		$output .= "</table>";
		
		return $output;
	}
	
	function editPageForm() {
		
		if (!$this->allow_write) return $this->permissionDenied();

		$res = $this->query("SELECT intro,goal,goalunit FROM $this->table_desc WHERE page=".$this->page_id);
		if ($res->num_rows == 0) {
			$desc = "";
			$goal = "100";
			$goalUnit = "kr";
		} else {
			$row = $res->fetch_assoc();
			$desc = stripslashes($row['intro']);
			$goal = stripslashes($row['goal']);
			$goalUnit = stripslashes($row['goalunit']);
		}
		
		$this->setDefaultCKEditorOptions();
		
		return '
			<form method="post" action="'.$this->generateCoolUrl('/savepage','noprint=true').'">
				
				Mål: <input type="text" name="goal" value="'.$goal.'" /> 
					 <input type="text" name="goalUnit" value="'.$goalUnit.'" />
				<p>
					<textarea id="editor_body" name="editor_body" style="width:'.$this->FCKeditorWidth.'px; height:400px;">'.$desc.'</textarea>
				</p>
				<input type="submit" value="Ok" />
			</form>
			
			<script type=\'text/javascript\'>
			//<![CDATA[
					
				function initializeFCK() {

					var editor = CKEDITOR.replace( "editor_body", { 
						customConfig : "'.LIB_CKEDITOR_URI.'config_18bergen.js",
						toolbar: "VerySimpleBergenVS",
						width:500,
						height:400,
						resize_minWidth:500,
						resize_maxWidth:500, // disables horizontal resizing
						filebrowserImageBrowseUrl: "'.LIB_CKFINDER_URI.'ckfinder.html?type=Bilder&start=Bilder:/Diverse/Sponsorstafett&rlf=0&dts=1",
						filebrowserImageUploadUrl: "'.LIB_CKFINDER_URI.'core/connector/php/connector.php?command=QuickUpload&type=Bilder&currentFolder=/Diverse/Sponsorstafett/"
					});
				
				}
				
				$(document).ready(initializeFCK);
			
			//]]>
			</script>
		';
		
	}
	
	function savePage() {

		if (!$this->allow_write) return $this->permissionDenied(); 
		
		$intro = addslashes($_POST['editor_body']);
		$goal = intval($_POST['goal']);
		$goalUnit = addslashes($_POST['goalUnit']);
		if ($goal <= 0) { $_SESSION['err'] = 'invalidGoal'; exit(); }
		
		$res = $this->query("SELECT page FROM $this->table_desc WHERE page=".$this->page_id);
		if ($res->num_rows == 0) {
			$this->query("INSERT INTO $this->table_desc (intro,goal,goalunit,page) VALUES (\"$intro\", \"$goal\", \"$goalUnit\", ".$this->page_id.")");		
		} else {
			$this->query("UPDATE $this->table_desc SET intro=\"$intro\", goal=\"$goal\", goalunit=\"$goalUnit\" WHERE page=".$this->page_id);
		}
		
		$this->redirect($this->generateCoolUrl(""), "Siden ble lagret");
		
	}
	
	function contributionForm($id=0) {
		global $memberdb;

		if (!$this->allow_write) return $this->permissionDenied(); 
	
		$res = $this->query("SELECT goalunit FROM $this->table_desc WHERE page=".$this->page_id);
		if ($res->num_rows == 0) {
			return "<p>Denne siden er ikke satt opp enda. <a href='".$this->generateCoolUrl("","editpage")."'>Sett opp siden</a></p>";
		}
		
		$row = $res->fetch_assoc();
		$goalUnit = stripslashes($row['goalunit']);
		
		if ($id != 0) {
			if (!is_numeric($id)) $this->fatalError("Invalid id");
			$res = $this->query("SELECT value,shortdesc,logo_image,link FROM $this->table_elem WHERE page=".$this->page_id." AND id=".$id);
			$row = $res->fetch_assoc();
			$value = $row['value'];
			$link = $row['link'];
			$shortdesc = stripslashes($row['shortdesc']);
			$default_image = stripslashes($row['logo_image']);
		} else {
			$value = "";
			$link = "";
			$shortdesc = "";		
			$default_image = "";
		}		

		$pathToUserFiles = '/'.$this->userFilesDir;
		$pathToThumbs100 = $pathToUserFiles.'_thumbs140/';
		
		return "
			<form method='post' action=\"".$this->generateCoolUrl("/savecontrib","noprint=true")."\">
				
				<input type='hidden' name='id' value='$id' />
				Bidrag: <input type='text' name='value' value=\"$value\" /> $goalUnit<br />
				Navn: <input type='text' name='shortdesc' value=\"$shortdesc\" /><br />
				Evt. webside: <input type='text' name='link' value=\"$link\" /><br />
				Bilde/logo: ".'

					<input type="hidden" name="logo_image" id="lead_image" value="'.$default_image.'" />
					<a href="#" class="bildevelgerlink" onclick="BrowseServer(); return false;" style="margin-left:5px;" title="Trykk for å velge bilde">
						<span id="ingressbildespan">
							'.(empty($default_image) ? 
								'<strong>Velg bilde</strong> ' :
								'<img src="'.$default_image.'" border="0" alt="Velg bilde" style="margin:5px;" />'
							).'
						</span>
					</a>
				
				<br />
				<input type="submit" value="Ok" />
			</form>
			<script type="text/javascript">
			//<![CDATA[
				
				// Render CKFinder in a popup page:
				function BrowseServer() {
					var finder = new CKFinder() ;
					finder.basePath = "'.LIB_CKFINDER_URI.'" ;	// The path for the installation of CKFinder.
					finder.selectActionFunction = SetFileField ;
					finder.startupPath = "Bilder:/Diverse/Sponsorstafett/" ;
					finder.rememberLastFolder = false ;
					finder.startupFolderExpanded = true ;
					finder.disableThumbnailSelection = true ;
					finder.resourceType = "Bilder" ;
					finder.popup() ;
				}
				
				// Called when a file is selected in CKFinder:
				function SetFileField( fileUrl, data ) {
					var pathToUserFiles = "'.$pathToUserFiles.'";
					var pathToThumbs = "'.$pathToThumbs100.'";
					var f = pathToThumbs + fileUrl.substr(pathToUserFiles.length);
					$("#lead_image").val(f);				
					$("#ingressbildespan").html("<img src=\'" + f + "\' alt=\'Velg bilde\' border=\'0\' style=\'margin:5px;\' />");
				}
	
			//]]>
			</script>
		';
	
	}
	
	function saveContribution() {
		
		if (!$this->allow_write) return $this->permissionDenied(); 
		
		$id = intval($_POST['id']);
		$value = intval($_POST['value']);
		$logo = addslashes($_POST['logo_image']);
		$shortdesc = addslashes($_POST['shortdesc']);
		$link = addslashes($_POST['link']);
		if ($value <= 0) { $_SESSION['err'] = 'invalidValue'; exit(); }
		
		if ($id > 0) {
			$this->query("UPDATE $this->table_elem SET value=\"$value\", link=\"$link\", shortdesc=\"$shortdesc\", logo_image=\"$logo\" WHERE id=".$id);
		} else {
			$this->query("INSERT INTO $this->table_elem (page,value,logo_image,shortdesc,link) VALUES (".$this->page_id.", \"$value\", \"$logo\", \"$shortdesc\", \"$link\" )");
		}
		
		$this->redirect($this->generateCoolUrl(""), "Bidraget ble lagret");
	
	}
	

}
?>
