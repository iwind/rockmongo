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
<?php hm("name"); ?>:<br/>
<input type="text" name="name" value="<?php h($collection);?>" disabled="disabled"/><br/>
<?php hm("iscapped"); ?>:<br/>
<input type="checkbox" name="is_capped" value="1" <?php if($isCapped):?>checked="checked"<?php endif;?>/><br/>
<?php hm("size"); ?>:<br/>
<input type="text" name="size" value="<?php h($size);?>"/><br/>
<?php hm("max"); ?>:<br/>
<input type="text" name="max" value="<?php h($max);?>"/><br/>
<input type="submit" value="<?php hm("save"); ?>"/><br/>

</form>

<div style="width:700px"><?php hm("warningprops"); ?></div>