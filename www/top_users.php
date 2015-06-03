<?php
include("../include/phplib/prepend.php3");
$cancel_login = 1;
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Default_Auth"));
include("../include/tool.php");
$db = new DB_Thesaurus;

$days = 7;
$title = sprintf(_("OpenThesaurus - Most active users"), $days);
include("../include/top.php");
?>

<table border="0" cellpadding="2" cellspacing="10">
<tr>
	<td valign="top"><?php top_x($db, 15, $days); ?></td>
	<td>&nbsp;</td>
	<td valign="top"><?php top_x($db, 15, 365); ?></td>
	<td>&nbsp;</td>
	<td valign="top"><br /><br /><p><?php print _("Only users who have configured their 'visible name' are shown here. Everbody else is listed as anonymous. If you have an account and want to be listed here, make sure to set the 'visible name' in your <a href=\"prefs.php\">preferences</a>.") ?></p>
	</td>
</tr>
</table>

<?php
function top_x($db, $top_x, $days) {
	$i = 0;
	$query = sprintf("SELECT user_actions_log.user_id, visiblename, count(*) AS ct 
		FROM user_actions_log, auth_user
		WHERE
			auth_user.user_id = user_actions_log.user_id AND
			date >= DATE_SUB(NOW(), INTERVAL %d DAY)
		GROUP BY user_id
		ORDER BY ct DESC
		LIMIT %d", $days, $top_x);
	$db->query($query);
	?>
	<p><strong><?php print sprintf(_("...the last %d days:"), $days) ?></strong></p>
	<table cellspacing="0" cellpadding="2">
	<tr>
		<td><strong>#</strong></td>
		<td><strong><?php print _("Changes") ?></strong></td>
		<td><strong><?php print _("User") ?></strong></td>
	</tr>
	<?php
	while( $db->next_record() ) {
		if( ($i % 2) == 0 ) {
			$col = ' bgcolor="#eeeeee"';
		} else {
			$col = "";
		}
		$username = escape($db->f('visiblename'));
		if( $username == "" || $username == _("(anonymous)") ) {
			$username = "<span class=\"anonymous\">"._("(anonymous)")."</span>";
		}
		?>
		<tr<?php print $col; ?>>
			<td><?php print $i+1 ?>.</td>
			<td align="right"><?php print $db->f('ct') ?></td>
			<td><?php print $username ?></td>
		</tr>
		<?php
		$i++;
	}
	?>
	</table>
<?php } ?>

<br />
<?php include("../include/textads_top_users.php"); ?>

<br />

<?php
include("../include/bottom.php");
page_close();
?>
