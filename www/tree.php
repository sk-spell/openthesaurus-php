<?php
include("../include/phplib/prepend.php3");
$cancel_login = 1;
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Default_Auth"));
include("../include/tool.php");
$db = new DB_Thesaurus;
$inner_db = new DB_Thesaurus;

function getJoinedSynset($id) {
	$max_disp_words = 4;
	return join(', ', getLimitedSynsetWithUsage($id, 1, $max_disp_words));
}

function printSubordinateSynsets($db, $inner_db, $id, $top_id) {
	$open_ids = array();
	array_push($open_ids, $top_id);
	if( array_key_exists('id', $_GET) ) {
		foreach(getSuperordinateSynsets($inner_db, $_GET['id']) as $tmp_id) {
			array_push($open_ids, $tmp_id);
		}
	}
	print "\t<ul class=\"tree\">\n";
	$sub_ids = getSubordinateSynsets($db, $id);
	$i = 0;
	$openall = 0;
	if( array_key_exists('openall', $_GET) && $_GET['openall'] == 1 ) {
		$openall = 1;
	}
	foreach( $sub_ids as $sub_id ) {
		$open = 0;
		if( $openall == 1 ) {
			$open = 1;
		}
		$has_sub_synsets = 1;
		if( sizeof(getSubordinateSynsets($db, $sub_id)) == 0 ) {
			$has_sub_synsets = 0;
		}
		$anchor = "";
		if( (array_key_exists('id', $_GET) && $_GET['id'] == $sub_id) ) {
			$open = 1;
			$anchor = '<a name="position"></a>';			
		} else if( array_search($sub_id, $open_ids) ) {
			$open = 1;
		}
		print "\t<li>$anchor";
		if( !$has_sub_synsets ) {
			print "<tt>[ ]</tt> ";
		} else if( $open ) {
			print "<a class=\"openplus\" href=\"tree.php?id=$id#position\"><tt>[-]</tt></a> ";
		} else {
			print "<a class=\"openplus\" href=\"tree.php?id=$sub_id#position\" ".
				"title=\""._("Show subordinate meanings")."\"><tt>[+]</tt></a> ";
		}
		print "<a href=\"synset.php?id=$sub_id\">";
		if( $open ) {
			print "<b>".getJoinedSynset($sub_id)."</b>";
		} else {
			print getJoinedSynset($sub_id);
		}
		print "</a>\n";
		if( $open ) {
			printSubordinateSynsets($db, $inner_db, $sub_id, $top_id);
		}
		print "\t</li>\n";
		$i++;
	}
	if( $i == 0 && $openall == 0 ) {
		// shouldn't happen - there's no such link
		print "\t<li>"._("No subordinate meanings defined yet")."</li>\n";
	}
	print "\t</ul>\n";
}

$title = _("OpenThesaurus Tree");
include("../include/top.php");
?>

<p>
<?php print _("This page displays the OpenThesaurus hierarchie of nouns. Click the <tt>[+]</tt> links to step into the hierarchie.") ?>

<?php print _("Note that this page only shows synonym sets which have been classified (which is the case for only a fraction of all terms in OpenThesaurus).") ?>
</p>

<?php
$query = sprintf("SELECT id
	FROM meanings
	WHERE super_id = %d
	ORDER BY id", TOP_SYNSET_ID);
$db->query($query);
?>
<ul class="tree">
	<li><?php print getJoinedSynset(TOP_SYNSET_ID); ?>
	<?php printSubordinateSynsets($db, $inner_db, TOP_SYNSET_ID, TOP_SYNSET_ID); ?>
	</li>
</ul>

<?php if( TOP_SYNSET_ID_VERB != -1 ) { ?>
	<?php
	$query = sprintf("SELECT id
		FROM meanings
		WHERE super_id = %d
		ORDER BY id", TOP_SYNSET_ID_VERB);
	$db->query($query);
	?>
	<ul class="tree">
		<li><?php print getJoinedSynset(TOP_SYNSET_ID_VERB); ?>
		<?php printSubordinateSynsets($db, $inner_db, TOP_SYNSET_ID_VERB, TOP_SYNSET_ID_VERB); ?>
		</li>
	</ul>
<?php } ?>

<?php if( TOP_SYNSET_ID_ADJ != -1 ) { ?>
    <?php
	$query = sprintf("SELECT id
		FROM meanings
		WHERE super_id = %d
		ORDER BY id", TOP_SYNSET_ID_ADJ);
	$db->query($query);
	?>
	<ul class="tree">
		<li><?php print getJoinedSynset(TOP_SYNSET_ID_ADJ); ?>
		<?php printSubordinateSynsets($db, $inner_db, TOP_SYNSET_ID_ADJ, TOP_SYNSET_ID_ADJ); ?>
		</li>
	</ul>
<?php } ?>

<br />

<?php include("../include/textads_tree.php"); ?>

<?php
include("../include/bottom.php");
page_close();
?>
