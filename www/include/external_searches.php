<?php
$word = $_GET['word'];
?>
<p class="compact"><strong><?php print sprintf(_("Search '<span class=\"inp\">%s</span>' using :"), escape($word)); ?></strong></p>

<ul class="compact">
	<li><a href="<?php print sprintf("http://sk.wikipedia.org/wiki/Špeciálne:Search?search=%s&amp;go=Ísť+na", urlencode($word)); ?>">Wikipedia</a>
		| <a href="<?php print sprintf("http://sk.wiktionary.org/wiki/Špeciálne:Search?search=%s&amp;go=Ísť+na", urlencode($word)); ?>">Wiktionary</a></li>
	<li><a href="<?php print sprintf("http://search.yahoo.com/search?ei=UTF-8&vl=lang_sk&p=%s", urlencode($word)); ?>">Yahoo</a>
		| <a href="<?php print sprintf("http://www.google.sk/search?hl=sk&q=%s&btnG=Hľadať&meta=lr=lang_sk", urlencode($word)); ?>">Google</a></li>
<!--	<li>Deutsch/Englisch-W&ouml;rterb&uuml;cher:
		<a href="<?php print sprintf("http://dict.tu-chemnitz.de/dings.cgi?lang=de&amp;".
			"noframes=1&amp;service=&amp;query=%s&amp;optword=1&amp;optcase=1&amp;opterrors=0&amp;optpro=0&amp;style=&amp;dlink=self",
			urlencode(iconv("utf8", "latin1//TRANSLIT", $word))); ?>">dict.tu-chemnitz.de</a> |
		<a href="<?php print sprintf("http://www.dict.cc/?s=%s", urlencode($word)); ?>">dict.cc</a></li>
</ul>
