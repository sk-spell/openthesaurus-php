<?php
	$user_word = iso_encode_xml(html_entity_decode($_GET['q']));
	$limit = $substrs_max_results + $substrs_from_results;
	$results = getSubstringMatches($db, iso_encode_xml(html_entity_decode($_GET['q'])), $limit);
	$substr_matches = 0;
	if( sizeof($results) > 0 ) {
		print "    <substringterms>\n";
		$i = 0;
		$more_matches = 0;
		foreach( $results as $word ) {
			if( $substr_matches >= $limit + $substrs_from_results ) {
				break;
			}
			$i++;
			$w = $user_word;
			if (strtolower($w) == strtolower($word)) {
				continue;
			}
			$w_regex = preg_quote(uservar('word'), '/');
			$w = preg_replace("/($w_regex)/i", "<strong>$1</strong>", $word);
			$substr_matches++;
			if ( $i > $substrs_from_results ) print "      <term term=\"" . utf_encode_xml($word) . "\"/>\n";
		}
		print "    </substringterms>\n";
	}
?>
