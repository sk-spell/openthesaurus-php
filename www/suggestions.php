<?php
include("./include/phplib/prepend.php3");
$cancel_login = 1;
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Default_Auth"));
include("./include/tool.php");
$db = new DB_Thesaurus;
$inner_db = new DB_Thesaurus;

if( ! array_key_exists('word', $_GET) ) {
	print "Illegal arguments";
	return;
}

$title = sprintf(_("No synonyms found for '%s'"), escape($_GET['word']));

include("./include/top.php");
?>

<?php
// =================== Search again ======================================
?>

<form action="synset.php" method="get" name="f">
	<input type="hidden" name="search" value="1" />
	<table border="0">
	<tr>
		<td><strong><?php print _("New search") ?>:</strong></td>
		<td><input type="text" size="18" name="word" value="<?php print escape($_GET['word']) ?>" /></td>
		<td><?php print "<input type='submit' value='" . _("Search") . "' />" ?></td>
	</tr>
	<tr>
		<td></td>
		<td colspan="2"><label class="myhoverbright"><input type="checkbox" name="substring" />
			<?php print _("Find substrings") ?></label></td>
	</tr>
	</table>
</form>

<?php
include("./include/baseforms.php");
?>

<?php
include("./include/levenshtein.php");
?>

<?php
include("./include/substring_matches.php");
?>

<?php if( uservar('word') && trim(uservar('word')) != "" ) { ?>
	<p><a href="add.php?word=<?php print urlencode($_GET['word'])?>"><?php print sprintf(_("Add '%s' and synonyms to the thesaurus"), escape($_GET['word'])) ?></a></p>
<?php } ?>

<script type="text/javascript">
<!--
	document.f.word.focus();
	document.f.word.select();
// -->
</script>

<?php
if( $queryterm != "" ) {
	externalSearchLinks($_GET['word']);
}
logSearch($db, $_GET['word'], 0, 0, getEndTimer());
include("./include/bottom.php");
page_close();
?>
