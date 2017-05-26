<h3><?php render_navigation($db); ?> &raquo; <?php hm("import"); ?></h3>

<?php if (isset($error)):?> <p class="error"><?php h($error);?></p><?php endif; ?>
<?php if (isset($message)):?> 
<p class="message"><?php h($message);?></p>
<script language="javascript">
window.parent.frames["left"].location.reload();
</script>
<?php endif; ?>

<p><strong>.js</strong> file exported with RockMongo:</p>

<form method="post" enctype="multipart/form-data">
<input type="hidden" name="format" value="js"/>
JS File: <input type="file" style="width:400px" name="json"/><br/>
<input type="submit" value="<?php hm("import"); ?>"/>
</form>

<hr style="margin:20px 0px"/>
<?php if (isset($error2)):?> <p class="error"><?php h($error2);?></p><?php endif; ?>
<?php if (isset($message2)):?> 
<p class="message"><?php h($message2);?></p>
<script language="javascript">
window.parent.frames["left"].location.reload();
</script>
<?php endif; ?>
<p><strong>.json</strong> file exported with <a href="http://www.mongodb.org/display/DOCS/Import+Export+Tools" target="_blank">mongoexport</a>:</p>

<form method="post" enctype="multipart/form-data">
<input type="hidden" name="format" value="json"/>
Import to collection name:<input type="text" name="collection"/><br/>
JSON File: <input type="file" style="width:400px" name="json"/><br/>
<input type="submit" value="<?php hm("import"); ?>"/>
</form>