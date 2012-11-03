<div class="operation">
	<?php render_server_menu("command"); ?>
</div>

<a href="http://www.mongodb.org/display/DOCS/List+of+Database+Commands" target="_blank">&raquo; <?php hm("listdbcommands"); ?></a> 

<?php if(isset($message)):?>
<p class="error"><?php h($message);?></p>
<?php endif;?>

<form method="post">
<textarea name="command" rows="5" cols="60"><?php h(x("command"));?></textarea>
<br/>
<?php hm("db"); ?>:
<select name="db">
<?php foreach ($dbs as $value):?>
<option value="<?php h($value["name"]);?>" <?php if(xn("db")==$value["name"]):?>selected="selected"<?php endif;?>><?php h($value["name"]);?></option>
<?php endforeach;?>
</select> 
<?php hm("format"); ?>:<select name="format">
<?php foreach (array("json" => "JSON", "array" => "Array") as $param=>$value):?>
<option value="<?php h($param);?>" <?php if(x("format")==$param):?>selected="selected"<?php endif;?>><?php h($value);?></option>
<?php endforeach;?>
</select> 
<br/>
<input type="submit" value="<?php hm("execute"); ?>"/> 
</form>

<?php if(isset($ret)):?>

<div style="border:2px #ccc solid;margin-bottom:5px;background-color:#eeefff">
<?php hm("responseserver"); ?>
	<div style="margin-top:5px">
		<?php h($ret);?>
	</div>
</div>
<?php endif; ?>