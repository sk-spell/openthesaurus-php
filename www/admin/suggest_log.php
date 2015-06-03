<?php
include("../../include/phplib/prepend.php3");
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Auth"));
$db = new DB_Thesaurus;
$db2 = new DB_Thesaurus;
include("../../include/tool.php");

if( $auth->auth['uname'] != 'admin' ) {
	print "Access denied.";
	return;
}

function shorten($str) {
	$str = substr($str, 0, 15);
	return $str;
}

$title = "Unknown words from search log";
include("../../include/top.php");
?>

<p>This pages lists words that people have searched
for but that didn't return a match (substring searches
are ignored). Note that this list may also contain
spelling errors.</p>

<p><a href="suggest_log.php?limit=20">20</a>
<a href="suggest_log.php?limit=50">50</a>
<a href="suggest_log.php?limit=100">100</a></p>

<?php
$limit = 20;
if( array_key_exists('limit', $_GET) && $_GET['limit'] ) {
	$limit = intval($_GET['limit']);
}
$i = 0;
$query = sprintf("SELECT date, term, matches, submatch, ip
	FROM search_log
	WHERE matches = 0 AND submatch = 0
	ORDER by date DESC
	LIMIT %d", $limit);
$db->query($query);
?>
<table>
<tr>
	<td><strong>#</strong></td>
	<td><strong>Date</strong></td>
	<td><strong>Term</strong></td>
	<td align="right"><strong>Matches</strong></td>
	<td align="right"><strong>IP</strong></td>
</tr>
</tr>
	<?php
	while( $db->next_record() ) {
		$query2 = sprintf("SELECT word, meaning_id
			FROM word_meanings, words, meanings
			WHERE 
				words.word = '%s' AND
				words.id = word_meanings.word_id AND
				word_meanings.meaning_id = meanings.id AND
				meanings.hidden = 0
				LIMIT 1", addslashes($db->f('term')));
		$db2->query($query2);
		if( $db2->nf() > 0 ) {
			continue;
		}

		if( ($i % 2) == 0 ) {
			$col = ' bgcolor="#eeeeee"';
		} else {
			$col = "";
		}
		?>
		<tr<?php print $col; ?>>
			<td align="right"><?php print $i+1 ?></td>
			<td><?php print $db->f('date') ?></td>
			<td><?php
				$link = "../synset.php?word=".escape($db->f('term'));
				if( $db->f('submatch') ) {
					$link .= "&substring=on";
				}
				?>
				<a href="<?php print $link ?>"><?php print escape($db->f('term')) ?></a></td>
			<td align="right"><?php print $db->f('matches') ?></td>
			<td align="right"><?php print $db->f('ip') ?></td>
		<?php
		$i++;
	}
?>
</table>

<?php
include("../../include/bottom.php");
page_close();
?>
