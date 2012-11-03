<h3><?php render_navigation($db); ?> &raquo; <?php hm("authentication"); ?></h3>

<div class="operation">
	<a href="<?php h(url("db.auth", array("db"=>$db))); ?>"><?php hm("users"); ?></a> |
	<a href="<?php h(url("db.addUser", array("db"=>$db))); ?>" class="current"><?php hm("adduser"); ?></a>
</div>

<div>
	<?php if(isset($error)):?><p class="error"><?php h($error);?></p><?php endif;?>
	<form method="post">
	<?php hm("username"); ?>:<br/>
	<input type="text" name="username" value="<?php h(x("username"));?>"/><br/>
	<?php hm("password"); ?>:<br/>
	<input type="password" name="password"/><br/>
	<?php hm("confirm_pass"); ?>:<br/>
	<input type="password" name="password2"/><br/>
	<?php hm("readonly"); ?><br/>
	<input type="checkbox" name="readonly" value="1"/><br/>
	<input type="submit" value="<?php hm("addreplace"); ?>"/>
	</form>
</div>