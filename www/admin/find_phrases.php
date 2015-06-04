<?php
include("../include/phplib/prepend.php3");
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Auth"));
$db = new DB_Thesaurus;
include("../include/tool.php");

if( $auth->auth['uname'] != 'admin' ) {
	print "Access denied.";
	return;
}

$title = "OpenThesaurus admin interface: find phrases";
include("../include/top.php");

$query = sprintf("SELECT words.id, word
	FROM words, word_meanings, meanings
	WHERE
		words.id = word_meanings.word_id AND
		word_meanings.meaning_id = meanings.id AND
		meanings.hidden = 0
	ORDER by word");
$db->query($query);
$i = 0;
while( $db->next_record() ) {
	$words = preg_split("/\s+/", $db->f('word'));
	if( sizeof($words) >= 4 ) {
		$i++;
		?>
		<?php print $i; ?>.
		<a href="../synset.php?word=<?php print urlencode($db->f('word')) ?>"><?php 
			print $db->f('word') ?></a><br />
		<?php
	}
}

include("../include/bottom.php");
page_close();
?>
