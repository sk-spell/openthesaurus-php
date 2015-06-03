<?php
include("../../include/phplib/prepend.php3");
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Auth"));
$db = new DB_Thesaurus;
include("../../include/tool.php");

if( $auth->auth['uname'] != 'admin' ) {
	print "Access denied.";
	return;
}

$title = "OpenThesaurus admin interface: find prefixes/suffixes";
include("../../include/top.php");

$i = 1;

print "<p><b>Words with parenthesis (= words that might contain prefixes/suffixes):</b></p>";

$query = "SELECT * FROM words WHERE
	word LIKE '%(%'";
$db->query($query);
while( $db->next_record() ) {
	print "$i. ";
	$case = 0;
	if( substr($db->f('word'), 0, 1) == "(" ) {
		print "prefix: ";
		$case = 1;
	}
	if( substr($db->f('word'), strlen($db->f('word'))-1, 1) == ")" ) {
		print "suffix: ";
		$case = 2;
	}
	if( $case == 0 ) {
		print "special case: ";
	}
	print "<a href=\"../synset.php?word=".urlencode($db->f('word'))."\">".$db->f('word')."</a><br>\n";
	$i++;
}

include("../../include/bottom.php");
page_close();
?>
