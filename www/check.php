<?php
include("./include/phplib/prepend.php3");
$cancel_login = 1;
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Default_Auth"));
include("./include/tool.php");
$db = new DB_Thesaurus;

$title = _("OpenThesaurus: Check synsets");
$stop_robots = 1;
include("./include/top.php");

if (!array_key_exists('limit', $_GET)) {
	$limit = 5;
} else {
	$limit = intval($_GET['limit']);
}

?>

<?php print sprintf(_("<p>Here are %s randomly selected synonym sets. Please check if each group contains only synonyms. If it doesn't, please follow the link and fix the synset:</p>"), $limit) ?>

<div class="simplePage">

<?php
$check_new_prob = 50;
$order = "";
if( rand(1,100) <= $check_new_prob ) {
	$order = "check_count ASC, RAND()";
} else {
	$order = "RAND()";
}
$query = sprintf("SELECT id, check_count
	FROM meanings 
	WHERE hidden = 0 AND
	id NOT IN (%s)
	ORDER BY %s
	LIMIT %d", HIDDEN_SYNSETS, $order, $limit);
$db->query($query);
?>
<ul>
	<?php
	$ids = array();
	while( $db->next_record() ) {
		$synset = getSynsetWithUsage($db->f('id'), 1);
		array_push($ids, $db->f('id'));
		?>
		<li><!-- check_count: <?php print $db->f('check_count') ?> -->
			<a href="synset.php?id=<?php print $db->f('id') ?>"><?php print join(', ', $synset) ?></a></li>
	<?php
	}
	?>
</ul>

<?php
	if( isset($auth) && $auth->auth['uid'] != 'nobody' ) {
		print "<!-- UPDATing check_count -->";
		foreach( $ids as $id ) {
			$query = sprintf("UPDATE meanings
				SET check_count = check_count + 1 
				WHERE id = %d", $id);
			#print $query."<br>\n";
			$db->query($query);
		}
	}
?>

<p>
<a href="check.php?time=%s&amp;limit=5"><?php print sprintf(_("Show %d more random synsets"), 5) ?></a><br />
<a href="check.php?time=%s&amp;limit=10"><?php print sprintf(_("Show %d more random synsets"), 10) ?></a><br />
<a href="check.php?time=%s&amp;limit=15"><?php print sprintf(_("Show %d more random synsets"), 15 ) ?></a><br />
</p>

</div>

<?php 
include("./include/bottom.php"); 
page_close();
?>
