<?php
include("./include/phplib/prepend.php3");
$cancel_login = 1;
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Default_Auth"));
include("./include/tool.php");
$db = new DB_Thesaurus;

$from = 0;
if ( array_key_exists('from', $_GET) ) {
	$from = intval($_GET['from']);
}
$limit = 20;
$results = getSubstringMatches($db, trim($_GET['word']), $limit, $from);
$totalcount = getSubstringMatchesCount($db, trim($_GET['word']));
$to = $from + $limit;
if( $to > $totalcount ) {
	$to = $totalcount;
}

$title = sprintf(_("No Substring  Matches for '%s'"), escape($_GET['word']));
if( sizeof($results) > 0 ) {
	$title = sprintf(_("Substring Matches for '%s'"), escape($_GET['word']));
}
$stop_robots = 1;
include("./include/top.php");
?>

<?php

$user_word = trim(escape($_GET['word']));
if( sizeof($results) == 0 ) { ?>
	<?php 
	if( strpos($user_word, "*") !== false || strpos($user_word, "%") !== false ) {
		print '<p>'._("Note that search operators like <span class='inp'>*</span> or <span class='inp'>%</span> are not supported.").'</p>';
	} 
} ?>

<?php
// =================== Search again ======================================
?>

<?php if( sizeof($results) == 0 ) { ?>

	<form action="synset.php" method="get" name="f">
		<input type="hidden" name="search" value="1" />
		<table border="0">
		<tr>
			<td><strong><?php print _("New search") ?>:</strong></td>
			<td><input type="text" size="18" name="word" value="<?php print escape($_GET['word']) ?>" /></td>
			<td><?php print "<input type='submit' value='" . _("Search") . "' />" ?></td>
		</tr>
		<tr>
			<td></td>
			<td colspan="2"><input checked="checked" type="checkbox" name="substring" />
				<?php print _("Find substrings") ?></td>
		</tr>
		</table>
	</form>

<?php } else { ?>

<?php
// =================== Substring Matches ======================================
?>

	<p><?php print sprintf(_("Matches <b>%d</b> to <b>%d</b> of <b>%d</b>:"), $from+1, $to, $totalcount); ?>

	<p>
	<?php $url = "substring_search.php?word=".urlencode($_GET['word']);
	if( $from > 1 ) { ?>
		<a href="<?php print $url."&amp;from=".($from-$limit) ?>"><?php print _("&lt;&lt; previous") ?></a> &nbsp;&nbsp;
	<?php } else {?>
		<span class="inactive"><?php print _("&lt;&lt; previous") ?></span> &nbsp;&nbsp;
	<?php } ?>
	<?php if( $to < $totalcount ) { ?>
		<a href="<?php print $url."&amp;from=".$to ?>"><?php print _(" next &gt;&gt;") ?></a>
	<?php } else { ?>
		<span class="inactive"><?php print _(" next &gt;&gt;") ?></span> &nbsp;&nbsp;
	<?php } ?>
	</p>

	<ul>
		<?php
		foreach( $results as $word ) {
			$w_regex = preg_quote(trim($_GET['word']), '/');
			$w = preg_replace("/($w_regex)/i", "<strong>$1</strong>", $word);
			?>
			<li><a href="synset.php?word=<?php print urlencode($word)?>"><?php print $w ?></a></li>
			<?php
		}
		?>

	</ul>

	<p>
	<!-- copied from above: -->
	<?php if( $from > 1 ) { ?>
		<a href="<?php print $url."&amp;from=".($from-$limit) ?>"><?php print _("&lt;&lt; previous") ?></a> &nbsp;&nbsp;
	<?php } else {?>
		<span class="inactive"><?php print _("&lt;&lt; previous") ?></span> &nbsp;&nbsp;
	<?php } ?>
	<?php if( $to < $totalcount ) { ?>
		<a href="<?php print $url."&amp;from=".$to ?>"><?php print _(" next &gt;&gt;") ?></a>
	<?php } else { ?>
		<span class="inactive"><?php print _(" next &gt;&gt;") ?></span> &nbsp;&nbsp;
	<?php } ?>
	</p>
	<!-- end of copy -->

<?php } ?>

<?php
if( uservar('word') ) { ?>
	<a href="add.php?word=<?php print urlencode($_GET['word'])?>"><?php print sprintf(_("Add '%s' to the thesaurus"), escape($_GET['word'])) ?></a>
<?php } ?>

<script type="text/javascript">
<!--
	if( document.f && document.f.word ) {
		document.f.word.focus();
		document.f.word.select();
	}
// -->
</script>

<?php
externalSearchLinks($_GET['word']);

logSearch($db, $_GET['word'], sizeof($results), 1, getEndTimer());
include("./include/bottom.php");
page_close();
?>
