<?php

#
# Export of a thesaurus in OpenOffice.org 3.x format (.oxt)
#

if( ! (getenv('REMOTE_ADDR') == getenv('SERVER_ADDR')) ) {
	print "Access from your host is denied.";
	return;
}

#### Configuration ###
$lang = "de";		// language of the thesaurus data
$lang2 = "DE";		// second part of "de_DE"
$target_name = "Deutscher-Thesaurus";	// resulting file will be called like this, ".oxt" will be appended automatically
$zip_command = "/usr/bin/zip";
#### End of configuration ###

$lang_code = $lang."_".$lang2;

include("../../include/phplib/prepend2.php3");
include("../../include/tool.php");

$title = "OpenThesaurus admin interface: Build OOo 3.x thesaurus files";
include("../../include/top.php");

print strftime("%H:%M:%S")." -- Starting OpenOffice.org 3.x export ...<br />\n";

// Load the REAMDE template, insert current date and save in folder
// that's going to be zipped:
$readme_template = "README_OOo3_template";
$readme_target = "../OOo2-Thesaurus/README_th_$lang_code.txt";

$readme_fh = fopen($readme_template, 'r');
if(!$readme_fh) {
	print "Error: cannot open '$readme_template'\n";
	return;
}
$readme = fread($readme_fh, filesize($readme_template));
$readme = str_replace("#YYYY-MM-DD#", date("Y-m-d"), $readme);
$readme = str_replace("#HH:MM#", date("H:i"), $readme);
$readme = str_replace("#LANG#", $lang, $readme);
$readme = str_replace("#YYYY#", date("Y"), $readme);
fclose($readme_fh);

$readme_fh = fopen($readme_target, 'w');
if(!$readme_fh) {
	print "Error: '$readme_target' cannot be opened for writing\n";
	return;
}
fwrite($readme_fh, $readme);
fclose($readme_fh);

// Name the files that are going to be zipped:
$description_template_file = "description_template.xml";
$description_file = "description.xml";
$description_target = "../OOo2-Thesaurus/$description_file";
$dictionaries_file = "Dictionaries.xcu";
$manifest_file = "META-INF/manifest.xml";

// read description template, replace variables and save under different name:
$desc_template_fh = fopen($description_template_file, 'r');
if(!$desc_template_fh) {
	print "Error: cannot open '$description_template_file'\n";
	return;
}
$desc_template = fread($desc_template_fh, filesize($description_template_file));
$desc_template = str_replace("#YYYY.MM.DD#", date("Y.m.d"), $desc_template);
fclose($desc_template_fh);
// save under a different name:
$desc_fh = fopen($description_target, 'w');
if(!$desc_fh) {
	print "Error: '$description_target' cannot be opened for writing\n";
	return;
}
fwrite($desc_fh, $desc_template);
fclose($desc_fh);

// Create ZIP:
print "Creating ZIP ...<br />\n";
print "<pre>";
$target = "../download/".$target_name.".oxt";
$tmp_target = "thes_".$lang."_".$lang2.".oxt";
$tmp_target2 = "../OOo2-Thesaurus/thes_".$lang_code.".oxt";
if (!chdir("../OOo2-Thesaurus")) {
	print "Could not change to directory ../OOo2-Thesaurus\n";
	return;
}
$zip = "$zip_command ".$target." th_".$lang_code."_v2.idx th_".$lang_code."_v2.dat README_th_".
	$lang_code.".txt ".$description_file." ".$dictionaries_file." ".$manifest_file;
print "$zip\n"; flush();

if(!system($zip)) {
	print "Error executing zip command ($zip)\n";
	return;
}

print "</pre>";

print "<p>";
print strftime("%H:%M:%S")." -- download OXT file from <a href=\"../$target\">".$target_name.".oxt</a></p>";

print "<hr />"; flush();

page_close();
?>