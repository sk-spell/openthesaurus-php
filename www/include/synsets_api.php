<?php

function utf_encode_xml($w)
{
  $iso = array("è", "¹", "¾", "È", "©", "®");
  $utf = array("&#x10d;", "&#x161;", "&#x17e;", "&#x10c;", "&#x160;", "&#x17d;");
  if (UTF8_DATABASE) {
    $w = str_replace($iso, $utf, $w);
  }
  return $w;
}

function iso_encode_xml($w)
{
  $iso = array("è", "¹", "¾", "È", "©", "®");
  $utf = array("&#x10d;", "&#x161;", "&#x17e;", "&#x10c;", "&#x160;", "&#x17d;");
  if (UTF8_DATABASE) {
    $w = str_replace($utf, $iso, $w);
  }
  $utf = array("Ä", "Å¡", "Å¾", "ÄŒ", "Å ", "Å½");
  if (UTF8_DATABASE) {
    $w = str_replace($utf, $iso, $w);
  }
  $utf = array(chr(232),chr(154),chr(158),chr(200),chr(138),chr(142));
  if (UTF8_DATABASE) {
    $w = str_replace($utf, $iso, $w);
  }
  return $w;
}

$word = iso_encode_xml(html_entity_decode($_GET['q']));

$query = sprintf("
	SELECT words.id AS word_id, word, meaning_id, super_id
	FROM words, word_meanings, meanings
	WHERE 
		word = '%s' AND
		words.id = word_meanings.word_id AND
		word_meanings.meaning_id = meanings.id AND
		meanings.hidden = 0
	
	UNION

	SELECT words.id AS word_id, word, meaning_id, super_id
	FROM words, word_meanings, meanings
	WHERE 
		lookup = '%s' AND
		words.id = word_meanings.word_id AND
		word_meanings.meaning_id = meanings.id AND
		meanings.hidden = 0

	ORDER BY word", myaddslashes($word), myaddslashes($word), myaddslashes($word), myaddslashes($word));
$db->query($query);
$word_ids = array();
$words = array();
$prev_word_id = -1;
$prev_mid = -1;
$synmatches = 0;
if ($db->nf() == 0) {
} else {
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
		$subject_str = $subject;
	}
	$synset_str = join("'/>\n      <term term='", $synset);
	$word_regexp = preg_quote($word, '/');
	$url_suffix = "";
	if( uservar('mode') == 'wordrandom' ) {
		$url_suffix = "&amp;mode=wordrandom";
	}
	$accesskey = "";
	$synmatches++;
	if( $synmatches < 10 ) {
		$accesskey = "accesskey=\"$synmatches\"";
	}
    print "  <synset id='" . $db->f('meaning_id') . "'>\n";
    if ($subject_str != "")
	    print "    <categories>\n      <category name='" . utf_encode_xml($subject_str) . "'/>\n    </categories>\n";
	else
		print "      <categories/>\n";
 	print "      <term term='" . utf_encode_xml($synset_str) . "'/>\n    </synset>\n";
}?>
