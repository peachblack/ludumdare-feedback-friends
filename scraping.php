<html>
<head>
  <meta http-equiv="refresh" content="1">
</head>
<body>

<?php

// TODO Password-protect

require_once('includes/init.php');

set_time_limit(LDFF_SCRAPING_TIMEOUT * 1.5);

$db = db_connect();

echo '<pre>';
print_r(scraping_run($db));

mysqli_close($db);

?>

</body>
</html>