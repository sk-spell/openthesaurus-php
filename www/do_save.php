<?php
include("../include/phplib/prepend.php3");
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Auth"));
$auth->login_if($auth->auth["uid"] == "nobody");
include("../include/tool.php");
$db = new DB_Thesaurus;

### FIXME: more checks?

if( ! uservar('meaning_id') ) {
	print "Error: no meaning_id";
	return;
}

# don't require comment from admin:
$remove = "DONT";
if( uservar('do_remove') == 1 ) {
	$remove = "do";
} else if( uservar('remove') == 1 && $auth->auth['uname'] == 'admin' ) {
	if( ADMIN_DELETE_COMMENT ) {
		$remove = "ask";
	} else {
		$remove = "do";
	}
} else if( uservar('remove') == 1 ) {
	# fixme: can be avoided by user...
	$remove = "ask";
}

if( $remove == "ask" ) {
	$url = sprintf("get_comment.php?mode=%s&meaning_id=%d",
		urlencode(uservar('mode')), urlencode(uservar('meaning_id')));
	if( uservar('mode') == 'random' ) {
		$url .= "&mode=random";
	}
	header("Location: $url");
	return;
} else if( $remove == "do" ) {

	# Don't allow the deletion of meanings that have a subordinate
	# or superordinate meaning:
	$query = sprintf("SELECT id FROM meanings WHERE
		(id = %d AND super_id IS NOT NULL) OR super_id = %d",
		uservar('meaning_id'), uservar('meaning_id'));
	$db->query($query);
	if( $db->nf() > 0 ) {
		print _("This synset cannot be deleted because it is a subordinate or superordinate synset for another synset.");
		return;
	}

	### Logging:
	doLog("", uservar('meaning_id'), REMOVE_SYNSET, uservar('comment'));

	### Remove (=hide) the synset:
	$query = sprintf("UPDATE meanings
		SET hidden = 1
		WHERE id = %d", uservar('meaning_id'));
	$db->query($query);
	
} else if( trim(uservar('synonym_new')) ) {

	### Adding the new word:
	$query = sprintf("SELECT id, word FROM words WHERE word = '%s'",
		myaddslashes(escape(trim(uservar('synonym_new')))));
	$db->query($query);
	$word_id = 0;
	$exists = 0;
	$existing_id = 0;
	# make sure the comparison is case-sensitive:
	while( $db->next_record() ) {
		if( $db->f('word') == escape(trim(uservar('synonym_new'))) ) {
			$exists = 1;
			$existing_id = $db->f('id');
			break;
		}
	}
	if( ! $exists ) {
		# word does not exists in database yet
		$word_id = $db->nextid("words");
		$lookup_word = trim(getLookupWord(uservar('synonym_new')));
		if( $lookup_word == trim(uservar('synonym_new')) ) {
			$lookup_word = "NULL";
		} else {
			$lookup_word = "'".myaddslashes(escape($lookup_word))."'";
		}
		$query = sprintf("INSERT INTO words
				(id, word, lookup) VALUES (%d, '%s', %s)",
					$word_id,
					myaddslashes(escape(trim(uservar('synonym_new')))),
					$lookup_word);
		$db->query($query);
	} else {
		$db->next_record();
		$word_id = $existing_id;
	}
	if( $word_id == 0 ) {
		die("No word_id found.");
	}
	
	$old_syns = getSynset(uservar('meaning_id'));
	if( in_array(escape(stripslashes(trim(uservar('synonym_new')))), $old_syns) ) {
		// the word exists already in this synset.
		// TODO?: should we provide an error message?
	} else {
		### Logging:
		// new synonym for existing synset
		$synonym_new = uservar('synonym_new');
		if( uservar('new_use') && uservar('new_use') != 1 ) {
			$query = sprintf("SELECT name FROM uses
					WHERE id = %d", uservar('new_use'));
			$db->query($query);
			$db->next_record();
			$synonym_new .= " [".$db->f('name')."]";
		}
		doLog($synonym_new, uservar('meaning_id'), ADD_WORD);

		$query = sprintf("INSERT INTO word_meanings
				(word_id, meaning_id)
				VALUES
				(%d, %d)",
					$word_id,
					uservar('meaning_id'));
		$db->query($query);
		// save the usage information:
		if( uservar('new_use') && uservar('new_use') != 1 ) {
			$query = sprintf("UPDATE word_meanings
				SET use_id = %d
				WHERE id = LAST_INSERT_ID()", uservar('new_use'));
			$db->query($query);
		}
	}
}

if( trim(uservar('delete_super')) ) {

	// remove reference to superordinate concept
	$query = sprintf("SELECT super_id
		FROM meanings
		WHERE id = %d", uservar('meaning_id'));
	$db->query($query);
	$db->next_record();
	doLog(join(', ', getSynset($db->f('super_id'), 3)),
		uservar('meaning_id'), DEL_SUPER);
	$query = sprintf("UPDATE meanings
		SET super_id = NULL
		WHERE id = %d", uservar('meaning_id'));
	$db->query($query);

} else if( uservar('super_id') ) {	# user coming from select_synset.php

	$id = uservar('super_id');
	if( uservar('super_id') == "nothingselected" ) {
		print _("Error: You did not select a superordinate conecpt. Please go back and select one of the given options.");
		return;
	} else if( uservar('super_id') == "create" ) {
		// create a new synset
		$id = addSynset($db, $auth, postvar('new_word'), "", "");
	}

	doLog(join(', ', getSynset($id, 3)),
		uservar('meaning_id'), ADD_SUPER);
	$query = sprintf("UPDATE meanings
		SET super_id = %d
		WHERE id = %d", $id, uservar('meaning_id'));
	$db->query($query);

} else if( trim(uservar('super_new')) ) {

	# remember all form values:
	$url = "select_synset.php?";
	while( list($key, $val) = each($_POST) ) {
		$url .= urlencode($key)."=".urlencode($val)."&";
	}
	header("Location: $url");
	return;
}

$del_list = array();
while( list($key, $val) = each($_POST) ) {
	$found = preg_match("/^word_([0-9]+)$/", $key, $matches);
	if( $found && $val == 'delete' ) {
		$id = intval($matches[1]);
		array_push($del_list, $id);
	}
	
}
if( sizeof($del_list) >= sizeof(getSynset(uservar('meaning_id'))) ) {
	print _("You cannot remove all synonyms of a synset. Use the checkbox to delete the synset instead.");
	page_close();
	return;
}
foreach( $del_list as $id ) {
	# delete from synset
	$query = sprintf("SELECT word FROM words WHERE id = %d", $id);
	$db->query($query);
	$db->next_record();
	doLog(unescape($db->f('word')), uservar('meaning_id'), REMOVE_SYNONYM);
	$query = sprintf("DELETE FROM word_meanings
		WHERE
			word_id = %d AND
			meaning_id = %d",
			$id,
			uservar('meaning_id'));
	$db->query($query);
}

if( uservar('do_remove') == 1 ) {
	$title = _("Synset removed");
	include("../include/top.php");
	?>
	<p><?php print sprintf(_("The synset <span class='inp'>%s</span> has been deleted."), 
			join(', ', getSynset(uservar('meaning_id')))) ?></p>

	<p><a href="./"><?php print _("Return to homepage") ?></a></p>

	<?php
	include("../include/bottom.php");
	page_close();
	return;
} else {
	// time() tries to force reload:
	$url = sprintf("synset.php?id=%d&changed=1&oldmode=%s&rand=%d", 
		intval(uservar('meaning_id')), uservar('mode'), time());
	header("Location: ".$url);
}

page_close();
?>
