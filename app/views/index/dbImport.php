<h3><a href="<?php h(url("databases"));?>"><?php hm("databases"); ?></a> &raquo; <a href="<?php h(url("db", array("db" => $db))); ?>"><?php h($db);?></a> &raquo; <?php hm("import"); ?></h3>

<?php if (isset($error)):?> <p class="error"><?php h($error);?></p><?php endif; ?>
<?php if (isset($message)):?> 
<p class="message"><?php h($message);?></p>
<script language="javascript">
window.parent.frames["left"].location.reload();
</script>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
<input type="file" size="40" name="json"/> <input type="submit" value="<?php hm("import"); ?>"/>
</form>