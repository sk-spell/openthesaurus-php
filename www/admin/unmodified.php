<?php
include("../../include/phplib/prepend.php3");
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Auth"));
$db = new DB_Thesaurus;
$db2 = new DB_Thesaurus;
include("../../include/tool.php");

if( $auth->auth['uname'] != 'admin' ) {
	print "Access denied.";
	return;
}

$title = "OpenThesaurus admin interface: count modified/unmodified synsets";
include("../../include/top.php");

$i = 1;

$query = "SELECT id FROM meanings
	WHERE hidden = 0";
$db->query($query);
$i = 0;
$unmodified = 0;
$modified = 0;
while( $db->next_record() ) {
	if( $i % 1000 == 0 ) {
		print "$i...<br>\n";
		flush();
	}
	$query = sprintf("SELECT id
		FROM user_actions_log
		WHERE synset_id = %d", $db->f('id'));
	$db2->query($query);
	if( $db2->nf() == 0 ) {
		$unmodified++;
	} else {
		$modified++;
	}
	$i++;
}
print "<p>";
print "Synsets: ".$db->nf()."<br>";
print "Unmodified synsets: ".$unmodified."<br>";
print "Modified synsets: ".$modified."<br>";

include("../../include/bottom.php");
page_close();
?>
