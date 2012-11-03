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
<?php hm("name"); ?>:<br/>
<input type="text" name="name" value="<?php h($name);?>" /><br/>
<?php hm("iscapped"); ?>:<br/>
<input type="checkbox" name="is_capped" value="1" <?php if($isCapped):?>checked="checked"<?php endif;?> /><br/>
<?php hm("size"); ?>:<br/>
<input type="text" name="size" value="<?php h($size);?>" /><br/>
<?php hm("max"); ?>:<br/>
<input type="text" name="max" value="<?php h($max);?>" /><br/>
<input type="submit" value="<?php hm("create"); ?>" />
</form>