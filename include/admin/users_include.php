
<table>
<tr>
	<td><strong>#</strong></td>
	<td><strong>Username</strong></td>
	<td><strong>Date of subscription</strong></td>
	<td><strong>Perm.</strong></td>
	<td><strong>Blocked</strong></td>
	<td><strong>Visible name</strong></td>
	<td><strong>Last login</strong></td>
</tr>
<?php
$query = sprintf("SELECT username, perms, subs_date, blocked, visiblename, last_login
	FROM auth_user
	ORDER by subs_date DESC");
$db->query($query);
$i = 0;
while( $db->next_record() && (($i < $limit) || ($limit == 0)) ) {
	$bg = "";
	if( $i % 2 == 0 ) {
		$bg = 'bgcolor="#eeeeee"';
	}
	$i++;
	?>
	<tr <?php print $bg; ?>>
		<td><?php print $i; ?></td>
		<td><?php print $db->f('username'); ?></td>
		<td><?php print $db->f('subs_date'); ?></td>
		<td><?php print $db->f('perms'); ?></td>
		<td><?php 
			if( $db->f('blocked') ) {
				print "<strong>blocked</strong>";
			} else {
				print "no";
			}
			?>
		</td>
		<td><?php print $db->f('visiblename'); ?></td>
		<td><?php print $db->f('last_login'); ?></td>
	</tr>	
	<?php
}
?>
</table>
