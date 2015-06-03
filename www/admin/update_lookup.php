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

$title = "OpenThesaurus admin interface: update 'lookup' field";
include("../../include/top.php");

?>

<?php
$query = sprintf("SELECT id, word, lookup
	FROM words
	ORDER by word");
$db->query($query);
while( $db->next_record() ) {
	$lookup = getLookupWord($db->f('word'));
	$lookup_db = $db->f('lookup');
	if( $lookup == $db->f('word') ) {
		# lookup is the same as the original word, so it should be set to NULL
		if( !is_null($lookup_db) ) {
			print "Setting lookup of '".$db->f('word')."' to NULL<br>\n";
			$query = sprintf("UPDATE words SET lookup = NULL WHERE id = %d", $db->f('id'));
			$db2->query($query);
		}
	} else {
		# lookup is NOT the same as the original word, so it should be set to getLookupWord(...):
		if( $lookup_db != $lookup ) {
			print "Setting lookup of '".$db->f('word')."' to '$lookup'<br>\n";
			$query = sprintf("UPDATE words SET lookup = '%s' WHERE id = %d",
				addslashes($lookup), $db->f('id'));
			$db2->query($query);
		}
	}
}
print "Done.\n";
?>

<p><a href="<?php print BASE_URL?>/admin/">Back to admin homepage</a></p>

<?php
include("../../include/bottom.php");
page_close();
?>
