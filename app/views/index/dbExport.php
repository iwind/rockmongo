<script language="javascript">
function checkAll(obj) {
	$(".check_collection").attr("checked", obj.checked);
}
</script>

<h3><a href="<?php h(url("databases"));?>"><?php hm("databases"); ?></a> &raquo; <a href="<?php h(url("db", array("db" => $db))); ?>"><?php h($db);?></a> &raquo; <?php hm("export"); ?></h3>


<form method="post">

Collections [<label>All <input type="checkbox" name="check_all" value="1" onclick="checkAll(this)"/></label>]
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
Download?<br/>
<input type="checkbox" name="can_download" value="1" <?php if(x("can_download")):?>checked="checked"<?php endif;?> />

<br/>

<input type="submit" value="Export"/>
</form>

<?php if(!x("can_download")&&isset($contents)):?>
<?php h($countRows);?> rows exported:<br/>
<textarea rows="30" cols="70"><?php h($contents);?></textarea>
<?php endif;?>