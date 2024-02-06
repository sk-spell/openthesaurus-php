<?php
include("../include/phplib/prepend.php3");
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Auth"));
$db = new DB_Thesaurus;
include("../include/tool.php");

if( $auth->auth['uname'] != 'admin' ) {
	print "Access denied.";
	return;
}

function shorten($str) {
	$str = substr($str, 0, 15);
	return $str;
}

$title = "OpenThesaurus admin interface: suggest words to import";
include("../include/top.php");

if( array_key_exists('text', $_POST) ) {

	#read stopwords:
	$stopwords_file = "stopwords_de.txt";
	$fh = fopen($stopwords_file, 'r');
	$contents = fread($fh, filesize($stopwords_file));
	fclose($fh);
	$lines = preg_split("/\n/", $contents);
	$stopwords = array();
	foreach( $lines as $line ) {
		$line = strtolower(trim($line));
		$stopwords[$line] = 1;	# 1= fake value
		#print $line;
	}

	print "<h2>Unknown words</h2>";
	
	$words = array();
	$words = preg_split("/[\s,:!;\.\"\?\/\-\(\)\\\]+/", $_POST['text']);
	$words = array_unique($words);
	$i = 0;
	foreach ($words as $count => $word) {
		#$org_word = $word;
		$base_words = getBaseform($db, $word);
		if( sizeof($base_words) > 0 ) {
			$word = $base_words[0];
		}
		# We should use a case-insensitive query here, to also find 
		# words which are spelled with a capital first letter only because
		# they aoocur at the beginning of a sentence. 
		# But UPPER(...) = UPPER(...) is too slow, so use this:
		$query = sprintf("SELECT word, words.id
			FROM words, word_meanings, meanings
			WHERE (word = '%s' OR word = LOWER('%s')) AND
				word_meanings.word_id = words.id AND
				word_meanings.meaning_id = meanings.id AND
				meanings.hidden = 0", 
			myaddslashes($word), myaddslashes($word));
		$db->query($query);
		//$db->next_record();
		$word_org = $word;
		$word = strtolower($word);
		if( $db->nf() == 0 && ! array_key_exists($word, $stopwords) && 
				! ereg("^[0-9]+$", $word)) {
			$i++;
			?>
			<?php print $i ?>. <a href="../add.php?word=<?php print urlencode($word_org) ?>"><?php print
				escape($word_org) ?></a><br />
		<?php
		}
	}
	print "<br><br>$i words";

	print "<h2>Unknown word forms</h2>";

	reset($words);
	foreach ($words as $count => $word) {
		$query = sprintf("SELECT word
			FROM word_forms
			WHERE word = '%s'", 
			myaddslashes($word));
		$db->query($query);
		if( $db->nf() == 0 ) {
			print $word;
			print " ";
		} else {
			# MySQL 'select' is case-insensitive, so make sure also those words
			# are displayed as unknown that exist in the table but with different
			# upper/lowercase spelling:
			$same_case = 0;
			while ($db->next_record()) {
				if( $db->f('word') == $word ) {
					$same_case = 1;
					break;
				}
			}
			if( !$same_case ) {
				print $word;
				print " ";
			}
		}
	}
		
} else { ?>

	<form action="import.php" method="post">
	<textarea name="text" rows="20" cols="50"></textarea><br />
	<input type="submit" value="Submit text"/>
	</form>

<?php } ?>

<?php
include("../include/bottom.php");
page_close();
?>
