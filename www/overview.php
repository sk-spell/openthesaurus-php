<?php
include("./include/phplib/prepend.php3");
$cancel_login = 1;
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Default_Auth"));
include("./include/tool.php");
$db = new DB_Thesaurus;
$inner_db = new DB_Thesaurus;

if( ! array_key_exists('word', $_GET) ) {
	print "Illegal arguments";
	return;
} else if ( $_GET['word'] == "%" ) {
	print "Illegal arguments";
	return;
}

$title = sprintf(_("Thesaurus Matches for '<span class=\"inp\">%s</span>'"), escape($_GET['word']));
include("./include/top.php");
?>


<table cellpadding="0" cellspacing="0" class="compact">
<tr>
	<td>
	<?php 
	include("./include/synsets.php");
	if ($synmatches == 0) {
		include("./include/levenshtein.php");
	}
	flush();
	# test flush: sleep(5); 
	?>
	</td>
	<td>
	</td>
</tr>
</table>

<br />

<?php include("./include/baseforms.php"); ?>

<table cellpadding="0" cellspacing="0" width="100%" class="compact">
<tr>
	<td class="compact" width="35%" valign="top">
		<?php include("./include/substring_matches.php"); ?>
		<?php if ($synmatches > 0) { ?>
			<p class="compact"><strong><?php print _("Similarly spelled words from OpenThesaurus:"); ?></strong></p>
			<?php include("./include/levenshtein.php"); ?>
		<?php } ?>
	</td>
	<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
	<td class="compact" width="65%" valign="top">
		<?php include("./include/wikipedia_links.php"); ?>
		<?php include("./include/wiktionary.php"); ?>
		<?php include("./include/spellcheck.php"); ?>
	</td>
</tr>
</table>

<table cellpadding="0" cellspacing="0" width="100%" class="compact">
<tr>
	<td class="compact" width="35%" valign="top">
	</td>
	<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
	<td class="compact" width="65%" valign="top">
	</td>
</tr>
</table>

<table cellpadding="0" cellspacing="0" width="100%" class="compact">
<tr>
	<td class="compact" width="35%" valign="top">
		<?php
		if( $queryterm != "" ) {
			include("./include/external_searches.php");
		}
		?>
	</td>
	<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
	<td class="compact" width="65%" valign="top">
		<?php if( uservar('word') && $queryterm != "" ) { ?>
			<p class="compact"><strong><?php print _("Action:") ?></strong></p>
			<ul class="compact">
				<li><a href="add.php?word=<?php print urlencode($_GET['word'])?>"><?php print 
				sprintf(_("Add '%s' and synonyms to OpenThesaurus"), escape($_GET['word'])) ?></a></li>
			</ul>
		<?php } ?>
	</td>
</tr>
</table>

<?php
logSearch($db, $_GET['word'], $synmatches, 0, getEndTimer());
include("./include/bottom.php");
page_close();
?>
