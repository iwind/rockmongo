<h3><a href="<?php h(url("databases"));?>"><?php hm("databases"); ?></a> &raquo; <a href="<?php h(url("db",array("db"=>$db)));?>"><?php h($db);?></a> &raquo; <?php hm("create_collection_full"); ?></h3>

<?php if (isset($message)):?>
<p class="message">
<?php h($message);?>
</p>
<script language="javascript">
window.parent.frames["left"].location.reload();
</script>
<?php endif;?>

<form method="post">
Name:<br/>
<input type="text" name="name" value="<?php h($name);?>"/><br/>
Is Capped:<br/>
<input type="checkbox" name="is_capped" value="1" <?php if($isCapped):?>checked="checked"<?php endif;?>/><br/>
Size:<br/>
<input type="text" name="size" value="<?php h($size);?>"/><br/>
Max:<br/>
<input type="text" name="max" value="<?php h($max);?>"/><br/>
<input type="submit" value="Create"/>
</form>