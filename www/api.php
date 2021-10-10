<?php
include("./include/phplib/prepend.php3");
$cancel_login = 1;
page_open(array("sess" => "Thesaurus_Session", "auth" => "Thesaurus_Default_Auth"));
$title = _("Access via the API");
$page = "api";
include("./include/top.php");
$bgcol = "#dddddd";
$style = 'style="padding:2px;%;background-color:'.$bgcol.'"';

$q_list = array();
$a_list = array();
$name_list = array();

// ------------------------------------------------
print _('<p>Numerous entries from this website can be directly queried via the HTTP-interface. Direct search of synonyms, similar words and substring matches is supported. Querrying through Wikipedia/Wiktionary is currently not supported.</p>');
print _('<p><strong>Warning: output format might change. However, it will only be extended, existing parts will not be changed without notice.</strong></p>');
print _('<h2>Query</h2>');
print _('<p>The following HTTP-query over GET finds all synsets including the word <span class="bsp">query</span>.</p>');
?>
    <pre style="background-color:#DEDEDE"><?php print HOMEPAGE . _("xml_api.php?q=<strong>query</strong>&format=text/xml")?></pre>
<?php 
print _('<h2>Result</h2>');
print _('<p>Query result is an XML file in the form:</p>');
?>

<pre style="background-color:#DEDEDE">
<?php
print "&lt;matches>\n";
print "  &lt;metaData>\n";
print "    &lt;apiVersion content='0.1.3'/>\n";
print "    &lt;warning content='WARNING -- this API is in beta -- the format may change without warning!'/>\n";
print "    &lt;copyright content='" . COPYRIGHT . "'/>\n";
print "    &lt;license content='" . LICENSE . "'/>\n";
print "    &lt;source content='" . HOMEPAGE . "'/>\n";
print "    &lt;date content='" . date("r") . "'/>\n";
print "  &lt;/metaData>\n";
print "  &lt;synset id='1234'>\n";
print "    &lt;categories>\n";
print "      &lt;category name='Category name'/>\n";
print "    &lt;/categories>\n";
print "    &lt;term term='Meaning 1, word 1'/>\n";
print "    &lt;term term='Meaning 1, word 2'/>\n";
print "  &lt;/synset>\n";
print "  &lt;synset id='2345'>\n";
print "    &lt;categories/>\n";
print "    &lt;term term='Meaning 2, word 1'/>\n";
print "  &lt;/synset>\n";
print "&lt;/matches>\n";?>
</pre>

<?php print _('<h2>Options</h2>'); ?>
    <ul>
    <?php print _("<li><b><tt>format=text/xml</tt></b>: query result will be returned in XML format. Other possible output formats currently aren't supported.</li>\n");
          print _("<li><b><tt>similar=true</tt></b>: every query returns up to five similar terms. This is useful for offering suggestions to users in case of typing errors etc. Query example:\n");?>
<pre style="background-color:#DEDEDE"><?php print HOMEPAGE . _("xml_api.php?q=<strong>query</strong>&amp;format=text/xml&amp;similar=true");?></pre>

            <?php print _("Output (excerpt):");?>
<pre style="background-color:#DEDEDE">
<?php
print "&lt;similarterms>\n";
print _('  &lt;term term="Similar term 1"  distance="1"/>
  &lt;term term="Similar term 2"  distance="2"/>
  &lt;term term="Similar term 3"  distance="2"/>
  &lt;term term="Similar term 4" distance="2"/>');
print '\n&lt;/similarterms>';
?>
</pre>
<?php
        print _('<tt>distance</tt> returns the Levenshtein distance to the search string (words in brackets are ignored). Words are sorted according to this parameter. Only words available in the OpenThesaurus are returned. This function is thus not intended for forming spell-check suggestions.</li>');
        print _('<li><b><tt>substring=true</tt></b>: every query returns up to ten hits including the search word as its substring. Query example:\n');?>
<pre style="background-color:#DEDEDE"><?php print HOMEPAGE . _("xml_api.php?q=<strong>query</strong>&amp;format=text/xml&amp;substring=true");?></pre>
        <?php print _("Output (excerpt):");?>

<pre style="background-color:#DEDEDE">
<?php
print "&lt;substringterms>\n";
print _("  &lt;term term='Substring term 1'/>
  &lt;term term='Substring term 2'/>
  &lt;term term='Substring term 3'/>
  &lt;term term='Substring term 4'/>");
print "\n&lt;/substringterms>";
?>
</pre>
          </li>
<?php
          print _('<li><b><tt>substringFromResults</tt></b> defines with which hit the output of substring matches should start (offset from first hit). Works only with <tt>substring=true</tt>. Default value is 0, so it starts with the first hit.</li>');
          print _('<li><b><tt>substringMaxResults</tt></b> defines the highest number of returned substring hits. Works only with <tt>substring=true</tt>. Default value is 10, highest value is 250.</li>');
          print _('<li><b><tt>mode=all</tt></b>: activates all available additional queries. Currently these are <tt>similar=true</tt> and <tt>substring=true</tt>.</li>');
        print "</ul>";

        print _('<h2>Known Issues</h2>');
        print "<ul>";
          print _('<li>In the query, letters with umlauts are treated as letters without umlauts, so word <span class="bsp">tur</span> also finds <span class="bsp">T&uuml;r</span> and vice versa.</li>');
        print "</ul>";

        print _('<h2>Downloads</h2>');
        print _('<p>Besides direct querying full database <a href="faq.php#ooo">downloads</a> are also still available.</p>');
        
print ('<br />
<p><span class="pagedate">
<i>');
print _('Page updated: ') . date ("Y-m-d", getlastmod());
print ('</i>
</span>
</p>');

include("./include/bottom.php"); ?>
