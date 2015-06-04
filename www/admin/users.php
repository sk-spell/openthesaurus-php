<?php
include("../include/phplib/prepend.php3");
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Auth"));
$db = new DB_Thesaurus;
include("../include/tool.php");

if( $auth->auth['uname'] != 'admin' ) {
	print "Access denied.";
	return;
}

$title = "OpenThesaurus admin interface: users list";
include("../include/top.php");

?>

<?php
$limit = 0;
include("../include/admin/users_include.php");
?>

<p><a href="<?php print BASE_URL ?>/admin/">Back to admin homepage</a></p>

<?php
include("../include/bottom.php");
page_close();
?>
