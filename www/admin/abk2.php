<?php
include("../../include/phplib/prepend.php3");
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Auth"));
$db = new DB_Thesaurus;
include("../../include/tool.php");

if( $auth->auth['uname'] != 'admin' ) {
	print "Access denied.";
	return;
}

$title = "OpenThesaurus admin interface: words with \"...\"";
include("../../include/top.php");

$i = 1;

print "<p><b>Words that contain \"...\":</b></p>";
$query = "SELECT word, words.id AS wid, word_meanings.meaning_id AS mid
	FROM words, word_meanings, meanings
	WHERE 
		words.word LIKE '%...%' AND
		word_meanings.word_id = words.id AND
		meanings.id = word_meanings.meaning_id AND
		meanings.hidden = 0
	ORDER BY word";
$db->query($query);
while( $db->next_record() ) {
	#print $db->f('mid');
	$s = getSynset($db->f('mid'));
	#print $i.". <a title=\"hide\" href=\"hide.php?id=".$db->f('mid')."\">".join(', ', $s)."</a>";
	print $i.". <a href=\"../synset.php?id=".$db->f('mid')."\">".join(', ', $s)."</a>";
	#print ' (<a href="../synset.php?id='.$db->f('mid').'">go</a>)';
	print "<br>";
	#print $i.". <a href=\"../synset.php?id=".$db->f('mid')."\">".join(', ', $s)."</a><br>";
	#print $i.". <a href=\"../synset.php?word=".urlencode($db->f('word'))."\">".$db->f('word')."</a><br>\n";
	$i++;
}

include("../../include/bottom.php");
page_close();
?>
