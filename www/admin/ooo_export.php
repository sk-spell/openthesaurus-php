<?php

#
# Export of a thesaurus in OpenOffice.org 1.x format
#

if( ! (getenv('REMOTE_ADDR') == getenv('SERVER_ADDR')) ) {
	print "Access from your host is denied.";
	return;
}

#### Configuration ###
$lang = "de_DE";

include("../include/phplib/prepend.php3");
$db = new DB_Thesaurus;
$db_temp = new DB_Thesaurus;
include("../include/tool.php");

# NOTE: requires an index on word_meanings.meaning_id, otherwise it's slow!!

$title = "OpenThesaurus admin interface: Build OOo 1.x thesaurus files";
include("../include/top.php");

$wordfile = "wordlist.txt";
$thesfile = "trimthes.txt";

print "Building data...<br />\n";

// only top10.000 words:		words.word IN (%s) AND
$query = sprintf("SELECT words.id AS word_id, word, meaning_id
	FROM words, word_meanings, meanings
	WHERE 
		words.id = word_meanings.word_id AND
		word_meanings.meaning_id = meanings.id AND
		meanings.hidden = 0
	ORDER BY BINARY(word)
");
$db->query($query);

$words = array();
#$words["-"] = 0;
$prev_word = "";
$l = array();
$last_id = 0;
while( $db->next_record() ) {
	if( $prev_word != $db->f('word') ) {
		if( $prev_word ) {
			$prev_word_lookup = getLookupWord($prev_word);
			if( $prev_word_lookup != $prev_word ) {
				if( array_key_exists($prev_word_lookup, $words) ) {
					$words[$prev_word_lookup] = $words[$prev_word_lookup].",".join(',', $l);
				} else {
					$words[$prev_word_lookup] = join(',', $l);
				}
			}
			if( array_key_exists($prev_word, $words) ) {
				$words[$prev_word] = $words[$prev_word].",".join(',', $l);
			} else {
				$words[$prev_word] = join(',', $l);
			}
		}
		$l = array();
		array_push($l, $db->f('meaning_id'));
	} else {	
		array_push($l, $db->f('meaning_id'));
	}
	$last_id = $db->f('meaning_id');
	$prev_word = $db->f('word');
}

array_push($l, $last_id);
$words[$prev_word] = join(',', $l);

$wordlist = array();
$thes = array();
while( list($key, $val) = each($words) ) {
	$query = sprintf("SELECT DISTINCT word, word_id, word_meanings.id AS wmid
		FROM word_meanings, words
		WHERE 
			meaning_id IN (%s) AND
			words.id = word_meanings.word_id", $val);
	$db->query($query);
	$table = array();
	while( $db->next_record() ) {
		$query = sprintf("SELECT uses.name, word_meanings.id
			FROM word_meanings, uses
			WHERE word_meanings.id = %d AND
				uses.id = word_meanings.use_id", $db->f('wmid'));
		$db_temp->query($query);
		$db_temp->next_record();
		$w = $db->f('word');
		if( $db_temp->f('name') ) {
			$w .= " (".$db_temp->f('name').")";		# "use" information
			array_push($wordlist, trim($w));
		}
		$table[$w] = 1; 		# 1 =fake value
	}
	$key_org = $key;
	$key = preg_replace("/,/", ";", $key);
	if( !(strpos($key_org, ",") === false) ) {
		print "Replacing $key_org by $key<br>\n";
	}
	$thes[$key] = array_keys($table);
}

$ct = 0;
$fh = fopen($thesfile, 'w');
if( ! $fh ) {
	print "Error: Cannot open '$thesfile' for writing.\n";
	return;
}
while( list($word, $synlist) = each($thes) ) {
	$l = array();
	foreach( $synlist as $t ) {
		$t = preg_replace("/,/", ";", $t);
		array_push($l, $t);
	}
	$syns = join(',', unescape($l));
	$first_word_org = unescape($word);
	$first_word = unescape($word).",";
	array_push($wordlist, trim($word));
	$line = $first_word.$syns;
	$line = maybeConvert($line);
	fwrite($fh, $line."\n");
	# also add derived forms. commented out because of the 32,000 word limit:
	#print "* ".$first_word.$syns."<br>\n";
	#$regex = "";
	#if( preg_match("/^[a-z]/", $first_word_org) ) {
	#	// word starts with a lower-case letter, so only
	#	// use derived forms that also start with a lower-case
	#	// character (useful only(?) for German):
	#	$regex = "/^[a-z]/";
	#} else {
	#	$regex = "/^[A-Z]/";
	#}
	#foreach( getDerivedForms($db, $first_word_org) as $form ) {
	#	if( preg_match($regex, $form) ) {
	#		# FIXME: nicht doppelt (z.B. behindern -> behindert, steht
	#		# auch so schon drin??!)
	#		fwrite($fh, $form.",".$syns."\n");
	#		array_push($wordlist, $form);
	#		#print "&nbsp;+ ".$form.",".$syns."<br>\n";
	#	}
	#}
	$ct++;
}
fclose($fh);

# Wordlist:
sort($wordlist);
$fh = fopen($wordfile, 'w');
if( ! $fh ) {
	print "Error: Cannot open '$wordfile' for writing.\n";
	return;
}
#fwrite($fh, "-\n");
$prev_word = "";
foreach( $wordlist as $word ) {
	if( $word != $prev_word ) {		# no duplicates
		# TODO:
		$word = preg_replace("/,/", ";", $word);
		$line = unescape($word);
		$line = maybeConvert($line);
		fwrite($fh, $line."\n");
	}
	$prev_word = $word;
}
fclose($fh);

print "Data saved to $wordfile and $thesfile.<br>\n";

$cmd = "/usr/bin/awk -f Parse_Thes.awk";
print "Calling '<tt>$cmd</tt>'...<br>\n";
print "<pre>";
if( ! system($cmd) ) {
	#print "FAIL?<br>";
}
print "</pre>";

$cmd = "mv ../OOo-Thesaurus/th_temp.dat ../OOo-Thesaurus/th_" .$lang. ".dat";
print "Calling '<tt>$cmd</tt>'...<br>\n";
if( ! system($cmd) ) {
	#print "FAIL?<br>";
}

$cmd = "mv ../OOo-Thesaurus/th_temp.idx ../OOo-Thesaurus/th_" .$lang. ".idx";
print "Calling '<tt>$cmd</tt>'...<br>\n";
if( ! system($cmd) ) {
	#print "FAIL?<br>";
}

// Load the REAMDE template, insert current date and save in folder
// that's goin to be zipped:
$readme_template = "README_OOo_template";
$readme_target = "../OOo-Thesaurus/README_th_".$lang.".txt";
$readme_fh = fopen($readme_template, 'r');
if( ! $readme_fh ) {
	print "Error: Cannot open '$readme_template' for reading.\n";
	return;
}
$readme = fread($readme_fh, filesize($readme_template));
$readme = str_replace("YYYY-MM-DD", date("Y-m-d"), $readme);
$readme = str_replace("HH:MM", date("H:i"), $readme);
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
$target = "download/OOo-Thesaurus-snapshot.zip";
$zip = "cd .. && /usr/bin/zip $target OOo-Thesaurus/th_".$lang.".* OOo-Thesaurus/README_th_".$lang.".txt";
print "$zip\n";

if( ! system($zip) ) {
	print "Error executing zip\n";
	return;
}

print "</pre>";

print "<p>ZIP saved as <a href=\"../$target\">OOo-Thesaurus.zip</a></p>";

print "<hr>";

page_close();
?>
