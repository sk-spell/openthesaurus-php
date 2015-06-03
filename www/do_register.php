<?php
include("../include/phplib/prepend.php3");
$db = new DB_Thesaurus;
include("../include/tool.php");

if( ! emailOkay(uservar('email')) ) { 
	print sprintf(_("Invalid email address '%s'."), escape(uservar('email')));
	return;
}

if( ! uservar('gpl') == "1" ) { 
	print _("Error: the checkbox must be selected");
	return;
}

# need this so mysql_real_escape_string() in tool.php won't fail
$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD) OR die(mysql_error());

$query = sprintf("SELECT user_id FROM auth_user WHERE username = '%s'",
	myaddslashes(uservar('email')));
$db->query($query);
if( $db->nf() == 1 ) {
	// username exists already
	header("Location: remind.php?email=".urlencode(uservar('email')));
	return;
}

$username = uservar('email');
$pwd = generatePassword(5);
	
$to = uservar('email');
$from = "dontreply@" . DOMAIN;
$subject = _("Registered on ") . DOMAIN;
$message = "\n".
_("You have succesfully registered on "). HOMEPAGE . 
"\n".
_("Username: ")."$username\n".
_("Password: ")."$pwd\n";

$ret = mail($to, $subject, $message, "From: $from");
if( ! $ret ) {
	print "Error: could not send mail";
	return;
}

$query = sprintf("INSERT INTO auth_user 
	(user_id, username, password, perms, subs_date, blocked)
	VALUES ('%s', '%s', '%s', 'user', '%s', 0)",
		myaddslashes(escape(uservar('email'))),
		myaddslashes(escape(uservar('email'))),
		$pwd,
		date("Y-m-d H:i:s"));
$db->query($query);

if( MAILING_LIST_SUBSCRIBE && uservar('list') == 1 ) {
	$to = MAILING_LIST_SUBSCRIBE;
	$from = uservar('email');
	$subject = "subscribe";
	$message = "";
	$ret = mail($to, $subject, $message, "From: $from");
	if( ! $ret ) {
		print "Error: could not send mailing list subscription mail";
		return;
	}
}

function generatePassword($length) { 
	// generate a random password:
	list($usec, $sec) = explode(' ', microtime());
	mt_srand((float) $sec + ((float) $usec * 100000));
	$possible_characters = "abcdefghijklmnopqrstuvwxyz1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ"; 
	$string = ""; 
	while( strlen($string) < $length ) { 
    	$string .= substr($possible_characters,
			mt_rand(0, 62) % (strlen($possible_characters)),
			1); 
	} 
	return $string;
} 

$title = _("Register");
include("../include/top.php");
?>

<p><?php print sprintf(_("Thanks for registering. The password will be sent to <span class='inp'>%s</span>."), escape(uservar('email'))) ?></p>

<p><a href="./"><?php print _("Back to homepage") ?></a></p>

<?php include("../include/bottom.php"); ?>
