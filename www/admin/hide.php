<?php
include("../../include/phplib/prepend.php3");
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Auth"));
include("../../include/tool.php");
$db = new DB_Thesaurus;

if( $auth->auth['uname'] != 'admin' ) {
	print "Access denied.";
	return;
}

if( ! uservar('id') ) {
	print "Error: no meaning_id";
	return;
}

### Remove (=hide) the synset:
$query = sprintf("UPDATE meanings
	SET hidden = 1
	WHERE id = %d", uservar('id'));
$db->query($query);
header("HTTP/1.0 204 No Content");
?>
