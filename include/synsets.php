<?php
$start = getmicrotime();
$word = $_GET['word'];
?>

<?php
$query = sprintf("
	SELECT words.id AS word_id, word, meaning_id, super_id
	FROM words, word_meanings, meanings
	WHERE 
		word = '%s' AND
		words.id = word_meanings.word_id AND
		word_meanings.meaning_id = meanings.id AND
		meanings.hidden = 0 AND
		meanings.id NOT IN (%s)
	
	UNION

	SELECT words.id AS word_id, word, meaning_id, super_id
	FROM words, word_meanings, meanings
	WHERE 
		lookup = '%s' AND
		words.id = word_meanings.word_id AND
		word_meanings.meaning_id = meanings.id AND
		meanings.hidden = 0 AND
		meanings.id NOT IN (%s)

	ORDER BY word", myaddslashes($word), HIDDEN_SYNSETS,
		myaddslashes($word), HIDDEN_SYNSETS);
$db->query($query);

$word_ids = array();
$words = array();
$prev_word_id = -1;
$prev_mid = -1;
$synmatches = 0;
if ($db->nf() == 0) {
	?>
	<p class="firstcompact"><strong><?php print _("No exact matches in OpenThesaurus. Did you mean...") ?></strong></p>
	<?php
} else {
	?>
	<p class="firstcompact"><strong><?php print _("Synsets:") ?></strong></p>
	<ul class="compact">
	<?php
}
while( $db->next_record() ) {
	$mid = $db->f('meaning_id');
	if ($mid == $prev_mid) {
		continue;
	}
	$prev_mid = $mid;
	$word_id = $db->f('word_id');
	if( $word_id != $prev_word_id ) {
		array_push($word_ids, $word_id);
		array_push($words, $db->f('word'));
	}
	$prev_word_id = $word_id;
	$synset = getSynsetWithUsage($db->f('meaning_id'), 1);
	$subject = getSubject($db->f('meaning_id'));
	$subject_str = "";
	if ($subject != "") {
		$subject_str = "[".$subject."]";
	}
	$synset_str = join(', ', $synset);
	$word_regexp = preg_quote($word, '/');
	# \b doesn't react on German special characters etc. so we use
	# "[\s,!?.]" as a workaround - we first needs to add whitespace
	# because the workaround wouldn't match start and end:
	$synset_str = " ".$synset_str." ";
	$synset_str = preg_replace("/([\s,!?.])($word_regexp)([\s,!?.])/i", "$1<strong>$2</strong>$3", $synset_str);
	$synset_str = trim($synset_str);
	$url_suffix = "";
	if( uservar('mode') == 'wordrandom' ) {
		$url_suffix = "&amp;mode=wordrandom";
	}
	$accesskey = "";
	$synmatches++;
	if( $synmatches < 10 ) {
		$accesskey = "accesskey=\"$synmatches\"";
	}
	?>
	<li class="synsetlist"><a <?php print $accesskey; ?> href="synset.php?id=<?php print $db->f('meaning_id')?><?php 
		print $url_suffix ?>"><?php print $synset_str." ".$subject_str ?></a>
		<?php if (SUPERSETS_IN_OVERVIEW && $db->f('super_id')) { ?>
			<br />
			<span class="supersynsethead"><?php print _("Superordinate synset") ?>:</span>
				<span class="supersynset"><?php print join(getSynsetWithUsage($db->f('super_id'), 1, 3), ", ") ?></span>
		<?php } ?>
		</li>
	<?php
}
?>
<?php if ($db->nf() > 0) { ?>
</ul>
<?php } ?>
<!-- TIME for synset matches: <?php print (getmicrotime()-$start) ?> -->
