<?php 
$start = getmicrotime();
if( ! uservar('substring') && $queryterm != "" ) {
	$user_word = escape(uservar('word'));
	$limit = 10;
	$results = getSubstringMatches($db, trim($_GET['word']), $limit);
	print "<p class=\"compact\"><strong>"._("Substring matches in OpenThesaurus:")."</strong></p>";
	print "<ul class=\"compact\">";
	$substr_matches = 0;
	if( sizeof($results) > 0 ) {
		$i = 0;
		$more_matches = 0;
		foreach( $results as $word ) {
			if( $i >= $limit-1 ) {
				$more_matches = 1;
				break;
			}
			$i++;
			$w = uservar('word');
			if (strtolower($w) == strtolower($word)) {
				continue;
			}
			$w_regex = preg_quote(uservar('word'), '/');
			$w = preg_replace("/($w_regex)/i", "<strong>$1</strong>", $word);
			$substr_matches++;
			?>
			<li><a href="<?php print DEFAULT_SEARCH ?>?word=<?php print urlencode($word)?>"><?php print $w ?></a></li>
			<?php
		}
		if( $more_matches ) { ?>
			<li style="list-style: none"><a href="substring_search.php?word=<?php print urlencode($_GET['word']) ?>"><?php print _("&gt;&gt; more substring matches") ?></a></li>
			<?php
		}
	}
	if ( $substr_matches == 0 ) {
		print "<li>"._("No substring matches")."</li>";
	}
	print "</ul>";
}
?>
<!-- TIME for substring matches: <?php print (getmicrotime()-$start) ?> -->
