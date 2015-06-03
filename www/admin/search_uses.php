<?php
include("../../include/phplib/prepend.php3");
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Auth"));
$db = new DB_Thesaurus;
include("../../include/tool.php");

if( $auth->auth['uname'] != 'admin' ) {
	print "Access denied.";
	return;
}

$title = "OpenThesaurus admin interface: Search uses != NULL";
include("../../include/top.php");

$i = 1;
$query = sprintf("SELECT word_meanings.use_id, meanings.id, words.word, uses.name
		FROM words, word_meanings, meanings, uses
		WHERE word_meanings.use_id IS NOT NULL AND
			uses.id = word_meanings.use_id AND
			meanings.hidden = 0 AND
			word_meanings.word_id = words.id AND
			word_meanings.meaning_id = meanings.id
		ORDER BY meanings.id");
$db->query($query);
while( $db->next_record() ) { ?>
	<?php print $i ?>.
		<a href="../synset.php?id=<?php print $db->f('id') ?>">
		<?php print $db->f('word')." [".$db->f('name')."]" ?></a>
		<br />
	<?php
	$i++;
}

include("../../include/bottom.php");
page_close();
?>
