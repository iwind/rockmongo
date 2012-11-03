<h3><?php render_navigation($db); ?> &raquo; <?php hm("authentication"); ?></h3>

<div class="operation">
	<a href="<?php h(url("db.auth", array("db"=>$db))); ?>" class="current">Users</a> |
	<a href="<?php h(url("db.addUser", array("db"=>$db))); ?>">Add User</a>
</div>

<table bgcolor="#cccccc" cellpadding="2" cellspacing="1" width="600">
	<tr>
		<th>ID</th>
		<th>User</th>
		<th>Read Only</th>
		<th>Operation</th>
	</tr>
	<?php foreach ($users as $user):?>
	<tr bgcolor="#fffeee">
		<td width="120" ><?php h($user["_id"]);?></td>
		<td><?php h($user["user"]);?></td>
		<th><?php h($user["readOnly"] ? "Y":"");?></th>
		<th><a href="<?php h(url("db.deleteUser", array("db"=>$db,"user"=>$user["user"]))); ?>" onclick="return window.confirm('Are you sure to remove user \'<?php h($user["user"]); ?>\'?')">Remove</a></th>
	</tr>
	<?php endforeach; ?>
</table>