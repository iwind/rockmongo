<script language="javascript">
function checkAll(obj) {
	$(".check_collection").attr("checked", obj.checked);
}
</script>

<h3><a href="<?php h(url("databases"));?>"><?php hm("databases"); ?></a> &raquo; <a href="<?php h(url("db", array("db" => $db))); ?>"><?php h($db);?></a> &raquo; <?php hm("transfer");?></h3>

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
	<h3>Collections [<label>All <input type="checkbox" name="check_all" value="1" onclick="checkAll(this)"/></label>]</h3>
	<ul class="list">
	<?php if(empty($collections)):?>
		There is no collections here, you can not transfer.
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
	<h3>Target</h3>
	Host:<br/>
	<select name="server">
		<?php foreach ($servers as $index=>$server):  if($index == $serverIndex) {continue;} ?>
		<option value="<?php h($index);?>" <?php if(xi("server")==$index): ?>selected="selected"<?php endif;?>><?php h($server["host"]);?>:<?php h($server["port"]); ?></option>
		<?php endforeach; ?>
	</select><br/>
	<!--Database:<br/>
	<input type="text" name="target" value="<?php h(x("target")); ?>"/><br/>-->
	<br/>
</div>
<div>
	<h3><?php hm("indexes");?></h3>
	Copy Indexes? <input type="checkbox" name="copy_indexes" value="1" <?php if(x("copy_indexes")): ?>checked="checked"<?php  endif;?>/>
	<br/><br/>
</div>
<div>
	<h3>Confirm</h3>
	<input type="submit" value="Transfer"/>
</div>
</form>