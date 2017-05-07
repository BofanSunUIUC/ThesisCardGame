



<?php

	header("Content-type: text/html");

	$base_addr = substr(strrchr(__FILE__, DIRECTORY_SEPARATOR), 1);
	$parts = explode("-", substr($base_addr, 0, -4));
	$size = $parts[0];	# m or s
	$app = $parts[1];	# walmart, wikipedia or times
	$algo = $parts[2];	# navigation, simplex or duplex

	$caps = "-entity-cap 1 -tag-cap 4";
	if ($size === "m" and $algo === "navigation") {
		$caps = "-entity-cap 2 -tag-cap 8";
	}
	$boost = 0.0;
	if ($app === "wikipedia") {
		$boost = 0.5;
	}
	$mode = "search";
	if ($app === "times") {
		$mode = "browse";
	}
	$entity_scroll_up_index = 0;
	$entity_scroll_down_index = 0;
	$entity_tag_switch_index = 0;
	$tag_scroll_up_index = 0;
	$tag_scroll_down_index = 0;
	$tag_entity_switch_index = 0;
	$entity_caption = "";
	$tag_caption = "";
	if ($algo !== "navigation") {
		$entity_scroll_up_index = 101;
		$entity_scroll_down_index = 103;
		$tag_scroll_up_index = 107;
		$tag_scroll_down_index = 109;
		if ($algo === "simplex") {
			$entity_tag_switch_index = 105;
			$tag_entity_switch_index = 111;
		}
		if ($app === "walmart") {
			$entity_caption = "products";
			$tag_caption = "product attributes";
		} elseif ($app === "wikipedia") {
			$entity_caption = "articles";
			$tag_caption = "article categories";
		} else {	# $app === "times"
			$entity_caption = "news";
			$tag_caption = "news keywords";
		}
	}

	echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n";
	echo "<html><head>\n";
	echo "\t<title>The " . ucwords($app) . " Canvas</title>\n";
	echo "\t<link rel=\"stylesheet\" type=\"text/css\" href=\"$size-style.css\">\n";
	echo "\t<link rel=\"icon\" type=\"image/x-icon\" href=\"magnifier.ico\">\n";
	echo "</head><body>\n";
	echo "\t<center><div class=\"wrap\">\n";

	$query = "";
	if ($mode === "search" and isset($_GET["query"])) {
		$query = preg_replace(
				array("/^\W+/", "/\W+/", "/\W+$/"),
				array("", " ", ""), $_GET["query"]);
	}
	# echo "\t\t<!--$query-->\n";
	$session = "";
	$path = "";
	$lap = "";
	$prev_lap = "";
	$selected = -1;
	if (isset($_GET["session"]) && ctype_digit($_GET["session"])) {
		$subdir = $_GET["session"] % 1000;
		$subsubdir = ($_GET["session"] - $subdir) / 1000 % 1000;
		$path = "sessions" . DIRECTORY_SEPARATOR .
				$subdir . DIRECTORY_SEPARATOR .
				$subsubdir . DIRECTORY_SEPARATOR .
				$_GET["session"] . DIRECTORY_SEPARATOR;
		if (file_exists($path) and file_exists($path . "info.txt") and
				isset($_GET["lap"]) and ctype_digit($_GET["lap"])) {
			$lap = $_GET["lap"];
			if ($lap > 0) {
				$prev_lap = $lap - 1;
			}
			if (file_exists($path . $prev_lap . "-selection.txt") and
					isset($_GET["selected"]) and
					ctype_digit($_GET["selected"])) {
				$session = $_GET["session"];
				$selected = $_GET["selected"];
			} elseif (file_exists($path . $lap . "-selection.txt")) {
				$session = $_GET["session"];
			}
		}
	}

	if ($mode === "search" and (($query === "" and $session === "") or
			($query !== "" and $session !== ""))) {
		echo "\t\t<div id=\"form-wrap\"><form action=\"$base_addr\" method=\"get\">\n";
		echo "\t\t\t<input autofocus type=\"text\" name=\"query\" placeholder=\"What's in your mind?\" id=\"search-box\" />\n";
		echo "\t\t\t<input type=\"submit\" value=\"\" id=\"search-button\" />\n";
		echo "\t\t</form></div>\n";
		echo "\t</div></center>\n";
		echo "</body></html>\n";
		exit();
	}

	if ($session === "") {
		$session = trim(shell_exec("java -cp blanket.jar:json-simple-1.1.1.jar Blanket -$app -$algo -initial-lap $caps -first-boost $boost -query \"$query\""));
		$subdir = $session % 1000;
		$subsubdir = ($session - $subdir) / 1000 % 1000;
		$path = "sessions" . DIRECTORY_SEPARATOR .
				$subdir . DIRECTORY_SEPARATOR .
				$subsubdir . DIRECTORY_SEPARATOR .
				$session . DIRECTORY_SEPARATOR;
		$lap = 0;
	} elseif ($selected >= 0) {
		shell_exec("java -cp blanket.jar:json-simple-1.1.1.jar Blanket -$algo -subsequent-laps $caps -session $session -lap $lap -selected $selected");
	}
	$selection = array_slice(file($path . $lap . "-selection.txt"), 1);
	$selection_size = count($selection);
	$entity_prefix = "ENTITY: ";
	$entity_prefix_len = strlen($entity_prefix);
	$s_simplex_entities = 1;
	if ($selection_size > 0 and $size === "s" and $algo === "simplex") {
		foreach ($selection as $line) {
			$s = trim($line);
			if (substr($s, 0, $entity_prefix_len) !== $entity_prefix) {
				$s_simplex_entities = 0;
				break;
			}
		}
	}
	$next_lap = $lap + 1;

	echo "\t\t<div class=\"arrow-wrap\">\n";
	$back_addr = $base_addr;
	if ($session !== "" and $prev_lap !== "") {
		$back_addr .= "?session=$session&lap=$prev_lap";
	}
	if ($algo === "navigation") {
		echo "\t\t<a href=\"$back_addr\" title=\"Back\"><img class=\"arrow\" src=\"back.png\"></a>\n";
	} elseif ($size === "s" and $algo === "simplex") {
		echo "\t\t\t<div class=\"arrow-sep\"></div>\n";
		echo "\t\t\t<div class=\"arrow-unit\"><a href=\"$back_addr\" title=\"Back\"><img class=\"arrow\" src=\"back.png\"></a></div>\n";
		if ($selection_size > 0) {
			$switch_addr = $base_addr . "?session=$session&lap=$next_lap&selected=";
			$switch_title = "Switch to ";
			if ($s_simplex_entities === 1) {
				$switch_addr .= $entity_tag_switch_index;
				$switch_title .= $tag_caption;
			} else {
				$switch_addr .= $tag_entity_switch_index;
				$switch_title .= $entity_caption;
			}
			echo "\t\t\t<div class=\"arrow-unit\"><a href=\"$switch_addr\" title=\"$switch_title\"><img class=\"arrow\" src=\"switch.png\"></a></div>\n";
		}
	} else {	# $size === "m" and $algo === "duplex"
		echo "\t\t\t<div class=\"arrow-sep\"></div>\n";
		echo "\t\t\t<div class=\"arrow-unit\"><a href=\"$back_addr\" title=\"Back\"><img class=\"arrow\" src=\"back.png\"></a></div>\n";
		if ($selection_size > 0) {
			$entity_up_addr = $base_addr . "?session=$session&lap=$next_lap&selected=$entity_scroll_up_index";
			echo "\t\t\t<div class=\"arrow-unit\"><a href=\"$entity_up_addr\" title=\"Scroll up $entity_caption\"><img class=\"arrow\" src=\"up.png\"></a></div>\n";
			$entity_down_addr = $base_addr . "?session=$session&lap=$next_lap&selected=$entity_scroll_down_index";
			echo "\t\t\t<div class=\"arrow-unit\"><a href=\"$entity_down_addr\" title=\"Scroll down $entity_caption\"><img class=\"arrow\" src=\"down.png\"></a></div>\n";
		}
	}
	echo "\t\t</div>\n";

	echo "\t\t<div class=\"content-wrap\">\n";
	$block_end = "BLOCKS END";
	$info = "";
	foreach ($selection as $line) {
		# $s = preg_replace("/\"/", "\\\"", trim($line));
		$s = trim($line);
		if (substr($s, 0, $entity_prefix_len) !== $entity_prefix) { continue; }
		if ($info === "") {
			$info = array_slice(file($path . "info.txt"), 1);
		}
		$id = substr($s, $entity_prefix_len);
		$count = 0;
		$state = 0;
		$title = "";
		$url = "";
		$img_url = "";
		$snippet = "";
		foreach ($info as $e_line) {
			$e_s = trim($e_line);
			if ($e_s === $block_end) {
				++$count;
				continue;
			}
			if ($count < $id) { continue; }
			if ($count > $id) { break; }
			if ($app === "walmart") {
				if ($state == 0) {
					$state = 1;
				} elseif ($state == 1) {
					$title = $e_s;
					$state = 2;
				} elseif ($state == 2) {
					$url = $e_s;
					$state = 3;
				} elseif ($state == 3) {
					$img_url = $e_s;
					$state = 4;
				} elseif ($state == 4) {
					$price = $e_s;
					echo "\t\t\t<div class=\"doc\">\n";
					echo "\t\t\t\t<div class=\"thumb-wrap\"><img class=\"thumb\" src=\"$img_url\" alt=\"$title\"></div>\n";
					echo "\t\t\t\t<div class=\"desc\">\n";
					echo "\t\t\t\t\t<a href=\"$url\" target=\"_blank\">$title</a><br/>\n";
					if ($price !== "null") {
						echo "\t\t\t\t\t<span class=\"sup\"><br/></span>\n";
						echo "\t\t\t\t\t$$price\n";
					}
					echo "\t\t\t\t</div>\n";
					echo "\t\t\t</div>\n";
					$state = 5;
				}
			} elseif ($app === "wikipedia") {
				if ($state == 0) {
					$title = $e_s;
					$state = 1;
				} elseif ($state == 1) {
					$url = $e_s;
					$state = 2;
				} elseif ($state == 2) {
					$snippet = $e_s;
					echo "\t\t\t<div class=\"doc\">\n";
					echo "\t\t\t\t<div class=\"desc-long\">\n";
					echo "\t\t\t\t\t<a href=\"$url\" target=\"_blank\">$title</a><br/>\n";
					echo "\t\t\t\t\t<span class=\"sup\"><br/></span>\n";
					echo "\t\t\t\t\t$snippet\n";
					echo "\t\t\t\t</div>\n";
					echo "\t\t\t</div>\n";
					$state = 3;
				}
			} else {	# times
				if ($state == 0) {
					$title = $e_s;
					$state = 1;
				} elseif ($state == 1) {
					$url = $e_s;
					$state = 2;
				} elseif ($state == 2) {
					$snippet = $e_s;
					$state = 3;
				} elseif ($state == 3) {
					$img_url = $e_s;
					echo "\t\t\t<div class=\"doc\">\n";
					if ($img_url === "null") {
						echo "\t\t\t\t<div class=\"desc-long\">\n";
						echo "\t\t\t\t\t<a href=\"$url\" target=\"_blank\">$title</a><br/>\n";
						echo "\t\t\t\t\t<span class=\"sup\"><br/></span>\n";
						echo "\t\t\t\t\t$snippet\n";
						echo "\t\t\t\t</div>\n";
					} else {
						echo "\t\t\t\t<div class=\"thumb-wrap\"><img class=\"thumb\" src=\"$img_url\" alt=\"$title\"></div>\n";
						echo "\t\t\t\t<div class=\"desc\">\n";
						echo "\t\t\t\t\t<a href=\"$url\" target=\"_blank\">$title</a><br/>\n";
						echo "\t\t\t\t</div>\n";
					}
					echo "\t\t\t</div>\n";
					$state = 4;
				}
			}
		}
	}
	# if ($selection_size > 0 and $size === "m" and $algo === "duplex" and $info === "") {
		# echo "\t\t\t<div class=\"doc\">\n";
		# echo "\t\t\t</div>\n";
	# }
	$selection_i = 0;
	foreach ($selection as $line) {
		# $s = preg_replace("/\"/", "\\\"", trim($line));
		$s = trim($line);
		if (substr($s, 0, $entity_prefix_len) !== $entity_prefix) {
			$tag_addr = $base_addr .
					"?session=$session&lap=$next_lap&selected=$selection_i";
			echo "\t\t\t<div class=\"cat\"><a href=\"$tag_addr\">$s</a></div>\n";
			++$selection_i;
		} elseif ($algo === "navigation") {
			++$selection_i;
		}
	}
	echo "\t\t</div>\n";

	echo "\t\t<div class=\"arrow-wrap\">\n";
	if ($selection_size > 0) {
		if ($algo === "navigation") {
			$more_addr = $base_addr . "?session=$session&lap=$next_lap&selected=$selection_size";
			echo "\t\t<a href=\"$more_addr\" title=\"More\"><img class=\"arrow\" src=\"more.png\"></a>\n";
		} elseif ($size === "s" and $algo === "simplex") {
			echo "\t\t\t<div class=\"arrow-sep\"></div>\n";
			$scroll_up_addr = $base_addr . "?session=$session&lap=$next_lap&selected=";
			$scroll_up_caption = "Scroll up ";
			$scroll_down_addr = $base_addr . "?session=$session&lap=$next_lap&selected=";
			$scroll_down_caption = "Scroll down ";
			if ($s_simplex_entities === 1) {
				$scroll_up_addr .= $entity_scroll_up_index;
				$scroll_up_caption .= $entity_caption;
				$scroll_down_addr .= $entity_scroll_down_index;
				$scroll_down_caption .= $entity_caption;
			} else {
				$scroll_up_addr .= $tag_scroll_up_index;
				$scroll_up_caption .= $tag_caption;
				$scroll_down_addr .= $tag_scroll_down_index;
				$scroll_down_caption .= $tag_caption;
			}
			echo "\t\t\t<div class=\"arrow-unit\"><a href=\"$scroll_up_addr\" title=\"$scroll_up_caption\"><img class=\"arrow\" src=\"up.png\"></a></div>\n";
			echo "\t\t\t<div class=\"arrow-unit\"><a href=\"$scroll_down_addr\" title=\"$scroll_down_caption\"><img class=\"arrow\" src=\"down.png\"></a></div>\n";
		} else {	# $size === "m" and $algo === "duplex"
			echo "\t\t\t<div class=\"arrow-sep\"></div>\n";
			echo "\t\t\t<div class=\"arrow-sep\"></div>\n";
			$tag_up_addr = $base_addr . "?session=$session&lap=$next_lap&selected=$tag_scroll_up_index";
			echo "\t\t\t<div class=\"arrow-unit\"><a href=\"$tag_up_addr\" title=\"Scroll up $tag_caption\"><img class=\"arrow\" src=\"up.png\"></a></div>\n";
			$tag_down_addr = $base_addr . "?session=$session&lap=$next_lap&selected=$tag_scroll_down_index";
			echo "\t\t\t<div class=\"arrow-unit\"><a href=\"$tag_down_addr\" title=\"Scroll down $tag_caption\"><img class=\"arrow\" src=\"down.png\"></a></div>\n";
		}
	}
	echo "\t\t</div>\n";

	echo "\t</div></center>\n";
	echo "</body></html>\n";

?>

