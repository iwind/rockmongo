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
Support gzipped (.js.gz) files.<br/><br/>

<input type="hidden" name="split_js" value="0"/>
<label for="import_js_split">Do split JS File by chunks:</label> <input id="import_js_split" type="checkbox" name="split_js" value="1"/><br/>
<label>Max documents in chunk:</label> <input id="import_js_split_max" type="range" name="split_max" min="1" max="50000" step="1" value="1000"/><span id="split_max_value">1000</span><br/>
<script language="javascript">
$('#import_js_split_max').change(function(){
	$('#split_max_value').html($(this).val());
});
</script>
Useful if file more than 4mb (2.x mongo) or 16mb (3.x mongo).<br/>

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