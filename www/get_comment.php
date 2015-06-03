<?php
include("../include/phplib/prepend.php3");
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Auth"));
$auth->login_if($auth->auth["uid"] == "nobody");
include("../include/tool.php");
$db = new DB_Thesaurus;
$page = "get_delete_comment";

$title = _("Comment on the deletion");
include("../include/top.php");
?>

<form action="do_save.php" method="post" name="commentform">
<input type="hidden" name="do_remove" value="1" />
<input type="hidden" name="meaning_id" value="<?php print intval(uservar('meaning_id'))?>" />

<table cellpadding="0" cellspacing="2" border="0">
<tr>
	<td colspan="2"><?php print sprintf(_("Please add a short comment that explains why the synset <span class='inp'>%s</span> can be deleted:"), join(', ', getSynset(uservar('meaning_id')))) ?></td>
</tr>
<tr><td>&nbsp;</td></tr>
<tr>
	<td><?php print _("Short comment:") ?></td>
	<td><input size="50" type="text" name="comment" value="" /></td>
</tr>
<tr>
	<td></td>
	<td align="right"><?php print "<input type=\"submit\" value=\""._("Send")."\" />" ?></td>
</tr> 
</table>

</form>

<script language="JavaScript" type="text/javascript">
<!--
document.forms['commentform']['comment'].focus();
// -->
</script>

<?php
include("../include/bottom.php");
page_close();
?>
