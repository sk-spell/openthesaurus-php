<?php
include("../include/phplib/prepend.php3");
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Auth"));
$db = new DB_Thesaurus;
$db_tmp = new DB_Thesaurus;
include("../include/tool.php");

if( $auth->auth['uname'] != 'admin' ) {
	print "Access denied.";
	return;
}

$title = "OpenThesaurus admin interface: Loose synsets";
include("../include/top.php");

print "<p>Searching synsets which have a superordinate synset but
which are not below the top synset...</p>";

$query = sprintf("SELECT id FROM meanings WHERE hidden = 0");
$db->query($query);

$i = 1;
print "Checking ".$db->nf()." meanings:<br />\n";
while( $db->next_record() ) {
	$synsets = getSuperordinateSynsets($db_tmp, $db->f('id'));
	if( sizeof($synsets) > 0 ) {
		$top_id = $synsets[sizeof($synsets)-1];
		if( sizeof($synsets) > 0 && $top_id != TOP_SYNSET_ID ) {
			print "$i. <a href=\"../synset.php?id=".$db->f('id')."\">".
				join(', ', getSynset($db->f('id'), 3))."</a> is a ".
				"<a href=\"../synset.php?id=".$top_id."\">".join(', ', getSynset($top_id, 3)).
				"</a><br />\n";
			$i++;
		}
	}
}

print "<hr />\n";

page_close();
?>
