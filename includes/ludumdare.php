<?php

function _http_fetch_page($queryParams) {
	$url = LDFF_SCRAPING_ROOT . LDFF_ACTIVE_EVENT_ID . '/?' . $queryParams;
	return file_get_contents($url);
}

/*
	Retrieves UIDs from all event entries.
*/
function http_fetch_uids() {
	$entry_list = array();

	$data = _http_fetch_page('action=misc_links');
	phpQuery::newDocumentHTML($data);

	foreach(pq('#compo2 td > a') as $entry_el) {
		$entry_list[] = str_replace('?action=preview&uid=', '', pq($entry_el)->attr('href'));
	}

	return $entry_list;
}

/*
	Retrieves the full info for an entry
	TODO Comments
*/
function http_fetch_entry($uid) {
	static $PLATFORM_KEYWORDS = array(
		'windows' => ['windows', 'win32', 'win64', 'java'],
		'linux' => ['linux', 'debian', 'ubuntu', 'java'],
		'osx' => ['mac', 'osx', 'os/x', 'os x', 'java'],
		'android' => ['android'],

		'web flash' => ['flash', 'swf'],
		'web html5' => ['html', 'webgl'],
		'web unity' => ['unity'],
		'web' => ['web']
	);

	// Fetch page and remove <script> tags for phpQuery (http://stackoverflow.com/a/36912417)
	$html = _http_fetch_page('action=preview&uid=' . $uid);
 	preg_match_all('/<script.*?>[\s\S]*?<\/script>/', $html, $tmp);
    $scripts_array = $tmp[0]; 
    foreach ($scripts_array as $script_id => $script_item){
        $html = str_replace($script_item, '', $html);
    }
	phpQuery::newDocumentHTML($html);

	// Figure out platforms
	$platforms = '';
	$platforms_text = strtolower(pq('.links li')->text());
	foreach ($PLATFORM_KEYWORDS as $platform_name => $keywords) {
		$found = false;
		foreach ($keywords as $keyword) {
			if (strpos($platforms_text, $keyword) !== false) {
				$found = true;
				break;
			}
		}

		if ($found) {
			if ($platforms != '') {
				$platforms .= ' ';
			}
			$platforms .= $platform_name;
			if (strpos($platform_name, 'web') !== false) {
				break; // Don't add multiple web platforms (e.g. "web unity" + "web")
			}
		}
	}
	if ($platforms == '') {
		$platforms = 'Unknown';
	}

	// Gather comments
	$comments = array();
	$order = 1;
	foreach (pq('.comment') as $comment) {
		$comments[] = array(
			'uid_author' => intval(str_replace('?action=preview&uid=', '', pq('a', $comment)->attr('href'))),
			'author' => pq('a', $comment)->text(),
			'order' => $order++,
			'comment' => pq('p', $comment)->html(),
			'date' => date_create_from_format('M d, Y @ g:ia', pq('small', $comment)->text())
		);
	}

	// Build entry array
	$entry = array(
		'uid' => $uid,
		'author' => pq('#compo2 a strong')->text(),
		'title' => pq('#compo2 h2')->eq(0)->text(),
		'type' => (pq('#compo2 > div > i')->text() == 'Compo Entry') ? 'compo' : 'jam',
		'description' => pq(pq('#compo2 h2')->eq(1))->prev()->html(),
		'platforms' => $platforms,
		'picture' => pq('.shot-nav img')->attr('src'),
		'comments' => $comments
	);
	
	return $entry;
}

?>