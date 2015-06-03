<?php
include("../include/phplib/prepend.php3");
$cancel_login = 1;
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Default_Auth"));
include("../include/tool.php");
$db = new DB_Thesaurus;
$inner_db = new DB_Thesaurus;

$meaning_id = 0;
$super_id = 0;
$matches_for_log = -1;
if( isset($_GET['word']) ) {
	// Search a given word
	$query = sprintf("SELECT word, meaning_id, meanings.super_id,
			distinction, meanings.hidden
		FROM word_meanings, words, meanings
		WHERE 
			(words.word = '%s' OR words.lookup = '%s') AND
			words.id = word_meanings.word_id AND
			word_meanings.meaning_id = meanings.id AND
			meanings.hidden = 0
		ORDER BY word",
		myaddslashes(trim(escape($_GET['word']))),
		myaddslashes(trim(escape($_GET['word']))));
	$db->query($query);
	#print $query;
	$db->next_record();
	if( uservar('substring') == 'on' ) {
		# TODO: no need to execute the query prior to this code
		$loc = sprintf("substring_search.php?word=%s&search=1", 
			urlencode($_GET['word']));
		header("Location: $loc");
		return;
	} else if( $db->nf() == 0 ) {
		// no match:
		$loc = sprintf("suggestions.php?word=%s&search=1", urlencode($_GET['word']));
		if( uservar('substring') ) {
			$loc .= "&substring=1";
		}
		header("Location: $loc");
		return;
	} elseif( $db->nf() > 1 )  {
		// more than one match:
		$loc = sprintf("multimatch.php?word=%s",
			urlencode(trim($_GET['word'])));
		if( array_key_exists('search', $_GET) ) {
			$loc .= sprintf("&search=%d", $_GET['search']);
		}
		header("Location: $loc");
		return;
	} else {
		$meaning_id = $db->f('meaning_id');
		$super_id = $db->f('super_id');
		$matches_for_log = $db->nf();
	}
} else if( isset($_GET['id']) ) {
	$meaning_id = intval($_GET['id']);
	$query = sprintf("SELECT super_id, distinction, hidden
		FROM meanings
		WHERE 
			id = %d", $meaning_id);
	$db->query($query);
	if( $db->nf() == 0 ) {
		print "No such ID.\n";
		return;
	}
	$db->next_record();
	$super_id = $db->f('super_id');
} else {
	print "Illegal arguments.";
	return;
}

$distinction = "";
if( $db->f('distinction') ) {
	$distinction = $db->f('distinction');
}
$hidden = 0;
if( intval($db->f('hidden')) == 1 ) {
	$hidden = 1;
}

$subject = getSubject($meaning_id);

$title = sprintf(_("Synset '%s'"), getSynsetString($meaning_id, 3));

$query = sprintf("SELECT words.id AS id, word_meanings.id AS wmid,
	word, meaning_id, word_meanings.use_id, uses.name
	FROM words, word_meanings LEFT JOIN uses ON (uses.id=word_meanings.use_id)
	WHERE 
		word_meanings.meaning_id = %d AND
		words.id = word_meanings.word_id
	ORDER BY meaning_id, word", $meaning_id);
$db->query($query);

include("../include/top.php");
?>

<?php 
if( uservar('changed') == 1 ) { ?>
	<p class="okay"><?php print _("Modification saved.") ?></p>
<?php } else if( uservar('changed') == 2 ) { ?>
	<p class="okay"><?php print _("Synset successfully created.") ?></p>
<?php } ?>

<?php if( isset($auth) && array_key_exists('uname', $auth->auth) ) { ?>
	<a href="synset_detail.php?mid=<?php print $meaning_id ?>"><?php print _("Details") ?></a>
<?php } ?>

<form action="do_save.php" method="post">
<input type="hidden" name="meaning_id" value="<?php print $meaning_id ?>" />

<table border="0" cellpadding="3" cellspacing="0">

<?php
$i = 0;
$color_ct = 0;
$synset_org = array();
$word_count = 0;
$antonym_defined = 0;
while( $db->next_record() ) {
	if( $i == 0 ) { 
		if( $hidden ) { ?>
			<tr>
				<td colspan="4"><strong>
					<?php print _("This synset was deleted.") ?></strong></td>
			</tr>
		<?php
		}
		?>
		<tr>
			<td colspan="4">
				<?php if( $subject ) { ?>
					<h3>[<?php print $subject ?>]</h3>
				<?php } ?>
				<?php if( $distinction ) { ?>
					<h3><?php print _("in terms of") ?> <span class="inp"><?php print $distinction ?></span></h3>
				<?php } ?>
			</td>
		</tr>
		<tr>
			<td></td>
			<td><?php print _("Synset") ?><sup><a href="faq.php#syn">?</a></sup>:</td>
			<td></td>
		</tr>
	<?php
	}
	$bgcolor = "";
	if( $color_ct % 2 == 0 ) {
		$bgcolor = "bgcolor=\"#dddddd\"";
	}
	?>

	<tr>
		<td width="5"></td>
		<td <?php print $bgcolor?>>
				<?php
				$orig_word = $db->f('word');
				$orig_word = trim(preg_replace("/\(.*?\)/", "", $orig_word));
				$word_displ = $db->f('word');
				$word_count++;
				if( $db->f('name') ) {
					$word_displ .= " [".$db->f('name')."]";
				}
				if( isset($auth) && array_key_exists('uname', $auth->auth) || WORD_DETAIL_WITHOUT_AUTH ) {
					print '<strong><a href="word_detail.php?wmid='
						.$db->f('wmid').'" title="'.
						_("Modify word properties").'">'.$word_displ.'</a></strong>';
				} else {
					print "<strong>".$word_displ."</strong>";
				}
				?>
				<?php
				$term_ids = array();
				// having two queries is faster then "... word = ... OR lookup = ...":
				$inner_query = sprintf("SELECT id FROM words WHERE word = '%s'", myaddslashes($orig_word));
				$inner_db->query($inner_query);
				while( $inner_db->next_record() ) {
					array_push($term_ids, $inner_db->f("id"));
				}
				$inner_query = sprintf("SELECT id FROM words WHERE lookup = '%s'", myaddslashes($orig_word));
				$inner_db->query($inner_query);
				while( $inner_db->next_record() ) {
					array_push($term_ids, $inner_db->f("id"));
				}
				$inner_query = sprintf("
					SELECT word_meanings.id
						FROM word_meanings, meanings
						WHERE
							(word_meanings.word_id IN (%s) AND
							meanings.id = word_meanings.meaning_id AND
							meanings.hidden = 0)",
					join(", ", $term_ids));
				$inner_db->query($inner_query);
				if( $inner_db->nf() > 1 || ($hidden && $inner_db->nf() == 1) ) {
					?>
					<?php print "<a title=\"".sprintf(_("show all %d meanings of this word"), $inner_db->nf()) ?>" href="<?php print DEFAULT_SEARCH ?>?word=<?php print urlencode($orig_word) ?>">(<?php print $inner_db->nf(); ?>)</a>
					<?php
				} ?>

				<?php
				$antonym_array = getAntonym($inner_db, $db->f('wmid'));
				if( is_array($antonym_array) ) {
					list($antonym_mid, $antonym_word) = $antonym_array;
					$antonym_defined = 1;
					?>
					&lt;<?php print _("Antonym:")." <a href=\"synset.php?id=".$antonym_mid."\">".$antonym_word ?></a>&gt;
					<?php
				}
				?>
				
		</td>
		<?php if( $hidden ) { ?>
			<td <?php print $bgcolor?>></td>
			<td <?php print $bgcolor?>></td>
		<?php } else { ?>
			<td colspan="2" <?php print $bgcolor?>><span class="myhover"><input id="<?php print "nothing$i" ?>" type="radio" name="word_<?php print $db->f('id') ?>" value="default" 
				checked="checked" /><label for="<?php print "nothing$i" ?>" class="kA"><?php print
				_("n/a") ?></label></span>
			&nbsp;
			<span class="myhover"><input id="<?php print "del$i" ?>" type="radio" name="word_<?php print $db->f('id') ?>" 
				value="delete" /><label for="<?php print "del$i" ?>"><?php print _("remove word from synset") ?></label></span>
			<? if( strpos(DEFAULT_SEARCH, "synset") === false ) { ?>
				&nbsp;
				<a href="overview.php?word=<?php print urlencode($orig_word) ?>"><?php print _("search word") ?></a>
			<?php } ?>
			</td>
		<?php } ?>
	</tr>

	<?php
	array_push($synset_org, $db->f('word'));
	
	$i++;
	$color_ct++;
} ?>

<?php if( ! $hidden ) { ?>
	<tr>
		<td height="5"></td>
	</tr>
	<tr>
		<td></td>
		<td bgcolor="#dddddd" colspan="3"><?php print 
			_("Optional: Add one more word - <a href=\"faq.php#grundform\">base forms</a> only - to this synset:") ?><br />
		<?php if( sizeof($synset_org) <= MAX_WORDS_PER_SYNSET ) { ?>
			<input accesskey="n" type="text" name="synonym_new" size="25" maxlength="50" />
			<br />
			<?php
			$query = sprintf("SELECT id, name 
				FROM uses
				ORDER BY id");
			$db->query($query);
			$usage_ct = 0;
			while( $db->next_record() ) {
				$check = "";
				$usage_ct++;
				if( $db->f("id") == 1 ) {
					$check = "checked=\"checked\"";
				}
				?>
				<span class="myhover"><input id="usage<?php print $usage_ct ?>" type="radio" <?php print $check ?> name="new_use"
					value="<?php print $db->f("id") ?>" /><label 
					for="usage<?php print $usage_ct ?>"><?php print $db->f("name") ?></label></span>
				&nbsp;
			<?php } ?>
		<?php } else { ?>
			<?php print _("(maximum number of synonyms reached)") ?>
		<?php } ?>
		</td>
	</tr>
	
	<?php 
	$can_delete = 1;
	$super_defined = 0;
	$sub_defined = 0;
	if( ONTOLOGY ) { ?>
	<tr>
		<td></td>
	</tr>
	<tr>
		<td></td>
		<td colspan="3" bgcolor="#dddddd">
		<?php 
		print _("Superordinate und subordinate synsets (<a href=\"faq.php#hierarchie\">Help</a>):<br />");
		?></td>
	</tr>
	<tr>
		<td></td>
		<td colspan="3" bgcolor="#dddddd">
		<?php 
		$max_synset_size = 3;	# don't display more words than this
		if( $meaning_id == TOP_SYNSET_ID || $meaning_id == TOP_SYNSET_ID_VERB ) {
			print _("This is the top superordinate synset.");
		} else if( $super_id ) {
			print _("Superordinate concept:");
			print " <a href=\"synset.php?id=".intval($super_id)."\">".join(', ', getSynsetWithUsage($super_id, 1, $max_synset_size))."</a>";
			print " (<label class=\"myhover\"><input type=\"checkbox\" name=\"delete_super\" value=\"1\"/>"._("delete reference").")</label>";
			$can_delete = 0;
			$super_defined = 1;
		} else {
			print _("No superordinate concept defined yet for this synset. Set one now:");
			?>
			<input accesskey="o" type="text" name="super_new" value="" />
			<?php
		}
		?>
		<br />
		<?php 
		$query = sprintf("SELECT id
			FROM meanings
			WHERE super_id = %s", $meaning_id);
		$db->query($query);
		$subsets = array();
		while( $db->next_record() ) {
			array_push($subsets, '<a href="synset.php?id='.$db->f('id').'">'.join(', ', getSynset($db->f('id'), $max_synset_size)).'</a>');
		}
		if( sizeof($subsets) > 0 ) {
			print _("Subordinate concepts:")." ";
			print join(' -- ', $subsets);
			$can_delete = 0;
			$sub_defined = 1;
		} else {
			print _("There are no subordinate concepts yet for this synset.");
		}
		?>
		</td>
	</tr>
	<?php if( $sub_defined || $super_defined ) { ?>
		<tr>
			<td></td>
			<td colspan="3" bgcolor="#dddddd">
				<a href="tree.php?id=<?php print $meaning_id ?>#position"><img 
					src="images/tree.png" border="0" alt="Tree" width="11" height="16" />&nbsp;<?php print _("Show in tree view") ?></a></td>
		</tr>
	<?php } ?>
	<?php } ?>

	<tr>
		<td></td>
		<td colspan="3">
		<?php if( $can_delete ) { ?>
			<span class="myhover"><input accesskey="x" id="delsyn" type="checkbox" name="remove" value="1" /><label for="delsyn"><?php print sprintf(_("Remove the synset '%s'"), join(', ', $synset_org)) ?></label></span>
		<?php } else {
			print "<span class=\"inactive\">"._("This synset cannot be deleted because it is a subordinate or superordinate synset for another synset.")."</span>";
		} ?>
		</td>
	</tr>

	<tr>
		<td></td>
		<td colspan="2">
		<?php if (WARN_SMALL_SYNSETS && $super_defined == 0 && $sub_defined == 0 && $word_count <= 1
				&& $antonym_defined == 0) { ?>
			<table border="0" width="550">
			<tr>
				<td><img src="images/warning.png" alt="Warning" width="33" height="30" /></td>
				<td><?php print _("This synset has only one word and no superordinate concept. Please add a synonym, set a superordinate concept, or delete this synset.") ?></td>
			</tr>
			</table>
		<?php }	?>
		</td>
		<?php if( ! $hidden ) { ?>
			<td align="right"><?php print "<input accesskey=\"a\" type=\"submit\" value=\"" . _("Modify") . "\" />" ?> <sup><a href="faq.php#korr">?</a></sup></td>
		<?php } ?>	
	</tr>


<?php } ?>

<tr>
	<td></td>
	<td colspan="3"><?php print _("Latest three modifications in this synset") ?>
		(<span class="added"><?php print _("added") ?></span>,
		<span class="removed"><?php print _("removed") ?></span>):</td>
</tr>

<tr>
	<td></td>
	<td colspan="3">

		<table border="0" cellspacing="0" cellpadding="0">
		<?php
		$limit = 3;
		if( isAdmin($auth) ) {
			$limit = 10;
		}
		$query = sprintf("SELECT id, user_id, date, word, synset, synset_id, type, comment
			FROM user_actions_log
			WHERE synset_id = %d
			ORDER BY date DESC
			LIMIT %d", $meaning_id, $limit);
		$db->query($query);
		if( $db->nf() == 0 ) { ?>
			<tr>
				<td colspan="3"><?php print _("(no modifications so far)") ?></td>
			</tr>
		<?php } ?>
		<?php
		while( $db->next_record() ) {
			?>
			<tr>
				<td valign="top"><?php 
					$date = trim($db->f('date'));
					$date = str_replace(" ", "&nbsp;", $date);
					print $date;
					$comment = $db->f('comment');
					if( ! $comment ) {
						$comment = "[keine]";
					}
					?></td>
				<td>&nbsp;</td>
				<td valign="top">
					<?php
					$msg = "";
					$msg = getChangeEntry($db->f('type'), $db->f('word'),
						$db->f('synset_id'), $db->f('synset'), $comment);
					print $msg;
					if( isAdmin($auth) ) {
						print " [".$db->f('user_id')."]";
					}
					?>
					</td>
			</tr>
		<?php 
		} 
		?>
		</table>
	</td>
</tr>

<tr><td></td></tr>

<?php if( array_key_exists('word', $_GET) ) { ?>
	<tr>
		<td></td>
		<td colspan="3"><a href="add.php?word=<?php print $_GET['word']; ?>"><?php print sprintf(_("Add another meaning of '%s' to the thesaurus"), escape($_GET['word'])) ?></a></td>
	</tr>
<?php } ?>

</table>

</form>

<?php
if( isset($_GET['word']) ) {
	logSearch($db, $_GET['word'], $matches_for_log, 0, getEndTimer());
}

include("../include/bottom.php");
page_close();
?>
