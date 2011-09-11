<?

class statistikk {

	function run() {
		
		print "<h2>Hva ligger på ".$this->server_name."?</h2>";

		print "<ul>";

		$res = $this->query("SELECT COUNT(id) as count FROM news_gruppe");
		$row = $res->fetch_assoc();
		$count = $row['count'];
		print "<li>$count nyheter</li>";

		$res = $this->query("SELECT COUNT(id) as count FROM forum_posts_intern");
		$row = $res->fetch_assoc();
		$count = $row['count'];
		$res = $this->query("SELECT COUNT(id) as count FROM forum_posts_gruppe");
		$row = $res->fetch_assoc();
		$count += $row['count'];
		print "<li>$count forum-meldinger</li>";

		$res = $this->query("SELECT COUNT(id) as count FROM wordbox");
		$row = $res->fetch_assoc();
		$count = $row['count'];
		print "<li>$count snikksnakk-meldinger</li>";

		$res = $this->query("SELECT COUNT(id) as count FROM imgarchive_files_tropp");
		$row = $res->fetch_assoc();
		$count = $row['count'];
		$res = $this->query("SELECT COUNT(id) as count FROM imgarchive_files_flokk");
		$row = $res->fetch_assoc();
		$count += $row['count'];
		print "<li>$count bilder</li>";

		$res = $this->query("SELECT COUNT(id) as count FROM cal_items_tropp");
		$row = $res->fetch_assoc();
		$count = $row['count'];
		$res = $this->query("SELECT COUNT(id) as count FROM cal_items_flokk");
		$row = $res->fetch_assoc();
		$count += $row['count'];
		print "<li>$count kalender-hendelser</li>";

		$res = $this->query("SELECT COUNT(id) as count FROM newsletters_gruppe");
		$row = $res->fetch_assoc();
		$count = $row['count'];
		print "<li>$count nyhetsbrev</li>";

		$res = $this->query("SELECT COUNT(id) as count FROM medlemmer");
		$row = $res->fetch_assoc();
		$count = $row['count'];
		print "<li>$count medlemmer</li>";


		$res = $this->query("SELECT COUNT(id) as count FROM imgarchive_comments_flokk");
		$row = $res->fetch_assoc();
		$count = $row['count'];
		$res = $this->query("SELECT COUNT(id) as count FROM imgarchive_comments_tropp");
		$row = $res->fetch_assoc();
		$count += $row['count'];
		$res = $this->query("SELECT COUNT(id) as count FROM comments_news_gruppe");
		$row = $res->fetch_assoc();
		$count += $row['count'];
		print "<li>$count kommentarer</li>";


		$res = $this->query("SELECT COUNT(id) as count FROM log_flokk");
		$row = $res->fetch_assoc();
		$count = $row['count'];
		$res = $this->query("SELECT COUNT(id) as count FROM log_tropp");
		$row = $res->fetch_assoc();
		$count += $row['count'];
		print "<li>$count logger</li>";

		$res = $this->query("SELECT COUNT(id) as count FROM articles_speidertips");
		$row = $res->fetch_assoc();
		$count = $row['count'];
		print "<li>$count speidertips</li>";


		print "</ul>";		
		
	
		print "<h2>Bidragsytere bildearkiv tropp:</h2>";
		$res = $this->query(
			"SELECT 
				uploadedby as ident,
				COUNT(id) as count
			FROM 
				imgarchive_files_tropp
			WHERE 
				visible=1 AND deletereason=''
			GROUP BY 
				uploadedby
			ORDER BY 
				count DESC
			LIMIT 5
			"
		);
		print "<ol>";
		while ($row = $res->fetch_assoc()) {
			$count = $row['count'];
			$m = call_user_func($this->make_memberlink, $row['ident']);
			print "<li>$m ($count bilder)</li>";
		}
		print "</ol>";
		
		
		
		print "<h2>Bidragsytere bildearkiv flokk:</h2>";
		$res = $this->query(
			"SELECT 
				uploadedby as ident,
				COUNT(id) as count
			FROM 
				imgarchive_files_flokk
			WHERE 
				visible=1 AND deletereason=''
			GROUP BY 
				uploadedby
			ORDER BY 
				count DESC
			LIMIT 5
			"
		);
		print "<ol>";
		while ($row = $res->fetch_assoc()) {
			$count = $row['count'];
			$m = call_user_func($this->make_memberlink, $row['ident']);
			print "<li>$m ($count bilder)</li>";
		}
		print "</ol>";
		
		
		
		print "<h2>Kommentarer bildearkiv tropp:</h2>";
		$res = $this->query(
			"SELECT 
				author as ident,
				COUNT(id) as count
			FROM 
				imgarchive_comments_tropp
			WHERE 
				author != 0
			GROUP BY 
				author
			ORDER BY 
				count DESC
			LIMIT 5
			"
		);
		print "<ol>";
		while ($row = $res->fetch_assoc()) {
			$count = $row['count'];
			$m = call_user_func($this->make_memberlink, $row['ident']);
			print "<li>$m ($count kommentarer)</li>";
		}
		print "</ol>";
		
		
		print "<h2>Kommentarer bildearkiv flokk:</h2>";
		$res = $this->query(
			"SELECT 
				author as ident,
				COUNT(id) as count
			FROM 
				imgarchive_comments_flokk
			WHERE 
				author != 0
			GROUP BY 
				author
			ORDER BY 
				count DESC
			LIMIT 5
			"
		);
		print "<ol>";
		while ($row = $res->fetch_assoc()) {
			$count = $row['count'];
			$m = call_user_func($this->make_memberlink, $row['ident']);
			print "<li>$m ($count kommentarer)</li>";
		}
		print "</ol>";
		
		
		print "<h2>Nyheter:</h2>";
		$res = $this->query(
			"SELECT 
				creator as ident,
				COUNT(id) as count
			FROM 
				news_gruppe
			GROUP BY 
				creator
			ORDER BY 
				count DESC
			LIMIT 5
			"
		);
		print "<ol>";
		while ($row = $res->fetch_assoc()) {
			$count = $row['count'];
			$m = call_user_func($this->make_memberlink, $row['ident']);
			print "<li>$m ($count nyheter)</li>";
		}
		print "</ol>";
		
		
		
		print "<h2>Snikksnakkmeldinger:</h2>";
		$res = $this->query(
			"SELECT 
				author as ident,
				COUNT(id) as count
			FROM 
				wordbox
			WHERE
				author != 0 AND deleted = 0
			GROUP BY 
				author
			ORDER BY 
				count DESC
			LIMIT 5
			"
		);
		print "<ol>";
		while ($row = $res->fetch_assoc()) {
			$count = $row['count'];
			$m = call_user_func($this->make_memberlink, $row['ident']);
			print "<li>$m ($count meldinger)</li>";
		}
		print "</ol>";
		
		
		print "<h2>Forum-meldinger:</h2>";
		$res = $this->query(
			"SELECT 
				author as ident,
				COUNT(id) as count
			FROM 
				forum_posts_intern
			GROUP BY 
				author
			ORDER BY 
				count DESC
			LIMIT 5
			"
		);
		print "<ol>";
		while ($row = $res->fetch_assoc()) {
			$count = $row['count'];
			$m = call_user_func($this->make_memberlink, $row['ident']);
			print "<li>$m ($count meldinger)</li>";
		}
		print "</ol>";
		
		
	}

}


?>