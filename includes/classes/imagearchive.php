<?
/* 

Version 2.1 - 23.07.10
	Added photo counts table

Version 2.0 - 28.12.09
    Modified folder structure for compliance with CKFinder

Version 1.8 - 26.06.09
	Removed chmodding
	Return output instead of printing it

Version 1.7 - 30.04.07 
	Better activitylog-integration
	Added user-tagging
	
Version 1.6 - 09.04.06 
	Edited templates
	Added option to choose source (camera or scan)

Version 1.5 - 19.03.06 
	Corrected file permissions of uploaded images and dirs
	Added ability to save proportions of lo-res thumbs

Version 1.4 - 28.12.05
	Cool URLs
	Added warning when safe_mode enabled. This often leads to timeout since set_timeout is disabled.

Version: 1.3 - 13.11.05
	Resizes hi-res images that exceeds max size.
	Improved updating of the thumbnailcreation-progress (improved dataflush)

Version: 1.2 - 08.10.05
	Added option to adjust the timestamps of multiple images at the same time.
	Added option to delete multiple images at the same time
	Added "Check All" and "Uncheck All"-buttons

Version: 1.1 - 01.10.05
	Option to hide images

Version 1.0 - ?
	Initial release

*/

require_once(BG_CLASS_PATH.'ThumbnailService.php');
require_once(BG_CLASS_PATH.'Utils.php');

class imagearchive extends comments {

	var $enable_calendar_functions = false;
	var $enable_exif_functions = false;
	var $use_title = false;
	var $lores_maxwidth = 140;
	var $lores_maxheight = 140;
	var $lores_keeppropotions = false;
	var $midres_maxwidth = 470;
	var $midres_maxheight = 600;
	var $hires_maxwidth = 2048;
	var $hires_maxheight = 2048;

	var $images_per_page = 15;
	var $columns = 3;

	var $allow_addimages = false;
	var $allow_editarchivesettings = false;
	var $allow_organizeimages = false;
	var $allow_deleteownimages = false;
	var $allow_deleteothersimages = false;
	var $allow_addarchives = false;
	var $allow_deleteownarchives = false;
	var $allow_deleteothersarchives = false;
	var $allow_tagself = false;
	var $allow_tagothers = false;
	var $allow_untagself = false;
	var $allow_untagothers = false;
	
	var $exif_dir = '../includes/exif/';
	
	/* Disse deklareres i cms_basic:
	var $table_pagelabels = 'cms_pagelabels';
	var $table_pages = 'cms_pages';
	*/
	var $calendar_class_id = 16;
	
	var $table_dirs = 'imgarchive_dirs';
	var $table_dirs_field_id				= 'id';
	var $table_dirs_field_pos				= 'position';
	var $table_dirs_field_timestamp			= 'timestamp';
	var $table_dirs_field_parentdir			= 'parentdir';
	var $table_dirs_field_cal_id			= 'cal_id';
	var $table_dirs_field_event_id			= 'event_id';
	var $table_dirs_field_directory			= 'directory';
	var $table_dirs_field_caption			= 'caption';
	var $table_dirs_field_thumbnail			= 'thumbnail';
	var $table_dirs_field_thumbdir			= 'thumbdir';
	var $table_dirs_field_deletereason      = 'deletereason';
	var $table_dirs_field_description		= 'description';
	var $table_dirs_field_showtitles		= 'showtitles';
	var $table_dirs_field_showweekdays		= 'showweekdays';
	
	var $table_files = 'imgarchive_files';
	var $table_files_field_id				= 'id';
	var $table_files_field_directory		= 'directory';
	var $table_files_field_filename			= 'filename';
	var $table_files_field_uploader			= 'uploadedby';
	var $table_files_field_title			= 'title';
	var $table_files_field_visible			= 'visible';
	var $table_files_field_deletereason     = 'deletereason';
	var $table_files_field_source		    = 'source';
	var $table_files_field_thumbwidth		= 'thumb_width';
	var $table_files_field_thumbheight		= 'thumb_height';

	var $table_tags = 'imgarchive_tags';
	var $table_tags_field_id 				= 'id';
	var $table_tags_field_img 				= 'img';
	var $table_tags_field_user 				= 'user';
	var $table_tags_field_fullname 			= 'fullname';
	var $table_tags_field_imgtab 			= 'imgtab';
	var $table_tags_field_x 				= 'x';
	var $table_tags_field_y 				= 'y';
	var $table_tags_field_taggedby			= 'taggedby';	
	
	var $table_counts = 'imgarchive_counts';

	var $no_thumbnail_found = 'nothumb.jpg';

	var $current_directory;
	var $current_image;
	var $use_hide = false;
	var $java_path_to_tiny;
	var $java_path_to_jupload;
	var $use_tagging = false;

/*	var $lo_res_dir = 'lo-res'; // relative
	var $mid_res_dir  = 'mid-res'; // relative
	var $hi_res_dir  = 'hi-res'; // relative
*/
	var $root_dir = '/userfiles/';
	var $temp_dir = '_temp/';
	var $hi_res_dir = 'Bilder/Bildearkiv';
	var $mid_res_dir = '_thumbs490/Bilder/Bildearkiv';
	var $lo_res_dir = '_thumbs140/Bilder/Bildearkiv';
	
	/* GENERIC LABELS */
	
	var $label_yes = 'Ja';
	var $label_no = 'Nei';
	var $label_move = 'Flytt';
	var $label_cancel = 'Avbryt';
	var $label_back = 'Tilbake';
	var $label_pagexofy = 'Side %x% av %y%';
	var $label_firstpage = 'Første side';
	var $label_prevpage = 'Forrige side';
	var $label_nextpage = 'Neste side';
	var $label_lastpage = 'Siste side';
	var $label_welcome = 'Velkommen til bildearkivet vårt som inneholder %imagecount% bilder.';

	
	/* SPECIFIC LABELS */
	
	var $label_firstimage = 'Første bilde';
	var $label_previmage = 'Forrige bilde';
	var $label_nextimage = 'Neste bilde';
	var $label_lastimage = 'Siste bilde';


	var $label_backto = 'Tilbake til';
	var $label_imagearchive = 'album';
	var $label_imagesfrom = 'Bilder fra';
	var $label_deleteimage = 'Slett bilde';
	var $label_moveimage = 'Flytt bilde';
	var $label_edittitle = 'Endre tittel';
	var $label_hideimage = 'Skjul bilde';
	var $label_rotateleft = 'Rotér 90º mot klokken';
	var $label_rotateright = 'Rotér 90º med klokken';
	
	var $label_addimagestoarchive = '';
	var $label_addimagestoarchive_hint = '';
	var $label_addarchive = '';
	var $label_editarchivesettings = '';
	var $label_movearchive = '';
	var $label_deletearchive = '';
	var $label_makethumbs = '';
	var $label_slideshow = '';
	var $label_zip = '';
	var $label_zip_note = '';
	
	var $label_archivenotfound = 'Det etterspurte arkivet ble ikke funnet';
	var $label_invalidinput = 'Fy!';
	var $label_imagenotfound = 'Det etterspurte bildet ble ikke funnet';
	
	var $label_imgxofy = 'Bilde %x% av %y% (%w% x %h%)';

	

	var $othercreatornotice_caption = '<b>NB</b>:
		Dette bildet ble ikke lastet opp av deg. Vær ekstra forsiktig med bilder andre har lastet opp!';
	var $deletenonemptyarchive_caption = '
		Dette albumet inneholder %count% bilder. Disse vil bli slettet <strong>permanent</strong> hvis du velger å fortsette!';
	var $movenonemptyarchive_caption = '<b>NB</b>:
		Dette albumet inneholder %count% bilder.';
	var $missingtimestamps_caption = '<b>NB</b>:
		%count% bildefiler i dette albumet mangler EXIF-tidsangivelse. 
		Bildene kan derfor være feil sortert.';
	var $missingthumbnail_caption = '<b>NB</b>:
		Dette albumet mangler et representativt bilde (thumbnail). Gå til «Innstillinger» for å velge ett.';
	var $archive_empty_caption = '
		Dette albumet er tomt.';

	var $dirthumbs_header =  '
		<h3>%path%</h3>
		%description%
		%actions%
	';
	var $dirthumb_template = '
		<a id="album%id%" href="%dirlink%" class="photoAlbum">
			<div class="alpha-shadow" style="cursor:pointer;cursor:hand;" onclick=\'window.location="%dirlink%"\'>
				<div class="inner_div">
					%overlay%
					<img src="%imgsrc%" alt="%caption%" style="width:200px; height:150px;" />
				</div>
			</div>
			<div style="clear:both;text-align:center">
		%caption%</div></a>
			
	';
	var $midresimage_template = '
		
		<script type="text/javascript">
		//<![CDATA[
		
			var bildeTittel = "";

			// ============================ 1) Load YUI lib ===================================

			loader.require("connection","json");
			loader.insert();
				
			function onYuiLoaderComplete() {
				// make edit link clickable
			}
		
			function errorMsg(txt) {
				$("img_title").innerHTML = \'<form method="post" onsubmit="saveTitle(); return false;"><input type="text" id="title_edit" value="\'+bildeTittel+\'" style="border: 1px solid #888; width:300px;" /></form><div style="color:red;font-weight:bold;">\'+txt+\'</div>\';
				$("title_edit").focus();
			}
			
			// ============================ 1) Edit and save title ================================

			function editTitle() {
				bildeTittel = $("bildetittel").value;
				bildeTittel = bildeTittel.replace(new RegExp("\"", "g"),"&quot;");
				$("img_title").innerHTML = \'<form method="post" onsubmit="saveTitle(); return false;"><input type="text" id="title_edit" value="\'+bildeTittel+\'" style="border: 1px solid #888; width:300px;" /></form>\';
				$("title_edit").focus();
			}
			
			function saveTitle() {
				var url = "%title_url%";
				bildeTittel = $("title_edit").value;
				$("img_title").innerHTML = "Lagrer…";
				YAHOO.util.Connect.asyncRequest("POST", url, {
					success: titleSaved,
					failure: titleSaveFailed
				}, "title="+bildeTittel);
			}

			function titleSaved(o) {
				try {
					var json = YAHOO.lang.JSON.parse(o.responseText);
					if (json.error == "0") {
						var newTitle = json.title;
						var titleAsDisplayed = newTitle;
						if (titleAsDisplayed=="") titleAsDisplayed = "<em style=\"font-weight:normal; color: #888;\">Ingen tittel</em>";
						$("img_title").innerHTML = \'<div onclick="editTitle();" style="cursor:pointer;"><input type="hidden" id="bildetittel" value="\'+newTitle+\'" />\'+titleAsDisplayed+\'</div>\';
					} else {
						errorMsg(json.error);
					}
				} catch (e) {
					errorMsg("Det oppstod en feil under lagringen ("+o.responseText+")");
				}
			}
			
			function titleSaveFailed(o) {
				errorMsg("Det oppstod en feil");					
			}	

		//]]>
		</script>
				
		<p align="center">
			%firstimage% | %previousimage% | <b>Bilde %imageno% av %imagecount%</b> | %nextimage% | %lastimage%<br />
		</p>
		%main_image%
		<div style="clear:both;">
			<!--<img src="%src%" width="%width%" height="%height%" />-->
			<div class="auto_complete" id="ppl_list" style="display:none"></div> 
			<div id="img_title" style="text-align: center; margin-top:1px; font-size:12px; font-weight: bold;">%titlehtml%</div>
			%options%
		</div>
		<p style="padding-top:5px;">
			%infotext%
		</p>
		<p>
			%download_image% %image_actions%
		</p>		
		
	';
	
	var $template_mainImage = '
	<div style="margin-left:%sidemargin%px; margin-right:%sidemargin%px;">
			<div class="alpha-shadow" id="fullsize">
				<div class="inner_div">
					<!-- %linkbegin% -->
						<div class="pseudoImg">
							<div id="pointer_div" style="background: url(%src%); width:%width%px; height:%height%px;">
								
								<div id="cross" class="tagged_person_cross"><div></div></div>
								<div id="name_float" class="tagged_person_name"></div>
								
								<span id="crossform">
									<span id="tag_user">%tag_user%</span>
								</span>
							</div>
						</div>
					<!-- %linkend% -->
				</div>
			</div>
		</div>
	';
/*
						%linkbegin%
						%linkend%

*/
	var $imagethumbs_header =  '
		<!--<h2>%caption%</h2>-->
		<h3>%path%</h3>
		%description%
		<p>%num_images% %image_location%</p>
		<div style="margin:3px;padding:1px;border:1px solid #ddd;">
			<div style="padding:4px;">
				%slideshow%
				%zip%
				%addimagestoarchive%
			</div>
			<div style="padding:4px;">			
				%addarchive%
				%editarchivesettings%
				%organize%
			</div>
		</div>
		<script type="text/javascript">
		//<![CDATA[
			YAHOO.util.Event.onDOMReady(function() {
				Nifty("h4.whiteInfoBox");
			});
		//]]>
		</script>
	';
	var $imagesfrom_template =  '
		<!--<h2>%caption%</h2>-->
		<h3>
			%path%
		</h3>
	';
	
	var $str_before_imagethumbs = '<div class="photoFrame">';
	var $template_imagethumb_frame = '
		%clearboth%
		<a href="%imagelink%" title="%img_title%" style="display:block;float:left; font-size:10px; margin:0px; padding:0px;">
			<div style="float:left; padding: %paddingtop%px %paddingleft%px 0px %paddingleft%px;">
			<div class="alpha-shadow" style="cursor:pointer;cursor:hand;" onclick=\'window.location="%imagelink%"\'>
				<div class="inner_div">
					%comment_indicator%
					<img src="%imgsrc%" alt="%title%" style="width:%width%px; height:%height%px;" />
				</div>
			</div>
			</div>
			<div style="clear:both;text-align:center; width: 160px;"><!-- IE no-height fix -->%title%</div></a>
		
	';
	var $template_imagethumb_noframe = '
	
		<a href="%imagelink%" class="noframe" title="%img_title%" style="width:%framewidth%px;height:%framewidth%px;">
			<img src="%imgsrc%" alt="%title%" style="width:%width%px;height:%height%px;" />
		</a>
	';
	var $template_imagethumb_nolinks = '
	
		<div class="noframe" style="width:%framewidth%px;height:%framewidth%px;">
			<img src="/18bergen%imgsrc%" alt="%title%" style="width:%width%px;height:%height%px;" />
		</div>
	';
	
	var $str_after_imagethumbs = '</div><div style="clear:both;"><!-- --></div>';

	var $thumbprogress_template = '
		<div style="background: #EEEEEE; border: 1px solid #00000; width: 500px;">
			<div style="background: #CCCCFF; font-family: Tahoma; font-size: 12px; font-weight: bold; padding: 3px; text-align: center; border-bottom: 1px solid #777799; border-right: 1px solid #777799; border-top: 1px solid #FFFFFF; border-left: 1px solid #FFFFFF;">
				<span id="tittel">Behandler opplastede bilder - vennligst vent...</span>
			</div>
			<div style="padding: 5px; border-bottom: 1px solid #777799; border-right: 1px solid #777799; border-top: 1px solid #FFFFFF; border-left: 1px solid #FFFFFF; font-family: Tahoma; font-size: 12px;">
				<span id="sp1">Vent litt... Om det tar for lang tid er kanskje javascript avslått. Status vil da ikke bli vist.</span><br />
				<span id="sp2"></span><br />
				<span id="sp3"></span><br />
				<div style="border: 1px solid #000000; margin-top: 5px;">
					<div name="pbar" id="pbar" style="border-bottom: 1px solid #115511; border-right: 1px solid #115511; border-top: 1px solid #ccddcc; border-left: 1px solid #ccddcc; height: 12px; background: #00CC66; width: 90%;">
					</div>
				</div>
			</div>
		</div>
		<script type="text/javascript">
			//<![CDATA[
			function getObject(objectId){
				if(document.getElementById && document.getElementById(objectId)) {
					return document.getElementById(objectId); // W3C DOM
			    } else if (document.all && document.all(objectId)) {
					return document.all(objectId); // MSIE 4 DOM
			 } else if (document.layers && document.layers[objectId]) {
					return document.layers[objectId]; // NN 4 DOM.. note: this won"t find nested layers
			    } else {
					return false;
			    }
			}	

			function setText(layername, newtext){
				var theObject = getObject(layername);
					theObject.innerHTML = newtext;
			}

			function setProgress(percent){
				var theObject = getObject("pbar");
					theObject.style.width = percent;
			}
			//]]>
		</script>
		<div style="font-family: Tahoma; font-size: 9px; color: #666666; margin-top: 20px;">
		<strong>Log:</strong><br />
	';
	
	var $smallthumbsfooter_template = '
		<p align="center">
			%prevpagelink% %gouplink% %nextpagelink%
		</p>
	';
	
	var $template_archivesettings = '
		<script type="text/javascript">
		//<![CDATA[
			
			function fetch_events() {
				var url = "%events_uri%";
				var pars = new Array();
				pars.push("cal_page_id=" + $("cal_id").value);
				pars = pars.join("&");
				var success = function(t){ 
					setText("span_event",t.responseText);
				}
				setText("span_event", "Vent litt...");
				var myAjax = new Ajax.Request(url, {method: "post", parameters: pars, onSuccess:success});
			}
			
			function select_imagedir(nr) {
				var url = "%imgdir_uri%";
				var pars = new Array();
				pars.push("select_nr="+nr);
				pars.push("select_value=" + $("img"+nr).value);
				pars = pars.join("&");
				var success = function(t){ 
					setText("imgspan"+(nr+1),t.responseText);
				}
				setText("imgspan"+(nr+1), "Vent litt...");
				var myAjax = new Ajax.Request(url, {method: "post", parameters: pars, onSuccess:success});
			}

			function select_image(nr) {
				var url = "%img_uri%";
				var pars = new Array();
				pars.push("select_nr="+nr);
				pars.push("select_value=" + $("img"+nr).value);
				pars = pars.join("&");
				var success = function(t){ 
					setText("imgspan"+(nr+1),t.responseText);
				}
				setText("imgspan"+(nr+1), "Vent litt...");
				var myAjax = new Ajax.Request(url, {method: "post", parameters: pars, onSuccess:success});
			}
			
		//]]>
		</script>
	
		<h3>Operasjoner for «%path%»</h3>
				%movearchive%
				%deletearchive%
				%makethumbs%		
		<h3>Innstillinger for «%path%»</h3>
		<form method="post" action="%post_uri%">
			<table>
				<tr>
					<td>Navn på arkivet: </td>
					<td><input type="text" name="caption" value="%caption%" style="width: 260px;" /></td>
				</tr>
				<!--
				<tr>
					<td valign="top">Mappenavn: </td>
					<td><input type="text" name="caption" value="%folder%" disabled="disabled" style="width: 260px;" /></td>
				</tr>
				-->
				<tr>
					<td valign="top">Beskrivelse: </td>
					<td><textarea name="description" style="width: 260px; height: 90px;">%description%</textarea></td>
				</tr>
				<tr>
					<td valign="top">Vis ukedager? </td>
					<td><input type="checkbox" name="showweekdays" %show-weekdays% /></td>
				</tr>
				<tr style="%hendelse_style%">
					<td>Knytt til hendelse: </td>
					<td>
						<select name="cal_id" id="cal_id" onchange="fetch_events();" style="font-size:90%; border: 1px solid #666666;">%cal-list%</select>
						<span id="span_event">%events%</span>
					</td>
				</tr>
				<tr><td valign="top">Representativt bilde: </td><td>
					<span id="imgspan0">
						%img-list%
					</span>
				</td></tr>
			</table>
			<input type="submit" value="    Lagre innstillinger    " />
			<p>&nbsp;</p>
		</form>		
	';


	var $weekDays = array("søndag","mandag","tirsdag","onsdag","torsdag","fredag","lørdag");
	var $months = array("","januar","februar","mars","april","mai","juni","juli","august","september","oktober","november","desember");
	var $shortmonths = array("","jan","feb","mar","apr","mai","jun","jul","aug","sep","okt","nov","des");

	var $calendarlookup_function;

	var $page_no = 1;


	/*******************************************************************************************
		 Utility functions                                                               
		 **************************************************************************************/

	/*
		Function: updatePhotoCounts
		This function updates the "photo counts" table, which caches the number of photos
		contained in each album and its sub-albums. This function should be called after
		photos has been added to or removed from the photo archive.
	*/
	function updatePhotoCounts($albumId = 1) { // 1 is the root directory
		$res = $this->query("SELECT id FROM $this->table_dirs WHERE parentdir=$albumId");
		if ($res->num_rows != 0) {
			$cnt = 0;
			while ($row = $res->fetch_assoc()) {
				$cnt += $this->updatePhotoCounts(intval($row['id']));
			}
		} else {
			$res3 = $this->query("SELECT COUNT(id) FROM $this->table_files WHERE directory=$albumId AND deletereason='' AND visible=1");
			$row3 = $res3->fetch_row();
			$cnt = intval($row3[0]);
		}
		$this->query("INSERT INTO $this->table_counts (id,photo_count) VALUES ($albumId,$cnt) ON DUPLICATE KEY UPDATE photo_count=$cnt");
		return $cnt;
	}
	
	function getPeopleOnPhoto($photoId) {
		$photoId = intval($photoId);
		$res = $this->query("SELECT user, x, y, width, height 
			FROM $this->table_tags WHERE imgtab=".$this->page_id." AND img=$photoId");
		$tags = array();
		$ppl = '';
		while ($row = $res->fetch_assoc()) {
			$m = call_user_func($this->lookup_member,$row['user']);
			$tags[] = array(
				'id' => $row['user'],
				'fullName' => $m->fullname,
				'firstName' => $m->firstname,
				'url' => $m->url,
				'position' => array(
					'x' => intval($row['x']),
					'y' => intval($row['y']),
					'width' => intval($row['width']),
					'height' => intval($row['height'])
				)
			);
		}
		return $tags;
	}
	
	function getUrlTo($directory, $filename = '', $size = 'original') {
		if ($filename == 'thumbnail') return $this->root_dir.$this->hi_res_dir.$directory.'thumbnail.jpg';
		switch ($size) {
			case 'small': return $this->root_dir.$this->lo_res_dir.$directory.$filename;
			case 'medium': return $this->root_dir.$this->mid_res_dir.$directory.$filename;
			default: return $this->root_dir.$this->hi_res_dir.$directory.$filename;
		}
	}
	
	function getPathTo($directory, $filename = '', $size = 'original') {
		if ($filename == 'thumbnail') return $this->root_path.$this->hi_res_dir.$directory.'thumbnail.jpg';
		switch ($size) {
			case 'small': return $this->root_path.$this->lo_res_dir.$directory.$filename;
			case 'medium': return $this->root_path.$this->mid_res_dir.$directory.$filename;
			default: return $this->root_path.$this->hi_res_dir.$directory.$filename;
		}
	}
	
	/* Don't rename! Used by CMS */
	function getLinkToEntry($id) {
		$id = intval($id);
		
		$td = $this->table_dirs;
		$tf = $this->table_files;
		$res = $this->query(
			"SELECT $td.directory as dir FROM $td,$tf WHERE $tf.id=$id AND $tf.directory = $td.id"
		);
		$row = $res->fetch_assoc();
		return $this->generateCoolURL($row['dir'].$id);
	}
	
	function twoDigits($t){
		return ($t < 10) ? "0".$t : $t;
	}
	
	function fiveDigits($int){
		if ($int < 10) return "0000$int";
		else if ($int < 100) return "000$int";
		else if ($int < 1000) return "00$int";
		else if ($int < 10000) return "0$int";
		else if ($int < 100000) return "$int";
	}
	
	function getDirInfo($id){
		$id = intval($id);
		$td = $this->table_dirs; $tf = $this->table_files;
		$res = $this->query(
			"SELECT $td.id,$td.directory,$td.cal_id,$td.event_id,$td.caption,$td.parentdir,
				$td.thumbnail,$td.deletereason,$td.description,$td.showtitles,$td.showweekdays,
				COUNT($tf.id) as image_count
			FROM $td,$tf WHERE $td.id=$id AND $tf.directory=$td.id AND $tf.deletereason=\"\""
		);
		if ($res->num_rows != 1){
			if ($id == 1){
				$this->query(
					"INSERT INTO $td (id,parentdir,directory,caption) VALUES (1,0,'/','Bildearkiv')"
				);
				$id = $this->insert_id();
				$this->addToActivityLog("opprettet bildearkivet \"Bildearkiv\" (ID: $id) ");
				$res = $this->query(
					"SELECT id,cal_id,event_id,caption,parentdir,directory,thumbnail,deletereason,
						description,showtitles,showweekdays FROM $td WHERE id=$id"
				);
				return $res->fetch_assoc();	
			} else {
				$this->fatalError("[imagearchive] Directory $id doesn't exist!");
			}
		}
		return $res->fetch_assoc();	
	}
	
	function dirExists($id){
		if (!is_numeric($id)) return false;
		$id = intval($id);
		$res = $this->query(
			"SELECT id FROM $this->table_dirs WHERE id=$id"
		);
		if (($res->num_rows != 1) && ($id != 1)) return false;
		return true;
	}
	
	function generatePath($albumId){
		$c = intval($albumId);
		$path = array();
		while ($c != 0){
			$res = $this->query(
				"SELECT parentdir,directory,caption FROM $this->table_dirs WHERE id=$c"
			);
			$row = $res->fetch_assoc();
			if ($row['parentdir'] != '0') {
				if (!isset($pageCaption)) $pageCaption = stripslashes($row['caption']);
				array_push($path,array("id" => $c,"caption" => stripslashes($row['caption']),"directory" => $row['directory']));
			}
			$c = intval($row['parentdir']);
		}
		if (!isset($pageCaption)) $pageCaption = ''; 
		$path = array_reverse($path);			
		foreach ($path as $i => $o){
			if (isset($path_html)){ $path_html .= " &gt; "; } else { $path_html = ""; }
			$lnk =  '<a href="'.$this->generateCoolURL($o['directory']).'">'.$o['caption'].'</a>';
			call_user_func($this->add_to_breadcrumb, $lnk);
			$path_html .= $lnk;
		}
		if (!isset($path_html)) $path_html = '';		
		$rootCaption = stripslashes($row['caption']); 
		return array($rootCaption,$pageCaption,$path_html);
	}

	function resolvePath($path){
		$res = $this->query("SELECT 
				$this->table_dirs_field_id as id,
				$this->table_dirs_field_deletereason as dl
			FROM $this->table_dirs
			WHERE $this->table_dirs_field_directory='".addslashes($path)."'"
		);
		if ($res->num_rows == 1){
			$row = $res->fetch_assoc();
			return intval($row['id']);
		} else if ($res->num_rows > 1){
			while ($row = $res->fetch_assoc()) {
				if ($row['dl'] == '') return intval($row['id']);
			}
			return intval($row['id']);
		} else {
			return false;
		}
	}
	
	function getFileExt($f){
		$t = explode(".",$f);
		return array_pop($t);
	}


	/*******************************************************************************************
		 General                                                               
		 **************************************************************************************/
	
	function imagearchive() {
		$this->table_dirs = DBPREFIX.$this->table_dirs;
		$this->table_files = DBPREFIX.$this->table_files;
		$this->table_tags = DBPREFIX.$this->table_tags;
		$this->table_counts = DBPREFIX.$this->table_counts;
		
		$this->table_comments = DBPREFIX.'comments';
	}

	function initialize(){
		@parent::initialize(); //$this->initialize_base(); $this->initialize_comments();

		array_push($this->getvars,'savetitles','ajaxtitle','archive_page','dir','img','errs',
			'uploadinfo','addimagestodir','moveimagestodir',
			'imageuploaderror','editarchivesettings','savearchivesettings','deleteimage',
			'dodeleteimage','addarchiveto','savearchiveto','deletearchive','dodeletearchive',
			'thumbparentdir','hideimage','handling','direction', 'days', 'hours', 'minutes','seconds',
			'moveimage','movetodir','domoveimage','addarchive','makethumbs','makethumb','setsource',
			'slideshow','stats','thumbscreated','tag','untag','movearchive','domovearchive',
			'fetcharchive','fetch-events','fetch-imglist','fetch-img','testupload','testupload-do',
			'testupload-view','action');

		if (isset($_GET['archive_page'])){
			if (!is_numeric($_GET['archive_page'])) $this->fatalError("Invalid page!");
			$this->page_no = $_GET['archive_page'];
		}
	}
	
	function run(){
		$this->initialize();
				
		$this->root_path = $this->path_to_www . $this->root_dir;
		$this->temp_path = $this->root_path . $this->temp_dir;
		
		if (isset($_GET['import'])) {
			$this->import();
			exit();
		}
		
		if (!empty($_FILES)){
			if ((isset($_GET['userid'])) && (is_numeric($_GET['userid']))){
				$this->uploadImagesDo();
			}
		}

		/* 
		##	Determine current directory and current image, and fetch some info on the current
		##	directory.
		*/
			
		$this->current_directory = (count($this->coolUrlSplitted) > 0) ? 
			"/".implode("/",$this->coolUrlSplitted)."/" : "/";

		if (isset($this->current_directory)){
			$curDirID = $this->resolvePath($this->current_directory);
			
			if ($curDirID === false){
				$tmp = explode("/",$this->current_directory); 
				array_pop($tmp); 
				$tmp2 =  array_pop($tmp); 
				$this->current_directory = implode("/",$tmp)."/";
				$curDirID = $this->resolvePath($this->current_directory);
				
				if ($curDirID === false){
					if ($this->current_directory == "/") $curDirID = $this->createRootDirectory();
                    else return $this->pageNotFound($this->label_archivenotfound); # base function
				} else {
					$this->current_image = $tmp2;
				}
			}
			$this->current_directory = $curDirID;
		} else {
			$this->current_directory = 1;
		}
		if (!$this->dirExists($this->current_directory)) return $this->notSoFatalError("
			[imagearchive] Det ble ikke funnet noe album med denne adressen.");
		$this->current_directory_details = $this->getDirInfo($this->current_directory);
		$this->subdirs = $this->query(
			"SELECT id,directory,caption,thumbnail,position,cal_id,event_id
			FROM $this->table_dirs
			WHERE parentdir=$this->current_directory
			AND $this->table_dirs_field_deletereason=''
			ORDER BY position DESC"
		);

		if (isset($this->current_image)){
            if (!is_numeric($this->current_image)) return $this->pageNotFound(
                'Det etterspurte albumet eller bildet ble ikke funnet!'
            );
			$this->current_image = intval($this->current_image);
			$res = $this->query("SELECT id FROM $this->table_files 
				WHERE id=$this->current_image AND directory=$this->current_directory"
			);
			if ($res->num_rows != 1)
                return $this->pageNotFound(
                    'Bildet finnes ikke. Sjekk at adrressen er riktig.<br /><br />'.
                    'Du kan forsøke å finne bildet i albumet «<a href="'.$this->generateCoolUrl($this->current_directory_details['directory']).'">'.
                    $this->current_directory_details['caption'].'</a>»'
                ); # base function
		}
		
		/*
		DEBUG:
		print_r($this->current_directory_details);
		exit();
		*/
		
		/* 
		##	Determine and execute requested action
		*/
		
		$albumActions = array(
			'addAlbum','addAlbumDo','deleteAlbum','deleteAlbumDo',
			'uploadInfo','uploadImages','uploadImagesDo','uploadError','processUploadedImages',
			'makeThumbs','ajaxSetImageSource','ajaxMakeThumb','ajaxThumbsCreated',
			'albumSettings','albumSettingsSave','organizeImages',
			'ajaxFetchEvents','ajaxFetchImagelist','ajaxFetchImage',
			
			'slideshow','downloadAlbum','reorderAlbums','reorderAlbumsDo',
			'moveAlbum','moveAlbumDo','deleteAlbum','deleteAlbumDo'
		);
		$photoActions = array('hidePhoto','unhidePhoto',
			'deletePhoto','deletePhotoDo','movePhoto','movePhotoDo','ajaxSavePhotoTitle',
			'ajaxTagUser','ajaxUntagUser','rotateLeft','rotateRight'
		);
		
		$action = isset($_GET['action']) ? $_GET['action'] : '';
		if (isset($this->current_image)){
			if (!in_array($action,$photoActions)) $action = 'showPhoto';
		} else if ($this->current_directory_details['image_count'] > 0) {
			if (!in_array($action,$albumActions)) $action = 'showPhotoThumbs';
		} else {
			if (!in_array($action,$albumActions)) $action = 'showAlbumThumbs';
		}
		return call_user_func(array($this,$action));
		
	}
	
	function createRootDirectory() {
		$this->query(
			"INSERT INTO $this->table_dirs 
				(
				$this->table_dirs_field_id,
				$this->table_dirs_field_parentdir,
				$this->table_dirs_field_directory,
				$this->table_dirs_field_caption
			) VALUES (
				'1',
				'0',
				'/',	
				'Bildearkiv'
			)"
		);
		$id = 1;
		$this->addToActivityLog("opprettet bildearkivet \"Bildearkiv\" (ID: 1) ");
		return 1;
	}

	/*******************************************************************************************
		 Print album thumbs                                                               
		 **************************************************************************************/

	function reorderAlbums() {

		if (!$this->allow_editarchivesettings) return $this->permissionDenied(); 	
		$cd = $this->current_directory_details;
		$albumId = $this->current_directory;
		if (!is_numeric($albumId)) $this->fatalError("[imagearchive] Invalid input!");		
		$tf = $this->table_files; $td = $this->table_dirs;
		$pathArray = $this->generatePath($albumId); // Needed for breadcrumb
		if ($cd['deletereason'] != ""){
			return '<p class="warning">Albumet «'.$cd['caption'].'» er slettet.</p>';
		}
		if ($albumId <= 0){
			return '<p class="warning">Du kan ikke endre albumrekkefølgen for dette albumet.</p>';		
		}
		
		$dirthumb_template = '
		<li id="album%id%" style="display:block;float:left;cursor:move;">
			<div class="alpha-shadow">
				<div class="inner_div">
					<img src="%imgsrc%" alt="%caption%" style="width:200px; height:150px;" />
				</div>
			</div>
			<div style="clear:both;text-align:center">
		%caption%</div>
		</li>';


		$output = "<h3>Endre albumrekkefølge</h3><p class='help'>Dra og slipp albumene for å endre rekkefølgen. 
			Trykk deretter på «Lagre ny rekkefølge» for å lagre den nye rekkefølgen.</p>
		
		<form id='reorderForm' method='post' action=\"".$this->generateURL('action=reorderAlbumsDo')."\">
			<input type='hidden' id='newOrder' name='newOrder' value='' />
			<input type='button' value='Avbryt' onclick='window.location=\"".$this->generateURL("")."\"' />
			<input type='submit' value='Lagre ny rekkefølge' />
		</form>
		
		<ul id='albums' style='margin:0px;padding:0px;'>\n";
		$albumCount = $this->subdirs->num_rows;
		$albums = array();
		while ($archive = $this->subdirs->fetch_assoc()){
			$thumbnail = $this->getUrlTo($archive['directory'],'thumbnail');
			$thumbnail = (empty($archive['thumbnail']) ? $this->image_dir.$this->no_thumbnail_found : $thumbnail);
			$r1a = array();	$r2a = array();
			$r1a[] = "%dirlink%";	$r2a[]  = $this->generateCoolURL($archive['directory']);
			$r1a[] = "%imgsrc%";	$r2a[]  = $thumbnail;
			$r1a[] = "%caption%";	$r2a[]  = $archive['caption'];
			$r1a[] = "%id%";		$r2a[]  = $archive['id'];
			$output .= str_replace($r1a, $r2a, $dirthumb_template);
			$albums[] = intval($archive['id']);
		}
		$output .= "</ul>\n";
		$output .= "<div style='clear:both;'></div>
		

				<script type=\"text/javascript\">
				//<![CDATA[
								
				var Dom = YAHOO.util.Dom;
				var Event = YAHOO.util.Event;
				var DDM;

				function onYuiLoaderComplete() {
					initDragDrop();
				}

				loader.require('dragdrop','animation');
				loader.insert();
				
				//////////////////////////////////////////////////////////////////////////////
				// example app
				//////////////////////////////////////////////////////////////////////////////
				
				var albums = [".implode(',',$albums)."];
				function initDragDrop() {

					initDDlist();
					new YAHOO.util.DDTarget('albums');
				
					for (var i=0;i<albums.length;i=i+1) {
						new YAHOO.example.DDList('album' + albums[i]);
						console.log('album' + albums[i]);
					}
					
					Event.addListener('reorderForm','submit',onFormSubmit);
				
				}
				
				function onFormSubmit(e) {
		            var items = $('albums').getElementsByTagName('li');
		            var newOrder = [];
		            for (i=0;i<items.length;i=i+1) {
		            	newOrder.push(items[i].id);
		            }
		            newOrder = newOrder.reverse();
		            newOrder = newOrder.join(',');
					$('newOrder').value = newOrder;
					return true;
				}
				
				//////////////////////////////////////////////////////////////////////////////
				// custom drag and drop implementation
				//////////////////////////////////////////////////////////////////////////////
				
				YAHOO.example.DDList = function(id, sGroup, config) {
				 
					YAHOO.example.DDList.superclass.constructor.call(this, id, sGroup, config);
				 
					this.logger = this.logger || YAHOO;
					var el = this.getDragEl();
					Dom.setStyle(el, 'opacity', 0.67); // The proxy is slightly transparent
				 
					this.goingUp = false;
					this.lastY = 0;
				};
				
				function initDDlist() {
					
					DDM = YAHOO.util.DragDropMgr;
 
					YAHOO.extend(YAHOO.example.DDList, YAHOO.util.DDProxy, {
					 
						startDrag: function(x, y) {
							this.logger.log(this.id + ' startDrag');
					 
							// make the proxy look like the source element
							var dragEl = this.getDragEl();
							var clickEl = this.getEl();
							Dom.setStyle(clickEl, 'visibility', 'hidden');
					 
							dragEl.innerHTML = clickEl.innerHTML;
					 
							Dom.setStyle(dragEl, 'color', Dom.getStyle(clickEl, 'color'));
							Dom.setStyle(dragEl, 'backgroundColor', Dom.getStyle(clickEl, 'backgroundColor'));
							Dom.setStyle(dragEl, 'border', '2px solid gray');
						},
					 
						endDrag: function(e) {
					 
							var srcEl = this.getEl();
							var proxy = this.getDragEl();
					 
							// Show the proxy element and animate it to the src element's location
							Dom.setStyle(proxy, 'visibility', '');
							var a = new YAHOO.util.Motion( 
								proxy, { 
									points: { 
										to: Dom.getXY(srcEl)
									}
								}, 
								0.2, 
								YAHOO.util.Easing.easeOut 
							)
							var proxyid = proxy.id;
							var thisid = this.id;
					 
							// Hide the proxy and show the source element when finished with the animation
							a.onComplete.subscribe(function() {
									Dom.setStyle(proxyid, 'visibility', 'hidden');
									Dom.setStyle(thisid, 'visibility', '');
								});
							a.animate();
						},
					 
						onDragDrop: function(e, id) {
					 
							// If there is one drop interaction, the li was dropped either on the list,
							// or it was dropped on the current location of the source element.
							if (DDM.interactionInfo.drop.length === 1) {
					 
								// The position of the cursor at the time of the drop (YAHOO.util.Point)
								var pt = DDM.interactionInfo.point; 
					 
								// The region occupied by the source element at the time of the drop
								var region = DDM.interactionInfo.sourceRegion; 
					 
								// Check to see if we are over the source element's location.  We will
								// append to the bottom of the list once we are sure it was a drop in
								// the negative space (the area of the list without any list items)
								if (!region.intersect(pt)) {
									var destEl = Dom.get(id);
									var destDD = DDM.getDDById(id);
									destEl.appendChild(this.getEl());
									destDD.isEmpty = false;
									DDM.refreshCache();
								}
					 
							}
						},
					 
						onDrag: function(e) {
					 
							// Keep track of the direction of the drag for use during onDragOver
							var y = Event.getPageY(e);
					 
							if (y < this.lastY) {
								this.goingUp = true;
							} else if (y > this.lastY) {
								this.goingUp = false;
							}
					 
							this.lastY = y;
						},
					 
						onDragOver: function(e, id) {
						
							var srcEl = this.getEl();
							var destEl = Dom.get(id);
					 
							// We are only concerned with list items, we ignore the dragover
							// notifications for the list.
							if (destEl.nodeName.toLowerCase() == 'li') {
								var orig_p = srcEl.parentNode;
								var p = destEl.parentNode;
					 
								if (this.goingUp) {
									p.insertBefore(srcEl, destEl); // insert above
								} else {
									p.insertBefore(srcEl, destEl.nextSibling); // insert below
								}
					 
								DDM.refreshCache();
							}
						}
					});
				
				}
				 
		
				//]]>
				</script>
		";
		
		return $output;
	
	}
	
	function reorderAlbumsDo() {
		if  (!$this->allow_editarchivesettings) return $this->permissionDenied(); 	
		$cd = $this->current_directory_details;
		$albumId = $this->current_directory;
		if (!is_numeric($albumId)) $this->fatalError("[imagearchive] Invalid input!");		
		$tf = $this->table_files; $td = $this->table_dirs;
		if ($cd['deletereason'] != ""){
			$this->fatalError('<p class="warning">Albumet «'.$cd['caption'].'» er slettet.</p>');
		}
		if ($albumId <= 0){
			$this->fatalError('<p class="warning">Du kan ikke endre albumrekkefølgen for dette albumet.</p>');		
		}
		if (!isset($_POST['newOrder'])) $this->fatalError('Invalid input');		
		
		$newOrder = explode(',',$_POST['newOrder']);
		for ($i = 0; $i < count($newOrder); $i++) {
			$pos = $i+1;
			$albumId = intval(substr($newOrder[$i],5));
			$res = $this->query("UPDATE $td SET position=$pos WHERE id=$albumId");
		}

		$this->addToActivityLog("endre rekkefølgen for underalbum av 
			<a href=\"".$this->generateURL('')."\">".$cd['caption']."</a>",false,"minor");

		$this->redirect($this->generateURL(''),
			"Den nye rekkefølgen er lagret!");
		
		//Array ( [newOrder] => album195,album193,album162,album117,album53,album4,album3,album2,album144,album72,album73,album103,album102,album90,album80,album82,album81,album61,album57,album56 )


	}
	
	function showAlbumThumbs(){
		$output = "";
				
		$albumId = $this->current_directory;
		$cd = $this->current_directory_details;
		
		$pathArray = $this->generatePath($albumId); // Needed for breadcrumb

		$toolBarItems = array(
			'addAlbum' => '
				<a href="'.$this->generateURL('action=addAlbum').'" 
					class="icn" style="background-image:url(/images/icns/folder_add.png);">
					  '.$this->label_addarchive.'
				</a>',
			'reorderAlbums' => '
				<a href="'.$this->generateURL('action=reorderAlbums').'" 
					class="icn" style="background-image:url(/images/icns/folder_table.png);">
					  '.$this->label_reorder.'
				</a>',
			'deleteAlbum' => '
				<a href="'.$this->generateURL('action=deleteAlbum').'" 
					class="icn" style="background-image:url(/images/icns/folder_delete.png);">
					  '.$this->label_deletearchive.'
				</a>',
			'addPhotos' => '
				<a href="'.$this->generateURL('action=uploadInfo').'" 
					class="icn" style="background-image:url(/images/icns/add.png);" 
					title="'.$this->label_addimagestoarchive_hint.'">
					  '.$this->label_addimagestoarchive.'
				</a>',
			'moveAlbum' => '
				<a href="'.$this->generateURL('action=moveAlbum').'" 
					class="icn" style="background-image:url(/images/icns/folder_go.png);">
					  '.$this->label_movearchive.'
				</a>',
			'albumSettings' => ' 
				<a href="'.$this->generateURL("action=albumSettings").'"
					class="icn" style="background-image:url(/images/icns/folder_wrench.png);">
					'.$this->label_editarchivesettings.'
				</a>'
		);		
		
		// First check if the album has been deleted
		if ($cd['deletereason'] != ""){
			$delinfo = explode("|",$cd['deletereason']);
			if (intval($delinfo[0] > 0)) $deluser = call_user_func($this->make_memberlink, $delinfo[0]);
			else $deluser = "(ukjent)";
			if (isset($delinfo[1]) && (intval($delinfo[1]) > 0)) $deldate = date("d.m.y",$delinfo[1]);
            else $deldate = "(ukjent)";

            $containerLink = "";
            if ($albumId != 1){
                if (!$cd['parentdir'] == 0){
                    $this->parentdir_details = $this->getDirInfo($cd['parentdir']);
                    $url_parentdir = $this->generateCoolURL($this->parentdir_details['directory']);
                    $containerLink = 'Albumet var en del av album-mappen «<a href="'.$url_parentdir.'">'.$this->parentdir_details['caption'].'</a>»';
                }
            }
            $output .= $this->pageNotFound('
				Albumet «'.$cd['caption'].'» ble slettet av '.$deluser.' den '.$deldate.'.'.
                (empty($delinfo[2]) ? '' : "<br />Årsak: ".stripslashes($delinfo[2])).'
                <br /><br />'.$containerLink);
			return $output;
		}

		// If we are at the imagearchive root, we will display a slightly different toolset and a welcome message
		if ($albumId == 1){
			$output .= "<!--<h2>".$cd['caption']."</h2>-->";

			$res = $this->query("SELECT photo_count FROM $this->table_counts WHERE id=1");
			$row = $res->fetch_array();			
			$output .= '<p>'.str_replace('%imagecount%', $row[0], $this->label_welcome).'</p>';
			
			if ($this->allow_addimages) {
				$output .= '
				<div style="margin:3px;padding:5px;border:1px solid #ddd;">
					'.$toolBarItems['addAlbum'].'
					'.$toolBarItems['reorderAlbums'].'					
				</div>';
			}

		} else {
			$desc = stripslashes($cd['description']);
			$desc = str_replace("\r\n","\n",$desc);
			$desc = str_replace("\r","\n",$desc);
			$desc = str_replace("\n","<br />\n",$desc);
			if (!empty($desc)) $desc = "<p>$desc</p>";

			$this->document_title = $pathArray[1];
			$r1a = array();	$r2a = array();
			$r1a[] = "%caption%";				$r2a[] = $pathArray[0];
			$r1a[] = "%actions%";				$r2a[] = ($this->allow_addimages ? '		<div style="margin:3px;padding:1px;border:1px solid #ddd;">
					<div style="padding:4px;">
						%addarchive%
						%addimages%
						%deletearchive%
						%editarchivesettings%
						%movearchive%
						%reorder%
					</div>
				</div>':'');
			$r1a[] = "%addarchive%";			$r2a[] = ($this->allow_addimages ? $toolBarItems['addAlbum'] : '');
			
			// If the album has no sub-albums:
			if ($this->subdirs->num_rows == 0){
				$r1a[] = "%editarchivesettings%";	$r2a[] = '';
				$r1a[] = "%movearchive%";			$r2a[] = '';
				$r1a[] = '%deletearchive%';			$r2a[]  = (($this->allow_deleteownarchives || $this->allow_deleteothersarchives) ? $toolBarItems['deleteAlbum'] : '');
				$r1a[] = '%addimages%';				$r2a[]  = ($this->allow_addimages ? $toolBarItems['addPhotos'] : '');
				$r1a[] = "%reorder%";				$r2a[] = '';
			} else {
				$r1a[] = "%editarchivesettings%";	$r2a[] = ($this->allow_editarchivesettings ? $toolBarItems['albumSettings'] : '');
				$r1a[] = "%movearchive%";			$r2a[] = ($this->allow_editarchivesettings ? $toolBarItems['moveAlbum'] : '');
				$r1a[] = '%deletearchive%';			$r2a[] = '';
				$r1a[] = '%addimages%';				$r2a[] = '';
				$r1a[] = "%reorder%";				$r2a[] = ($this->allow_editarchivesettings ? $toolBarItems['reorderAlbums'] : '');
			}
			$r1a[] = "%path%";					$r2a[] = $pathArray[1];
			$r1a[] = "%description%";			$r2a[] = $desc;
			$output .= str_replace($r1a, $r2a, $this->dirthumbs_header);
			if ($cd['event_id'] != "0"){
				// calendarlookup 
			}
			if ((!$this->subdirs->num_rows == 0) && empty($cd['thumbnail'])){
				$output .= "<p class='notice'>".$this->missingthumbnail_caption."</p>";
			}
		}

		// Is the album empty?
		if ($this->subdirs->num_rows == 0){
			$output .= "<p class='notice'>".$this->archive_empty_caption."</p>";
		}
		
		// Make album thumbs
		$output .= "<div style='margin-left:30px;'>\n";
		while ($archive = $this->subdirs->fetch_assoc()){

			$rs = $this->query("SELECT photo_count FROM $this->table_counts WHERE id=".$archive['id']);
			if ($rs->num_rows == 0) {
				$photoCount = 0;
			} else {
				$ro = $rs->fetch_row();
				$photoCount = $ro[0];
			}
			
			$overlay = '<span class="photoCountBox" id="photoCountBox'.$archive['id'].'">'.$photoCount.'</span>';
			$thumbnail = $this->getUrlTo($archive['directory'],'thumbnail');
			$thumbnail = (empty($archive['thumbnail']) ? $this->image_dir.$this->no_thumbnail_found : $thumbnail);
			$r1a = array();	$r2a = array();
			$r1a[] = "%dirlink%";	$r2a[] = $this->generateCoolURL($archive['directory']);
			$r1a[] = "%imgsrc%";	$r2a[] = $thumbnail;
			$r1a[] = "%caption%";	$r2a[] = $archive['caption'];
			$r1a[] = "%id%";		$r2a[] = $archive['id'];
			$r1a[] = "%overlay%";	$r2a[] = $overlay;
			
			$output .= str_replace($r1a, $r2a, $this->dirthumb_template);
		}
		$output .= "</div>\n";
		$output .= "<div style='clear:both;'></div>";

		// Make link to containing album		
		if ($albumId != 1){
			if (!$cd['parentdir'] == 0){
				$this->parentdir_details = $this->getDirInfo($cd['parentdir']);
				$url_parentdir = $this->generateCoolURL($this->parentdir_details['directory']);
				$output .= '
					<p align="center">
						<a href="'.$url_parentdir.'">'.$this->label_backto.' '.$this->parentdir_details['caption'].'</a>
					</p>
				';
			}
		}
		return $output;
	}

	/*******************************************************************************************
		 Print image thumbs                                                               
		 **************************************************************************************/

	function showPhotoThumbs($showHidden = false){
		$output = "";

		$tf = $this->table_files; $tc = $this->table_comments;
		$cd = $this->current_directory_details;
		$albumId = intval($this->current_directory);

		$res = $this->query(
			"SELECT count(*) FROM $this->table_files 
			WHERE directory=$albumId AND deletereason=''
			".(!$showHidden ? "AND visible=1" : "")
		);
		$row = $res->fetch_row();
		$total_images = $row[0];
		$total_pages = ceil($total_images/$this->images_per_page);
		$pathArray = $this->generatePath($albumId);
		$eventTitle = $pathArray[1];
		
		$url_addarchive = $this->generateURL("addarchive");
		$url_editarchivesettings = $this->generateURL("action=albumSettings");
		$url_organize = $this->generateURL('action=organizeImages');
		$url_addimages = $this->generateURL('action=uploadInfo');
		$url_slideshow = $this->generateURL("slideshow");
		$url_zip = $this->generateURL('action=downloadAlbum');
		$url_deletearchive = $this->generateURL('action=deleteAlbum');
		$url_makethumbs = $this->generateURL('action=makeThumbs');
		$url_movearchive = $this->generateURL('action=moveAlbum');
		$desc = stripslashes($cd['description']);
		$desc = str_replace("\r\n","\n",$desc);
		$desc = str_replace("\r","\n",$desc);
		$desc = str_replace("\n","<br />\n",$desc);
		if (!empty($desc)) $desc = "<p style='text-align:center; font-style: italic; background: #ffffff; padding: 4px; border: 1px solid #999999;'>$desc</p>";

		$imageLocation = "";
		if ($this->enable_calendar_functions){
			if ($cd['event_id'] != "0"){

				$cal = new calendar_basic(); 
				call_user_func($this->prepare_classinstance, $cal, $cd['cal_id']);
				$cal->initialize_base();
				$calEvent = $cal->getEventDetails($cd['event_id']);
				$topic = $calEvent['caption'];	
				$dt_start = date("j.n.y",$calEvent['startdate']);
				$dt_end = date("j.n.y",$calEvent['enddate']);
				$datestamp = ($dt_start == $dt_end) ? $dt_start : "$dt_start - $dt_end";
				$url = $cal->getLinkToEvent($calEvent['id']);
				$imageLocation = 'Tatt på '.$url.' ('.$datestamp.'). ';

			}
		}	
		if ($total_images > $this->images_per_page) 
			$numImagesText = "$total_images bilder, hvorav $this->images_per_page vises per side. ";
		else
			$numImagesText = "$total_images bilder. ";

		$this->document_title = $eventTitle;

		$r1a = array();	$r2a = array();
		$r1a[] = "%caption%";				$r2a[]  = $pathArray[0];
		if ($cd['deletereason'] == ""){
			$r1a[] = '%slideshow%';				$r2a[]  = (($total_images != 0) ? '<a href="'.$url_slideshow.'" class="icn" style="background-image:url(/images/icns/control_play.png);">'.$this->label_slideshow.'</a>' : '');
			$r1a[] = '%zip%';					$r2a[]  = (($total_images != 0) ? ' <a href="'.$url_zip.'" class="icn" style="background-image:url(/images/icns/compress.png);">'.$this->label_zip.'</a>'.$this->label_zip_note : '');
			$r1a[] = '%addarchive%';			$r2a[]  = (($this->allow_addimages && ($total_images == 0)) ? '<a href="'.$url_addarchive.'" class="icn" style="background-image:url(/images/icns/add.png);">'.$this->label_addarchive.'</a> ' : '');
			$r1a[] = '%addimagestoarchive%';	$r2a[]  = ($this->allow_addimages ? ' <a href="'.$url_addimages.'" class="icn" style="background-image:url(/images/icns/add.png);" title="'.$this->label_addimagestoarchive_hint.'">'.$this->label_addimagestoarchive.'</a>' : '');
			$r1a[] = '%editarchivesettings%';	$r2a[]  = ($this->allow_editarchivesettings ? ' <a href="'.$url_editarchivesettings.'" class="icn" style="background-image:url(/images/icns/folder_wrench.png);">'.$this->label_editarchivesettings.'</a>' : '');
			$r1a[] = '%organize%';				$r2a[]  = ($this->allow_organizeimages ? ' <a href="'.$url_organize.'" class="icn" style="background-image:url(/images/icns/images.png);">'.$this->label_organizeimages.'</a>' : '');
			$r1a[] = '%deletearchive%';			$r2a[]  = (($this->allow_deleteownarchives || $this->allow_deleteothersarchives) ? ' <a href="'.$url_deletearchive.'" class="icn" style="background-image:url(/images/icns/folder_delete.png);">'.$this->label_deletearchive.'</a>' : '');
			$r1a[] = '%makethumbs%';			$r2a[]  = (($this->allow_addimages) ? ' <a href="'.$url_makethumbs.'" class="icn" style="background-image:url(/images/icns/folder_wrench.png);">'.$this->label_makethumbs.'</a>' : '');
			$r1a[] = '%movearchive%';			$r2a[]  = ($this->allow_editarchivesettings ? '<a href="'.$url_movearchive.'" class="icn" style="background-image:url(/images/icns/folder_go.png);">'.$this->label_movearchive.'</a>' : '');
		} else {
			$r1a[] = '%zip%';					$r2a[]  = '';
			$r1a[] = '%slideshow%';				$r2a[]  = '';
			$r1a[] = '%addarchive%';			$r2a[]  = '';
			$r1a[] = '%addimagestoarchive%';	$r2a[]  = '';
			$r1a[] = '%editarchivesettings%';	$r2a[]  = '';
			$r1a[] = '%deletearchive%';			$r2a[]  = '';
			$r1a[] = '%makethumbs%';			$r2a[]  = '';
			$r1a[] = '%movearchive%';			$r2a[]  = '';
			$r1a[] = '%organize%';				$r2a[]  = '';
		}
		$r1a[] = "%path%";					$r2a[]  = $eventTitle;
		$r1a[] = "%description%";			$r2a[]  = $desc;
		$r1a[] = "%num_images%";			$r2a[]  = $numImagesText;
		$r1a[] = "%image_location%";		$r2a[]  = $imageLocation;
		$output .= str_replace($r1a, $r2a, $this->imagethumbs_header);

		
		// Check if any images are missing timestamps:
		$res = $this->query(
			"SELECT count(*) FROM $this->table_files 
				WHERE directory=$albumId AND visible=1 AND deletereason=''
				AND datetime_original=0"
		);
		$row = $res->fetch_row();
		$missingTimestamps = $row[0];
		
		$orderBy = "$tf.datetime_original";
		if ($missingTimestamps > 0){
			$orderBy = "$tf.id";
		}
		if ($total_images == 0){
			$output .= "<p class='notice'>".$this->archive_empty_caption."</p>";
		} else if (empty($cd['thumbnail'])){
			$output .= "<p class='notice'>".$this->missingthumbnail_caption."</p>";
		}
		
		$use_title = $cd['showtitles'];

		$start_at = ($this->page_no-1)*$this->images_per_page;
		
		$res = $this->query(
			"SELECT 
				$tf.id, $tf.filename, $tf.thumb_width, $tf.thumb_height, $tf.datetime_original, $tf.datetime_source,
				COUNT($tc.parent_id) as commentcount				
				".($use_title ? ", $tf.title" : "")."
			FROM 
				$tf
			LEFT JOIN $tc 
				ON $tf.id=$tc.parent_id AND $tc.page_id=$this->page_id
			WHERE 
				$tf.directory=$albumId
				AND
				$tf.visible=1
				AND
				$tf.deletereason=''
			GROUP BY 
				$tf.id
			ORDER BY 
				$orderBy
			LIMIT 
				$start_at,$this->images_per_page"
		);
		//print "DEBUG: order by: $orderBy";
		$output .= $this->str_before_imagethumbs;
		$i = 0;
		$showWeekDays = ($cd['showweekdays'] == '1');
		$oldwday = "";
		while ($row = $res->fetch_assoc()){
			$unixTimestamp = strtotime($row['datetime_original']);
			$wday = strftime('%A %e. %B',$unixTimestamp);
			if ($showWeekDays && $wday != $oldwday) {
				$output .="<div style='clear:both;'><!-- --></div><h4 style='margin:10px 0px;padding:0px;' class='whiteInfoBox'><span style='padding:3px 10px 3px 10px; display:block;'>".ucfirst($wday).":</span></h4>";
				$oldwday = $wday;
				$i = 0;
			}
			$commentcount = intval($row['commentcount']);
			$commentindicator = $commentcount ?  '<span style="background:url(/images/icns/comment.png); position:absolute; z-index:2;width:16px;height:16px;margin:6px;"></span>' : '';
			$paddingtop = (140-$row['thumb_height'])/2;
			$paddingleft = (140-$row['thumb_width'])/2;
			
			$imageToolTip = ($row['datetime_source'] == 'None') ? '' : strftime("%e. %B %Y, kl. %H:%M",$unixTimestamp).", \n";
			$imageToolTip .= $commentcount.(($commentcount==1)?" kommentar":" kommentarer");
			
			$r1a = array();	$r2a = array();
			$r1a[] = "%imagelink%";		$r2a[]  = $this->generateCoolURL($cd['directory'].$row['id']);
			$r1a[] = "%timestamp%";		$r2a[]  = date("d. M Y, H:i:s",$unixTimestamp);
			$r1a[] = "%img_title%";		$r2a[]  = $imageToolTip;
			$r1a[] = "%imgsrc%";		$r2a[]  = $this->getUrlTo($cd['directory'],$row['filename'],'small');
			$r1a[] = "%title%";			$r2a[]  = ($use_title ? stripslashes($row['title']) : "");
			$r1a[] = "%paddingtop%";	$r2a[]  = $paddingtop;
			$r1a[] = "%paddingleft%";	$r2a[]  = $paddingleft;
			$r1a[] = "%width%";			$r2a[]  = $row['thumb_width'];
			$r1a[] = "%height%";		$r2a[]  = $row['thumb_height'];
			$r1a[] = "%halfheight%";	$r2a[]  = $row['thumb_height']/2;
			$r1a[] = "%clearboth%";		$r2a[]  = ($i%$this->columns == 0) ? "<div style='clear:both; height: 1px;'><!-- --></div>" : "";
			$r1a[] = "%comment_indicator%"; $r2a[] = $commentindicator;
			$outp = str_replace($r1a, $r2a, $this->template_imagethumb_frame);
			$output .= $outp;
			$i++;
		}
		$output .= $this->str_after_imagethumbs;
		
		if ($cd['parentdir'] == 0){
			$url_parentdir  = $this->generateURL("");
			$caption_parentdir = $this->label_imagearchive;
		} else {
			$this->parentdir_details = $this->getDirInfo($cd['parentdir']);
			$url_parentdir = $this->generateCoolURL($this->parentdir_details['directory']);
			$caption_parentdir = $this->parentdir_details['caption'];
		}

		$prev_page = $this->page_no-1;
		$next_page = $this->page_no+1;
		
		$url_firstpage = $this->generateCoolURL($cd['directory'],"archive_page=0");
		$url_prevpage  = $this->generateCoolURL($cd['directory'],"archive_page=$prev_page");
		$url_nextpage  = $this->generateCoolURL($cd['directory'],"archive_page=$next_page");
		$url_lastpage  = $this->generateCoolURL($cd['directory'],"archive_page=".($total_pages-1));

		$r1a	= array();	$r2a = array();
		$r1a[0] = "%x%";	$r2a[0]  = $this->page_no;
		$r1a[1] = "%y%";	$r2a[1]  = $total_pages;
		$pagexofy = str_replace($r1a, $r2a, $this->label_pagexofy);
		
		$r1a	= array();	$r2a = array();
		$r1a[0] = '%prevpagelink%';		$r2a[0]  = ($this->page_no > 1 ? 
			'<a href="'.$url_prevpage.'" class="icn" style="background-image:url(/images/icns/arrow_left.png);">'.$this->label_prevpage.'</a>' : 
			'');
		$r1a[1] = '%nextpagelink%';		$r2a[1]  = ($this->page_no < $total_pages ? 
			'<a href="'.$url_nextpage.'" class="icn" style="background-image:url(/images/icns/arrow_right.png);">'.$this->label_nextpage.'</a>' : 
			'');
		$r1a[2] = '%gouplink%';			$r2a[2]  = (($this->current_directory != 1) ? 
			'<a href="'.$url_parentdir.'" class="icn" style="background-image:url(/images/icns/arrow_up.png);">'.$this->label_backto.' '.$caption_parentdir.'</a>' : '');
		$output .= str_replace($r1a, $r2a, $this->smallthumbsfooter_template);
				
		/*
		if ($missingTimestamps){
			$output .= "<p class='notice'>".str_replace("%count%",$missingTimestamps,$this->missingtimestamps_caption)."</p>";
		}
		*/		
		
		return $output;
		
	}

	/*******************************************************************************************
		 Print single image                                                               
		 **************************************************************************************/
	
	function showPhoto(){
		global $memberdb;
		$output = "";
		$id = intval($this->current_image);
		if ($id <= 0) $this->fatalError("[imagearchive] Invalid image id!");
	
		$pathArray = $this->generatePath($this->current_directory);
		
		$r1a = array();	$r2a = array();
		$r1a[0] = "%caption%";	$r2a[0]  = $pathArray[0];
		$r1a[1] = "%path%";		$r2a[1]  = $pathArray[1];
		
		$output .= str_replace($r1a, $r2a, $this->imagesfrom_template);
		
		// Check if any images are missing timestamps:
		$res = $this->query("SELECT count(*) FROM $this->table_files 
			WHERE directory=$this->current_directory AND visible=1 AND deletereason='' AND datetime_original=0");
		$row = $res->fetch_row();
		$missingTimestamps = ($row[0] > 0);
		
		$orderBy = $missingTimestamps? 'id' : 'datetime_original';
		
		// Find number of images:
		$res = $this->query("SELECT COUNT(*) as rowcount FROM $this->table_files 
			WHERE directory=$this->current_directory AND visible=1 AND deletereason=''");
		$row = $res->fetch_assoc();
		$total_images = intval($row['rowcount']);

		// Find first image:
		$res = $this->query("SELECT id FROM $this->table_files 
			WHERE directory=$this->current_directory AND visible=1 AND deletereason='' 
			ORDER BY $orderBy LIMIT 1");
		$first_image = ($res->num_rows == 1) ? implode("",$res->fetch_row()) : -1;

		// Find last image:
		$res = $this->query("SELECT id FROM $this->table_files 
			WHERE directory=$this->current_directory AND visible=1 AND deletereason='' 
			ORDER BY $orderBy DESC LIMIT 1");
		$last_image = ($res->num_rows == 1) ? implode("",$res->fetch_row()) : -1;

		$use_title = true; //$this->current_directory_details['showtitles'];

		$res = $this->query("SELECT filename,datetime_original,uploadedby as uploader, deletereason,
			source,title,$orderBy as ordervalue
			FROM $this->table_files WHERE id=$id");
		$row = $res->fetch_assoc();
		$ordervalue = $row['ordervalue'];
		$unixTimestamp = ($row['datetime_original'] == '0000-00-00 00:00') ? 0 : strtotime($row['datetime_original']);

		if ($use_title) $tittel = $tittelHtml = stripslashes($row['title']);
		if  ($this->allow_organizeimages){
			if (empty($tittel)) $tittelHtml = "<em style='font-weight:normal; color: #888;'>Ingen tittel</em>";
			$tittelHtml = "<div onclick=\"editTitle();\" style='cursor:pointer;'><input type='hidden' id='bildetittel' value=\"".htmlspecialchars($tittel)."\" />$tittelHtml</div>";
		} else {
			$tittelHtml = "<div>$tittelHtml</div>";		
		}
		$uploader = $row['uploader'];
		$res = $this->query("SELECT id FROM $this->table_files 
			WHERE 
				$orderBy < '$ordervalue' 
				AND visible=1 
				AND deletereason=''
				AND directory=$this->current_directory
			ORDER BY $orderBy DESC LIMIT 1"
		);
		//print "<p>DEBUG: Order by: $orderBy, orderValue: $ordervalue</p>";
		$prev_image = ($res->num_rows == 1) ? implode("",$res->fetch_row()) : -1;

		$res = $this->query(
			"SELECT $this->table_files_field_id as id FROM $this->table_files 
			WHERE 
				$orderBy > '$ordervalue' 
				AND $this->table_files_field_visible=1 
				AND $this->table_files_field_deletereason=''
				AND $this->table_files_field_directory='$this->current_directory'
			ORDER BY $orderBy LIMIT 1"
		);
		$next_image = ($res->num_rows == 1) ? implode("",$res->fetch_row()) : -1;
		
		//print "<p>Debug: Prev: $prev_image, This: $ordervalue, Next: $next_image</p>";

		$res = $this->query(
			"SELECT count(*) FROM $this->table_files 
			WHERE 
				$orderBy <= '$ordervalue' 
				AND $this->table_files_field_visible=1 
				AND $this->table_files_field_deletereason=''
				AND $this->table_files_field_directory='$this->current_directory'"
		);
		$image_no = ($res->num_rows == 1) ? implode("",$res->fetch_row()) : -1;

		$image_filename = $row['filename'];
		
		$largethumb = $this->getUrlTo($this->current_directory_details['directory'],$image_filename,'medium');
		$largethumb_server = $this->getPathTo($this->current_directory_details['directory'],$image_filename,'medium');

		$orgimage = $this->getUrlTo($this->current_directory_details['directory'],$image_filename);
		$orgimage_server = $this->getPathTo($this->current_directory_details['directory'],$image_filename);
 		 		
 		$imageDamaged = false;
 		if (!file_exists($largethumb_server)) $imageDamaged = true;
 		if (!file_exists($orgimage_server)) $imageDamaged = true;

 		if (!$imageDamaged && empty($row['deletereason'])){
			list($thumb_width, $thumb_height, $type, $attr) = getimagesize($largethumb_server);
			list($image_width, $image_height, $type, $attr) = getimagesize($orgimage_server);
		}
		//print_r(exif_read_data($orgimage_server));
		//print_r(getimagesize($orgimage_server));		
		//print $orgimage_server;
		
		if (!empty($this->login_identifier)){
			$abegin = "<a href='$orgimage' target='BildeVindu'>";
			$aend = "</a>";
		} else {
			$abegin = "";
			$aend = "";
		}
		switch ($row['source']) {
			case 'scan':
				$source = 'scannet';
				break;
			case 'camera':
			default:
				$source = 'tatt';
				break;				
		
		}
		
		if ($this->enable_calendar_functions && ($this->current_directory_details['event_id'] != "0")){

			$cal = new calendar_basic(); 
			call_user_func($this->prepare_classinstance, $cal, $this->current_directory_details['cal_id']);
			$cal->initialize_base();
			$calEvent = $cal->getEventDetails($this->current_directory_details['event_id']);
			$topic = $calEvent['caption'];
			$cal_id = $calEvent['id'];
			$year = date("Y",$calEvent['startdate']);
			$url = "/".$cal->fullslug."/$year/?show_event=".$cal_id;
			$topic = '<a href="'.$url.'">'.$topic.'</a>';

		}

		if ($unixTimestamp == 0){
			if (!$this->enable_calendar_functions || ($this->current_directory_details['event_id'] == "0")){
				$datecalstr = "Bildet mangler informasjon om når det er tatt.";
			} else {
				$datecalstr = "Bildet er fra $topic. Nøyaktig tidspunkt mangler i bildefilen. ";
			}
		} else {
			$ds = getdate($unixTimestamp);
			$datestr = $this->weekDays[$ds['wday']]." ".$ds['mday'].". ".$this->months[$ds['mon']-1]." ".$ds['year'].", kl. ".$this->twoDigits($ds['hours']).":".$this->twoDigits($ds['minutes']);
			
			if (!$this->enable_calendar_functions || ($this->current_directory_details['event_id'] == "0")){
				$datecalstr = "Bildet er $source $datestr.";
			} else {

				$dt_start = date("j.n.y",$calEvent['startdate']);
				$dt_end = date("j.n.y",$calEvent['enddate']);
				$datestamp = ($dt_start == $dt_end) ? $dt_start : "$dt_start - $dt_end";
				if ($source == 'tatt') {
					$datecalstr = "Bildet er tatt $datestr, på $topic.";				
				} else {
					$datecalstr = "Bildet er scannet og stammer fra $topic.";
				}

			}
		}


		if (!empty($this->lookup_member)){
			$uploader = call_user_func($this->lookup_member,$row['uploader']);
			$uploader_info = call_user_func($this->make_memberlink,$row['uploader'],$uploader->firstname);
		} else {
			$uploader_info = "(ukjent)";		
		}
		if (!empty($row['deletereason'])){
			$delinfo = explode("|",$row['deletereason']);
			$deluser = call_user_func($this->make_memberlink, $delinfo[0]);
			$upl = call_user_func($this->make_memberlink, $row['uploader']);
			$del_text = "Dette bildet ble slettet av $deluser den ".strftime("%e. %B %Y",$delinfo[1]).".
				Bildet ble opprinnelig lastet opp av $upl.";
			if (!empty($delinfo[2])){
				$del_text .= "<br />Årsak for sletting: <strong>".stripslashes($delinfo[2])."</strong>";
            }
            $del_text .= '<br /><br />Bildet var en del av albumet «<a href="'.$this->generateCoolUrl($this->current_directory_details['directory']).'">'.
                $this->current_directory_details['caption'].'</a>»';
            $output .= $this->pageNotFound($del_text);
		} else {
		
			if ($this->useCoolUrls){
				$url_firstimage  = $this->generateCoolURL($this->current_directory_details['directory']."$first_image");
				$url_previmage   = $this->generateCoolURL($this->current_directory_details['directory']."$prev_image");
				$url_nextimage   = $this->generateCoolURL($this->current_directory_details['directory']."$next_image");
				$url_lastimage   = $this->generateCoolURL($this->current_directory_details['directory']."$last_image");
				$url_parentdir   = $this->generateCoolURL($this->current_directory_details['directory']);
			} else {
				$url_firstimage  = $this->generateURL("img=$first_image");
				$url_previmage   = $this->generateURL("img=$prev_image");
				$url_nextimage   = $this->generateURL("img=$next_image");
				$url_lastimage   = $this->generateURL("img=$last_image");
				$url_parentdir   = $this->generateURL("dir=$this->current_directory");
			}
			$allow_delete = ($this->allow_deleteothersimages || ($this->allow_deleteownimages && ($row['uploader'] == $this->login_identifier)));		
			
			
			$res = $this->query("SELECT user, x, y, width, height
				FROM $this->table_tags WHERE imgtab='".$this->page_id."' AND img='$id'");
			$tags = array();
			$ppl = '';
			while ($row = $res->fetch_assoc()) {
				$m = call_user_func($this->lookup_member,$row['user']);
				if ($m != false) {
					$allow = false;
					if (!empty($this->login_identifier)) {
						if ($this->allow_untagself && ($m->ident == $this->login_identifier)) $allow = true;
						if ($this->allow_untagothers) $allow = true;
					}
					$ppl .= 'photoTagger0.addPerson('.intval($m->ident).',"'.$m->fullname.'","'.$m->url.'",'.intval($row['x']).','.intval($row['y']).','.intval($row['width']).','.intval($row['height']).");\n";
					$tags[] = $row;
				}
			}

			$options = "";
			$options .= "<p id='tagged_users'></p>";
			if (!empty($this->login_identifier)) {
			
				$allMembers = array();
				foreach ($memberdb->getAllMembers() as $m) {
					$mfull = $m->firstname." ".$m->middlename." ".$m->lastname;
					$mfull = str_replace('  ',' ',$mfull);
					$allMembers[] = $mfull;
				}
				$options .= '
				<script type="text/javascript">
				//<![CDATA[
				
				var allMembers = ["'.implode('","',$allMembers).'"];
				var photoTagger0 = new BG18.photoTagger(%tagging_enabled%,"%tag_url%","%untag_url%","%me_fullname%",allMembers);
				'.$ppl.'
				
				function onYuiLoaderComplete() {
					photoTagger0.init();
				}

				loader.require("datasource","autocomplete","json","connection");
				loader.insert();
			
				//]]>
				</script>';
			
			}
			
			if ($imageDamaged) {
				$side_margin = 0;
			} else {
				$side_margin = (480-$thumb_width)/2;
				if ($side_margin < 0) $side_margin = 0;
			}
			
			//$userSelectBox = $memberdb->generateMemberSelectBox("tag_user");
			
			if (!empty($this->login_identifier)) {
				$m = call_user_func($this->lookup_member,$this->login_identifier);
				$mfull = $m->firstname." ".$m->middlename." ".$m->lastname;
				$me = str_replace('  ',' ',$mfull);
			} else {
				$me = "";
			}
			$members = array();
			foreach ($memberdb->members as $m) { 
				if (count($m->memberof) > 0) {
					$mfull = $m->firstname." ".$m->middlename." ".$m->lastname;
					$mfull = str_replace('  ',' ',$mfull);
					$members[] = $mfull;
				}
			}
			if ($this->allow_tagothers) {
				$tag_form = '
					<form method="post" onsubmit="photoTagger0.tagMember(); return false;">
						Hvem er dette?
						<div id="tagged_user_container" style="width:175px;padding-bottom:1.8em;">
							<input id="tagged_user" type="text" name="tagged_user" value="" autocomplete="off" style="border:1px solid #ccc; background:#eee; font-size:10px;" />
							<div id="tagged_user_ac"></div>
						</div>

						<input id="btn_tagme" type="button" value="Meg" style="font-size:10px; margin:1px; border:1px solid #ccc;" /> 
						<input id="btn_tagsave" type="submit" value="Lagre" style="font-size:10px; margin:1px; border:1px solid #ccc;float:right; font-weight:bold;" />
						<input id="btn_tagcancel" type="button" value="Avbryt" style="font-size:10px; margin:1px; border:1px solid #ccc; float:right;" /> 
					</form>
				';
			} else if ($this->allow_tagself) {
				$tag_form = "
					<form method='post' onsubmit='photoTagger0.tagMember(); return false;'>
						<p align='center'>
							<input id='tagged_user' type='hidden' name='tagged_user' value='' />
							<input id='btn_tagcancel' type='button' value='Avbryt' style='font-size:80%; border:1px solid #ccc;' /> 
							<input id='btn_tagme' type='button' value='Dette er meg' style='font-size:80%; border:1px solid #ccc;' /> 
						</p>
					</form>
				";			
			} else {
				$tag_form = "";
			}

			$this->document_title = $pathArray[1].' (bilde '.$image_no.' av '.$total_images.')';

			$image_actions = $this->printImageActions($id);
			
			if ($imageDamaged) {
				$r1a[] = "%main_image%";		$r2a[] = $this->pageNotFound("Bildefilen ble ikke funnet, eller den er skadet!");
				$r1a[] = "%infotext%";			$r2a[] = "%dateinfo% Det ble lagt inn i bildearkivet av %uploader%";
			} else {
				$r1a[] = "%main_image%";		$r2a[] = $this->template_mainImage;
				$r1a[] = "%infotext%";			$r2a[] = "%dateinfo% Det ble lagt inn i bildearkivet av %uploader% og er tilgjengelig i %fullwidth% x %fullheight% px for innloggede brukere.";
				$r1a[] = "%width%";				$r2a[] = $thumb_width;
				$r1a[] = "%height%";			$r2a[] = $thumb_height;
				$r1a[] = "%fullwidth%";			$r2a[] = $image_width;
				$r1a[] = "%fullheight%";		$r2a[] = $image_height;
			}
			
			$r1a[] = "%options%";			$r2a[] = $options;
			$r1a[] = "%dateinfo%";			$r2a[] = $datecalstr;
			$r1a[] = "%uploader%";			$r2a[] = $uploader_info;
			$r1a[] = "%linkbegin%";			$r2a[] = $abegin;
			$r1a[] = "%linkend%";			$r2a[] = $aend;
			$r1a[] = "%src%";				$r2a[] = $largethumb;
			$r1a[] = "%titlehtml%";			$r2a[] = ($use_title ? $tittelHtml : "");
			$r1a[] = "%title%";				$r2a[] = ($use_title ? $tittel : "");
			$r1a[] = "%imageno%";			$r2a[] = $image_no;
			$r1a[] = "%imagecount%";		$r2a[] = $total_images;
			$r1a[] = "%image_actions%";		$r2a[] = $image_actions;
			$r1a[] = "%firstimage%";		$r2a[] = ($id != $first_image ? '<a href="'.$url_firstimage.'">'.$this->label_firstimage.'</a>' : $this->label_firstimage);
			$r1a[] = '%previousimage%';		$r2a[] = ($id != $first_image ? '<a href="'.$url_previmage.'">'.$this->label_previmage.'</a>' : $this->label_previmage);
			$r1a[] = '%nextimage%';			$r2a[] = ($id != $last_image ? '<a href="'.$url_nextimage.'">'.$this->label_nextimage.'</a>' : $this->label_nextimage);
			$r1a[] = '%lastimage%';			$r2a[] = ($id != $last_image ? '<a href="'.$url_lastimage.'">'.$this->label_lastimage.'</a>' : $this->label_lastimage);
			$r1a[] = '%backtothumbs%';		$r2a[] = '<a href="'.$url_parentdir.'">$this->label_backto '.$this->current_directory_details["caption"].'</a>';
			$r1a[] = "%sidemargin%";		$r2a[] = $side_margin;
			$r1a[] = "%imagedir%";			$r2a[] = $this->image_dir;
			$r1a[] = "%tag_user%";			$r2a[] = $tag_form;
			$r1a[] = "%all_members%";		$r2a[] = "\"".implode("\",\"",$members)."\"";
			$r1a[] = "%tag_url%";			$r2a[] = $this->generateURL('action=ajaxTagUser');
			$r1a[] = "%untag_url%";			$r2a[] = $this->generateURL('action=ajaxUntagUser');
			$r1a[] = "%title_url%";			$r2a[] = $this->generateURL('action=ajaxSavePhotoTitle');
			$r1a[] = "%me_fullname%";		$r2a[] = $me;
			$r1a[] = "%tagging_enabled%";	$r2a[] = $this->allow_tagself ? "true":"false";
			$r1a[] = "%useAutoComplete%";	$r2a[] = $this->allow_tagothers ? "true":"false";
			$r1a[] = "%download_image%";	$r2a[] = empty($this->login_identifier) ? "" : "<a href=\"$orgimage\" target=\"_blank\" class=\"icn\" style=\"background-image:url(/images/icns/picture_save.png);\">Vis bilde i full oppløsning</a> (til utskrift)";
			$output .= str_replace($r1a, $r2a, $this->midresimage_template);
		}
		
		if ($this->enable_comments){
			$this->comment_desc = 2;
			$output .= $this->printComments($this->current_image);
		}
		
		return $output;
	}

	/*******************************************************************************************
		 FETCH ARCHIVE                                                               
		 **************************************************************************************/
	
	function downloadAlbum() {
		
		if (empty($this->login_identifier)) {
			$this->sendContentType();
			print $this->permissionDenied();
			exit();
		}
		$albumId = intval($this->current_directory);
		$cd = $this->current_directory_details;
		$path = $this->root_path.$this->temp_dir.'zip';
		
		if (!file_exists($path)){
			if(!mkdir($path,0755,true)){
				$this->fatalError("Can't create temp directory \"$path\".");
			}
		}

		$filename = "_imagearchive_".$this->login_identifier.".zip";
		$path = "$path/$filename";

		if (file_exists($path)){
			unlink($path);
		}
		
		touch($path);
		/*
		$old = umask(0);
		chmod($path, 0666);
		umask($old);
		*/

		$zip = new ZipArchive();
		$zipErr = $zip->open($path,ZIPARCHIVE::OVERWRITE);
		if ($zipErr !== true) {
			switch ($zipErr) {
				case ZIPARCHIVE::ER_OPEN:
					$this->fatalError("$filename: ER_OPEN");
				case ZIPARCHIVE::ER_READ:
					$this->fatalError("$filename: ER_READ");
				case ZIPARCHIVE::ER_SEEK:
					$this->fatalError("$filename: ER_SEEK");
				case ZIPARCHIVE::ER_MEMORY:
					$this->fatalError("$filename: ER_MEMORY");
				default:
					$this->fatalError("cannot open ".$filename.": $zipErr");
			}
		}
		
		$res = $this->query(
			"SELECT id,filename FROM $this->table_files WHERE directory=$albumId 
				AND visible=1 AND deletereason=''"
		);
		while ($row = $res->fetch_assoc()){
			$src = $this->getPathTo($cd['directory'],$row['filename'],'original');
			$zip->addFile($src,"/bilder/".$row['filename']);
		}	
		//echo "numfiles: " . $zip->numFiles . "\n";
		//echo "status:" . $zip->status . "\n";
		$zip->close();
		
		/*
		$old = umask(0);
		chmod($path, 0644);
		umask($old);
		*/

		header("location: ".ROOT_DIR.$this->root_dir.$this->temp_dir."zip/".$filename);
		exit();
	}

	/*******************************************************************************************
		 TAGGING                                                               
		 **************************************************************************************/

	function ajaxTagUser() {
		global $memberdb;
		
		if (!$this->isLoggedIn()) {
			echo json_encode(array(
				'people' => array(),
				'error' => 'Du er ikke logget inn'
			));
			exit();		
		}
		
		$params = json_decode($_POST['json']);		
		$error = '0';
		$imgtab = $this->page_id;
		$img = $this->current_image;

		$this->sendContentType();
		$user = 0;
		if (!isset($params->fullname)) $error = 'Ingen person angitt!';
		else {
			$pname = trim($params->fullname);
			foreach ($memberdb->getAllMembers() as $m) {
				$mfull = $m->firstname." ".$m->middlename." ".$m->lastname;
				$mfull = str_replace('  ',' ',$mfull);
				if ($mfull == $pname){ 
					$user = $m->ident; 
					break; 
				}
			}
			if ($user == 0){ 
				$error = 'Fant ikke noen person med navn "'.strip_tags($pname).'"!';
			} else if (empty($this->login_identifier)) { 
				$error = "Du er ikke logget inn!"; 
			} else if (!$this->allow_tagothers && $user != $this->login_identifier) {
				$error = "Beklager, du har ikke tilgang til å utføre denne operasjonen.";
			} else if (!$this->allow_tagself) {
				$error = "Beklager, du har ikke tilgang til å utføre denne operasjonen.";			
			}
			$x = $params->frame->x;
			$y = $params->frame->y;
			$w = $params->frame->width;
			$h = $params->frame->height;
			if (!is_numeric($x)) { $error = 'Ugyldig x-verdi'; }
			if (!is_numeric($y)) { $error = 'Ugyldig y-verdi'; }
			if (!is_numeric($w)) { $error = 'Ugyldig bredde'; }
			if (!is_numeric($h)) { $error = 'Ugyldig høyde'; }
		}

		if ($error == '0') {
			$taggedby = $this->login_identifier;
			$res = $this->query("SELECT $this->table_tags_field_id as id FROM $this->table_tags WHERE
				$this->table_tags_field_img=$img AND $this->table_tags_field_imgtab=$imgtab
				AND $this->table_tags_field_user=$user"
			);
			if ($res->num_rows > 0) {
				$row = $res->fetch_assoc();
				$id = $row['id'];
				$this->query("UPDATE $this->table_tags 
					SET x=$x, y=$y, width=$w, height=$h, taggedby=$taggedby
					WHERE id=$id"
				);
				if ($this->affected_rows() != 1) {
					$error = 'En ukjent feil oppstod ved oppdatering av denne personens posisjon!';
				}				
			} else {
				$this->query("INSERT INTO $this->table_tags 
						(img,imgtab,user,x,y,width,height,taggedby,date_tagged)
					VALUES ($img,$imgtab,$user,$x,$y,$w,$h,$taggedby,NOW())"
				);
				if ($this->affected_rows() != 1) {
					$error = 'En ukjent feil oppstod ved innlegging av denne personen:'.
					$this->getLastMySqlError();
				}				
			}
		}
		
		$res = $this->query("SELECT user,x,y,width,height
			FROM $this->table_tags WHERE imgtab='".$this->page_id."' AND img='$img'");
		$ppl = array();
		while ($row = $res->fetch_assoc()) {
			$row['user'] = call_user_func($this->lookup_member,$row['user']);
			$ppl[] = array(
				'id' => intval($row['user']->ident),
				'name' => $row['user']->fullname,
				'url' => $row['user']->url,
				'x' => intval($row['x']),
				'y' => intval($row['y']),
				'width' => intval($row['width']),
				'height' => intval($row['height'])
			);
		}

		echo json_encode(array(
			'people' => $ppl,
			'error' => $error
		));
		exit();
	}
	
	function ajaxUntagUser() {

		if (!$this->isLoggedIn()) {
			echo json_encode(array(
				'people' => array(),
				'error' => 'Du er ikke logget inn'
			));
			exit();		
		}	

		$params = json_decode($_POST['json']);
		$error = '0';

		$this->sendContentType();
		global $memberdb;
		$user = intval($params->tag_uid);
		if (!$memberdb->isUser($user)){ 
			$error = "Fant ikke personen!"; 
		} else if (empty($this->login_identifier)) { 
			$error = "Du er ikke logget inn!"; 
		} else if (!$this->allow_tagothers && $user != $this->login_identifier) {
			$error = "Beklager, du har ikke tilgang til å utføre denne operasjonen.";
		} else if (!$this->allow_tagself) {
			$error = "Beklager, du har ikke tilgang til å utføre denne operasjonen.";			
		}
		
		$imgtab = $this->page_id;
		$img = $this->current_image;

		if ($error == '0') {
			$res = $this->query("DELETE FROM $this->table_tags 
				WHERE imgtab=$imgtab AND img=$img AND user=$user"
			);
			if ($this->affected_rows() != 1) {
				$error = 'Det ser ut som personen allerede er fjernet.';
			}
		}
		
		$res = $this->query("SELECT user,x,y,width,height
			FROM $this->table_tags WHERE imgtab='".$this->page_id."' AND img='$img'");
		$ppl = array();
		while ($row = $res->fetch_assoc()) {
			$row['user'] = call_user_func($this->lookup_member,$row['user']);
			$ppl[] = array(
				'id' => intval($row['user']->ident),
				'name' => $row['user']->fullname,
				'url' => $row['user']->url,
				'x' => intval($row['x']),
				'y' => intval($row['y']),
				'width' => intval($row['width']),
				'height' => intval($row['height'])
			);
		}
		
		echo json_encode(array(
			'people' => $ppl,
			'error' => $error
		));
		exit();

	}

	/*******************************************************************************************
		 Slideshow                                                               
		 **************************************************************************************/
	
	function slideShow($dir_id) {
		
		$output = "";
		
		$orderBy = "datetime_original";
		$res = $this->query(
			"SELECT id,filename,datetime_original,title,source
			FROM $this->table_files 
			WHERE directory=$dir_id AND visible=1 AND deletereason=''
			ORDER BY $orderBy
			"
		);
		$i = 0;
		
		if ($this->enable_calendar_functions && ($this->current_directory_details['event_id'] != "0")){

			$cal = new calendar_basic(); 
			call_user_func($this->prepare_classinstance, $cal, $this->current_directory_details['cal_id']);
			$cal->initialize_base();
			$calEvent = $cal->getEventDetails($this->current_directory_details['event_id']);
			$topic = $calEvent['caption'];
			$cal_id = $calEvent['id'];
			$year = date("Y",$calEvent['startdate']);
			$url = "/".$cal->fullslug."/$year/?show_event=".$cal_id;
			$topic = str_replace("\"","&quot;",$topic);
			$topic = '<a href="'.$url.'">'.$topic.'</a>';

		}

		$dir = $this->root_dir.$this->mid_res_dir.$this->current_directory_details['directory'];
		$abs_dir = $this->root_path.$this->mid_res_dir.$this->current_directory_details['directory'];
		
		$jsa = array();
		while ($row = $res->fetch_assoc()){
			$timestamp = ($row['datetime_original'] == '0000-00-00 00:00') ? 0 : strtotime($row['datetime_original']);
		
			switch ($row['source']) {
				case 'scan':
					$source = 'scannet';
					break;
				case 'camera':
				default:
					$source = 'tatt';
					break;				
			
			}
			if ($timestamp == 0){
				if (!$this->enable_calendar_functions || ($this->current_directory_details['event_id'] == "0")){
					$datecalstr = "Bildet mangler informasjon om når det er tatt.";
				} else {
					$datecalstr = "Bildet er fra $topic. Nøyaktig tidspunkt mangler i bildefilen. ";
				}
			} else {
				$ds = getdate($timestamp);
				$datestr = $this->weekDays[$ds['wday']]." ".$ds['mday'].". ".$this->months[$ds['mon']-1]." ".$ds['year'].", kl. ".$this->twoDigits($ds['hours']).":".$this->twoDigits($ds['minutes']);
				if (!$this->enable_calendar_functions || ($this->current_directory_details['event_id'] == "0")){
					$datecalstr = "Bildet er $source $datestr.";
				} else {
	
					$dt_start = date("j.n.y",$calEvent['startdate']);
					$dt_end = date("j.n.y",$calEvent['enddate']);
					$datestamp = ($dt_start == $dt_end) ? $dt_start : "$dt_start - $dt_end";
					if ($row['source'] == 'camera') {
						$datecalstr = "Bildet ble $source $datestr, på $topic.";				
					} else {
						$datecalstr = "Bildet er scannet og stammer fra $topic.";
					}

				}
			}
			
			$dt = date("d. M Y, H:i:s",$timestamp);
			$filename = $row['filename'];
			$abs_src = "$abs_dir/$filename";
			$src = "$dir/$filename";
			list($width, $height, $type, $attr) = getimagesize($abs_src);
			$title = trim(stripslashes($row['title']));
			if (!empty($title)) $title = "« $title »<br />";
			$title .= "$datecalstr";
			$jsa[] = "new Array('$filename', $width, $height, '".str_replace("'","\'",$title)."')";
		}
		
		$imgdir = $this->image_dir.'slideshow';
		$jsdir = '/jscript/slideshow';
		
		$path = $this->generatePath($dir_id);
		$r1a = array();	$r2a = array();
		$r1a[] = "%caption%";	$r2a[]  = $path[0];
		$r1a[] = "%path%";		$r2a[]  = $path[1];
		$output .= str_replace($r1a, $r2a, $this->imagesfrom_template);

		$output .= '		
			<!-- slideshow -->
		
			<script type="text/javascript" src="'.$jsdir.'/behaviour.js"></script>
			<script type="text/javascript" src="'.$jsdir.'/soundmanager.js"></script>
		
			<script type="text/javascript">
			//<![CDATA[
				
				// get current photo id from URL
				var thisURL = document.location.href;
				var splitURL = thisURL.split("#");
				var photoId = splitURL[1] - 1;
				
				// if no photoId supplied then set default
				var photoId = (!photoId)? 0 : photoId;
				
				// CSS border size x 2
				var borderSize = 10;
				
				// Photo directory for this gallery
				var photoDir = "'.$dir.'/";
				
				// Define each photo\'s name, height, width, and caption
				var photoArray = new Array(
					// Source, Width, Height, Caption
					'.implode(", \n\t\t\t\t\t",$jsa).'
				);
			//]]>
			</script>

			<script type="text/javascript" src="'.$jsdir.'/slideshow.js"></script>
		
			<div id="OuterContainer">
				<div id="ImageContainer">
					<img id="Photo" src="'.$imgdir.'/c.gif" alt="Photo: Couloir" />
					<div id="LinkContainer">
						<a href="#" id="PrevLink" title="Previous Photo"><span>Previous</span></a>
						<a href="#" id="NextLink" title="Next Photo"><span>Next</span></a>
					</div>
					<div id="Loading"><img src="'.$imgdir.'/loading_animated2.gif" width="48" height="47" alt="Loading..." /></div>
				</div>
			</div>
			
			<div id="CaptionContainer">
				<p><span id="Counter">&nbsp;</span> <span id="Caption">&nbsp;</span></p>
			</div>
			
			<script type="text/javascript">
			//<![CDATA[
				YAHOO.util.Event.onDOMReady(function() {
					Behaviour.register(myrules);
				});
			//]]>
			</script>

		';
		
		return $output;
	
	}
	
	function printImageActions($id) {
	
		$use_title = $this->current_directory_details['showtitles'];

		$url_deleteimage = $this->generateURL('action=deletePhoto');
		$url_moveimage   = $this->generateURL('action=movePhoto');
		$url_edittitle   = $this->generateURL('action=editTitle');
		$url_hideimage   = $this->generateCoolURL('action=hidePhoto');
		$url_rotateleft   = $this->generateURL('action=rotateLeft');
		$url_rotateright   = $this->generateURL('action=rotateRight');
		
		$id = intval($id);
		$res = $this->query("SELECT uploadedby FROM $this->table_files WHERE $id=$id"); 
		$row = $res->fetch_assoc();
		$uploader = intval($row['uploadedby']);
		
		$allow_delete = ($this->allow_deleteothersimages || ($this->allow_deleteownimages && ($uploader==$this->login_identifier)));	
			
		$output = "";
		if ($this->allow_editarchivesettings || $allow_delete) {
			if ($this->use_hide) $output .= '<a href="'.$url_hideimage.'" class="icn" style="background-image:url(/images/icns/picture_empty.png);">'.$this->label_hideimage.'</a>';
		 	$output .= ' <a href="'.$url_moveimage.'" class="icn" style="background-image:url(/images/icns/picture_go.png);">'.$this->label_moveimage.'</a>';
			$output .= ' <a href="'.$url_deleteimage.'" class="icn" style="background-image:url(/images/icns/picture_delete.png);">'.$this->label_deleteimage.'</a>';
			$output .= ' <a href="'.$url_rotateleft.'" class="icn" style="background-image:url(/images/icns/arrow_rotate_anticlockwise.png);">'.$this->label_rotateleft.'</a>';			
			$output .= ' <a href="'.$url_rotateright.'" class="icn" style="background-image:url(/images/icns/arrow_rotate_clockwise.png);">'.$this->label_rotateright.'</a>';
		}
		return $output;
	}

	/* Deprecated ? */
	function hideImage($id){
		if (!is_numeric($id)) $this->fatalError("[imagearchive] Invalid input!");
		if  (!$this->allow_editarchivesettings) return $this->permissionDenied(); 	
		$res = $this->query(
			"SELECT
				$this->table_files_field_directory as dir
			FROM 
				$this->table_files
			WHERE 
				$this->table_files_field_id='$id'"
		);
		if ($res->num_rows != 1) $this->fatalError("[imagearchive] img not found!");
		$row = $res->fetch_assoc(); $dir = $row['dir'];
		$res = $this->query(
			"UPDATE 
				$this->table_files
			SET 
				$this->table_files_field_visible=0
			WHERE 
				$this->table_files_field_id='$id'"
		);
		header("Location: ".$this->generateURL("dir=$dir"));
		exit;
	}

	/* Deprecated ? */
	function unhideImage($id){
		if (!is_numeric($id)) $this->fatalError("[imagearchive] Invalid input!");
		if  (!$this->allow_editarchivesettings) return $this->permissionDenied(); 	
		$res = $this->query(
			"SELECT
				$this->table_files_field_directory as dir
			FROM 
				$this->table_files
			WHERE 
				$this->table_files_field_id='$id'"
		);
		if ($res->num_rows != 1) $this->fatalError("[imagearchive] img not found!");
		$row = $res->fetch_assoc(); $dir = $row['dir'];
		$res = $this->query(
			"UPDATE 
				$this->table_files
			SET 
				$this->table_files_field_visible=1
			WHERE 
				$this->table_files_field_id='$id'"
		);
		header("Location: ".$this->generateURL("dir=$dir"));
		exit;
	}
	
	/*******************************************************************************************
		 Add image(s) to archive                                                               
		 **************************************************************************************/

	function enoughMemory($fileName){
		$MB = pow(1024,2);		// number of bytes in 1M
		$K64 = pow(2,16);		// number of bytes in 64K
		$TWEAKFACTOR = 1.2;		// Or whatever works for you
		$imageInfo = getimagesize($fileName);
		//print_r($imageInfo);
		$memoryNeeded = round( 
			($imageInfo[0] * $imageInfo[0] * $imageInfo['bits'] * $imageInfo['channels'] / 8 + $K64) * $TWEAKFACTOR 
		);
		$memoryHave = memory_get_usage();
		$memoryLimitMB = (integer) ini_get('memory_limit');
		$memoryLimit = $memoryLimitMB * $MB;
		$memoryAvaible = $memoryLimit - $memoryHave;
		if ( function_exists('memory_get_usage')  && $memoryHave + $memoryNeeded > $memoryLimit ) {
			$this->notSoFatalError("Ikke nok minne! Brukt: ".round($memoryHave/$MB,2)." MB. Trenger: ".round($memoryNeeded/$MB,2)." MB. Grense: $memoryLimitMB MB.");
			return false;
		} else {
			// print "Minne som trengs for dette bildet: ".round($memoryNeeded/$MB,2)." MB. Minne tilgjengelig: ".round($memoryAvaible/$MB,2)." MB.<br />\n";
			return true;
		}
	}

	function dirlist($dirName) { 
		$d = dir($dirName);
		$files = array();
		while($entry = $d->read()) { 
			if ($entry != "." && $entry != "..") { 
				if (!is_dir($dirName.$entry)) { 
					$fileInfo = pathinfo($dirName.$entry);
					$fileext = strtolower($fileInfo["extension"]);
					if (in_array($fileext,array("jpg","png","gif"))){
						array_push($files,$entry);
					}
				} 
			}	 
		} 
		$d->close();
		return $files;
	} 
	
	function uploadInfo(){

		$path = $this->generatePath($this->current_directory);
		return '
			<h3>Opplasting av nye bilder</h3>
			<p>
				Er dette første gangen du laster opp bilder?<br />
				Da bør du lese <a href="/hjelp/laste-opp-bilder-til-bildearkivet/">guide til 
				bildeopplasting</a> først.
			</p>
			<h3>Retningslinjer</h3>
			<p>
				Det finnes ikke noe godkjenningssystem. Du kan derfor laste opp hva som helst hvor 
				som helst. Bruk derfor sunn fornuft og tenk på følgende:
			</p>
			<ul>
				<li><strong>Ikke legg opp bilder du tror andre vil finne støtende!</strong></li>
				<li>Husk at det er kjedelig å bla gjennom for mange bilder. Ikke legg opp flere 
					bilder av nesten samme motiv, med mindre dette gir mening.</li>
				<li>Er bildene på «høykant»? Rett de opp før du laster de opp, så slipper vi vridd 
					nakke :)</li>
			</ul>
			<h3>Kan du laste opp bilder?</h3>
			<p>
				For å laste opp bilder må du ha Java installert på din datamaskin. Noen nettlesere 
				vil gi deg automatisk melding om dette. I andre tilfeller må du selv velge å laste
				dette ned og installere det. Java kan lastes ned gratis fra 
				<a href="http://www.java.com/">www.java.com</a>.
			</p>
			<p style="margin: 3px; padding: 3px; border: 1px solid #000000">
				<b>Test: </b>
				<script type="text/javascript">
				//<![CDATA[
					document.write(navigator.javaEnabled() ? \'<img src="/images/icns/accept.png" /> Java er installert.\' : \'Java er deaktivert eller ikke installert!\');
				//]]>
				</script>
			</p>
		
			<h3>Lest alt sammen?</h3>
			<p>
				<a href="'.$this->generateURL('action=uploadImages').'" class="icn" style="background-image:url(/images/icns/arrow_right.png);">Fortsett til opplastingssiden</a>
			</p>

		';

	}

	function uploadImages(){

		$albumId = intval($this->current_directory);
		if ($albumId <= 0) return "Invalid album";
		
		// ===== Check permission: =====
			if (!$this->allow_addimages) return $this->permissionDenied();

		// ===== Check input: =====
			if (!is_numeric($albumId)) $this->fatalError("[imagearchive] Invalid directory!");
		
		// ===== Generate output: =====
			$output = "";
			
			$path = $this->generatePath($albumId);
			$output .= "<h3>Laste opp bilder til «".$path[1]."»</h3>\n";
			
			$jup = new javaupload();
			$jup->jupload_dir = ROOT_DIR.$this->java_path_to_jupload;
			$jup->actionurl = 'http://'.$_SERVER['SERVER_NAME'].ROOT_DIR.$this->generateURL(array("'action=uploadImagesDo","userid=$this->login_identifier"));
			$jup->completeurl = 'http://'.$_SERVER['SERVER_NAME'].ROOT_DIR.$this->generateURL('action=processUploadedImages');
			$jup->errorurl = 'http://'.$_SERVER['SERVER_NAME'].ROOT_DIR.$this->generateURL('action=uploadError');		
			$output .= $jup->printUploadForm();
			
			$output .= "
				<h3>Hjelp</h3>
				<ul>
					<li style='margin-bottom: 8px;'>
						<b>Det er ingenting ovenfor!</b><br />
						Vent først litt. Programmet trenger litt tid for å starte. Dersom ingenting skjer, 
						sjekk at du har Java innstallert <a href='".$this->generateURL('action=uploadInfo')."'>her</a>.
					</li>
					<li style='margin-bottom: 8px;'>
						<b>Feilmeldingen \"server disconnected: connection reset by peer: socket write error\" kommer</b><br />
						Prøv å ikke laste opp så mange bilder på en gang. Maks 20 om gangen er en grei regel.
					</li>
					<li style='margin-bottom: 8px;'>
						<b>Det står java.lang.NoClassDefFoundError: (osv..)</b><br />
						Hvis det står noe sånt ovenfor, har du mest sannsynlig en for gammel versjon av JAVA innstallert.
						Les mer om hvordan du innstallerer nyeste versjon av JAVA <a href='".$this->generateURL('action=uploadInfo')."'>her</a>.
					</li>
					<li style='margin-bottom: 8px;'>
						<b>Filene jeg velger kommer ikke opp i listen!</b><br />
						Dette skjer av og til og vil som regel ordne seg hvis du oppdaterer siden (trykk F5 eller Ctrl-R) og prøver på nytt.
					</li>
				</ul>
				<h3>Tips</h3>
				<ul>
					<li style='margin-bottom: 8px;'>
						Du velger flere filer ved å trykke på en fil og holde inne shift mens du trykker på en annen.
					</li>
				</ul>
				";
			return $output;
	}

	function uploadError(){
		$this->notSoFatalError("[imagearchive] Det oppstod en feil under opplastingen! Om du ikke forstår bæret av hvorfor dette skjedde, kan du gjerne gi beskjed på forumet, så kan kanskje noen ta en titt i serverloggene og klø seg litt i hodet.");
	}
	
	function uploadImagesDo(){
		//$this->addToErrorLog("processUploadedImages($userid)");
		
		$uploaderId = intval($_GET['userid']);
		if ($uploaderId <= 0) return "error";

		switch(php_sapi_name()){
			case 'cgi':
			case 'cgi-fcgi': 
				$sz_htstatus = 'Status: ';
				break;
			default: 
				$sz_htstatus = 'HTTP/1.0: ';
				break;
		}
		
		// ===== Find temp dir: =====
			$temp_path = $this->root_path.$this->temp_dir.'/user'.$uploaderId.'/';
		
			if (!file_exists($temp_path)){
				if(!mkdir($temp_path,0755,true)){
					$this->addToErrorLog("Can't create temp directory \"$temp_path\".");
					$sz_message='406 Error Occured';
					header($sz_htstatus.$sz_message);
					exit();
				}
			}

		// ===== Move uploaded files to temp dir: =====
			$i = 1;
			foreach ($_FILES as $tagname => $fileobject){
				$tempname = $fileobject['tmp_name'];
				$realname = $fileobject['name'];
				
				$fileExt = strtolower($this->getFileExt($realname));
				if ($fileExt == "jpeg") $fileExt = "jpg";
				if (!in_array($fileExt,array("jpg","png","gif"))) $fileExt = "jpg"; // make a guess... :)
	
				$target = "$temp_path/image".rand(1,9999).".".$fileExt;
				while (file_exists($target)){
					$target = "$temp_path/image".rand(1,9999).".".$fileExt;
				}
				if (is_uploaded_file($tempname)){
					if (move_uploaded_file($tempname, $target)) {
						//$this->addToActivityLog("Bildearkiv: Lastet opp $i av ".count($_FILES).": '$realname' as '$target'");
					} else {
						$this->addToErrorLog("Bildearkiv: Kunne ikke flytte fil '$realname' til temp-mappa!");
						$sz_message='406 Error Occured';
						header($sz_htstatus.$sz_message);
						exit;
					}
				} else {
					$this->addToErrorLog("Bildearkiv: Filen '$realname' ble ikke lastet opp! Kanskje den overstiger filesize limit eller no?");
					$sz_message='406 Error Occured';
					header($sz_htstatus.$sz_message);
					exit;
				}
				$i++;
			}

		// ===== Give feedback to JUpload applet: =====
			if (count($_FILES) == 0){
				$sz_message='406 Error Occured';
				$this->addToErrorLog("Bildearkiv: Ingen filer ble lastet opp av JUpload");
			} else {
				$sz_message='200 OK';
			}
			header($sz_htstatus.$sz_message);
			exit();
	}
	
	function processUploadedImages(){

		// ===== Check permission: =====
			if (!$this->allow_addimages) return $this->permissionDenied();
			$uploaderId = $this->login_identifier;

		// ===== Determine locations: =====
			$albumId = intval($this->current_directory);
			if ($albumId <= 0) return "Invalid album";
			$this->current_directory_details = $this->getDirInfo($albumId);
			$dirPath = $this->current_directory_details['directory'];
			$orig_path = $this->getPathTo($dirPath);
			$mid_path = $this->getPathTo($dirPath,'','medium');
			$lo_path = $this->getPathTo($dirPath,'','small');
			$temp_path = $this->root_path.$this->temp_dir.'/user'.$uploaderId.'/';
		
		// ===== Move files to correct location and add entries to our database: =====
			$files = $this->dirlist($temp_path);		
			$newFiles = array();
			
			foreach ($files as $tempName){
				$res = $this->query("INSERT INTO $this->table_files 
					(directory,visible,uploadedby)
					VALUES ($albumId,1,$uploaderId)"
				);
				$imgId = $this->insert_id();
				$fileExt = $this->getFileExt($tempName);
				$fileName = "i".$this->fiveDigits($imgId).".".$fileExt;
				
				$from = $temp_path.$tempName;
				$to_full = $orig_path.$fileName;
					
				if (!rename($from,$to_full)){
					$this->addToErrorLog("[imagearchive] Kunne ikke flytte '$from' til '$to_full'. Sjekk rettigheter...");
					$this->fatalError("[imagearchive] Kunne ikke flytte $fileName til destinasjonsmappen. Sjekk rettigheter!");
				}
	
				array_push($newFiles,$fileName);
				
				$exifDateSource = 'None';
				$exifDateUnix = 0;
				$exifDateStr = '0000-00-00 00:00';
				$exif = exif_read_data($to_full, 'IFD0');				
				if ($exif === false) {
					// Absolutely no EXIF here...
					$this->addToErrorLog("Fant ikke timestamp for $fileName (0)");
				} else {
					$exif = exif_read_data($to_full,0,true);
					if (isset($exif['EXIF']['DateTimeOriginal'])) {
						$exifDateSource = 'EXIF.DateTimeOriginal';
						$exifDateUnix = strtotime($exif['EXIF']['DateTimeOriginal']); // Format: YYYY:MM:DD HH:mm:SS
						$exifDateStr = strftime('%F %T',$exifDateUnix);
					} else if (isset($exif['EXIF']['DateTimeDigitized'])) {
						$exifDateSource = 'EXIF.DateTimeDigitized';
						$exifDateUnix = strtotime($exif['EXIF']['DateTimeDigitized']); // Format: YYYY:MM:DD HH:mm:SS
						$exifDateStr = strftime('%F %T',$exifDateUnix);
					} else if (isset($exif['EXIF']['DateTime'])) {
						$exifDateSource = 'EXIF.DateTime';
						$exifDateUnix = strtotime($exif['EXIF']['DateTime']); // Format: YYYY:MM:DD HH:mm:SS
						$exifDateStr = strftime('%F %T',$exifDateUnix);
					} else if (isset($exif['IFD0']['DateTime'])) {
						$exifDateSource = 'IFD0.DateTime';
						$exifDateUnix = strtotime($exif['IFD0']['DateTime']); // Format: YYYY:MM:DD HH:mm:SS
						$exifDateStr = strftime('%F %T',$exifDateUnix);
					} else {
						// No timestamp found...
						$this->addToErrorLog("Fant ikke timestamp for $fileName (1)");
					}
				}			
				
				$fs = filesize($to_full);
				$res = $this->query("UPDATE $this->table_files SET 
						filename=\"$fileName\",
						filesize=$fs,
						datetime_added=NOW(),
						datetime_original=\"$exifDateStr\",
						datetime_source=\"$exifDateSource\"
					WHERE id='$imgId'"
				);
			}

		// ===== Redirect: =====	
			$this->redirect($this->generateURL('action=makeThumbs'));
	}
	
	function makeThumbs() {

		// ===== Check permission: =====
		if (!$this->allow_addimages) return $this->permissionDenied();

		$output = "";
		
		$albumId = $this->current_directory;

		// ===== Check permission: =====
			if (!$this->allow_addimages) return $this->permissionDenied();

		$output = "";
		$res = $this->query("SELECT id FROM $this->table_files WHERE $this->table_files_field_thumbwidth='0' AND $this->table_files_field_directory='$albumId'");
		$imglist = array();
		while ($row = $res->fetch_assoc()) {
			$imglist[] = $row['id'];
		}
		if (count($imglist) == 0) {
			$output .= "
				<h3>Ingen behandling nødvendig</h3>
				<p>
					<a href=\"".$this->generateUrl("")."\">Vis bilder</a>
				</p>			
			";
			return $output;
		} else if (count($imglist) == 1) {
			$imglist_js = "var bildeliste = new Array(); bildeliste[0] = '".$imglist[0]."';";		
		} else {
			$imglist_imp = implode(",",$imglist);
			$imglist_js = "var bildeliste = new Array($imglist_imp); ";
		}
		
		$output .= '
			<h3><span id="processtitle">Velg kilde</span></h3>
			<div style="margin-left: 0px; text-align: center;">
			<p>
			    <span id="statustext" style="font-weight:bold;">
						'.count($imglist).' bilde(r) ble lastet opp, 
						men de er ikke synlige i bildearkivet enda.
				</span><br />
				<span id="progressbar">&nbsp;</span>
			</p>
			</div>
			<div style="margin-left: 180px; height: 150px; text-align: center;">
				<div id="imageprocessor" style="margin-left: 0px; width: 170px;">
				
				<form method="post">
					<p>
						Hvor kommer bildene fra?
					</p>
					<select name="source" id="source">
						<option value="camera" selected="selected">Kamera</option>
						<option value="scan">Scanner</option>
					</select>
					<p>
						<input type="button" value="Fortsett" onclick="startProcessing();" id="continueBtn" disabled="disabled" />
					</p>
				</form>
				
				</div>
			</div>
			<div style="clear:both;"></div>
			<div id="continuelink" style="text-align:center;visibility:hidden; font-weight: bold;">
				<a href="'.$this->generateURL("").'">Vis bildene</a>
			</div>
			<script type="text/javascript">
			//<![CDATA[

				// ============================ 1) Load YUI lib ===================================

				loader.require("connection","json");
				loader.insert();
					
				function onYuiLoaderComplete() {
					$("continueBtn").disabled = "";
				}
			
				'.$imglist_js.'
				var currentImage = 0; // Hurray, globals!! ;)

				function errorMsg(txt) {
					$("progressbar").innerHTML = "";
					$("processtitle").innerHTML = "Det oppstod en feil!";
					$("imageprocessor").innerHTML = txt;
				}
				
				// ============================ 1) Set imagesource ================================

				function startProcessing() {		
					var url = "'.$this->generateURL('action=ajaxSetImageSource').'";
					var theSource = $("source").value;

					$("progressbar").innerHTML = "<img src=\"'.$this->image_dir.'progressbar1.gif\" alt=\"Progressbar\" width=\"100\" height=\"9\" />";
					$("processtitle").innerHTML = "Vennligst vent mens bildene behandles...";
					$("imageprocessor").innerHTML = "Vennligst vent...";
					$("statustext").innerHTML = "Lagrer innstillinger...";

					YAHOO.util.Connect.asyncRequest("POST", url, {
						success: sourceSet,
						failure: sourceSetFailed
					}, "source="+theSource);					
				}
				
				function sourceSet(o) {
					try {
						var json = YAHOO.lang.JSON.parse(o.responseText);
						if (json.error == "0") {
							if (bildeliste.length <= 0) {
								$("imageprocessor").innerHTML = "";
								ferdig();
							} else {
								behandleBilde();
							}
						} else {
							errorMsg(json.error);
						}
					} catch (e) {
						errorMsg(o.responseText);
					}
				}
				
				function sourceSetFailed(o) {
					errorMsg("Det oppstod en feil");					
				}	

				// ============================ 2) Make thumbnails ================================

				function behandleBilde() {					
					var id = bildeliste[currentImage];
					currentImage++;
					$("statustext").innerHTML = "Behandler bilde "+currentImage+" av "+bildeliste.length+"...";
					var url = "'.$this->generateURL('action=ajaxMakeThumb').'";
					YAHOO.util.Connect.asyncRequest("POST", url, {
						success: thumbCreated,
						failure: thumbFailed
					}, "image_id="+id);
				}

				function thumbCreated(o) {
					try {
						var json = YAHOO.lang.JSON.parse(o.responseText);
						if (json.error == "0") {
							console.log(json);
							$("imageprocessor").innerHTML = json.thumbnail;
							if (currentImage < bildeliste.length) behandleBilde();
							else finalize();
						} else {
							errorMsg(json.error);						
						}
					} catch (e) {
						errorMsg(o.responseText);
					}
				}
				
				function thumbFailed(o) {
					errorMsg("Det oppstod en feil");					
				}

				// ============================ 3) Finalize =======================================
								
				function finalize() {
					var id = bildeliste[currentImage];
					currentImage++;
					$("statustext").innerHTML = "Lagrer innstillinger...";
					var url = "'.$this->generateURL('action=ajaxThumbsCreated').'";
					YAHOO.util.Connect.asyncRequest("POST", url, {
						success: finalizeOk,
						failure: finalizeError
					}, "imgcount="+bildeliste.length+"&firstimg="+bildeliste[0]);					
				}
				
				function finalizeOk(o) {
					try {
						var json = YAHOO.lang.JSON.parse(o.responseText);
						$("statustext").innerHTML = "Takk for ditt bidrag til bildearkivet!";						
						$("progressbar").innerHTML = "";
						$("processtitle").innerHTML = "Bildene er behandlet";
						$("continuelink").style.visibility = "visible";
					} catch (e) {
						errorMsg(o.responseText);
					}
				}
				
				function finalizeError(o) {
					errorMsg("Det oppstod en feil");					
				}	

				// ================================== Finito ======================================
				
			//]]>
			</script>
		';
			
		return $output;
	}

	function ajaxSetImageSource() {
		
		$source = $_POST['source'];
		if ($source == "scan" || $source == "camera") {
			
			$res = $this->query(
				"UPDATE $this->table_files 
					SET 
						$this->table_files_field_source='$source'
					WHERE 
						$this->table_files_field_directory='$this->current_directory'
						AND
						$this->table_files_field_thumbwidth='0'"
			);
			
		}
		
		print json_encode(array('error' => '0'));
		exit();
		
	}
	
	function ajaxMakeThumb(){
		
		if (!is_numeric($_POST['image_id'])) { print json_encode(array('error' => 'invalid image id')); exit(); }
		$img_id = intval($_POST['image_id']);

		$res = $this->query("SELECT filename,directory FROM $this->table_files WHERE id=$img_id");
		if ($res->num_rows != 1) $this->fatalError("image doesn't exist");
		$row = $res->fetch_assoc();
		$dir_id = intval($row['directory']);
		$fileName = $row['filename'];

		$res = $this->query("SELECT directory FROM $this->table_dirs WHERE id=$dir_id");
		if ($res->num_rows != 1) $this->fatalError("dir doesn't exist");
		$row = $res->fetch_assoc();
		$dir = $row['directory'];

		$original_path = $this->getPathTo($dir,$fileName);
		$medium_path = $this->getPathTo($dir,$fileName,'medium');
		$small_path = $this->getPathTo($dir,$fileName,'small');
		
		if (!is_file($original_path)) {
			print json_encode(array('error' => "<strong>Ikke en fil</strong>: $original_path"));	
			exit();
		}
		
		ThumbnailService::createThumb($original_path, $medium_path, 490, 490);
		ThumbnailService::createThumb($original_path, $small_path, 140, 140);
		
		list($width, $height, $type, $attr) = getimagesize($small_path);

		$res = $this->query(
			"UPDATE $this->table_files 
				SET visible=1, thumb_width=$width, thumb_height=$height
				WHERE id=$img_id"
		);
		
		$src = ROOT_DIR.$this->getUrlTo($dir,$fileName,'small');
		print json_encode(array('error' => '0', 'thumbnail' => '
			<div class="alpha-shadow">
				<div class="inner_div">
					<img src="'.$src.'" alt="Behandlet bilde" style="width:'.$width.'px; height:'.$height.'px;" />
				</div>
			</div>'));

		exit();
		
	}

	function ajaxThumbsCreated() {
		$imgcount = $_POST['imgcount'];
		$img_id = $_POST['firstimg'];
		if (!is_numeric($imgcount)) exit();
		if (!is_numeric($img_id)) exit();
		
		$res = $this->query("SELECT directory FROM $this->table_files WHERE id='$img_id'");
		if ($res->num_rows != 1) $this->fatalError("dir doesn't exist");
		$row = $res->fetch_assoc();
		$dir_id = $row['directory'];
		
		$res = $this->query("SELECT caption,directory FROM $this->table_dirs WHERE id='$dir_id'");
		if ($res->num_rows != 1) $this->fatalError("dir doesn't exist");
		$row = $res->fetch_assoc();
		$dir = $row['directory'];
		$caption = $row['caption'];

		// Update the photo count database
		$this->updatePhotoCounts();

		$this->addToActivityLog("la opp $imgcount bilder fra <a href=\"".$this->generateCoolUrl($dir)."\">$caption</a>",false,"major");
		print json_encode(array('error' => '0'));
		exit();
	}
	
	/*******************************************************************************************
		 Organize images                                                               
		 **************************************************************************************/
	
	function organizeImages() {

		if  (!$this->allow_organizeimages) return $this->permissionDenied(); 	
		
		$organizeActions = array('deletePhotos','deletePhotosDo',
			'adjustTimestamps','adjustTimestampsDo','editTitles','editTitlesDo');
		if (isset($_POST['action'])) 
			if (in_array($_POST['action'],$organizeActions)) 
				return call_user_func(array($this,$_POST['action']));			
		

		$albumId = $this->current_directory;
		$path = $this->generatePath($albumId); // needed for breadcrumb

		$td = $this->table_dirs; $tf = $this->table_files;
		$cd = $this->current_directory_details;
		
		$use_title = $cd['showtitles'];

		$checkedImages = array();
		if (isset($_POST['handling'])){
			foreach ($_POST as $name => $value){
				if (substr($name,0,4) == "chck"){
					array_push($checkedImages,substr($name,4,strlen($name)-4));
				}
			}
		}
		
		$res = $this->query(
			"SELECT count(*) FROM $tf WHERE directory=$albumId AND visible=1 AND datetime_original=0"
		);
		$row = $res->fetch_row();
		$missingTimestamps = ($row[0] > 0);
		$orderBy = ($missingTimestamps) ? 'id' : 'datetime_original';

		$res = $this->query(
			"SELECT $tf.id,$td.directory,$tf.filename,$tf.uploadedby as uploader,$tf.title,
					$tf.datetime_original,$tf.datetime_source,$tf.datetime_added,
					$tf.filesize
				FROM $tf,$td
				WHERE 
					$tf.directory='$albumId'
					AND $tf.directory=$td.id
					AND $tf.visible=1 
					AND $tf.deletereason=''
				ORDER BY $orderBy"
		);
		$imageTable = "<table width='100%' cellpadding='4' cellspacing='0' class='forum'>
		 <tr>
			<td style='width: 20px;'></td>
			<td style='width: 85px;'></td>
			<td></td>
		</tr>\n";
		$classNo = 1;

		$checkallcode = "";
		$uncheckallcode = "";
		while ($row = $res->fetch_assoc()){
			$classNo = !$classNo;
			$new = false;
			$trclass = $new ? "forum3" : "forum".($classNo+1);
			$tdclass =  $new ? "forum3" : "forum".($classNo+1);
			$imgcode = "<table width='140' height='140' cellpadding='0' cellspacing='0'><tr><td valign='middle' align='center'><img src=\"".$this->getUrlTo($row['directory'],$row['filename'],"small")."\" /></td></tr></table>";
			$id = intval($row['id']);
			
			$dateOriginal = ($row['datetime_original'] == '0000-00-00 00:00') ? '<em>Ukjent</em>' :
				date("d.m.Y, H:i:s",strtotime($row['datetime_original']));
			$dateAdded = ($row['datetime_added'] == '0000-00-00 00:00') ? '<em>Ukjent</em>' :
				date("d.m.Y, H:i:s",strtotime($row['datetime_added']));
			$showCheckBoxes = !isset($_POST['handling']);
			$membercaption = (empty($this->make_memberlink) ? $row['uploader'] : call_user_func($this->make_memberlink, $row['uploader']));
			$tittel = stripslashes($row['title']);
			if (empty($tittel)) $tittel = '<em style="color:#888">Ingen tittel</em>';
			else $tittel = "«".$tittel."»";
			
			$ppl = $this->getPeopleOnPhoto($id);
			$tags = array();
			foreach ($ppl as $p) {
				$tags[] = '<a href="'.$p['url'].'">'.$p['firstName'].'</a>';
			}
			$tags = (count($tags) == 0) ? '<em style="color:#888">Ukjent</em>' : implode(', ',$tags);
			
			$imageTable .= "
				<tr class='$trclass' ".($showCheckBoxes ? "onclick=\"toggleCheckbox('chck$id');\"" : "").">
					".($showCheckBoxes ? "<td class='$tdclass'><input type='checkbox' name='chck$id' id='chck$id' onclick=\"toggleCheckbox('chck$id');\" /></td>" : "<td>&nbsp;</td>")."
					<td class='$tdclass'>$imgcode</td>
					<td style='font-size:10px'>ID: <a href=\"".$this->generateCoolURL($cd['directory'].$id)."\" target='_blank'>$id</a>. Tittel: $tittel<br />
						Lastet opp av: $membercaption den $dateAdded.<br />
						Tatt: $dateOriginal (Kilde: ".$row['datetime_source'].").<br />
						Filstørrelse: ".Utils::formatBytes($row['filesize']).".<br />
						Kommentarer: ".$this->commentCount($id).".<br />
						Personer på bildet: ".$tags.".
					</td>
				</tr>
			";
			$checkallcode .= "document.forms['listform']['chck".$row['id']."'].checked = true\n";
			$uncheckallcode .= "document.forms['listform']['chck".$row['id']."'].checked = false\n";
		}
		$imageTable .= "</table>\n";
		
		if (isset($_POST['handling']) && ($_POST['handling'] == 1)){
			$form2url = $this->generateURL(array("noprint=true","savetitles"));
		} else {
			$form2url = $this->generateURL("action=organizeImages");
		}
		
		$output = '
	<script type="text/javascript">
		//<![CDATA[

			function toggleCheckbox(checkbox_object){
				document.forms["listform"][checkbox_object].checked = !document.forms["listform"][checkbox_object].checked;
			}
			function checkAll(){
				'.$checkallcode.'
			}
			function uncheckAll(){
				'.$uncheckallcode.'
			}
			
			
		//]]>
		</script>
		<h3>Organisere bilder i albumet «'.$path[1].'»</h3>
		<p class="info">Bildene er sortert etter '.$orderBy.'.</p>';
		if (!$this->allow_deleteothersimages) {
			$output .= '<p class="warning">Merk: Du har kun tilgang til å slette bilder du selv har lastet opp, men du kan redigere titler og justere tidsstempel for alle bilder.</p>';
		}
		$output .= '
		<form id="listform" name="listform" method="post" action="'.$form2url.'">
			<p>	
					<select name="action">
						<option value="0">Velg handling</option>
						'.($use_title ? '<option value="editTitles">Redigere tittel/titler</option>' : '').'
						<option value="adjustTimestamps">Justere dato og tid</option> 
						<option value="deletePhotos">Slette bilde(r)</option>
					</select>
				<input type="submit" value="    Fortsett    " />
					<input type="button" value="    Markér alle    " onClick="checkAll()" /> 
					<input type="button" value="    Markér ingen    "  onClick="uncheckAll()" />

			</p>
			
			'.$imageTable.'
		</form>
		';
		return $output;
		
	}
	
	function generatePreviewList($orderBy, $images, $showCheckBoxes = true, $showThumb = true, $showID = true, $showUploader = true, 
		$showTimestamp = true, $showTitle = true, $showEXIF = true, $showComments = true, $extraColumn = NULL)
	{

		$tf = $this->table_files;
		$td = $this->table_dirs;

		$imageTable = "<p class='info'>Bildene er sortert etter $orderBy.</p>
			<table width='100%' cellpadding='4' cellspacing='0' class='forum'>
		 <tr>
			".($showCheckBoxes ? "<td style='width: 20px;'></td>" : "")."
			".($showThumb ? "<td style='width: 85px;'></td>" : "")."
			".($showID ? "<td><b><i>ID</i></b></td>" : "")."
			".($showUploader ? "<td><b><i>Opplaster</i></b></td>" : "")."
			".($showTimestamp ? "<td><b><i>Originalt tidsstempel</i></b></td>" : "")."
			".(($showTitle && $this->use_title) ? "<td><b><i>Tittel</i></b></td>" : "")."
			".(($showEXIF && $this->enable_exif_functions) ? "<td><b><i>EXIF</i></b></td>" : "")."
			".(($showComments && $this->enable_comments) ? "<td><b><i>Komm-<br />entarer</i></b></td>" : "")."
			".(($extraColumn != NULL) ? "<td><b><i>".$extraColumn["caption"]."</i></b></td>" : "")."
		</tr>\n";
		$classNo = 1;

		$res = $this->query(
			"SELECT $tf.id,$td.directory,$tf.filename,$tf.uploadedby as uploader,$tf.title,
					$tf.datetime_original,$tf.datetime_source
				FROM 
					$tf,$td
				WHERE 
					$tf.id IN (".addslashes(implode(",",$images)).")
					AND $tf.directory=$td.id
				ORDER BY $orderBy"
		);

		while ($row = $res->fetch_assoc()){
			$classNo = !$classNo;
			$new = false;
			$trclass = $new ? "forum3" : "forum".($classNo+1);
			$tdclass =  $new ? "forum3" : "forum".($classNo+1);
			$imgcode = "<img src=\"".$this->getUrlTo($row['directory'],$row['filename'],"small")."\" style='width: 80px; height: 60px;' />";
			
			if ($row['datetime_original'] == "0000-00-00 00:00"){
				$datestr = "<em>Ukjent</em>";
			} else {
				$datestr = date("d.m.Y, H:i:s",strtotime($row['datetime_original']));
			}
			$membercaption = (empty($this->make_memberlink) ? $row['uploader'] : call_user_func($this->make_memberlink, $row['uploader']));
			if ($this->use_title) $tittel = stripslashes($row['title']);
			$imageTable .= "
				<tr class='$trclass' ".($showCheckBoxes ? "onclick=\"toggleCheckbox('chck".$row['id']."');\"" : "").">
					".($showCheckBoxes ? "<td class='$tdclass'><input type='checkbox' name='chck".$row['id']."' id='chck".$row['id']."' /></td>" : "")."
					".($showThumb ? "<td class='$tdclass'>".$imgcode."</td>" : "")."
					".($showID ? "<td class='$tdclass'>".$row['id']."</td>" : "")."
					".($showUploader ? "<td class='$tdclass'>".$membercaption."</td>" : "")."
					".($showTimestamp ? "<td class='$tdclass'>".$datestr."</td>" : "")."
					".(($showTitle && $this->use_title) ? "<td class='$tdclass'>".$tittel."</td>" : "")."
					".(($showEXIF && $this->enable_exif_functions) ? "<td class='$tdclass'>".(($row['datetime_source'] == 'EXIF.DateTimeOriginal') ? 'Ja' : 'Nei')."</td>" : "")."
					".(($showComments && $this->enable_comments) ? "<td class='$tdclass'>".$this->commentCount($row['id'])."</td>" : "")."
					".(($extraColumn != NULL) ? "<td class='$tdclass'>".$extraColumn['values'][$row['id']]."</td>" : "")."
				</tr>
			";
		}
		$imageTable .= "</table>\n";
		return $imageTable;
	}

	function getImageSelection(){
		$checkedImages = array();
		if (isset($_POST['action'])){
			foreach ($_POST as $name => $value){
				if (substr($name,0,4) == "chck"){
					array_push($checkedImages,substr($name,4,strlen($name)-4));
				}
			}
		}
		return $checkedImages;
	}

	function deletePhotos(){

		if  (!$this->allow_organizeimages) return $this->permissionDenied(); 	

		$albumId = $this->current_directory;
		$imagesToDel = $this->getImageSelection();
		if (count($imagesToDel) < 1) {
			$this->redirect($this->generateURL('action=organizeImages'),'Du må velge minst ett bilde','error');
		}

		$res = $this->query(
			"SELECT 
				$this->table_files_field_uploader as uploader,
				$this->table_files_field_directory as directory,
				$this->table_files_field_uploader as uploader
			FROM 
				$this->table_files
			WHERE
				$this->table_files_field_id IN (".addslashes(implode(",",$imagesToDel)).")"
		);
		while ($row = $res->fetch_assoc()){
			$allow_delete = ($this->allow_deleteothersimages || ($this->allow_deleteownimages && ($row['uploader']==$this->login_identifier)));
			if (!$allow_delete) return $this->notSoFatalError("Beklager. Noen av bildene du prøver å slette er lastet opp av andre enn deg. Du kan kun slette bilder du selv har lagt opp. Om du har gode grunner for å slette dette bildet, f.eks. fordi du finner det støtende, ta kontakt med webmaster som kan slette det for deg.");
		}
		$path = $this->generatePath($this->current_directory);

		return '
			<h3>Slette bilder</h3>
			<form method="post" action="'.$this->generateURL('action=organizeImages').'">
				Er du sikker på at du ønsker å slette disse bildene?
				<br /><br />
				<input type="hidden" name="imagestodel" value="'.implode(',',$imagesToDel).'" />
				<input type="hidden" name="action" value="deletePhotosDo" />
				'.$this->generatePreviewList($this->table_files_field_id, $imagesToDel, false, true, true, true, true, false, false, false).'
				<br /><br />
				Skriv gjerne inn årsaken til at du sletter bildene her:
				<input type="text" name="delete_reason" style="width:450px;" /><br /><br />
				
				<input type="submit" name="confirmdelete" value="    Ja    " /> 
				<input type="submit" name="abortdelete" value="    Nei    " />
			</form>		
		';		
	}
	
	function deletePhotosDo(){

		if (!$this->allow_organizeimages) return $this->permissionDenied(); 	

		$albumId = $this->current_directory;
		if ($albumId <= 0) $this->fatalError("[imagearchive] Invalid input (5)!");
		$imgarr = $_POST['imagestodel'];
		$imgArray = explode(",",$imgarr);
		if (count($imgArray) < 1) {
			$this->redirect(
				$this->generateURL('action=organizeImages'),
				'Du må velge minst ett bilde','error'
			);
		}

		$res = $this->query("SELECT uploadedby,directory FROM $this->table_files 
            WHERE id IN (".addslashes($imgarr).") and deletereason=''");
        if ($res->num_rows == 0) {
		    $this->redirect($this->generateURL('action=organizeImages'),'Bildene har allerede blitt slettet (kanskje siden ble lastet to ganger?)');	
        } else if ($res->num_rows != count($imgArray)) {
            $this->fatalError("Bilder forespurt: ".count($imgArray).". Bilder funnet: ".$res->num_rows."! Ta kontakt med webmaster.");
        }
		while ($row = $res->fetch_assoc()){
			$allow_delete = ($this->allow_deleteothersimages || 
				($this->allow_deleteownimages && (intval($row['uploadedby']) == $this->login_identifier)));
			if (!$allow_delete) {
				print $this->permissionDenied();
				exit();
			}
            if (intval($row['directory']) != $albumId) {
                $this->fatalError("Image array contains images not in the current album!");
            }
		}
		$res2 = $this->query("SELECT caption,thumbnail,directory FROM $this->table_dirs WHERE id=$albumId");
		if ($res2->num_rows != 1) $this->fatalError("[imagearchive] The album $albumId does not exist 2!");
        $row2 = $res2->fetch_assoc();
        $thumbnailId = intval($row2['thumbnail']);
        $albumCaption = $row2['caption'];
		if (in_array($thumbnailId,$imgArray)){
			$this->query("UPDATE $this->table_dirs SET thumbnail=NULL WHERE id=$albumId");
			unlink($this->getPathTo($row2['directory'],'thumbnail'));
		}
		
		$delete_reason1 = (isset($_POST['delete_reason']) ? str_replace("|"," ",strip_tags($_POST['delete_reason'])) : "");
		$delete_reason = addslashes($this->login_identifier."|".time()."|".$delete_reason1);
	
		$this->query("UPDATE $this->table_files SET deletereason=\"$delete_reason\" WHERE id IN (".addslashes($imgarr).")");	

		// Update the photo count database
		$this->updatePhotoCounts();
		
		// Write to eventlog
		$this->addToActivityLog("slettet bildene $imgarr fra albumet <a href=\"".$this->generateUrl('')."\">".$albumCaption."</a>.  Årsak: \"$delete_reason1\"");
		
		$msg = ((count($imgArray)>1)?'Bildene':'Bildet').' ble slettet';
		$this->redirect($this->generateURL('action=organizeImages'),$msg);	
	}

	function adjustTimestamps(){
		if  (!$this->allow_organizeimages) return $this->permissionDenied(); 	
		$albumId = $this->current_directory;
		$tf = $this->table_files; $td = $this->table_dirs;

		$imagesToEdit = $this->getImageSelection();
		if (count($imagesToEdit) < 1) {
			$this->redirect($this->generateURL('action=organizeImages'),'Du må velge minst ett bilde','error');
		}		
		
		$column = array("caption" => "Nytt tidsstempel", "values" => array());
		foreach ($imagesToEdit as $i) {
			$i = intval($i);
			$res = $this->query("SELECT datetime_original FROM $tf WHERE id=$i");
			$row = $res->fetch_assoc();
			$column["values"][$i] = "
				<span id='newDateTime$i'>
					".date("d.m.Y, H:i:s",strtotime($row['datetime_original']))."
				</span>
				<input type='hidden' id='origDateTime$i' name='origDateTime$i' value='".strtotime($row['datetime_original'])."' />
				<input type='hidden' id='adjustment$i' name='adjustment$i' value='0' />
			";
		}

		$path = $this->generatePath($this->current_directory);

		$output = '
			
		<script type="text/javascript">
		//<![CDATA[
			
			var imageList = ['.implode(',',$imagesToEdit).'];
		
	 		YAHOO.util.Event.onContentReady("timestampForm", handleOnAvailable); 
	 		
	 		function handleOnAvailable(o) {
	 			function fnCallback(e) { alert("click"); }
				YAHOO.util.Event.addListener("tDirection", "change", dateChanged);
				YAHOO.util.Event.addListener("tDays", "change", dateChanged);
				YAHOO.util.Event.addListener("tHours", "change", dateChanged);
				YAHOO.util.Event.addListener("tMinutes", "change", dateChanged);
				YAHOO.util.Event.addListener("tSeconds", "change", dateChanged);
				
	 		}
	 		
	 		function twoDigits(n) {
	 			if (n < 10) return "0"+n
	 			else return n;
	 		}
	 		
	 		function dateChanged(e) {
	 			var adjustment = parseInt($("tSeconds").value) 
	 				+ parseInt($("tMinutes").value)*60 
	 				+ parseInt($("tHours").value)*60*60 
	 				+ parseInt($("tDays").value)*60*60*24;
	 			var dir = $("tDirection").value;
	 			if (dir == "subtract") adjustment = -adjustment;
	 			var id,origDateTime,newDateTime,newJsDate;
	 			for (var i = 0; i < imageList.length; i++) {
	 				id = imageList[i];
		 			$("adjustment"+id).value = adjustment;
		 			origDateTime = parseInt($("origDateTime"+id).value);
		 			newDateTime = origDateTime + adjustment;
		 			newJsDate = new Date(newDateTime*1000);
	 				$("newDateTime"+id).innerHTML = twoDigits(newJsDate.getDate()) + "."
	 					+ twoDigits(newJsDate.getMonth()+1) + "."
	 					+ (1900+newJsDate.getYear()) + ", "
	 					+ twoDigits(newJsDate.getHours()) + ":"
	 					+ twoDigits(newJsDate.getMinutes()) + ":"
	 					+ twoDigits(newJsDate.getSeconds());
	 			}
	 		}
	 		
		//]]>
		</script>			
			<h3>Justere dato og tid</h3>
			<form id="timestampForm" method="post" action="'.$this->generateURL('action=organizeImages').'">
				
				<input type="hidden" name="action" value="adjustTimestampsDo" />
				
				<p>
				<select id="tDirection" name="direction" id="direction">';
					$sel2 = ((isset($_GET["direction"]) && ($_GET["direction"] == "subtract")) ? ' selected="selected"' : '');
					$output .='
					<option value="add">Legg til</option>
					<option value="subtract" '.$sel2.'>Trekk fra</option>
				</select>
				<select id="tDays" name="days" id="days">
				';
				for ($i = 0; $i < 51; $i++){
					$sel = ((isset($_GET['days']) && ($_GET['days'] == $i)) ? " selected='selected'" : "");
					$output .= "<option value='$i' $sel>$i</option>\n";
				}
				$output .= "</select>
				dager,
				<select id='tHours' name='hours' id='hours'>
				";
				for ($i = 0; $i < 24; $i++){
					$sel = ((isset($_GET['hours']) && ($_GET['hours'] == $i)) ? " selected='selected'" : "");
					$output .= "<option value='$i' $sel>$i</option>\n";
				}
				$output .= "</select>
				timer,
				<select id='tMinutes' name='minutes' id='minutes'>
				";
				for ($i = 0; $i < 60; $i++){
					$sel = ((isset($_GET['minutes']) && ($_GET['minutes'] == $i)) ? " selected='selected'" : "");
					$output .= "<option value='$i' $sel>$i</option>\n";
				}
				$output .= "</select>
				minutter,
				<select id='tSeconds' name='seconds' id='seconds'>
				";
				for ($i = 0; $i < 60; $i++){
					$sel = ((isset($_GET['seconds']) && ($_GET['seconds'] == $i)) ? " selected='selected'" : "");
					$output .= "<option value='$i' $sel>$i</option>\n";
				}
				$output .= "</select>
				sekunder.
				<br /><br />
				<input type='hidden' name='imagestoedit' value='".implode(",",$imagesToEdit)."' />
				<input type='submit' name='save' value='    Lagre    ' />
				</p>
		";		
		

		$res = $this->query(
			"SELECT count(*) FROM $tf WHERE directory=$albumId AND visible=1 AND datetime_original=0"
		);
		$row = $res->fetch_row();
		$missingTimestamps = ($row[0] > 0);
		$orderBy = ($missingTimestamps) ? 'id' : 'datetime_original';
		
		if ($missingTimestamps){
			$output .="<p class='warning'>".str_replace("%count%",$row[0],$this->missingtimestamps_caption)."</p>";
			$orderBy = $this->table_files_field_id;
		}

		$output .= $this->generatePreviewList($orderBy, $imagesToEdit, false, true, false, false, true, false, false, false, $column);
		$output .= "</form>";
		return $output;
	}
	
	function adjustTimestampsDo(){

		if  (!$this->allow_organizeimages) return $this->permissionDenied(); 	
		$albumId = $this->current_directory;
		$tf = $this->table_files; $td = $this->table_dirs;

		$imgarr = $_POST['imagestoedit'];
		$direction = $_POST['direction'];
		$days = intval($_POST['days']);
		$hours = intval($_POST['hours']);
		if ($hours < 10) $hours = "0$hours";
		$mins = intval($_POST['minutes']);
		if ($mins < 10) $mins = "0$mins";
		$secs = intval($_POST['seconds']);
		if ($secs < 10) $secs = "0$secs";
		$adjustment = "$days $hours:$mins:$secs";
		if ($direction == 'add') 
			$adjustmentStatement = "ADDTIME(datetime_original,'$adjustment')";
		else
			$adjustmentStatement = "SUBTIME(datetime_original,'$adjustment')";		
		
		$res = $this->query("UPDATE $tf SET datetime_original=$adjustmentStatement 
			WHERE id IN  (".addslashes($imgarr).")");
		
		$this->redirect($this->generateURL("action=organizeImages"),
			"Tidsstempel ble justert for de valgte bildene!");
	}
	
	function editTitles(){
		if  (!$this->allow_organizeimages) return $this->permissionDenied(); 	
		$albumId = $this->current_directory;
		$tf = $this->table_files; $td = $this->table_dirs;

		$imagesToEdit = $this->getImageSelection();
		if (count($imagesToEdit) < 1) {
			$this->redirect($this->generateURL('action=organizeImages'),'Du må velge minst ett bilde','error');
		}
		
		$column = array("caption" => "Ny tittel", "values" => array());
		foreach ($imagesToEdit as $i) {
			$i = intval($i);
			$res = $this->query("SELECT title FROM $tf WHERE id=$i");
			$row = $res->fetch_assoc();
			$column["values"][$i] = "
				<input type='text' name='titl$i' style='width:180px;' value=\"".stripslashes($row['title'])."\" />
			";
		}

		$path = $this->generatePath($this->current_directory);

		$output = '
			
			<h3>Endre bildetitler</h3>
			<form method="post" action="'.$this->generateURL('action=organizeImages').'">
				
				<input type="hidden" name="action" value="editTitlesDo" />
				<input type="submit" value="    Lagre    " />
		
		';

		$res = $this->query(
			"SELECT count(*) FROM $tf WHERE directory=$albumId AND visible=1 AND datetime_original=0"
		);
		$row = $res->fetch_row();
		$missingTimestamps = ($row[0] > 0);
		$orderBy = ($missingTimestamps) ? 'id' : 'datetime_original';
		
		if ($missingTimestamps){
			$output .="<p class='warning'>".str_replace("%count%",$row[0],$this->missingtimestamps_caption)."</p>";
			$orderBy = $this->table_files_field_id;
		}

		$output .= $this->generatePreviewList($orderBy, $imagesToEdit, false, true, false, false, false, true, false, false, $column);
		$output .= "</form>";
		return $output;
	}
	
	function editTitlesDo(){
				
		if  (!$this->allow_organizeimages) return $this->permissionDenied();	
		$checkedImages = array();
		$titles = array();
		foreach ($_POST as $name => $value){
			if (substr($name,0,4) == "titl"){
				array_push($checkedImages,substr($name,4,strlen($name)-4));
				$titles[substr($name,4,strlen($name)-4)] = addslashes(strip_tags($value));
			}
		}
		foreach ($checkedImages as $n => $imgid){
			$this->query(
				"UPDATE $this->table_files SET title=\"".$titles[$imgid]."\" WHERE id='$imgid'"
			);
			$this->addToActivityLog("oppdaterte tittel for bildet $imgid");
		}
		$this->redirect($this->generateURL('action=organizeImages'),"Titler er oppdatert");
	}
	
	function ajaxSavePhotoTitle() {
	
		if  (!$this->allow_organizeimages) return $this->permissionDenied(); 	
		$newTitle = addslashes(htmlspecialchars($_POST['title']));
		$img = $this->current_image;

		$this->query(
			"UPDATE $this->table_files 
			SET 
				$this->table_files_field_title=\"$newTitle\"
			WHERE $this->table_files_field_id=$img"
		);
		$this->addToActivityLog("oppdaterte tittel for bildet <a href=\"".$this->generateURL("")."\">$img</a>", true);
		
		$newTitleHtml = empty($newTitle) ? "<em style='font-weight:normal; color: #888;'>Ingen tittel</em>" : $newTitle;

		print json_encode(array(
			'error' => 0,
			'title' => stripslashes($newTitle)
		));
		exit();
		
	}


	/*******************************************************************************************
		 Album settings                                                               
		 **************************************************************************************/
	
	function albumSettings(){

		if  (!$this->allow_editarchivesettings) return $this->permissionDenied(); 	

		$output = "";
		
		$albumId = $this->current_directory;
		if (!is_numeric($albumId)) $this->fatalError("[imagearchive] Invalid input!");		
		$tf = $this->table_files; $td = $this->table_dirs;

		$res = $this->query(
			"SELECT caption,directory,cal_id,event_id,thumbdir,thumbnail,description,showtitles,showweekdays
			FROM $td WHERE id=$albumId"
		);
		if ($res->num_rows != 1) $this->fatalError("[imagearchive] 0 Arkivet finnes ikke!");
		$row = $res->fetch_assoc();
		$default_caption = stripslashes($row['caption']);
		$default_foldername = stripslashes($row['directory']);
		$default_cal_id = $row['cal_id'];
		$default_event_id = $row['event_id'];
		$default_description = $row['description'];
		$default_showweekdays = ($row['showweekdays'] ? "checked='checked'" : "");
		if (isset($_GET['thumbparentdir'])){
			$thumbdirs = $_GET['thumbparentdir'];
			$default_thumbnail = "";
		} else {
			$thumbdirs = $row['thumbdir'];
			$default_thumbnail = $row['thumbnail'];
		}
		if (empty($thumbdirs)) $thumbdirs = $albumId;

		$thumbdirsarray = explode(",",$thumbdirs);
		$thumbdirsarray[] = $default_thumbnail;
		
		$res = $this->query(
			"SELECT count(*) FROM $tf WHERE directory=$albumId AND visible=1 AND datetime_original=0"
		);
		$row = $res->fetch_row();
		$missingTimestamps = ($row[0] > 0);
		$orderBy = ($missingTimestamps) ? 'id' : 'datetime_original';
		
		$r1a = array(); 				$r2a = array();
		
		if ($this->enable_calendar_functions){
		
			$calendars = $this->listCalendars();
			$cal_opts = "";
			$cal_opts .= "<option value='-'>[ Ingen hendelse ]</option>\n";
			foreach ($calendars as $c) {
				$def = ($default_cal_id == $c['id']) ? " selected=\"selected\"" : "";
				$cal_opts .= "<option value='".$c['id']."'$def>".stripslashes($c['header'])."</option>\n";
			}

			$r1a[] = "%cal-list%";			$r2a[] = $cal_opts;
			$r1a[] = "%events_uri%";		$r2a[] = $this->generateUrl('action=ajaxFetchEvents');
			
			if ($default_cal_id > 0) {
				$r1a[] = "%events%";			$r2a[] = $this->makeEventsDropdown($default_cal_id,$default_event_id);
			} else {
				$r1a[] = "%events%";			$r2a[] = "";
			}
		}

		$path = $this->generatePath($albumId);
		$imgList = $this->makeImgList($thumbdirsarray,0);

		$url_deletearchive = $this->generateURL('action=deleteAlbum');
		$url_makethumbs = $this->generateURL('action=makeThumbs');
		$url_movearchive = $this->generateURL('action=moveAlbum');
		
		$r1a[] = "%post_uri%";				$r2a[] = $this->generateURL('action=albumSettingsSave');
		//$r1a[] = "%js-imagelist%";			$r2a[] = $imgjscode;
		//$r1a[] = "%lo-res-image-dir%";		$r2a[] = $this->root_dir.$thumb_directory.$this->lo_res_dir;
		$r1a[] = "%caption%";				$r2a[] = $default_caption;
		$r1a[] = "%folder%";				$r2a[] = $default_foldername;
		$r1a[] = "%description%";			$r2a[] = $default_description;
		$r1a[] = "%path%";					$r2a[] = $path[1];
		$r1a[] = "%show-weekdays%";			$r2a[] = $default_showweekdays;
		$r1a[] = "%hendelse_style%";		$r2a[] = $this->enable_calendar_functions ? "":"display:none;";
		$r1a[] = '%deletearchive%';			$r2a[]  = (($this->allow_deleteownarchives || $this->allow_deleteothersarchives) ? ' <a href="'.$url_deletearchive.'" class="icn" style="background-image:url(/images/icns/folder_delete.png);">'.$this->label_deletearchive.'</a>' : '');
		$r1a[] = '%makethumbs%';			$r2a[]  = (($this->allow_addimages) ? ' <a href="'.$url_makethumbs.'" class="icn" style="background-image:url(/images/icns/folder_wrench.png);">'.$this->label_makethumbs.'</a>' : '');
		$r1a[] = '%movearchive%';			$r2a[]  = ($this->allow_editarchivesettings ? '<a href="'.$url_movearchive.'" class="icn" style="background-image:url(/images/icns/folder_go.png);">'.$this->label_movearchive.'</a>' : '');
		
		//$r1a[] = "%prev-image%";			$r2a[] = $imgsrc;
		$r1a[] = "%img-list%";				$r2a[] = $imgList;
		$r1a[] = "%imgdir_uri%";			$r2a[] = $this->generateUrl('action=ajaxFetchImagelist');
		$r1a[] = "%img_uri%";				$r2a[] = $this->generateUrl('action=ajaxFetchImage');		

		$output .= str_replace($r1a, $r2a, $this->template_archivesettings);
		
		return $output;
	}
	
	function albumSettingsSave(){

		if (!$this->allow_editarchivesettings) return $this->permissionDenied();
		$albumId = intval($this->current_directory);
		if ($albumId <= 0) return "Invalid album";
	
		
		if (!ini_get('safe_mode')){
			set_time_limit(120);
		} else {
			$this->addToErrorLog("The function set_time_limit seems to be disabled in the serversetup. This may lead to timeouts during the image upload. Max execution time is ".ini_get("max_execution_time")." seconds.");
		}

		if (empty($_POST['caption'])) $this->fatalError("[imagearchive] Du må fylle inn arkivnavn. Gå tilbake og prøv på nytt!");
		
		$thumbdirs = array($albumId);
		for ($i = 0; $i <= 100; $i++) {
			if (isset($_POST['img'.$i])) $thumbdirs[] = intval($_POST['img'.$i]);
			else break;
		}
		$thumbnail = array_pop($thumbdirs);
		$thumbdir = implode(",",$thumbdirs);

		if (empty($thumbdir)) $thumbdir = $albumId;
		$res = $this->query("SELECT $this->table_files_field_id FROM $this->table_files WHERE $this->table_files_field_id=$thumbnail");
		if ($res->num_rows == 0){
			$this->redirect($this->generateURL("action=albumSettings"),"Du må velge et representativt bilde!","error");
		}
				
		$caption = addslashes(strip_tags($_POST['caption']));
		$description = addslashes(strip_tags($_POST['description']));
		$showweekdays = ((isset($_POST['showweekdays']) && ($_POST['showweekdays'] == "on")) ? "1" : "0");
		
		$cal_id = isset($_POST['cal_id']) ? intval($_POST['cal_id']) : 0;
		$event_id = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;
	
		$res = $this->query(
			"SELECT 
				$this->table_dirs_field_caption as caption,
				$this->table_dirs_field_directory as directory,
				$this->table_dirs_field_cal_id as cal_id,
				$this->table_dirs_field_event_id as event_id,
				$this->table_dirs_field_thumbnail as thumbnail,
				$this->table_dirs_field_description as description,
				$this->table_dirs_field_showtitles as showtitles,
				$this->table_dirs_field_showweekdays as showweekdays				
			FROM 
				$this->table_dirs 
			WHERE 
				$this->table_dirs_field_id=$albumId"
		);
		if ($res->num_rows != 1) $this->fatalError("[imagearchive] Arkivet finnes ikke!");
		$row = $res->fetch_assoc();
		$dbthumbnail = $row['thumbnail'];
		$dbdirectory = $row['directory'];
		$dbcaption = $row['caption'];
		$dbdescription = $row['description'];
		$dbcalid = $row['cal_id'];
		$dbeventid = $row['event_id'];
		$dbweekdays = $row['showweekdays'];
		
		$updates = array();
		if ($cal_id != $dbcalid || $event_id != $dbeventid) $updates[] = "kalendertilknytning";
		if ($description != $dbdescription) $updates[] = "beskrivelse";
		if ($showweekdays != $dbweekdays) $updates[] = "visning av ukedager";
		

		if ($dbthumbnail != $thumbnail){
			$res = $this->query(
				"SELECT 
					$this->table_dirs.$this->table_dirs_field_directory as directory,
					$this->table_files.$this->table_files_field_filename as filename
				FROM 
					$this->table_files,
					$this->table_dirs 
				WHERE 
					$this->table_files.$this->table_files_field_id='$thumbnail'
					AND
					$this->table_files.$this->table_files_field_directory=$this->table_dirs.$this->table_dirs_field_id"
			);
			if ($res->num_rows != 1) $this->fatalError("[imagearchive] Thumbnailbildet finnes ikke!");
			$row = $res->fetch_assoc();
			$thumbdirectory = $row['directory'];
			$filename = $row['filename'];
			$org_path   = $this->getPathTo($thumbdirectory,$filename);
			$thumb_path = $this->getPathTo($dbdirectory,'thumbnail');
						
			ThumbnailService::createThumb($org_path, $thumb_path, 200, 150, 80, false);

  			$updates[] = "representativt bilde";
		}

		$this->query(
			"UPDATE $this->table_dirs 
			SET 
				$this->table_dirs_field_caption='$caption',
				$this->table_dirs_field_cal_id='$cal_id',
				$this->table_dirs_field_event_id='$event_id',
				$this->table_dirs_field_thumbnail='$thumbnail',
				$this->table_dirs_field_thumbdir='$thumbdir',
				$this->table_dirs_field_description='$description',
				$this->table_dirs_field_showweekdays='$showweekdays'
			WHERE $this->table_dirs_field_id=$albumId"
		);
		if (!empty($updates)) {
			$lastupdt = array_pop($updates);
			$updates = (count($updates) > 0) ? implode(", ",$updates)." og ".$lastupdt : $lastupdt;
			$this->addToActivityLog("oppdaterte $updates for bildearkivet <a href=\"".$this->generateCoolUrl($dbdirectory)."\">$caption</a>");
		}
		$this->redirect($this->generateURL(""));
	}
	
	function makeImgList($thumbdir,$level) {
		
		if (is_array($thumbdir)) {
			if (empty($thumbdir)) return "";
			$thumbdirs = $thumbdir;
			$thumbdir = array_shift($thumbdirs);
		} else {
			$thumbdirs = array();
		}

		$res = $this->query(
			"SELECT 
				$this->table_files_field_id as id,
				$this->table_files_field_filename as filename
			FROM 
				$this->table_files
			WHERE 
				$this->table_files_field_directory='$thumbdir'
				AND $this->table_files_field_visible=1 
				AND $this->table_files_field_deletereason=''"
		);

		if ($res->num_rows != 0){

			$opts = "<option value='-1'>--- Velg fra listen ---</option>";
			if (isset($thumbdirs[0])) $default_thumbnail = $thumbdirs[0];
			else $default_thumbnail = -1;

			while ($row = $res->fetch_assoc()){
				//if ($default_thumbnail == $row['id']) $default_thumbnail_filename = $row['filename'];
				$selected = ($default_thumbnail == $row['id']) ? " selected='selected'" : "";
				$opts .= "
							<option value='".$row['id']."'$selected>".$row['id']."</option>";
			}
			
			$icode = "";
			if (count($thumbdirs) > 0) {
				$img_id = intval($thumbdirs[0]);
				if ($img_id > 0) {
					$res = $this->query(
						"SELECT 
							$this->table_files.$this->table_files_field_filename as filename,
							$this->table_dirs.$this->table_files_field_directory as directory
						FROM 
							$this->table_files,$this->table_dirs
						WHERE 
							$this->table_files.$this->table_files_field_id=$img_id
							AND $this->table_files.$this->table_files_field_directory=$this->table_dirs.$this->table_dirs_field_id"
					);
					$row = $res->fetch_assoc();
					$dir =  $row['directory'];
					$fname = $row['filename'];
					$path = $this->getUrlTo($dir,$fname,"medium");
					$icode = '
							<div class="alpha-shadow">
								<div class="inner_div">
									<img src="'.$path.'" style="width:200px; height:150px;" />
								</div>
							</div>
							<div style="clear:both;text-align:center"><!-- --></div>
					';
				}
			}
			return "
						<select name='img$level' id='img$level' onchange=\"select_image($level);\" style='font-size:90%; border: 1px solid #666666;'>
							$opts
						</select>
						<span id='imgspan".($level+1)."' style='display:block;width:250px;height:200px;'>$icode</span>
			";
		} else {
			if (empty($thumbdir)) return "";
			$res = $this->query(
				"SELECT 
					$this->table_dirs_field_id as id,
					$this->table_dirs_field_caption as caption
				FROM 
					$this->table_dirs
				WHERE
					$this->table_dirs_field_parentdir='$thumbdir'
					AND $this->table_dirs_field_deletereason=''
				ORDER BY 
					$this->table_dirs_field_timestamp"
			);
			$opts = "<option value='-1'>--- Velg fra listen ---</option>";
			if (isset($thumbdirs[0])) $thisselected = $thumbdirs[0];
			else $thisselected = -1;
			while ($row = $res->fetch_assoc()){
				//if ($thisselected == $row['id']) $default_thumbnail_filename = $row['caption'];
				$selected = ($thisselected == $row['id']) ? " selected='selected'" : "";
				$opts.= "
							<option value='".$row['id']."'$selected>".stripslashes($row['caption'])."</option>";
			}
			return "
						<select name='img$level' id='img$level' onchange=\"select_imagedir($level);\" style='font-size:90%; border: 1px solid #666666;'>
							$opts
						</select>
						<span id='imgspan".($level+1)."'>".$this->makeImgList($thumbdirs,$level+1)."</span>
			";
		}
	}
	
	
	function listCalendars() {
		
		$lang = $this->preferred_lang;
		$calendars = array();
		$res = $this->query("SELECT 
				$this->table_pages.id,
				$this->table_pages.fullslug,
				$this->table_pagelabels.value as header
			FROM 
				$this->table_pages 
			LEFT JOIN
				$this->table_pagelabels
				ON
				($this->table_pagelabels.page=$this->table_pages.id
				AND
				$this->table_pagelabels.label='page_header'
				AND
				$this->table_pagelabels.lang='$lang')
			WHERE 
				$this->table_pages.class=$this->calendar_class_id"
		);
		while ($row = $res->fetch_assoc()) {
			$calendars[] = $row;
		}
		return $calendars;
		
	}
	
	function initCalendar($id) {
		if (!is_numeric($id)) return;
		$calObj = new calendar_basic();
		call_user_func($this->prepare_classinstance,$calObj,$id);
		$calObj->initialize_base();
		return $calObj;
	}
	
	function makeEventsDropdown($calendar, $def) {
		$calObj = new calendar_basic();
		call_user_func($this->prepare_classinstance,$calObj,$calendar);
		$calObj->initialize_base();
		$dropdown = $calObj->makeEventsDropDown(array(
			'pastEventsOnly' => true,
			'numEvents' => 20,
			'defaultEvent' => $def
		));
		return "
			<select name='event_id' style='font-size:90%; border: 1px solid #666666;'>
			".$dropdown."
			</select>
		";
	}
	function ajaxFetchEvents() {
		$this->sendContentType();
		$c = intval($_POST['cal_page_id']);
		if ($c > 0) {
			print $this->makeEventsDropdown($c, 0);
		} else {
			print " ";
		}
		exit();
	}
	
	function ajaxFetchImagelist() {
		$this->sendContentType();
		$s_val = $_POST['select_value'];
		$nr = $_POST['select_nr'];
		if (!is_numeric($s_val)) return;
		if (!is_numeric($nr)) return;
		print $this->makeImgList($s_val,$nr+1);
		exit();
	}

	function ajaxFetchImage() {
		$this->sendContentType();
		$s_val = $_POST['select_value'];
		$nr = $_POST['select_nr'];
		if (!is_numeric($s_val)) return;
		if (!is_numeric($nr)) return;
		
		$res = $this->query(
			"SELECT 
				$this->table_files.$this->table_files_field_filename as filename,
				$this->table_dirs.$this->table_files_field_directory as directory
			FROM 
				$this->table_files,$this->table_dirs
			WHERE 
				$this->table_files.$this->table_files_field_id=$s_val
				AND $this->table_files.$this->table_files_field_directory=$this->table_dirs.$this->table_dirs_field_id"
		);
		$row = $res->fetch_assoc();
		$dir =  $row['directory'];
		$fname = $row['filename'];
		$path = ROOT_DIR.$this->getUrlTo($dir,$fname,"medium");
		print "
			<div class='alpha-shadow'>
				<div class='inner_div'>
					<img src='$path' style='width:200px; height:150px;' />
				</div>
			</div>
			<div style='clear:both;text-align:center'><!-- --></div>
		";
		exit();
	}
	
	/*******************************************************************************************
		 Moving                                                               
		 **************************************************************************************/
	
	function makeDirTree($dir,$str,$level,$defaultVal = -1,$enableFolders = true){
		$dir = intval($dir);
		$res = $this->query(
			"SELECT id,caption FROM $this->table_dirs 
			WHERE parentdir=$dir AND deletereason='' ORDER BY position DESC"
		);
		while ($row = $res->fetch_assoc()){
			$id = intval($row['id']);
			if ($enableFolders) $dis = "";
			else {
				$res2 = $this->query("SELECT id FROM $this->table_dirs WHERE parentdir=$id AND deletereason=''");
				$dis = ($res2->num_rows == 0) ? "" : " disabled='disabled'";
			}
			$spaces = "";
			for ($i = 1; $i < $level; $i++){ $spaces .= "&nbsp;&nbsp;&nbsp;&nbsp;"; }
			$sel = ($defaultVal == $id) ? " selected='selected'" : "";
			$str .= "<option value='".$id."'$sel$dis>$spaces".stripslashes($row['caption'])."</option>\n";
			$str = $this->makeDirTree($id,$str,$level+1,$defaultVal,$enableFolders);
		}
		return $str;
	}
	
	function list_subdirs($dir) {
		$res = $this->query(
			"SELECT 
				$this->table_dirs_field_id as id,
				$this->table_dirs_field_caption as caption
			FROM 
				$this->table_dirs 
			WHERE 
				$this->table_dirs_field_parentdir='$dir' 
			AND
				$this->table_dirs_field_deletereason=''"
		);
		$tmp = array();
		while ($row = $res->fetch_assoc()) {
			$id = $row['id']; $caption = stripslashes($row['caption']);
			$tmp[$id] = array('caption' => $caption, 'subdirs' => array());
			$tmp[$id]['subdirs'] = $this->list_subdirs($id);
		}
		return $tmp;
	}
	
	function print_subdirs($d,$indent) {
		$output = "";
		foreach ($d as $n => $i) {
			$output .= "$indent<img src=\"".$this->image_dir."folder.gif\" /> ".$i['caption']."<br />";
			$output .= $this->print_subdirs($i['subdirs'],"&nbsp;&nbsp;&nbsp;&nbsp;$indent");
		}
		return $output;
	}

	/*******************************************************************************************
		 Move directory                                                               
		 **************************************************************************************/
	
	
	function import() {	
	
		$tropp_page_id = 82;
		$flokk_page_id = 83;
	
		$res = $this->query(
			"SELECT 
				id
			FROM 
				imgarchive_dirs_tropp
			WHERE directory=\"/flokk/\""
		);
		$row = $res->fetch_assoc(); 
		$flokk_dir_id = $row['id'];

		$res = $this->query(
			"SELECT 
				*
			FROM 
				imgarchive_dirs_flokk"
		);
		$dir_tab = array();
		while ($row = $res->fetch_assoc()) {
			if ($row['parentdir'] != 0) {
				$dir = "/flokk".$row['directory'];
				$this->query("INSERT INTO imgarchive_dirs_tropp (parentdir,directory) VALUES ($flokk_dir_id, \"$dir\")");
				$new_id = $this->insert_id();
				$dir_tab[$row['id']] = $new_id;
				$this->query("UPDATE imgarchive_dirs_tropp SET thumbdir=$new_id WHERE id=$new_id");
				foreach ($row as $n => $v) {
					if ($n != "id" && $n != "parentdir" && $n != "directory" && $n != "thumbdir") $this->query("UPDATE imgarchive_dirs_tropp SET $n=\"$v\" WHERE id=$new_id");
				}
			}
		}
		
		$res = $this->query(
			"SELECT 
				id,directory
			FROM 
				imgarchive_files_flokk"
		);
		while ($row = $res->fetch_assoc()) {
			$id = $row['id'];
			$old_dir = $row['directory'];
			$new_dir = $dir_tab[$old_dir];
			$this->query("UPDATE imgarchive_files_flokk SET directory=$new_dir WHERE id=$id");			
		}
		
		$res = $this->query(
			"SELECT 
				*
			FROM 
				imgarchive_files_flokk"
		);
		$files_tab = array();
		while ($row = $res->fetch_assoc()) {
			$this->query("INSERT INTO imgarchive_files_tropp (filename) VALUES (\"".$row['filename']."\")");
			$new_id = $this->insert_id();
			$files_tab[$row['id']] = $new_id;
			foreach ($row as $n => $v) {
				if ($n != "id" && $n != "filename") $this->query("UPDATE imgarchive_files_tropp SET $n=\"$v\" WHERE id=$new_id");
			}
		}
		
		// Imgtags:
		$res = $this->query(
			"SELECT 
				id,img,imgtab
			FROM 
				imgtags
			WHERE imgtab=$flokk_page_id"
		);
		while ($row = $res->fetch_assoc()) {
			$id = $row['id'];
			$old_img = $row['img'];
			$new_img = $files_tab[$old_img];
			$imgtab = $tropp_page_id;
			$this->query("UPDATE imgtags SET img=$new_img, imgtab=$imgtab WHERE id=$id");			
		}
		
		// Log:
		$res = $this->query(
			"SELECT 
				id,imagearchive_id
			FROM 
				log_global
			WHERE log_page=115 AND imagearchive_id != 0"
		);
		while ($row = $res->fetch_assoc()) {
			$id = $row['id'];
			$old_img = $row['imagearchive_id'];
			$new_img = $dir_tab[$old_img];
			$this->query("UPDATE log_global SET imagearchive_id=$new_img WHERE id=$id");			
		}
		
		// Comments:
		$res = $this->query(
			"SELECT 
				*
			FROM 
				imgarchive_comments_flokk"
		);
		while ($row = $res->fetch_assoc()) {
			$new_parent = $files_tab[$row['parent']];
			$this->query("INSERT INTO imgarchive_comments_tropp (parent) VALUES ($new_parent)");
			$new_id = $this->insert_id();
			foreach ($row as $n => $v) {
				if ($n != "id" && $n != "parent") $this->query("UPDATE imgarchive_comments_tropp SET $n=\"$v\" WHERE id=$new_id");
			}
		}
		
	}
	
	function moveAlbum(){

		if (!$this->allow_editarchivesettings) return $this->permissionDenied();
		$albumId = intval($this->current_directory);
		if ($albumId <= 0) return "Invalid album";
		
		$path = $this->generatePath($albumId);
		$output = "<h3>Flytt albumet «".$path[1]."»</h3>";
		$tf = $this->table_files; $td = $this->table_dirs;
		$res = $this->query(
			"SELECT count($tf.id) as image_count, $td.caption, $td.directory
			FROM $td LEFT JOIN $tf ON ($tf.directory=$td.id AND $tf.deletereason='')
			WHERE $td.id=$albumId GROUP BY $td.id"
		);
		if ($res->num_rows != 1) $this->fatalError("[imagearchive] The entry does not exist!");
		$row = $res->fetch_assoc();
		$archivecaption = stripslashes($row['caption']);
		$imgcount = $row['image_count'];
		$dirpath = $row['directory'];
		
		$me = intval($this->login_identifier);
		$res = $this->query(
			"SELECT id FROM $tf WHERE directory=$albumId AND deletereason='' AND NOT (uploadedby=$me)");
		$notyours = $res->num_rows;
		if ($notyours > 0){
			if (!$this->allow_deleteothersarchives){
				$output .= "
					<p class='warning'>
					Beklager, du har ikke tilgang til å flytte dette albumet siden det inneholder 
					$notyours bilder lastet opp av andre brukere enn deg. Ta kontakt med webmaster 
					om du har gode grunner for å ønske å flytte den.
					</p>
				";
			}
		}

		$subdirs = $this->list_subdirs($albumId);
		if (!empty($subdirs)) {
			$output .= "<p class='warning'>NB! Arkivet inneholder underarkiv, som også vil bli flyttet:<br />";
			$output .= "<img src=\"".$this->image_dir."folder.gif\" /><strong> ".$archivecaption."</strong><br />";
			$output .= $this->print_subdirs($subdirs,"&nbsp;&nbsp;&nbsp;&nbsp;");
			$output .= "</p>";
		}

		$allow_delete = ($this->allow_deleteothersarchives || ($this->allow_deleteownarchives && ($notyours == 0)));
		if (!$allow_delete) return $this->permissionDenied();
		
		if ($imgcount > 0){
			$output .= "
				<p class='info'>
					Dette albumet inneholder $imgcount bilder (hvorav $notyours er lastet 
					opp av andre enn deg).
				</p>
			";
		}
		$output .= "
			<form action=\"".$this->generateURL('action=moveAlbumDo')."\" method='post'>
				
				<strong>Hvor vil du flytte dette albumet?</strong><br />
				
				<div class='alpha-shadow'>
					<div class='inner_div'>
						<img src=\"".$this->getUrlTo($dirpath,'thumbnail')."\" style='width:200px; height:150px;' />
					</div>
				</div>
				<div style='clear:both;text-align:center'></div>

				
				<br /><br />
				<select name='movetodir'>
					".$this->makeDirTree(1,"",1,-1,true)."
				</select>

				<input type=\"submit\" name=\"btn_yes\" value=\"    $this->label_move    \" /> 
				<input type=\"button\" name=\"btn_no\" value=\"    $this->label_cancel    \" onclick='window.location=\"".$this->generateURL("")."\"' />
			</form>";
		
		return $output;
	}
	
	function moveAlbumDo(){
	
		if (!$this->allow_editarchivesettings) return $this->permissionDenied(); 	

		$source_id = $this->current_directory;
		$dest_id = $_POST['movetodir'];
		if (!is_numeric($source_id)) $this->fatalError("[imagearchive] Invalid input (5)!");
		if (!is_numeric($dest_id)) $this->fatalError("[imagearchive] Invalid input (5)!");
		
		/* __________ Gather information from DB __________ */

			$res = $this->query(
				"SELECT 
					$this->table_dirs.$this->table_dirs_field_caption as caption,
					$this->table_dirs.$this->table_dirs_field_directory as directory,
					$this->table_dirs.$this->table_dirs_field_parentdir as parentdir
				FROM 
					$this->table_dirs
				WHERE
					$this->table_dirs.$this->table_dirs_field_id='$source_id'"
			);
			if ($res->num_rows != 1) $this->fatalError("[imagearchive] The entry does not exist!");
			$row = $res->fetch_assoc();
	
			$source_caption = stripslashes($row['caption']);
			$source_parent = $row['parentdir'];
			$source_dir = $row['directory'];
	
			$res = $this->query(
				"SELECT 
					$this->table_dirs.$this->table_dirs_field_caption as caption,
					$this->table_dirs.$this->table_dirs_field_directory as directory,
					$this->table_dirs.$this->table_dirs_field_parentdir as parentdir
				FROM 
					$this->table_dirs
				WHERE
					$this->table_dirs.$this->table_dirs_field_id='$dest_id'"
			);
			if ($res->num_rows != 1) $this->fatalError("[imagearchive] The entry does not exist!");
			$row = $res->fetch_assoc();
	
			$dest_caption = stripslashes($row['caption']);
			$dest_parent = $row['parentdir'];
			$dest_dir = $row['directory'];
			$tmp = explode("/",$source_dir);
			$dest_dir_full = $row['directory'].$tmp[count($tmp)-2]."/";

		/* __________ Check if archive contain files and permissions for these __________ */

			$res = $this->query(
				"SELECT 
					$this->table_files.$this->table_files_field_id 
				FROM 
					$this->table_files
				WHERE 
					$this->table_files_field_directory='$source_id' 
					AND NOT $this->table_files_field_uploader='$this->login_identifier'"
			);
			$notyours = $res->num_rows;
			$allow_delete = ($this->allow_deleteothersarchives || ($this->allow_deleteownarchives && ($notyours == 0)));
			if (!$allow_delete) return $this->permissionDenied();
		
		/* __________ Move album physically __________ */
		
			$oldname = $this->root_path.$this->hi_res_dir.rtrim($source_dir,"/");
			$newname = $this->root_path.$this->hi_res_dir.rtrim($dest_dir_full,"/");
						
			if (strpos($newname,$oldname) !== false) 
				$this->fatalError("Du kan ikke flytte albumet til et album inni seg selv!");
			if (file_exists($newname)) 
				$this->fatalError("Det eksisterer allerede et album med det samme filnavnet i mappen du ønsker å flytte til.");			
							
			if(!rename($oldname,$newname)) 
				$this->fatalError("[imagearchive] Det oppstod en feil under flyttingen av albumet \"".addslashes($source_caption)."\" fra parentdir:$source_parent. Dette kan komme av at arkivet allerede er flyttet, f.eks. hvis du trykket to ganger på \"Ja\".");

		/* __________ Move thumbs as well __________ */

			$oldname = $this->root_path.$this->lo_res_dir.rtrim($source_dir,"/");
			$newname = $this->root_path.$this->lo_res_dir.rtrim($dest_dir_full,"/");
							
			if(!rename($oldname,$newname)) 
				$this->fatalError("[imagearchive] Det oppstod en feil under flyttingen av albumet \"".addslashes($source_caption)."\" fra parentdir:$source_parent. Dette kan komme av at arkivet allerede er flyttet, f.eks. hvis du trykket to ganger på \"Ja\".");

			$oldname = $this->root_path.$this->mid_res_dir.rtrim($source_dir,"/");
			$newname = $this->root_path.$this->mid_res_dir.rtrim($dest_dir_full,"/");
							
			if(!rename($oldname,$newname)) 
				$this->fatalError("[imagearchive] Det oppstod en feil under flyttingen av albumet \"".addslashes($source_caption)."\" fra parentdir:$source_parent. Dette kan komme av at arkivet allerede er flyttet, f.eks. hvis du trykket to ganger på \"Ja\".");
			

		/* __________ Update database __________ */
		
			$this->query("UPDATE $this->table_dirs SET 
				$this->table_dirs_field_parentdir='$dest_id',
				$this->table_dirs_field_directory='$dest_dir_full'
				WHERE id='$source_id'");	
			$this->updateChildArchives($source_id,$source_dir,$dest_dir_full);

		/* __________ Write to activitylog and redirect __________ */

			$this->addToActivityLog("flyttet albumet <a href=\"".$this->generateCoolURL($dest_dir_full)."\">$source_caption</a> til <a href=\"".$this->generateCoolURL($dest_dir)."\">$dest_caption</a>");
			$this->redirect($this->generateCoolURL($dest_dir),"Mappen ble flyttet");
		
	}
	
	
	/*******************************************************************************************
		 Delete archive                                                               
		 **************************************************************************************/


	function deleteAlbum(){

		if (!$this->allow_editarchivesettings) return $this->permissionDenied(); 	

		$albumId = intval($this->current_directory);
		if ($albumId <= 0) return "Invalid album";

		$path = $this->generatePath($albumId);
		$output = "<h3>Slett albumet «".$path[1]."»</h3>";
		
		$tf = $this->table_files;
		$td = $this->table_dirs;
		$res = $this->query(
			"SELECT count($tf.id) as image_count, $td.caption, $td.directory
			FROM $td LEFT JOIN $tf ON ($tf.directory=$td.id AND $tf.deletereason='')
			WHERE $td.id=$albumId GROUP BY $td.id"
		);
		if ($res->num_rows != 1) $this->fatalError("[imagearchive] The entry does not exist!");
		$row = $res->fetch_assoc();
		$archivecaption = stripslashes($row['caption']);
		$imgcount = $row['image_count'];
		
		$me = intval($this->login_identifier);
		$res = $this->query(
			"SELECT id FROM $tf WHERE directory=$albumId AND deletereason='' AND NOT (uploadedby=$me)");
		$notyours = $res->num_rows;
		if ($notyours > 0){
			if (!$this->allow_deleteothersarchives){
				$output .= "<p>Beklager, du har ikke tilgang til å slette dette albumet siden det inneholder $notyours bilder lastet opp av andre brukere enn deg. Ta kontakt med webmaster om du har gode grunner for å ønske å slette det.</p>";
			}
		}

		if ($this->subdirs->num_rows > 0) return '<p class="warning">
			Albumet «'.$archivecaption.'» inneholder '.$this->subdirs->num_rows.' underalbum. 
			For å hindre utilsiktet sletting av bilder, krever vi at du sletter 
			alle underalbum før du kan slette dette albumet.<br /><br />
			<a href="'.$this->generateURL('').'">Tilbake til albumet</a></p>';

		$allow_delete = ($this->allow_deleteothersarchives || ($this->allow_deleteownarchives && ($notyours == 0)));
		if ($allow_delete){
			if ($imgcount > 0){
				$output .= "<p class='warning'>";
				$output .= str_replace("%count%",$imgcount,$this->deletenonemptyarchive_caption);
				if ($notyours > 0){
					$output .= " Av disse er $notyours lastet opp av andre brukere enn deg!";
				}
				$output .= "</p>";
			}
			$output .= '
				<form action="'.$this->generateURL('action=deleteAlbumDo').'" method="post">
					Er du sikker på at du ønsker å slette albumet "<strong>'.$archivecaption.'</strong>"?<br /><br />
					Skriv gjerne inn årsaken til at du sletter albumet her:
					<input type="text" name="delete_reason" style="width:450px;" /><br /><br />

					<input type="submit" name="btn_yes" value="    '.$this->label_yes.'    " /> 
					<input type="button" name="btn_no" value="    '.$this->label_no.'    " onclick=\'window.location="'.$this->generateURL('').'"\' />
				</form>';
		} else {
			$output .= $this->permissionDenied();
		}
		return $output;
	}

	function deleteAlbumDo(){

		if (!$this->allow_editarchivesettings) return $this->permissionDenied();
		$albumId = intval($this->current_directory);
		if ($albumId <= 0) return "Invalid album";
		$cd = $this->current_directory_details;

		$tf = $this->table_files;
		$td = $this->table_dirs;

		$res = $this->query("SELECT caption,directory,parentdir FROM $td WHERE id=$albumId");
		if ($res->num_rows != 1) $this->fatalError("[imagearchive] The entry does not exist!");
		$row = $res->fetch_assoc();

		$archivecaption = stripslashes($row['caption']);
		$parentdir = $row['parentdir'];
		$directory = $row['directory'];

		$me = intval($this->login_identifier);
		$res = $this->query(
			"SELECT id FROM $tf WHERE directory=$albumId AND deletereason='' AND NOT (uploadedby=$me)");
		$notyours = $res->num_rows;

		$allow_delete = ($this->allow_deleteothersarchives || ($this->allow_deleteownarchives && ($notyours == 0)));
		if (!$allow_delete) return $this->permissionDenied();

		$exceptions = array(".", "..");
		if(!$this->deleteFiles($this->root_path.$this->hi_res_dir.rtrim($directory,"/"), $exceptions, false)){
			print $this->notSoFatalError("[imagearchive] Det oppstod en feil under slettingen av albumet \"".addslashes($archivecaption)."\" fra parentdir:$parentdir. Dette kan komme av at arkivet allerede er slettet, f.eks. hvis du trykket to ganger på \"Ja\".");
		}
		$this->deleteFiles($this->root_path.$this->mid_res_dir.rtrim($directory,"/"), $exceptions, false);
		$this->deleteFiles($this->root_path.$this->lo_res_dir.rtrim($directory,"/"), $exceptions, false);
		
		$delete_reason1 = (isset($_POST['delete_reason']) ? str_replace("|"," ",strip_tags($_POST['delete_reason'])) : "");
		$delete_reason = addslashes($this->login_identifier."|".time()."|".$delete_reason1);
		$this->query("UPDATE $td SET deletereason=\"$delete_reason\",cal_id=0,event_id=0 WHERE id=$albumId");	
		$this->query("UPDATE $tf SET deletereason=\"$delete_reason\" WHERE directory='$albumId'");	
		
		array_pop($tmpDir = explode("/",trim($directory,"/")));
		$parentdir = "/".implode("/",$tmpDir);
		
		$delete_reason2 = (empty($delete_reason1) ? "" : ". Årsak: $delete_reason1");
		$this->addToActivityLog("slettet albumet <a href=\"".$this->generateURL("")."\">$archivecaption</a>$delete_reason2");
		$this->redirect($this->generateCoolURL($parentdir),"Albumet ble slettet");
	}

	function deleteFiles($target, $exceptions, $output=true){
		$sourcedir = opendir($target);
		while(false !== ($filename = readdir($sourcedir))){
			if(!in_array($filename, $exceptions)){
				if($output){ echo "Processing: ".$target."/".$filename."<br>"; }
				if(is_dir($target."/".$filename)){
					// recurse subdirectory; call of function recursive
					$this->deleteFiles($target."/".$filename, $exceptions,$output);
				} else if(is_file($target."/".$filename)){
					// unlink file
					unlink($target."/".$filename);
				}
			}
		}
		closedir($sourcedir);
		if(rmdir($target)){ return true; } else { return false; }
	}
	
	function updateChildArchives($dir,$oldstr,$newstr){			
		$res = $this->query(
			"SELECT 
				$this->table_dirs_field_id as id,
				$this->table_dirs_field_directory as directory
			FROM 
				$this->table_dirs
			WHERE
				$this->table_dirs_field_parentdir = '$dir'"
		);
		while ($row = $res->fetch_assoc()) {
			$id = $row['id'];
			$dir = $row['directory'];
			$ndir = str_replace($oldstr,$newstr,$dir);
			//print "<div>$dir -> $ndir</div>";
			
			$this->query("UPDATE $this->table_dirs 
				SET
					directory='$ndir'
				WHERE id='$id'"
			);
			
			$this->updateChildArchives($id,$oldstr,$newstr);
		}			
	}

	/*******************************************************************************************
		 Rotate single photo                                                               
		 **************************************************************************************/
	
	/*
	    Function: rotatePhoto
	    Rotates the image by 'degrees' degrees.
	*/
	function rotatePhoto($degrees) {

		if (!$this->allow_organizeimages) return $this->permissionDenied(); 	

		$photoId = intval($this->current_image);
		if ($photoId <= 0) return "Invalid photo";

		$cd = $this->current_directory_details;
		$tf = $this->table_files; $td = $this->table_dirs;
		$res = $this->query("SELECT uploadedby,filename,directory FROM $tf WHERE id=$photoId");
		if ($res->num_rows != 1) $this->fatalError("[imagearchive] The entry does not exist!");
		$row = $res->fetch_assoc();
		$albumId = intval($row['directory']);
		$albumDir = $cd['directory'];
		if ($albumId != $cd['id']){ print $this->fatalError("album not correctly identified!"); exit(); }		
		$fileName = $row['filename'];
		

		switch($degrees) {
			case 90: case 180: case 270: 
				break;
			case 0: case 360:
				$this->redirect($this->generateURL(""),"Hva var vitsen med dette?");
				exit();
			default: 
				print "Invalid rotation angle";
				exit();
		}

		$original_path = $this->getPathTo($albumDir,$fileName);
		$medium_path = $this->getPathTo($albumDir,$fileName,'medium');
		$small_path = $this->getPathTo($albumDir,$fileName,'small');
		
		// First we try ImageMagick:
		exec('convert -rotate -'.$degrees.' '.escapeshellarg($original_path).' '.escapeshellarg($original_path).' 2>&1',$out,$ret);
		if ($ret != '0') {
			// print "[$err] $out[0]";

			// ImageMagick not found. Let's try GD:
			$img = imagecreatefromjpeg($original_path);
			$width = imagesx($img);
			$height = imagesy($img);
			$newimg= @imagecreatetruecolor($height , $width );
			if (function_exists('imagerotate')) {
				$newimg = imagerotate($img,intval($degrees), 0);
			} else {				
				// We have to do a manual rotation. This is quite slow...
				$width = imagesx($img);
				$height = imagesy($img);
				switch($degrees) {
					case 90: case 180: case 270: 
						$newimg= @imagecreatetruecolor($height , $width );
						break;
					case 0: case 360:
						$this->redirect($this->generateURL(""),"Hva var vitsen med dette?");
						exit();
					default: 
						print "Invalid rotation angle";
						exit();
				}

				for($i = 0;$i < $width ; $i++) { 
					for($j = 0;$j < $height ; $j++) {
						$reference = imagecolorat($img,$i,$j);
						switch($degrees) {
							case 270:  if(!@imagesetpixel($newimg, ($height - 1) - $j, $i, $reference )){return false;}break;
							case 180: if(!@imagesetpixel($newimg, $width - $i, ($height - 1) - $j, $reference )){return false;}break;
							case 90: if(!@imagesetpixel($newimg, $j, $width - $i, $reference )){return false;}break;
						}
					}
				} 
			}
			imagejpeg($newimg,$original_path);
		}
	
		ThumbnailService::createThumb($original_path, $medium_path, 490, 490);
		ThumbnailService::createThumb($original_path, $small_path, 140, 140);
		
		list($width, $height, $type, $attr) = getimagesize($small_path);

		$res = $this->query(
			"UPDATE $this->table_files 
				SET visible=1, thumb_width=$width, thumb_height=$height
				WHERE id=$photoId"
		);

		$this->redirect($this->generateURL(""),"Bildet ble rotert. Last siden på nytt hvis du ikke ser noen forskjell.");
			
	}
	
	/*
		Function: rotateLeft
	    Rotates the image 90 degrees anti-clockwise
	*/
	function rotateLeft() {
		return $this->rotatePhoto(90);
	}

	/*
		Function: rotateRight
	    Rotates the image 90 degrees clockwise
	*/
	function rotateRight() {
		return $this->rotatePhoto(270);
	}
	
	/*******************************************************************************************
		 Move single photo                                                               
		 **************************************************************************************/
	
	function movePhoto(){

		$photoId = intval($this->current_image);
		if ($photoId <= 0) return "Invalid photo";

		$path = $this->generatePath($this->current_directory); // Needed for breadcrumb
		$output = "<h3>Flytt enkelt-bilde</h3>";
		$tf = $this->table_files; $td = $this->table_dirs;
		$res = $this->query(
			"SELECT $tf.uploadedby,$tf.filename,$td.directory
			FROM $tf,$td WHERE $tf.id=$photoId AND $tf.directory=$td.id"
		);
		if ($res->num_rows != 1) $this->fatalError("[imagearchive] The entry does not exist!");
		$row = $res->fetch_assoc();
		
		$allow_delete = ($this->allow_deleteothersimages || ($this->allow_deleteownimages && ($row['uploadedby']==$this->login_identifier)));
		if ($allow_delete){

			if ($row['uploadedby'] != $this->login_identifier){
				$output .= "<p class='notice'>".$this->othercreatornotice_caption."</p>";
			}
			
			$output .= '
				<form action="'.$this->generateURL('action=movePhotoDo').'" method="post">
					Hvor vil du flytte dette bildet?<br /><br />
					<img src="'.$this->getUrlTo($row["directory"],$row["filename"],"small").'" />
					<br /><br />
					<select name="movetodir">
					'.$this->makeDirTree(1,'',1,-1,false).'
					</select>
					<br /><br />
					<input type="submit" name="btn_yes" value="    '.$this->label_move.'    " /> 
					<input type="button" name="btn_no" value="    '.$this->label_cancel.'    " onclick=\'window.location="'.$this->generateURL('').'"\' />
				</form>';
		} else {
			$output .= $this->permissionDenied();
		}
		return $output;
	}
	
	function movePhotoDo(){

		$photoId = intval($this->current_image);
		if ($photoId <= 0) return "Invalid photo";

		$cd = $this->current_directory_details;
		$tf = $this->table_files; $td = $this->table_dirs;
		$res = $this->query("SELECT uploadedby,filename,directory FROM $tf WHERE id=$photoId");
		if ($res->num_rows != 1) $this->fatalError("[imagearchive] The entry does not exist!");
		$row = $res->fetch_assoc();
		$fromAlbumId = intval($row['directory']);
		if ($fromAlbumId != $cd['id']){ print $this->fatalError("album not correctly identified!"); exit(); }
		
		$fileName = $row['filename'];
		$allow_delete = ($this->allow_deleteothersimages || ($this->allow_deleteownimages && ($row['uploadedby']==$this->login_identifier)));
		if (!$allow_delete) return $this->permissionDenied();

		$toAlbumId = intval($_POST['movetodir']);
		if ($toAlbumId <= 0) $this->fatalError("[imagearchive] Invalid input (6)!");

		$fromAlbumDir = $cd['directory'];
		$fromAlbumThumbnail = $cd['thumbnail'];

		$res2 = $this->query("SELECT directory as todir FROM $td WHERE id=$toAlbumId");
		if ($res2->num_rows != 1) $this->fatalError("Mottaksarkivet eksisterer ikke");
		$row2 = $res2->fetch_assoc();
		$toAlbumDir = $row2['todir'];
				
		// Move lo-res thumbnail:
		rename(
			$this->getPathTo($fromAlbumDir,$fileName,'small'), 
			$this->getPathTo($toAlbumDir,$fileName,'small')
		);
		// Move mid-res thumbnail:
		rename(
			$this->getPathTo($fromAlbumDir,$fileName,'medium'), 
			$this->getPathTo($toAlbumDir,$fileName,'medium')
		);
		// Move original image:
		rename(
			$this->getPathTo($fromAlbumDir,$fileName), 
			$this->getPathTo($toAlbumDir,$fileName)
		);

		if ($fromAlbumThumbnail == $photoId){
			$this->query("UPDATE $td SET thumbnail=NULL WHERE id=$fromAlbumId");
			unlink($this->getPathTo($fromAlbumDir,'thumbnail'));
		}

		$this->query("UPDATE $tf SET directory='$toAlbumId' WHERE id=$photoId");	
		$this->addToActivityLog("flyttet bildet $photoId fra $fromAlbumDir til $toAlbumDir.");
        
        // Update the photo count database
		$this->updatePhotoCounts();
        
        $this->redirect($this->generateCoolURL($toAlbumDir),"Bildet ble flyttet");
	}

	/*******************************************************************************************
		 Delete single photo                                                               
		 **************************************************************************************/

	function deletePhoto(){

		$photoId = intval($this->current_image);
		if ($photoId <= 0) return "Invalid photo";

		$output = "<h3>Bekreft sletting</h3>";
		$res = $this->query(
			"SELECT 
				$this->table_files.$this->table_files_field_uploader as uploader,
				$this->table_files.$this->table_files_field_filename as filename,
				$this->table_dirs.$this->table_dirs_field_directory as directory
			FROM 
				$this->table_files,$this->table_dirs
			WHERE
				$this->table_files.$this->table_files_field_id=$photoId
				AND $this->table_files.$this->table_files_field_directory=$this->table_dirs.$this->table_dirs_field_id"
		);
		if ($res->num_rows != 1) $this->fatalError("[imagearchive] The entry does not exist!");
		$row = $res->fetch_assoc();
		
		$allow_delete = ($this->allow_deleteothersimages || ($this->allow_deleteownimages && ($row['uploader']==$this->login_identifier)));
		if (!$allow_delete){
			$output .= $this->permissionDenied();
			return $output;
		}
		if ($row['uploader'] != $this->login_identifier){
			$output .= "<p class='notice'>".$this->othercreatornotice_caption."</p>";
		}
		$output .= '
			<form action="'.$this->generateURL('action=deletePhotoDo').'" method="post">
				Er du sikker på at du ønsker å slette dette bildet?<br /><br />
				<img src="'.$this->getUrlTo($row["directory"],$row["filename"],"small").'" />
				<br /><br />
				Om du ønsker det kan du skrive inn en grunn til at du sletter det her:
				<input type="text" name="delete_reason" style="width:450px;" /><br /><br />

				<input type="submit" name="btn_yes" value="    '.$this->label_yes.'    " /> 
				<input type="button" name="btn_no" value="    '.$this->label_no.'    " onclick=\'window.location="'.$this->generateURL('').'"\' />
			</form>';
		return $output;
	}

	function deletePhotoDo(){

		$photoId = intval($this->current_image);
		if ($photoId <= 0) return "Invalid photo";

		$res = $this->query(
			"SELECT 
				$this->table_files_field_uploader as uploader,
				$this->table_files_field_directory as directory,
				$this->table_files_field_uploader as uploader
			FROM 
				$this->table_files
			WHERE
				$this->table_files_field_id=$photoId"
		);
		if ($res->num_rows != 1) $this->fatalError("[imagearchive] The entry does not exist!");
		$row = $res->fetch_assoc();
		$res2 = $this->query(
			"SELECT 
				$this->table_dirs_field_caption as caption,
				$this->table_dirs_field_thumbnail as thumbnail,
				$this->table_dirs_field_directory as directory
			FROM 
				$this->table_dirs
			WHERE
				$this->table_dirs_field_id='".$row['directory']."'"
		);
		if ($res2->num_rows != 1) $this->fatalError("[imagearchive] The entry does not exist 2!");
		$row2 = $res2->fetch_assoc();
		$dir_caption = stripslashes($row2['caption']);
		$dirpath = $row2['directory'];

		$allow_delete = ($this->allow_deleteothersimages || ($this->allow_deleteownimages && ($row['uploader']==$this->login_identifier)));
		if (!$allow_delete) { print $this->permissionDenied(); exit(); }
		if ($row2['thumbnail'] == $photoId){
			$this->query("UPDATE $this->table_dirs SET $this->table_dirs_field_thumbnail=NULL WHERE $this->table_dirs_field_id='".$row['directory']."'");
			unlink($this->getPathTo($dirpath,'thumbnail'));
		}
		$delete_reason1 = (isset($_POST['delete_reason']) ? str_replace("|"," ",strip_tags($_POST['delete_reason'])) : "");
		$delete_reason = addslashes($this->login_identifier."|".time()."|".$delete_reason1);
		$this->query("UPDATE $this->table_files SET $this->table_files_field_deletereason='$delete_reason' WHERE $this->table_files_field_id=$photoId");	

		$delete_reason2 = (empty($delete_reason1) ? "" : ". Årsak: $delete_reason1");

		// Update the photo count database
		$this->updatePhotoCounts();

		// Write to eventlog
		$this->addToActivityLog("slettet <a href=\"".$this->generateURL("")."\">et bilde</a> fra albumet \"$dir_caption\"$delete_reason2");		

		// Redirect
		$this->redirect($this->generateURL(""),"Bildet ble slettet");
	}


	/*******************************************************************************************
		 Add new archive                                                               
		 **************************************************************************************/


	function addAlbum(){
		$parent = intval($this->current_directory);
		if ($parent <= 0) $this->fatalError("[imagearchive] Invalid input!");
		if  (!$this->allow_addarchives) return $this->permissionDenied(); 
		$res = $this->query("SELECT $this->table_dirs_field_caption as caption FROM $this->table_dirs WHERE $this->table_dirs_field_id='$parent'");
		if ($res->num_rows != 1) $this->fatalError("[imagearchive] Foreldrearkivet finnes ikke!");
		$row = $res->fetch_assoc();
		$parentcaption = stripslashes($row['caption']);
		
		if ($this->enable_calendar_functions){
		
			$calendars = $this->listCalendars();
			$cal_opts = "";
			$cal_opts .= "<option value='-'>[ Ingen hendelse ]</option>\n";
			foreach ($calendars as $c) {
				$cal_opts .= "<option value='".$c['id']."'>".stripslashes($c['header'])."</option>\n";
			}

			$events_uri = $this->generateUrl('action=ajaxFetchEvents');
			
		}
		
		$path = $this->generatePath($this->current_directory);
		$url = ROOT_DIR.rtrim($this->generateUrl(''),'/').'/';
		return '
			<script type="text/javascript">
			//<![CDATA[
				
				function fetch_events() {
					var url = "'.$events_uri.'";
					var pars = new Array();
					pars.push("cal_page_id="+$("cal_id").value);
					pars = pars.join("&");
					var success = function(t){ 
						setText("span_event",t.responseText);
					}
					setText("span_event", "Vent litt...");
					var myAjax = new Ajax.Request(url, {method: "post", parameters: pars, onSuccess:success});
				}
								
				function generateSlug(tittel){
					tittel = tittel.toLowerCase();
					tittel = tittel.replace(/\s/g, "-");
					tittel = tittel.replace(/ø/g, "o");
					tittel = tittel.replace(/å/g, "a");
					tittel = tittel.replace(/æ/g, "ae");
					re = /[^\w-]/g;  // \w is a shorthand for A-Za-z0-9_
					tittel = tittel.replace(re, "");
					return tittel;
				}

				function onSubjectChange(e) {
					$("article_slug").value = generateSlug($("article_caption").value);
				}
				
				YAHOO.util.Event.onDOMReady(function() {
					YAHOO.util.Event.addListener("article_caption","keyup",onSubjectChange);
				});
			
			//]]>
			</script>
			<h3>Opprett nytt album</h3>
			<form method="post" action="'.$this->generateURL('action=addAlbumDo').'">
				<table>
					<tr>
						<th align="right">Navn: </th>
						<td><input type="text" id="article_caption" name="caption"  value="" style="width:250px;" /></td>
					</tr>
					<tr>
						<th align="right">Url:</th>
						<td style="font-size:10px;">'.$url.'<input id="article_slug" name="slug" type="text" value="" style="width:150px;" /></td>
					</tr>
					'.($this->enable_calendar_functions ? '
						<tr>
							<th align="right">Knytt til hendelse: </th>
							<td>
								<select name="cal_id" id="cal_id" onchange="fetch_events();" style="font-size:90%; border: 1px solid #666666;">'.$cal_opts.'</select>
								<span id="span_event"></span>
							</td>
						</tr>
					':'').'
				</table>
				<br />
				<input type="submit" value="    Legg til    " />
			</form>
		';
	}

	function addAlbumDo(){

		$parentDirId = intval($this->current_directory);

		// ===== Check permission: =====
			if  (!$this->allow_addarchives) return $this->permissionDenied();
		
		// ===== Verify input: =====
			if (!is_numeric($parentDirId)) $this->fatalError("[imagearchive] Invalid input!");
			$parentDirId = intval($parentDirId);
			$res = $this->query("SELECT directory FROM $this->table_dirs WHERE id=$parentDirId");
			if ($res->num_rows != 1) $this->fatalError("[imagearchive] Foreldrearkivet finnes ikke!");
			$row = $res->fetch_assoc();
			$parentDirPath = stripslashes($row['directory']);
			if (empty($_POST['caption'])) $this->fatalError("[imagearchive] Du må fylle inn arkivnavn. Gå tilbake og prøv på nytt!");
			$caption = addslashes($_POST['caption']);			
			$cal_id = $event_id = 0;
			if ($this->enable_calendar_functions) {
				$cal_id = intval($_POST['cal_id']);
				$event_id = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;
			}
			$slug = addslashes($_POST['slug']);
			$slug = mb_strtolower($slug,'UTF-8');
			$slug = str_replace(array("ø","æ","å"," ","--"),array("o","ae","a","-","-"),$slug);	
			$slug = preg_replace('/[^\w-]/','',$slug);
			$slug = trim($slug,"-");
			$dirname = $slug;

			//$dirname = preg_replace("/(\W+)/","",$dirname);
			if (empty($dirname)) $this->fatalError("[imagearchive] Du har skrevet inn et arkivnavn som kun består av 'usikre' tegn! Prøv på nytt. Du må minst ha et tegn av typen a-z, 0-9, eller -,_");
			$directory = $parentDirPath.$dirname."/";
			$orig_path = $this->getPathTo($parentDirPath.$dirname,'');
			$mid_path = $this->getPathTo($parentDirPath.$dirname,'','medium');
			$lo_path = $this->getPathTo($parentDirPath.$dirname,'','small');
					
		// ===== Check if the directory already exists: =====
			if (file_exists($orig_path)) 
              $this->fatalError("[imagearchive] Mappen $directory eksisterer allerede! Antakelig finnes arkivet allerede. Hvis ikke må du gå tilbake og prøve med et nytt arkivnavn.");

        // ===== Check if the directory exists in the database: =====
        
            $res = $this->query("SELECT id FROM $this->table_dirs WHERE directory=\"$directory\"");
            $albumId = 0;
            if ($res->num_rows == 0) {
                $dbEntryExists = false;
            } else if ($res->num_rows == 1) {
              $dbEntryExists = true;
              $row = $res->fetch_assoc();
              $albumId = intval($row['id']);
            } else {
                $this->fatalError("Mer enn én kopi av albumet eksisterer i databasen!");
            }
		
		// ===== Create directories: =====
			foreach (array($orig_path,$mid_path,$lo_path) as $d) {
				if(!mkdir($d,0755,true)){ 
					$this->addToErrorLog("Albumet kunne ikke opprettes fordi det ikke gikk å opprette mappen \"".$d."\"!"); 
					$this->fatalError("Albumet kunne ikke opprettes fordi det ikke gikk å opprette mappen $d. <br />
						Antakelig er det feil i rettighetsinnstillingene på serveren. Ta kontakt med webmaster.");
				}
			}

		// ===== Add database entry: =====
			$res = $this->query("SELECT MAX(position) FROM $this->table_dirs WHERE parentdir='$parentDirId'");
			$row = $res->fetch_row();
            $maxpos = intval($row[0] + 1);
            if ($dbEntryExists) {
                $this->query(
                  "UPDATE $this->table_dirs SET
                    caption=\"$caption\",
                    position=$maxpos,
                    cal_id=$cal_id,
                    event_id=$event_id,
                    deletereason=\"\"
                    WHERE id=$albumId"
                );

            } else {
                $this->query(
                  "INSERT INTO $this->table_dirs 
                      (parentdir,directory,caption,position,cal_id,event_id) 
                      VALUES ($parentDirId,\"$directory\",\"$caption\",$maxpos,$cal_id,$event_id)"
                );
			    $albumId = $this->insert_id();
			    $this->query("UPDATE $this->table_dirs SET thumbdir=$albumId WHERE id=$albumId");
            }

		// ===== Redirect: =====
			$url = $this->generateCoolURL($directory);
			$this->addToActivityLog("opprettet albumet <a href=\"$url\">$caption</a> ",false);
			$this->addToChangeLog("Bilder lagt opp fra: \"<a href=\"$url\">$caption</a>\"");
			$this->redirect($url,"Albumet ble opprettet");
	}
	
	/*******************************************************************************************
		 Public functions for use by other classes                                                               
		 **************************************************************************************/
	
	/*
		Function: getAlbumIdFromEventId
		Given a known calendar event id, this function will return the corresponding
		image archive album id. If no corresponding album exists, the function will
		return zero (0).
	*/
	public function getAlbumIdFromEventId($event_id) {
		$event_id = intval($event_id);
		$res = $this->query("SELECT id FROM $this->table_dirs WHERE event_id=$event_id");
		if ($res->num_rows == 0) {
			return 0;
		}
		$row = $res->fetch_assoc();
		return intval($row['id']);
	}

	/*
		Function: getAlbumPhotoCount
		This will return the number of photos in a given album and its subalbums, if any.		
	*/
	public function getAlbumPhotoCount($album_id) {
		$album_id = intval($album_id);
		$rs = $this->query("SELECT photo_count FROM $this->table_counts WHERE id=$album_id");
		if ($rs->num_rows == 0) return 0;
		$ro = $rs->fetch_row();
		return intval($ro[0]);
	}
	
	/*
		Function: getAlbumLink
		This will return the URL to a given album.		
	*/
	public function getAlbumURL($album_id) {
		$album_id = intval($album_id);
		$res = $this->query("SELECT id, directory FROM $this->table_dirs WHERE id=$album_id");
		if ($res->num_rows == 0) return "";
		$row = $res->fetch_assoc();
		return $this->generateCoolURL($row['directory']);
	}

	/* 
		Function getAlbumPhotos
		Formerly referred to as <getRandomImages>
	*/
	public function getAlbumPhotos($album_id, $num_photos = 3, $use_frames = true, $photo_width = 100, $use_links = true){
		$album_id = intval($album_id);
		$num_photos = intval($num_photos);
		$use_frames = ($use_frames == true);
		$photo_width = intval($photo_width);
		
		$dirInfo = $this->getDirInfo($album_id);
		$caption = $dirInfo['caption'];		

		$tf = $this->table_files; $td = $this->table_dirs;
		$res = $this->query(
			"SELECT $td.directory, $tf.id, $tf.filename, $tf.thumb_width, $tf.thumb_height, 
				$tf.title, $tf.datetime_original
			FROM 
				$tf,$td
			WHERE 
				$tf.directory=$album_id AND $tf.visible=1 
				AND $tf.deletereason='' AND $tf.directory=$td.id
			LIMIT 
				$num_photos"
		);
		$outp = $this->str_before_imagethumbs;
		$i = 0;
		while ($row = $res->fetch_assoc()){
			$paddingtop = (140-$row['thumb_height'])/2;
			$paddingleft = (140-$row['thumb_width'])/2;
			$dir = "/".$this->fullslug.$dirInfo['directory'];
			$img_id = $row['id'];
			$src = $this->getUrlTo($dirInfo['directory'],$row['filename'],'small');
			$w = $row['thumb_width']/140*$photo_width;
			$h = $row['thumb_height']/140*$photo_width;
			$r1a = array();	$r2a = array();
			$r1a[] = "%imagelink%";			$r2a[] = $dir.$img_id;
			$r1a[] = "%timestamp%";			$r2a[] = date("d. M Y, H:i:s",strtotime($row['datetime_original']));
			$r1a[] = "%imgsrc%";			$r2a[] = $src;
			$r1a[] = "%title%";				$r2a[] = stripslashes($row['title']);
			$r1a[] = "%img_title%";			$r2a[] = strftime("%e. %B %Y, kl. %H:%M",strtotime($row['datetime_original']));
			$r1a[] = "%paddingtop%";		$r2a[] = $paddingtop;
			$r1a[] = "%paddingleft%";		$r2a[] = $paddingleft;
			$r1a[] = "%framewidth%";		$r2a[] = $photo_width;
			$r1a[] = "%width%";				$r2a[] = $w;
			$r1a[] = "%height%";			$r2a[] = $h;
			$r1a[] = "%clearboth%";			$r2a[] = ($i%3 == 0) ? "<div style='clear:both; height: 1px;'><!-- --></div>" : "";
			$r1a[] = "%comment_indicator%"; $r2a[] = '';
			if ($use_frames) 
				$outp .= str_replace($r1a, $r2a, $this->template_imagethumb_frame);
			else if (!$use_links) 
				$outp .= str_replace($r1a, $r2a, $this->template_imagethumb_nolinks);
			else
				$outp .= str_replace($r1a, $r2a, $this->template_imagethumb_noframe);
			$i++;
		}
		$outp .= $this->str_after_imagethumbs;
		
		return $outp;

	}	
	
	/* 
		Function getRepresentativeArchiveImage
	*/
	public function getRepresentativeArchiveImage($album_id, $size, $asLink = false){
		
		$root_dir = $this->root_dir;
		$root_dir = $this->path_to_www.$this->root_dir;
		
		$dirInfo = $this->getDirInfo($album_id);
		
		$thumbnail = $this->getUrlTo($dirInfo['directory'],'thumbnail');
		$thumbnail = (empty($dirInfo['thumbnail']) ? $this->no_thumbnail_found : $thumbnail);
		
		$caption = $dirInfo['caption'];		
		if ($asLink) {
			$url = "/".$this->fullslug.$dirInfo['directory'];
			$aBefore = "<a href=\"$url\" title='Vis bilder fra denne hendelsen i bildearkivet vårt'>"; $aAfter = "</a>";
		} else {
			$aBefore = ""; $aAfter = "";
		}
		switch ($size) {
			case 'large':
				$outp = "
					<div class='alpha-shadow'><div class='inner_div'>
						$aBefore<img src=\"$thumbnail\" alt='$caption' style='width: 200px; height: 150px;' />$aAfter
					</div></div>

				";
				break;
			case 'small':
				$outp = "
					<div class='alpha-shadow'><div class='inner_div'>
						$aBefore<img src=\"$thumbnail\" alt='$caption' style='width: 140px; height: 105px;' />$aAfter
					</div></div>
				";
				break;
		}			
		
		return $outp;

	}
	
	
	public function getRandomMemberImages($member, $count){
				
		$tt = $this->table_tags;
		$tf = $this->table_files;
		$td = $this->table_dirs;
		$res = $this->query(
			"SELECT $tt.id as tag_id, $tt.img as id,
				$tf.filename, $tf.thumb_width, $tf.thumb_height, $tf.title, 
				$tf.directory, $tf.datetime_original
			FROM 
				$tt,$tf,$td
			WHERE 
				$tt.user = $member AND $tt.imgtab = ".$this->page_id." AND $tt.img = $tf.id
				AND $tf.directory = $td.id AND $tf.visible=1 AND $tf.deletereason=''
			ORDER BY 
				$tf.datetime_original DESC
			"
		);
		if ($res->num_rows == 0) {
			return "";
		}

		$num_imgs = $res->num_rows;
		$r1a = array();				$r2a = array();
		$r1a[] = "%count%";			$r2a[] = $num_imgs;
		$outp = str_replace($r1a, $r2a, $this->str_before_imagethumbs);

		$i = 0;
		while ($row = $res->fetch_assoc()){
			$img_id = intval($row['id']);
			$dirInfo = $this->getDirInfo($row['directory']);
			$dir = '/'.$this->fullslug.$dirInfo['directory'];
			$src = $this->getUrlTo($dirInfo['directory'],$row['filename'],'small');
			$paddingtop = (140-$row['thumb_height'])/2;
			$paddingleft = (140-$row['thumb_width'])/2;
			$r1a = array();					$r2a = array();
			$r1a[] = "%imagelink%";			$r2a[] = $dir.$img_id;
			$r1a[] = "%timestamp%";			$r2a[] = date("d. M Y, H:i:s",strtotime($row['datetime_original']));
			$r1a[] = "%imgsrc%";			$r2a[] = $src;
			$r1a[] = "%title%";				$r2a[] = stripslashes($row['title']);
			$r1a[] = "%img_title%";			$r2a[] = strftime("%e. %B %Y, kl. %H:%M", strtotime($row['datetime_original']));
			$r1a[] = "%paddingtop%";		$r2a[] = $paddingtop;
			$r1a[] = "%paddingleft%";		$r2a[] = $paddingleft;
			$r1a[] = "%width%";				$r2a[] = $row['thumb_width'];
			$r1a[] = "%height%";			$r2a[] = $row['thumb_height'];
			$r1a[] = "%clearboth%";			$r2a[] = ($i%3 == 0) ? "<div style='clear:both; height: 1px;'><!-- --></div>" : "";
			$r1a[] = "%comment_indicator%"; $r2a[] = '';
			$outp .= str_replace($r1a, $r2a, $this->template_imagethumb_frame);
			$i++;
			if ($i >= $count) break;
		}
		
		$r1a = array();				$r2a = array();
		$r1a[] = "%count%";			$r2a[] = $num_imgs;
		$outp .= str_replace($r1a, $r2a, $this->str_after_imagethumbs);
		
		return $outp;
	}

}

?>
