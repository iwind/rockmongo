<h3><?php render_navigation($db,$collection,false); ?> &raquo; Sharding</h3>

<?php if($sharded): ?>
<?php if(isset($ret)): ?>
<?php render_server_response($ret) ?>
<?php endif; ?>

<?php else: ?>

<form method="post">
<input type="hidden" name="namespace" value="<?php h($db) ?>.<?php h($collection) ?>"/>
<table>
	<tr>
		<td>Namespace</td>
		<td><input type="text" value="<?php h($db) ?>.<?php h($collection) ?>" disabled="disabled"/>
	</tr>	
	<tr>
		<td>Key(s)</td>
		<td><input type="text" name="key" value="<?php h($key) ?>"/>(seperated by comma, such as "uid,name")</td>
	</tr>
	<tr>
		<td>Is Unique</td>
		<td><input type="checkbox" value="1" name="is_unique" <?php if($is_unique): ?>checked="checked"<?php endif; ?> /></td>
	</tr>
	<tr>
		<td colspan="2"><input type="submit" value="Shard"/></td>
	</tr>
</table>
</form>

<?php if(isset($ret)): ?>
<?php render_server_response($ret) ?>
<?php endif; ?>

<?php endif; ?>