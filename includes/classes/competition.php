<?php
class competition extends base {

	var $getvars = array("editpage");

	var $table_desc = "compet_pages";
	var $table_groups = "compet_participants";
	var $table_rounds = "compet_rounds";
	var $table_points = "compet_points";
	var $images_per_page = 20;

	var $FCKeditorWidth = 450;
	
	function __construct() {
		$this->table_desc = DBPREFIX.$this->table_desc;
		$this->table_groups = DBPREFIX.$this->table_groups;
		$this->table_rounds = DBPREFIX.$this->table_rounds;
		$this->table_points = DBPREFIX.$this->table_points;
	}
	
	function initialize(){
	
		@parent::initialize(); //$this->initialize_base();

		//array_push($this->getvars,'editpage','savepage','editpart','savepart','deletepart','addentry');
		
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

			case 'edit-participants':
				return $this->editParticipants();
				break;

			case 'add-participant':
				return $this->addParticipant();
				break;

			case 'delete-participant':
				return $this->deleteParticipant();
				break;

			case 'add-entry':
				return $this->addEntry();
				break;

			case 'delete-entry':
				return $this->deleteEntry();
				break;

			case 'chart-data':
				return $this->generateChartData();
				break;

			case 'table-data':
				return $this->generateTableData();
				break;
							
			/******* DEFAULT *******/
			
			default:
				return $this->welcome();
				break;
		
		}

	}
	
	function welcome() {
	
		$res = $this->query("SELECT intro FROM $this->table_desc WHERE page=".$this->page_id);
		if ($res->num_rows == 0) {
			return "
				<p>
					Denne konkurransen er ikke ferdig satt opp enda. Trykk på linken under for å
					skrive inn en kort beskrivelse som vil vises øverst på siden. Denne kan 
					redigeres senere.
				</p>
				<p>
					<a href='?editpage' class='icn' style='background-image:url(/images/icns/pencil.png);'>Skriv beskrivelse</a>
				</p>
			";
		}
		$row = $res->fetch_assoc();

		$res2 = $this->query("SELECT id FROM $this->table_groups WHERE page=".$this->page_id);
		if ($res2->num_rows == 0) {
			return "
				<p>
					Denne konkurransen er ikke ferdig satt opp enda. Trykk på linken under for
					å legge til deltakere. Deltakere kan legges til og slettes frem til de første
					poengene er lagt til, men <em>ikke</em> etter dette.
				</p>
				<p>
					<a href=\"".$this->generateCoolURL("/edit-participants")."\" class=\"icn\" style=\"background-image:url(/images/icns/group.png);\">Legg til deltakere</a>
				</p>
			";
		}

		$output = "";
		$desc = stripslashes($row['intro']);
		
		if ($this->allow_write) {
			$output .= $this->make_editlink($this->generateURL("editpage"), "Rediger side");

			$res2 = $this->query("SELECT id FROM $this->table_rounds WHERE page=".$this->page_id);
			$editPartsLink = ($res2->num_rows == 0) ? "<a href=\"".$this->generateCoolURL("/edit-participants")."\" class=\"icn\" style=\"background-image:url(/images/icns/group.png);\">Rediger deltakere</a>" : ""; 

			$output .= "
				<p>
					<a href=\"".$this->generateURL("editpage")."\" class=\"icn\" style=\"background-image:url(/images/icns/pencil.png);\">Rediger beskrivelse</a>
					$editPartsLink
				</p>
			";
		}
						
		$output .= '
			<div style="padding:10px 0px 10px 0px;">'.$desc.'</div>
			<div id="divErrorMsg" style="display:none;border:2px solid red;background:white;padding:5px;"></div>
		';

		$res = $this->query("SELECT id,caption,description FROM $this->table_groups WHERE page=".$this->page_id);
		$parts = array();
		while ($row = $res->fetch_assoc()) {
			$id = $row['id'];
			$parts[] = array(
				'id' => $id,
				'name' => $row['caption']
			);
		}

		$groupsStr = '<table cellpadding="1" cellspacing="0">';
		$listeners = '';
		$groupIDs = array();
		foreach ($parts as $g) {
			$id = $g['id'];
			$groupIDs[] = $id;
			$groupsStr .= '<tr><td valign="top">'.$g['name'].': </td><td valign="top"><input type="text" id="formGroupP'.$id.'" value="" size="10" /></td><td valign="top"><div id="formGroupP'.$id.'nfo" style="color:red;font-size:10px;padding-left:5px;"></span></td></tr>';
			$listeners .= '
				$("#formGroupP'.$id.'").on("keydown",checkIntKey);
				$("#formGroupP'.$id.'").on("blur",onTxtBlur);';
		}
		$groupsStr .= '</table>';

		$chartDataUrl = $this->generateCoolUrl("/chart-data");
		$tableDataUrl = $this->generateCoolUrl("/table-data");
		
		if ($this->allow_write) {
			$addUrl = $this->generateCoolUrl("/add-entry");
			$delUrl = $this->generateCoolUrl("/delete-entry");
			$output .= '
				<a id="btnAddContrib" href="#" onclick="return false;" class="icn" style="background-image:url(/images/icns/add.png);">Registrér nye poeng</a>
				<div id="formAddContribDiv" style="">
				<form method="post" action="/" id="formAddContrib" style="border:1px solid #ccc;background:#eee;padding:10px;">
					<table><tr><td valign="top" style="width:150px;font-weight:bold;text-align:right;font-size:11px;">Beskrivelse: </td><td><input type="text" id="formDesc" value="" size="50" style="width:300px;" /><br />
					<span style="font-size:10px;">F.eks. «Troppsmøte september» eller «O-løp».</span></td></tr>
					<tr><td valign="top" style="font-weight:bold;text-align:right;font-size:11px;">Maks antall poeng: </td><td><input type="text" id="formMaxP" value="" size="10" /><span id="formMaxPnfo" style="color:red;font-size:10px;padding-left:10px;"></span></td></tr>
					<tr><td valign="top" style="font-weight:bold;text-align:right;font-size:11px;">
					Poeng:</td><td>
					'.$groupsStr.'
					</td></tr></table>
					<input id="btnCancelContrib" type="button" value="Avbryt" />
					<input id="btnSubmitContrib" type="submit" value="Legg til" />
					<span id="workIndicator" style="display:none;">Lagrer...</span>
					<span id="successIndicator" style="visibility:hidden;background:url(/images/icns/accept.png) left no-repeat; padding:2px 2px 2px 20px;"> Lagret</span>
				</form>
				</div>
				
				<script type="text/javascript">
					//<![CDATA[
					
					var parts = ['.implode($groupIDs,",").'];
				
					function cancelContribForm(e) {
						e.preventDefault();
						$("#btnAddContrib").css("display","");
						$("#formAddContribDiv").css("display","none");	
						$("#workIndicator").css("display","none");
						$("#successIndicator").css("visibility","hidden");
					}
					
					function addContribForm(e) {
						e.preventDefault();
						$("#btnAddContrib").css("display","none");
						$("#formAddContribDiv").css("display","block");
						//$("#formDesc").val("");
						$("#formDesc").focus();
					}
					
					function submitNewContrib(e) {
						e.preventDefault();
						/*
							Note: As of YUI 2.8.0, there is a bug in JSON.stringify
							so arrays don\'t get right, while objects do. Therefore, we
							avoid arrays.
						*/
						var postData = { 
							description: $("#formDesc").val(),
							maxPoints: $("#formMaxP").val()
						};
						if (postData["description"] == "") {
							alert("Du glemte(?) å fylle inn beskrivelse");
							return;
						}
						if (Number(postData["maxPoints"]) <= 0) {
							alert("Du glemte(?) å fylle inn maks antall poeng");
							return;
						}
						for (var i = 0; i < parts.length; i++) {
							postData["group"+parts[i]] = $("formGroupP"+parts[i]).value;
						}

						$("#successIndicator").css("visibility","hidden");
						$("#workIndicator").show();

						disableForm();

						$.post("'.$addUrl.'", postData).done(contribAdded).error(contribAddFailed)
					}
					
					function errorMsg(msg) {
						$("#divErrorMsg").show();				
						$("#divErrorMsg").html(msg);
					}
					
					function clearError() {
						$("#divErrorMsg").hide();			
					}

					function disableForm() {
						$("#btnSubmitContrib").prop("disabled", true);
						$("#btnCancelContrib").prop("disabled", true);
						$("#formDesc").prop("disabled", true);
						$("#formMaxP").prop("disabled", true);
						
						for (var i = 0; i < parts.length; i++) {
							$("#formGroupP"+parts[i]).prop("disabled", true);
						}
					}
					
					function enableForm() {
						$("#btnSubmitContrib").prop("disabled", false);
						$("#btnCancelContrib").prop("disabled", false);
						$("#formDesc").prop("disabled", false);
						$("#formMaxP").prop("disabled", false);
						
						for (var i = 0; i < parts.length; i++) {
							$("#formGroupP"+parts[i]).prop("disabled", false);
						}
					}
					
					function contribAdded(response) {
						enableForm();
						$("#successIndicator").css("visibility","visible");
						$("#workIndicator").hide();
						//$("inpPartName").disabled = "";
						$("#formDesc").val("");
						$("#formMaxP").val("");
						for (var i = 0; i < parts.length; i++) {
							$("#formGroupP"+parts[i]).val("");
						}
						$("#formDesc").focus();
						json = response;
						barChart0.refreshData();
						tableData.sendRequest();
					}
					
					function contribAddFailed(o) {
						enableForm();
						$("#workIndicator").css("visibility","hidden");
						
					}
					
					function rowDeleted(o) {
						$("#btnDeleteRow").css("display","");
						$("#deleteProgress").css("visibility","hidden");
						var json = YAHOO.lang.JSON.parse(o.responseText);
						barChart0.refreshData();
						tableData.sendRequest();
					}
					
					function rowDelFailed(o) {
						$("#btnDeleteRow").css("display","");
						$("#deleteProgress").css("visibility","hidden");						
					}
					
					
					function deleteSelectedRow(e) {
						e.preventDefault();
						if (selectedRow == -1) {
							alert("Du må velge en rad.");
							return;
						}
						if (confirm("Er du helt sikker på at du ønsker å slette denne raden?")) {
							$("#btnDeleteRow").css("display","none");
							$("#deleteProgress").css("visibility","visible");
							var postData = { id: selectedRow };
							var chartData = YAHOO.lang.JSON.stringify(postData);
							YAHOO.util.Connect.asyncRequest("POST", "'.$delUrl.'", {
								success: rowDeleted,
								failure: rowDelFailed
							}, "req="+chartData );
							selectedRow = -1;
						}
					}
					
					function checkIntKey(e,f,g) {
						$("#successIndicator").css("visibility","hidden");

						if ((!e.shiftKey && !e.altKey) && ((e.keyCode >= 48 && e.keyCode <= 57) || (e.keyCode >= 96 && e.keyCode <= 105))) {
							// ok
						} else if (e.keyCode == 16 || e.keyCode == 8 || e.keyCode == 9 || e.keyCode == 13 || e.keyCode == 16 || e.keyCode == 17 || e.keyCode == 18 || e.keyCode == 19 || (e.keyCode >= 33 && e.keyCode <= 40) || e.keyCode == 224) {
						
						} else {
							var t = YAHOO.util.Event.getTarget(e);
							$(t.id+"nfo").innerHTML = "Skriv kun inn tall (#"+e.keyCode+")"; 
							e.preventDefault();
						}
					}
					
					function onTxtBlur(e) {
						var t = YAHOO.util.Event.getTarget(e);
						$(t.id+"nfo").innerHTML = "";
						var maxP = Number($("#formMaxP").val());
						var P = Number(t.value);
						if (isNaN(P)) P = 0;
						t.value = P;
						if ((t.id != "formMaxP") && (maxP > 0) && (P > maxP)) {
							$(t.id+"nfo").innerHTML = "Verdien ("+P+") overskrider maks antall poeng ("+maxP+")";
							e.preventDefault();
						}
					}
					
					function initAdminStuff() {
						$("#formAddContribDiv").css("display","none");
						$("#btnDeleteRow").on("click",deleteSelectedRow);
						$("#btnAddContrib").on("click",addContribForm);
						$("#btnCancelContrib").on("click",cancelContribForm);
						$("#formAddContrib").on("submit",submitNewContrib);
						$("#formMaxP").on("keydown",checkIntKey);
						$("#formMaxP").on("blur",onTxtBlur);
						'.$listeners.'
					}
					
				//]]>
				</script>		
				
			';
		}
		$output .= '
			<div style="padding-top:20px;">
				<div id="chart" style="width:490px;height:250px;">
					Unable to load Flash content. The YUI Charts Control requires Flash Player 9.0.45 or higher. 
					You can download the latest version of Flash Player from the 
					<a href="http://www.adobe.com/go/getflashplayer">Adobe Flash Player Download Center</a>.
				</div>
			</div>
			<div style="padding-top:20px;">
				<div id="datatable"></div>
				<div style="font-size:10px;">«Dato» er datoen poengene ble registrert på 
					nettsiden.</div>
			</div>
		
			<script type="text/javascript">
			//<![CDATA[
			
				selectedRow = -1; // Hurray, globals!!

				// ============================= Load YUI components ==============================

				
				loader.require("charts","datatable");
				loader.insert();
				
				function onYuiLoaderComplete() {
					console.log("chart loaded");
					genChart();
					'.($this->allow_write ? 'initAdminStuff();':'').'
				}
												
				// ============================= Generate chart ==============================
	
				function genChart() {

					// ============================= Generate chart ==============================

					
					var chartData = new YAHOO.util.DataSource( "'.$chartDataUrl.'" );
					//use POST so that IE doesn\'t cache the data
					chartData.connMethodPost = true;
					chartData.responseType = YAHOO.util.DataSource.TYPE_JSON;
					chartData.responseSchema =
						{
							resultsList: "Results",
							fields: ["Id","Name","Points"]
						};
				
					var yAxis = new YAHOO.widget.NumericAxis();
					yAxis.minimum = 0;
				
					barChart0 = new YAHOO.widget.ColumnChart( "chart", chartData, {
						xField: "Name",
						yField: "Points",
						yAxis: yAxis,
						backgroundColor: "#edf0ed",
						//only needed for flash player express install
						expressInstall: "assets/expressinstall.swf"
					});
					
					// ============================ Generate datatable ============================

					tableData = new YAHOO.util.DataSource( "'.$tableDataUrl.'" );
					//use POST so that IE doesn\'t cache the data
					tableData.connMethodPost = true;
					tableData.responseType = YAHOO.util.DataSource.TYPE_JSON;
					tableData.responseSchema = { resultsList: "Results" };
					tableData.subscribe("responseParseEvent",dataTableUpdated);
					
					var columns = ['.implode($this->getTableColumnsList(),',').'];
					dataTable0 = new YAHOO.widget.DataTable( "datatable", columns, tableData, {
						selectionMode:"single"
					});
					
					// Subscribe to events for row selection
					dataTable0.subscribe("rowMouseoverEvent", dataTable0.onEventHighlightRow);
					dataTable0.subscribe("rowMouseoutEvent", dataTable0.onEventUnhighlightRow);
					';
					if ($this->allow_write) {
						$output .= '
						dataTable0.subscribe("rowClickEvent", dataTable0.onEventSelectRow);
						dataTable0.subscribe("rowSelectEvent", onRowSelect);
						';
					}
					$output .= '



					// ================================= End =====================================

				}
				
				function dataTableUpdated(args) {
					console.log("Table updated");
					dataTable0.onDataReturnInitializeTable(args.request,args.response);
				}
				
				function onRowSelect(args,a2) {
					var dat = args.record.getData();
					console.log(dat);
					console.log(dat["Id"]);
					selectedRow = dat["Id"];
				}
								
			//]]>
			</script>	
		
		';
		
		if ($this->allow_write) {
		
			$output .= "
				<a id=\"btnDeleteRow\" href=\"#\" class=\"icn\" style=\"background-image:url(/images/icns/cross.png);\">Slett valgt rad</a>
				<span id=\"deleteProgress\" style=\"visibility:hidden;\">Sletter rad...</span>
			";
		
		}

			
		return $output;
	}
	
	function editPageForm() {
		
		if (!$this->allow_write) return $this->permissionDenied();

		$res = $this->query("SELECT intro FROM $this->table_desc WHERE page=".$this->page_id);
		if ($res->num_rows == 0) {
			$desc = "";
		} else {
			$row = $res->fetch_assoc();
			$desc = stripslashes($row['intro']);
		}
		
		$this->setDefaultCKEditorOptions();
		
		return '
			<form method="post" action="'.$this->generateCoolUrl('/savepage','noprint=true').'">

				<h2>Beskrivelse</h2>				
				<p>
					<textarea id="editor_body" name="editor_body" style="width:'.$this->FCKeditorWidth.'px; height:400px;">'.$desc.'</textarea>
				</p>
				<input type="submit" value="Lagre beskrivelse" />
			</form>
			
			<script type=\'text/javascript\'>
			//<![CDATA[

					var editor = CKEDITOR.replace( "editor_body", { 
						customConfig : "'.LIB_CKEDITOR_URI.'config_18bergen.js",
						toolbar: "VerySimpleBergenVS",
						width: 500,
						resize_minWidth:500,
						resize_maxWidth:500, // disables horizontal resizing
						filebrowserImageBrowseUrl: "'.LIB_CKFINDER_URI.'ckfinder.html?type=Bilder&start=Bilder:/Diverse&rlf=0&dts=1",
						filebrowserImageUploadUrl: "'.LIB_CKFINDER_URI.'core/connector/php/connector.php?command=QuickUpload&type=Bilder&currentFolder=/Diverse/"
					});
					/*	
					CKFinder.SetupCKEditor( editor, {
						basePath: "'.LIB_CKFINDER_URI.'"
					}, "Bilder");
					*/
			//]]>
			</script>
		';
		
	}
	
	function savePage() {

		if (!$this->allow_write) return $this->permissionDenied(); 
		
		$intro = addslashes($_POST['editor_body']);
		
		$res = $this->query("SELECT page FROM $this->table_desc WHERE page=".$this->page_id);
		if ($res->num_rows == 0) {
			$this->query("INSERT INTO $this->table_desc (intro,page) VALUES (\"$intro\", ".$this->page_id.")");		
		} else {
			$this->query("UPDATE $this->table_desc SET intro=\"$intro\" WHERE page=".$this->page_id);
		}
		
		$this->redirect($this->generateCoolUrl("/"), "Siden ble lagret");
		
	}
	
	
	function editParticipants() {

		if (!$this->allow_write) return $this->permissionDenied(); 
		
		$res = $this->query("SELECT id FROM $this->table_rounds WHERE page=".$this->page_id);
		if ($res->num_rows > 0) {
			return $this->notSoFatalError("Beklager, deltakere kan ikke redigeres etter at de første
				poengene er lagt til i konkurransen.");
		}
		
		
		$res = $this->query("SELECT id,caption,description FROM $this->table_groups WHERE page=".$this->page_id);
		$output = '
			  <a href="'.$this->generateCoolUrl('/').'" class="icn" style="background-image:url(/images/icns/arrow_left.png);">Tilbake</a>
			<h2>Rediger deltakere</h2>
			<div id="divErrorMsg" style="display:none;border:2px solid red;background:white;padding:5px;"></div>
			<p>
			  <a id="btnAddPart" href="#" class="icn" style="background-image:url(/images/icns/add.png);">Legg til deltaker</a>
			</p>
			<div id="addPartForm" style="display:none;">
				<form id="newPartForm" action="#" onsubmit="return false;">
					<input type="text" id="inpPartName">
					<input type="button" id="btnCancelNewPart" value="Avbryt">
					<input type="submit" id="btnSaveNewPart" value="Legg til">
				</form>
				<span id="workIndicator" style="visibility:hidden;">Vent...</span>
			</div>		
			<div id="partsTable">
			
			</div>
		';
		
		$parts = array();
		while ($row = $res->fetch_assoc()) {
			$id = $row['id'];
			$parts[] = "{ id: ".$id.", name: \"".$row['caption']."\" }";
		}
		$addUrl = $this->generateCoolUrl("/add-participant");
		$delUrl = $this->generateCoolUrl("/delete-participant");
		$output .= '
		
		<script type="text/javascript">
			
			var parts = ['.implode($parts,",").'];
		
			function cancelPartForm(e) {
				e.preventDefault();
				$("#btnAddPart").show();
				$("#addPartForm").hide();			
			}
			
			function addPartForm(e) {
				e.preventDefault();
				$("#btnAddPart").hide();
				$("#addPartForm").show();
				$("#inpPartName").val("");
				$("#inpPartName").focus();
			}
			
			function submitNewPart(e) {
				e.preventDefault();
				var postData = { name: $("#inpPartName").val() };
				var chartData = YAHOO.lang.JSON.stringify(postData);
				$("#workIndicator"").css("visibility","visible");
				$("#inpPartName").blur();
				$("#inpPartName").prop("disabled", true);
				$.post("'.$addUrl.'", chartData)
				.done(partAdded)
				.error(partAddFailed);
			}
			
			function errorMsg(msg) {
				$("#divErrorMsg").show();				
				$("#divErrorMsg").html(msg);
			}
			
			function clearError() {
				$("#divErrorMsg").hide();			
			}
			
			function partAdded(response) {
				console.log(response);
				$("#workIndicator".css("visibility","hidden");
				$("#inpPartName").prop("disabled", false);
				$("#inpPartName").val("");
				$("#inpPartName").focus();
				json = response;
				parts.push(json);
				redraw();
			}
			
			function partAddFailed(o) {
				$("#workIndicator").css("visibility","hidden");	
			}
			
			function partDeleted(response) {
				console.log(response);
				var json = response;
				parts = json;
				//console.log(parts);
				redraw();
			}
			
			function partDelFailed(o) {
				
			}
			
			function redraw() {
				var o = "<table cellpadding=\"3\">";
				for (var i = 0; i < parts.length; i++) {
					o += "<tr><td>"+parts[i]["name"]+"</td>"+
						"<td><a id=\"deletepart"+parts[i]["id"]+"\" onclick=\"confirmDeletePart("+parts[i]["id"]+");return false;\" href=\"#\"><img src=\"/images/icns/cross.png\" border=\"0\" title=\"Slett deltaker\" /></a></td>"+
						"</tr>";
				}
				o += "</table>";
				$("#partsTable").html(o);
			}
			
			function confirmDeletePart(id) {
				if (confirm("Er du helt sikker?")) {
					var postData = { id: id };
					$.post("'.$delUrl.'", postData).done(partDeleted).error(partDelFailed);
				}
			}
			
			$(document).ready(function() {
				$("#btnAddPart").on("click", addPartForm);
				$("#btnCancelNewPart").on("click", cancelPartForm);
				$("#newPartForm").on("submit", submitNewPart);
				redraw();
			});
			
		//]]>
		</script>		
		';
		return $output;	
	}
	
	function addParticipant() {
		if (!$this->allow_write) {
			echo json_encode(array(
				'people' => array(),
				'error' => 'Du er ikke logget inn'
			));		
			exit();
		}
		$handle = fopen('php://input','r');
		$jsonInput = fgets($handle);
		$json = json_decode($jsonInput,true);
		$name = $json['name'];
		if (empty($name)) {
			echo json_encode(array(
				'error' => 'Tomt navn'
			));
			exit();
		}
		
		$res = $this->query("INSERT INTO $this->table_groups (page,caption) VALUES (".$this->page_id.",\"".addslashes($name)."\")");
		$id = $this->insert_id();

		echo json_encode(array(
			'id' => $id,
			'name' => $name,
			'error' => '0'
		));
		exit();

	}
	
	function deleteParticipant() {
		if (!$this->allow_write) {
			echo json_encode(array(
				'people' => array(),
				'error' => 'Du er ikke logget inn'
			));		
			exit();
		}
		$handle = fopen('php://input','r');
		$jsonInput = fgets($handle);
		$json = json_decode($jsonInput,true);
		$id = intval($json['id']);
		
		$id = intval($id);
		$res = $this->query("DELETE FROM $this->table_groups WHERE page=".$this->page_id." AND id=$id");
		$res = $this->query("SELECT id,caption,description FROM $this->table_groups WHERE page=".$this->page_id);		
		$parts = array();
		while ($row = $res->fetch_assoc()) {
			$id = $row['id'];
			$parts[] = array(
				'id' => $id,
				'name' => $row['caption']
			);
		}	
		echo json_encode($parts);
		exit();
	}
	
	function addEntry() {
		if (!$this->allow_write) {
			echo json_encode(array(
				'people' => array(),
				'error' => 'Du er ikke logget inn'
			));		
			exit();
		}
		$json = json_decode($_POST['req'],true);
		if (!isset($json['description'])) {
			echo json_encode(array(
				'error' => 'Invalid input'
			));		
			exit();
		}
		$maxPoints = intval($json['maxPoints']);		
		$res = $this->query("INSERT INTO $this->table_rounds (page,max_points,description,timestamp) VALUES (".$this->page_id.",".
			$maxPoints.",".
			'"'.addslashes($json['description']).'",NOW())'
		);
		$round_id = $this->insert_id();
		
		$res = $this->query("SELECT id FROM $this->table_groups WHERE page=".$this->page_id);		
		while ($row = $res->fetch_assoc()) {
			$id = $row['id'];
			if (isset($json['group'.$id])) {
				$this->query("INSERT INTO $this->table_points (round_id,participant_id,points) VALUES (".$round_id.",".
					intval($id).",".
					intval($json['group'.$id]).')'
				);			
			}
		}
		
		echo json_encode(array(
			'error' => '0'
		));
		exit();
		
	}
	
	function deleteEntry() {
		if (!$this->allow_write) {
			echo json_encode(array(
				'error' => 'Du er ikke logget inn'
			));		
			exit();
		}
		$json = json_decode($_POST['req'],true);
		$id = intval($json['id']);
		
		$id = intval($id);
		if ($id <= 0) {
			echo json_encode(array(
				'error' => 'Invalid input'
			));		
			exit();
		}
		$this->query("DELETE FROM $this->table_rounds WHERE page=".$this->page_id." AND id=$id");
		if ($this->affected_rows() == 1) {
			$this->query("DELETE FROM $this->table_points WHERE round_id=$id");		
		}
		echo json_encode(array('error' => 0));
		exit();
	}
	
	function generateChartData() {
		$r = $this->table_rounds;
		$p = $this->table_points;
		$g = $this->table_groups;

		$parts = array();
		$partsIndex = array();
		$res = $this->query("SELECT id,caption FROM $g WHERE page=".$this->page_id);		
		while ($row = $res->fetch_assoc()) {
			$parts[] = array('Id' => intval($row['id']),'Name' => stripslashes($row['caption']),'Points' => 0);
			$partsIndex[intval($row['id'])] = count($parts)-1;
		}

		$res = $this->query("SELECT id as round_id,max_points,description,timestamp FROM $r WHERE page=".$this->page_id);		
		$data = array();
		while ($row = $res->fetch_assoc()) {
			$round_id = intval($row['round_id']);
			$res2 = $this->query("SELECT $g.id as part_id, $g.caption as part_name, $p.points FROM $p,$g WHERE $p.round_id=$round_id AND $p.participant_id=$g.id");
			while ($row2 = $res2->fetch_assoc()) {
				$points = intval($row2['points']);
				$part_id = intval($row2['part_id']);
				$parts[$partsIndex[$part_id]]['Points'] += $points;
			}
		}
		header('Content-type: application/json');
		header("Content-Type: text/html; charset=utf-8");
		$output = array(
			'Results' => $parts
		);
		echo json_encode($output);
		exit();
	}
	
	function getTableColumnsList() {
		$r = $this->table_rounds;
		$p = $this->table_points;
		$g = $this->table_groups;
		$fields = array(
			'{ key: "Dato", sortable: false, resizeable: true }',
			'{ key: "Beskrivelse", sortable: false, resizeable: true }'
		);
		$rounds = array();
		$res = $this->query("SELECT id,caption FROM $g WHERE page=".$this->page_id);		
		while ($row = $res->fetch_assoc()) {
			$fields[] = '{ key: "'.$row['caption'].'", sortable: false, resizeable: true, formatter: "number" }';
		}
		return $fields;
	}
	
	function generateTableData() {
		$r = $this->table_rounds;
		$p = $this->table_points;
		$g = $this->table_groups;

		$rounds = array();

		$res = $this->query("SELECT id as round_id,max_points,description,timestamp FROM $r WHERE page=".$this->page_id." ORDER by timestamp");		
		$data = array();
		while ($row = $res->fetch_assoc()) {
			$round_id = intval($row['round_id']);
			$round = array(
				'Id' => $round_id,
				'Dato'=> strftime("%e. %b. %y",strtotime($row['timestamp'])),
				'Beskrivelse'=> $row['description']
			);
			$res2 = $this->query("SELECT $g.id as part_id, $g.caption as part_name, $p.points FROM $p,$g WHERE $p.round_id=$round_id AND $p.participant_id=$g.id");
			while ($row2 = $res2->fetch_assoc()) {
				$points = intval($row2['points']);
				$part_name = stripslashes($row2['part_name']);
				$round[$part_name] = $points;
			}
			$rounds[] = $round;
		}
		header('Content-type: application/json');
		header("Content-Type: text/html; charset=utf-8");
		$output = array(
			'Results' => $rounds
		);
		echo json_encode($output);
		exit();
	}

}
?>
