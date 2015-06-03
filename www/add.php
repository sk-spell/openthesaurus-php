<?php
include("../include/phplib/prepend.php3");
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Auth"));
$auth->login_if($auth->auth["uid"] == "nobody");
include("../include/tool.php");
$db = new DB_Thesaurus;

$title = _("Add another Synset");
$stop_robots = 1;
include("../include/top.php");
?>

<form action="do_add.php" method="post">

<table border="0">
<tr>
	<td valign="top"><?php print _("Word&nbsp;(<a href='faq.php#grundform'>base&nbsp;form</a>)") ?>:</td>

	<?php
	if( getvar('id') ) {
		$query = sprintf("SELECT id, word
			FROM words
			WHERE 
				id = %d", $_GET['id']);
		$db->query($query);
		$db->next_record();
		?>
		<td valign="top">
			<?php print $db->f('word'); ?>
			<input type="hidden" name="word" value="<?php print $db->f('word'); ?>">
		</td>
	<?php } else { ?>
		<td valign="top"><input size="25" maxlength="50" type="text" name="word" value="<?php print escape(uservar('word')); ?>" /></td>
		<td valign="top">
			<?php if( ! getvar('id') ) { ?>
				<?php print _("A new synset -- i.e. a new meaning -- will be started with this word.") ?>
			<?php } ?>
		</td>
	<?php } ?>
</tr>
<!--
<tr>
	<td valign="top"><?php print _("in terms of:") ?></td>
	<td valign="top"><input type="text" name="distinction" value=""></td>
	<td valign="top">
		<?php print _("Which of the possibly several meanings does this new meaning refer to?"); ?>
		<?php print _("Example: <span class='bsp'>pawn</span> can be used in terms of <span class='bsp'>chessman</span>. You only have to fill out this field if the meaning isn't clear by looking at the word's synonyms.") ?>
		</td>
</tr>
-->
<tr>
	<td valign="top"><?php print _("Subject:") ?></td>
	<td valign="top"><select name="subject_id">
			<option value=""><?php print _("(none)") ?></option>
			<?php
			$query = sprintf("SELECT id, subject
				FROM subjects
				ORDER BY subject");
			$db->query($query);
			while( $db->next_record() ) {
				# ignore these, they belong to word_meanings.use_id
				if( $db->f('id') == 16			# figurativ
					 || $db->f('id') == 17 ) {	# umgangssprachlich
					continue;
				}
				?>
				<option value="<?php print $db->f('id') ?>"><?php print $db->f('subject') ?></option>
			<?php } ?>
		</select>
	</td>
	<td><?php print _("Which subject does the synset refer to?") ?></td>
</tr>

<tr><td>&nbsp;</td></tr>

<tr>
	<td></td>
	<td><?php print "<input type=\"submit\" value=\"" . _("Send") . "\" />"; ?></td>
	<td><?php print _("Synonyms may be added on the next page") ?></td>
</tr>
</table>

</form>

<?php
include("../include/bottom.php");
page_close();
?>
