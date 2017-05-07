<?php

	header("Content-type: text/html");

	echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n";
	echo "<html><head>\n";
	echo "\t<title>List</title>\n";
	echo "\t<link rel=\"stylesheet\" type=\"text/css\" href=\"style.css\">\n";
	echo "\t<link rel=\"icon\" type=\"image/x-icon\" href=\"magnifier.ico\">\n";
	echo "</head><body>\n";
	echo "\t<center>\n";

	$session = trim(shell_exec("java -cp blanket.jar:json-simple-1.1.1.jar Blanket -times -initial-lap -shuffle"));
	$subdir = $session % 1000;
	$subsubdir = ($session - $subdir) / 1000 % 1000;
	$path = "sessions" . DIRECTORY_SEPARATOR .
			$subdir . DIRECTORY_SEPARATOR .
			$subsubdir . DIRECTORY_SEPARATOR .
			$session . DIRECTORY_SEPARATOR;
	$info = array_slice(file($path . "info.txt"), 1);

	$entity_prefix = "ENTITY: ";
	$entity_prefix_len = strlen($entity_prefix);
	$block_end = "BLOCKS END";
	$state = 0;
	$title = "";
	$url = "";
	$snippet = "";
	$img_url = "";
	$keywords = array();
	foreach ($info as $line) {
		$s = trim($line);
		if ($s === $block_end) {
			echo "\t\t<div class=\"box\">\n";
			if ($img_url !== "null") {
				echo "\t\t\t<div class=\"thumb-wrap\"><img class=\"thumb\" src=\"$img_url\" alt=\"$title\"></div>\n";
				echo "\t\t\t<div class=\"content\">\n";
			} else {
				echo "\t\t\t<div class=\"content-wide\">\n";
			}
			echo "\t\t\t\t<p class=\"text\"><a href=\"$url\" target=\"_blank\">$title</a></p>\n";
			echo "\t\t\t\t<p class=\"text\">$snippet</p>\n";
			foreach ($keywords as $keyword) {
				echo "\t\t\t\t<div class=\"subject\">$keyword</div>\n";
			}
			echo "\t\t\t</div>\n";
			echo "\t\t</div>\n";
			unset($keywords);
			$keywords = array();
			$state = 0;
		} elseif ($state === 0) {
			$title = $s;
			$state = 1;
		} elseif ($state === 1) {
			$url = $s;
			$state = 2;
		} elseif ($state === 2) {
			$snippet = $s;
			$state = 3;
		} elseif ($state === 3) {
			$img_url = $s;
			$state = 4;
		} elseif ($state === 4) {
			$state = 5;
		} elseif (substr($s, 0, $entity_prefix_len) !== $entity_prefix) {
			$keywords[] = $s;
		}
	}

	echo "\t</center>\n";
	echo "</body></html>\n";

?>

