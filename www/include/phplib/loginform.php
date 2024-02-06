<?php
$login_page = 1;
$page = "login";
include("./include/tool.php");
$htmlcharset = "utf-8";
include("./include/top.php");
?>

<p><?php print _("You can use this form to suggest words without being logged in. These words will be checked before they are included in OpenThesaurus."); ?></p>

<form action="suggest.php" method="post">
<?php if ( array_key_exists('word', $_GET) ) { ?>
	<input type="hidden" name="word" value="<?php print escape($_GET['word']) ?>" />
<?php } ?>
<?php if ( array_key_exists('meaning_id', $_POST) ) { ?>
	<input type="hidden" name="meaning_id" value="<?php print escape($_POST['meaning_id']) ?>" />
<?php } ?>
<table border="0" bgcolor="#eeeeee" align="center" cellspacing="7" cellpadding="0">
<tr>
	<td valign="bottom"><?php print _("Synonyms (separated by comma):") ?></td>
	<td valign="bottom">
			<input type="text" name="message" size="60" maxlength="100" />
	</td>
</tr>
<tr>
	<td></td>
	<?php
	# move outside quotes so we don't confuse i18n process:
	$msg = _("Suggest Synonyms");
	?>
	<td align="right"><input type="submit" value="<?php print $msg ?>" /></td>
</tr>
</table>
</form>

<form action="<?php print $this->url() ?>" method="post" name="loginform">

<p>
<?php print _("You have to log in to directly add your corrections to our data. Don't have an account yet? <a href=\"register.php\"><strong>Sign up here.</strong></a>"); ?>
</p>
<?php
foreach ($_POST as $key => $val) {
    if ($key == 'username' || $key == 'password' || $key == 'submit') {
        continue;
    }
	print "<input type=\"hidden\" name=\"".escape($key)."\" value=\"".escape($val)."\" />\n";
}
?>

<table border="0" bgcolor="#eeeeee" align="center" cellspacing="7" cellpadding="0">
	<tr valign="top" align="left">
		<td><?php print _("Username (email):") ?></td>
		<td align="right"><input type="text" name="username"
			value="<?php 
					if( isset($this->auth["uname"]) ) {
						print $this->auth["uname"];
					} ?>"
			size="32" maxlength="64" /></td>
	</tr>
	<tr valign="top" align="left">
		<td><?php print _("Password:") ?></td>
		<td align="right"><?php print "<input type=\"password\" name=\"password\" size=\"32\" maxlength=\"32\" />" ?></td>
	</tr>
	<tr>
		<td align="right" colspan="2"><?php print _("Note: Cookies need to be enabled to log in") ?>
			&nbsp; <?php print "<input type=\"submit\" name=\"submit\" value=\"Login\" />" ?></td>
	</tr>
</table>

<!-- failed login code: -->
<?php
if (!empty($_POST['username']) ) { ?>
	<br />
	<table>
		<tr>
			<td colspan="2">
				<?php if( !array_key_exists('Thesaurus_Session', $_COOKIE) ) { ?>
					<?php print _("<p class=\"error\">Couldn't log in, please activate cookies.</p>") ?>
				<?php } else { ?>
					<?php print _("<p class=\"error\">Invalid username or password.<br />Please note: Login requires cookies.</p>") ?>
				<?php } ?>
			</td>
		</tr>
	</table>

<?php } ?>

<p><?php print _("Forgot your password? Just <a href=\"register.php\">register again</a> and the password will be emailed to you.") ?></p>

</form>


<script language="JavaScript" type="text/javascript">
<!--
if (document.forms['loginform'][0].value != '') {
	document.forms['loginform'][1].focus();
} else {
	document.forms['loginform'][0].focus();
}
// -->
</script>

<?php
#include $_SERVER['DOCUMENT_ROOT'].BASE_URL."/include/bottom.php";
include("./include/bottom.php");
?>
