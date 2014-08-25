<h3><?php render_navigation($db,$realName,false); ?> &raquo; <?php hm("rename");?></h3>

<?php if(isset($error)):?>
<p class="error"><?php h($error); ?></p>
<?php endif;?>
<?php if(isset($message)):?>
<p class="message"><?php h($message); ?></p>
<?php endif;?>

<form method="post">
<input type="hidden" name="oldname" value="<?php h_escape($realName);?>"/>
<?php hm("oldname"); ?>:<br/>
<input type="text" value="<?php h_escape($realName); ?>" disabled="disabled"/><br/>
<?php hm("newname"); ?>:<br/>
<input type="text" name="newname" value="<?php h_escape(x("newname")); ?>" autofocus="autofocus"/><br/>
<?php hm("dropifexists"); ?><br/>
<input type="checkbox" name="remove_exists" value="1" <?php if(x("remove_exists")):?>checked="checked"<?php endif;?> /><br/>
<input type="submit" value="<?php hm("save"); ?>"/>
</form>

<?php if(isset($ret)):?>

<div style="border:2px #ccc solid;margin-bottom:5px;background-color:#eeefff">
<?php hm("responseserver"); ?>
	<div style="margin-top:5px">
		<?php h($ret_json);?>
	</div>
</div>

<?php if($ret["ok"]):?>
<script language="javascript">
window.parent.frames["left"].location.reload();
</script>
<?php endif;?>

<?php endif; ?>