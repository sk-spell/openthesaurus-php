<?php
include("../../include/phplib/prepend.php3");
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Auth"));
$db = new DB_Thesaurus;
include("../../include/tool.php");

if( $auth->auth['uname'] != 'admin' ) {
	print "Access denied.";
	return;
}

function shorten($str, $length=15) {
	$str = substr($str, 0, $length);
	return $str;
}

$title = "OpenThesaurus admin interface";
$no_text_decoration = 1;
include("../../include/top.php");

# get last display date of this page:
$query = sprintf("SELECT value FROM admin_checks
	WHERE keyname = 'login_date'");
$db->query($query);
$db->next_record();
$admin_page_last_display = $db->f('value');

# get last memory DB update:
$mem_db_date = "";
if( MEMORY_DB ) {
	$query = sprintf("SELECT lookup FROM memwords
		WHERE word = '__last_modified__'");
	$db->query($query);
	$db->next_record();
	$mem_db_date = $db->f('lookup');
}

# store date of now:
$query = sprintf("UPDATE
	admin_checks
	SET value = '%s'
	WHERE keyname = 'login_date'", date("Y-m-d H:i:s"));
$db->query($query);

?>

<table border="0" cellpadding="4" cellspacing="0">
<tr>
	<td valign="top" width="25%">
		<strong>Actions</strong><br />

		<a href="synset_size.php">Calculate average size of synsets</a><br />
		<a href="bsp.php">Random synsets</a><br />
		<a href="import.php">Import new words from text</a><br />
		<a href="suggest_log.php">Unknown words from search log</a><br />
		<a href="active_users.php">Most active users</a><br />
		<a href="topsearches.php">Top10 searches</a><br />
		<br />
		
		<a href="ooo_export.php">Build OpenOffice.org thesaurus</a><br />
		<a href="ooo_new_export.php">Build OpenOffice.org 2.0 thesaurus</a><br />
		<a href="ooo_oxt_export.php">Build OpenOffice.org 3.0 thesaurus</a><br />
		<a href="text_export.php">Build text thesaurus</a><br />
		<a href="kword_export.php">Build KWord thesaurus</a><br />
		<a href="text_list_export.php">Build text list for spell checking</a><br />
		<br />

		<a href="update_lookup.php">Update 'lookup' field</a> |
		<a href="import_wiktionary.php">Import Wiktionary</a><br />
		<br />
		
		<a href="loose.php">Words not below the top synset</a> |
		<a href="search.php">Free text search</a> | <a href="duplicate_check.php">Duplicates</a> |
		<a href="search_senses.php">Senses</a> | <a href="search_uses.php">Uses</a> | <a href="subject.php?id=1">Subjects</a> |
		<a href="find_phrases.php">Phrases</a> | <a href="abk2.php">Ellipsis</a> |
		<a href="find_large.php">Large synsets</a> | <a href="find_small.php?size=1">Small Synsets</a> |
		<a href="suffix.php">Prefix/suffix</a> | <a href="abk.php">Short forms</a> |
		<a href="find_occurences.php">Multi occurences</a> |
		<a href="fremd.php">Possible foreign words</a> | <a href="unmodified.php">modified synsets</a><br />
		
		<br />
		<strong>Admin page last displayed:</strong><br />
		<?php print $admin_page_last_display ?>
		<br />
		<?php if( $mem_db_date != "" ) { ?>
			<strong>Latest memory DB update:</strong><br />
			<?php print $mem_db_date ?>
		<?php } ?>
		

	</td>
	<td valign="top">
		<?php
		$query = sprintf("SELECT username, perms, subs_date
			FROM auth_user
			ORDER by subs_date DESC");
		$db->query($query);
		?>
		<?php $limit = 5; ?>
		<strong><?php print $limit ?> most recently subscribed users
			of <?php print $db->nf() ?>
			(<a href="users.php">show all</a>):</strong>
		<?php
		include("../../include/admin/users_include.php");
		?>

		<?php
		$limit = 15;
		if( array_key_exists('searches_limit', $_GET) && $_GET['searches_limit'] ) {
			$limit = intval($_GET['searches_limit']);
		}

		$query = sprintf("SELECT count(*) AS number FROM search_log
			WHERE date >= DATE_SUB(NOW(), INTERVAL 1 DAY)");
		$db->query($query);
		$db->next_record();
		$all_searches_recent = $db->f('number');

		$query = sprintf("SELECT count(*) AS number FROM search_log
			WHERE date >= DATE_SUB(NOW(), INTERVAL 1 DAY) AND (searchform = '1' OR searchform = '2')");
		$db->query($query);
		$db->next_record();
		$all_real_searches_recent = $db->f('number');
		?>
		<strong><?php print $limit ?> most recent searches, 
			last 24 hours: <?php print $all_searches_recent ?> searches,
			<?php print $all_real_searches_recent ?> real searches (via form or plugin),
		<a href="index.php?searches_limit=50">show more</a></strong>:
		<?php
		$i = 0;
		$query = sprintf("SELECT date, term, matches, submatch, ip, searchtime, searchform, webservice
			FROM search_log
			ORDER by id DESC
			LIMIT %d", $limit);
		$db->query($query);
		?>
		<table>
		<tr>
			<td><strong>Date</strong></td>
			<td align="right"><strong>Form</strong></td>
			<td><strong>Term</strong></td>
			<td align="right"><strong>Hits</strong></td>
			<td align="right"><strong>Time</strong></td>
			<td align="right"><strong>Sub</strong></td>
			<td align="right"><strong>IP</strong></td>
			<td align="right"><strong>WS</strong></td>
		</tr>
			<?php
			while( $db->next_record() ) {
				if( ($i % 2) == 0 ) {
					$col = ' bgcolor="#eeeeee"';
				} else {
					$col = "";
				}
				?>
				<tr<?php print $col; ?>>
					<td><?php print $db->f('date') ?></td>
					<td align="right"><span class="light">
						<?php
						if ($db->f('searchform') == "") {
							print "?";
						} else if ($db->f('searchform') == "1") {
							print "+";
						} else if ($db->f('searchform') == "2") {
							print "S";
						}
						?>
					</span></td>
					<td><?php
						$link = "../".DEFAULT_SEARCH."?word=".urlencode($db->f('term'));
						if( $db->f('submatch') ) {
							$link .= "&substring=on";
						}
						?>
						<a href="<?php print $link ?>"><?php print escape($db->f('term')) ?></a></td>
					<td align="right"><?php print $db->f('matches') ?></td>
					<td align="right">&nbsp;<?php
						$t = $db->f('searchtime');
						if ($t > 1) {
							print "<span class=\"timewarn\">";
						}
						printf("%.2f", $t); ?>
						<span class="light">s</span>
						<?php
						if ($t > 1) {
							print "</span>";
						} ?>
						</td>
					<td align="right"><?php
						if( $db->f('submatch') ) {
							print "+";
						} ?></td>
					<td align="right"><span class="light"><?php print $db->f('ip') ?></span></td>
					<td align="right"><span class="light">
						<?php
						if ($db->f('webservice') != "0") {
							print "+";
						}
						?>
					</span></td>
				<?php
				$i++;
			}
		?>
		<tr>
			<td colspan="7">Sub = substring search; Form = query submitted via search form (+) or search
				plugin (S); WS = query via webservice</td>
		</tr>
		</table>
	</td>
</tr>
</table>

<p><strong>Synsets checked only 1 time or less:</strong>
<?php
	$query = sprintf("SELECT count(*) AS ct FROM meanings WHERE check_count <= 1 AND hidden = 0");
	$db->query($query);
	$db->next_record();
	print $db->f('ct');
?>
<br />
<strong>Synsets that have a superordinate meaning:</strong>
<?php
	$query = sprintf("SELECT count(*) AS ct FROM 
		meanings WHERE super_id IS NOT NULL AND hidden = 0");
	$db->query($query);
	$db->next_record();
	print $db->f('ct');
?><br />
<strong>Wiktionary entries:</strong>
<?php
	$query = sprintf("SELECT count(*) AS ct FROM wiktionary");
	$db->query($query);
	$db->next_record();
	print $db->f('ct');
?></p>

<?php 
$actions_limit = 20;
if( array_key_exists('actions_limit', $_GET) && $_GET['actions_limit'] ) {
	$actions_limit = $_GET['actions_limit'];
}

$date_filter = 0;
if( array_key_exists('date_limit', $_GET) && $_GET['date_limit'] ) {
	$date_filter = 1;
}

# save the date of the latest entry the admin just checked:
if( array_key_exists('last_check', $_GET) && $_GET['last_check'] ) {
	$query = sprintf("UPDATE
		admin_checks
		SET value = '%s'
		WHERE keyname = 'check_date'", myaddslashes($_GET['last_check']));
	$db->query($query);
}

# get the date of the latest action hat has been checked:
$query = sprintf("SELECT value
	FROM admin_checks
	WHERE keyname = 'check_date'");
$db->query($query);
$db->next_record();
$check_date_default = "2000-01-01";
if( $db->nf() > 0 ) {
	$check_date_default = $db->f('value');
}
?>

<a name="checking">&nbsp;</a>
<form action="index.php" method="get">
	Show actions later than <input type="text" name="date_limit" value="<?php print $check_date_default ?>" /> <input type="submit" value="Go" />
</form>

<strong>Latest <a href="index.php?actions_limit=20">20</a>,  
	<a href="index.php?actions_limit=100">100</a>,
	<a href="index.php?actions_limit=250">250</a>
	actions (<a href="index.php?showadmin=1">include changes by admin</a>)</strong><br />

	<table>
	<?php
	$without_admin_sql = "WHERE user_id != 1";
	if( array_key_exists('showadmin', $_GET) && $_GET['showadmin'] ) {
		$without_admin_sql = "WHERE user_id >= 0";  # show everything
	}
	$date_limit = "";
	$order = "date DESC";
	if( $date_filter ) {
		$date_limit = sprintf("AND date > '%s'", myaddslashes($_GET['date_limit']));
		$order = "date ASC";
	}
	$query = sprintf("SELECT id, ip_address, user_id, date, word, synset, synset_id, type, comment
		FROM user_actions_log
		$without_admin_sql
		$date_limit
		ORDER BY $order
		LIMIT %d", $actions_limit);
	$db->query($query);
	#print $query;
	$prev_user = "_start";
	$prev_date = "_start";
	while( $db->next_record() ) {
		?>
		<?php if( $db->f('user_id') != $prev_user && $prev_user != "_start" ) { ?>
		<tr>
			<td colspan="3"><hr size="1" /></td>
		</tr>
		<?php } 
		?>
		<tr>
			<?php if( $db->f('date') == $prev_date && $db->f('user_id') == $prev_user ) { ?>
				<td></td>
				<td></td>
			<?php } else { ?>
				<td valign="top"><?php
						$date = str_replace(" ", "&nbsp;", $db->f('date'));
						print $date;
					?></td>
				<td valign="top"><a href="mailto:<?php print $db->f('user_id') ?>"><?php print shorten($db->f('user_id'), 10) ?></a></td>
			<?php } ?>
			<td><span class="light"><?php print $db->f('ip_address') ?></span></td>
			<?php
				$date = $db->f('date');
				$prev_date = $db->f('date');
				$prev_user = $db->f('user_id');
				$date = str_replace(" ", "&nbsp;", $db->f('date'));
				$latest_date = $date;
				$comment = $db->f('comment');
				if( ! $comment ) {
					$comment = "[none]";
				}
			?>
			<td valign="top">
				<?php
				$msg = "";
				$msg = getChangeEntry($db->f('type'), $db->f('word'),
					$db->f('synset_id'), $db->f('synset'), $comment, "../", 1);
				print $msg;
				?>
			</td>
		</tr>
	<?php 
	}
	if( $date_filter ) { ?>
	<tr>
		<td colspan="3" align="right">
			<form action="index.php" method="get">
				<input type="hidden" name="last_check" value="<?php  print $latest_date ?>" />
				<input type="hidden" name="date_limit" value="<?php  print $latest_date ?>" />
				<input type="submit" value="Continue checking" />
			</form>
		</td>
	</tr>
	<?php } ?>
	</table>

<br />

<?php
include("../../include/bottom.php");
page_close();
?>
