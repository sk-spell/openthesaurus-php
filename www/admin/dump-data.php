<?php
#
# Export of a openthesaurus database
#

if( ! (getenv('REMOTE_ADDR') == getenv('SERVER_ADDR')) ) {
	print "Access from your host is denied.";
	return;
}

# this fix if export is run by cron outside of the admin dir
chdir(dirname(__FILE__));
include("../include/phplib/prepend.php3");

$title = "OpenThesaurus admin interface: Dumping data from database";
$no_text_decoration = 1;
include("../include/top.php");
$db = new DB_Thesaurus;

//TODO
//$target = TARGET_DUMP;
$target = "../download/thesaurus_dump.sql";

print "Dumping data to '$target'...<br>\n";
flush();
$User=DB_USER;
$Password = DB_PASSWORD;
$cmd ="mysqldump -C -u  $User --password=$Password thesaurus uses subjects db_sequence meanings words word_meanings >$target";
flush();
system($cmd);
#rint "Calling '$cmd'...<br>\n";

$cmd = "gzip -f $target";
print "Calling '$cmd'...<br>\n";
system($cmd);

#unlink($target . ".gz");
print "Done. File is at <a href=\"$target.gz\">$target.gz</a><br>\n";

print "<hr />\n";
?>

<p>

<?php
#include("../include/bottom.php");
page_close();
?>
