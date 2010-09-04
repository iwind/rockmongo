<h3><a href="<?php h(url("databases"));?>"><?php hm("databases"); ?></a> &raquo; <a href="<?php h(url("db",array("db"=>$db)));?>"><?php h($db);?></a> &raquo; <a href="<?php h(url("collection", array( "db"=>$db,"collection"=>$collection )));?>"><?php h($collection)?></a> &raquo; Create Row</h3>

<?php if (isset($error)):?> 
<p class="error"><?php h($error);?></p>
<?php endif; ?>
<?php if (isset($message)):?> 
<p class="message"><?php h($message);?></p>
<script language="javascript">
window.parent.frames["left"].location.reload();
</script>
<?php endif; ?>

<form method="post">
Data:<br/>
<textarea rows="35" cols="70" name="data"><?php echo x("data") ?></textarea><br/>
<input type="submit" value="<?php hm("save"); ?>"/>
</form>

Data must be a valid PHP array, just like:
<blockquote>
<pre>
array (
	'value1' => 1,
	'value2' => 2,
	...
);
</pre>
</blockquote>