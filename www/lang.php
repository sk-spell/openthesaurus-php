<?php
include("./include/phplib/prepend.php3");
if( isset($_GET['lang']) && preg_match("/^\w\w(_\w\w)?$/", $_GET['lang']) ) {
	setcookie("thes_lang", $_GET['lang']);
} else {
	print "Missing valid 'lang' parameter.";
	return;
}
if( BASE_URL == "" ) {
	header("Location: /");
} else {
	header("Location: ".BASE_URL);
}
?>
