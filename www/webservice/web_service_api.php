<?php
/* web_service_api.php */

include("../include/phplib/prepend.php3");
include("../include/tool.php");
error_reporting(E_ERROR);		# fails otherwise

$xmlrpc_methods = array();
$xmlrpc_methods['openthesaurus.searchSynonyms'] = 'openthesaurus_searchSynonyms';
$xmlrpc_methods['method_not_found'] = 'XMLRPC_method_not_found';

function openthesaurus_searchSynonyms($query) {
	$start = getmicrotime();
	$items = array();

# FIXME: myaddslashes uses mysql_real_escape_string which needs a 
# MySQL connection, won't work otherwise!

	$query_str = sprintf("
		SELECT words.id AS word_id, word, meaning_id
		FROM words, word_meanings, meanings
		WHERE 
			word = '%s' AND
			words.id = word_meanings.word_id AND
			word_meanings.meaning_id = meanings.id AND
			meanings.hidden = 0
	
		UNION

		SELECT words.id AS word_id, word, meaning_id
		FROM words, word_meanings, meanings
		WHERE 
			lookup = '%s' AND
			words.id = word_meanings.word_id AND
			word_meanings.meaning_id = meanings.id AND
			meanings.hidden = 0

		ORDER BY word",
			myaddslashes($query), myaddslashes($query),
			myaddslashes($query), myaddslashes($query));

	$db = new DB_Thesaurus;
	$db->query($query_str);

	$synmatches = 1;
	if ($db->nf() == 0) {
		$item['words'] = array();
		$items[] = $item;
	}
	while( $db->next_record() ) {
		$mid = $db->f('meaning_id');
		$item['words'] = getSynsetWithUsage($db->f('meaning_id'), 1);
		$items[] = $item;
	}

	$_GET['search'] = 1;	# otherwise logSearch ignores the search
	logSearch($db, $query, $db->nf(), 0, getEndTimer(), 1);
	XMLRPC_response(XMLRPC_prepare($items), KD_XMLRPC_USERAGENT);
}

function XMLRPC_method_not_found($methodName){
    XMLRPC_error("2", "The method you requested, " . $methodName
        . ", was not found.", KD_XMLRPC_USERAGENT);
}

?>
