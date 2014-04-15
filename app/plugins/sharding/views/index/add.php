<div class="operation">
	<?php render_server_menu("@sharding.index.index"); ?>
</div>

<?php if(isset($error)):?>
<p class="error"><?php h($error);?></p>
<?php endif;?>
<?php if(isset($message)):?>
<p class="message"><?php h($message);?></p>
<?php endif;?>

<form method="post">
<table>
	<tr>
		<td>Replica Set Name</td>
		<td><input type="text" name="replica_name" size="30" value="<?php h($replica_name) ?>"/></td>
	</tr>
	<tr>
		<td>Server:</td>
		<td><input type="text" name="server" size="30" value="<?php h($server) ?>"/></td>
	</tr>
	<tr>
		<td>Port:</td>
		<td><input type="text" name="port" size="30" value="<?php h($port) ?>"/></td>
	</tr>
	<tr>
		<td>Name(Optional):</td>
		<td><input type="text" name="name" size="30" value="<?php h($name) ?>"/></td>
	</tr>
	<tr>
		<td>Max Size(Optional):</td>
		<td><input type="text" name="max_size" size="30" value="<?php h($max_size) ?>"/>M</td>
	</tr>
	<tr>
		<td colspan="2"><input type="submit" value="Add"/> <input type="button" value="Back to List" onclick="window.location='<?php render_url("index") ?>'"/></td>
	</tr>	
</table>
</form>

<?php if(isset($ret)):?>
<?php render_server_response($ret) ?>
<?php endif; ?>