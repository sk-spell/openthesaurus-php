<?php

$matches = array();
$trans_table = array();
$queryterm = iso_encode_xml(html_entity_decode($_GET['q']));
$_GET['word'] = $queryterm;
if( $queryterm != "" ) {
	$matches = getSimilarWords($db, $queryterm, 1);
}
if( sizeof($matches) > 0 ) {
    print "    <similarterms>\n";
	asort($matches);
	$max = 5;
	$i = 0;
    while( $i < $max && list($w, $diff) = each($matches) ) {
		print "      <term term='". utf_encode_xml($w) . "' distance='" . $diff . "'/>\n";
		$i++;
	}
	print "    </similarterms>\n";
} ?>