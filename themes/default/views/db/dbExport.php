<script type="text/javascript">
function checkAll(obj) {
	$(".check_collection").attr("checked", obj.checked);
}
</script>

<h3><?php render_navigation($db); ?> &raquo; <?php hm("export"); ?></h3>


<form method="post">
<input type="hidden" name="can_download" value="0"/>

<?php hm("collections"); ?> [<label><?php hm("all"); ?> <input type="checkbox" name="check_all" value="1" onclick="checkAll(this)"/></label>]
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
<?php hm("download"); ?><br/>
<input type="checkbox" name="can_download" value="1" <?php if(x("can_download")):?>checked="checked"<?php endif;?> /><br/>
<?php hm("compressed"); ?>:<br/>
<label><input type="checkbox" name="gzip" value="1"/> GZIP</label>

<br/><br/>
<input type="submit" value="<?php hm("export"); ?>"/>
</form>

<?php if(!x("can_download")&&isset($contents)):?>
<?php h($countRows);?> <?php hm("rowsexported"); ?>:<br/>
<textarea rows="30" cols="70"><?php h($contents);?></textarea>
<?php endif;?>