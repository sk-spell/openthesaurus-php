<?php
include("../include/phplib/prepend.php3");
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Auth"));
$db = new DB_Thesaurus;
$db2 = new DB_Thesaurus;
include("../include/tool.php");

if( $auth->auth['uname'] != 'admin' ) {
	print "Access denied.";
	return;
}

$title = "Most active users";
include("../include/top.php");
?>

<p><a href="active_users.php?limit=20">20</a>
<a href="active_users.php?limit=50">50</a>
<a href="active_users.php?limit=100">100</a></p>

<?php
$limit = 20;
if( array_key_exists('limit', $_GET) && $_GET['limit'] ) {
	$limit = intval($_GET['limit']);
}
$i = 0;
$query = sprintf("SELECT user_id, count(*) AS ct 
	FROM user_actions_log 
	GROUP BY user_id
	ORDER BY ct DESC
	LIMIT %d", $limit);
$db->query($query);
?>
<table>
<tr>
	<td><strong>#</strong></td>
	<td><strong>Changes</strong></td>
	<td><strong>Username</strong></td>
</tr>
</tr>
	<?php
	while( $db->next_record() ) {
		if( ($i % 2) == 0 ) {
			$col = ' bgcolor="#eeeeee"';
		} else {
			$col = "";
		}
		?>
		<tr<?php print $col; ?>>
			<td align="right"><?php print $i+1 ?>.</td>
			<td align="right"><?php print $db->f('ct') ?></td>
			<td><?php print $db->f('user_id') ?></td>
		<?php
		$i++;
	}
?>
</table>

<?php
include("../include/bottom.php");
page_close();
?>
