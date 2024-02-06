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
					<a href="background.php"><?php print _("About") ?></a> -
					<a href="faq.php"><?php print _("FAQ") ?></a> -
					<a href="api.php"><?php print _("API") ?></a> -
					<a href="faq.php#ooo"><?php print _("Download") ?></a> -
					<a href="statistics.php"><?php print _("Statistics") ?></a> -
					<a href="top_users.php"><?php print _("Top-user") ?></a>
					<br /><br />

					<a href="a-z.php"><?php print _("From A to Z") ?></a> -
					<!-- <a href="subjects.php"> <?php #print _("Subjects") ?></a> -  -->
					<a href="check.php"><?php print _("Random Check") ?></a> -
					<a href="news_archive.php"><?php print _("News Archive") ?> - </a>
					<a href="licence.html"><?php print _("Licencia") ?></a>
				</td>
			</tr>
			<tr>
				<td align="center">
					<br />
					<?php include("news.php"); ?>
				</td>
			</tr>
		</table>

	</td>
</tr>

<tr>
	<td align="center">
		<table>
		<tr>
			<td style="text-align:left">
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----">
<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1"> </form>
			</td>
		</tr>
		</table>
	</td>
</tr>

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

