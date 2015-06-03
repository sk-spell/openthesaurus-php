<?php 
# This file must be <link>ed from all page so the browser
# can auto-detect our search url:
include("../include/phplib/prepend.php3");
header("Content-Type: application/opensearchdescription+xml");
print '<?xml version="1.0" encoding="UTF-8"?>';
print "\n";
?>
<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/">
	<ShortName><?php print OPENSEARCH_TITLE ?></ShortName>
	<Description><?php print OPENSEARCH_DESCRIPTION ?></Description>
	<Image height="16" width="16" type="image/x-icon">http://www.openthesaurus.de/favicon.ico</Image>
	<Url type="text/html" 
		template="<?php print OPENSEARCH_URL ?>"/>
	<Language><?php print OPENSEARCH_LANGUAGE ?></Language>
	<OutputEncoding><?php print OPENSEARCH_ENCODING ?></OutputEncoding>
	<InputEncoding><?php print OPENSEARCH_ENCODING ?></InputEncoding>
</OpenSearchDescription>
