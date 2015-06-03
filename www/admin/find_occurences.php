<?php
include("../../include/phplib/prepend.php3");
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Auth"));
$db = new DB_Thesaurus;
include("../../include/tool.php");

if( $auth->auth['uname'] != 'admin' ) {
	print "Access denied.";
	return;
}

$title = "OpenThesaurus admin interface: multi-occurences";
include("../../include/top.php");

$limit = 3;

print "<p>All words which appear in $limit or more synsets:</p>";

$query = sprintf("SELECT id, word
	FROM words
	ORDER by word");
$db->query($query);
$i = 0;
$ids = array();
$words = array();
while( $db->next_record() ) {
	array_push($ids, $db->f('id'));
	array_push($words, $db->f('word'));
}

$i = 0;
$c = 0;
foreach($ids as $id) {
	$query = sprintf("SELECT id
		FROM word_meanings
		WHERE word_meanings.word_id = %d", $id);
	#print $query."<br>";
	$db->query($query);
	if( $db->nf() >= $limit ) {
		$c++;
		?>
		<?php print $c; ?>.
		<a href="../multimatch.php?word=<?php print urlencode($words[$i]) ?>"><?php 
			print $words[$i] ?></a><br />
		<?php
	}
	$i++;
}

include("../../include/bottom.php");
page_close();
?>
