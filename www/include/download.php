	<?php
	$filename = "../www/download/" . TARGET_OOO2;
	$fp = fopen($filename, "r");
	$s_array = fstat($fp);
	fclose($fp);
	$date = date(TIMEFORMAT_SHORT, $s_array["mtime"]);
	$size = sprintf("%.0f", $s_array["size"]/1000);
	?>
	<a href="<?php print BASE_URL . "/download/" . TARGET_OOO2 ?>"><?php print _("OpenOffice.org 2.x thesaurus") ?></a>
	(<?php print $size ?> KB, 
	<?php print $date ?>)
	<br />

	<?php
	// only relevant for German:
	define('TARGET_OOO2_CH', "thes_de_CH_v2.zip");
	$filename = "../www/download/" . TARGET_OOO2_CH;
	$fp = fopen($filename, "r");
	$s_array = fstat($fp);
	fclose($fp);
	$date = date(TIMEFORMAT_SHORT, $s_array["mtime"]);
	$size = sprintf("%.0f", $s_array["size"]/1000);
	?>
	<a href="<?php print BASE_URL . "/download/" . TARGET_OOO2_CH ?>">OpenOffice.org-2.x-Thesaurus, schweizer Version</a>
	(<?php print $size ?> KB, 
	<?php print $date ?>)
	<br />

	<?php
	$filename = "../www/download/" . TARGET_OOO;
	$fp = fopen($filename, "r");
	$s_array = fstat($fp);
	fclose($fp);
	$date = date(TIMEFORMAT_SHORT, $s_array["mtime"]);
	$size = sprintf("%.0f", $s_array["size"]/1000);
	?>
	<a href="<?php print BASE_URL . "/download/" . TARGET_OOO ?>"><?php print _("OpenOffice.org 1.x thesaurus") ?></a>
	(<?php print $size ?> KB, 
	<?php print $date ?>)
	<br />

	<?php
	$filename = "../www/download/" . TARGET_TEXT . ".gz";
	$fp = fopen($filename, "r");
	$s_array = fstat($fp);
	fclose($fp);
	$date = date(TIMEFORMAT_SHORT, $s_array["mtime"]);
	$size = sprintf("%.0f", $s_array["size"]/1000);
	?>
	<a href="<?php print BASE_URL . "/download/" . TARGET_TEXT . ".gz" ?>"><?php print _("Plain text thesaurus") ?></a>
		(<?php print $size ?> KB, <?php print $date ?>,
		<?php print _("can be used with <a href=\"http://www-user.tu-chemnitz.de/~fri/ding/\">Ding</a>") ?>)
	<br />

	<?php
	$filename = "../www/download/" . TARGET_KWORD . ".gz";
	$fp = fopen($filename, "r");
	$s_array = fstat($fp);
	fclose($fp);
	$date = date(TIMEFORMAT_SHORT, $s_array["mtime"]);
	$size = sprintf("%.0f", $s_array["size"]/1000);
	?>
	<a href="<?php print BASE_URL . "/download/" . TARGET_KWORD . ".gz" ?>"><?php print _("KWord thesaurus") ?></a>
		(<?php print $size ?> KB, <?php print $date ?>)
