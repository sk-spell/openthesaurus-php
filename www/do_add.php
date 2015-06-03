<?php
include("../include/phplib/prepend.php3");
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Auth"));
$auth->login_if($auth->auth["uid"] == "nobody");
include("../include/tool.php");
$db = new DB_Thesaurus;

$new_meaning_id = addSynset($db, $auth, postvar('word'),
	postvar('subject_id'), postvar('distinction'));
if( $new_meaning_id != -1 ) {
	header("Location: synset.php?id=$new_meaning_id&changed=2");
}

page_close();
?>
