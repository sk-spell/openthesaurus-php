<?php
# Re-creates the memwords table used when the 
# MEMORY_TABLE option is on

if( ! (getenv('REMOTE_ADDR') == getenv('SERVER_ADDR')) ) {
	print "Access from your host is denied.";
	return;
}

include("../../include/phplib/prepend.php3");
include("../../include/tool.php");
$db = new DB_Thesaurus;

$start = getmicrotime();

print "Dropping memory DB<br/>\n";
$query = "DROP TABLE IF EXISTS memwordsTmp";
$db->query($query);

# TODO:  VARCHAR(50) isn't enough, but MySQL complains when 
# using more ("The table 'memwordsTmp' is full")
$query = "CREATE TABLE IF NOT EXISTS memwordsTmp
	(word VARCHAR(50) NOT NULL, lookup VARCHAR(50))
	ENGINE = MEMORY";
$db->query($query);

# Add a special field for use in admin/index.php so
# the admin can see when the database was last updated:
$query = sprintf("INSERT INTO memwordsTmp (word, lookup)
	VALUES ('__last_modified__', '%s')", date("Y-m-d H:i:s"));
$db->query($query);

print "Importing into mem DB...<br/>\n";

$query = sprintf("INSERT INTO memwordsTmp SELECT DISTINCT word, lookup
	FROM word_meanings, words, meanings
	WHERE 
		words.id = word_meanings.word_id AND
		word_meanings.meaning_id = meanings.id AND
		meanings.hidden = 0 AND
		meanings.id NOT IN (%s)
	ORDER BY word", HIDDEN_SYNSETS);
$db->query($query);

$query = "RENAME TABLE memwords TO memwordsBak, memwordsTmp TO memwords";
$db->query($query);

$query = "DROP TABLE memwordsBak";
$db->query($query);

print (getmicrotime()-$start)."s";

?>
