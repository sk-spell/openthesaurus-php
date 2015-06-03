<?php
include("../include/phplib/prepend.php3");
$cancel_login = 1;
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Default_Auth"));
include("../include/tool.php");
$db = new DB_Thesaurus;

$word = trim($_GET['super_new']);
$title = sprintf(_("Synsets with '%s'"), escape($word));

include("../include/top.php");
?>

<form action="do_save.php" method="post">

<?php
while( list($key, $val) = each($_GET) ) {
	if( $key == 'super_new' ) {
		continue;
	}
	?>
	<input type="hidden" name="<?php print $key; ?>" value="<?php print $val; ?>" />
<?php } ?>

<p><?php print(sprintf(_("Please select a superordinate concept for <strong>%s</strong>:"),
	join(', ', getSynset($_GET['meaning_id'], 3)))) ?></p>

<?php
$syns = getSynset($_GET['meaning_id']);
if( array_search($word, $syns) ) {
	print sprintf(_("Error: <strong>%s</strong> cannot be a superordinate concept, as it already appears in the current synset. Please go back and try again."),
		escape($word));
} else {
	?>

	<label class="myhoverbright"><input type="radio" name="super_id" value="nothingselected" checked="checked" /><?php print _("Please select below:"); ?></label><br />
	<label class="myhoverbright"><input type="radio" name="super_id" value="create" /><?php print sprintf(_("Create a new synset containing '%s' as the first word"), escape($word)); ?></label><br />
	<input type="hidden" name="new_word" value="<?php print escape($word); ?>" />

	<?php
	$word_query = "(";
	$parts = preg_split("/,/", $word);
	$i = 0;
	$word_regexp_array = array();
	foreach( $parts as $p ) {
		if( $i > 0 ) {
			$word_query .= " OR ";
		}
		$p = trim($p);
		$word_query .= sprintf("word = '%s' OR lookup = '%s'", myaddslashes($p), myaddslashes($p));
		array_push($word_regexp_array, preg_quote($p, '/'));
		$i++;
	}
	$word_regexp = join('|', $word_regexp_array);
	$word_query .= ")";
	$query = sprintf("SELECT words.id AS word_id, word, meaning_id
		FROM words, word_meanings, meanings
		WHERE 
			$word_query AND
			words.id = word_meanings.word_id AND
			word_meanings.meaning_id = meanings.id AND
			meanings.hidden = 0 AND
			meanings.id != %d
		ORDER BY meaning_id", $_GET['meaning_id']);
	$db->query($query);
	$prev_word_id = -1;
	$prev_meaning_id = -1;
	while( $db->next_record() ) {
		if( $db->f('meaning_id') == $prev_meaning_id ) {
			# filter duplicates:
			$prev_meaning_id = $db->f('meaning_id');
			continue;
		}
		$prev_meaning_id = $db->f('meaning_id');
		$synset = getSynset($db->f('meaning_id'));
		$synset_str = join(', ', $synset);
		# TODO: \b doesn't react on "�" etc.:
		$synset_str = preg_replace("/\b($word_regexp)\b/i", "<strong>$1</strong>", $synset_str);
		?>
		<span class="myhoverbright"><label><input type="radio" name="super_id" value="<?php print $db->f('meaning_id')?>" /><?php print _("Use synset:") ?></label>
			<a href="synset.php?id=<?php print $db->f('meaning_id')?>"><?php print $synset_str ?></a></span><br />
		<?php
	}
	?>

	<br />
	<?php print '<input type="submit" value="'._("Continue").'" />'; ?>
<?php } ?>

</form>

<br />

<?php
include("../include/bottom.php");
page_close();
?>
