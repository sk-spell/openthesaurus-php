<?php
if( isset($db) ) {
	# called from index.php, don't re-initialise vars
} else {
	# called from cronjob
	include("../include/phplib/prepend.php3");
	include("../include/tool.php");
	$db = new DB_Thesaurus;
}
?>
		<table cellspacing="2" cellpadding="0" border="0">
		<tr>
			<td colspan="2"><strong><?php print _("Database statistics") ?><br />
				<span class="newsdate"><?php print date(TIMEFORMAT)?></span></strong></td>
		</tr>

		<?php
		// Not all words from the "words" table are used, so count
		// those linked in the word_meanings table:
		// TODO: speed up this query!
		$query = "SELECT count(DISTINCT word_id) AS ct 
			FROM word_meanings, meanings 
			WHERE meanings.id = word_meanings.meaning_id AND
				meanings.hidden = 0";
		$db->query($query);
		$db->next_record();
		?>
		<tr>
			<td><?php print _("Number of words:") ?></td>
			<td align="right"><?php print number_format($db->f('ct'), 0, ',', '.')?></td>
		</tr>

		<?php
		$query = "SELECT count(*) AS ct FROM meanings WHERE hidden = 0";
		$db->query($query);
		$db->next_record();
		?>
		<tr>
			<td><?php print _("Number of synonym sets:") ?></td>
			<td align="right"><?php print number_format($db->f('ct'), 0, ',', '.')?></td>
		</tr>

		<?php
		//$query = sprintf("SELECT count(*) AS ct FROM user_actions_log
		//	WHERE type = '%s' OR type = '%s' OR type = 'h'",
		//	ADD_WORD, ADD_SYNSET);
		//$db->query($query);
		//$db->next_record();
		?>
		<!-- 
		<tr>
			<td><?php //print _("User contributions:") ?></td>
			<td valign="bottom" align="right"><?php //print number_format($db->f('ct'), 0, ',', '.')?></td>
		</tr>
		 -->
		
		<?php
		$query = sprintf("SELECT count(*) AS ct FROM auth_user");
		$db->query($query);
		$db->next_record();
		?>
		<tr>
			<td><?php print _("Subscribed users:") ?></td>
			<td align="right" valign="bottom"><?php print number_format($db->f('ct'), 0, ',', '.')?></td>
		</tr>

		<?php
		$limit = 7;
		$query = sprintf("SELECT count(*) AS ct FROM user_actions_log
			WHERE (type = '%s' OR type = '%s' OR type = 'h') AND
			 date >= DATE_SUB(NOW(), INTERVAL %d DAY)",
			 ADD_WORD, ADD_SYNSET, $limit);
		$db->query($query);
		$db->next_record();
		?>
		<tr>
			<td><?php print sprintf(_("Changes (last %d days):"), $limit) ?></td>
			<td align="right" valign="bottom"><?php print number_format($db->f('ct'), 0, ',', '.')?></td>
		</tr>
		</table>

		<?php
		include("../include/rss.php");
		?>
