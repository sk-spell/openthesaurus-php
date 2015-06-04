<?php
include("./include/phplib/prepend.php3");
$cancel_login = 1;
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Default_Auth"));
include("./include/tool.php");
$title = "OpenThesaurus Suggestions";
$page = "suggest";
include("./include/top.php");
?>

<?php
$text = trim($_POST["message"]);
if( $text == "" ) {
	print "Error: No text specified\n";
	return;
}
if (strpos($text, "<") === false && strpos($text, "http:") === false) {
	# okay
} else {
	print "Error: illegal text\n";		# HTML/"http:" == spam
	return;
}
$msg = $text."\n";
$msg = HOMEPAGE."overview.php?word=".urlencode($text);
$msg .= "\n\n";
$msg .= "IP address: " . $_SERVER['REMOTE_ADDR'] . "\n";
if ( array_key_exists('word', $_POST) ) {
	$text = trim($_POST['word']);
	if( $text == "" || strlen($text) <= 1 ) {
		# this is needed to fight spam
		print "Error: No word specified or word too short\n";
		return;
	}
	$msg .= "Word: " . $_POST['word'] . "\n";
}
if ( array_key_exists('meaning_id', $_POST) ) {
	$id = intval($_POST['meaning_id']);
	$msg .= "Meaning: " .getSynsetString($id) . "\n";
	$msg .= "Meaning ID: ".HOMEPAGE."synset.php?id=" . $id . "\n";
}

#print "<pre>".$msg."</pre>";

$subj = SUGGEST_SUBJECT . ": " . preg_replace("/[\n\r]/", " ", $text);
$success = mail(SUGGEST_EMAIL, $subj, $msg);
if (!$success) {
	print "Sending mail failed, please contact the webmaster";
	return;
}
?>

<p><?php print _("Thanks for your contribution.") ?></p>

<p><a href="./">OpenThesaurus Homepage</a></p>

<?php include("./include/bottom.php"); ?>
