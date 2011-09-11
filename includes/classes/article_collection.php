<?

class article_collection extends comments {

	var $allow_viewleads = false;
	var $allow_viewbodies = false;
	var $allow_addarticle = false;
	var $allow_editothersarticles = false;
	
	var $table_articles;
	var $table_articles_field_id = "id";
	var $table_articles_field_page = "page";
	var $table_articles_field_lang = "lang";
	var $table_articles_field_created = "created";
	var $table_articles_field_lastmodified = "lastmodified";
	var $table_articles_field_author = "author";
	var $table_articles_field_topic = "topic";
	var $table_articles_field_lead = "lead";
	var $table_articles_field_lead_image = "lead_image";
	var $table_articles_field_body = "body";
	var $table_articles_field_weekno = "weekno";
	var $table_articles_field_published = "published";
	
	var $lead_image_dir;
	var $leads_per_page = 10;
	var $page_no = 1;
	var $document_title = '';
	
	var $label_save = "Lagre";
	var $label_addarticle = "Legg til artikkel";
	var $label_editarticle = "Endre artikkel";
	var $label_help = "Hvordan skrive speidertips?";
	var $label_articledoesntexist = "Artikkelen eksisterer ikke";
	var $label_readmore = "Les mer";	
	var $label_newer = "&lt;&lt; Nyere tips";
	var $label_older = "Eldre tips &gt;&gt;";
	
	var $template_leadlistheader = '
		<p>
			<a href="%addarticleurl%" class="icn" style="background-image:url(/images/icns/add.png);">%addarticle%</a> 
			<a href="%helpurl%" class="icn" style="background-image:url(/images/icns/help.png);">%help%</a>
		</p>
	';
	
	var $template_leadlistitem = '
		<div class="%divclass%">
			<h1 class="date-header">%header%</h1>
			<h2 class="post-title"><a href="%url_readmore%">%topic%</a></h2>
			<div class="post">
				<p>%lead%</p>
				<p class="footerLinks">
					<a href="%url_readmore%#respond" class="%commentclass%">%comments%</a>
					<a href="%url_readmore%" class="readmore">%readmore%</a> 
				</p>
				%notpublishedyet%
			</div>
		</div>
	';
	
	var $template_addarticleform = '
		<h2>%addarticle%</h2>
		%errors%
		<form method="post" action="%posturl%">
			<table><tr>
			<th align="right">
				Tittel:</th><td>
				<input type="text" name="article_topic" id="article_topic" value="%topic%" style="width:300px;" />
			</td>
			</tr><tr>
			<th align="right">Url:</th><td style="font-size:10px;">
				%url%<input id="article_slug" name="article_slug" type="text" value="%slug%" style="width:150px;" />
			</td>
			</tr></table>
		
			<p>
				<input type="submit" value="%submit%" />
			</p>
		</form>
	';
	
	var $template_editarticleform = '
		<h2>%editarticle%</h2>
		<form method="post" action="%posturl%">
			<input type="hidden" name="article_id" value="%id%" />
			<p>
				<strong>Tittel: </strong><br />
				<input type="text" name="article_topic" id="article_topic" value="%topic%" style="width:500px;" />
			</p>
			<p>
				<strong>Ingress: </strong><br />
				<textarea name="article_lead" id="article_lead" style="width:500px; height: 80px;">%lead%</textarea>
			</p>
			<p>
				<strong>Brødtekst: </strong><br />
				<textarea name="article_body" id="article_body" style="width:500px; height: %bodyeditheight%px;">%body%</textarea>
			</p>
			<p>
				Uke: %weekno%
			</p>
			<p>
				<input type="checkbox" name="published" id="published"%published%/><label for="published">Publisér dette tipset</label>
			</p>
			<p>
				<input type="submit" value="%submit%" />
			</p>
		</form>
		
		<script type="text/javascript"><!--
			
			function initCKeditor() {
			
				var editor = CKEDITOR.replace( "article_body", { 
					customConfig : "%ckeditor_uri%config_18bergen.js",
					toolbar: "SimpleBergenVS",
					width: 500,
					resize_minWidth: 500, // disables horizontal resizing
					resize_maxWidth: 500, // disables horizontal resizing
					filebrowserImageBrowseUrl: "%ckfinder_uri%ckfinder.html?type=Bilder&start=Bilder:/Speidertips&rlf=0&dts=1",
					filebrowserImageUploadUrl: "%ckfinder_uri%core/connector/php/connector.php?command=QuickUpload&type=Bilder&currentFolder=/Speidertips/"

				});
				/*
				CKFinder.SetupCKEditor( editor, {
					basePath: "%ckfinder_uri%",
					startupPath : "Bilder:/speidertips"
				}, "Bilder");
				*/

			}

			YAHOO.util.Event.onDOMReady(initCKeditor);
		
		//--></script>
	';
	
	var $template_viewarticle = '
		<div class="article">
			<div class="author">
				<div class="alpha-shadow-nomargin"><div class="inner_div">
					<img src="%authorimg%" alt="Forfatterbilde" />
				</div></div>
				<div style="padding-bottom:5px;">Av %authorname%</div>
			</div>
			<div class="hidefromprint"><h1 class="date-header">%header%</h1></div>
			<h2 class="post-title">%topic%</h2>
			<div class="post">
			<div class="post-body">
				<hr />
				<p class="lead"><strong>%lead%</strong></p>
				<hr />
				<p>%body%</p>
			</div></div>			
		</div>
		<p class="hidefromprint" style="text-align:center;">
				%prev%
				&nbsp;
				%next%
		</p>
	
	';
	
	function article_collection() {
		$this->table_articles = DBPREFIX.'articles';
		$this->table_comments = DBPREFIX.'comments';
	}
	
	function initialize() {
		@parent::initialize();

		$this->errorMessages['empty_topic'] = "Du må skrive noe i tittelfeltet";
		$this->errorMessages['empty_slug'] = "Du må skrive noe i URL-feltet";

		array_push($this->getvars,'add_article','save_article','edit_article','update_article',
			'view_article','page');

		if ((isset($_GET['page'])) && (is_numeric($_GET['page']))){
			$this->page_no = ($_GET['page']); 
		} else { 
			$this->page_no = 1;
		}

		$this->action = "default";
		if (isset($_GET['add_article'])) $this->action = 'add_article';
		else if (isset($_GET['save_article'])) $this->action = 'save_article';
		else if (isset($_GET['edit_article'])) $this->action = 'edit_article';
		else if (isset($_GET['update_article'])) $this->action = 'update_article';
		else if (isset($_GET['view_article'])) $this->action = 'view_article';
		else if (isset($this->coolUrlSplitted[0])) $this->action = 'view_article';
		
		if (isset($_GET['action']))
			$this->action = $_GET['action'];
					
		$this->currentArticleId = 0;
		if (isset($this->coolUrlSplitted[0])) {
			$slug = addslashes($this->coolUrlSplitted[0]);
			$res = $this->query("SELECT id FROM $this->table_articles WHERE slug=\"$slug\"");
			if ($res->num_rows != 1) return $this->notSoFatalError("Siden finnes ikke.");
			$row = $res->fetch_assoc();
			$this->currentArticleId = intval($row['id']);		
		}				

	}
	
	function sitemapListAllPages(){
		$urls = array();
		
		// List articles
		$res = $this->query("SELECT id,slug,lastmodified FROM $this->table_articles WHERE page=".$this->page_id);
		while ($row = $res->fetch_assoc()) {
			$id = $row['id'];
			$slug = $row['slug'];
			$u = $this->generateCoolUrl("/$slug");
			$urls[] = array(
				'loc' => $u, 
				'lastmod' => $row['lastmodified'],
				'changefreq' => 'monthly'
			);
		}
		
		return $urls;
	}

	function getLinkToEntry($id) {
		$id = intval($id);
		$res = $this->query("SELECT slug FROM $this->table_articles WHERE id=$id");
		$row = $res->fetch_assoc();
		return $this->generateCoolURL('/'.$row['slug']);
	}
	
	function run(){
		$this->initialize();

		switch ($this->action) {
			
			case 'add_article':
				return $this->addArticleForm();
				break;

			case 'save_article':
				return $this->addArticle();
				break;
				
			case 'edit_article':
				return $this->editArticleForm($this->currentArticleId);
				break;
				
			case 'update_article':
				return $this->updateArticle($this->currentArticleId);
				break;

			case 'view_article':
				return $this->viewArticle($this->currentArticleId);
				break;
				
			case 'saveComment':
                return $this->saveComment();
            case 'deleteCommentDo':
                return $this->deleteCommentDo();
            case 'subscribeToThread':
                return $this->subscribeToThread();
            case 'unsubscribeFromThread':
                return $this->unsubscribeFromThread();

				
			default:
				return $this->printArticleLeads();
				break;
			
		}
	}
		
	function getLastArticle() {
	
		if (!$this->allow_viewleads) {
			print $this->permissionDenied();
			return false;
		}
	
		$res = $this->query("SELECT id,lastmodified,author,topic,slug,lead,lead_image
			FROM $this->table_articles
			WHERE published='1' AND page=".$this->page_id."
			ORDER BY created DESC
			LIMIT 1");
		if ($res->num_rows == 1) {
			$row = $res->fetch_assoc();
			$row['uri'] = $this->generateCoolURL('/'.$row['slug']);
			return $row;
		} else {
			return false;
		}
	
	}
		
	function printArticleLeads() {
		
		$output = "";
		if (!$this->allow_viewleads) {
			$this->permissionDenied();
			return;
		}
		
		$r1a = array(); $r2a = array();
		$r1a[]  = "%addarticleurl%";		$r2a[]  = $this->generateURL("add_article");
		$r1a[]  = "%helpurl%";				$r2a[]  = "/hjelp/skrive-speidertips/";
		if ($this->allow_addarticle) {
			$r1a[]  = "%addarticle%";			$r2a[]  = $this->label_addarticle;
			$r1a[]  = "%help%";					$r2a[]  = $this->label_help;
			$output .= str_replace($r1a, $r2a, $this->template_leadlistheader);
		} else {
			$r1a[]  = "%addarticle%";			$r2a[]  = "";		
			$r1a[]  = "%help%";					$r2a[]  = "";		
		}
		
		$ta = $this->table_articles;
		$tc = $this->table_comments;
		$res = $this->query("SELECT 
				$ta.id,$ta.lastmodified,$ta.author,$ta.topic,$ta.slug,$ta.lead,$ta.lead_image,$ta.published,$ta.weekno,
				COUNT($tc.id) as commentcount
			FROM $ta
			LEFT JOIN $tc 
				ON $ta.id=$tc.parent_id AND $tc.page_id=$this->page_id
			WHERE $ta.page=".$this->page_id."
			GROUP BY $ta.id
			ORDER BY $ta.created DESC
			LIMIT ".(($this->page_no-1)*$this->leads_per_page).",$this->leads_per_page"
		);
		
		$divclass = "first_article";
		$header = "Siste tips:";
		
		while ($row = $res->fetch_assoc()) {
			$id = $row['id'];
			$topic = stripslashes($row['topic']);
			$lead = stripslashes($row['lead']);
			$lead_image = $row['lead_image'];
			$slug = stripslashes($row['slug']);
			$commentcount = intval($row['commentcount']);
			
			if ($row['published'] == '1') {
				$notpublishedyet = "";
			} else {
				$notpublishedyet = "<div style='background: #ffffaa; margin: 0px; padding: 3px;'>Denne artikkelen er ikke publisert enda.</div>";
			}
			
			$author = $row['author'];
			$allow_edit = (($this->login_identifier == $author) || $this->allow_editothersarticles);

			if ($commentcount > 1) $comments_str = $commentcount.' kommentarer';
			else if ($commentcount == 1) $comments_str = '1 kommentar';
			else $comments_str = 'Vil du kommentere?';
			
			if ($allow_edit || ($row['published'] == '1')) {
				
				$r1a = array(); 				$r2a = array();
				$r1a[] = "%addarticle%";		$r2a[] = $this->label_addarticle;
				$r1a[] = "%topic%";				$r2a[] = $topic;
				$r1a[] = "%lead%";				$r2a[] = $lead;
				$r1a[] = "%url_readmore%";		$r2a[] = $this->generateCoolURL("/$slug");
				$r1a[] = "%readmore%";			$r2a[] = $this->label_readmore;
				$r1a[] = "%divclass%";			$r2a[] = $divclass;
				$r1a[] = "%header%";			$r2a[] = str_replace("%weekno%",$row['weekno'],$header);
				$r1a[] = "%notpublishedyet%";	$r2a[] = $notpublishedyet;
				$r1a[] = "%commentclass%";		$r2a[] = ($commentcount > 1) ? 'comments' : 'comment';
				$r1a[] = "%comments%";			$r2a[] = $comments_str;
	
				$outp = str_replace($r1a, $r2a, $this->template_leadlistitem);
				$output .= $outp;
		
				$divclass = "other_article";
				$header = "Uke %weekno%:";
			
			}
		}

		$res = $this->query("SELECT COUNT(id) FROM $this->table_articles WHERE $this->table_articles_field_page=".$this->page_id);
		$count = $res->fetch_array(); 
		$this->item_count = $count[0];
		$cp = $this->page_no;
		$tp = ceil($this->item_count/$this->leads_per_page);
		$xofy = str_replace(array("%x%","%y%"),Array($cp,$tp),$this->label_pagexofy);
		$lp = ($cp == 1)   ? $this->label_newer : '<a href="'.$this->generateURL("page=".($cp-1)).'">'.$this->label_newer.'</a>';
		$np = ($cp == $tp) ? $this->label_older   : '<a href="'.$this->generateURL("page=".($cp+1)).'">'.$this->label_older.'</a>';
		$output .= "<table width='100%'><tr><td>$lp</td><td><p style='text-align:center;'>$xofy</p></td><td><p style='text-align:right'>$np</p></td></tr></table>\n\n";
		
		return $output;		
	}
	
	function viewArticle($id) {
		if (isset($_GET['view_article'])) $id = intval($_GET['view_article']);
		if ($id <= 0) return $this->notSoFatalError("Siden finnes ikke.");
		$ta = $this->table_articles;

		$output = "";
	
		if (!$this->allow_viewbodies) { $this->permissionDenied(); return; }		
		if (!is_numeric($id)) { $this->permissionDenied(); return; }
		
		$res = $this->query("SELECT topic,author,lead,lead_image,slug,body,created
			FROM $this->table_articles
			WHERE id = '$id'");
		
		
		if ($res->num_rows != 1) { $this->notSoFatalError($this->label_articledoesntexist); return; }
		$row = $res->fetch_assoc();
		
		$author = $row['author'];
		$topic = stripslashes($row['topic']);
		$lead = stripslashes($row['lead']);
		$slug = stripslashes($row['slug']);
		$body = stripslashes($row['body']);
		$created = stripslashes($row['created']);
	
		$this->document_title = $topic;
				
		$author_img = call_user_func($this->lookup_memberimage, $author);
		$author_name = call_user_func($this->make_memberlink, $author);
				
		$allow_editarticle = ($row['author'] == $this->login_identifier || $this->allow_editothersarticles);
		$url_editarticle = $this->generateCoolURL("/$slug","edit_article");
		$link_editarticle = $allow_editarticle ? "<a href='$url_editarticle'>$this->label_editarticle</a>" : "";
				
		if ($allow_editarticle) {
			$output .= $this->make_editlink($this->generateCoolURL("/$slug","edit_article"), "Rediger tips");
		}

		// Finn forrige tips
		$res = $this->query("
			SELECT $ta.id,$ta.topic,$ta.slug FROM $ta WHERE $ta.page=".$this->page_id."
			AND $ta.created < $created ORDER BY $ta.created DESC LIMIT 1");
		if ($res->num_rows == 1) {
			$row = $res->fetch_assoc();
			$forrigeArr = '<a href="'.$this->generateCoolURL('/'.$row['slug']).'" title="'.stripslashes($row['topic']).'" class="icn" style="background-image:url(/images/icns/arrow_left.png);">Forrige speidertips</a>';
		} else {
			$forrigeArr = '<span class="icn" style="color:#999; background-image:url(/images/icns/arrow_left.png);">Forrige speidertips</span>';		
		}
		
		// Finn neste tips
		$res = $this->query("
			SELECT $ta.id,$ta.topic,$ta.slug FROM $ta WHERE $ta.page=".$this->page_id."
			AND $ta.created > $created ORDER BY $ta.created LIMIT 1");
		if ($res->num_rows == 1) {
			$row = $res->fetch_assoc();
			$nesteArr = '<a href="'.$this->generateCoolURL('/'.$row['slug']).'" title="'.stripslashes($row['topic']).'" class="right_icn" style="background-image:url(/images/icns/arrow_right.png);">Neste speidertips</a>';
		} else {
			$nesteArr = '<span class="right_icn" style="color:#999; background-image:url(/images/icns/arrow_right.png);">Neste speidertips</span>';		
		}
		
		$r1a = array(); $r2a = array();
		$r1a[] = "%editarticle%";		$r2a[] = $link_editarticle;
		$r1a[] = "%posturl%";			$r2a[] = $this->generateURL(array('noprint=true','update_article'));
		$r1a[] = "%topic%";				$r2a[] = $topic;
		$r1a[] = "%lead%";				$r2a[] = $lead;
		$r1a[] = "%body%";				$r2a[] = $body;
		$r1a[] = "%submit%";			$r2a[] = $this->label_save;
		$r1a[] = "%fckbasepath%";		$r2a[] = $this->pathToFCKeditor;
		$r1a[] = "%bodyeditheight%";	$r2a[] = $this->field_body_height;
		$r1a[] = "%id%";				$r2a[] = $id;
		$r1a[] = "%header%";			$r2a[] = strftime("%B %Y",$created).':';
		$r1a[] = "%authorname%";		$r2a[] = $author_name;
		$r1a[] = "%authorimg%";			$r2a[] = $author_img;
		$r1a[] = "%prev%";		 		$r2a[] = $forrigeArr;
		$r1a[] = "%next%";		 		$r2a[] = $nesteArr;

		$output .= str_replace($r1a, $r2a, $this->template_viewarticle);

		$this->comment_desc = 3;
		$output .= $this->printComments($id);

		call_user_func($this->add_to_breadcrumb,'<a href="'.$this->generateCoolURL("/$slug").'">'.$topic.'</a>');
		return $output;
	
	}
	
	function addArticleForm() {
	
		if (!$this->allow_addarticle) return $this->permissionDenied();		

		$this->setDefaultCKEditorOptions();
		
		$topic_default = '';
		$slug_default = '';
		$errstr = '';
		
		if (isset($_SESSION['errors'])){
		
			$errors = $_SESSION['errors'];
			$postdata = $_SESSION['postdata'];
			$slug_default 					= $postdata['article_slug'];
			$topic_default 					= $postdata['article_topic'];

			$errstr = "<ul>";
			foreach ($_SESSION['errors'] as $s){
				if (isset($this->errorMessages[$s]))
					$errstr.= "<li>".$this->errorMessages[$s]."</li>";
				else
					$errstr.= "<li>$s</li>";				
			}
			$errstr .= "</ul>";
			$errstr = $this->notSoFatalError($errstr,array('logError'=>false,'customHeader'=>'Artikkelen ble ikke opprettet fordi:'));
			
			unset($_SESSION['errors']);
			unset($_SESSION['postdata']);
			
		} 
		
		$r1a = array(); $r2a = array();
		$r1a[] = "%addarticle%";			$r2a[] = $this->label_addarticle;
		$r1a[] = "%posturl%";				$r2a[] = $this->generateURL(array('noprint=true','save_article'));
		$r1a[] = "%topic%";					$r2a[] = $topic_default;
		$r1a[] = "%url%";					$r2a[] = "http://www.".$_SERVER['SERVER_NAME'].ROOT_DIR.$this->generateCoolURL("/");
		$r1a[] = "%slug%";					$r2a[] = $slug_default;
		$r1a[] = "%submit%";				$r2a[] = $this->label_save;
		$r1a[] = "%errors%";				$r2a[] = $errstr;

		$output = str_replace($r1a, $r2a, $this->template_addarticleform);
		$output .= '
				
			<script type="text/javascript">
								
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
					$("article_slug").value = generateSlug($("article_topic").value);
				}
				
				YAHOO.util.Event.onDOMReady(function() {
					YAHOO.util.Event.addListener("article_topic","keyup",onSubjectChange);
				});
				
			</script>
			
		';
		return $output;
	}
	
	function addArticle() {
	
		if (!$this->allow_addarticle) return $this->permissionDenied();
		
		$errors = array();
		$topic = addslashes($_POST['article_topic']);
		$slug = addslashes($_POST['article_slug']);

		$slug = mb_strtolower($slug,'UTF-8');
		$slug = str_replace(array("ø","æ","å"," ","--"),array("o","ae","a","-","-"),$slug);	
		$slug = preg_replace('/[^\w-]/','',$slug);
		$slug = trim($slug,"-");

		if (empty($topic)) {
			$errors[] = 'empty_topic';
		}
		
		if (empty($slug)) {
			$errors[] = 'empty_slug';
		} else {
			$try_no = 1;
			$orig_slug = $slug;
			$duplicate = true;
			while ($duplicate) {
				$res2 = $this->query("SELECT id,slug FROM $this->table_articles 
				WHERE slug=\"".addslashes($slug)."\" AND page=$this->page_id");
				if ($res2->num_rows > 0) {
					$slug = $orig_slug.'-'.($try_no++);
					$duplicate = true;
				} else {
					$duplicate = false;		
				}
			}
		}

		if (count($errors) > 0) {
			$_SESSION['errors'] = array_unique($errors);
			$_SESSION['postdata'] = $_POST;
			$this->redirect($this->generateURL('add_article'));
		}
		
		$author_id = intval($this->login_identifier);
		$timestamp = time();
		$lang = $this->preferred_lang;
		
		$this->query("INSERT INTO $this->table_articles (author,lang,created,topic,slug,page) 
			VALUES ($author_id,\"$lang\",$timestamp,\"$topic\",\"$slug\",".$this->page_id."
			)
		");
		$this->currentArticleId = $this->insert_id();		
        $this->subscribeToThread(false);
		$this->redirect($this->generateCoolURL("/$slug","editarticle"));
	
	}
	
	function editArticleForm($id) {
		if (isset($_GET['edit_article']) && !empty($_GET['edit_article'])) $id = intval($_GET['edit_article']);
		if ($id <= 0) return $this->notSoFatalError("Siden finnes ikke.");
	
		if (!$this->allow_addarticle) return $this->permissionDenied();		
		if (!is_numeric($id)) return $this->permissionDenied();
		
		$res = $this->query("SELECT topic,author,lead,lead_image,slug,body,weekno,published
			FROM $this->table_articles
			WHERE id = '$id'");
		
		if ($res->num_rows != 1) { $this->notSoFatalError($this->label_articledoesntexist); return; }
		$row = $res->fetch_assoc();
		
		$author = $row['author'];
		if ($author != $this->login_identifier && !$this->allow_editothersarticles) { $this->permissionDenied(); return; }
		
		$topic = htmlspecialchars(stripslashes($row['topic']));
		$lead = stripslashes($row['lead']);
		$slug = stripslashes($row['slug']);
		$body = stripslashes($row['body']);
		
		$c = date("W",time());
		$weekbox = "<select name='weekno'>\n";
		for ($i = 1; $i <= 52; $i++) {
			$d = ($i == $row['weekno'] || (empty($row['weekno']) && $i==$c)) ? " selected='selected'" : "";
			$weekbox .= "   <option value='$i'$d>$i</option>\n";
		}
		$weekbox .= "</select> (Nå er vi i uke $c)\n";
		
		$pubstatus = ($row['published'] == '1') ? " checked='checked'" : "";

		$this->setDefaultCKEditorOptions();

		if (strpos($this->fullslug,"speidertips") === 0) {
			//$_SESSION['FCKautoOpen'] = "bilder/speidertips";
		}
		
		$r1a = array(); 				$r2a = array();
		$r1a[] = "%editarticle%";		$r2a[]  = $this->label_editarticle;
		$r1a[] = "%posturl%";			$r2a[]  = $this->generateURL(array('noprint=true','update_article'));
		$r1a[] = "%topic%";				$r2a[]  = $topic;
		$r1a[] = "%lead%";				$r2a[]  = $lead;
		$r1a[] = "%body%";				$r2a[]  = $body;
		$r1a[] = "%submit%";			$r2a[]  = $this->label_save;
		$r1a[] = "%fckbasepath%";		$r2a[]  = $this->pathToFCKeditor;
		$r1a[] = "%bodyeditheight%";	$r2a[]  = $this->field_body_height;
		$r1a[] = "%id%";				$r2a[]  = $id;
		$r1a[] = "%weekno%";			$r2a[]  = $weekbox;
		$r1a[] = "%published%";			$r2a[]  = $pubstatus;
		$r1a[] = "%ckfinder_uri%";		$r2a[]  = LIB_CKFINDER_URI;
		$r1a[] = "%ckeditor_uri%";		$r2a[]  = LIB_CKEDITOR_URI;

		call_user_func($this->add_to_breadcrumb,'<a href="'.$this->generateCoolURL("/$slug").'">'.$topic.'</a>');
		return str_replace($r1a, $r2a, $this->template_editarticleform);	
	}
	
	function updateArticle($id) {
			
		if ($id <= 0) $id = $_POST['article_id'];
	
		if (!$this->allow_addarticle) return $this->permissionDenied();		
		if (!is_numeric($id)) return $this->permissionDenied(); 
		
		$res = $this->query("SELECT author,slug,published
			FROM $this->table_articles
			WHERE id = '$id'");
		
		if ($res->num_rows != 1) return $this->notSoFatalError($this->label_articledoesntexist); 
		$row = $res->fetch_assoc();
		
		$author = $row['author'];
		if ($author != $this->login_identifier && !$this->allow_editothersarticles) { $this->permissionDenied(); return; }
		$alreadypublished = $row['published'];
		$slug = stripslashes($row['slug']);
		
		$topic = addslashes($_POST['article_topic']);
		$lead = addslashes($_POST['article_lead']);
		$body = addslashes($_POST['article_body']);
		$timestamp = time();
		$weekno = addslashes($_POST['weekno']);
		$published = (isset($_POST['published']) && $_POST['published'] == 'on') ? '1' : '0';
		
		$this->query("UPDATE $this->table_articles SET
				topic = '$topic',
				lead = '$lead',
				body = '$body',
				lastmodified = '$timestamp',
				weekno = '$weekno',
				published = '$published'
			WHERE
				id = '$id'"
		);
		
		$url = $this->generateCoolURL("/$slug");
		
		if ($alreadypublished == '0' && $published == '1') {
			$this->publishArticle($id);
			$this->redirect($url,"Speidertipset ble lagret og publisert");
		} else {
			$this->redirect($url,"Speidertipset ble lagret");
		}
	
	}
	
	function publishArticle($id) {
		
		$res = $this->query("SELECT slug,topic
			FROM $this->table_articles
			WHERE id= '$id'");
		$row = $res->fetch_assoc();		
		$topic = $row['topic'];
		$slug = $row['slug'];
		
		$url = $this->generateCoolURL("/$slug"); 
		
		$this->addToActivityLog('har skrevet et speidertips: <a href="'.$url.'">'.$topic.'</a>',false,'major');

	}
    
    /** COMMENTS **/
	
	function subscribeToThread($post_id = 0, $redirect = true) {
	    $post_id = intval($post_id);
	    if ($post_id == 0) $post_id = $this->currentArticleId;
	    @parent::subscribeToThread($post_id, $redirect);
	}

	function unsubscribeFromThread($post_id = 0, $redirect = true) {
	    $post_id = intval($post_id);
	    if ($post_id == 0) $post_id = $this->currentArticleId;
	    @parent::unsubscribeFromThread($post_id, $redirect);
	}

	function saveComment($post_id = 0, $context = '') {
	    $post_id = intval($post_id);
	    if ($post_id == 0) $post_id = intval($this->currentArticleId);
	    if ($post_id <= 0) { $this->fatalError("incorrect input!"); }
		
		$tn = $this->table_articles;
		$res = $this->query("SELECT topic FROM $tn WHERE id=$post_id");
		if ($res->num_rows != 1) $this->fatalError("Artikkelen ble ikke funnet!");

		$row = $res->fetch_assoc();
		$context = 'speidertipset «'.stripslashes($row['topic']).'»';
	    @parent::saveComment($post_id, $context);
	}
	
}


?>