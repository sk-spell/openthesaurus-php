<?php
include("../include/phplib/prepend.php3");
$cancel_login = 1;
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Default_Auth"));
$title = "OpenThesaurus Shortcuts";
$page = "keys";
include("../include/top.php");
?>

<p>Hinweis: alle Shortcuts wurden bisher nur mit 
<a href="http://www.mozilla.org/products/firefox/">Firefox</a> und
<a href="http://www.konqueror.org/">Konqueror</a> (KDE 3.3+) getestet. Bei Konqueror
gen&uuml;gt es, die Strg-Taste zu dr&uuml;cken und wieder loszulassen, es werden dann alle
Shortcuts angezeigt (man muss dann nicht mehr <span class="shortcut">Alt</span> dr&uuml;cken).</p>

<h2>Globale Shortcuts</h2>

<ul>
	<li><span class="shortcut">Alt + H</span>: Zur&uuml;ck zur Homepage</li>
	<li><span class="shortcut">Alt + S</span>: Aktivierung des Suchfelds oben in der Navigation</li>
</ul>

<h2>Shortcuts auf der Synonymseite</h2>

<ul>
	<li><span class="shortcut">Alt + N</span>: Feld zur Eingabe eines weiteren Synonyms aktivieren</li>
	<li><span class="shortcut">Alt + X</span>: Checkbox zum L&ouml;schen der gesamten Synonymgruppe
		selektieren</li>
	<li><span class="shortcut">Alt + O</span>: Feld zur Eingabe eines Oberbegriffs aktivieren</li>
	<li><span class="shortcut">Alt + A</span>: &Auml;nderungen abschicken</li>
</ul>

<?php include("../include/bottom.php"); ?>
