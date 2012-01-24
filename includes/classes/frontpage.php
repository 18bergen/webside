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
	
	var $upcoming_events_days = 14;
	
	function get_newest_logs() {
			
        $logs = new log(); 
        call_user_func($this->prepare_classinstance, $logs, $this->tropp_logg);
        $logs->initializeCalendar();
        $last_logs = $logs->getLastLogsGlobal(4);

        $output = '';
        foreach ($last_logs as $log) {
            
            $dsa = getdate($log['cal_event']['startdate']);
            $dea = getdate($log['cal_event']['enddate']);
            if ($dsa['mday'].".".$dsa['mon'].".".$dsa['year'] == $dea['mday'].".".$dea['mon'].".".$dea['year']){
                // Dagshendelse
                $dss = strftime("%e. %B",$log['cal_event']['startdate']);
            } else if ($dsa['mon'].".".$dsa['year'] == $dea['mon'].".".$dea['year']){
                // Flerdagshendelse, samme måned
                $dss = strftime("%e.–",$log['cal_event']['startdate']).strftime("%e. %B",$log['cal_event']['enddate']);
            } else {
                $dss = strftime("%e. %B – ",$log['cal_event']['startdate']).strftime("%e. %B",$log['cal_event']['enddate']);
            }
            $output .= "<li class='".$log['flag']."'>".
                $log['caption'].': '.
                '<a href="'.$log['uri'].'">'.$log['cal_event']['caption'].' '.$dss.'</a>'.
                ' <small>(skrevet '.strftime("%e. %B",$log['lastmodified']).')</small>'.
                '</li>';
        }
        if (empty($logs)) {
            $output = '<li><em>Ingen logger er publisert enda</em></li>';
        }
        return $output;
	}
	
	function get_upcoming_events() {
		$cal = $this->initializeCalendarInstance();
		$events = $cal->getCalendarEvents(0, array( 'onlyFutureEvents' => true, 'maxFutureDays' => $this->upcoming_events_days ));
		if (count($events) == 0) return '<li><em>Det store intet</em></li>';
		$output = '';
		foreach ($events as $event) {
		    $dsa = getdate($event['startdate']);
            $dea = getdate($event['enddate']);
            if ($dsa['mday'].".".$dsa['mon'].".".$dsa['year'] == $dea['mday'].".".$dea['mon'].".".$dea['year']){
                // Dagshendelse
                $dss = strftime("%e. %B",$event['startdate']);
            } else if ($dsa['mon'].".".$dsa['year'] == $dea['mon'].".".$dea['year']){
                // Flerdagshendelse, samme måned
                $dss = strftime("%e.–",$event['startdate']).strftime("%e. %B",$event['enddate']);
            } else {
                $dss = strftime("%e. %B – ",$event['startdate']).strftime("%e. %B",$event['enddate']);
            }
		    $output .= "<li class='".$event['flag']."'>".
		        $event['cal_name_short'].': '.
		        '<a href="'.$event['uri'].'">'.$event['caption'].' '.$dss.'</a>'.
		        '</li>';
		}
		return $output;
	}
	
	function run() {

		$this->setRssUrl("/nyheter/rss");

		$output = "
			<table>
		";
        		
		if ($this->show_upcomingtropp) {
	        $output .= '
                <tr><th>Nærmer seg:</th><td>
                    <ul>
                        '.$this->get_upcoming_events().'
                    </ul>
                </td></tr>
            ';
        }
        
        if ($this->show_troppslogger) {
	        $output .= '
                <tr><th>Nyeste logger:</th><td>
                    <ul>
                        '.$this->get_newest_logs().'
                    </ul>
                </td></tr>
            ';
        }
        
        if ($this->show_weeklyarticle) {
			
			$this->articles_instance = new article_collection(); 
			call_user_func($this->prepare_classinstance, $this->articles_instance, $this->article_collection);
			$ukens_tips = $this->articles_instance->getLastArticle();
			
			$output .= '<tr><th>Siste speidertips:</th><td><ul>';
			if ($ukens_tips === false) {
				$output .= '
					<li><em>Ingen speidertips publisert enda</em></li>
				';
			} else {
				$output .= '
					<li class="idea"><a href="'.$ukens_tips['uri'].'">'.$ukens_tips['topic'].'</a></li>
				';
			}
			$output .= '</ul></td></tr>';
		}

        $output .= '</table>';
        $output = '<div id="aktuelt">'.$output.'</div>
        ';

		$this->noteboard_instance = new noteboard(); 
		call_user_func($this->prepare_classinstance, $this->noteboard_instance, $this->noteboard);
		$this->noteboard_instance->initialize();
		$output .= $this->noteboard_instance->printEntries();
		
		return $output;
	}

}


?>
