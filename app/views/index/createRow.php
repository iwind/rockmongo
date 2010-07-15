<h4><?php h($db);?> &raquo; <a href="<?php h(url("collection", array( "db"=>$db,"collection"=>$collection )));?>"><?php h($collection)?></a> &raquo; Create Row</h4>

<p class="error">
<?php if (isset($message)):h($message);endif; ?>
</p>

<form method="post">
Data:<br/>
<textarea rows="20" cols="60" name="data"><?php echo x("data") ?></textarea><br/>
<input type="submit" value="Save"/>
</form>

Data must be an valid PHP array, just like:
<blockquote>
<pre>
array (
	'value1' => 1,
	'value2' => 2,
	...
);
</pre>
</blockquote>