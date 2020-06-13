
</div>

<?php if( !isset($page) || ($page != "homepage" && $page != "faq" && $page != "login" && $page != "get_delete_comment")) { ?>
	<script language="JavaScript" type="text/javascript">
	<!--
	document.forms['searchform']['word'].focus();
	// -->
	</script>
<?php }

if (isset($auth->auth['uname']) !=  'admin' ) {
   include("analytics.php");
}
?>
</body>
</html>
