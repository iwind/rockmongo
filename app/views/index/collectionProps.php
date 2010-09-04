<h3><a href="<?php h(url("databases"));?>"><?php hm("databases"); ?></a> &raquo; <a href="<?php h(url("db",array("db"=>$db)));?>"><?php h($db);?></a> &raquo; <a href="<?php h(url("collection", array( "db"=>$db,"collection"=>$collection )));?>"><?php h($collection)?></a> &raquo; <?php hm("properties");?></h3>

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
Name:<br/>
<input type="text" name="name" value="<?php h($collection);?>" disabled="disabled"/><br/>
Is Capped:<br/>
<input type="checkbox" name="is_capped" value="1" <?php if($isCapped):?>checked="checked"<?php endif;?>/><br/>
Size:<br/>
<input type="text" name="size" value="<?php h($size);?>"/><br/>
Max:<br/>
<input type="text" name="max" value="<?php h($max);?>"/><br/>
<input type="submit" value="<?php hm("save"); ?>"/><br/>

</form>

<div style="width:700px"><strong>Notice</strong>: To change collection options, we will create a new collection, copy all data from old one, then drop old one. This will spend a long time to complete when you have a large collection.</div>