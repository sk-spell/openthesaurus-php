<p class="compact"><strong><?php print _("Spellcheck suggestions:") ?></strong> (Falsche Vorschl&auml;ge? <a href="faq.php#spellcheck">Siehe FAQ</a>)</p>

<table cellspacing="0" cellpadding="0">
<tr>
<td valign="top">
<ul class="compact">
<?php
$start = getmicrotime();
$w = $_GET['word'];
$arr = spellcheck($w);

$misspelled_words = 0;
foreach ($arr as $subArray) {
	foreach ($subArray as $term) {
		$misspelled_words++;
		# this query looks strange, but it's faster than 
		# "words.word = '%s' OR words.lookup = '%s'":
		# Currently commented out because we link all words, even those that
		# are not in OpenThesaurus:
		/*$query = sprintf("
			SELECT meanings.id
			FROM word_meanings, words, meanings
			WHERE 
				words.id = word_meanings.word_id AND
				word_meanings.meaning_id = meanings.id AND
				meanings.hidden = 0 AND
				words.word = '%s'
						
			UNION 

			SELECT meanings.id
			FROM word_meanings, words, meanings
			WHERE 
				words.id = word_meanings.word_id AND
				word_meanings.meaning_id = meanings.id AND
				meanings.hidden = 0 AND
				words.lookup = '%s'",
			myaddslashes($term),
			myaddslashes($term));
		$db->query($query);
		$db->next_record();*/
		#if( $db->nf() == 0 ) {
		#	print "<li>$term</li>";
		#} else {
		if ($term == NO_SPELL_SUGGESTION) {
			print "<li>"._("unknown word, no similar words found")."</li>";
		} else {
			print "<li><a href=\"".DEFAULT_SEARCH."?word=".urlencode($term)."\">$term</a></li>";
		}
		#}
		if (sizeof($subArray) > 5 && $misspelled_words == ceil(sizeof($subArray)/2)) {
			?>
			</ul>
			</td>
			<td valign="top">
			<ul class="compact">
			<?php
		}
	}
}  
if ($misspelled_words == 0) {
	print _("<li>Word is spelled correctly</li>");
}
?>
</ul>
</td>
</tr>
</table>
<!-- TIME for spellcheck matches: <?php print (getmicrotime()-$start) ?> -->
