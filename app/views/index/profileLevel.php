<h3><a href="<?php h(url("databases"));?>"><?php hm("databases"); ?></a> &raquo; <a href="<?php h(url("db", array("db"=>$db))); ?>"><?php h($db);?></a> &raquo; Profile</h3>

<div class="operation">
	<a href="<?php h(url("profile", array("db"=>$db))); ?>"><?php hm("profile"); ?></a> | 
	<a href="<?php h(url("profileLevel", array("db"=>$db))); ?>" class="current">Change level</a> | 
	<a href="<?php h(url("clearProfile", array("db"=>$db))); ?>" onclick="return window.confirm('Are you sure to clear profile on db \'<?php h($db); ?>\'?')"><?php hm("clear"); ?></a> 
</div>

<div>
<form method="post">
<input type="hidden" name="go" value="save_level"/>
Choose current profiling level: 
<br/>
<select name="level" onchange="changeLevel(this)">
<option value="0" <?php if($level==0):?>selected="selected"<?php endif;?>>0 - off</option>
<option value="1" <?php if($level==1):?>selected="selected"<?php endif;?>>1 - log slow operations (>N ms)</option>
<option value="2" <?php if($level==2):?>selected="selected"<?php endif;?>>2 - log all operations</option>
</select><br/>
<div id="slowmsDiv" style="display:none">
	Time cost &gt; <input type="text" name="slowms" size="7" value="<?php h(xi("slowms"));?>"/> ms
</div>
<input type="submit" value="<?php hm("save"); ?>"/>
</form>
</div>

<script language="javascript">
function changeLevel(select) {
	if (select.value == 1) {
		//$("#slowmsDiv").show();
	}
	else {
		$("#slowmsDiv").hide();
	}
}
</script>