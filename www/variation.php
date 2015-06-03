<?php
include("../include/phplib/prepend.php3");
$cancel_login = 1;
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Default_Auth"));
include("../include/tool.php");
$db = new DB_Thesaurus;

if ($_GET['lang'] == 'at') {
	$title = "Austriazismen - &Ouml;sterreichische W&ouml;rter - OpenThesaurus";
	$condition = "österr.";
} else if ($_GET['lang'] == 'ch') {
	$title = "Helvetismen - Schweizer W&ouml;rter - OpenThesaurus";
	$condition = "schweiz.";
} else {
	print "Unknown language";
	exit;
}

include("../include/top.php");
?>

<?php if ($_GET['lang'] == 'at') { ?>
	<p>Synonymgruppen mit haupts&auml;chlich in &Ouml;sterreich gebr&auml;uchlichen W&ouml;rtern. Diese Liste ist nicht
	vollst&auml;ndig. Mehr findet man in der <a href="http://de.wikipedia.org/wiki/Liste_von_Austriazismen">Wikipedia</a>.</p>
<?php } else if ($_GET['lang'] == 'ch') { ?>
	<p>Synonymgruppen mit haupts&auml;chlich in der Schweiz gebr&auml;uchlichen W&ouml;rtern. Diese Liste ist nicht
	vollst&auml;ndig. Mehr findet man in der <a href="http://de.wikipedia.org/wiki/Helvetismus">Wikipedia</a>.</p>
<?php } ?>

<?php
$query = sprintf("SELECT meaning_id, word, meanings.super_id,
		distinction, meanings.hidden
	FROM word_meanings, words, meanings
	WHERE 
		words.word LIKE '%%%s%%' AND
		words.id = word_meanings.word_id AND
		word_meanings.meaning_id = meanings.id AND
		meanings.hidden = 0
	ORDER BY word", $condition);
$db->query($query);
?>
<ul>
<?php
$i = 1;
$prev_id = -1;
while( $db->next_record() ) {
	$s = getSynsetWithUsage($db->f('meaning_id'), 1);
	$str = "";
	$var_word = "";
	$nonvar_words = array();
	foreach ($s as $syn) {
		if ($str != "") {
			$str .= ", ";
		}
		if (strpos($syn, $condition) !== false) {
			#$str .= "<strong>".$syn."</strong>";
			$var_word = preg_replace("/\($condition\)/", "", $syn);
			$var_word = trim($var_word);
		} else {
			array_push($nonvar_words, $syn);
		}
	}
	$id = $db->f('meaning_id');
	if ($id != $prev_id) {
		print "<li>$var_word: <a href=\"synset.php?id=".$id."\">".limitedJoin(', ', $nonvar_words, 3)."</a></li>";
	}
	$prev_id = $id;
	$i++;
} ?>

</ul>

<?php
include("../include/bottom.php");
page_close();
?>
