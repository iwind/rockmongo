<h3><a href="<?php h(url("databases"));?>"><?php hm("databases"); ?></a> &raquo; <a href="<?php h(url("db",array("db"=>$db)));?>"><?php h($db);?></a> &raquo; <a href="<?php 
				h(url("collection", array( 
					"db" => $db, 
					"collection" => $collection
				)));
			?>"><?php h($collection);?></a> &raquo; <?php hm("duplicate"); ?></h3>

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
Copy Collection:<br/>
<input type="text" value="<?php h($collection);?>" disabled="disabled"/><br/>
To:<br/>
<input type="text" name="target" value="<?php h(x("target"));?>"/><br/>
Remove Target if Exists?<br/>
<input type="checkbox" name="remove_target" value="1" <?php if(x("remove_target")):?>checked="checked"<?php endif;?>/><br/>
Copy Indexes ?<br/>
<input type="checkbox" name="copy_indexes" value="1" <?php if (x("copy_indexes")): ?>checked="checked"<?php endif;?>/><br/>
<input type="submit" value="Duplicate"/>
</form>			