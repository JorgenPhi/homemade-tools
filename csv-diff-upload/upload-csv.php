<?php 

/* This file is specific for foolfuuka / asagi databases, but the concept can be applied to almost any other auto-incrimenting database */

define('DB_HOST', 'localhost');
define('DB_USER', 'backupbot');
define('DB_PASS', 'password');
define('DB_DB', 'backupbot');

$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_DB);

if($db->connect_errno > 0) {
    die('Unable to connect to database [' . $db->connect_error . ']');
}

function processBoard($db, $board) {
	$maxDocNum = $db->query("SELECT `doc_id` FROM `asagi`.`".mysqli_real_escape_string($db, $board)."` ORDER BY `doc_id` DESC LIMIT 1")or die($db->error);
	$maxDocNum = intval($maxDocNum->fetch_array()[0]);
	
	$lastDocNum = $db->query("SELECT `lastid` FROM  `backupbot`.`backupstats` WHERE  `board` = '".mysqli_real_escape_string($db, $board)."'") or die($db->error);
    $lastDocNum = $lastDocNum->fetch_array();
	if(isset($lastDocNum[0])) {
    	$lastDocNum = intval($lastDocNum[0]);
    } else {
    	$lastDocNum = 0;
    }
    $numToBackup = $maxDocNum - $lastDocNum;
    if($numToBackup == 0) {
		$done = true;
    } else {
    	$done = false;
    }

	while($done === false) {
		if(($lastDocNum + 10000000) > $maxDocNum){
			$currentDocNum = $maxDocNum;
			$done = true;
		} else {
			$currentDocNum = $lastDocNum + 10000000;
		}
		$filename = date('Ymd')."-$board-$currentDocNum.csv";
		echo "* Backing up /$board/ posts ".($lastDocNum+1)." - $currentDocNum to $filename\r\n";

		// Gen file from SQL
		$backupquery = 'SELECT `doc_id`, `media_id`, `num`, `subnum`, `thread_num`, `op`, `timestamp`, `timestamp_expired`, `preview_orig`, `preview_w`, `preview_h`, `media_filename`, `media_w`, `media_h`, `media_size`, `media_hash`, `media_orig`, `spoiler`, `deleted`, `capcode`, `email`, `name`, `trip`, `title`, `comment`, `sticky`, `locked`, `poster_hash`, `poster_country`, `exif` FROM `asagi`.`'.mysqli_real_escape_string($db, $board).'` where `doc_id` < '.mysqli_real_escape_string($db, $currentDocNum +1).' AND `doc_id` > '.mysqli_real_escape_string($db, $lastDocNum).' INTO OUTFILE "/var/lib/mysql-files/'.mysqli_real_escape_string($db, $filename).'" FIELDS TERMINATED BY "," ENCLOSED BY \'"\' LINES TERMINATED BY \'\n\';';
		$backupres = $db->query($backupquery) or die($db->error);

		// GZIP File
		echo "* Compressing $filename...\r\n";
		exec("gzip /var/lib/mysql-files/".escapeshellarg($filename));

		// Upload to IA
		$year = date('Y');
		echo "* Starting ia uploader..\r\n";
		exec("ia upload asagi-".escapeshellarg($year)."-db /var/lib/mysql-files/".escapeshellarg($filename).".gz");

		// Delete file
		unlink("/var/lib/mysql-files/".$filename.".gz");

		$lastDocNum = $currentDocNum;
		$insert_query = 'INSERT INTO `backupbot`.`backupstats` (`board`, `lastid`, `timestamp`) VALUES ("'.mysqli_real_escape_string($db, $board).'",'.mysqli_real_escape_string($db, $lastDocNum).','.mysqli_real_escape_string($db, time()).') ON DUPLICATE KEY UPDATE `lastid`='.mysqli_real_escape_string($db, $lastDocNum).', `timestamp`='.mysqli_real_escape_string($db, time()).';';
	    $insert_result = $db->query($insert_query) or die($db->error);
	}

}

echo "* Processing Asagi Archive...\r\n";
$boards = $db->query("SELECT `shortname` FROM `foolfuuka`.`ff_boards` WHERE `archive` = 1 AND `hidden` = 0;") or die($db->error);
while($board = $boards->fetch_array()) {
	processBoard($db, $board[0]);
}
unset($boards);