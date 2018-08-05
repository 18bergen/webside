<?php
class localization extends base {
	
	var $page_title;
	var $pagenotfound = false;
	
	var $table_classlabels = "cms_classlabels";
	var $table_classes = "cms_classes";
	var $table_languages = "languages";

	private static $_defaultLang = 'no';
	private static $_languageDetermined = false;
	private static $_languages = array(
			'no' => array(
				'name' => 'Norsk (bokmål)',
				'greeting' => 'Ditt foretrukne språk er nå satt til norsk.'
			),
			'en' => array(
				'name' => 'Engelsk',
				'greeting' => 'Your preferred language is now set to English.'
			),
			'es' => array(
				'name' => 'Spanish',
				'greeting' => 'Tu lengua preferida ahora se fija al español.'
			),
			'de' => array(
				'name' => 'German',
				'greeting' => 'Deine bevorzugte Sprache wird jetzt auf Deutschen eingestellt.'
			),
			'fr' => array(
				'name' => 'French',
				'greeting' => 'Votre langue préférée est maintenant placée au Français.'
			),
			'jp' => array(
				'name' => 'Japanese',
				'greeting' => 'Your preferred language is now set to Japanese.'
			),
			'it' => array(
				'name' => 'Italian',
				'greeting' => 'Your preferred language is now set to Italian.'
			)
		);	
	
	/* Callbacks */

	var $lookup_group;
	var $lookup_member;
	var $lookup_webmaster;
	
	function __construct() {
		$this->table_classlabels = DBPREFIX.$this->table_classlabels;
		$this->table_classes = DBPREFIX.$this->table_classes;
		$this->table_languages = DBPREFIX.$this->table_languages;
	}
	
	function initialize(){
	
		$this->initialize_base();

		array_push($this->getvars, 'localize_class', 'lang1', 'lang2', 'save_localization');
		
		$this->action = "";

		if (isset($_GET['localize_class'])){
			$this->action = 'localize_class';
		} else if (isset($_GET['save_localization'])){
			$this->action = 'save_localization';
		}
	}

	function run(){
		
		$this->initialize();
		
		if ($this->pagenotfound){
			include("../includes/notfound.php");
			return;
		}
		
		switch ($this->action){
			case 'localize_class':
				return $this->editClassLabels();
				break;
			case 'save_localization':
				return $this->saveClassLabels();
				break;
			default:
				return $this->selectClassForm();
				break;
					
		}

	}
	
	public static function getLanguages() {
		return self::$_languages;
	}
	
	public static function languageDetermined() {
		return self::$_languageDetermined;
	}
	
	private static function setPhpLocale($lang) {
		/* Check output from locale -a */
		switch ($lang) {
			case 'no':	
				$new_loc = setlocale(LC_TIME, 'nb_NO.utf8','no_NO.utf8','no_NO');
				break;
			case 'en':	
				$new_loc = setlocale(LC_TIME, 'en_GB');
				break;
			case 'de':	
				$new_loc = setlocale(LC_TIME, 'de_DE');
				break;
			case 'fr':	
				$new_loc = setlocale(LC_TIME, 'fr_FR.utf8','fr_FR');
				break;
			case 'es':	
				$new_loc = setlocale(LC_TIME, 'es_ES');
				break;
		}
	}
	
	public static function determineLanguage() {
		self::$_languageDetermined = true;
		
		if (!isset($_SESSION['lang'])){
			$_SESSION['lang'] = self::$_defaultLang;
		}
		self::setPhpLocale($_SESSION['lang']);
		if (isset($_GET['set_lang'])) {
			
			$new_lang = $_GET['set_lang'];
			if (!in_array($new_lang, array_keys(self::$_languages))) {
				print "unknown language";
				return;
			}
			
			//print "set lang ".$new_lang; exit();
	
			$_SESSION['lang'] = $new_lang;
			$_SESSION['msg'] = self::$_languages[$_SESSION['lang']]['greeting'];
	
			self::setPhpLocale($_SESSION['lang']);
		
			$uri = explode("?",$_SERVER['REQUEST_URI']);
			$uri = $uri[0];
	
			$baseurl = $uri;
	
			$entity = "&";
			$ps = "";
			$notToInclude = array("set_lang","noprint","s");
			// Filter out temporary variables from $_GET
			foreach ($_GET as $n => $v){
				if (!in_array($n,$notToInclude)){
					$current = (empty($v) ? "$n" : "$n=$v");
					$ps .= ($ps == "") ? "$current" : "$entity$current";
				}
			}
			$uri = ($ps == "") ? $baseurl : "$baseurl?$ps";		
			header("Location: $uri");
			exit();
		}
	}
	
	function selectClassForm() {
		
		$res = $this->query("SELECT 
				$this->table_classes.id,
				$this->table_classes.friendlyname
			FROM
				$this->table_classes
				"
		);
		$classes = "<option value='0'>&lt;Generelt&gt;</option>";
		while ($row = $res->fetch_assoc()) {
			$classes .= "<option value='".$row['id']."'>".stripslashes($row['friendlyname'])."</option>";
		}
		$res = $this->query("SELECT 
				$this->table_languages.code,
				$this->table_languages.fullname
			FROM
				$this->table_languages
				"
		);
		$languages1 = "<option value='0' selected='selected'>Variabelnavn</option>\n";
		$languages2 = "<option value='0'>Variabelnavn</option>\n";
		$first = true;
		while ($row = $res->fetch_assoc()) {
			$languages1 .= "<option value='".$row['code']."'>".stripslashes($row['fullname'])."</option>\n";
			$sel = $first ? " selected='selected'" : ""; 
			$languages2 .= "<option value='".$row['code']."'$sel>".stripslashes($row['fullname'])."</option>\n";
			$first = false;
		}
		
		return '
			<form method="get" action="'.$this->generateURL('').'">
				
				<p>
					Klasse: 
					<select name="localize_class">
						'.$classes.'
					</select>
					Vis:
					<select name="lang1">
						'.$languages1.'
					</select>
					og
					<select name="lang2">
						'.$languages2.'
					</select>
			
					<input type="submit" value=" Ok " />
				</p>
			
			</form>
		';
	
	}
	
	function editClassLabels() {
		if (!isset($_GET['localize_class'])) $this->fatalError("invalid input .1");
		if (!isset($_GET['lang1'])) $this->fatalError("invalid input .2");
		if (!isset($_GET['lang2'])) $this->fatalError("invalid input .3");
		if (!is_numeric($_GET['localize_class'])) $this->fatalError("invalid input .4");
		if (strlen($_GET['lang1'])>2) $this->fatalError("invalid input .5");
		if (strlen($_GET['lang2'])>2) $this->fatalError("invalid input .6");

		$class = addslashes($_GET['localize_class']);
		$lang1 = addslashes($_GET['lang1']);
		$lang2 = addslashes($_GET['lang2']);
		$output = "";
		
		if ($class == '0') {
			$class_name = "&lt;Generelt&gt;";
		} else {
			$res = $this->query("SELECT friendlyname FROM $this->table_classes WHERE id=\"$class\"");
			if ($res->num_rows != 1) $this->fatalError("klassen eksisterer ikke");
			$row = $res->fetch_assoc();
			$class_name = stripslashes($row['friendlyname']);
		}
		if ($lang1 == '0') {
			$lang1_name = "Variabelnavn";
		} else {
			$res = $this->query("SELECT fullname FROM $this->table_languages WHERE code=\"$lang1\"");
			if ($res->num_rows != 1) $this->fatalError("språket eksisterer ikke");
			$row = $res->fetch_assoc();
			$lang1_name = stripslashes($row['fullname']);
		}

		if ($lang2 == '0') {
			$lang2_name = "Variabelnavn";
		} else {
			$res = $this->query("SELECT fullname FROM $this->table_languages WHERE code=\"$lang2\"");
			if ($res->num_rows != 1) $this->fatalError("språket eksisterer ikke");
			$row = $res->fetch_assoc();
			$lang2_name = stripslashes($row['fullname']);
		}

		$res = $this->query(
			"SELECT label,multiline
			FROM $this->table_classlabels
			WHERE class='$class'
			GROUP BY label
			ORDER BY id"
		);
		$labels = array();
		while ($row = $res->fetch_assoc()) {
			$labels[$row['label']] = array(
				'multiline' => $row['multiline'],
				'lang1' => '',
				'lang2' => ''

			);
		}

		$query = "SELECT 
				$this->table_classlabels.id,
				$this->table_classlabels.label,
				$this->table_classlabels.value,
				$this->table_classlabels.lang,
				$this->table_classlabels.multiline
			FROM
				$this->table_classlabels
			WHERE
				$this->table_classlabels.class='$class'";		
		if ($lang1 != '0' && $lang2 != '0')
			$query .= " AND ($this->table_classlabels.lang='$lang1' OR $this->table_classlabels.lang='$lang2')";
		else if ($lang1 != '0')
			$query .= " AND $this->table_classlabels.lang='$lang1'";
		else if ($lang2 != '0')
			$query .= " AND $this->table_classlabels.lang='$lang2'";
		else {
			$this->notSoFatalError("Du må velge minst et språk.");
			return;
		}
		$res = $this->query($query);
		while ($row = $res->fetch_assoc()) {
			$label = stripslashes($row['label']);
			$value = htmlspecialchars(stripslashes($row['value']));
			$lang = $row['lang'];
			
			if ($lang == $lang1)
				$labels[$label]['lang1'] = $value;
			else if ($lang == $lang2)
				$labels[$label]['lang2'] = $value;
		}
		
		$output .= '
			<p>
				You are editing the localization for the class: '.$class_name.'
			</p>
			<form method="post" action="'.$this->generateURL(array('noprint=true','save_localization')).'">
				<input type="hidden" name="class" value="'.$class.'" />
				<input type="hidden" name="language1" value="'.$lang1.'" />
				<input type="hidden" name="language2" value="'.$lang2.'" />
				<table><tr><td><strong>'.$lang1_name.'</strong></td><td><strong>'.$lang2_name.'</strong></td></tr>
			';
		
		foreach ($labels as $label => $value) {
			
			if ($lang1 == '0') {
				$field0 = $label;
			} else {
				if ($value['multiline'] == '1') {
					$field0 = "<textarea name='".$label."0' style='width:240px; height: 80px;'>".$value['lang1']."</textarea>";
				} else {
					$field0 = "<input type='text' name='".$label."0' value=\"".$value['lang1']."\" style='width:240px;'/>";			
				}
			}
			
			if ($lang2 == '0') {
				$field1 = $label;
			} else {		
				if ($value['multiline'] == '1') {
					$field1 = "<textarea name='".$label."1' style='width:240px; height: 80px;'>".$value['lang2']."</textarea>";
				} else {
					$field1 = "<input type='text' name='".$label."1' value=\"".$value['lang2']."\" style='width:240px;'/>";			
				}
			}
			
			$output .= "<tr><td>$field0</td><td>$field1</td></tr>\n";

		
		}
		$output .= "
				</table>
				<input type='submit' value='Lagre' />
			</form>\n";
		return $output;
	}
	
	function saveClassLabels() {
		if (!isset($_POST['class'])) $this->fatalError("invalid input .1");
		if (!isset($_POST['language1'])) $this->fatalError("invalid input .2");
		if (!isset($_POST['language2'])) $this->fatalError("invalid input .3");
		if (!is_numeric($_POST['class'])) $this->fatalError("invalid input .4");
		if (strlen($_POST['language1'])>2) $this->fatalError("invalid input .5");
		if (strlen($_POST['language2'])>2) $this->fatalError("invalid input .6");

		$class = $_POST['class'];
		$lang1 = $_POST['language1'];
		$lang2 = $_POST['language2'];
		
		$res = $this->query(
			"SELECT label,multiline
			FROM $this->table_classlabels
			WHERE class='$class'
			GROUP BY label"
		);
		$labels = array();
		while ($row = $res->fetch_assoc()) {
			$labels[$row['label']] = array(
				'multiline' => $row['multiline'],
				'lang1' => 0,
				'lang2' => 0

			);
		}
		
		$query = "SELECT 
				$this->table_classlabels.id,
				$this->table_classlabels.label,
				$this->table_classlabels.lang
			FROM
				$this->table_classlabels
			WHERE
				$this->table_classlabels.class='$class'";		
		if ($lang1 != '0' && $lang2 != '0')
			$query .= " AND ($this->table_classlabels.lang='$lang1' OR $this->table_classlabels.lang='$lang2')";
		else if ($lang1 != '0')
			$query .= " AND $this->table_classlabels.lang='$lang1'";
		else if ($lang2 != '0')
			$query .= " AND $this->table_classlabels.lang='$lang2'";
		else {
			$this->notSoFatalError("Du må velge minst et språk.");
			return;
		}
		$query .= " ORDER BY $this->table_classlabels.label";
		$res = $this->query($query);
		while ($row = $res->fetch_assoc()) {
			$label = stripslashes($row['label']);
			$id = $row['id'];
			$lang = $row['lang'];
			
			if ($lang == $lang1)
				$labels[$label]['lang1'] = $id;
			else if ($lang == $lang2)
				$labels[$label]['lang2'] = $id;
		}
		
		foreach ($labels as $label => $value) {
			
			$label = addslashes($label);
			$multiline = $value['multiline'];
			$lang1id = $value['lang1'];
			$lang2id = $value['lang2'];
			
			if ($lang1 != '0') {
				$lang1value = addslashes($_POST[$label."0"]);
				if ($lang1id == 0) {
					if (!empty($lang1value)) {
						$this->query("INSERT INTO $this->table_classlabels (class,lang,label,value,multiline) VALUES (
							'$class','$lang1','$label','$lang1value','$multiline'
						)");
					}
				} else {
					$this->query("UPDATE $this->table_classlabels SET value='$lang1value' WHERE id='$lang1id'");
				}
			}
			
			if ($lang2 != '0') {			
				$lang2value = addslashes($_POST[$label."1"]);
				if ($lang2id == 0) {
					if (!empty($lang2value)) {
						$this->query("INSERT INTO $this->table_classlabels (class,lang,label,value,multiline) VALUES (
							'$class','$lang2','$label','$lang2value','$multiline'
						)");
					}
				} else {
					$this->query("UPDATE $this->table_classlabels SET value='$lang2value' WHERE id='$lang2id'");
				}
			}
		
		}

		$this->redirect($this->generateURL(""),"Lokaliseringen ble lagret");
		
	}
	
}

?>
