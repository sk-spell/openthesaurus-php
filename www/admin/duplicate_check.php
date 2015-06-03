<?php
include("../../include/phplib/prepend.php3");
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Auth"));
$db = new DB_Thesaurus;
include("../../include/tool.php");

if( $auth->auth['uname'] != 'admin' ) {
	print "Access denied.";
	return;
}

$title = "OpenThesaurus admin interface: Duplicate Finder (slow!)";
include("../../include/top.php");

$i = 1;

$query = "SELECT word_id, meaning_id, hidden
	FROM word_meanings, meanings
	WHERE meaning_id = meanings.id AND
		meanings.hidden = 0
	ORDER BY meaning_id";
$db->query($query);
$meanings = array();
$prev_id = -1;
# fixme: first id
$ids = array();
// first read all non-hidden meanings into an 
// meaning -> word1,word2,... hash table:
while( $db->next_record() ) {
	if( $db->f('meaning_id') != $prev_id ) {
		array_push($ids, $db->f('word_id'));
		$meanings[$prev_id] = $ids;
		$ids = array();
	} else {
		array_push($ids, $db->f('word_id'));
	}
	
	$prev_id = $db->f('meaning_id');
}

$keys = array_keys($meanings);
sort($keys);
foreach( $keys as $key ) {
	$meaning_id = $key;
	$word_ids = $meanings[$key];
	print $meaning_id."<br>\n";
	flush();
	foreach( $keys as $key ) {
		#print "*".join('-', $word_ids)." -- \n";
		$meaning_id_tmp = $key;
		$word_ids_tmp = $meanings[$key];
		if( $meaning_id != $meaning_id_tmp &&
			sizeof(array_intersect($word_ids, $word_ids_tmp)) > 2 ) {
			#print $meaning_id.": ".join(',', $word_ids)."<br>";
			print join(',', getSynset($meaning_id))." -- ".join(',', getSynset($meaning_id_tmp))."<br>\n";
		}
	}
}

include("../../include/bottom.php");
page_close();
?>
