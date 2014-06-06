<h3><?php render_navigation($db,$collection,false); ?> &raquo; <?php hm("properties");?></h3>

<?php if (isset($error)):?>
<p class="error">
<?php h($error);?>
</p>
<?php endif;?>
<?php if (isset($message)):?>
<p class="message">
<?php h($message);?>
</p>
<?php endif;?>

<form method="post">
<table bgcolor="#cccccc" cellpadding="2" cellspacing="1" width="400">
	<tr bgcolor="#ffffff">
		<td width="100"><?php hm("name"); ?></td>
		<td><?php h($collection); ?></td>
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
		<td colspan="2"><input type="submit" value="<?php hm("save"); ?>" /></td>
	</tr>
</table>

</form>

<div style="width:700px;margin-top:10px"><?php hm("warningprops"); ?></div>