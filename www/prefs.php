<?php
include("../include/phplib/prepend.php3");
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Auth"));
$auth->login_if($auth->auth["uid"] == "nobody");
include("../include/tool.php");
$db = new DB_Thesaurus;
$inner_db = new DB_Thesaurus;

$title = _("Personal Settings");
$page = "prefs";
include("../include/top.php");

$msg = "";
if( array_key_exists('change_settings', $_POST) ) {
	if( $_POST['change_settings'] == 1 && $_POST['pw1'] != $_POST['pw2'] ) {
		$msg = _("<p class=\"error\">Error: passwords don't match.</p>");
	} elseif( $_POST['change_settings'] == 1 && trim($_POST['pw1']) != "" &&
			strlen($_POST['pw1']) <= 3 ) {
		$msg = _("<p class=\"error\">Error: password is too short (minimum length is 4 characters).</p>");
	} elseif( $_POST['change_settings'] == 1 ) {
		$msg = "";
		if( trim($_POST['pw1']) != "" ) {
			$query = sprintf("UPDATE auth_user SET 
				password = '%s'
				WHERE username = '%s'
				LIMIT 1", $_POST['pw1'], $auth->auth['uname']);
			$db->query($query);
			$msg .= _("<p class=\"okay\">New password has been set and will become active for the next login.</p>");
		}
		if( trim($_POST['visiblename']) != $_POST['visiblename_old'] ) {
			$query = sprintf("UPDATE auth_user SET 
				visiblename = '%s'
				WHERE username = '%s'
				LIMIT 1", trim($_POST['visiblename']), $auth->auth['uname']);
			$db->query($query);
			$msg .= _("<p class=\"okay\">New visible name has been set.</p>");
		}
		if( $msg == "" ) {
			$msg .= _("<p class=\"error\">No changes made.</p>");
		}		
	}
}

$query = sprintf("SELECT user_id, visiblename FROM auth_user
	WHERE username = '%s'", $auth->auth['uname']);
$db->query($query);
$db->next_record();

?>

<?php if( $msg ) { ?>
	<?php print $msg; ?>
<?php } ?>

<form action="prefs.php" method="post">
<input type="hidden" name="change_settings" value="1" />

<table border="0">
<tr>
	<td valign="top"><?php print _("Login:") ?></td>
	<td valign="top"><span class="naviusername"><?php print escape($db->f("user_id")) ?></span></td>
</tr>
<tr>
	<td valign="top"><?php print _("New password:") ?></td>
	<td valign="top"><input type="password" name="pw1" /></td>
	<td valign="top"><?php print _("Leave empty to keep your current password.") ?></td>
</tr>
<tr>
	<td valign="top"><?php print _("New password (again):") ?></td>
	<td valign="top"><input type="password" name="pw2" /></td>
</tr>
<tr>
	<td valign="top"><?php print _("Visible name:") ?></td>
	<td valign="top"><input type="text" name="visiblename" value="<?php print escape($db->f("visiblename")) ?>" />
		<input type="hidden" name="visiblename_old" value="<?php print escape($db->f("visiblename")) ?>" /></td>
	<td valign="top"><?php print _("This name will appear in the statistics that are publicly visible. Leave empty if you want to stay anonymous.") ?></td>
</tr>
<tr>
	<td></td>
	<td><?php print "<input type=\"submit\" value=\"" . _("Change") . "\" />" ?></td>
</tr>
</table>
</form>


<h2><?php print _("Personal Statistics") ?></h2>

<table border="0">
<tr>
	<td><?php print _("Words and synsets added:") ?></td>
	<td>
		<?php
		$query = sprintf("SELECT count(*) AS ct FROM user_actions_log
			WHERE 
			(type = '%s' OR type = '%s' OR type = 'h') AND
			user_id = '%s'", ADD_WORD, ADD_SYNSET,
			myaddslashes($auth->auth['uname']));
		$db->query($query);
		$db->next_record();
		print $db->f('ct');
		?>
	</td>
</tr>
<tr>
	<td><?php print _("Words and synsets removed:") ?></td>
	<td>
		<?php
		$query = sprintf("SELECT count(*) AS ct FROM user_actions_log
			WHERE 
			(type = '%s' OR type = '%s') AND
			user_id = '%s'",
			REMOVE_SYNONYM, REMOVE_SYNSET, myaddslashes($auth->auth['uname']));
		$db->query($query);
		$db->next_record();
		print $db->f('ct');
		?>
	</td>
</tr>
</table>

<?php
include("../include/bottom.php");
page_close();
?>
