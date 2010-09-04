<h3><a href="<?php h(url("databases"));?>"><?php hm("databases"); ?></a> &raquo; <a href="<?php h(url("db",array("db"=>$db)));?>"><?php h($db);?></a> &raquo; <a href="<?php h(xn("uri"));?>"><?php h($collection)?></a> &raquo; Modify Row '<?php h($row["_id"]);?>' [<a href="<?php h($_SERVER['REQUEST_URI']);?>"><?php hm("refresh"); ?></a>]</h3>

<p class="error">
<?php if (isset($message)):h($message);endif; ?>
</p>

<form method="post">
_id:<br/>
<input type="text" readonly value="<?php h($row["_id"]);?>" size="72"/>
<br/>
Data:<br/>
<textarea rows="35" cols="70" name="data"><?php h($data); ?></textarea><br/>
<input type="submit" value="<?php hm("save"); ?>"/> <input type="button" value="<?php hm("back"); ?>" onclick="window.location='<?php h(xn("uri"));?>'"/>
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