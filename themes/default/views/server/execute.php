<script language="javascript">
function addArgument() {
	var no = $("#arguments").children("div").length;
	$("#arguments").show();
	$("#arguments").append("<div><?php hm("argument"); ?> #<span>" + no +  "</span>[JSON] <a href=\"#\" onclick=\"removeArgument(this);return false;\"><?php hm("remove"); ?></a><br/><textarea name=\"argument[]\" rows=\"5\" cols=\"60\"></textarea><br/></div>");
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
	<?php render_server_menu("execute"); ?>
</div>

<a href="http://api.mongodb.org/js/" target="_blank">&raquo; Javascript API</a>


<?php if(isset($message)):?>
<p class="error"><?php h($message);?></p>
<?php endif;?>

<form method="post">
<textarea name="code" rows="20" cols="80" class="code-editor"><?php h(x("code"));?></textarea>
<br/>
<div id="arguments" <?php if(empty($arguments)):?>style="display:none"<?php endif;?>>
<?php if(!empty($arguments)):?>
	<?php foreach ($arguments as $index=>$argument): ?>
	<div>
		<?php hm("argument"); ?> #<span><?php h($index);?></span>[JSON] <a href="#" onclick="removeArgument(this);return false"><?php hm("remove"); ?></a><br/><textarea name="argument[]" rows="5" cols="60"><?php h($argument);?></textarea><br/>
	</div>
	<?php endforeach;?>
<?php endif;?>
</div>
<?php hm("db"); ?>:
<select name="db">
<?php foreach ($dbs as $value):?>
<option value="<?php h($value["name"]);?>" <?php if(xn("db")==$value["name"]):?>selected="selected"<?php endif;?>><?php h($value["name"]);?></option>
<?php endforeach;?>
</select> <?php hm("argument"); ?>:<input type="button" onclick="addArgument()" value="<?php hm("add"); ?>"/>
<br/>
<input type="submit" value="Execute Code"/> 
</form>

<?php if(isset($ret)):?>

<div style="border:2px #ccc solid;margin-bottom:5px;background-color:#eeefff">
<?php hm("responseserver"); ?>
	<div style="margin-top:5px">
		<?php h($ret);?>
	</div>
</div>
<?php endif; ?>