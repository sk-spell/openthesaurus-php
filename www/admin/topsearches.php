<?php
include("../include/phplib/prepend.php3");
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Auth"));
$db = new DB_Thesaurus;
include("../include/tool.php");

if( $auth->auth['uname'] != 'admin' ) {
	print "Access denied.";
	return;
}

$title = "OpenThesaurus admin interface: top searches";
include("../include/top.php");
?>

<?php
$days = 7;
if (isset($_GET['days'])) {
	$days = intval($_GET['days']);
}
$limit = 20;
if (isset($_GET['limit'])) {
	$limit = intval($_GET['limit']);
}
?>

<form action="topsearches.php" method="get">
	Show last 
		<input size="2" name="limit" value="<?php print $limit ?>" /> entries for the last
		<input size="2" name="days" value="<?php print $days ?>" /> days <input type="submit" value="Go" />
</form>

<table border="0">
<tr>
	<td align="right">#</td>
	<td>term</td>
</tr>
<?php
// find the most-searched words (case-insensitive thanks to MySQL):
$query = "SELECT submatch, term, count(term) AS ct
	FROM search_log
	WHERE
		submatch = 0 AND
		searchform = 1 AND
		date >= DATE_SUB(NOW(), INTERVAL $days DAY)
	GROUP BY term 
	ORDER BY ct desc
	LIMIT $limit";
$db->query($query);
while( $db->next_record() ) {
	?>
	<tr>
		<td align="right"><?php print $db->f('ct') ?></td>
		<td><a href="../overview.php?word=<?php 
			print urlencode($db->f('term')) ?>"><?php print escape($db->f('term')) ?></a></td>
	</tr>
	<?php
}
?>
</table>

<p><b>Most searched terms that didn't match:</b></p>

<table border="0">
<tr>
	<td align="right">#</td>
	<td>term</td>
</tr>
<?php
$query = "SELECT submatch, term, count(term) AS ct
	FROM search_log
	WHERE
		submatch = 0 AND
		matches = 0 AND
		searchform = 1 AND
		date >= DATE_SUB(NOW(), INTERVAL $days DAY)
	GROUP BY term 
	ORDER BY ct desc
	LIMIT $limit";
$db->query($query);
while( $db->next_record() ) {
	?>
	<tr>
		<td align="right"><?php print $db->f('ct') ?></td>
		<td><a href="../overview.php?word=<?php 
			print urlencode($db->f('term')) ?>"><?php print escape($db->f('term')) ?></a></td>
	</tr>
	<?php
}
?>

</table>

<p>Note: if a search result page is reloaded, this is
(somewhat incorrectly) counted as a separate search.</p>

<?php
include("../include/bottom.php");
page_close();
?>
