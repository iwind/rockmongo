<h3><?php render_navigation($db,$collection,false); ?> &raquo; <?php hm("duplicate"); ?></h3>

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
<?php hm("copycollection"); ?>:<br/>
<input type="text" value="<?php h($collection);?>" disabled="disabled"/><br/>
<?php hm("to"); ?>:<br/>
<input type="text" name="target" value="<?php h(x("target"));?>"/><br/>
<?php hm("removeifexists"); ?><br/>
<input type="checkbox" name="remove_target" value="1" <?php if(x("remove_target")):?>checked="checked"<?php endif;?>/><br/>
<?php hm("copyindexes"); ?><br/>
<input type="checkbox" name="copy_indexes" value="1" <?php if (x("copy_indexes")): ?>checked="checked"<?php endif;?>/><br/>
<input type="submit" value="<?php hm("duplicate"); ?>"/>
</form>			