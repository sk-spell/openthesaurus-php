<?php
include("../include/phplib/prepend.php3");
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Auth"));
$db = new DB_Thesaurus;
include("../include/tool.php");

if( $auth->auth['uname'] != 'admin' ) {
	print "Access denied.";
	return;
}

$title = "OpenThesaurus admin interface: find possible foreign words";
include("../include/top.php");

$i = 1;

print "<p><b>Words that end in -us (but not -aus):</b></p>";
$query = "SELECT * FROM words WHERE
	word LIKE '%us' AND
	word NOT LIKE '%aus'";
$db->query($query);
while( $db->next_record() ) {
	print $i.". <a href=\"../synset.php?word=".urlencode($db->f('word'))."\">".$db->f('word')."</a><br>\n";
	$i++;
}

print "<p><b>Words that begin with ex-:</b></p>";
$query = "SELECT * FROM words WHERE
	word LIKE 'ex%'";
$db->query($query);
while( $db->next_record() ) {
	print $i.". <a href=\"../synset.php?word=".urlencode($db->f('word'))."\">".$db->f('word')."</a><br>\n";
	$i++;
}

print "<p><b>Words that begin with in-:</b></p>";
$query = "SELECT * FROM words WHERE
	word LIKE 'in%' AND
	word NOT LIKE 'ins%' AND
	word NOT LIKE 'in %'";
$db->query($query);
while( $db->next_record() ) {
	print $i.". <a href=\"../synset.php?word=".urlencode($db->f('word'))."\">".$db->f('word')."</a><br>\n";
	$i++;
}

print "<p><b>Words that begin with pre-/prä (but not prei-):</b></p>";
$query = "SELECT * FROM words WHERE
	(
	word LIKE 'pre%' OR
	word LIKE 'prä%'
	)
	AND
	word NOT LIKE 'prei%'";
$db->query($query);
while( $db->next_record() ) {
	print $i.". <a href=\"../synset.php?word=".urlencode($db->f('word'))."\">".$db->f('word')."</a><br>\n";
	$i++;
}

print "<p><b>Words that begin with pro-:</b></p>";
$query = "SELECT * FROM words WHERE
	word LIKE 'pro%'";
$db->query($query);
while( $db->next_record() ) {
	print $i.". <a href=\"../synset.php?word=".urlencode($db->f('word'))."\">".$db->f('word')."</a><br>\n";
	$i++;
}

include("../include/bottom.php");
page_close();
?>
