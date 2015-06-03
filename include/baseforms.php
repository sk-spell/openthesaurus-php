<?php
$queryterm = trim($_GET['word']);
$links = array();
if( $queryterm != "" ) {
	$gram_related = getBaseform($db, trim($_GET['word']));
	if( sizeof($gram_related) > 0 ) {
		foreach( $gram_related as $word ) {
			if( wordInDB($db, $word) && $queryterm != $word ) {
				array_push($links, '<a href="'.DEFAULT_SEARCH.'?word='.urlencode($word).'">'.$word.'</a>');
			}
		}
	}
}
if( sizeof($links) > 0 ) { ?>
	<p class="compact"><strong><?php print _("Grammatically related words (base forms):"); ?></strong></p>
	<ul class="compact">
		<?php foreach( $links as $link ) { ?>
			<li><?php print $link ?></li>
		<?php }	?>
	</ul>
<?php } ?>
