<?php
include("../include/phplib/prepend.php3");
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Auth"));
$db = new DB_Thesaurus;
include("../include/tool.php");

if( $auth->auth['uname'] != 'admin' ) {
	print "Access denied.";
	return;
}

$title = "OpenThesaurus admin interface: find large synsets";
include("../include/top.php");

$limit = 5;
$query_limit = 2000;

print "<p>All (non-hidden) synsets with at least $limit words (query_limit=$query_limit).</p>";

$query = sprintf("SELECT meaning_id
	FROM word_meanings, meanings
	WHERE word_meanings.meaning_id = meanings.id AND
		meanings.hidden = 0
	ORDER by meaning_id LIMIT $query_limit");
$db->query($query);
$i = 0;
$ids = array();
while( $db->next_record() ) {
	$ids[$db->f('meaning_id')] = 1;	# 1 is a fake value
}

$i = 0;
$c = 0;
while( list($id, $val) = each($ids) ) {
	$query = sprintf("SELECT id
		FROM word_meanings
		WHERE word_meanings.meaning_id = %d", $id);
	#print $query."<br>";
	$db->query($query);
	if( $db->nf() >= $limit ) {
	#if( $db->nf() == 1 ) {
		$c++;
		?>
		<?php print $c; ?>.
		<a href="../synset.php?id=<?php print $id ?>"><?php 
			print join(', ', getSynset($id)) ?></a><br />
		<?php
	}
	$i++;
}

include("../include/bottom.php");
page_close();
?>
