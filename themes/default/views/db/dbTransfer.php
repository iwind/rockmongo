<script language="javascript">
function checkAll(obj) {
	$(".check_collection").attr("checked", obj.checked);
}
</script>

<h3><?php render_navigation($db); ?> &raquo; <?php hm("transfer");?></h3>

<?php if (isset($error)):?>
<p class="error">
<?php h($error);?>
</p>
<?php endif;?>
<?php if (isset($message)):?>
<p class="message">
<?php h($message);?>
</p>
<script language="javascript">
window.parent.frames["left"].location.reload();
</script>
<?php endif;?>

<form method="post">
<div>
	<h3><?php hm("collections"); ?> [<label><?php hm("all"); ?> <input type="checkbox" name="check_all" value="1" onclick="checkAll(this)"/></label>]</h3>
	<ul class="list">
	<?php if(empty($collections)):?>
		<?php hm("nocollections"); ?>
	<?php else: ?>
		<?php foreach ($collections as $collection):?>
			<li><label><input type="checkbox" class="check_collection" name="checked[<?php h($collection->getName()); ?>]" value="1" <?php if (in_array($collection->getName(), $selectedCollections)): ?>checked="checked"<?php endif;?>/> <?php h($collection->getName()); ?></label></li>
		<?php endforeach; ?>
	<?php endif; ?>
	</ul>
	<div class="clear"></div>
	<br/>
</div>
<div>
	<h3><?php hm("target"); ?></h3>
	<table>
		<tr>
			<td width="100"><?php hm("host"); ?>:</td>
			<td><input type="text" name="target_host" value="<?php h($target_host); ?>" style="width:120px"/></td>
		</tr>
		<tr>
			<td><?php hm("port"); ?>:</td>
			<td><input type="text" name="target_port" value="<?php h($target_port); ?>" style="width:50px"/></td>
		</tr>
		<tr>
			<td><?php hm("authenticate"); ?>?</td>
			<td><input type="checkbox" name="target_auth" <?php if($target_auth==1): ?>checked="checked"<?php endif; ?> value="1"/></td>
		</tr>
		<tr>
			<td><?php hm("username"); ?>:</td>
			<td><input type="text" name="target_username" value="<?php h($target_username); ?>" style="width:120px"/></td>
		</tr>
		<tr>
			<td><?php hm("password"); ?>:</td>
			<td><input type="password" name="target_password" style="width:120px"/></td>
		</tr>
	</table>
	<br/>
</div>
<div>
	<h3><?php hm("indexes"); ?></h3>
	<?php hm("copyindexes"); ?> <input type="checkbox" name="copy_indexes" value="1" <?php if(x("copy_indexes")): ?>checked="checked"<?php  endif;?>/>
	<br/><br/>
</div>
<div>
	<h3><?php hm("confirm"); ?></h3>
	<input type="submit" value="<?php hm("transfer"); ?>"/>
</div>
</form>