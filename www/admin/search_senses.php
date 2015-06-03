<?php
include("../../include/phplib/prepend.php3");
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Auth"));
$db = new DB_Thesaurus;
include("../../include/tool.php");

if( $auth->auth['uname'] != 'admin' ) {
	print "Access denied.";
	return;
}

$title = "OpenThesaurus admin interface: Search senses != NULL";
include("../../include/top.php");

$i = 1;
$query = sprintf("SELECT id, distinction FROM meanings
		WHERE distinction IS NOT NULL
			AND hidden = 0
			ORDER BY id");
$db->query($query);
while( $db->next_record() ) { ?>
	<?php print $i ?>.
		<a href="../synset.php?id=<?php print $db->f('id') ?>">
		<?php print join(', ', getSynset($db->f('id'))) ?></a>
		in terms of '<?php print $db->f('distinction'); ?>'
		<br />
	<?php
	$i++;
}

include("../../include/bottom.php");
page_close();
?>
