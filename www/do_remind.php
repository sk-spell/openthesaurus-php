<?php
include("./include/phplib/prepend.php3");
$db = new DB_Thesaurus;
include("./include/tool.php");

// just to be sure, check this here, too:
if( ! emailOkay(uservar('email')) ) { 
	print "Error: invalid email address '".escape(uservar('email'))."'.";
	return;
}

# need this so mysql_real_escape_string() in tool.php won't fail
$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD) OR die(mysql_error());

$query = sprintf("SELECT password FROM auth_user WHERE username = '%s'",
	myaddslashes(uservar('email')));
$db->query($query);
if( $db->nf() == 0 ) {
	print "Error: user not found";
	return;
}
$db->next_record();
$pwd = $db->f('password');
	
$to = uservar('email');
$from = "dontreply@" . DOMAIN;
$subject = _("Password Reminder");
$message = "\n".
sprintf(_("Password reminder for %s"), HOMEPAGE)."\n\n".
_("Username: ").uservar('email')."\n".
_("Password: ")."$pwd\n";

$ret = mail($to, $subject, $message, "From: $from");
if( ! $ret ) {
	print "Error: could not send mail";
	return;
}

$title = _("Sending Password Reminder");
include("./include/top.php");
?>

<p><?php print _("A password reminder will soon be sent to your email address.") ?></p>

<p><a href="./"><?php print _("Back to homepage") ?></a></p>

<?php include("./include/bottom.php"); ?>
