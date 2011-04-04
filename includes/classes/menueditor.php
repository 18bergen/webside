<?

class menu extends base {

	var $getvars = array('addmenuitem', 'addseparator', 'editmenuitem','savemenuitem','deletemenuitem','dodeletemenuitem','savemenuorder');
		
	var $table_labels = "menu_labels";
	var $table_items = "menu_items";
	var $table_languages = "languages";
	var $table_pages = "cms_pages";
	var $table_classes = "cms_classes";
	
	/* Callbacks */

	var $lookup_group;
	var $lookup_member;
	var $lookup_webmaster;
	
	var $root_itemid = 0;
	
	var $allow_addmenuitems = false;
	var $allow_editmenuitems = false;
	
	var $item_to_edit = -1;
	var $item_to_delete = -1;
	
	function menu() {
		$this->table_labels = DBPREFIX.$this->table_labels;
		$this->table_items = DBPREFIX.$this->table_items;
		$this->table_languages = DBPREFIX.$this->table_languages;
		$this->table_pages =  DBPREFIX.$this->table_pages;
		$this->table_classes =  DBPREFIX.$this->table_classes;
	}

	
	function initialize(){
	
		$this->initialize_base();
		
		$this->action = "";

		if (isset($_GET['addmenuitem']))
			$this->action = 'addmenuitem';
		else if (isset($_GET['addseparator']))
			$this->action = 'addseparator';
		else if (isset($_GET['dodeletemenuitem']))
			$this->action = 'deletemenuitem';
		else if (isset($_GET['editmenuitem']) && is_numeric($_GET['editmenuitem']))
			$this->item_to_edit = $_GET['editmenuitem'];
		else if (isset($_GET['deletemenuitem']) && is_numeric($_GET['deletemenuitem']))
			$this->item_to_delete = $_GET['deletemenuitem'];
		else if (isset($_GET['savemenuitem']))
			$this->action = 'savemenuitem';
		else if (isset($_GET['savemenuorder']))
			$this->action = 'savemenuorder';	
	}

	function run(){
	
		$this->initialize();
		
		switch ($this->action){
			case 'addmenuitem':
				return $this->addMenuItem();
				break;
			case 'addseparator':
				return $this->addSeparator();
				break;
			case 'savemenuitem':
				return $this->saveMenuItem();
				break;
			case 'savemenuitem':
				return $this->saveMenuItem();
				break;
			case 'deletemenuitem':
				return $this->deleteMenuItem();
				break;
			case 'savemenuorder':
				return $this->saveMenuOrder();
				break;
			default:
				return $this->printOutMenu();
				break;
					
		}

	}
	
	function outputMenu() {
		return $this->printMenuItems($this->root_itemid, false, 0);
	}
	
	function printOutMenu() {
		$url_add = $this->generateURL(array("noprint=true","addmenuitem"));
		$url_addsep = $this->generateURL(array("noprint=true","addseparator"));
		$url_post = $this->generateURL(array("noprint=true","savemenuorder"));
		$img_add  = $this->image_dir."add3.gif";
		
		$output = "";
		$output .= '
						
			<p>
				<a href="'.$url_add.'"><img src="'.$img_add.'" style="border:none;" /> Legg til menyobjekt</a> &nbsp;
				<a href="'.$url_addsep.'"><img src="'.$img_add.'" style="border:none;" /> Legg til overskrift</a>
			</p>
			
		';
		if ($this->item_to_edit == -1 && $this->item_to_delete == -1) {
			$output .= '
				<form method="post" action="'.$url_post.'" onsubmit="populateHiddenVars();">
					<input type="hidden" name="menuOrder" id="menuOrder" value="javascript_disabled" />
					<p>
						<input type="submit" value="Lagre rekkefølge" />
					</p>
				</form>
			';
		}
		$output .= $this->printMenuItems($this->root_itemid, true, 0, true);
		
		if ($this->item_to_edit == -1 && $this->item_to_delete == -1) {
			$output .= '
			
			<script type="text/javascript">
		    //<![CDATA[
		    
		    var menuModified = false;

    		Sortable.create("sortablemenu", {
    			tree:true,
    			scroll:window,
				onUpdate: function() {
					menuModified = true;
				}
    		});
    		
			function populateHiddenVars() {
				$("menuOrder").value = Sortable.serialize("sortablemenu");
				menuModified = false;
				return true;
			}
			
			function confirmBrowseAway() {
				if (menuModified) {
				  return "Du har gjort endringer i menyrekkefølgen som ikke er lagret enda. Dersom du fortsetter går disse endringene tapt.";
				}
			  }
			
			window.onbeforeunload = confirmBrowseAway;
			
    		//]]>
    		</script>
			
			';
		}
		return $output;
	}
	
	function printMenuItems($parent, $editable, $level, $expandAll = false) {
		$output = "";
		$current_page = implode("/", $this->coolUrlSplitted);
		
		$res = $this->query("SELECT 
				$this->table_items.id,
				$this->table_items.page_id,
				$this->table_items.page_url,
				$this->table_items.is_header,
				$this->table_items.onlyforloggedin,
				$this->table_labels.label,
				$this->table_pages.fullslug
			FROM
				$this->table_items
			LEFT JOIN
				$this->table_labels
				ON $this->table_items.id = $this->table_labels.menu_id
				AND $this->table_labels.lang_id='$this->preferred_lang'
			LEFT JOIN
				$this->table_pages
				ON $this->table_items.page_id = $this->table_pages.id
			WHERE
				$this->table_items.parent = '$parent'
			ORDER BY 
				$this->table_items.position
				"
		);
		if (!$editable && $res->num_rows == 0) {
			return;
		}
		for ($i = 0; $i < (3 + $level*2); $i++)
			$output .= "	";

		$editableclass = "";
		if ($this->item_to_edit == -1 && $this->item_to_delete == -1) $editableclass = " class='editable'";
		if ($editable)
			if ($parent == $this->root_itemid) 
				$output .= "<ul id='sortablemenu' $editableclass>\n";
			else
				$output .= "<ul class='edit_list'>\n";
		else
			if ($parent == $this->root_itemid) 
				$output .= "<ul id='mainmenu' class='menu'>\n";
			else
				$output .= "<ul>\n";		
		
		if ($res->num_rows > 0) {
			while ($row = $res->fetch_assoc()) {
				$id = $row['id'];	
				$fullslug = explode("/", $row['fullslug']);
				if ($fullslug[count($fullslug)-1] == "start") array_pop($fullslug);
				$fullslug = implode("/",$fullslug);
				
				
				$selected = (
					(!empty($current_page) && !empty($fullslug) && strpos($current_page,$fullslug) !== false)
				);
				//$selected = (count($current_page) - 1 >= $level && $current_page[$level] == $fullslug[$level]);

				if ($row['onlyforloggedin'] == '0' || $this->isLoggedIn()) {

					if ($editable && $this->item_to_edit == $id) {
						$item = $this->makeEditableItem($row);
					} else if ($editable && $this->item_to_delete == $id) {
						$item = $this->makeDeleteForm($row);
					} else {
						$item = stripslashes($row['label']);
						if (empty($item)) $item = "<i>Menu item $id</i>";
						if ($editable) {
							$url_edit = $this->generateURL("editmenuitem=$id").'#editform';
							$url_delete = $this->generateURL("deletemenuitem=$id").'#deleteform';
							$lock = ($row["onlyforloggedin"] == "1") ? '&nbsp;<img src="'.$this->image_dir.'lock-1.gif" alt="Vis kun for innloggede brukere" title="Vis kun for innloggede brukere" />' : '';
							$item = $item.' '.$lock.' &nbsp;<a href="'.$url_edit.'" class="icn" style="background-image:url(/images/icns/bullet_wrench.png);" alt="Rediger" title="Rediger dette menyobjektet">&nbsp;</a> 
								<a href="'.$url_delete.'" class="icn" style="background-image:url(/images/icns/bullet_delete.png);" alt="Slett" title="Slett dette menyobjektet">&nbsp;</a>';
						} else if ($row['is_header']) {
							
						} else {
							if (!empty($row['page_url'])) {
								$url = stripslashes($row['page_url']);
								$item = '<a href="'.$url.'">'.$item.'</a>';
							} else {
								$url = "/".$fullslug;
								$item = '<a href="'.$url.'">'.$item.'</a>';
							}
						}
					}
					
					for ($i = 0; $i < (4 + $level*2); $i++) $output .= "	";

					$classes = array();
					if ($row['is_header']) $classes[] = 'header'; 
					if ($selected) $classes[] = 'selected';
					$classes = (count($classes) > 0) ? ' class="'.implode(" ",$classes).'"' : '';
					$liId = $editable ? ' id="menuid_'.$id.'"' : '';
					$output .= '<li'.$classes.$liId.'>';
					$output .= "\n";
					for ($i = 0; $i < (5 + $level*2); $i++)
						$output .= "	";
					if ($selected) $item = '<div class="selected">'.$item.'</div>';
					$output .= $item;
					$output .= "\n";
					if (!$row['is_header'] && ($selected || $expandAll)) $output .= $this->printMenuItems($id, $editable, $level + 1, $expandAll);
					for ($i = 0; $i < (4 + $level*2); $i++)
						$output .= "	";
					$output .= "</li>\n";
				}
			}
		}
		for ($i = 0; $i < (3 + $level*2); $i++)
			$output .= "	";
		$output .= "</ul>\n";
		return $output;
	}

	function makeDeleteForm($row) {
		$id = $row['id'];
		$url_post = $this->generateURL(array("noprint=true","dodeletemenuitem=$id"));
		$url_cancel = $this->generateURL("");
		$e = '
			<a name="deleteform"> </a>
			<form method="post" action="'.$url_post.'">
			<p>
				Er du sikker på at du ønsker å fjerne '.$row["label"].' fra menyen?
			</p>
			<p>
				<input type="submit" value="Ja" /> 
				<input type="button" value="Nei, stopp!" onclick=\'window.location="'.$url_cancel.'";\' />
			</p>
			</form>
		';
		return $e;

	}
	
	function deleteMenuItem() {
		if (!$this->allow_editmenuitems) { $this->permissionDenied(); return; }
		$menu_id = $_GET['dodeletemenuitem'];
		if (!is_numeric($menu_id)) $this->fatalError("incorrect input .1");
		
		$this->query("DELETE FROM $this->table_items WHERE id='$menu_id'");
		$this->query("DELETE FROM $this->table_labels WHERE menu_id='$menu_id'");
		
		$this->redirect($this->generateURL(""),"Menyobjektet ble slettet.");
	
	}
	
	function makeEditableItem($row) {
		
		$id = $row['id'];
		$res2 = $this->query("SELECT 
				$this->table_languages.code as lang_code,
				$this->table_languages.fullname as lang_name,
				$this->table_languages.flag as lang_flag,
				$this->table_labels.label as label
			FROM
				$this->table_languages
			LEFT JOIN
				$this->table_labels
			ON 
				$this->table_languages.code = $this->table_labels.lang_id
				AND
				$this->table_labels.menu_id='$id'
			"
		);
		$url_post = $this->generateURL(array("noprint=true","savemenuitem"));
		$e = '
			<a name="editform"> </a>
			<form method="post" action="'.$url_post.'">
				<input type="hidden" name="menu_id" value="'.$id.'" />
				<table>
		';
		if ($row['is_header']) {
			$e .= "<tr><td></td><td><em>Overskrift</em></td></tr>\n";
		} else {
			$e .= "<tr><td valign='top'><strong>Side: </strong></td><td>
			Lokal: ".$this->makePagesDropDown($row['page_id'])."<br />
			eller ekstern: <input type='text' name='page_url' style='width: 300px;' value='".htmlspecialchars(stripslashes($row['page_url']))."' /></td></tr>
			\n";
		}
		$e .= " <tr><td valign='top'><strong>Tekst: </strong></td><td>";
		while ($row2 = $res2->fetch_assoc()) {
			$lang_code = $row2['lang_code'];
			$lang_name = $row2['lang_name'];
			$flag = $this->image_dir."flags/".$row2['lang_flag'];
			$val = stripslashes($row2['label']);
			$e .= '
				<div style="border: 1px solid #aaa; background:#fff; padding: 2px; margin: 2px;">
					<img src="'.$flag.'" alt="'.$lang_name.'" />
					<input type="text" name="label_'.$lang_code.'" value="'.$val.'" style="border:none; width: 330px;" />
				</div>
			';
		}			
		$e .= "</td></tr>\n";
		
		$d = ($row['onlyforloggedin'] == true) ? ' checked="checked"' : '';
		$e .= "
				<tr>
					<td valign='top'><strong>Tilgang: </strong></td>
					<td><input type='checkbox' name='onlyforloggedin'$d />Vis kun for innloggede brukere</td>
				</tr>
		";

		$e .= "
					<tr><td></td><td><p style='text-align:right;'> 
						<input type='submit' value='Lagre' /> 
						<input type='submit' name='cancel' value='Avbryt' />
					</p></td></tr>
				</table>
			</form>";
		return $e;
	}
	
	function makePagesDropDown($defaultValue) {
	

		$dp0 = new cms_basic();
		$dp0->setDbLink($this->getDbLink());
		$dp0->table_pages = "cms_pages";
		$dp0->table_classes = "cms_classes";
		$dp0->lookup_group = $this->lookup_group;
		$dp0->lookup_member = $this->lookup_member;
		$dp0->lookup_webmaster = $this->lookup_webmaster;
		$dp0->preferred_lang = $this->preferred_lang;
		if (isset($this->login_identifier)){
			$dp0->login_identifier = $this->login_identifier;
		}
		$dp0->useCoolUrls = true;
		$dp0->coolUrlPrefix = "sideoversikt";
		$dp0->initialize();
		$dropdown = $dp0->allPagesDropDown('page_id',$defaultValue);
		unset($dp0); 
		
		return $dropdown;
	}
	
	function addMenuItem() {
		if (!$this->allow_addmenuitems) { $this->permissionDenied(); return; }
		$parent = $this->root_itemid;
		$res = $this->query("SELECT MAX(position) FROM $this->table_items");
		$row = $res->fetch_row();
		$pos = $row[0]+1;
		$this->query("INSERT INTO
				$this->table_items
				(parent,position) VALUES ('$parent','$pos')
				"
		);
		$editURL = $this->generateURL("editmenuitem=".$this->insert_id()).'#editform';
		
		$this->redirect($editURL);
	}

	function addSeparator() {
		if (!$this->allow_addmenuitems) { $this->permissionDenied(); return; }
		$parent = $this->root_itemid;
		$res = $this->query("SELECT MAX(position) FROM $this->table_items");
		$row = $res->fetch_row();
		$pos = $row[0]+1;
		$this->query("INSERT INTO
				$this->table_items
				(parent,position,is_header) VALUES ('$parent','$pos',1)
				"
		);
		$editURL = $this->generateURL("editmenuitem=".$this->insert_id()).'#editform';
		
		$this->redirect($editURL);
	}
	
	function saveMenuItem() {
		if (!$this->allow_editmenuitems) { $this->permissionDenied(); return; }
		
		if (isset($_POST['cancel'])) {
			$url = $this->generateURL("");
			$this->redirect($url);
		}
		
		$menu_id = $_POST['menu_id'];
		if (!is_numeric($menu_id)) $this->fatalError("incorrect input .1");
		
		$res = $this->query(
			"SELECT $this->table_items.is_header FROM $this->table_items WHERE id=$menu_id"
		);
		if ($res->num_rows != 1) $this->fatalError("menu item doesn't exist");
		$row = $res->fetch_assoc();		
		
		if (!$row['is_header']) {
			$page_id = $_POST['page_id'];
			if (!is_numeric($page_id)) $this->fatalError("incorrect input .2");
			
			$page_url = addslashes($_POST['page_url']);
			
			$onlyforloggedin = (isset($_POST['onlyforloggedin']) && $_POST['onlyforloggedin'] == 'on') ? '1' : '0';
			
			if (!empty($page_url)) {
				$this->query("UPDATE $this->table_items SET page_id='0', page_url='$page_url', onlyforloggedin='$onlyforloggedin' WHERE id='$menu_id'");
			} else {
				$this->query("UPDATE $this->table_items SET page_id='$page_id', page_url='', onlyforloggedin='$onlyforloggedin' WHERE id='$menu_id'");			
			}
		}
		
		$labels = array();
		$res = $this->query("SELECT code FROM $this->table_languages");
		while ($row = $res->fetch_assoc()) {
			$id = $row['code'];
			$labels[$id] = addslashes($_POST["label_$id"]);
		}
		foreach ($labels as $id => $value) {
			$res2 = $this->query("SELECT label FROM $this->table_labels 
				WHERE menu_id='$menu_id' AND lang_id='$id'"
			);
			if ($res2->num_rows == 1) {
				$row2 = $res2->fetch_assoc();
				$this->query("UPDATE $this->table_labels SET label='$value' WHERE menu_id='$menu_id' AND lang_id='$id'");
			} else {
				$this->query("INSERT INTO $this->table_labels 
					(menu_id, lang_id, label) VALUES 
					('$menu_id','$id','$value')"
				);			
			}
		}
		
		$url = $this->generateURL("");
		$this->redirect($url,"Meny-objektet ble lagret!");
	}
	
	// Example: [menuOrder] => sortablemenu[0]=1&sortablemenu[0][0]=2&sortablemenu[0][1]=3&sortablemenu[1]=4&sortablemenu[2]=5
	
	function getOrderArray($input) {
		$input = explode("&",$input);
		$list = array();
		foreach ($input as $item) {
			list($value,$menu_id) = explode("=",$item);		
			$position = array();
			$offset = 0;
			$i = 0;
			while ($i++ < 100) {
				$s = strpos($value,"[",$offset);
				if ($s === false) break;
				$s++;
				$e = strpos($value,"]",$s);
				if ($e === false) break;
				$val = substr($value,$s,$e-$s);
				if (is_numeric($val)) $position[] = $val;
				$offset = $e;
			}
			$list[] = array(
				'menu_id' => $menu_id,
				'position' => $position
			);
		}
		return $list;
	}
	
	function makeFlatOrder($input) {
		$output = array();
		$level = -1;
		while ($level++ < 100) {
			$itemFound = false;
			foreach ($input as $item) {
				if (count($item['position']) == $level+1) {
					$parent = $this->root_itemid;
					for ($i = 0; $i < $level; $i++) {
						foreach ($output as $o) {
							if ($o['parent'] == $parent && $o['position'] == $item['position'][$i]) {
								$parent = $o['menu_id']; 
								break; 
							}
						}
					}
					$item['parent'] = $parent;
					$item['position'] = $item['position'][count($item['position'])-1];
					$output[] = $item;
					$itemFound = true;
				}
			}
			if (!$itemFound) break;
		}
		return $output;
	}
	
	function saveMenuOrder() {
		if (!$this->allow_editmenuitems) return $this->permissionDenied(); 
		
		$menuOrder = $_POST['menuOrder'];
		if ($menuOrder == 'javascript_disabled') return $this->javascriptRequired();
		
		$order = $this->getOrderArray($menuOrder);
		
		$order = $this->makeFlatOrder($order);
						
		foreach ($order as $o) {
			$position = $o['position'];
			$parent = $o['parent'];
			$menu_id = $o['menu_id'];
			$query = "UPDATE $this->table_items SET position='$position', parent='$parent' WHERE id='$menu_id'";
			//print "$query<br />";
			$this->query($query);
		}

		$this->redirect($this->generateURL(""),"Menyen er lagret");
	}
	
}

?>