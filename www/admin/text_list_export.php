<?php
if( ! (getenv('REMOTE_ADDR') == getenv('SERVER_ADDR')) ) {
	print "Access from your host is denied.";
	return;
}

chdir(dirname(__FILE__));
include("../include/phplib/prepend.php3");
$db = new DB_Thesaurus;
include("../include/tool.php");

$query = sprintf("SELECT DISTINCT word
	FROM words, word_meanings, meanings WHERE
		words.id = word_meanings.word_id AND
		word_meanings.meaning_id = meanings.id AND
		hidden = 0
		ORDER BY word");
$db->query($query);

print $db->nf()." words:<br />\n";
$word_ct = 0;
while( $db->next_record() ) {
	print $db->f('word')." ";
	$word_ct++;
	if( $word_ct % 50 == 0 ) {
		print "\n\n<p>";
	}
}

print "<hr />\n";

page_close();
?>
