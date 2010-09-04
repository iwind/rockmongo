<h3><a href="<?php h(url("databases"));?>"><?php hm("databases"); ?></a> &raquo; <a href="<?php h(url("db", array("db"=>$db))); ?>"><?php h($db);?></a> &raquo; <?php hm("authentication"); ?></h3>

<div class="operation">
	<a href="<?php h(url("auth", array("db"=>$db))); ?>">Users</a> |
	<a href="<?php h(url("addUser", array("db"=>$db))); ?>" class="current">Add User</a>
</div>

<div>
	<?php if(isset($error)):?><p class="error"><?php h($error);?></p><?php endif;?>
	<form method="post">
	Username:<br/>
	<input type="text" name="username" value="<?php h(x("username"));?>"/><br/>
	Password:<br/>
	<input type="password" name="password"/><br/>
	Confirm Password:<br/>
	<input type="password" name="password2"/><br/>
	Read Only?<br/>
	<input type="checkbox" name="readonly" value="1"/><br/>
	<input type="submit" value="Add or Replace"/>
	</form>
</div>