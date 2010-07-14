<?php if (isset($message)):?><p class="error"><?php h($message); ?></p><?php endif;?>

<div style="padding:10px">
	<form method="post">
	<table>
		<tr>
			<td>Host</td>
			<td><select name="host">
			<?php foreach ($servers as $index => $server) : ?>
			<option value="<?php echo $index;?>"><?php echo $server["host"]; ?></option>
			<?php endforeach; ?>
			</select></td>
		</tr>
		<tr>
			<td>Admin</td>
			<td><input type="text" name="username" value="<?php echo $username;?>"/></td>
		</tr>
		<tr>
			<td>Password</td>
			<td><input type="password" name="password"/></td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" value="Login"/></td>
		</tr>
	</table>
	</form>
</div>