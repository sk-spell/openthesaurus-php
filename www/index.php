<?php
include("./include/phplib/prepend.php3");
$cancel_login = 1;
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Default_Auth"));
include("./include/tool.php");
$db = new DB_Thesaurus;

$disable_title = 1;
$pagetitle = _("OpenThesaurus (homepage headline)");
$title = _("OpenThesaurus (homepage title)");
$page = "homepage";
include("./include/top.php");
?>

<br />
<br />
<br />

<table border="0" cellpadding="2" cellspacing="5" width="100%">
<tr>
	<td align="center">
	
		<table border="0" cellpadding="2" cellspacing="5" width="80%">
			<tr>
				<td align="center">
					<img src="art/logo.png" alt="OpenThesaurus Logo" width="70" height="63" />
				</td>
			</tr>
			<tr>
				<td align="center">
					<h1><?php print $pagetitle ?></h1>
				</td>
				<td rowspan="3">
					<!-- place for ad -->
				</td>
			</tr>
			<tr>
				<td align="center">
					<br />
					<form action="<?php print DEFAULT_SEARCH ?>" method="get" name="f">
						<input type="hidden" name="search" value="1" />
						<input accesskey="s" type="text" size="25" name="word" value="" />
						<?php print "<input type='submit' value='" . _("Search") . "' />" ?>
					</form>
				</td>
			</tr>
			<tr>
				<td align="center">
					<br />
					<a href="background.php">About</a> -
					<a href="faq.php">FAQ</a> -
					<a href="faq.php#ooo">Download</a> -
					<a href="statistics.php">Statistik</a> - 
					<a href="top_users.php">Top-User</a>
					<br /><br />

					<a href="a-z.php">A bis Z</a> - 
					<!-- <a href="subjects.php">Themengebiete</a> -  --> 
					<a href="check.php">Zufallseintr&auml;ge</a> - 
					<a href="tree.php">Baumansicht</a> -
					<a href="variation.php?lang=at">&Ouml;sterreichische W&ouml;rter</a> - 
					<a href="variation.php?lang=ch">Schweizer W&ouml;rter</a>

				</td>
			</tr>
			<tr>
				<td align="center">
					<br />
					<?php include("news.php"); ?>
					
					<br /><br /><br />
					<a title="Bookmark setzen bei Mister Wong"
						href="http://www.mister-wong.de/index.php?action=addurl&amp;bm_url=http://www.openthesaurus.de&amp;bm_description=Synonym-W&ouml;rterbuch%20-%20OpenThesaurus&amp;bm_tags=synonyme%20w&ouml;rterbuch%20thesaurus"><img border="0" width="22" height="22" alt="Mister Wong" src="images/sb_misterwong.gif"/></a>
					&nbsp;
					<a title="Bookmark setzen bei delicious"
						href="http://del.icio.us/post?url=http://www.openthesaurus.de&amp;title=Synonym-W&ouml;rterbuch%20-%20OpenThesaurus&amp;notes="><img border="0" width="22" height="22" alt="delicious" src="images/sb_delicious.gif"/></a>

				</td>
			</tr>
		</table>
		
	</td>
</tr>

<tr><td>&nbsp;</td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td>&nbsp;</td></tr>
<tr>
	<td align="center">
		<table>
		<tr>
			<td style="text-align:left">
				<?php include("./include/textads_homepage.php"); ?>
			</td>
		</tr>
		</table>
	</td>
</tr>

</table>

<script type="text/javascript">
<!--
	document.f.word.focus();
// -->
</script>

<?php 
include("./include/bottom.php"); 
page_close();
?>
 