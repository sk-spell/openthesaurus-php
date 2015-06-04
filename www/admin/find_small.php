<?php
include("../include/phplib/prepend.php3");
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Auth"));
$db = new DB_Thesaurus;
include("../include/tool.php");

if( $auth->auth['uname'] != 'admin' ) {
	print "Access denied.";
	return;
}

$title = "OpenThesaurus admin interface: find small synsets";
include("../include/top.php");

#$query_limit = 8000;

$synset_size = 1;
if( array_key_exists('size', $_GET) ) {
	$synset_size = $_GET['size'];
}

print "<p>All (non-hidden) synsets that contain exactly $synset_size word(s):</p>";

$query = sprintf("SELECT id
	FROM meanings
	WHERE hidden = 0");
$db->query($query);
$i = 0;
$ids = array();
while( $db->next_record() ) {
	$ids[$db->f('id')] = 1;	# 1 is a fake value
}

$c = 0;
while( list($id, $val) = each($ids) ) {
	$query = sprintf("SELECT id
		FROM word_meanings
		WHERE word_meanings.meaning_id = %d", $id);
	#print $query."<br>";
	$db->query($query);
	$s = getSynset($id);
	if( sizeof($s) == $synset_size ) {
		$c++;
		?>
		<?php print $c; ?>.
		<a href="../synset.php?id=<?php print $id ?>"><?php 
			print join(', ', getSynset($id)) ?></a> &nbsp;
		<a href="hide.php?id=<?php print $id ?>">(hide)</a>
			<br />
		<?php
	}
}

include("../include/bottom.php");
page_close();
?>
