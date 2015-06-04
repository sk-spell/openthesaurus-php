<?php
include("./include/phplib/prepend.php3");
$cancel_login = 1;
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Default_Auth"));
include("./include/tool.php");
$db = new DB_Thesaurus;

$word = $_GET['word'];
$title = sprintf(_("Matches for '%s'"), escape($word));

$stop_robots = 1;
include("./include/top.php");
?>

<?php include("./include/synsets.php"); ?>

<?php
if( uservar('word') ) {
	$i = 0;
	foreach( $word_ids as $word_id ) { ?>
		<a href="add.php?id=<?php print $word_id; ?>"><?php print sprintf(_("Add another meaning of '%s' to the thesaurus"), escape($words[$i])) ?></a><br />
		<?php 
		$i++;
	}
	if( ! in_array($word, $words) ) { ?>
		<a href="add.php?word=<?php print urlencode($_GET['word'])?>"><?php print sprintf(_("Add '%s' and synonyms to the thesaurus"), escape($_GET['word'])) ?></a>
	<?php }
} ?>

<br />
<?php
include("./include/external_searches.php");

logSearch($db, $word, $db->nf(), 0, getEndTimer());

include("./include/bottom.php");
page_close();
?>
