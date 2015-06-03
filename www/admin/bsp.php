<?php
include("../../include/phplib/prepend.php3");
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Auth"));
$db = new DB_Thesaurus;
include("../../include/tool.php");

if( $auth->auth['uname'] != 'admin' ) {
	print "Access denied.";
	return;
}

$title = "OpenThesaurus admin interface: random synsets";
include("../../include/top.php");

$word = "";
// Show random word
# FIXME: this can fail if the word is in no synset:
$query = sprintf("SELECT id FROM meanings ORDER BY RAND() LIMIT 40");
#print $query;
$db->query($query);
?>

<?php
$word_ids = array();
$words = array();
$prev_word_id = -1;
while( $db->next_record() ) {
	#print $db->f('id')."<br>";
	print join(', ', getSynset($db->f('id')))."<br>\n";
}
?>

<?php
include("../../include/bottom.php");
page_close();
?>
