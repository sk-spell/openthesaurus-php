<?php

// NOTE: this file is UTF-8!

$start = getmicrotime();
$queryterm = trim($_GET['word']);
$links = array();

function wiktionaryClean($s) {
	$s = preg_replace("/:(\[[\d,]+\])/", "<span class=\"wiktionarymeaning\">$1</span>", $s);
	$s = preg_replace("/\[\[/", "", $s);
	$s = preg_replace("/\]\]/", "", $s);
	$s = preg_replace("/&lt;sup&gt;(.*)&lt;\/sup&gt;/", "<span class=\"wiktionarymeaningref\">$1</span>", $s);
	return $s;
}

$match = 0;
if( $queryterm != "" ) {
	$query = sprintf("SELECT headword, meanings, synonyms FROM wiktionary WHERE headword = '%s'",
		myaddslashes($queryterm));
		//myaddslashes(iconv("latin1", "utf8", $queryterm)));
	$db->query($query);
	$match = $db->next_record();
	$wikiword = $db->f('headword');
	$wikilink = "http://sk.wiktionary.org/w/index.php?title=".urlencode($wikiword);
	$wikilink_history = "http://sk.wiktionary.org/w/index.php?title=".urlencode($wikiword)."&amp;action=history";
	$wikilink_edit = "http://sk.wiktionary.org/w/index.php?title=".urlencode($wikiword)."&amp;action=edit";
	#$wikiword = iconv("utf8", "latin1", $wikiword);
	if (!$match) {
		$wikilink = "http://sk.wiktionary.org/";
	}
	?>
	<p class="compact"><strong><a href="http://sk.wiktionary.org"><?php print _("Wiktionary") ?></a></strong>:</p>
	<ul class="compact">
	<?php
	if ($match) {
		$meanings = wiktionaryClean($db->f('meanings'));
		$synonyms = wiktionaryClean($db->f('synonyms'));
		# FIXME: needed to display special characters, find a proper solution for this!
		$meanings = preg_replace("/[„“]/", "'", $meanings);
		#$meanings = iconv("utf8", "latin1", $meanings);
		#$synonyms = iconv("utf8", "latin1", $synonyms);
		# end fixme
		if ($synonyms == "") {
			$synonyms = _("(none)");
		}
		?>
		<li><strong><?php print _("Meanings:") ?></strong> <?php print $meanings ?></li>
		<li><strong><?php print _("Synonyms:") ?></strong> <?php print $synonyms ?></li>
		<?php
	} else {
		print "<li>"._("No matches")."</li>";
	}
}
?>

<?php if ($match) { ?>
	<li class="wiktionarylicense"><?php print _("Source: ") ?>
	<a href="<?php print $wikilink ?>" class="wikilicenselink"><?php print $wikiword ?></a>
	<?php print _("Licence: ") ?><a href="wiktionary/fdl.txt"
	class="wikilicenselink"><?php print _("The GNU Free Documentation License") ?></a>,
	<a href="<?php print $wikilink_history ?>" class="wikilicenselink"><?php print _("Version/Authors") ?></a>
<?php } ?>

</ul>
<!-- TIME for wiktionary matches: <?php print (getmicrotime()-$start) ?> -->
