<?php

#
# Export of a thesaurus in OpenOffice.org 2.x format
#

if( ! (getenv('REMOTE_ADDR') == getenv('SERVER_ADDR')) ) {
	print "Access from your host is denied.";
	return;
}

chdir(dirname(__FILE__));
#### Configuration ###
$lang = "de_DE";
# This is the text attached to the generic terms:
$generic_term = " (Oberbegriff)";
#$sub_term = " (Unterbegriff)";		# leave empty to disable
$sub_term = "";		# leave empty to disable
$antonym_term = " (Antonym)";		# leave empty to disable

# Don't list words in the top hierarchie as generic terms, as it's usually not very
# helpful to know that e.g. a "creature" is an "entity" (use -1 to disable generic terms):
$min_depth = 4;
# FIXME -- not yet properly tested: ging -> gehen etc. (requires word_forms, word_mappings tables):
$full_forms = 0;

include("../include/phplib/prepend.php3");
$db = new DB_Thesaurus;
$db2 = new DB_Thesaurus;
include("../include/tool.php");

$swiss_spelling = 0;			# replace "ß" by "ss" (makes sense only for German)?
if (sizeof($argv) == 2 && $argv[1] == "de_CH") {
	$swiss_spelling = 1;
	$lang = "de_CH";
}	
$output_file = "../OOo2-Thesaurus/th_".$lang."_v2.dat";
$index_file = "../OOo2-Thesaurus/th_".$lang."_v2.idx";
$readme_template = "README_OOo2_template";
$readme_target = "../OOo2-Thesaurus/README_th_".$lang."_v2.txt";

function getLine($synset, $w, $comment) {
	# TODO??: avoid colloqial as first entry, otherwise "gehen" has
	# a meaning "gehen (umgangssprachlich)" which is confusing?
	$str = "-";
	foreach($synset as $s) {
		# FIXME: some words don't have synonyms, these should be ignored
		if( $s != $w || sizeof($synset) == 1 ) {
			$s = avoidPipe($s);
			if( $comment != '' ) {
				$str .= "|".unescape($s.$comment);
			} else {
				$str .= "|".unescape($s);
			}
		}
	}
	if( $str == "-" ) {
		return "";
	}
	$str = swissSpelling($str);
	return $str;
}

function avoidPipe($s) {
	if( strpos($s, "|") !== false ) {
		print "Warning: '$s': pipe symbol will be removed<br>\n";
		$s = preg_replace("/\|/", " ", $s);
	}
	return $s;
}

function cmp($a, $b) {
	$first_parts = preg_split("/\|/", $a);
	$second_parts = preg_split("/\|/", $b);
	return(strcmp($first_parts[0], $second_parts[0]));
}

function swissSpelling($word) {
	global $swiss_spelling;
	if ($swiss_spelling == 1) {
		# Seems we need to use conv because *this* file (ooo_new_export.php)
		# is in UTF-8?
		$word = preg_replace("/".iconv("latin1", "utf8", "ß")."/", "ss", $word);
	}
	return $word;
}

$title = "OpenThesaurus admin interface: Build OOo 2.0 thesaurus files";
include("../include/top.php");

print strftime("%H:%M:%S")." -- Building data...<br />\n";

$query = sprintf("SELECT words.id AS word_id, word, lookup, meaning_id, super_id, word_meanings.id AS wmid
	FROM words, word_meanings, meanings
	WHERE 
		words.id = word_meanings.word_id AND
		word_meanings.meaning_id = meanings.id AND
		meanings.hidden = 0
	ORDER BY COALESCE(lookup, word)
	");
$db->query($query);
print strftime("%H:%M:%S")." -- Step 1 done, ".$db->nf()." matches<br>"; flush();
  
$fh = fopen($output_file, 'w');
if( ! $fh ) {
	print "Error: Cannot open '$output_file' for writing.\n";
	return;
}
$encoding = "ISO8859-1";
fwrite($fh, "$encoding\n");

$occurences = array();
while( $db->next_record() ) {
	$w = $db->f('lookup');
	$w = strtolower($w);
	if( ! $w ) {
		$w = $db->f('word');
		$w = strtolower($w);
		if( ! $w ) {
			continue;
		}
	}
	$w = swissSpelling($w);
	if( array_key_exists($w, $occurences) ) {
		$occurences[$w]++;
	} else {
		$occurences[$w] = 1;
	}
	#print "$w - ".$occurences[$w]."<br>";
}

print strftime("%H:%M:%S")." -- Step 2 done.<br>"; flush();

$prev_word = "";
//$curr_word = "";
$lines = array();
$db->query($query);
$i = 0;
while( $db->next_record() ) {
	$w = $db->f('lookup');
	if( ! $w ) {
		$w = $db->f('word');
		if( ! $w ) {
			continue;
		}
	}
	$w = swissSpelling($w);
	//if ($curr_word == "" || strtolower($curr_word) != strtolower($w))
	//	$curr_word = $w;
	if( $i % 1000 == 0 ) {
		print strftime("%H:%M:%S")." -- word $i...<br>"; flush();
	}
	$i++;
	$word_id = $db->f('word_id');
	#print $db->f('meaning_id')."\n";
	$synset = getSynsetWithUsage($db->f('meaning_id'));
	$syn_line = getLine($synset, $w, '');
	$generic_line = "";
	# superordinate concepts:
	if( $min_depth != -1 ) {
		#startTimer();
		$depth = sizeof(getSuperordinateSynsets($db2, $db->f('meaning_id')));
		if( $i % 1000 == 0 ) {
			#print "getSuperordinateSynsets: ";
			#endTimer();
			#print "<br>";
		}
		if( $depth >= $min_depth ) {
			$generic_synset = getSynsetWithUsage($db->f('super_id'));
			if( sizeof($generic_synset) > 0 ) {
				$generic_line = getLine($generic_synset, $w, $generic_term);
				$generic_line = substr($generic_line, 1);	# cut off "-"
				#print $syn_line.$generic_line."<br>";
			}
		}
	}
	# Antonyme:
	$antonym_line = "";
	if ($antonym_term != "") {
		$antonym_array = getAntonym($db2, $db->f('wmid'));
		if( is_array($antonym_array) ) {
			list($antonym_mid, $antonym_word) = $antonym_array;
			$antonym_line = $antonym_word . $antonym_term;
			#print "Anto: ".$w.": ".$antonym_word." -- ".$antonym_line."<br>";
		}
	}
	if ($sub_term != "") {
		#subordinate concepts:
		$sub_line = "";
		$sub = getSubordinateSynsets($db2, $db->f('meaning_id'));
		foreach( $sub as $sub_id ) {
			$sub_synset = getSynsetWithUsage($sub_id);
			if(sizeof($sub_synset) > 0 ) {
				$sub_line = getLine($sub_synset, $w, $sub_term);
				$sub_line = substr($sub_line, 1);       # cut off "-"
				$syn_line = $syn_line.$sub_line;
			}
		}
	}

	$syn_line = $syn_line.$generic_line;
	if ($antonym_line != "") {
		$syn_line .= "|" . $antonym_line;
	}
	if( strtolower($prev_word) == strtolower($w) ) {
		if( $syn_line != "" ) {
			array_push($lines, $syn_line."\n");
		}
	} else {
		natcasesort($lines);		# there's no natural order, but make sure order is always the same
		foreach( $lines as $line ) {
			$line = maybeConvert($line);
			fwrite($fh, $line);
		}
		$occurences_count = $occurences[strtolower($w)];
		$w = avoidPipe($w);
		$line = unescape(strtolower($w))."|".$occurences_count."\n";
		$line = maybeConvert($line);
		fwrite($fh, $line);
		$lines = array();
		if( $syn_line != "" ) {
			array_push($lines, $syn_line."\n");
		}
	}
	$prev_word = $w;
}
foreach( $lines as $line ) {
	$line = maybeConvert($line);
	fwrite($fh, $line);
}

fclose($fh);
print strftime("%H:%M:%S")." -- Data saved to $output_file.<br>\n"; flush();

$lines = array();
$fh = fopen($output_file, 'r');
$content = fread($fh, filesize($output_file));
$lines = preg_split("/\n/is", $content);
fclose($fh);
   
# read thesaurus line by line
# first line of every block is an entry and meaning count
$foffset = 0 + strlen($encoding)+1; 
$i = 1;
$ne = 0;
$tindex = array();
$words_unique = array();
$warnings= 0;
$warnings_max = 100;
$warnings_max_msg = 0;
while($i < sizeof($lines)) {
	$rec = $lines[$i];
	$rl = strlen($rec) + 1;
	$parts = split("\|", $rec);
	$entry = $parts[0];
	if( ! isset($parts[1]) ) {
		$i++;
		continue;
	}
	$nm = $parts[1];
	$p = 0;
	while( $p < $nm ) {
		$i++;
		$meaning = $lines[$i];
		$rl = $rl + strlen($meaning) + 1;
		$p++;
	}       
	array_push($tindex, "$entry|$foffset");
	$words_unique[$entry] = 1;
	### Optionally also add full forms:
	if( $full_forms ) {
		$derived_words = getDerivedForms($db, $entry);
		foreach( $derived_words as $word ) {
			if( $word != $entry && substr($word, 0, 1) == substr($entry, 0, 1) ) {
				# trying to filter useless entries: different case;
				# prefixes like "un-":
				array_push($tindex, "$word|$foffset");
				if( $warnings >= $warnings_max && !$warnings_max_msg ) {
					$warnings_max_msg = 1;
					print "Warning: maximum number of warnings reached<br>\n";
				}
				#
				# FIXME: too many ambiguities -- never map a verb to
				# an adjective (abschleifen -> abgeschleift)
				#
				if( array_key_exists($word, $words_unique) && $warnings < $warnings_max ) {
					print "Warning: '$word' is ambiguous ($entry, $words_unique[$word])<br>\n";
					$warnings++;
				}
				$words_unique[$word] = $entry;
				$ne++;
			}
		}
	}
	$ne++;
	$foffset = $foffset + $rl;
	$i++;
}

# now we have all of the information
# so sort it and then output the encoding, count and index data
usort($tindex, "cmp");
$fh = fopen($index_file, 'w');
fwrite($fh, "$encoding\n");
fwrite($fh, "$ne\n");
foreach($tindex as $one) {
	fwrite($fh, "$one\n");
}
fclose($fh);
print strftime("%H:%M:%S")." -- Index saved to $index_file.<br>\n"; flush();

// Load the REAMDE template, insert current date and save in folder
// that's goin to be zipped:
$readme_fh = fopen($readme_template, 'r');
if( ! $readme_fh ) {
	print "Error: Cannot open '$readme_template' for reading.\n";
	return;
}
$readme = fread($readme_fh, filesize($readme_template));
$readme = str_replace("#YYYY-MM-DD#", date("Y-m-d"), $readme);
$readme = str_replace("#HH:MM#", date("H:i"), $readme);
$readme = str_replace("#LANG#", $lang, $readme);
fclose($readme_fh);
$readme_fh = fopen($readme_target, 'w');
if( ! $readme_fh ) {
	print "Error: Cannot open '$readme_target' for writing.\n";
	return;
}
fwrite($readme_fh, $readme);
fclose($readme_fh);

print "Calling ZIP...<br>\n";
print "<pre>";
$target = "../download/thes_".$lang."_v2.zip";
$tmp_target = "thes_".$lang."_v2.zip";
if (!chdir("../OOo2-Thesaurus")) {
	print "Error switching to ../OOo2-Thesaurus\n";
	return;
}
$zip = "/usr/bin/zip $tmp_target th_".$lang."_v2.idx th_".$lang."_v2.dat README_th_".$lang."_v2.txt";
print "$zip\n";

if( ! system($zip) ) {
	print "Error executing zip\n";
	return;
}

if( ! rename($tmp_target, $target) ) {
	print "Error renaming '$tmp_target' to '$target'\n";
}

print "</pre>";

print "<p>";
print strftime("%H:%M:%S")." -- ZIP saved as <a href=\"../$target\">OOo2-Thesaurus.zip</a></p>";

print "<hr>";

page_close();
?>
