<?php
$word = $_GET['word'];
?>
<p class="compact"><strong><?php print sprintf(_("Search '<span class=\"inp\">%s</span>' using :"), escape($word)); ?></strong></p>

<ul class="compact">
	<li><a href="<?php print sprintf("http://de.wikipedia.org/wiki/Spezial:Search?search=%s&amp;go=Eintrag", urlencode($word)); ?>">Wikipedia</a>
		| <a href="<?php print sprintf("http://de.wiktionary.org/wiki/Spezial:Search?search=%s&amp;go=Eintrag", urlencode($word)); ?>">Wiktionary</a></li>
	<li><a href="<?php print sprintf("http://www.canoo.net/services/Controller?input=%s&amp;service=inflection",
		urlencode(iconv("utf8", "latin1//TRANSLIT", $word))); ?>">Flexion auf Canoo.net</a></li>
	<li><a href="<?php print sprintf("http://de.search.yahoo.com/search?p=%s", urlencode($word)); ?>">Yahoo</a>
		| <a href="<?php print sprintf("http://www.google.de/search?q=%s&amp;lr=lang_de", urlencode($word)); ?>">Google</a></li>
	<li>Deutsch/Englisch:
		<a href="<?php print sprintf("http://dict.tu-chemnitz.de/dings.cgi?lang=de&amp;".
			"noframes=1&amp;service=&amp;query=%s&amp;optword=1&amp;optcase=1&amp;opterrors=0&amp;optpro=0&amp;style=&amp;dlink=self",
			urlencode(iconv("utf8", "latin1//TRANSLIT", $word))); ?>">dict.tu-chemnitz.de</a> |
		<a href="<?php print sprintf("http://www.dict.cc/?s=%s", urlencode($word)); ?>">dict.cc</a></li>
</ul>
