<?php
include("../include/phplib/prepend.php3");
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Auth"));
$db = new DB_Thesaurus;
include("../include/tool.php");

if( $auth->auth['uname'] != 'admin' ) {
	print "Access denied.";
	return;
}

$title = "OpenThesaurus admin interface: Free text search (case-insensitive)";
include("../include/top.php");

$s = "";
if( isset($_GET['str']) && trim($_GET['str']) ) {
	$s = $_GET['str'];
}
?>

<form action="" method="get">
	Enter SQL: word LIKE <input type="text" name="str" value="<?php print escape($s); ?>" />
	<input type="submit" value="Search" />
</form>


<?php

if( isset($_GET['str']) && trim($_GET['str']) ) {
	$i = 1;
	$query = sprintf("SELECT words.word, meanings.id AS mid
		FROM words, meanings, word_meanings
		WHERE
			LOWER(words.word) LIKE '%s' AND
			words.id = word_meanings.word_id AND
			word_meanings.meaning_id = meanings.id AND
			meanings.hidden = 0
			ORDER BY word", myaddslashes(strtolower($_GET['str'])));
	$db->query($query);
	print '<br />';
	#$prev_word = "";
	while( $db->next_record() ) {
		#if( $db->f('word') == $prev_word ) {
		#	$prev_word = $db->f('word');
		#	continue;
		#}
		#$prev_word = $db->f('word');
		print $i.". <a href=\"../synset.php?id=".urlencode($db->f('mid'))."\">".$db->f('word')."</a><br>\n";
		$i++;
	}
}
include("../include/bottom.php");
page_close();
?>
