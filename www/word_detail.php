<?php
include("./include/phplib/prepend.php3");
$cancel_login = 1;
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Default_Auth"));
require_once("./include/tool.php");
if (!WORD_DETAIL_WITHOUT_AUTH && $auth->auth["uid"] == "nobody") {
	$auth->login_if(true);
	return;
}
if (uservar('do_save') == 1 && $auth->auth["uid"] == "nobody") {
	$auth->login_if(true);
	return;
}
$db = new DB_Thesaurus;

$query = sprintf("SELECT words.word, word_meanings.use_id, word_meanings.meaning_id
	FROM word_meanings, words
	WHERE 
		word_meanings.id = %d AND
		words.id = word_meanings.word_id", myaddslashes(uservar('wmid')));
$db->query($query);
if( $db->nf() == 0 ) {
	print "ID not found";
	return;
}
$db->next_record();
$word = $db->f('word');
$word_use = $db->f('use_id');
if( isset($_POST['use_id']) ) {
	$word_use = $_POST['use_id'];
}
$meaning_id = $db->f('meaning_id');

$error = "";
$disambiguate = "";
if( uservar('do_save') == 1 ) {
	if( uservar('use_id') == 1 ) {
		$new_id = "NULL";
	} else {
		$new_id = intval(uservar('use_id'));
	}
	# Update database:
	if( uservar('use_id') != uservar('use_id_org') ) {
		# Logging:
		$query = sprintf("SELECT name FROM word_meanings, uses
				WHERE word_meanings.id = %d AND
					uses.id = word_meanings.use_id",
					myaddslashes(uservar('wmid')));
		$db->query($query);
		if( $db->nf() == 0 ) {
			$old_use = "[-]";
		} else {
			$db->next_record();
			$old_use = "[".$db->f('name')."]";
		}
		$query = sprintf("SELECT name FROM uses
				WHERE id = %d", myaddslashes(uservar('use_id')));
		$db->query($query);
		$db->next_record();
		$new_use = "[".$db->f('name')."]";
		// 'u' = "change usage information"
		$query = sprintf("UPDATE word_meanings
			SET use_id = %s
			WHERE
			id = %d",
			$new_id, myaddslashes(uservar('wmid')));
		$db->query($query);
		doLog($word, $meaning_id, CHANGE_USAGE, $old_use."->".$new_use);
	}

	if( isset($_POST['new_antonym_wmid']) ) {
		if (uservar("wmid") == uservar("new_antonym_wmid")) {
			$error = _("A word cannot be an antonym of itself");
		} else {
			setNewAntonym(uservar("wmid"), uservar("new_antonym_wmid"));
		}
	} else if( uservar('antonym') != uservar('antonym_org') ) {
		if (uservar('antonym') == "") {
			list($word1, $mid) = getAntonymWord(uservar('wmid'), $db);
			list($word2, $mid) = getAntonymWord(uservar('antonym_org_id'), $db);
			doLog(join(', ', getSynset(uservar('wmid'), 3)), $mid, DEL_ANTONYM, $word1."<->".$word2);
			$query = sprintf("DELETE FROM antonyms WHERE
				word_meaning_id1 = '%s' OR word_meaning_id2 = '%s'",
				myaddslashes(uservar('wmid')), myaddslashes(uservar('wmid')));
			$db->query($query);
		} else {
			$query = sprintf("SELECT word, word_meanings.id AS wmid, meanings.id AS mid,
					word FROM words, word_meanings, meanings
				WHERE
					word_meanings.word_id = words.id AND
					meanings.id = word_meanings.meaning_id AND
					meanings.hidden = 0 AND
					word = '%s'", myaddslashes(uservar('antonym')));
			$db->query($query);
			if ($db->nf() == 0) {
				$error = _("This word doesn't exist. Please create a new synonym group with this word first.");
			} else if ($db->nf() == 1) {
				$db->next_record();
				$new_wmid = $db->f('wmid');
				if ($new_wmid == uservar('wmid')) {
					$error = _("A word cannot be an antonym of itself");
				} else {
					setNewAntonym(uservar('wmid'), $new_wmid);
				}
			} else {
				$i = 0;
				while( $db->next_record() ) {
					$check = "";
					if( $i == 0 ) {
						$check = "checked=\"checked\"";
					}
					$disambiguate .= "<label><input type=\"radio\" name=\"new_antonym_wmid\" 
						value=\"".$db->f('wmid')."\" $check />". $db->f('word')." in: ".
						"<a href=\"synset.php?id=".$db->f('mid')."\">".getSynsetString($db->f('mid'), 5)."</a></label><br />\n";
					$i++;
				}
			}
		}
	} else {
		$url = sprintf("synset.php?id=%d&time=%d", $meaning_id, time());
		header("Location: $url");
		return;
	}
}

$title = sprintf(_("Details for word '%s'"), $word);

function getAntonymWord($id, $db) {
	$query = sprintf("SELECT word, word_meanings.meaning_id AS mid FROM antonyms, word_meanings, words WHERE
			word_meanings.id = %d AND words.id = word_meanings.word_id", $id);
	$db->query($query);
	$db->next_record();
	return array($db->f('word'), $db->f('mid'));
}

function setNewAntonym($thisWMID, $newWMID) {
	global $db;
	$this_id = myaddslashes($thisWMID);
	$new_antonym_id = myaddslashes($newWMID);
	$query = sprintf("SELECT id FROM antonyms
		WHERE word_meaning_id1 = %d OR word_meaning_id2 = %d",
		$this_id, $this_id);
	#print $query."<p>";
	$db->query($query);
	if ($db->nf() == 0) {
		$next_id = $db->nextid("antonyms");
		# The INSERT statement can lead to an duplicate key error if the new antonym
		# is already connected to a different word as its antonym, so check before:
		$query = sprintf("SELECT * FROM antonyms WHERE word_meaning_id1 = %d OR word_meaning_id2 = %d",
			myaddslashes($newWMID), myaddslashes($newWMID));
		$db->query($query);
		if( $db->nf() > 0 ) {
			print _("Error: the antonym you selected is already connected to a different word.");
			exit;
		}
		$query = sprintf("INSERT INTO antonyms (id, word_meaning_id1, word_meaning_id2)
			VALUES (%d, %d, %d)",
			$next_id, myaddslashes($newWMID), myaddslashes($thisWMID));
		#print $query;
		$db->query($query);
		// Logging:
		// FIXME: should be moved before the INSERT query is executed, but the
		// INSERT can lead to an error and we don't want to log the action in that case:
		list($word1, $mid) = getAntonymWord($this_id, $db);
		list($word2, $mid) = getAntonymWord($newWMID, $db);
		doLog(join(', ', getSynset($this_id, 3)), $mid, ADD_ANTONYM, $word1."<->".$word2);
	} else if ($db->nf() == 1) {
		// Logging:
		list($word1, $mid) = getAntonymWord($this_id, $db);
		list($word2, $mid) = getAntonymWord($newWMID, $db);
		doLog(join(', ', getSynset($this_id, 3)), $mid, CHANGE_ANTONYM, $word1."<->".$word2);
		# one of the next two UPDATE statements will succeed:
		$query = sprintf("UPDATE antonyms
			SET word_meaning_id2 = %d
			WHERE word_meaning_id1 = %d",
			$newWMID, $this_id);
		#print $query."<p>";
		$db->query($query);
		$query = sprintf("UPDATE antonyms
			SET word_meaning_id1 = %d
			WHERE word_meaning_id2 = %d",
			$newWMID, $this_id);
		#print $query."<p>";
		$db->query($query);
	} else {
		print "Internal error: more than one match for $query";
		return;
	}
}

function buttons() {
	global $db, $word_use;
	$query = "SELECT id, name FROM uses";
	$db->query($query);
	$i = 0;
	while( $db->next_record() ) {
		$checked = "";
		if( ($i == 0 && ! $word_use ) || $word_use == $db->f('id') ) {
			$checked = "checked=\"checked\"";
			?>
			<input type="hidden" name="use_id_org" value="<?php print $word_use ?>" />
			<?php
		}
		print '<span class="myhoverbright"><input id="id'.$db->f('id').'" '.$checked.' type="radio" name="use_id" value="'.$db->f('id').
			'" /><label for="id'.$db->f('id').'">'.$db->f('name').'</label></span><br />'."\n";
		$i++;
	}
}

function printUsage() {
	global $db, $word_use;
	$query = "SELECT id, name FROM uses";
	$db->query($query);
	$i = 0;
	while( $db->next_record() ) {
		$checked = "";
		if( $word_use == $db->f('id') ) {
			print $db->f('name');
			return;
		}
		if( $i == 0 ) {
			$first = $db->f('name');
		}
		$i++;
	}
	print $first;
}

include("./include/top.php");
?>

<form action="word_detail.php" method="post">
<input type="hidden" name="do_save" value="1" />
<input type="hidden" name="wmid" value="<?php print escape(uservar('wmid')); ?>" />

<table border="0" cellpadding="4" cellspacing="0">
<tr>
	<td width="15%"><strong><?php print _("Synset:") ?></strong></td>
	<td><?php print getSynsetString($meaning_id); ?></td>
</tr>
<tr>
	<td><strong><?php print _("Word:") ?></strong></td>
	<td><?php print $word; ?></td>
</tr>
<tr>
	<td valign="top"><strong><?php print _("Word forms:") ?></strong></td>
	<td valign="top"><?php
	$forms = join(", ", getWordForms($db, $word));
	if ($forms == "") {
		print "-";
	} else {
		print $forms;
	}
	?></td>
</tr>
<tr>
	<td valign="top"><strong><?php print _("Word usage in this synset:") ?></strong></td>
	<td valign="top">
		<?php
		if ( $auth->auth["uid"] == "nobody"	) {
			printUsage();
		} else {
			buttons();
		}
		?>
	</td>
</tr>
<tr>
	<td valign="top"><strong><?php print _("Antonym:") ?></strong></td>
	<td>
	<?php
	$antonym_array = getAntonym($db, uservar('wmid'));
	$antonym_word = "";
	if( is_array($antonym_array) ) {
		list($antonym_mid, $antonym_word, $antonym_wmid) = $antonym_array;
	}
	if( isset($_POST['antonym']) && $antonym_word == "" ) {
		$antonym_word = escape($_POST['antonym']);
	}
	if( $disambiguate != "" ) {
		print "<span class=\"error\">"._("Please disambiguate your input:")."</span><br /><br />";
		print $disambiguate;
	} else if( $antonym_word != "" ) {
		if ( $auth->auth["uid"] == "nobody"	) {
			print $antonym_word. ", ";
		} else {
			?>
			<input type="text" name="antonym" value="<?php print $antonym_word ?>" />
			<?php
		}
		?>
		<input type="hidden" name="antonym_org" value="<?php print $antonym_word ?>" />
		<input type="hidden" name="antonym_org_id" value="<?php print $antonym_wmid ?>" />
		<?php if (isset($antonym_mid)) { ?>
			Synset: <a href="synset.php?id=<?php print $antonym_mid ?>"><?php print getSynsetString($antonym_mid, 5) ?></a>
		<?php } ?>
		<?php
	} else {
		if ( $auth->auth["uid"] == "nobody"	) {
			print "-";
		} else {
		?>
			<input type="text" name="antonym" value="" />
			<input type="hidden" name="antonym_org" value="" />
			<?php
		}
	}
	if( $error != "" ) {
		print "<br /><br /><span class=\"error\">".$error."</span>";
	}
	?>
	</td>
</tr>
<tr>
	<td></td>
	<td><?php
	if ( $auth->auth["uid"] != "nobody"	) {
		print "<input type=\"submit\" value=\"" . _("Modify") . "\" />";
	} ?></td>
</tr>
</table>

</form>

<p><a href="synset.php?id=<?php print $meaning_id ?>"><?php print _("Back to synset") ?></a></p>

<?php 
$_GET['word'] = $word;
include("./include/external_searches.php"); 

include("./include/bottom.php");
page_close();
?>
