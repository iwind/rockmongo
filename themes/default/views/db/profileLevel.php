<h3><?php render_navigation($db); ?> &raquo; <?php hm("profile"); ?></h3>

<div class="operation">
	<a href="<?php h(url("db.profile", array("db"=>$db))); ?>"><?php hm("profile"); ?></a> | 
	<a href="<?php h(url("db.profileLevel", array("db"=>$db))); ?>" class="current"><?php hm("change_level"); ?></a> | 
	<a href="<?php h(url("db.clearProfile", array("db"=>$db))); ?>" onclick="return window.confirm('<?php hm("clear_profile"); ?> \'<?php h($db); ?>\'?')"><?php hm("clear"); ?></a> 
</div>

<div>
<form method="post">
<input type="hidden" name="go" value="save_level"/>
<?php echo hm("choose_profiling_level"); ?>: 
<br/>
<select name="level" onchange="changeLevel(this)">
<option value="0" <?php if($level==0):?>selected="selected"<?php endif;?>><?php echo hm("profiling_level1"); ?>: </option>
<option value="1" <?php if($level==1):?>selected="selected"<?php endif;?>><?php echo hm("profiling_level2"); ?></option>
<option value="2" <?php if($level==2):?>selected="selected"<?php endif;?>><?php echo hm("profiling_level3"); ?></option>
</select><br/>
<div id="slowmsDiv" style="display:none">
	<?php echo hm("timecost"); ?> &gt; <input type="text" name="slowms" size="7" value="<?php h(xi("slowms"));?>"/> ms
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