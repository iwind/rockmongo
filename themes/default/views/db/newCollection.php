<h3><?php render_navigation($db); ?> &raquo; <?php hm("create_collection_full"); ?></h3>

<?php if (isset($message)):?>
<p class="message">
<?php h($message);?>
</p>
<script language="javascript">
window.parent.frames["left"].location.reload();
</script>
<?php endif;?>

<form method="post">
<table bgcolor="#cccccc" cellpadding="2" cellspacing="1" width="400">
	<tr bgcolor="#ffffff">
		<td width="100"><?php hm("name"); ?></td>
		<td><input type="text" name="name" value="<?php h($name);?>" autofocus="autofocus"/></td>
	</tr>
	<tr>
		<td colspan="2"><strong>Capped Collection Options</strong></td>
	</tr>
	<tr bgcolor="#ffffff">
		<td><?php hm("iscapped"); ?></td>
		<td><input type="checkbox" name="is_capped" value="1" <?php if($isCapped):?>checked="checked"<?php endif;?> /></td>
	</tr>
	<tr bgcolor="#ffffff">
		<td><?php hm("size"); ?></td>
		<td><input type="text" name="size" value="<?php h($size);?>" size="10" /> bytes</td>
	</tr>
	<tr bgcolor="#ffffff">
		<td><?php hm("max"); ?></td>
		<td><input type="text" name="max" value="<?php h($max);?>" size="10" /> documents</td>
	</tr>
	<tr bgcolor="#ffffff">
		<td colspan="2"><input type="submit" value="<?php hm("create"); ?>" /></td>
	</tr>
</table>
</form>

<p><a href="http://docs.mongodb.org/manual/reference/method/db.createCollection/" target="_blank">Here for more details &raquo;</a></p>