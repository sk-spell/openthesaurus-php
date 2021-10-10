<?php
include("./include/phplib/prepend_xml.php3");
$cancel_login = 1;
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Default_Auth"));
include("./include/tool.php");
$db = new DB_Thesaurus;
$inner_db = new DB_Thesaurus;
if( ! array_key_exists('q', $_GET) ) {
	print _("Error: Invalid arguments");
	return;
} else if ( $_GET['q'] == "%" ) {
	print _("Error: Invalid arguments");
	return;
}
if( ! array_key_exists('similar', $_GET) ) {
	$similis = false;
} else if ( $_GET['similar'] == "true" ) {
	$similis = true;
} else {
    $similis = false;
}

$substrs = false;
if( array_key_exists('substring', $_GET) ) {
	if ( $_GET['substring'] == "true" ) $substrs = true;
}

$substrs_max_results = 10;
if( array_key_exists('substringMaxResults', $_GET) ) {
	if ( is_numeric($_GET['substringMaxResults']) ) $substrs_max_results = (int)$_GET['substringMaxResults'];
}

$substrs_from_results = 0;
if( array_key_exists('substringFromResults', $_GET) ) {
	if ( is_numeric($_GET['substringFromResults']) ) $substrs_from_results = (int)$_GET['substringFromResults'];
}

if( array_key_exists('mode', $_GET) ) {
	if ( $_GET['mode'] == "all" ) {
		$similis = true;
		$substers = true;
	}
}

header ("content-type: text/xml");
print "<matches>\n";
print "  <metaData>\n";
print "    <apiVersion content='0.1.3'/>\n";
print "    <warning content='WARNING -- this API is in beta -- the format may change without warning!'/>\n";
print "    <copyright content='" . COPYRIGHT . "'/>\n";
#print "    <license content='" . LICENCE . "'/>\n";
print "    <source content='" . HOMEPAGE . "'/>\n";
print "    <date content='" . date("r") . "'/>\n";
print "    <query content='" . $_GET['q'] . "'/>\n";
print "  </metaData>\n";
include("./include/synsets_api.php");
if ( $similis ) {
	include("./include/levenshtein_api.php");
}
if ( $substrs ) {
	include("./include/substring_matches_api.php");
}
print "</matches>\n";
page_close();
?>
