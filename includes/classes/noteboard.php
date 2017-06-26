<?php
class noteboard extends comments {

	/* General options */
		
	var $table_news = "news";
	var $table_news_field_id = "id";
	var $table_news_field_page = "page";
	var $table_news_field_author = "creator";
	var $table_news_field_image = "img";
	var $table_news_field_subject = "subject";
	var $table_news_field_body = "body";
	var $table_news_field_timestamp = "date";
	var $table_news_field_version = "version";
	var $forced_image_ratio = 1.25;
	var $document_title = '';

	var $use_subject = false;
	var $use_images = false;
	var $items_per_page = 20;
	var $sticky_msg;
	var $thumbsPerPage = 12;
	var $images_per_page = 28;
	var $FCKeditorWidth = 350;
	/* Image options */
	
	var $table_imagedirs = "cms_pages";
	var $table_images = "images";
	var $table_images_field_id = "id";
	var $table_images_field_extension = "extension";
	var $table_images_field_caption = "caption";
	var $table_images_field_width = "width";
	var $table_images_field_height = "height";
	var $table_images_field_category = "category";
	var $table_images_field_timestamp = "timestamp";
	var $table_images_field_size = "size";
	var $table_images_field_uploader = "uploader";
	var $table_images_field_parent = "parent";

	var $image_directory;
	var $image_imgnotfound = "images/imagenotfound.jpg";
	var $image_width = 100;
	var $image_height = 80;
	var $dynamic_image_file = "fetchnewsimage.php";
		
	/* Callbacks */

	var $lookup_member;
	var $lookup_group;
	var $lookup_webmaster;

	/* RSS */

	var $rss_maxitems = 15;

	/* Access administration */

	var $allow_addentry = false;
	var $allow_addcomment = false;
	
	var $allow_editownentries = false;
	var $allow_editothersentries = false;
	var $allow_deleteownentries = false;
	var $allow_deleteothersentries = false;
	
	var $currentArticle;
	
	var $str_editlink		= "%beginlink%Rediger%endlink%";
	var $str_deletelink		= "%beginlink%Slett%endlink%";
		
	/* Templates and localization */

	var $months = array("january","february","march","april","may","june","july","august","september","october","november","december");
	
	var $newsListingString = "

		<div class='news'>
			<h2 class='post-title'><a href=\"%url%\">%subject%</a></h2>
			<h3 class='author'>%writtenby%</h3>
			<div style='float:right; padding-top:5px;padding-bottom:10px;'>
				%image%
			</div>
			%body%
			<div class='footerLinks'>
				%comments% %editlink% %deletelink%
			</div>
			<div style='clear:both;'><!-- --></div>
		</div>
		";
	var $imgCode = "
		<div class='newsImage'>
			<img src=\"%imageurl%\" width=\"%imagewidth%\" height=\"%imageheight%\" alt=\"%imagecaption%\" />
		</div>";

	var $rss_entry_template = "
		<item rdf:about='%articleurl%'>
			<title>%title%</title>
			<link>%articleurl%</link>
			<description>%body%</description>
			<dc:date>%timestamp%</dc:date>
			<dc:creator>%author%</dc:creator>
		</item>";
	
	var $codesnip_header 				= ""; // "<h1>Nyheter</h1>";
	var $codesnip_newheader 			= "<h2>Legg til nyhet</h2>";
	var $codesnip_editheader 			= "<h2>Rediger nyhet</h2>";
	var $codesnip_uploadimgheader  		= "<h2>Last opp nytt bilde</h2>";
	var $codesnip_confirmdeleteheader	= "<h2>Bekreft sletting</h2>";
	
	var $str_image 				= "Bilde";
	var $str_noimage			= "Intet bilde";
	var $str_pagexofy			= "Side %x% av %y%";		// %x% replaced with current page, %y% with pagecount
	var $str_nocomments			= "Vil du kommentere?";
	var $str_xcomments          = "%count% kommentarer";
	var $str_onecomment 		= "1 kommentar";
	var $str_uploadimage		= "Last opp ny";
	var $str_confirmdelete		= "Er du sikker på at du vil slette denne nyheten?";
	var $str_confirmdeletecomment = "Er du sikker på at du vil slette denne kommentaren?";
	var $str_yes				= "Ja";
	var $str_no					= "Nei";
	var $str_newentry			= "Legg til nyhet";
	
	var $label_newer = "&lt;&lt; Nyere nyheter";
	var $label_older = "Eldre nyheter &gt;&gt;";
	

	/* Initialization (no need to edit) */

	var $image_mimetypes = Array(
		'jpg' => 'image/jpeg',
		'gif' => 'image/gif',
		'png' => 'image/png'
	);

	var $images;
	var $page_no = 1;
	var $item_count;
	var $start_fetch_from;
	
	/* Constructor */

	function noteboard(){
		$this->table_news = DBPREFIX.$this->table_news;
		$this->table_imagedirs = DBPREFIX.$this->table_imagedirs;
		$this->table_images = DBPREFIX.$this->table_images;
		if ((isset($_GET['news_page'])) && (is_numeric($_GET['news_page']))){
			$this->page_no = ($_GET['news_page']); 
		} else { 
			$this->page_no = 1; 
		}
	}

	function initialize(){
		@parent::initialize(); //$this->initialize_base(); $this->initialize_comments();
	
	    array_push($this->getvars,'news_edit','save_news','news_delete','news_page','news_image',
			'def_image','news_article','makerss','errs','fullname','email','body','parent',
			'comment_id','cropimage','docropimage');

		$res = $this->query("SELECT COUNT(*) FROM $this->table_news WHERE $this->table_news_field_page=".$this->page_id);
		$count = $res->fetch_array(); 
		$this->item_count = $count[0];
		
		$this->action = "";
		if (isset($_GET['action']))
			$this->action = $_GET['action'];

        $this->currentArticle = 0;
		if (isset($this->coolUrlSplitted[0]) && is_numeric($this->coolUrlSplitted[0])){
			$this->currentArticle = intval($this->coolUrlSplitted[0]);
		}

	}
	
	function sitemapListAllPages(){
		$urls = array();
		
		// List pages
		$tp = ceil($this->item_count/$this->items_per_page);
		for ($i = 1; $i <= $tp; $i++) {
			$urls[] = array(
				'loc' => $this->generateURL("news_page=$i"),
				'changefreq' => 'weekly'
			);
		}
		
		// List comments
		$res = $this->query("SELECT id FROM $this->table_news WHERE $this->table_news_field_page=".$this->page_id);
		while ($row = $res->fetch_assoc()) {
			$id = $row['id'];
			$u = $this->generateCoolUrl("/$id");
			$urls[] = array(
				'loc' => $u,
				'changefreq' => 'weekly'
			);
		}
		
		return $urls;
	}
	
	function prepare($str){
		$str = stripslashes($str);
		$str = parse_bbcode($str);
		$str = parse_emoticons($str);
		return $str;
	}

	function prepareForRss($str){
		$str = stripslashes($str);
		$str = parse_bbcode($str);
		$str = parse_emoticons($str);
		$str = strip_tags($str);
		$str = html_entity_decode($str,ENT_NOQUOTES,'utf-8');
		$str = str_replace("<","&lt;",$str);
		$str = str_replace(">","&gt;",$str);
		$str = str_replace("\r\n","\n",$str);
		$str = str_replace("\r","\n",$str);
		$str = str_replace("\n","\r\n",$str);
		$str = str_replace("<br />"," ",$str);
		return $str;
	}

	function getLinkToEntry($id) {
		return $this->generateCoolUrl("/$id");
	}
	
	function run(){
		$this->initialize();
		$this->loadImageTable();

		$this->setRssUrl($this->generateCoolURL("/rss"));

        switch ($this->action) {
            case 'saveComment':
                return $this->saveComment();
            case 'deleteCommentDo':
                return $this->deleteCommentDo();
            case 'subscribeToThread':
                return $this->subscribeToThread();
            case 'unsubscribeFromThread':
                return $this->unsubscribeFromThread();
            
        }
        
		if (isset($_GET['save_news'])){
			return $this->saveEntry();
		} else if (isset($_GET['cropimage'])){
			return $this->cropImageForm();
		} else if (isset($_GET['docropimage'])){
			return $this->cropImage();
		} else if (isset($_POST['news_delete'])){
			return $this->deleteEntry($this->currentArticle);
		} else if (isset($this->coolUrlSplitted[0]) && ($this->coolUrlSplitted[0] == "rss")){
			return $this->makeSomeNiftyRSS();

		} else if (isset($_GET['news_edit'])){
			return $this->editEntry($this->currentArticle);
		} else if (isset($_GET['news_delete'])){
			return $this->confirmDeleteEntry($this->currentArticle);
		
		} else if ($this->currentArticle > 0){
			return $this->printDetails($this->currentArticle);

		} else {
			return $this->printEntries();
		}

	}

	function loadImageTable(){
		if ($this->use_images){
			$ti = $this->table_images;
			$res = $this->query("SELECT 
					$ti.$this->table_images_field_id as id,
					$ti.$this->table_images_field_caption as caption,
					$ti.$this->table_images_field_extension as extension
				FROM 
					$ti
				WHERE
					$ti.$this->table_images_field_parent='".$this->image_directory."'"
			);
			$this->images = array();
			while ($row = $res->fetch_assoc()){
				$this->images[$row['id']] = array(
					'extension' => $row['extension'],
					'caption' => $row['caption'],
					'id' => $row['id']
				);
			}
		}
	}
	
	function printEntries(){
		
		$output = "";
	
		$output .= $this->codesnip_header;
		if ($this->allow_addentry){
			$output .= '<p id="news_newentry"><a href="'.$this->generateURL("news_edit").'" style="background:url('.$this->image_dir.'icns/newspaper_add.png) no-repeat left;padding:3px 3px 3px 20px;">'.$this->str_newentry.'</a>'.
				'</p>';
		}
		if ($this->item_count == 0){
			$output .= "<p class='cal_notice'>Ingen nyheter er lagt inn</p>";
			return $output;
		}
		
		if (!empty($this->sticky_msg)){
			$output .= $this->sticky_msg;
		}
		
		$i = $this->findImageDir();
		$realDir = $i['real'];
		$virtDir = $i['virtual'];
		
		// Shorten the variables a bit...
		$tn = $this->table_news;
		$tc = $this->table_comments;
		$ti = $this->table_images;
		$res = $this->query(
			"SELECT 
				$tn.id,
				$tn.creator as author,
				$tn.version,
				$tn.img as imageid,".
				($this->use_subject ? "$tn.subject," : "")."
				$tn.body,
				$tn.date as timestamp,
				$tn.lead_image".
				($this->enable_comments ? ",
					COUNT($tc.id) as commentcount" : "")."
			FROM $tn".
			($this->enable_comments ? " LEFT JOIN $tc 
				ON $tn.id=$tc.parent_id AND $tc.page_id=$this->page_id" : "").
			" WHERE $tn.page=".$this->page_id.
			" GROUP BY $tn.id
			ORDER BY $tn.date DESC
			LIMIT ".(($this->page_no-1)*$this->items_per_page).",$this->items_per_page"
		);
		
		while ($row = $res->fetch_assoc()){
			$output .= $this->printEntry($row, $realDir, $virtDir);
		}

		if ($this->page_no > 1) 
			$this->document_title = 'Side '.$this->page_no;

		$cp = $this->page_no;
		$tp = ceil($this->item_count/$this->items_per_page);
		$xofy = str_replace(array("%x%","%y%"),Array($cp,$tp),$this->str_pagexofy);
		$lp = ($cp == 1)   ? $this->label_newer : '<a href="'.$this->generateURL("news_page=".($cp-1)).'">'.$this->label_newer.'</a>';
		$np = ($cp == $tp) ? $this->label_older   : '<a href="'.$this->generateURL("news_page=".($cp+1)).'">'.$this->label_older.'</a>';
		$output .= '<table width="100%"><tr><td>'.$lp.'</td><td><p style="text-align:center;">'.$xofy.'</p></td><td><p style="text-align:right">'.$np.'</p></td></tr></table>';
		return $output;
	}
		
	function printEntry($row, $realDir, $virtDir, $singleEntry = false){
	
		/*
			SET lc_time_names = 'nb_NO';
			SELECT DATE_FORMAT(dt_start,'%e %M %Y') FROM bg_cal_items
		*/

		$writtenToday = false;
		if (date("Y",$row["timestamp"]) == date("Y",time())) {
			if (date("dm",$row["timestamp"]) == date("dm",time())) {
				$writtenToday = true;
				$dateStr = 'i dag';
			} else {
				$dateStr = strftime("%e. %B", $row["timestamp"]);			
			}
		} else {
			$dateStr = strftime("%e. %B %Y", $row["timestamp"]);
		}

		$shortDateStr = date("d.m.y",$row["timestamp"]);
		if (!$this->use_images || empty($row["lead_image"])){
			$imagecode = "";
		} else {
			
			$thumbnail = stripslashes($row["lead_image"]);

			$caption = "Ukjent";
			$imagecode = sprintf('
				<div style="padding:0px 0px 0px 20px;">
					<div>
						<img src="%s" alt="%s" class="ingressbilde" />
				</div></div>
				',$thumbnail,$caption);
		}
		$editlinkcode = '';
		$deletelinkcode = '';
		if ($row['commentcount'] == 0) {
			$commentcode = '<a href="'.$this->generateCoolUrl("/%id%").'#respond" class="comment">'.$this->str_nocomments.'</a>';		
		} else if ($row['commentcount'] == 1) {
			$commentcode = '<a href="'.$this->generateCoolUrl("/%id%").'#respond" class="comment">'.$this->str_onecomment.'</a>';		
		} else {
			$commentcode = '<a href="'.$this->generateCoolUrl("/%id%").'#respond" class="comments">'.str_replace("%count%",$row['commentcount'],$this->str_xcomments).'</a>';
        }
        $author_id = intval($row['author']);
        $author_data = $this->getUserData(array($author_id), array('FirstName','ProfileUrl'));
        $u = $author_data[$author_id];
		$r1a = array();					$r2a = array();
		$r1a[] = "%writtenby%";			$r2a[] = $this->label_writtenby;
		$r1a[] = "%datestring%";		$r2a[] = "ost";
		$r1a[] = "%timestamp%";			$r2a[] = $writtenToday ? $this->label_writtenby_today : $this->label_writtenby_time;
		$r1a[] = "%timestamp%";			$r2a[] = $dateStr;
		$r1a[] = "%url%"; 				$r2a[] = $this->generateCoolUrl("/%id%");
		$r1a[] = "%id%";				$r2a[] = $row['id'];
        $r1a[] = "%author%";			$r2a[] =  '<a href="'.$u['ProfileUrl'].'">'.$u['FirstName'].'</a>';
		$r1a[] = "%subject%";			$r2a[] = ($this->use_subject ? $row['subject'] : $dateStr );
		if ($row['version'] == 1) {
			$r1a[] = "%body%";				$r2a[] = $this->prepare($row['body']);
		} else {
			$r1a[] = "%body%";				$r2a[] = stripslashes($row['body']);		
		}
		$r1a[] = "%commentcount%";		$r2a[] = $row['commentcount'];
		if ($this->use_images) {
/*			$r1a[] = "%imageid%";			$r2a[] = $row['imageid'];
			$r1a[] = "%imagewidth%";		$r2a[] = $this->image_width;
			$r1a[] = "%imageheight%";		$r2a[] = $this->image_height;
			$r1a[] = "%imagecaption%";		$r2a[] = $row['imagecaption'];
			*/
			$r1a[] = "%imagefilename%";		$r2a[] = stripslashes($row['lead_image']);
			$r1a[] = "%image%"; 			$r2a[] = str_replace($r1a, $r2a, $imagecode);
		}
		$r1a[] = "%editlink%"; 			$r2a[] = str_replace($r1a, $r2a, $editlinkcode);
		$r1a[] = "%deletelink%"; 		$r2a[] = str_replace($r1a, $r2a, $deletelinkcode);
		$r1a[] = "%comments%"; 			$r2a[] = str_replace($r1a, $r2a, $commentcode);
		$r1a[] = "%shorttimestamp%";	$r2a[] = $shortDateStr;
		$outp = str_replace($r1a, $r2a,	$this->newsListingString);
		return $outp;
	}
	
	
	function cropImageForm() {
		
		$output = "";
		
		if (!call_user_func($this->is_allowed,"w", $this->image_directory)){
			return $this->permissionDenied();
		}
		
		$article = $this->currentArticle;
		$res = $this->query("SELECT 
				$this->table_news_field_image as imageid
			FROM 
				$this->table_news
			 WHERE 
				$this->table_news_field_id='$article'"
		);
		if ($res->num_rows != 1){ $this->fatalError("entry doesn't exist"); }
		$row = $res->fetch_assoc();
		$img_id = $row['imageid'];
		
		$forced_image_ratio = $this->forced_image_ratio;
		if (empty($forced_image_ratio)) $forced_image_ratio = false;
		
		$output .= "
			<h2>Beskjær bilde</h2>
			<form method='post' action='".$this->generateURL(array("noprint=true","docropimage"))."'>
		";
		$this->initializeImagesInstance();
		$output .= $this->imginstance->outputCropForm($img_id, $forced_image_ratio, true); // (Forklaring: 100/80 = 1.25)
		$output .= "
			</form>
		";
		
		return $output;
	}
	
	function cropImage() {
	
		if (!call_user_func($this->is_allowed,"w", $this->image_directory)){
			return $this->permissionDenied();
		}
		
		$article = $this->currentArticle;
		$res = $this->query("SELECT 
				$this->table_news_field_image as imageid
			FROM 
				$this->table_news
			 WHERE 
				$this->table_news_field_id='$article'"
		);
		if ($res->num_rows != 1){ $this->fatalError("entry doesn't exist"); }
		$row = $res->fetch_assoc();
		$img_id = $row['imageid'];
		
		if (!isset($_POST['crop_x']) || !isset($_POST['crop_x'])) $this->fatalError('invalid input .1');
		if (!isset($_POST['crop_y']) || !isset($_POST['crop_y'])) $this->fatalError('invalid input .2');
		if (!isset($_POST['crop_width']) || !isset($_POST['crop_width'])) $this->fatalError('invalid input .3');
		if (!isset($_POST['crop_height']) || !isset($_POST['crop_height'])) $this->fatalError('invalid input .4');
		
		$this->initializeImagesInstance();
		$img_filename = $this->imginstance->getFullPathToImage($img_id);
		$this->imginstance->cropImage($img_filename, $_POST['crop_x'], $_POST['crop_y'], $_POST['crop_width'], $_POST['crop_height']);
		$this->imginstance->updateDatabaseInfo($img_id);		
		$this->imginstance->createThumbnail($img_id,true,100,100,"_thumb100");
		$this->imginstance->createThumbnail($img_id,true,500,-1,"_thumb490");
		
		$this->redirect($this->generateURL(""),"Bildet er beskjært. Hvis du ikke ser endringene, kan det være du må laste siden på nytt i nettleseren din.");
		
	}
	
		
	function editEntry($article_id){
		
		$output = "";
		
		/*** BEGIN INGRESSBILDE ***/
			
		if ($this->use_images) {
			$this->initializeImagesInstance();
			if (isset($_GET['ajax_image_action'])) {
				$this->imginstance->ajaxImageAction(
					$this->image_directory,
					$this->images_per_page,
					'lead_image'
				);
				exit();
			}			
		}
			
		/*** END INGRESSBILDE ***/
				
		// Shorten the variables a bit...
		$tn = $this->table_news;
		$ti = $this->table_images;
		
		$output .= $this->codesnip_header;
		
		$this->setDefaultCKEditorOptions();
		//$_SESSION['FCKautoOpen'] = "bilder/nyheter";

		$version = 2;
		if (!empty($article_id)){
			if (!is_numeric($article_id)){ $this-fatalError("incorrect input"); }
			$res = $this->query("SELECT 
					$tn.$this->table_news_field_author as author,
					$tn.$this->table_news_field_version as version,".
					($this->use_subject ? "$tn.$this->table_news_field_subject as subject," : "").
					"$tn.$this->table_news_field_body as body,
					$tn.lead_image
				FROM 
					$tn
				WHERE 
					$tn.$this->table_news_field_id='$article_id'"
			);
			if ($res->num_rows != 1){ $this->fatalError("entry doesn't exist"); }
			$row = $res->fetch_assoc();
			$default_subject = $this->use_subject ? stripslashes($row["subject"]) : "";
			$version = $row['version'];
			$default_body = ($version == 1) ? $this->prepare($row['body']) : stripslashes($row["body"]); // str_replace("<br />","\r\n",
			$default_image = stripslashes($row['lead_image']);

			if (($this->allow_editothersentries) || (($this->allow_editownentries) && ($row['author'] == $this->login_identifier))){ } else {
				return $this->permissionDenied();
			}
			$output .= $this->codesnip_editheader;
		} else {
			if (!$this->allow_addentry){
				return $this->permissionDenied();
			}
			$default_subject = "";
			$default_body = "";
			$default_image = "";
			if ($this->use_images) {
				$default_image = isset($_GET['def_image']) ? $_GET['def_image'] : "";
			}
			$output .= $this->codesnip_newheader;
		}

		$url_post = ($this->useCoolUrls? 
			$this->generateURL(array("noprint=true","save_news")) :
			$this->generateURL(array("news_article=$article_id","noprint=true"))
		);
		
		$subject = $this->use_subject ? 
			"<input name='subject' id='subject' style=\"width:".$this->FCKeditorWidth."px; font-weight:normal; font-size:160%; color:#1B0431; border:1px solid #aaa; margin-top:3px; margin-bottom:8px;\" value=\"".htmlspecialchars($default_subject)."\" /><br />" : "";
		
		
		$body = "<textarea name='editor_body' id='editor_body' rows='10' cols='40' style='width:".$this->FCKeditorWidth."px;'>".$default_body."</textarea>";
		
/*					$_SESSION['ajax_imageselector_defaultimage'] = $default_image;
			$_SESSION['ajax_imageselector_imagesperpage'] = $this->images_per_page;
			list($ingressbildeDialog,$ingressbilde) = $this->imginstance->makeAjaxImageSelector(
				$this->image_directory,
				'lead_image'
			);

<img src="/18bergen/uimages/brukerfiler/images/ingressbilder/image566_thumb100.jpg" title="Velg nytt bilde" style="border: none; width:100px; height:80px;">

*/

		if ($this->use_images) {
			$ingressbilde = '
				<div style="float:right;width:150px;text-align:center;">
					<input type="hidden" name="lead_image" id="lead_image" value="'.$default_image.'" />
					<a href="#" class="bildevelgerlink" onclick="BrowseServer(); return false;" style="margin-left:5px;" title="Trykk for å velge bilde">
						<span id="ingressbildespan">
							'.(empty($default_image) ? 
								'<strong>Velg bilde</strong> ' :
								'<img src="'.$default_image.'" border="0" alt="Velg bilde" style="margin:5px;" />'
							).'
						</span>
					</a>
				</div>
			';
		} else {
			$ingressbilde = "Bilder er slått av";
		}
		
		$pathToUserFiles = '/'.$this->userFilesDir;
		$pathToThumbs100 = $pathToUserFiles.'_thumbs140/';
		
		$output .= '
		<script type="text/javascript">
		//<![CDATA[
			
			// Render CKFinder in a popup page:
			function BrowseServer() {
				var finder = new CKFinder() ;
				finder.basePath = "'.LIB_CKFINDER_URI.'" ;	// The path for the installation of CKFinder.
				finder.selectActionFunction = SetFileField ;
				finder.startupPath = "Bilder:/Ingressbilder/" ;
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
		$output .= "
			<form id=\"newsform\" method=\"post\" enctype=\"multipart/form-data\" action=\"$url_post\">
				
						$subject
		";
		if ($this->use_images) {
			$output .= "
						<div style='width:".($this->FCKeditorWidth+150)."px;'>
							<div style='display:block;width:150px; float:right;'>$ingressbilde</div>
							<div style='width:".$this->FCKeditorWidth."px;'>$body</div>
						</div>
			";
		} else {
			$output .= "
						<div style='width:".($this->FCKeditorWidth+5)."px;'>
							<div style='width:".$this->FCKeditorWidth."px;'>$body</div>
						</div>			
			";
		}
		$output .= '
		
				<script type="text/javascript">
				//<![CDATA[

					function FCKeditor_OnComplete( editorInstance ) {
						// Editor loaded			
					}
					
					function initCKeditor() {
					
						var editor = CKEDITOR.replace( "editor_body", { 
							customConfig : "'.LIB_CKEDITOR_URI.'config_18bergen.js",
							toolbar: "VerySimpleBergenVS",
							width: '.$this->FCKeditorWidth.',
							height: 300,
							resize_minWidth: '.$this->FCKeditorWidth.',
							resize_maxWidth: '.$this->FCKeditorWidth.', // disables horizontal resizing
							filebrowserBrowseUrl: "'.LIB_CKFINDER_URI.'ckfinder.html?type=Vedlegg&start=Vedlegg:/Nyheter&rlf=0",
							filebrowserUploadUrl: "'.LIB_CKFINDER_URI.'core/connector/php/connector.php?command=QuickUpload&type=Vedlegg&currentFolder=/Nyheter/",
							filebrowserImageBrowseUrl: "'.LIB_CKFINDER_URI.'ckfinder.html?type=Bilder&start=Bilder:/Nyheter&rlf=0&dts=1",
   							filebrowserImageUploadUrl: "'.LIB_CKFINDER_URI.'core/connector/php/connector.php?command=QuickUpload&type=Bilder&currentFolder=/Nyheter/"
						});
						
						// http://docs.cksource.com/CKFinder/Developers_Guide/PHP/CKEditor_Integration#JavaScript
						// http://cksource.com/forums/viewtopic.php?f=10&t=17228&sid=5391b9f92daf8debb50a5e20226ecbdb&p=44334#p44334
						
					}

					var useFckEditor = true;
					$(document).ready(initCKeditor);
	
				//]]>
				</script>
				
				<p>
					<input type="submit" value="Lagre" />
				</p>
				
			</form>
		
		';

		if (empty($article_id)) {
			call_user_func($this->add_to_breadcrumb, '<a href="'.$this->generateCoolURL("/","news_edit").'">Legg til nyhet</a>');
		} else if ($this->use_subject) {
			call_user_func($this->add_to_breadcrumb, '<a href="'.$this->generateCoolURL("/$article_id").'">'.$default_subject.'</a>');
		} else {
			call_user_func($this->add_to_breadcrumb, '<a href="'.$this->generateCoolURL("/$article_id").'">Oppslag '.$article_id.'</a>');
		}
		
		return $output;
	}

	//		if ($id == "_new") return $this->outputUploadImageForm();
	function getImageFilename($id, $extension){
		if (!is_numeric($id)) $this->fatalError("image id not int!");
		if ($id == -1) return "";
		return 'image'.$id.'_thumb100.'.$extension;
	}
	
	function findImageDir(){
		$res = $this->query("SELECT fullslug FROM $this->table_imagedirs WHERE id='$this->image_directory'");
		$row = $res->fetch_assoc();
		$fullslug = $row['fullslug'];
		
		$f = explode("/",$_SERVER['SCRIPT_FILENAME']);
		array_pop($f);
		$image_dir = rtrim(implode("/",$f),"/").$this->pathToImages;
		$image_dir .= $fullslug;
		
		$virtual_image_dir = $this->pathToImages;
		$virtual_image_dir .= $fullslug;
		
		return array('virtual' => $virtual_image_dir, 'real' => $image_dir);
		
	}
	
	function saveEntry(){		

		$id = $this->currentArticle;
		
		/** CHECK PERMISSION **/
		
		if (empty($id)){
			if (!$this->allow_addentry){
				return $this->permissionDenied();
			}
		} else {
			if (!is_numeric($id)){ $this->fatalError("incorrect input!"); }
			$res = $this->query("SELECT 
					$this->table_news_field_author as author 
				FROM 
					$this->table_news 
				WHERE 
					$this->table_news_field_id='$id'"
			);
			if ($res->num_rows != 1) fatalError("can't update non-existing entry");
			$row = $res->fetch_assoc();
			if (($this->allow_editothersentries) || (($this->allow_editownentries) && ($row['author'] == $this->login_identifier))){ } else {
				return $this->permissionDenied();
			}
		}
		
		/** PARSE INPUT **/

        $body = $_POST['editor_body'];
        $body = str_replace('"https://www.18bergen.org/','"/',$body); // make links domain-invariant
        $body = str_replace('"https://www.18bergen.no/','"/',$body);
		$body = addslashes($body); 
		
		$lead_image = isset($_POST['lead_image']) ? $_POST['lead_image'] : '';
		if (ROOT_DIR != '' && !empty($lead_image)){
			$lead_image = substr($lead_image,strlen(ROOT_DIR));
		}
		$subject = $this->use_subject ? addslashes(strip_tags($_POST['subject'])) : ""; 
		$author = $this->login_identifier;
		$timestamp = time(); 
				
		/** SAVE ENTRY AND REDIRECT **/

		if (empty($id)){
			
			$this->query("INSERT INTO $this->table_news 
				(
					$this->table_news_field_page,
					$this->table_news_field_version,
					$this->table_news_field_author,
					$this->table_news_field_timestamp,
					$this->table_news_field_body,
					lead_image".
					($this->use_subject ? ",$this->table_news_field_subject" : "")."
				) 
				VALUES 
				(
					".$this->page_id.",
					2,
					'$author',
					'$timestamp',
					\"$body\",
					\"".addslashes($lead_image)."\"".
					($this->use_subject ? ",'$subject'" : "")."
				)"
			);
			$this->currentArticle = $this->insert_id();
			$tsubject = empty($subject) ? "<em style='color:#aaa;'>Uten tittel</em>":$subject;
			$this->addToActivityLog('skrev nyheten <a href="'.$this->generateCoolURL("/$this->currentArticle").'">'.$tsubject.'</a>',false,'major');
			
			/*
			if ($image_is_uploaded) {
				$this->redirect($this->generateCoolURL("/$id","cropimage"),"Nyheten ble lagret");
			} else {
				$this->redirect($this->generateCoolURL("/$id"),"Nyheten ble lagret");
			}*/

            $this->subscribeToThread(false);
			$this->redirect($this->generateCoolURL("/$this->currentArticle"),"Nyheten ble lagret");
		
		} else {
		
			$this->query("UPDATE $this->table_news 
				SET 
					version=2,
					body=\"$body\", 
					lead_image=\"".addslashes($lead_image)."\"".
					($this->use_subject ? ",$this->table_news_field_subject='$subject'" : "")."
				WHERE 
					$this->table_news_field_id='$id'"
			);
			$this->addToActivityLog('redigerte nyheten "<a href="'.$this->generateURL("").'">'.$subject.'</a>"',true,'update');	
			/*
			if ($image_is_uploaded) {
				$this->redirect($this->generateURL("cropimage"),"Nyheten ble lagret");
			} else {
				$this->redirect($this->generateURL(""),"Nyheten ble lagret");
			}*/
			$this->redirect($this->generateURL(""),"Nyheten ble lagret");
			
		}
	

	}
	
	function deleteEntry($id){
		if (!is_numeric($id)){ $this->fatalError("incorrect input!"); }
		$res = $this->query("SELECT 
				$this->table_news_field_subject as subject 
			FROM 
				$this->table_news 
			WHERE 
				$this->table_news_field_id='$id'"
		);
		if ($res->num_rows != 1) fatalError("can't delete non-existing entry");
		$row = $res->fetch_assoc();
		$subject = stripslashes($row['subject']);

		if ($this->allow_deleteothersentries){
			$this->query("DELETE 
				FROM 
					$this->table_news
				WHERE 
					$this->table_news_field_id='$id'"
			);
		} else if ($this->allow_deleteownentries) { 
			$this->query("DELETE 
				FROM 
					$this->table_news
				WHERE 
					$this->table_news_field_id='$id' 
					AND $this->table_news_field_author='$this->login_identifier'"
			);
		}
		$this->addToActivityLog("slettet nyheten \"$subject\"");	
		
		$this->redirect($this->generateCoolURL("/"), "Nyheten ble slettet");

	}

	function confirmDeleteEntry($id){
		$output = $this->codesnip_header;
		if (!is_numeric($id)){ $this->fatalError("incorrect input!"); }
		$res = $this->query("SELECT 
				$this->table_news_field_author as author
			FROM
				$this->table_news
			WHERE
				$this->table_news_field_id='$id'"
		);
		if ($res->num_rows != 1) $this->fatalError("Nyheten eksisterer ikke!");
		$row = $res->fetch_assoc();
		if (($this->allow_deleteothersentries) || (($this->allow_deleteownentries) && ($row['author'] == $this->login_identifier))){ 
			$output .= $this->codesnip_confirmdeleteheader;
			$output .= "<p>$this->str_confirmdelete</p>";
			$output .= sprintf('<form method="post" action="%s">
					<input type="hidden" name="news_delete" value="true" />
					<input type="submit" value="%s" /> 
					<input type="button" value="%s" onclick="window.location=\'%s\'">
				</form>', $this->generateURL("noprint=true"), 
					"     $this->str_yes      ",
					"     $this->str_no      ",
					$_SERVER['HTTP_REFERER']
			);
			return $output;
		} else {
			return $this->permissionDenied();
		}
	}

	function printDetails($id){
		$output = "";
		if (!is_numeric($id)){ $this->fatalError("incorrect input!"); }
		$output .= $this->codesnip_header;
		
		// Print newsentry
		$tn = $this->table_news;
		$tc = $this->table_comments;
		$ti = $this->table_images;
		$res = $this->query(
			"SELECT 
				$tn.id,
				$tn.creator as author,
				$tn.version,
				$tn.img imageid,".
				($this->use_subject ? "$tn.subject," : "")."
				$tn.body,
				$tn.date as timestamp,
				$tn.lead_image".
				($this->enable_comments ? ",
					count($tc.id) as commentcount" : "")."
			FROM $tn".
			($this->enable_comments ? " LEFT JOIN $tc 
				ON $tn.id=$tc.parent_id AND $tc.page_id=$this->page_id" : "").
			" WHERE 
				$tn.$this->table_news_field_id='$id' 
			GROUP BY 
				$tn.$this->table_news_field_id"
		);
		if ($res->num_rows != 1){
			return $this->notSoFatalError("Nyheten ble ikke funnet!");
		}
		$row = $res->fetch_assoc();
			
		$i = $this->findImageDir();
		$realDir = $i['real'];
		$virtDir = $i['virtual'];

		$this->document_title = ($this->use_subject ? stripslashes($row['subject']) : $dateStr );
		$output .= $this->printEntry($row, $realDir, $virtDir,true);
		
		$this->comment_desc = 1;
		$output .= $this->printComments($id);
		
		if (($this->allow_editothersentries) || (($this->allow_editownentries) && ($row['author'] == $this->login_identifier))) {
			$output .= $this->make_editlink($this->generateURL("news_edit"), "Rediger nyhet",false);		
		}
		
		if ($this->use_subject) 
			call_user_func($this->add_to_breadcrumb, '<a href="'.$this->generateCoolURL("/$id").'">'.$row['subject'].'</a>');
		else
			call_user_func($this->add_to_breadcrumb, '<a href="'.$this->generateCoolURL("/$id").'">Oppslag '.$id.'</a>');
		
		return $output;
 	}

 	
 	/* ######################################## RSS ######################################################*/

	function makeRssSubject($str){
		$minLen = 20;
		$maxLen = 80;
		
		$str = $this->prepareForRss($str);
		$str1 = explode("\r\n",$str);
		$str2 = explode(".",$str);
		$str3 = explode("!",$str);
		
		$len1 = strlen($str1[0]); 
		$len2 = strlen($str2[0]); 
		$len3 = strlen($str3[0]);
		
		if ($len1 <= $len2 && $len1 <= $len3 && $len1 >= $minLen && $len1 <= $maxLen) {
			return $str1[0];
		}
		if ($len2 <= $len3 && $len2 >= $minLen && $len2 <= $maxLen) {
			return $str2[0].".";
		}
		if ($len3 >= $minLen && $len3 <= $maxLen) {
			return $str3[0]."!";
		}
		
		$str4 = explode(" ",$str);
		$i = 0;
		$str5 = "";
		while (strlen($str5) + strlen($str4[$i]) < $maxLen && $i < count($str4)-1) {
			$str5 .= $str4[$i]." ";
			$i++;
		}
		$str5 = trim($str5)."...";
		return $str5;
	}

	function makeRssEntry($row){
		$mf = $this->lookup_member;
		$dateStr = date("Y-m-d",$row['timestamp'])."T".date("H:i",$row['timestamp'])."+01:00";		
		$aut = $mf($row['author']);
		$r1a[0]  = "%id%";			 $r2a[0]  = $row['id'];
		$r1a[1]  = "%author%";		 $r2a[1]  = $aut->firstname;
		$r1a[2]  = "%title%";		 $r2a[2]  = ($this->use_subject ? $row['subject'] : $this->makeRssSubject($row['body']));
		$r1a[3]  = "%body%";		 $r2a[3]  = $this->prepareForRss($row['body']);
		$r1a[4]  = "%timestamp%";	 $r2a[4]  = $dateStr;
		$r1a[5]  = "%commentcount%"; $r2a[5]  = $row['commentcount'];
		$r1a[11] = "%articleurl%"; 	 $r2a[11] = "https://".$_SERVER['SERVER_NAME'].ROOT_DIR.str_replace("&","&amp;",($this->useCoolUrls? 
					$this->generateCoolURL("/".$row['id']) :
					$this->generateURL("news_article=".$row['id'])
				));
		$outp = str_replace($r1a, $r2a, $this->rss_entry_template);
		return $outp;
	}

	function makeSomeNiftyRSS(){
		
		$res = $this->query("SELECT COUNT(*) FROM $this->table_news WHERE $this->table_news_field_page=".$this->page_id);
		$count = $res->fetch_array(); 
		$count = $count[0];
			
		// Shorten the variables a bit...
		$tn = $this->table_news;
		$tc = $this->table_comments;
		$ti = $this->table_images;
		$res = $this->query(
			"SELECT 
				$tn.$this->table_news_field_id as id,
				$tn.$this->table_news_field_author as author,
				$tn.$this->table_news_field_image as imageid,".
				($this->use_subject ? "$tn.$this->table_news_field_subject as subject," : "")."
				$tn.$this->table_news_field_body as body,
				$tn.$this->table_news_field_timestamp as timestamp".
				($this->use_images ? ",
					$ti.$this->table_images_field_caption as imagecaption,
					$ti.$this->table_images_field_width as imagewidth,
					$ti.$this->table_images_field_height as imageheight,
					$ti.$this->table_images_field_extension as imgext " : "").
				($this->enable_comments ? ",
					count($tc.id) as commentcount" : "").
			" FROM $tn".
			($this->enable_comments ? " LEFT JOIN $tc 
				ON $tn.$this->table_news_field_id=$tc.parent_id AND $tc.page_id=$this->page_id" : "").
			($this->use_images ? " LEFT JOIN $ti 
				ON $ti.$this->table_images_field_id=$tn.$this->table_news_field_image" : "").
			" WHERE $this->table_news_field_page=".$this->page_id.
			" GROUP BY $tn.$this->table_news_field_id
			ORDER BY $tn.$this->table_news_field_timestamp DESC
			LIMIT $this->rss_maxitems"
		);
		$rssTOC = ""; $rssItems = "";
		while ($row = $res->fetch_assoc()){
			$rssTOC .= "
                    <rdf:li resource=\"https://".$_SERVER['SERVER_NAME'].ROOT_DIR.str_replace("&","&amp;",($this->useCoolUrls? 
						$this->generateCoolURL("/".$row['id']) :
						$this->generateURL("news_article=".$row['id'])
					))."\" />";
			$rssItems .= $this->makeRssEntry($row);
		}
		header("Content-Type: application/rss+xml; charset=utf-8");
		print "<?xml version=\"1.0\" encoding=\"utf-8\"?>
<rdf:RDF 
    xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\" 
    xmlns:dc=\"http://purl.org/dc/elements/1.1/\" 
    xmlns:syn=\"http://purl.org/rss/1.0/modules/syndication/\"
    xmlns:admin=\"http://webns.net/mvcb/\" 
    xmlns=\"http://purl.org/rss/1.0/\"
>
    <channel rdf:about=\"https://".$this->server_name."/\">
        <title>".$this->site_name.": Nyheter</title>
        <link>https://".$this->server_name."/</link>
        <description>".$this->site_name." sin nyhetsfeed.</description>
        <image rdf:resource=\"https://".$this->server_name."/images/scoutlogo2.gif\" />
        <items>
            <rdf:Seq>$rssTOC
            </rdf:Seq>
        </items>
    </channel>
    <image rdf:about=\"https://".$this->server_name."/images/scoutlogo2.gif\">
        <title>".$this->site_name."</title>
        <link>https://".$this->server_name."</link>
        <url>https://".$this->server_name."/images/scoutlogo2.gif</url>
    </image>
    $rssItems
</rdf:RDF>
";
	exit();
	}

	/** COMMENTS **/
	
	function subscribeToThread($post_id = 0, $redirect = true) {
	    $post_id = intval($post_id);
	    if ($post_id == 0) $post_id = $this->currentArticle;
	    @parent::subscribeToThread($post_id, $redirect);
	}

	function unsubscribeFromThread($post_id = 0, $redirect = true) {
	    $post_id = intval($post_id);
	    if ($post_id == 0) $post_id = $this->currentArticle;
	    @parent::unsubscribeFromThread($post_id, $redirect);
	}

	function saveComment($post_id = 0, $context = '') {
	    $post_id = intval($post_id);
	    if ($post_id == 0) $post_id = intval($this->currentArticle);
	    if ($post_id <= 0) { $this->fatalError("incorrect input!"); }
		
		$tn = $this->table_news;
		$res = $this->query("SELECT subject FROM $tn WHERE id=$post_id");
		if ($res->num_rows != 1) $this->fatalError("Nyheten ble ikke funnet!");

		$row = $res->fetch_assoc();
		$context = 'nyheten «'.stripslashes($row['subject']).'»';
	    @parent::saveComment($post_id, $context);	    
	}

}

?>
