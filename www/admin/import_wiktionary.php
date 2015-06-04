<?php
error_reporting(E_ALL);

# Imports selected content from XML file like this (unpack first):
# http://download.wikimedia.org/dewiktionary/latest/dewiktionary-latest-pages-meta-current.xml.bz2

if( ! (getenv('REMOTE_ADDR') == getenv('SERVER_ADDR')) ) {
	print "Access from your host is denied.";
	return;
}

include("../include/phplib/prepend.php3");
$db = new DB_Thesaurus;
include("../include/tool.php");

$title = "OpenThesaurus admin interface: Import Wiktionary";
include("../include/top.php");

function clean($s) {
	$s = preg_replace("/&lt;!--.*?--&gt;/", "", $s);
	$s = preg_replace("/'/", "", $s);
	return trim($s);
}

function finalClean($s) {
	$s = preg_replace("/^\n+/", "", $s);
	$s = preg_replace("/\n+/", "\n", $s);
	$s = preg_replace("/&lt;!--.*?--&gt;/", "", $s);
	$s = preg_replace("/&lt;!--/", "", $s);
	$s = preg_replace("/--&gt;/", "", $s);
	return $s;
}

function storeData($title, $meanings, $synonyms) {
	global $insert_count, $db;
	$meanings_str = finalClean(join("\n", $meanings));
	$synonyms_str = finalClean(join("\n", $synonyms));
	$query = sprintf("INSERT INTO wiktionary (headword, meanings, synonyms) VALUES ('%s', '%s', '%s')",
		addslashes($title), 
		myaddslashes($meanings_str), 
		myaddslashes($synonyms_str));
	$db->query($query);
	$insert_count++;
	#print "<p>$title:<br>&nbsp;MEAN:".join(',', $meanings)."<br>";
	#print "&nbsp;SYNO:".join(',', $synonyms);
}

$handle = fopen(WIKTIONARY_XML, "r");
$i = 0;
$insert_count = 0;
$title = "";
$meanings = array();
$synonyms = array();
$state = "";
if ($handle) {
	$query = sprintf("DELETE FROM wiktionary");
	$db->query($query);
	$lang = "";
    while (!feof($handle)) {
        $buffer = fgets($handle, 4096);
        #echo "\n<br>###".$buffer;
        $pos = strpos($buffer, "<title>");
        $endpos = strpos($buffer, "</title>");
        if ($pos !== false && $endpos !== false) {
        	if (sizeof($meanings) > 0 && $lang == WIKTIONARY_LANG) {
        		if ((strpos($title, "Vorlage:") !== false || strpos($title, "Wiktionary:") !== false)) {
					#print "Ignoring(2): $title<br>";
				} else {
					storeData($title, $meanings, $synonyms);
				}
				$lang = "";
        	} elseif (sizeof($meanings) > 0 && $lang != WIKTIONARY_LANG) {
				#print "Ignoring(1): $title<br>";
        	}
        	$meanings = array();
        	$synonyms = array();
    	    $title = substr($buffer, $pos+7, $endpos-($pos+7));
    	    if (strpos($title, "MediaWiki:") !== false) {
    	    	$title = "";
    	    }
        } elseif ((strpos($buffer, "{{Bedeutungen}}") !== false || strpos($buffer, "{{Bedeutung}}") !== false) && $title != "") {
        	$state = "bedeutungen";
        } elseif (strpos($buffer, "{{Synonyme}}") !== false && $title != "") {
        	$state = "synonyme";
        } elseif (strpos($buffer, WIKTIONARY_LANG) !== false && $title != "") {
			$lang = WIKTIONARY_LANG;
        } elseif (strpos($buffer, "{{") !== false && $title != "") {
        	$state = "";
        } else {
        	if ($state == "bedeutungen") {
        		$buffer = clean($buffer);
        		array_push($meanings, $buffer);
        	} elseif ($state == "synonyme") {
        		$buffer = clean($buffer);
        		if (preg_match("/[a-zA-ZöäüßÖÄÜ]+/", $buffer)) {
        			array_push($synonyms, $buffer);
        		} else {
        			#print "empty: $buffer<br>\n";
        		}
        	}
        }
        $i++;
        if ($i % 10000 == 0) {
        	print $i."<br>";
        }
        #if ($i > 10000) {
        #	break;
        #}
    }
    fclose($handle);
}

if (sizeof($meanings) > 0) {
	storeData($title, $meanings, $synonyms);
}
print "<br />Done (inserted $insert_count headwords).";
page_close();
?>
