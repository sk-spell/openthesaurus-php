<?php
include("./include/phplib/prepend.php3");
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Auth"));
$auth->login_if($auth->auth["uid"] == "nobody");
include("./include/tool.php");
$db = new DB_Thesaurus;
$inner_db = new DB_Thesaurus;

$changed = 0;
if( uservar('do_save') == 1 ) {
	if( uservar('subject_id') == "" ) {
		$new_id = "NULL";
	} else {
		$new_id = intval(uservar('subject_id'));
	}
	$distinction_sql = "";
	if( trim(uservar('distinction')) == "" ) {
		$distinction_sql = "distinction = NULL, ";
	} else {
		$distinction_sql = "distinction = '" . escape(trim(myaddslashes(uservar('distinction')))) . "', ";
	}
	if( uservar('visible') ) {
		$new_hidden = 0;
	} else {
		$new_hidden = 1;
	}
	$query = "";
	if( $auth->auth['uname'] == 'admin' ) {
		$query = sprintf("UPDATE meanings
			SET subject_id = %s,
				%s
				hidden = %s
			WHERE
				id = %d",
				$new_id,
				$distinction_sql,
				myaddslashes($new_hidden),
				myaddslashes(uservar('mid')));
	} else {
		$query = sprintf("UPDATE meanings
			SET subject_id = %s
			WHERE
				id = %d",
				$new_id,
				myaddslashes(uservar('mid')));
	}
	$db->query($query);
	$query = sprintf("SELECT subject FROM subjects WHERE id = %d", $new_id);
	$db->query($query);
	$db->next_record();
	$new_subject = $db->f('subject');
	doLog(getSynsetString(uservar('mid'), 3), uservar('mid'), CHANGE_SUBJECT,
		uservar('oldsubject')."->".$new_subject);
	$changed = 1;
}

$query = sprintf("SELECT id, subject_id, distinction, hidden
	FROM meanings
	WHERE id = %d", myaddslashes(uservar('mid')));
$db->query($query);
if( $db->nf() == 0 ) {
	print "ID not found";
	return;
}
$db->next_record();
$subject_id = $db->f('subject_id');

$title = sprintf(_("Details for synset '%s'"), getSynsetString(uservar('mid'), 3));

function popdownlist() {
	global $db, $subject_id;
	$query = "SELECT id, subject FROM subjects ORDER By subject";
	$db->query($query);
	$i = 0;
	print '<select name="subject_id">';
	print '<option value="">'._("(none)").'</option>'."\n";
	$oldsubject = _("(none)");
	while( $db->next_record() ) {
		$checked = "";
		# specific to German OpenThesaurus:
		# these are now handled on a word-by-word basis, so ignore them here:
		if( $db->f('id') == 17 ||	# umgangssprachlich
			$db->f('id') == 16 ) {	# figurativ
			continue;
		}
		# end "specific to German OpenThesaurus"
		if( $subject_id == $db->f('id') ) {
			$checked = "selected=\"selected\"";
			$oldsubject = $db->f('subject');
		}
		print '<option '.$checked.' value="'.$db->f('id').'">'.$db->f('subject').'</option>'."\n";
		$i++;
	}
	print '</select>';
	print '<input type="hidden" name="oldsubject" value="'.$oldsubject.'"/>';
}

if ($changed) {
	$loc = sprintf("synset.php?id=%d&changed=1&rand=%d", uservar('mid'), time());
	header("Location: $loc");
	return;
}

include("./include/top.php");
?>

<form action="synset_detail.php" method="post">
<input type="hidden" name="do_save" value="1" />
<input type="hidden" name="mid" value="<?php print escape(uservar('mid')); ?>" />

<table border="0" cellpadding="4" cellspacing="0">
<tr>
	<td width="15%"><strong><?php print _("Synset:") ?></strong></td>
	<td><?php print join(', ', getSynset($db->f('id'))); ?></td>
</tr>
<?php if( isset($auth) && array_key_exists('uname', $auth->auth) && $auth->auth['uname'] == 'admin' ) {
	?>
	<tr>
		<td><strong><?php print _("Visible:") ?></strong></td>
		<td><input type="checkbox" name="visible"
			<?php if( !$db->f('hidden')) { print 'checked=checked'; } ?> /></td>
	</tr>
	<tr>
		<td><strong><?php print _("in terms of:") ?></strong></td>
		<td><input type="text" name="distinction" value="<?php print $db->f('distinction'); ?>" /></td>
	</tr>
	<?php
} ?>
<tr>
	<td valign="top"><strong><?php print _("Subject:") ?></strong></td>
	<td>
		<?php popdownlist(); ?>
	</td>
</tr>
<tr>
	<td></td>
	<td><?php print "<input type=\"submit\" value=\"" . _("Modify") . "\" />" ?></td>
</tr>
</table>

</form>

<p><a href="synset.php?id=<?php print escape(uservar('mid')) ?>"><?php print _("Back to synset") ?></a></p>

<?php
include("./include/bottom.php");
page_close();
?>
