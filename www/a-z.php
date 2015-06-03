<?php
include("../include/phplib/prepend.php3");
$cancel_login = 1;
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Default_Auth"));
include("../include/tool.php");
$db = new DB_Thesaurus;

$disable_title = 1;
$title = _("OpenThesaurus: All Words A-Z");
$pagetitle = _("Words A-Z");
$stop_robots = 1;
include("../include/top.php");
?>

<h1><?php print $pagetitle ?></h1>

<br />

<?php
$start_chars = "";
if (array_key_exists('start', $_GET) && $_GET['start']) {
	$start_chars = myaddslashes($_GET['start']);
} else {
	$start_chars = "A";
}
$chars = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N',
	'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
$subchar = "";
// first level:
$i = 0;
print "<div class=\"allchars\">";
foreach ($chars as $char) {
	if ($i > 0) {
		print " | ";
	}
	if ($start_chars && strpos($start_chars, $char) === 0) {
		print "<strong>".$char."</strong>";
		$subchar = $char;
	} else {
		print "<a href=\"?start=$char\">".$char."</a>";
	}
	$i++;
}
print "</div>";
?>
<?php
// second level:
$i = 0;
if ($subchar) {
	foreach ($chars as $char) {
		if ($i > 0) {
			print " | ";
		}
		if ($start_chars && strpos($start_chars, $subchar.$char) === 0) {
			print "<strong>".$subchar.strtolower($char)."</strong>";
		} else {
			print "<a href=\"?start=$subchar$char\">".$subchar.strtolower($char)."</a>";
		}
		$i++;
	}
}
?>

<br /><br />

<table border="0" width="80%">
<tr>
	<td width="30%" valign="top">
	<ul>

	<?php
	$startpos = 0;
	if (array_key_exists('startpos', $_GET) && $_GET['startpos']) {
		$startpos = $_GET['startpos'];
	}
	$limit = 60;
	$show_next_link = 0;
	if ($start_chars) {
		$query = sprintf("SELECT DISTINCT word FROM words, word_meanings, meanings
			WHERE word LIKE '%s%%'  AND
			words.id = word_meanings.word_id AND
			word_meanings.meaning_id = meanings.id AND
			meanings.hidden = 0 AND
			meanings.id NOT IN (%s)
			LIMIT %d,%d",
			$start_chars, HIDDEN_SYNSETS, $startpos, $limit+2);
		#print $query;
		$db->query($query);
		$count = 0;
		while( $db->next_record() ) {
			if (($count % ($limit/3)) == 0 && $count > 0) {
				print "</ul></td>";
				print "<td width=\"10\">&nbsp;&nbsp;&nbsp;</td>";
				print "<td width=\"30%\" valign=\"top\">";
				print "<ul>";
			}
			print "<li><a href=\"overview.php?word=".urlencode($db->f('word'))."\">".
				$db->f('word')."</a></li>";
			if ($count >= $limit-1) {
				$show_next_link = 1;
				break;
			}
			$count++;
		}
		if ($count == 0) {
			print "<li>"._("No words found starting with these characters")."</li>";
		}
		if ($count < 60) {
			# avoid right column getting too wide
			print "</ul></td><td><ul><li style=\"list-style:none\">&nbsp;</li>";
		}
	}
	?>
	</ul>
	</td>
</tr>

<tr>
	<td>&nbsp;</td>
</tr>
<tr>
	<td colspan="5" align="center">
		<?php if ($startpos-$limit >= 0) { ?>
			<b><a href="?start=<?php print escape($start_chars) ?>&amp;startpos=<?php print $startpos-$limit ?>">&lt;&lt;&nbsp;zurück</a></b>
		<?php } else { ?>
			<b>&lt;&lt;&nbsp;zurück</b>
		<?php } ?>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<?php if ($show_next_link) { ?>
			<b><a href="?start=<?php print escape($start_chars) ?>&amp;startpos=<?php print $startpos+$limit ?>">weiter&nbsp;&gt;&gt;</a></b>
		<?php } else { ?>
			<b>weiter&nbsp;&gt;&gt;</b>
		<?php } ?>
	</td>
</tr>
</table>

<br />

<?php
include("../include/bottom.php");
page_close();
?>
