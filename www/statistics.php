<?php
include("./include/phplib/prepend.php3");
$cancel_login = 1;
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Default_Auth"));
include("./include/tool.php");
$db = new DB_Thesaurus;

$title = _("OpenThesaurus - Statistics");
include("./include/top.php");
?>

<br />
<br />

<div class="simplePage">

<?php
include("stats.php");
?>

</div>

<?php 
include("./include/bottom.php"); 
page_close();
?>
