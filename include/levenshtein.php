
<ul class="compact">
<?php	
$start = getmicrotime();
$matches = array();
$trans_table = array();
$queryterm = trim($_GET['word']);

if( $queryterm != "" ) {
	$matches = getSimilarWords($db, $queryterm, 1);
}
if( sizeof($matches) > 0 ) {
	asort($matches);
	$max = 5;
	$i = 0;
	?>
	<?php while( $i < $max && list($w, $diff) = each($matches) ) {
		?> 
		<li><a href="<?php print DEFAULT_SEARCH ?>?word=<?php print urlencode($w) ?>"><?php print $w ?></a></li>
		<?php
		$i++;
	}
	?>
<?php 
} else { ?>
	<li><?php print _("No matches") ?></li>
<?php } ?>
</ul>
<!-- TIME for levenshtein: <?php print (getmicrotime()-$start) ?> -->
