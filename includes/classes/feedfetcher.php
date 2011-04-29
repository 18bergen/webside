<?

require_once(BG_LIB_PATH.'simplepie/SimplePieAUtoLoader.php');

class FeedFetcher {

	function fetchFeeds($feeds) {

		$_cacheDir = '../cache/simplepie';

		$output = "";
		
		$feed_no = 0;
		foreach ($feeds as $stream_id => $stream_data) {
			$feed_no++;
		
			if (isset($_SESSION['rss_'.$stream_id.'_disabled']) && $_SESSION['rss_'.$stream_id.'_disabled'] == true) {
				$output .= "
					<h3 class='small'>Nyheter fra ".$stream_data['short_title'].":</h3>
					<em style='font-size:10px;'>Får ikke tak i ".$stream_data['short_title']."... :(</em>
				";			
			} else {
									
				$feed = new SimplePie();
				$feed->set_feed_url($stream_data['url']);
				$feed->set_cache_location($_cacheDir);
				$feed->set_cache_duration(7200); // 7200 seconds = 2 hours
				$feed->init();
				$feed->handle_content_type();

				$output .= "
					<h3 class='small'>Nyheter fra ".$stream_data['short_title'].":</h3>
					<div id='".$stream_id."_nyheter_content'>
						<ul class='snikksnakk'>
				";
				if ($feed->data) {
					$no = 0;
					$i = 0;
					foreach ($feed->get_items() as $item) {
						$i++;
						if ($i > 4) break;
						$no = !$no;

						$href = $item->get_permalink();
						$title = strip_tags($item->get_title());
						$pubDate = $item->get_date('j M Y');
						$desc = $pubDate.": ".strip_tags($item->get_content());
						$output .= "
							<li class='snikksnakk".($no+1)."' title=\"$desc\">
								<a href=\"$href\">$title</a>
							</li>
						";
					}
				} else {
					$_SESSION['rss_'.$stream_id.'_disabled'] = true;
					$output .= "
						<li><em>Midlertidig utilgjengelig</em></li>
					";
				}
				$output .= "
					</ul>
				</div><!-- ".$stream_id."_nyheter_content -->
				";
			}
			if ($feed_no < count($feeds)) $output .= "<br />&nbsp;";
		}
		return $output;
	}
}
?>
