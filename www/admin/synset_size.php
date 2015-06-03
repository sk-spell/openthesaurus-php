<?php
include("../../include/phplib/prepend.php3");
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Auth"));
$db = new DB_Thesaurus;
include("../../include/tool.php");

if( $auth->auth['uname'] != 'admin' ) {
	print "Access denied.";
	return;
}

$title = "OpenThesaurus admin interface: average synset size";
include("../../include/top.php");

$i = 1;

$query = "SELECT id FROM meanings
	WHERE hidden = 0";
$db->query($query);
$i = 0;
$count = 0;
while( $db->next_record() ) {
	if( $i % 1000 == 0 ) {
		print "$i...<br>\n";
		flush();
	}
	$synset = getSynset($db->f('id'));
	$count = $count + sizeof($synset);
	$i++;
}
print "<p>";
print "Synsets: ".$db->nf()."<br>";
print "Words in synsets: ".$count."<br>";
print "Words/synset: ".($count/$db->nf())."<br>";

include("../../include/bottom.php");
page_close();
?>
