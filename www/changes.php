<?php
include("./include/phplib/prepend.php3");
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Auth"));
$db = new DB_Thesaurus;
include("./include/tool.php");

function shorten($str, $length=15) {
	$str = substr($str, 0, $length);
	return $str;
}

$title = _("Recent changes in the thesaurus");
$no_text_decoration = 1;
include("./include/top.php");
?>

<?php
$actions_limit = 30;
if( array_key_exists('actions_limit', $_GET) && $_GET['actions_limit'] ) {
	$actions_limit = $_GET['actions_limit'];
}
if( $actions_limit > 500 ) {
	$actions_limit = 500;
}
?>
<strong><?php print _("Recent changes");?>: <a href="changes.php?actions_limit=30">30</a>,
	<a href="changes.php?actions_limit=100">100</a>,
	<a href="changes.php?actions_limit=250">250</a></strong><br />

	<table>
	<tr><td>&nbsp;</td></tr>
	<?php
	$query = sprintf("SELECT id, user_actions_log.user_id, visiblename, date, word, synset, synset_id, type, comment
		FROM user_actions_log, auth_user
		WHERE
			auth_user.user_id = user_actions_log.user_id
			ORDER BY date DESC
			LIMIT %d", $actions_limit);
	$db->query($query);
	$prev_user = "_start";
	$prev_date = "_start";
	while( $db->next_record() ) {
		?>
		<?php if( $db->f('user_id') != $prev_user && $prev_user != "_start" ) { ?>
		<tr>
			<td colspan="3"><hr size="1" /></td>
		</tr>
		<?php }
		?>
		<tr>
			<?php if( $db->f('date') == $prev_date && $db->f('user_id') == $prev_user ) { ?>
				<td></td>
				<td></td>
			<?php } else { ?>
				<td valign="top"><?php
						$date = str_replace(" ", "&nbsp;", $db->f('date'));
						print $date;
					?></td>
				<td valign="top"><?php
        $username = escape($db->f('visiblename'));
		    if( $username == "" || $username == _("(anonymous)") ) {
			  $username = "<span class=\"anonymous\">"._("(anonymous)")."</span>";
			  }
        print $username ?>
        </td>
			<?php } ?>
			<?php
				$date = $db->f('date');
				$prev_date = $db->f('date');
				$prev_user = $db->f('user_id');
				$date = str_replace(" ", "&nbsp;", $db->f('date'));
				$comment = $db->f('comment');
				if( ! $comment ) {
					$comment = _("[none]");
				}
			?>
			<td valign="top">
				<?php
				$msg = "";
				$msg = getChangeEntry($db->f('type'), $db->f('word'),
					$db->f('synset_id'), $db->f('synset'), $comment, "../", 1);
				// TODO: assoziationen
				print $msg;
				?>
			</td>
		</tr>
	<?php } ?>
	</table>

<br />

<?php
include("./include/bottom.php");
page_close();
?>
