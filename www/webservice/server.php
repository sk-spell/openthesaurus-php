<?php
/* server.php */

include("kd_xmlrpc.php");
include("web_service_api.php");

$xmlrpc_request = XMLRPC_parse($GLOBALS['HTTP_RAW_POST_DATA']);
$methodName = XMLRPC_getMethodName($xmlrpc_request);
$params = XMLRPC_getParams($xmlrpc_request);

if(!isset($xmlrpc_methods[$methodName])) {
	$xmlrpc_methods['method_not_found']($methodName);
} else {
	$xmlrpc_methods[$methodName]($params[0]);
}
?>
