<?php

require_once('includes/init.php');

function read_version($db) {
	$current_version = setting_read($db, 'current_version', 0);
	echo 'Current version is '.$current_version.'<br />';
	return $current_version;
}

function write_version($db, $version) {
	setting_write($db, 'current_version', $version);
	echo 'Upgraded version to '.$version.'!<br />';
	return $version;
}

$db = db_connect();
$current_version = read_version($db);

if ($current_version < LDFF_VERSION) {

	$target_version = 1;
	if ($current_version < $target_version) {
		mysqli_query($db, "CREATE TABLE `entry` (
			`uid` INT NOT NULL ,
			`event_id` VARCHAR(64) NOT NULL ,
			`author` VARCHAR(255) NOT NULL ,
			`title` VARCHAR(255) NOT NULL ,
			`type` VARCHAR(5) NOT NULL,
			`description` VARCHAR(8192),
			`platforms` VARCHAR(255) NOT NULL ,
			`comments_given` INT NOT NULL DEFAULT '0',
			`comments_received` INT NOT NULL DEFAULT '0', 
			`coolness` INT NOT NULL DEFAULT '0', 
			`last_updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, 
			PRIMARY KEY (`uid`, `event_id`),
			INDEX `comments_given_index` (`comments_given`),
			INDEX `comments_received_index` (`comments_received`), 
			INDEX `coolness` (`coolness`), 
			FULLTEXT INDEX `index_platforms` (`platforms`) , 
			FULLTEXT INDEX `index_full` (`uid`, `author`, `title`, `platforms`, `type`)
			) ENGINE = MyISAM") or die("Failed to create entry table");  // MyISAM required for fulltext indexes on MySQL < 5.6
		mysqli_query($db, "CREATE TABLE `comment` (
			`uid_entry` INT NOT NULL ,
			`event_id` VARCHAR(64) NOT NULL ,
			`order` INT NOT NULL , 
			`uid_author` INT NOT NULL ,
			`comment` VARCHAR(8192) NOT NULL ,
			`date` DATETIME NOT NULL ,
			`score` INT NOT NULL , 
			PRIMARY KEY(`uid_entry`, `event_id`, `order`),
			INDEX `uid_author_index` (`uid_author`)
			) ENGINE = InnoDB") or die("Failed to create comment table");
		mysqli_query($db, "CREATE TABLE `setting` (
			`id` VARCHAR(255) NOT NULL , 
			`value` MEDIUMTEXT NOT NULL , 
			PRIMARY KEY (`id`)
			) ENGINE = InnoDB") or die("Failed to create setting table");
		
		$current_version = write_version($db, $target_version);
	}
}
else {
	echo 'Nothing to upgrade.';
}

mysqli_close($db);

?>