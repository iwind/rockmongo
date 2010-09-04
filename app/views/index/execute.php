<script language="javascript">
function addArgument() {
	var no = $("#arguments").children("div").length;
	$("#arguments").show();
	$("#arguments").append("<div>Argument #<span>" + no +  "</span>[JSON] <a href=\"#\" onclick=\"removeArgument(this);return false;\">Remove</a><br/><textarea name=\"argument[]\" rows=\"5\" cols=\"60\"></textarea><br/></div>");
}

function removeArgument(link) {
	$(link).parent().remove();
	
	//re-order
	var divs = $("#arguments").children("div");
	for (var i=0; i<divs.length; i++) {
		$(divs[i]).find("span").html(i);
	}
}

</script>
<div class="operation">
	<a href="<?php h(url("server")); ?>"><?php hm("server"); ?></a> | 
	<a href="<?php h(url("status")); ?>"><?php hm("status"); ?></a> | 
	<a href="<?php h(url("databases")); ?>"><?php hm("databases"); ?></a> |
	<a href="<?php h(url("processlist")); ?>"><?php hm("processlist"); ?></a> |
	<a href="<?php h(url("command",array("db"=>xn("db")))); ?>"><?php hm("command"); ?></a> |
	<a href="<?php h(url("execute",array("db"=>xn("db")))); ?>" class="current"><?php hm("execute"); ?></a> |
	<a href="<?php h(url("replication")); ?>"><?php hm("master_slave"); ?></a> 
</div>

<a href="http://api.mongodb.org/js/" target="_blank">&raquo; Javascript API</a>


<?php if(isset($message)):?>
<p class="error"><?php h($message);?></p>
<?php endif;?>

<form method="post">
<textarea name="code" rows="20" cols="60"><?php h(x("code"));?></textarea>
<br/>
<div id="arguments" <?php if(empty($arguments)):?>style="display:none"<?php endif;?>>
<?php if(!empty($arguments)):?>
	<?php foreach ($arguments as $index=>$argument): ?>
	<div>
		Argument #<span><?php h($index);?></span>[JSON] <a href="#" onclick="removeArgument(this);return false">Remove</a><br/><textarea name="argument[]" rows="5" cols="60"><?php h($argument);?></textarea><br/>
	</div>
	<?php endforeach;?>
<?php endif;?>
</div>
DB:
<select name="db">
<?php foreach ($dbs as $value):?>
<option value="<?php h($value["name"]);?>" <?php if(xn("db")==$value["name"]):?>selected="selected"<?php endif;?>><?php h($value["name"]);?></option>
<?php endforeach;?>
</select> Argument:<input type="button" onclick="addArgument()" value="Add"/>
<br/>
<input type="submit" value="Execute Code"/> 
</form>

<?php if(isset($ret)):?>

<div style="border:2px #ccc solid;margin-bottom:5px;background-color:#eeefff">
Response from server:
	<div style="margin-top:5px">
		<?php h($ret);?>
	</div>
</div>
<?php endif; ?>