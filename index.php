<?php

require_once(__DIR__ . '/includes/init.php');

$db = db_connect();

// Run scraping through pseudo cron
if (LDFF_SCRAPING_PSEUDO_CRON) {
	$last_run = intval(setting_read($db, 'pseudo_scraping_last_run', 0));
	$now = time();
	if ($now - $last_run > LDFF_SCRAPING_PSEUDO_CRON_DELAY) {
		setting_write($db, 'pseudo_scraping_last_run', $now);
		$report = scraping_run($db, LDFF_SCRAPING_TIMEOUT);
	}
}

//print_r(http_fetch_entry(55626)); // DEBUG

// Context

$context = array();
$context['competition'] = LDFF_COMPETITION_PAGE;
$context['ld_root'] = LDFF_SCRAPING_ROOT . '/' . LDFF_COMPETITION_PAGE . '?action=preview&';
$context['entries'] = array();
$results = mysqli_query($db, "SELECT * FROM entry ORDER BY last_updated DESC LIMIT 10");
while ($row = mysqli_fetch_array($results)) {
	$context['entries'][] = $row;
}
$context['entry_count'] = db_select_single_value($db, "SELECT COUNT(*) FROM entry");

mysqli_close($db);


// Templates rendering

function render($template_name) {
	global $mustache, $context;
	$template = $mustache->loadTemplate($template_name);
	echo $template->render($context);
}

// TODO Let ajax requests return a single, chosen template
render('header');
render('contents');
render('footer');

/*if (isset($report)) { // DEBUG
	echo "<pre>";
	print_r($report);
	echo "</pre>";
}*/

?>