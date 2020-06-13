<?php

	define('ADD_WORD',		'a');
	define('ADD_SYNSET',	'n');
	define('CHANGE_USAGE',	'u');
	define('REMOVE_SYNSET', 'r');
	define('REMOVE_SYNONYM', 'd');
	define('DEL_SUPER',		'b');
	define('ADD_SUPER',		'c');
	define('CHANGE_SUBJECT',	's');
	define('ADD_ANTONYM',	't');
	define('CHANGE_ANTONYM',	'v');
	define('DEL_ANTONYM',	'w');
	
	define('NO_SPELL_SUGGESTION', 'no suggestion');

	function isAdmin($auth) { 
		if( isset($auth) && array_key_exists('uname', $auth->auth) && 
				$auth->auth['uname'] == 'admin' ) {
			return 1;
		}
		return 0;
	}

	function getmicrotime() { 
		list($usec, $sec) = explode(" ",microtime()); 
		return ((float)$usec + (float)$sec); 
	}
	
	function startTimer() {
		global $time_start_tmp;
		$time_start_tmp = getmicrotime();
	}

	function endTimer() {
		global $time_start_tmp;
		print sprintf("%.2fs", getmicrotime()-$time_start_tmp);
	}

	function getEndTimer() {
		global $time_start_tmp;
		$t = sprintf("%.2f", getmicrotime()-$time_start_tmp);
		$t = str_replace(",", ".", $t);
		return $t;
	}

	function getLookupWord($word) {
		# FIXME: what about nested parenthesis?
		$word = preg_replace("/\(.*?\)/", "", $word);
		return trim($word);
	}

	// Find all words forms to the given word, as long as they have
	// the same upper/lowercase spelling in the first character
	// (this avoids displaying a lot of useless words with the German
	// Morphy data)
	// TODO: upper/lowercase check doesn't work with special characters
	// Requires the word_forms and word_mapping tables to be filled.
	function getWordForms($db, $word) {
		$isUpperCase = $word[0] == strtoupper($word[0]);
		#print "*".$isUpperCase.": ".$word." ".strtoupper($word[0])."<br>";
		$matches = array();
		$query = sprintf("SELECT word_forms2.word
				FROM word_forms, word_forms AS word_forms2, word_mapping, word_mapping AS word_mapping2
				WHERE 
					word_forms.word = '%s' AND
					word_mapping.base_id = word_forms.id AND
					word_mapping2.derived_id = word_mapping.derived_id AND
					word_forms2.id = word_mapping2.derived_id
				ORDER BY word", myaddslashes(getLookupWord($word)));
		$db->query($query);
		while( $db->next_record() ) {
			$wordform = $db->f('word');
			$formIsUpperCase = $wordform[0] == strtoupper($wordform[0]);
			#print $formIsUpperCase.": ".$wordform."<br>";
			if (in_array($wordform, $matches)) {
				continue;
			}
			if ($isUpperCase && $formIsUpperCase) {
				array_push($matches, $wordform);
			} else if (!$isUpperCase && !$formIsUpperCase) {
				array_push($matches, $wordform);
			}
		}
		return $matches;
	}

	function getSimilarWords($db, $word, $fastmode=0) {
		$matches = array();
		$trans_table = array();
		if( WEB_LANG == 'de_DE.utf8' ) {
			# TODO?: cases with more than one needed modification like "grosszuegig"
			# are not found
			$trans_table['ss'] = 'ß';
			$trans_table['ß'] = 'ss';
			$trans_table['ue'] = 'ü';
			$trans_table['ae'] = 'ä';
			$trans_table['oe'] = 'ö';
		}
		while( list($k, $v) = each($trans_table) ) {
			$altern_spelling = str_replace($k, $v, trim($_GET['word']));
			if( $altern_spelling == trim($_GET['word']) ) {
				continue;
			}
			$query = sprintf("SELECT word
				FROM word_meanings, words, meanings
				WHERE 
					words.id = word_meanings.word_id AND
					word_meanings.meaning_id = meanings.id AND
					meanings.hidden = 0 AND
					meanings.id NOT IN (%s) AND
					word = '%s'", HIDDEN_SYNSETS, myaddslashes($altern_spelling));
			$db->query($query);
			if( $db->nf() == 1 ) {
				$db->next_record();
				$matches[$db->f('word')] = 0;
			}
		}
		# get all non-hidden words which are about as long as the query
		# word (speed up compared to getting all words):
		$query_part = "";
		$query_part2 = "";
		if ( $fastmode == 1 ) {
			$query_part = makeSimQueryPart($word, "word");
			$query_part2 = makeSimQueryPart($word, "lookup");
		}
		$wordlen = strlen(trim($_GET['word']));
		if ( MEMORY_DB ) {
			$query = sprintf("SELECT word, lookup FROM memwords 
					WHERE (($query_part
						CHAR_LENGTH(word) >= %d AND CHAR_LENGTH(word) <= %d)
						OR
						($query_part2
						CHAR_LENGTH(lookup) >= %d AND CHAR_LENGTH(lookup) <= %d))
						ORDER BY word",
					$wordlen-1, $wordlen+1, $wordlen-1, $wordlen+1);
		} else {
			$query = sprintf("SELECT DISTINCT word, lookup
					FROM word_meanings, words, meanings
					WHERE 
						words.id = word_meanings.word_id AND
						word_meanings.meaning_id = meanings.id AND
						meanings.hidden = 0 AND
						meanings.id NOT IN (%s) AND
							(($query_part
							CHAR_LENGTH(word) >= %d AND CHAR_LENGTH(word) <= %d)
							OR
							($query_part2
							CHAR_LENGTH(lookup) >= %d AND CHAR_LENGTH(lookup) <= %d))
							ORDER BY word",
						HIDDEN_SYNSETS, $wordlen-1, $wordlen+1, $wordlen-1, $wordlen+1);
		}

		$db->query($query);
		$user_word = strtolower(trim($_GET['word']));
		while( $db->next_record() ) {
			$w = $db->f('word');
			if ( strtolower($w) ==  strtolower($word) ) {
				continue;
			}
			$diff = levenshtein($user_word, strtolower($w));
			if( $diff > 3 ) {
				$w_lookup = $db->f('lookup');
				if( $w_lookup != "" ) {
					$diff = levenshtein($user_word, strtolower($w_lookup));
				}
			}
			if( $diff <= 3 && ! array_key_exists($w, $matches) ) {
				$matches[$w] = $diff;
			}
		}
		return $matches;
	}

	# private
	function makeSimQueryPart($word, $field) {
			$firstchar = substr($word, 0, 1);
			if ($firstchar == '%') {
				$firstchar = '%%';		// avoid warning
			} else if ($firstchar == "'") {
				$firstchar = "\'";		// avoid error
			}
			$query_part = myaddslashes($field)." LIKE '".$firstchar."%%' AND";
			return $query_part;
	}
	
	// Is the word in the synonyms database and is it visible (=not hidden)?
	function wordInDB($db, $word) {
		$query = sprintf("SELECT word, meaning_id
			FROM word_meanings, words, meanings
			WHERE 
				words.word = '%s' AND
				words.id = word_meanings.word_id AND
				word_meanings.meaning_id = meanings.id AND
				meanings.hidden = 0
				LIMIT 1", myaddslashes($word));
		$db->query($query);
		if( $db->nf() > 0 ) {
			return 1;
		}
		return 0;
	}

	function getAntonym($db, $wmid) {
		$wmid = myaddslashes($wmid);
		$inner_query = sprintf("SELECT word_meaning_id1, word_meaning_id2 FROM antonyms
			WHERE word_meaning_id1 = %d OR word_meaning_id2 = %d", $wmid, $wmid);
		#print $inner_query;
		$db->query($inner_query);
		if( $db->nf() >= 1 ) {
			$db->next_record();
			$wmid1 = $db->f('word_meaning_id1');
			$wmid2 = $db->f('word_meaning_id2');
			$other_wmid = $wmid1;
			if ($wmid1 == $wmid) {
				$other_wmid = $wmid2;
			}
			$inner_query = sprintf("SELECT words.word, word_meanings.meaning_id AS mid
				FROM word_meanings, words, meanings
				WHERE word_meanings.id = %d AND
						word_meanings.word_id = words.id AND
						word_meanings.meaning_id = meanings.id AND
						meanings.hidden = 0", $other_wmid);
			$db->query($inner_query);
			if( $db->nf() >= 1 ) {
				$db->next_record();
				return array($db->f('mid'), $db->f('word'), $other_wmid);
			}
		}
		return "";
	}
		
	function getSuperordinateSynsets($db, $id) {
		$query = sprintf("SELECT super_id FROM meanings WHERE id = %d", $id);
		$db->query($query);
		$db->next_record($query);
		$synsets = array();
		if( $db->f('super_id') ) {
			array_push($synsets, $db->f('super_id'));
		}
		while( $db->f('super_id') ) {
			$last_super_id = $db->f('super_id');
			$query = sprintf("SELECT super_id FROM meanings WHERE id = %d", $db->f('super_id'));
			$db->query($query);
			$db->next_record($query);
			if( $db->f('super_id') ) {
				array_push($synsets, $db->f('super_id'));
			}
		}
		return $synsets;
	}

	function getSubordinateSynsets($db, $id) {
		$query = sprintf("SELECT id FROM meanings WHERE super_id = %d", $id);
		$db->query($query);
		$synsets = array();
		while ($db->next_record() ) {
			array_push($synsets, $db->f('id'));
		}
		return $synsets;
	}


	/** Add a new synset to the database. Return -1 on error or
	  * the synset's id on success.
	  */
	function addSynset($db, $auth, $word, $subject_id, $distinction) {
		if( strlen(trim($word)) < 1 ) {
			print _("Error: word is too short.");
			return -1;
		} else if( strlen(trim($word)) > 50 ) {
			print _("Error: word is too long.");
			return -1;
		}

		### Logging (before saving the word!):
		$date = date("Y-m-d H:i:s");
		$log_id = $db->nextid("user_actions_log");
		$query = sprintf("INSERT INTO user_actions_log
			(id, user_id, ip_address, date, word, type)
			VALUES
			(%d, '%s', '%s', '%s', '%s', '%s')",
				$log_id, myaddslashes($auth->auth['uid']), 
				myaddslashes(getenv('REMOTE_ADDR')),
				$date, myaddslashes(escape($word)), ADD_SYNSET);
		$db->query($query);

		// Adding a new meaning
		$subject_id_sql = "NULL";
		if( $subject_id != "" ) {
			$subject_id_sql = intval($subject_id);
		}
		$distinction_sql = "NULL";
		if( $distinction ) {
			$distinction_sql = "'".myaddslashes(escape($distinction))."'";
		}
		$new_meaning_id = $db->nextid("meanings");
		$query = sprintf("INSERT INTO meanings
				(id, subject_id, distinction)
				VALUES (%d, %s, %s)",
					$new_meaning_id,
					$subject_id_sql,
					$distinction_sql);
		$db->query($query);

		# update the log with the meaning id:
		$query = sprintf("UPDATE user_actions_log
			SET synset_id = %d
			WHERE id = %d",
				$new_meaning_id,
				$log_id);
		$db->query($query);

		// check if the word is in the database already
		$query = sprintf("SELECT id, word FROM words 
			WHERE word = '%s'",
			myaddslashes(escape(trim($word))));
		$db->query($query);
		$exists = 0;
		$existing_id = 0;
		# make sure the comparison is case-sensitive:
		while( $db->next_record() ) {
			if( $db->f('word') == escape(trim($word)) ) {
				$exists = 1;
				$existing_id = $db->f('id');
				break;
			}
		}	
		if( $exists ) {
			// a new meaning for an existing word
			$db->next_record();
			$query = sprintf("INSERT INTO word_meanings
					(word_id, meaning_id)
					VALUES (%d, %d)",
						$existing_id,
						$new_meaning_id);
			$db->query($query);
		} else {
			// a new word with a new meaning
			$new_word_id = $db->nextid("words");
			$lookup_word = trim(getLookupWord($word));
			if( $lookup_word == trim($word) ) {
				$lookup_word = "NULL";
			} else {
				$lookup_word = "'".myaddslashes(escape($lookup_word))."'";
			}
			$query = sprintf("INSERT INTO words
					(id, word, lookup)
					VALUES (%d, '%s', %s)",
						$new_word_id,
						myaddslashes(escape(trim($word))),
						$lookup_word);
			$db->query($query);
			$query = sprintf("INSERT INTO word_meanings
					(word_id, meaning_id)
					VALUES (%d, %d)",
						$new_word_id,
						$new_meaning_id);
			$db->query($query);
		}
		return $new_meaning_id;
	}
	
	// Show links to Google etc.:
	function externalSearchLinks($word) {
		$msg = sprintf(_("Search '%s' with Google"), escape($word));
		$url = sprintf(_("http://www.google.de/search?q=%s&amp;lr=lang_de"), urlencode($word));
		print '<p><a accesskey="g" href="'.$url.'">'.$msg.'</a>';
		print ' -- ';
		$msg = sprintf(_("Search '%s' with Wikipedia"), escape($word));
		$url = sprintf(_("http://www.google.de/search?q=site:de.wikipedia.org+%s"), urlencode($word));
		print '<a accesskey="w" href="'.$url.'">'.$msg.'</a></p>';
	}
	
	function doLog($word, $synset_id, $type, $comment="") {
		// It's ugly to save the data instead of IDs? Well, this is
		// supposed to be a log, so we cannot use IDs, as the data
		// might change later.
		// TODO: use NULL if value is not defined
		global $db, $auth;
		$date = date("Y-m-d H:i:s");
		$synset = getSynset($synset_id);
		$synset_str = "";
		if( count($synset) > 5 ) {
			# don't use more than the "first" 5 entries
			$synset = array_slice($synset, 0, 4);
			$synset_str = join(', ', $synset) . ", ...";
		} else {
			$synset_str = join(', ', $synset);
		}
		$log_id = $db->nextid("user_actions_log");
		$query = sprintf("INSERT INTO user_actions_log
			(id, user_id, ip_address, date, word, synset, synset_id, type, comment)
			VALUES
			(%d, '%s', '%s', '%s', '%s', '%s', %d, '%s', '%s')",
				$log_id,
				myaddslashes($auth->auth['uid']),
				myaddslashes(getenv('REMOTE_ADDR')),
				$date, 
				myaddslashes(escape($word)),
				myaddslashes($synset_str),
				$synset_id,
				$type,
				myaddslashes(escape($comment)));
		$db->query($query);
	}

	/** Get a word's derived forms. E.g.
	 *  believe -> unbelievable, believed, ...
	 */
	function getDerivedForms($db, $word) {
		$query = sprintf("SELECT word_forms.id, word_mapping.derived_id
			FROM word_forms, word_mapping
			WHERE 
				word_forms.word = '%s' AND
				word_mapping.base_id = word_forms.id
			ORDER BY word", myaddslashes($word));
		$db->query($query);
		$ids = array();
		while( $db->next_record() ) {
			array_push($ids, $db->f('derived_id'));
		}
		$words = array();
		if( sizeof($ids) ) {
			$query = sprintf("SELECT word
				FROM word_forms
				WHERE id IN (%s)", join(',', $ids));
			$db->query($query);
			while( $db->next_record() ) {
				array_push($words, $db->f('word'));
			}
		}
		return $words;
	}

	/** Get a word's possible base forms. E.g.
	 *  unbelievable -> believable, believe
	 */
	function getBaseform($db, $word) {
		$query = sprintf("SELECT word_forms.id, word_mapping.base_id
			FROM word_forms, word_mapping
			WHERE 
				word_forms.word = '%s' AND
				word_mapping.derived_id = word_forms.id
			ORDER BY word", myaddslashes($word));
		$db->query($query);
		$base_ids = array();
		while( $db->next_record() ) {
			array_push($base_ids, $db->f('base_id'));
		}
		$base_words = array();
		if( sizeof($base_ids) > 0 ) {
			// order by length so that the more specific (=longer)
			// words come first in the result list:
			$query = sprintf("SELECT word
				FROM word_forms
				WHERE id IN (%s)
				ORDER BY LENGTH(word) DESC", join(',', $base_ids));
			$db->query($query);
			while( $db->next_record() ) {
				array_push($base_words, $db->f('word'));
			}
		}
		return $base_words;
	}

	function getSubstringMatches($db, $str, $limit, $from=0) {
		$results = array();
		if ( MEMORY_DB ) {
			$query = sprintf("SELECT word FROM memwords
				WHERE word LIKE '%%%s%%'", myaddslashes(escape($str)));
		} else {
			$query = sprintf("SELECT DISTINCT word
				FROM word_meanings, words, meanings
				WHERE 
					words.word LIKE '%%%s%%' AND
					words.id = word_meanings.word_id AND
					word_meanings.meaning_id = meanings.id AND
					meanings.hidden = 0 AND
					meanings.id NOT IN (%s)
				ORDER BY word", myaddslashes(escape($str)), HIDDEN_SYNSETS);
		}
		$db->query($query);
		$prev_word = "";
		$i = 0;
		while( $db->next_record() ) {
			if( $i >= $from && sizeof($results) < $limit ) {
				array_push($results, $db->f('word'));
			}
			$i++;
			$prev_word = $db->f('word');
		}
		return $results;
	}

	# TODO: merge with getSubstringMatches():
	function getSubstringMatchesCount($db, $str) {
		$results = array();
		if ( MEMORY_DB ) {
			$query = sprintf("SELECT word FROM memwords 
				WHERE word LIKE '%%%s%%'", myaddslashes(escape($str)));
		} else {
			$query = sprintf("SELECT DISTINCT word
				FROM word_meanings, words, meanings
				WHERE 
					words.word LIKE '%%%s%%' AND
					words.id = word_meanings.word_id AND
					word_meanings.meaning_id = meanings.id AND
					meanings.hidden = 0 AND
					meanings.id NOT IN (%s)
				ORDER BY word", myaddslashes(escape($str)), HIDDEN_SYNSETS);
		}
		$db->query($query);
		return $db->nf();
	}

	/** Check the words in text for spelling errors, using aspell. A list
		of suggested corrections is returned for the first error. All further
		errors are then ignored. Based on:
		spellcheck.php -- aspell-based spellchecker implemented in PHP
		Copyright (C) 2003 by Chris Snyder (csnyder@chxo.com)
	*/
	function spellcheck($text)
	{
		// create a tempfile:
		$temptext = tempnam("/tmp", "spelltext");
		if( ! $temptext ) {
			print "Warning: spellcheck: cannot get temp file name";
			return;
		}

		$spellcommand = "cat $temptext | ".SPELLCHECK_EXE." ".
			"-a -d ".SPELLCHECK_DICT_BASE;
		#print $spellcommand."<br>";

		if( $fd = fopen($temptext, "w") ) {
			$textarray = explode("\n", $text);
			fwrite($fd, "\n");
			foreach($textarray as $key=>$value) {
				// adding the carat to each line prevents the use of aspell commands 
				// within the text...
				fwrite($fd,"$value\n");
			}
			fclose($fd);
			// run aspell:
			$return = shell_exec($spellcommand);
			if ($return == "") {
				print "Error: Could not properly execute SPELLCHECK_EXE";
				return array();
			}
			// unlink that tempfile:
			unlink($temptext);
			// parse $return and $text line by line:
			$returnarray = explode("\n", $return);
			$resultarray = array();
			foreach( $returnarray as $key=>$value ) {
				// if there is a correction here, processes it:
				if( substr($value, 0, 1) == "&" ) {
					$correction = explode(" ", $value);
					$word = $correction[1];
					$suggstart = strpos($value, ":") + 2;
					$suggestions = substr($value, $suggstart);
					$suggestionarray = explode(", ", $suggestions);
					array_push($resultarray, $suggestionarray);
				} elseif( substr($value, 0, 1) == "#" ) {
					array_push($resultarray, array(NO_SPELL_SUGGESTION));
				} elseif( substr($value, 0, 1) == "*" ) {
					array_push($resultarray, array());
				}
			}
		} else {
			print "Warning: spellcheck: could not open temp file";
		}
		return $resultarray;
	}

	function logSearch($db, $term, $matches, $submatch, $searchtime, $webservice=0) {
		$searchform = 0;
		if( array_key_exists('search', $_GET) && $_GET['search'] == 1 ) {
			// only log explicit searches (not clicks on links)
			$searchform = 1;
		} else if( array_key_exists('search', $_GET) && $_GET['search'] == 2 ) {
			// also log searches via Firefox (Sherlock) search:
			$searchform = 2;
		}
		$query = sprintf("INSERT INTO
			search_log (term, date, matches, submatch, ip, searchtime, searchform, webservice)
			VALUES ('%s', '%s', %d, %d, '%s', %s, %d, %d)",
			myaddslashes(trim($term)),
			date("Y-m-d H:i:s"),
			$matches, $submatch, getenv('REMOTE_ADDR'), $searchtime, $searchform, $webservice);
		$db->query($query);
	}

	/** Get the synset as a string. */
	function getSynsetString($id, $max_elements=-1) {
		$syn_arr = getSynset($id, $max_elements);
		$s = join(', ', $syn_arr);
		return $s;
	}

	/** Get the synset as an array. */
	function getSynset($id, $max_elements=-1) {
		$db = new DB_Thesaurus;
		$query = sprintf("SELECT word
			FROM words, word_meanings
			WHERE 
				word_meanings.meaning_id = %d AND
				words.id = word_meanings.word_id
			ORDER BY word", $id);
		$db->query($query);
		$synset = array();
		while( $db->next_record() ) {
			array_push($synset, $db->f('word'));
		}
		if( $max_elements != -1 && sizeof($synset) > $max_elements ) {
			$synset = array_slice($synset, 0, $max_elements);
			array_push($synset, "...");
		}
		return $synset;
	}

	/** Get the synset as an array, including "(colloquial)" etc but limited to at 
	  * most $max_elements words.
	  */
	function getLimitedSynsetWithUsage($id, $shortname, $max_elements) {
		return getSynsetWithUsage($id, $shortname, $max_elements);
	}

	/** Get the synset as an array, including "(colloquial)" etc. */
	function getSynsetWithUsage($id, $shortname=0, $max_elements=-1) {
		$db = new DB_Thesaurus;
		$query = sprintf("SELECT word, uses.name, uses.shortname
			FROM words, word_meanings LEFT JOIN uses ON (uses.id=word_meanings.use_id)
			WHERE 
				word_meanings.meaning_id = %d AND
				words.id = word_meanings.word_id
			ORDER BY word", $id);
		$db->query($query);
		$synset = array();
		while( $db->next_record() ) {
			$w = $db->f('word');
			$colName = 'name';
			if ($shortname == 1) {
				$colName = 'shortname';
			}
			if( $db->f($colName) ) {
				$w .= " (".$db->f($colName).")";
			}
			array_push($synset, $w);
		}
		if( $max_elements != -1 && sizeof($synset) > $max_elements ) {
			$synset = array_slice($synset, 0, $max_elements);
			array_push($synset, "...");
		}
		return $synset;
	}

	function getSubject($meaning_id) {
		$db = new DB_Thesaurus;
		$query = sprintf("SELECT subject
			FROM meanings, subjects
			WHERE 
				meanings.id = %d AND
				subjects.id = meanings.subject_id", $meaning_id);
		$db->query($query);
		$db->next_record();
		return $db->f('subject');
	}

	/** Strike out deleted words and show added words in green. */
	function getChangeEntry($type, $word, $synset_id, $synset, $comment, $prefix="", $admin=0) {	
		$word_org = "";
		$msg = "";
		if( $type == 'd' ) {
			$s = $synset;
			$del = sprintf("<span class=\"removed\">%s</span>", $word);
			$s_org = $s;
			$parts = split(", ", $s);
			$i = 0;
			foreach( $parts as $part ) {
				if( trim($part) == $word ) {
					$parts[$i] = $del;
				}
				$i++;
			}
			$s = join(', ', $parts);
			if( $s_org == $s ) {
				// the entry was shortened and the relevent word
				// was not part of the entry, so add it:
				$s = sprintf("<span class=\"removed\">%s</span>, %s", $word, $s);
			}
			$word = $s;
		} elseif( $type == ADD_WORD ) {
			$word = sprintf("<span class=\"added\">%s</span>, %s", $word, $synset);
		} elseif( $type == REMOVE_SYNSET ) {
			$word = sprintf("<span class=\"removed\">%s</span> %s", $synset, $comment);
		} elseif( $type == ADD_SYNSET ) {
			$word = sprintf("<span class=\"added\">%s</span> [%s]", $word, _("new meaning"));
		} elseif( $type == CHANGE_USAGE ) {
			$comments = preg_split("/-&gt;/", $comment);
			if( ! isset($comments[1]) ) {
				// could only happen with old loggin code, if at all:
				$word = sprintf("<span>%s: %s</span>", $word, $comment);
			} else {
				$word = sprintf("%s: <span class=\"removed\">%s</span> &gt;&gt; <span class=\"added\">%s</span>", $word, $comments[0], $comments[1]);
			}
		} elseif( $type == DEL_SUPER && $admin ) {
			$word = sprintf("%s is a <span class=\"removed\">%s</span>", $synset, $word);
		} elseif( $type == DEL_SUPER ) {
			$word = sprintf(_("Removed superordinate reference: <span class=\"removed\">%s</span>"), $word);
		} elseif( $type == ADD_SUPER && $admin ) {
			$word = sprintf("%s is a <span class=\"added\">%s</span>", $synset, $word);
		} elseif( $type == ADD_SUPER ) {
			$word = sprintf(_("Added superordinate reference: <span class=\"added\">%s</span>"), $word);
		} elseif( $type == ADD_ANTONYM ) {
			$word = sprintf(_("Added antonym relation: <span class=\"added\">%s</span>"), $comment);
		} elseif( $type == CHANGE_ANTONYM ) {
			$word = sprintf(_("Changed antonym relation: <span class=\"added\">%s</span>"), $comment);
		} elseif( $type == DEL_ANTONYM ) {
			$word = sprintf(_("Deleted antonym relation: <span class=\"removed\">%s</span>"), $comment);
		} elseif( $type == CHANGE_SUBJECT ) {
			$comments = preg_split("/-&gt;/", $comment);
			if ($comments[1] == '') {
				$comments[1] = _("(none)");
			}
			$synset_str = "";
			if( $admin ) {
				$synset_str = $word;
			}
			$word = sprintf(_("Change of subject:")." %s <span class=\"removed\">%s</span> &gt;&gt; <span class=\"added\">%s</span>",
				$synset_str, $comments[0], $comments[1]);
		} else {
			$word = sprintf("??? Unknown action for <span class=\"inp\">%s</span>", $word);
		}
		if( $admin ) {
			$msg = sprintf("<a href=\"${prefix}synset.php?id=%d\">%s</a>",
				$synset_id, $word);
		} else {
			$msg = $word;
		}
		#return $type." ".$msg;
		return $msg;
	}

	/** Escape stuff that gets printed to page to avoid cross site scripting. */
	function escape($string) {
		$string = preg_replace("/&/", "&amp;", $string);
		$string = preg_replace("/\"/", "&quot;", $string);
		$string = preg_replace("/'/", "&apos;", $string);
		$string = preg_replace("/</", "&lt;", $string);
		$string = preg_replace("/>/", "&gt;", $string);
		return $string;
	}
	
	/** Unescape the basic XML entities. */
	function unescape($string) {
		$string = preg_replace("/&amp;/", "&", $string);
		$string = preg_replace("/&quot;/", "\"", $string);
		$string = preg_replace("/&apos;/", "'", $string);
		$string = preg_replace("/&lt;/", "<", $string);
		$string = preg_replace("/&gt;/", ">", $string);
		return $string;
	}

	function html_escape($html_escape) {
        $html_escape =  htmlspecialchars($html_escape, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return $html_escape;
    }

	function myaddslashes($st) {
		if (get_magic_quotes_gpc()) {
			$st = stripslashes($st);
		}
		if (!is_numeric($st)) {
			$st = html_escape($st);
		}
		return $st;
	}

	function limitedJoin($delim, $arr, $maxElements) {
		$s = "";
		$i = 0;
		foreach ($arr as $elem) {
			if ( $i > $maxElements ) {
				$s .= ", ...";
				break;
			}
			if ( $i > 0 ) {
				$s .= $delim;
			}
			$s .= $elem;
			$i++;
		}
		return $s;
	}

	/* the following three functions provide access to
	   user variables from forms, but without throwing warnings
	   if the variables don't exist. */
	   
	function postvar($name) {
		if ( array_key_exists($name, $_POST) ) {
			return $_POST[$name];
		} else {
			return;
		}
	}

	function getvar($name) {
		if ( array_key_exists($name, $_GET) ) {
			return $_GET[$name];
		} else {
			return;
		}
	}

	function uservar($name) {
		if ( array_key_exists($name, $_POST) ) {
			return $_POST[$name];
		} else if ( array_key_exists($name, $_GET) ) {
			return $_GET[$name];
		} else {
			return;
		}
	}

	function emailOkay($email) {
		if( eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$", $email)) { 
			return 1;
		} else {
			return 0;
		}
	}

	# Convert text from utf8 to latin1 if UTF8_DATABASE is set.
	function maybeConvert($word) {
		if (UTF8_DATABASE) {
			$word = iconv("utf-8", "latin1", $word);
		}
		return $word;
	}

?>
