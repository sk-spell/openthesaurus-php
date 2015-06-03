<html>
<head>
<title>Example OpenThesaurus KD XML RPC News Client</title>
</head>

<body>
<?php
/* client.php */

include("kd_xmlrpc.php");

$query = "lange";		# must be in utf-8(?)

#online server:
$site = "www.openthesaurus.de";
$location = "/webservice/server.php";

#lokaler test:
#$site = "localhost";
#$location = "/openthesaurus/www/webservice/server.php";

list($success, $response) = XMLRPC_request($site, $location, 'openthesaurus.searchSynonyms',
	array(XMLRPC_prepare($query)));

if ($success) {
	$count = 0;
	while (list ($key, $val) = each ($response)) {
		print join(', ', $response[$count]['words']);
		print "<br />";
		$count++;
	}
} else {
	print "<p>Error: " . nl2br($response['faultString']);
}
?>

</body>
</html>
