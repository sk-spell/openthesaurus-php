<?php
include("./include/phplib/prepend.php3");
$title = _("OpenThesaurus - Register");
include("./include/top.php");
?>

<p><?php print _("You need to register to log in if you want write access. A generated password will be sent to your email address.") ?></p>

<p><?php print _("The only reason to register is to actively contribute to this site (adding synonyms etc). If you don't plan to do that, there's no need to register.") ?></p>

<form action="do_register.php" method="post">

<table border="0">
<tr>
	<td><?php print _("Email:") ?></td>
	<td><input type="text" name="email" value="" size="30" /></td>
</tr>
<tr>
	<td valign="top" align="right"><input type="checkbox" name="gpl" value="1" /></td>
	<td><?php print _("I agree that the words and synonyms entered by me will be published under the <a href='http://www.gnu.org/copyleft/lesser.html'>GNU Lesser General Public License (LGPL)</a>. OpenThesaurus reserves the right to additionally publish the data under any other <a href='http://www.opensource.org/docs/definition.php'>Open Source License (OSI definition)</a>. I will only enter data that nobody else holds a copyright on.") ?></td>
</tr>
<?php if( MAILING_LIST_SUBSCRIBE ) { ?>
<tr>
	<td valign="top" align="right"><input type="checkbox" name="list" value="1" /></td>
	<td><?php print _("Also subscribe me on the (low-traffic) OpenThesaurus mailing list. You will get an additional email that you need to reply to in order to join the list.") ?></td>
</tr>
<?php } ?>
<tr>
	<td></td>
	<td align="right"><input type="submit" value="<?php print _("Register") ?>" /></td>
</tr>
</table>

</form>

<br />

<?php include("./include/bottom.php"); ?>
