<?

class memberlist extends base {
	
	var $members;
	var $groups;
	
	var $table_pages = "cms_pages";
	var $table_pageoptions = "cms_pageoptions";
	var $table_pagelabels = "cms_pagelabels";
	var $table_memberlist = "members";
	var $table_memberlistlocal = "members_local";
	var $table_images = "images";
	var $table_rang = "rang";
	var $table_groups = "groups";
	var $table_memberships = "memberships";
	var $table_list_templates = "memberlist_templates";
	var $table_guardians = "member_guardians";
	var $table_groupcats = "group_categories";
	var $table_rights = "rights";
	var $table_membershiptypes = "membershiptypes";	
	var $table_verv = "verv";	
	var $table_vervhistorie = "vervhistorie";	
	var $table_forumposts = "forum_posts";	
	var $table_comments = "comments";
	var $table_news = "news";
	var $table_wordbox = "wordbox";	
	var $table_onlineusers = "onlineusers";	
	var $document_title = '';

	var $memberImagesDir = 'Medlemsfiler/';

	private $userDataCache = array();
	private $activeMembersCache = array();
	
	var	$kategoriOrder = array(
		"NN" => 9, 
		"LE" => 8, 
		"RO" => 7, 
		"SP" => 6, 
		"SM" => 5, 
		"FO" => 4, 
		"PE" => 3, 
		"AN" => 2, 
		"GR" => 1
	);
	
	var $countries = array(
		'AF'=>'Afghanistan',
		'AL'=>'Albania',
		'DZ'=>'Algeria',
		'AS'=>'American Samoa',
		'AD'=>'Andorra',
		'AO'=>'Angola',
		'AI'=>'Anguilla',
		'AQ'=>'Antarctica',
		'AG'=>'Antigua And Barbuda',
		'AR'=>'Argentina',
		'AM'=>'Armenia',
		'AW'=>'Aruba',
		'AU'=>'Australia',
		'AT'=>'Austria',
		'AZ'=>'Azerbaijan',
		'BS'=>'Bahamas',
		'BH'=>'Bahrain',
		'BD'=>'Bangladesh',
		'BB'=>'Barbados',
		'BY'=>'Belarus',
		'BE'=>'Belgium',
		'BZ'=>'Belize',
		'BJ'=>'Benin',
		'BM'=>'Bermuda',
		'BT'=>'Bhutan',
		'BO'=>'Bolivia',
		'BA'=>'Bosnia And Herzegovina',
		'BW'=>'Botswana',
		'BV'=>'Bouvet Island',
		'BR'=>'Brazil',
		'IO'=>'British Indian Ocean Territory',
		'BN'=>'Brunei',
		'BG'=>'Bulgaria',
		'BF'=>'Burkina Faso',
		'BI'=>'Burundi',
		'KH'=>'Cambodia',
		'CM'=>'Cameroon',
		'CA'=>'Canada',
		'CV'=>'Cape Verde',
		'KY'=>'Cayman Islands',
		'CF'=>'Central African Republic',
		'TD'=>'Chad',
		'CL'=>'Chile',
		'CN'=>'China',
		'CX'=>'Christmas Island',
		'CC'=>'Cocos (Keeling) Islands',
		'CO'=>'Columbia',
		'KM'=>'Comoros',
		'CG'=>'Congo',
		'CK'=>'Cook Islands',
		'CR'=>'Costa Rica',
		'CI'=>'Cote D\'Ivorie (Ivory Coast)',
		'HR'=>'Croatia (Hrvatska)',
		'CU'=>'Cuba',
		'CY'=>'Cyprus',
		'CZ'=>'Czech Republic',
		'CD'=>'Democratic Republic Of Congo (Zaire)',
		'DK'=>'Denmark',
		'DJ'=>'Djibouti',
		'DM'=>'Dominica',
		'DO'=>'Dominican Republic',
		'TP'=>'East Timor',
		'EC'=>'Ecuador',
		'EG'=>'Egypt',
		'SV'=>'El Salvador',
		'GQ'=>'Equatorial Guinea',
		'ER'=>'Eritrea',
		'EE'=>'Estonia',
		'ET'=>'Ethiopia',
		'FK'=>'Falkland Islands (Malvinas)',
		'FO'=>'Faroe Islands',
		'FJ'=>'Fiji',
		'FI'=>'Finland',
		'FR'=>'France',
		'FX'=>'France, Metropolitan',
		'GF'=>'French Guinea',
		'PF'=>'French Polynesia',
		'TF'=>'French Southern Territories',
		'GA'=>'Gabon',
		'GM'=>'Gambia',
		'GE'=>'Georgia',
		'DE'=>'Germany',
		'GH'=>'Ghana',
		'GI'=>'Gibraltar',
		'GR'=>'Greece',
		'GL'=>'Greenland',
		'GD'=>'Grenada',
		'GP'=>'Guadeloupe',
		'GU'=>'Guam',
		'GT'=>'Guatemala',
		'GN'=>'Guinea',
		'GW'=>'Guinea-Bissau',
		'GY'=>'Guyana',
		'HT'=>'Haiti',
		'HM'=>'Heard And McDonald Islands',
		'HN'=>'Honduras',
		'HK'=>'Hong Kong',
		'HU'=>'Hungary',
		'IS'=>'Iceland',
		'IN'=>'India',
		'ID'=>'Indonesia',
		'IR'=>'Iran',
		'IQ'=>'Iraq',
		'IE'=>'Ireland',
		'IL'=>'Israel',
		'IT'=>'Italy',
		'JM'=>'Jamaica',
		'JP'=>'Japan',
		'JO'=>'Jordan',
		'KZ'=>'Kazakhstan',
		'KE'=>'Kenya',
		'KI'=>'Kiribati',
		'KW'=>'Kuwait',
		'KG'=>'Kyrgyzstan',
		'LA'=>'Laos',
		'LV'=>'Latvia',
		'LB'=>'Lebanon',
		'LS'=>'Lesotho',
		'LR'=>'Liberia',
		'LY'=>'Libya',
		'LI'=>'Liechtenstein',
		'LT'=>'Lithuania',
		'LU'=>'Luxembourg',
		'MO'=>'Macau',
		'MK'=>'Macedonia',
		'MG'=>'Madagascar',
		'MW'=>'Malawi',
		'MY'=>'Malaysia',
		'MV'=>'Maldives',
		'ML'=>'Mali',
		'MT'=>'Malta',
		'MH'=>'Marshall Islands',
		'MQ'=>'Martinique',
		'MR'=>'Mauritania',
		'MU'=>'Mauritius',
		'YT'=>'Mayotte',
		'MX'=>'Mexico',
		'FM'=>'Micronesia',
		'MD'=>'Moldova',
		'MC'=>'Monaco',
		'MN'=>'Mongolia',
		'MS'=>'Montserrat',
		'MA'=>'Morocco',
		'MZ'=>'Mozambique',
		'MM'=>'Myanmar (Burma)',
		'NA'=>'Namibia',
		'NR'=>'Nauru',
		'NP'=>'Nepal',
		'NL'=>'Netherlands',
		'AN'=>'Netherlands Antilles',
		'NC'=>'New Caledonia',
		'NZ'=>'New Zealand',
		'NI'=>'Nicaragua',
		'NE'=>'Niger',
		'NG'=>'Nigeria',
		'NU'=>'Niue',
		'NF'=>'Norfolk Island',
		'KP'=>'North Korea',
		'MP'=>'Northern Mariana Islands',
		'NO'=>'Norway',
		'OM'=>'Oman',
		'PK'=>'Pakistan',
		'PW'=>'Palau',
		'PA'=>'Panama',
		'PG'=>'Papua New Guinea',
		'PY'=>'Paraguay',
		'PE'=>'Peru',
		'PH'=>'Philippines',
		'PN'=>'Pitcairn',
		'PL'=>'Poland',
		'PT'=>'Portugal',
		'PR'=>'Puerto Rico',
		'QA'=>'Qatar',
		'RE'=>'Reunion',
		'RO'=>'Romania',
		'RU'=>'Russia',
		'RW'=>'Rwanda',
		'SH'=>'Saint Helena',
		'KN'=>'Saint Kitts And Nevis',
		'LC'=>'Saint Lucia',
		'PM'=>'Saint Pierre And Miquelon',
		'VC'=>'Saint Vincent And The Grenadines',
		'SM'=>'San Marino',
		'ST'=>'Sao Tome And Principe',
		'SA'=>'Saudi Arabia',
		'SN'=>'Senegal',
		'SC'=>'Seychelles',
		'SL'=>'Sierra Leone',
		'SG'=>'Singapore',
		'SK'=>'Slovak Republic',
		'SI'=>'Slovenia',
		'SB'=>'Solomon Islands',
		'SO'=>'Somalia',
		'ZA'=>'South Africa',
		'GS'=>'South Georgia And South Sandwich Islands',
		'KR'=>'South Korea',
		'ES'=>'Spain',
		'LK'=>'Sri Lanka',
		'SD'=>'Sudan',
		'SR'=>'Suriname',
		'SJ'=>'Svalbard And Jan Mayen',
		'SZ'=>'Swaziland',
		'SE'=>'Sweden',
		'CH'=>'Switzerland',
		'SY'=>'Syria',
		'TW'=>'Taiwan',
		'TJ'=>'Tajikistan',
		'TZ'=>'Tanzania',
		'TH'=>'Thailand',
		'TG'=>'Togo',
		'TK'=>'Tokelau',
		'TO'=>'Tonga',
		'TT'=>'Trinidad And Tobago',
		'TN'=>'Tunisia',
		'TR'=>'Turkey',
		'TM'=>'Turkmenistan',
		'TC'=>'Turks And Caicos Islands',
		'TV'=>'Tuvalu',
		'UG'=>'Uganda',
		'UA'=>'Ukraine',
		'AE'=>'United Arab Emirates',
		'UK'=>'United Kingdom',
		'US'=>'United States',
		'UM'=>'United States Minor Outlying Islands',
		'UY'=>'Uruguay',
		'UZ'=>'Uzbekistan',
		'VU'=>'Vanuatu',
		'VA'=>'Vatican City (Holy See)',
		'VE'=>'Venezuela',
		'VN'=>'Vietnam',
		'VG'=>'Virgin Islands (British)',
		'VI'=>'Virgin Islands (US)',
		'WF'=>'Wallis And Futuna Islands',
		'EH'=>'Western Sahara',
		'WS'=>'Western Samoa',
		'YE'=>'Yemen',
		'YU'=>'Yugoslavia',
		'ZM'=>'Zambia',
		'ZW'=>'Zimbabwe'
	);

	/* Constructor. Reads the member database into an array. */
	function memberlist(){
		$this->table_pages = DBPREFIX.$this->table_pages;
		$this->table_pageoptions = DBPREFIX.$this->table_pageoptions;
		$this->table_pagelabels = DBPREFIX.$this->table_pagelabels;
		$this->table_memberlist = DBPREFIX.$this->table_memberlist;
		$this->table_memberlistlocal = DBPREFIX.$this->table_memberlistlocal;
		$this->table_images = DBPREFIX.$this->table_images;
		$this->table_rang = DBPREFIX.$this->table_rang;
		$this->table_groups = DBPREFIX.$this->table_groups;
		$this->table_memberships = DBPREFIX.$this->table_memberships;
		$this->table_list_templates = DBPREFIX.$this->table_list_templates;
		$this->table_guardians = DBPREFIX.$this->table_guardians;
		$this->table_groupcats = DBPREFIX.$this->table_groupcats;
		$this->table_rights = DBPREFIX.$this->table_rights;
		$this->table_membershiptypes = DBPREFIX.$this->table_membershiptypes;
		$this->table_verv = DBPREFIX.$this->table_verv;
		$this->table_vervhistorie = DBPREFIX.$this->table_vervhistorie;
		$this->table_forumposts = DBPREFIX.$this->table_forumposts;
		$this->table_comments = DBPREFIX.$this->table_comments;
		$this->table_news = DBPREFIX.$this->table_news;
		$this->table_wordbox = DBPREFIX.$this->table_wordbox;		
		$this->table_onlineusers = DBPREFIX.$this->table_onlineusers;
		
		$this->userDataCache = array();
	}
	
	public function getGroupById($id) {
		if (isset($this->groups[$id])) {
			return $this->groups[$id];
		} else {
			$this->addToErrorLog("Group $id requested, but does not exist!");
			return false;
		}
	}
	
	public function getGroupBySlug($slug) {
		foreach ($this->groups as $g) {
			if ($g->slug == $slug) {
				return $g;
			}
		}
		return false;
	}
	
	public function getAllGroups() {
		return $this->groups;
	}

	public function getAllMembers() {
		return $this->members;
	}
	
	public function getProfileImage($id, $size = 'small'){
		$basename = $this->members[$id]->profilbilde;
		if (empty($basename)) {
			return $this->image_dir."unknown.jpg";		
		}
		$dir = $this->memberImagesDir.$id.'/';
		switch ($size) {
			case 'small': return '/'.$this->userFilesDir.$dir.'_thumbs140/'.$basename;
			case 'medium': return '/'.$this->userFilesDir.$dir.'_thumbs490/'.$basename;
			default: return '/'.$this->userFilesDir.$dir.$basename;
		}
	}
	
	
	function getForumImage($id, $size = 'small'){
		$basename = $this->members[$id]->forumbilde;
		if (empty($basename)) {
			return $this->image_dir."unknown.jpg";		
		}
		$dir = $this->memberImagesDir.$id.'/';
		switch ($size) {
			case 'small': return '/'.$this->userFilesDir.$dir.'_thumbs140/'.$basename;
			case 'medium': return '/'.$this->userFilesDir.$dir.'_thumbs490/'.$basename;
			default: return '/'.$this->userFilesDir.$dir.$basename;
		}
	}
	
	/*
		==========================================================================================
		The cache system:
		
		<userDataCache> is a cache containing all user data. This cache is only filled with the
		users as requested
		
		<activeMembersCache> is a cache containing just the basic data on all the active members.
		
	*/
	
	private function generateSimpleCache() {
		$tm = $this->table_memberlist;
		$tmm = $this->table_memberships;
		$tr = $this->table_rang;
		$res = $this->query("SELECT $tm.ident,$tm.firstname,$tm.middlename,$tm.lastname, $tm.bday, $tm.slug
			FROM $tm,$tmm,$tr WHERE $tm.ident=$tmm.bruker AND $tmm.enddate=0
			AND $tm.rang = $tr.id
			GROUP BY $tm.ident"
		);
		$this->activeMembersCache = array();
		while ($row = $res->fetch_assoc()) {
			$mid = (empty($row['middlename'])) ? "" : mb_substr($row['middlename'],0,1,'UTF-8').". ";
			$user_id = intval($row['ident']);
			
			$bday = explode("-",$row['bday']);
			$cur_m = date('n',time()); $cur_d = date('j',time()); $cur_y = date('Y',time());
			if ($bday[1] > $cur_m) $bday = $cur_y."-".$bday[1].'-'.$bday[2];
			elseif ($bday[1] < $cur_m) $bday = ($cur_y+1)."-".$bday[1].'-'.$bday[2];
			else {
				if ($bday[2] < $cur_d) $bday = ($cur_y+1)."-".$bday[1].'-'.$bday[2];
				else $bday = $cur_y."-".$bday[1].'-'.$bday[2];			
			}
			
			$this->activeMembersCache[$user_id]=array(
				'UserId' 	 => $user_id,
				'FirstName'  => $row['firstname'],
				'FullName' 	 => $row['firstname']." $mid".$row['lastname'],
				'Birthday'	 => $bday,
				'ProfileUrl' => (empty($row['slug']) ? '/medlemsliste/medlemmer/'.$user_id : '/medlemsliste/'.$row['slug'])
			);
		}
	}
	
	function sortByFullName($a,$b) {
		return strcmp($a['FullName'],$b['FullName']);
	}
	
	function sortByBirthday($a,$b) {
		return strcmp($a['Birthday'],$b['Birthday']);
	}
	
	public function getActiveMembersList($options = array()) {
		if (isset($options['SortBy'])) {
			switch ($options['SortBy']) {
				case 'FullName':
					uasort($this->activeMembersCache, array('memberlist','sortByFullName'));
					break;
				case 'Birthday':
					uasort($this->activeMembersCache, array('memberlist','sortByBirthday'));
					break;
			}
		}
		if (isset($options['Limit'])) {
			return array_slice($this->activeMembersCache,0,$options['Limit']);	
		}
		return $this->activeMembersCache;
	}
	
	private function addToUserDataCache($users) {
		if (!is_array($users) || count($users) == 0) {
			$this->fatalError("Argument to addToUserDataCache should be an array of user id's.");
		}
		foreach ($users as $user_id) {
			if (!is_numeric($user_id)) {
				$this->fatalError("Argument to addToUserDataCache should be an array of <em>numeric</em> values.");
			}
		}
		$res = $this->query("SELECT 
				$this->table_memberlist.*, 
				$this->table_rang.tittel, 
				$this->table_rang.classname 
			FROM 
				$this->table_memberlist, 
				$this->table_rang 
			WHERE 
				$this->table_memberlist.rang = $this->table_rang.id
			AND ident IN (".implode(',',$users).")"
		);
		while ($row = $res->fetch_assoc()) {
			// Deprecated fields: homepage, msn, profilecreated, mail_newthreads, mail_onreply, lastonforum, myalbum
			$tmp = array(
				// Profile data:
				'FirstName'		=> $row['firstname'],
				'MiddleName'	=> $row['middlename'],
				'LastName'		=> $row['lastname'],
				'NickName'		=> $row['nickname'],
				'Street'		=> $row['street'],
				'StreetNo'		=> $row['streetno'],
				'PostCode'		=> $row['postno'],
				'City'			=> $row['city'],
				'State'			=> $row['state'],
				'Country'		=> $row['country'],
				'Email'			=> $row['email'],
				'HomePhone'		=> $row['homephone'],
				'CellPhone'		=> $row['cellular'],
				'Birthday'		=> $row['bday'],
				'ProfilePicture'=> $row['profilbilde'],
				'ForumPicture'	=> $row['forumbilde'],
				'Notes'			=> $row['notes'],
				'Slug'			=> $row['slug'],
				
				// Account data:
				'UserId'		=> intval($row['ident']),
				'AccountClosed' => $row['kontosperret'],
				'Rights'		=> $row['rights'],
				'Rang'			=> $row['rang'],
				'Status'		=> $row['memberstatus'],
				
				// Site settings:
				'Voted'			=> $row['voted']	
			);
			
			// Profile picture
			if (empty($tmp['ProfilePicture'])) {
				$tmp['ProfilePicture'] = array(
					'UploadedPicture' => false,
					'FileName'        => '',
					'SmallThumb'      => $this->image_dir."unknown.jpg",
					'MediumThumb'     => $this->image_dir."unknown.jpg",
					'Original'        => $this->image_dir."unknown.jpg"
				);
			} else {
				$dir = $this->memberImagesDir.$tmp['UserId'].'/';
				$basename = $tmp['ProfilePicture'];
				$tmp['ProfilePicture'] = array(
					'UploadedPicture' => true,
					'FileName'        => $basename,
					'SmallThumb'      =>  '/'.$this->userFilesDir.$dir.'_thumbs140/'.$basename,
					'MediumThumb'     =>  '/'.$this->userFilesDir.$dir.'_thumbs490/'.$basename,
					'Original'        =>  '/'.$this->userFilesDir.$dir.$basename
				);
			}
			
			// Forum picture
			if (empty($tmp['ForumPicture'])) {
				$tmp['ForumPicture'] = array(
					'UploadedPicture' => false,
					'FileName'        => '',
					'SmallThumb'      => $this->image_dir."unknown.jpg",
					'MediumThumb'     => $this->image_dir."unknown.jpg",
					'Original'        => $this->image_dir."unknown.jpg"
				);
			} else {
				$dir = $this->memberImagesDir.$tmp['UserId'].'/';
				$basename = $tmp['ProfilePicture'];
				$tmp['ForumPicture'] = array(
					'UploadedPicture' => true,
					'FileName'        => $basename,
					'SmallThumb'      =>  '/'.$this->userFilesDir.$dir.'_thumbs140/'.$basename,
					'MediumThumb'     =>  '/'.$this->userFilesDir.$dir.'_thumbs490/'.$basename,
					'Original'        =>  '/'.$this->userFilesDir.$dir.$basename
				);
			}
			
			// Hide names to Google:
			$useragent = "unknown";
			if (isset($_SERVER['HTTP_USER_AGENT'])) $useragent = $_SERVER['HTTP_USER_AGENT'];
			if (isset($_SERVER['USER_AGENT'])) $useragent = $_SERVER['USER_AGENT'];
			if ((strpos("unknown",$useragent) !== false) || (strpos("Googlebot",$useragent) !== false) || (isset($_GET['simulategoogle']))){
				$tmp['FirstName'] = $tmp['MiddleName'] = $tmp['LastName'] = "[skjult]";
			}
			
			// Cache full name:
			$mid = (empty($tmp['MiddleName'])) ? "" : mb_substr($tmp['MiddleName'],0,1,'UTF-8').". ";
			$tmp['FullName'] = $tmp['FirstName']." $mid".$tmp['LastName'];

			$tmp['ProfileUrl'] = (empty($tmp['Slug'])) ? '/medlemsliste/medlemmer/'.$tmp['UserId']
				: '/medlemsliste/'.$tmp['Slug'];

			$tmp['Memberships'] = array();
			$tmp['Guardians'] = array();
			$tmp['Children'] = array();

			//$bday_unix = strtotime($v);
			//if ($bday_unix < strtotime('1900-01-01')) $bday_unix = 0;
			//$this->members[$id]->$n = $bday_unix;
			/* The DateTime class looks promising, but the functionality is limited as 
			   of PHP 5.2.9. getTimestamp() introduced in 5.3.0, but still no function
			   like strftime with locale support...
			*/

			$this->userDataCache[intval($row['ident'])] = $tmp;

		}		
	}

	public function getUserData($users,$fields) {
		$usersNotCached = array();
		$cachedUsers = array_keys($this->userDataCache);
		if (is_array($users)) {
			foreach ($users as $user_id) {
				if (!in_array($user_id,$cachedUsers)) $usersNotCached[]=$user_id;
			}
		} else {
			if (!in_array($users,$cachedUsers)) $usersNotCached[]=$users;		
		}
		if (count($usersNotCached) != 0)
			$this->addToUserDataCache($usersNotCached);
		
		
		if (is_array($users)) {
			$toReturn = array();
			foreach ($users as $user_id) {
				$toReturn[$user_id] = array();	
				foreach ($fields as $field_name) {
					if (!isset($this->userDataCache[$user_id][$field_name])) {
						print $this->notSoFatalError("[memberlist] Unknown field &lt;$field_name&gt; for user &lt;$user_id&gt;");
					}
					$toReturn[$user_id][$field_name] = $this->userDataCache[$user_id][$field_name];
				}
			}
			return $toReturn;
		} else {
			$toReturn = array();	
			foreach ($fields as $field_name) {
				$toReturn[$field_name] = $this->userDataCache[$field_name];
			}
			return $toReturn;
		}
	}
	
	public function getMemberById($id) {
		$id = intval($id);
		if (isset($this->members[$id])) {
			return $this->members[$id];
		} else {
			$this->addToErrorLog("Member $id requested, but does not exist!");
			return false;
		}
	}
	
	public function getUserCategory($id) {
		$u = $this->members[$id];
		$memberof = array();
		$memberShortCategory = "-"; $mscid = 0;
		foreach ($u->memberof as $g){
			$grp = $this->groups[$g];
			$memberof[] = $grp->caption;
			if ($this->kategoriOrder[$grp->kategori] > $mscid) {
				$mscid = $this->kategoriOrder[$grp->kategori];;
				$memberShortCategory = $grp->kategori;
			}
		}	
		return $memberShortCategory;
	}

	public function getUserMainMembership($id) {
		$u = $this->members[$id];
		$memberof = array();
		$memberMembership = 0; $mscid = 0;
		foreach ($u->memberof as $g){
			$grp = $this->groups[$g];
			$memberof[] = $grp->caption;
			if ($this->kategoriOrder[$grp->kategori] > $mscid) {
				$mscid = $this->kategoriOrder[$grp->kategori];;
				$memberMembership = $grp->id;
			}
		}	
		return $memberMembership;
	}
	
	public function getCategoryByAbbr($abbr) {
		switch ($abbr) {
			case 'FO': return 'Foreldre';
			case 'SP': return 'Speidere';
			case 'SM': return 'Småspeidere';
			case 'LE': return 'Ledere';
			case 'PE': return 'Pensjonerte';
			case 'RO': return 'Rovere';
			case 'AN': return 'Annet';
			default: return 'Ukjent';
		}
	}
	
	public function getCategoryRole($abbr) {
		switch ($abbr) {
			case 'FO': return 'foresatt';
			case 'SP': return 'speider';
			case 'SM': return 'småspeider';
			case 'LE': return 'leder';
			case 'PE': return 'pensjonert speider';
			case 'RO': return 'rover';
			case 'AN': return 'annet';
			default: return 'ukjent';
		}
	}

	/* Parameter: DateTime object */
	function validDate($d) {
		return ($d != 0);
	}
		
	function initialize() {
		@parent::initialize();	
		
		$this->generateSimpleCache();
	
		$this->members = array();
		$res = $this->query("SELECT 
				$this->table_memberlist.*, 
				$this->table_rang.tittel, 
				$this->table_rang.classname 
			FROM 
				$this->table_memberlist, 
				$this->table_rang 
			WHERE 
				$this->table_memberlist.rang = $this->table_rang.id
			ORDER BY BINARY
				$this->table_memberlist.firstname,
				$this->table_memberlist.lastname"
		);
		$useragent = "unknown";
		if (isset($_SERVER['HTTP_USER_AGENT'])) $useragent = $_SERVER['HTTP_USER_AGENT'];
		if (isset($_SERVER['USER_AGENT'])) $useragent = $_SERVER['USER_AGENT'];
		while ($row = $res->fetch_assoc()){
			$id = $row['ident'];
			foreach ($row as $n => $v){
				switch ($n) {
					case 'firstname':
					case 'middlename':
					case 'nickname':
					case 'lastname':
						if ((strpos("unknown",$useragent) !== false) || (strpos("Googlebot",$useragent) !== false) || (isset($_GET['simulategoogle']))){
							$this->members[$id]->$n = "[skjult]";
						} else {
							$this->members[$id]->$n = $v;					
						}
						break;
					case 'bday':
						$bday_unix = strtotime($v);
						if ($bday_unix < strtotime('1900-01-01')) $bday_unix = 0;
						$this->members[$id]->$n = $bday_unix;
						/* The DateTime class looks promising, but the functionality is limited as 
						   of PHP 5.2.9. getTimestamp() introduced in 5.3.0, but still no function
						   like strftime with locale support...
						*/
						break;
					default:
						$this->members[$id]->$n = $v;
				}
			}

			if (empty($this->members[$id]->slug)) $this->members[$id]->url = '/medlemsliste/medlemmer/'.$this->members[$id]->ident;
			else $this->members[$id]->url = '/medlemsliste/'.$this->members[$id]->slug;

			$this->members[$id]->memberof = array();
			$this->members[$id]->guardians = array();
			$this->members[$id]->guarded_by = array();
			$mid = "";
			if (!empty($this->members[$id]->middlename)) {
				$mid = mb_substr($this->members[$id]->middlename,0,1,'UTF-8').". ";
			}
			$this->members[$id]->fullname = $this->members[$id]->firstname." ".$mid.$this->members[$id]->lastname; // gjør livet enklere...
		}
		$res->close();
		
		$this->groups = array();
		$res = $this->query("SELECT * FROM $this->table_groups ORDER BY position");
		while ($row = $res->fetch_assoc()){
			$id = $row['id'];
			foreach ($row as $n => $v){
				$this->groups[$id]->$n = stripslashes($v);
			}
			$this->groups[$id]->children = array();
			$this->groups[$id]->members = array();
			$this->groups[$id]->realmembers = array();
		}
		$res->close();

		$res = $this->query(
			"SELECT 
				$this->table_memberlist.ident,
				$this->table_memberships.gruppe
			FROM 
				$this->table_memberlist, 
				$this->table_memberships, 
				$this->table_rang 
			WHERE 
				$this->table_memberlist.ident = $this->table_memberships.bruker 
				AND $this->table_memberlist.rang = $this->table_rang.id 
				AND $this->table_memberships.enddate = '0000-00-00' 
			ORDER BY 
				$this->table_rang.position,
				$this->table_memberlist.bday"
		);
		while ($row = $res->fetch_assoc()){
			$this->groups[$row['gruppe']]->members[] = $row['ident'];
			$this->groups[$row['gruppe']]->realmembers[] = $row['ident'];
			$parentGroup = $this->groups[$row['gruppe']]->parent;
			while ($parentGroup != 0){
				$this->groups[$parentGroup]->members[] = $row['ident'];
				$parentGroup = $this->groups[$parentGroup]->parent;
			}
			$this->members[$row['ident']]->memberof[] = $row['gruppe'];
		}
		$res->close();


		
		foreach ($this->groups as $grp){
			if ($grp->parent != 0) {
				$this->groups[$grp->parent]->children[] = $grp->id;
			}
		}
		$this->cacheGuardians();
		//$this->cacheCategories();
	}
	
	/*
	function cacheCategories() {
		foreach ($this->members as $u) {
			foreach ($u->memberof as $g){
				$grp = $this->groups[$g];
				$memberof[] = $grp->caption;
				if ($this->kategoriOrder[$grp->kategori] > $mscid) {
					$mscid = $this->kategoriOrder[$grp->kategori];;
					$u->kategori = $grp->kategori;
				}
			}
		}
	}
	*/
	
	/* Reload memberlist array after db changes */
	function reloadMemberlist(){
		$this->initialize();
	}
	
	/* Sjekker om indeksen (ident-verdien) $u eksisterer i bruker-arrayen */
	function isUser($u){
		if (!is_numeric($u)) return false;
		if (array_key_exists($u, $this->members)){
			return true;
		} else {
			return false;
		}
	}
	
	/* Sjekker om indeksen (ident-verdien) $u eksisterer i gruppe-arrayen */
	function isGroup($u){
		if (array_key_exists($u, $this->groups)){
			return true;
		} else {
			return false;
		}
	}

	function userByTask($task){
		global $db;		
		$vervObj = new vervredigering();
		$verv = $vervObj->getVervBySlug($task);
		if ($verv == false){ ErrorMessageAndExit("Vervoppgaven $task er ikke forbundet med et verv!"); }
		$rs = $db->query("SELECT person FROM $this->table_vervhistorie WHERE verv=$verv AND enddate IS NULL");
		if ($rs->num_rows == 0) ErrorMessageAndExit("Vervet $task er ikke bemannet!");
		$rw = $rs->fetch_array();
		$rs->close();
		return $this->members[$rw[0]];
	}

	function isGroupLeader($ident){
		global $db;

		// Sjekker om bruker er peff/ass i en gruppe, og returner isåfall gruppe-id.
		global $groups;
		$leder_i = 0;

		$vervObj = new vervredigering();
		$peffverv = $vervObj->getVervBySlug('peff');
		$assverv = $vervObj->getVervBySlug('ass');
		if (!$peffverv || !$assverv) return 0;
		$result = $db->query("SELECT * FROM $this->table_vervhistorie ".
			"WHERE verv=$peffverv ".
				"AND person='$ident' ".
				"AND enddate IS NULL"
		);
		if ($result->num_rows == 1){
			$row = $result->fetch_assoc();
			$leder_i = array("group" => $row['gruppe'], "tittel" => "peff");
		}
		$result = $db->query("SELECT * FROM $this->table_vervhistorie ".
			"WHERE verv=$assverv ".
				"AND person='$ident' ".
				"AND enddate IS NULL"
		);
		if ($result->num_rows == 1){
			$row = $result->fetch_assoc();
			$leder_i = array("group" => $row['gruppe'], "tittel" => "ass");
		}
		$result->close();
		return $leder_i;
	}
	
	function groupsToStringList($ident){
		global $db;
		$res = $db->query("SELECT $this->table_groups.caption ".
			"FROM $this->table_groups,$this->table_memberships ".
			"WHERE $this->table_groups.id=$this->table_memberships.gruppe ".
				"AND $this->table_memberships.bruker=".$ident." ".
				"AND $this->table_memberships.enddate='0000-00-00'"
		);
		$igrps = array();
		while ($row = $res->fetch_assoc()){
			array_push($igrps,$row['caption']);
		}
		return $igrps;
	}
	
	function groupBySlug($s){
		foreach ($this->groups as $g){
			if ($g->slug == $s) return $g;
		}
		return false;
	}
	
	function generateMemberSelectBox($navn, $default = -1, $hiddenMembers = array()) {
		$selectBox = "<select name='$navn' id='$navn'>\n";
		$selectBox .= "                    <option value='0'>Velg:</option>\n";
		foreach ($this->members as $m) {
			if (count($m->memberof) > 0) {
				if (!in_array($m->ident,$hiddenMembers)) {
					$def = ($default == $m->ident) ? " selected='selected'" : "";
					$selectBox .= "                    <option value='".$m->ident."'$def>".$m->fullname."</option>\n";
				}
			}
		}
		$selectBox .= "                </select>";
		return $selectBox;
	}
	
	function generateGroupSelectBox($navn, $visKunPatruljer = false, $skjulteGrupper = array(), $default = -1, $allowNone = false) {
		$selectBox = "<select name='$navn' id='$navn'>\n";
		if ($allowNone) {
			$selectBox .= "
					<option value='0' style='font-style:italic; color:red;'>Ingen</option>
			";
		}
		foreach ($this->groups as $g) {
			if ($g->parent == '0') $selectBox .= $this->addToGroupBox(array($g->id), "", $visKunPatruljer, $skjulteGrupper, $default);
		}
		$selectBox .= "                </select>";
		return $selectBox;
	}
	
	function addToGroupBox($grps, $spaces, $visKunPatruljer = false, $skjulteGrupper = array(), $default = -1) {
		$toReturn = "";
		foreach ($grps as $gid) {
			$g = $this->groups[$gid];
			if (!$visKunPatruljer || $visKunPatruljer && $g->kategori != "SP" && $g->kategori != "RO") {
				if (!in_array($g->id,$skjulteGrupper)) {
					$def = ($default == $g->id) ? " selected='selected'" : "";
					$toReturn .= "                    <option value='".$g->id."'$def>$spaces".$g->caption."</option>\n";
				}
			}
			if (count($g->children) > 0) $toReturn .= $this->addToGroupBox($g->children, "&nbsp;&nbsp;&nbsp;$spaces", $visKunPatruljer, $skjulteGrupper, $default);
		}
		return $toReturn;
	}
		
	
	
	function cacheGuardians() {
		$res = $this->query("SELECT medlem, foresatt FROM $this->table_guardians");
		while ($row = $res->fetch_assoc()){
			$m = intval($row['medlem']);
			$g = intval($row['foresatt']);
			if (!$this->isUser($m)) {
				$this->addToErrorLog("Bruker #$g er registrert som foresatt til #$m, men brukeren #$m eksisterer ikke lenger!");
			} else if (!$this->isUser($g)) {
				$this->addToErrorLog("Bruker #$g er registrert som foresatt til #$m, men brukeren #$g eksisterer ikke lenger!");
			} else {
				$this->members[$m]->guardians[] = $g;
				$this->members[$g]->guarded_by[] = $m;
			}
		}
	}
	
	function makeMemberLink($id, $customText = "", $customQuery = "") {
		if (!isset($this->members[$id])) {
			return $this->notSoFatalError("Kunne ikke hente medlemsdata for medlem med id $id. Medlemmet eksisterer ikke.");
		}
		$m = $this->members[$id];
		if (empty($m->slug)) $url = "/medlemsliste/medlemmer/$m->ident";
		else $url = "/medlemsliste/$m->slug";
		if ($customQuery != "") $url = "$url?$customQuery";
		$name = $m->firstname;
		if ($customText == "") return '<a href="'.$url.'">'.$name.'</a>';
		else return '<a href="'.$url.'">'.$customText.'</a>';
	}
	
	function getMemberUrl($id) {
		if (!isset($this->members[$id])) {
			return $this->notSoFatalError("Kunne ikke hente medlemsdata for medlem med id $id. Medlemmet eksisterer ikke.");
		}
		$m = $this->members[$id];
		if (empty($m->slug)) $url = "/medlemsliste/medlemmer/$m->ident";
		else $url = "/medlemsliste/$m->slug";
		return $url;
	}	
	
	function getGroupUrl($id) {
		if (!isset($this->groups[$id])) {
			return $this->notSoFatalError("Kunne ikke finne noen gruppe med id $id. Gruppen eksisterer ikke.");
		}
		$g = $this->groups[$id];
		$url = "/medlemsliste/$g->slug";
		return $url;
	}	
	
	
}


?>