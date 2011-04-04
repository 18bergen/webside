<?

class frontpage extends base {

	var $article_collection;
	var $articles_instance;

	var $noteboard;
	var $noteboard_instance;

	var $tropp_logg;
	var $tropp_logg_instance;
	var $flokk_logg;
	var $flokk_logg_instance;
	
	var $tropp_kal;
	var $tropp_kal_instance;
	var $flokk_kal;
	var $flokk_kal_instance;
	
	var $show_weeklyarticle = false;
	var $show_troppslogger = false;
	var $show_flokklogger = false;
	var $show_upcomingflokk = false;
	var $show_upcomingtropp = false;
	
	function run() {

		$this->setRssUrl("/nyheter/rss");

		$output = "
			<div id='aktuelt'>
			<div id='aktuelt1'><div id='aktuelt1sub'>
		";
		
		
		if ($this->show_weeklyarticle) {
			
			$this->articles_instance = new article_collection(); 
			call_user_func($this->prepare_classinstance, $this->articles_instance, $this->article_collection);
			$ukens_tips = $this->articles_instance->getLastArticle();
			
			$output .= '<div style="padding:2px;font-weight:bold">Ukens speidertips</div>';
			if ($ukens_tips === false) {
				$output .= '
					<div style="text-align:left">
						<em>Ingen speidertips publisert enda</em>
					</div>
				';
			} else {
				$output .= '
					<div style="text-align:left">
						<a href="'.$ukens_tips['uri'].'" class="icn" style="background-image:url(/images/lightbulb.gif);">'.$ukens_tips['topic'].'</a>
					</div>
				';
			}
		}
		
		if ($this->show_troppslogger) {
		
			$this->tropp_logg_instance = new log(); 
			call_user_func($this->prepare_classinstance, $this->tropp_logg_instance, $this->tropp_logg);
			$this->tropp_logg_instance->initializeCalendar();
			$siste_logger = $this->tropp_logg_instance->getLastLogsGlobal(4);

			$logs = "";
			foreach ($siste_logger as $log) {
				
				$dsa = getdate($log['cal_event']['startdate']);
				$dea = getdate($log['cal_event']['enddate']);
				if ($dsa['mday'].".".$dsa['mon'].".".$dsa['year'] == $dea['mday'].".".$dea['mon'].".".$dea['year']){
					$dss = $dsa['mday'].".".$dsa['mon'];
				} else if ($dsa['mon'].".".$dsa['year'] == $dea['mon'].".".$dea['year']){
					$dss = $dsa['mday'].".".$dsa['mon']." - ".$dea['mday'].".".$dea['mon'];
				} else {
					$dss = $dsa['mday'].".".$dsa['mon']." - ".$dea['mday'].".".$dea['mon'];
				}
				
				$logs .= '<a href="'.$log['uri'].'" class="icn smallicn" style="background-image:url(/images/document10_12.gif);">'.$log['cal_event']['caption'].' '.$dss.'</a>';
			}
			if (empty($logs)) {
				$logs = '<em>Ingen logger er publisert enda</em>';
			}
			$output .= '
				<div style="padding:2px;font-weight:bold">Siste logger:</div>
				<div style="text-align:left">
					'.$logs.'
				</div>
			';
		}
		
		
		$output .= '
			</div></div>
			
			

			
			<div id="aktuelt2"><div id="aktuelt2sub">
		';
		if ($this->show_upcomingtropp) {
			
			$this->tropp_kal_instance = new calendar_basic(); 
			call_user_func($this->prepare_classinstance, $this->tropp_kal_instance, $this->tropp_kal);
			$this->tropp_kal_instance->use_log = false;
			$this->tropp_kal_instance->use_iarchive = false;
			$this->tropp_kal_instance->initialize();		
			$this->tropp_kal_instance->entries_per_page = 3;
			$this->tropp_kal_instance->noentries_future_template = '<i>Ingen hendelser</i>';
			$this->tropp_kal_instance->calview_template = '
				%noentries% %entries%
			';
			$this->tropp_kal_instance->calview_entry_template = '
					<div>
						<a href="%detailsurl%" class="icn smallicn" style="background-image:url(/images/calendar_red.gif);">%subject% (%shortdate%)</a>
					</div>
			';
			
			$output .= '
				<div style="padding:2px;font-weight:bold">Nærmer seg (tropp):</div>
				<div style="text-align:left">
					'.$this->tropp_kal_instance->viewCalendar().'
				</div>
			';
		}
		if ($this->show_upcomingflokk) {

				$this->flokk_kal_instance = new calendar_basic(); 
				call_user_func($this->prepare_classinstance, $this->flokk_kal_instance, $this->flokk_kal);
				$this->flokk_kal_instance->use_log = false;
				$this->flokk_kal_instance->use_iarchive = false;
				$this->flokk_kal_instance->initialize();		
				$this->flokk_kal_instance->entries_per_page = 3;
				$this->flokk_kal_instance->noentries_future_template = '<i>Ingen hendelser</i>';
				$this->flokk_kal_instance->calview_template = '
					%noentries% %entries%
				';
				$this->flokk_kal_instance->calview_entry_template = '
						<div>
							<a href="%detailsurl%" class="icn smallicn" style="background-image:url(/images/calendar_red.gif);">%subject% (%shortdate%)</a>
						</div>
				';

				$output .= '
					<div style="padding:2px;font-weight:bold">Nærmer seg (flokk):</div>
					<div style="text-align:left">
						'.$this->flokk_kal_instance->viewCalendar().'
					</div>
				';
		}
		$output .= '
			</div></div></div>
			
			<script type="text/javascript">
			//<![CDATA[
				YAHOO.util.Event.onDOMReady(function() {
					Nifty("div#aktuelt1");
					Nifty("div#aktuelt2");
				});
			//]]>
			</script>

			<div style="height:1px; clear:both;"><!-- --></div>
		';	

		$this->noteboard_instance = new noteboard(); 
		call_user_func($this->prepare_classinstance, $this->noteboard_instance, $this->noteboard);
		$this->noteboard_instance->initialize();
		$output .= $this->noteboard_instance->printEntries();
		
		return $output;
	}

}


?>
