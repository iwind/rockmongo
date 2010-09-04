<script language="javascript">
//show manual links
function showManuals(link) {
	var obj = $(link);
	window.parent.setManualPosition(".manual", obj.position().left)
	
}

function showServerMenu(link) {
	var obj = $(link);
	window.parent.setManualPosition(".server-menu", obj.position().left)
}
</script>

<div class="top">
	<div class="left">
		mongodb://<select name="host" onchange="window.parent.location='<?php h(url("changeHost")); ?>&index='+this.value" title="Switch Hosts">
		<?php foreach ($servers as $index => $server):?>
		<option value="<?php h($index);?>" <?php if($index == $serverIndex): ?>selected="selected"<?php endif;?>><?php h($server["host"]);?>:<?php h($server["port"]);?></option> 
		<?php endforeach; ?>
		</select>
		| <a href="#" onclick="showServerMenu(this);return false;"><?php hm("tools");?></a> <?php if(!is_null($isMaster)): ?>| <?php if($isMaster):?><a href="<?php h(url("replication")); ?>" target="right" title="Master">Master</spaan><?php else:?><a href="<?php h(url("replication")); ?>" target="right" title="Slave">Slave</a><?php endif;?><?php endif;?>
	</div>
	<div class="right"><?php h($admin);?> | <a href="#" onclick="showManuals(this);return false;"><?php hm("manuals");?></a> |  <a href="<?php h($logoutUrl);?>" target="_top"><?php hm("logout"); ?></a> | <a href="<?php h(url("about")); ?>" target="right">RockMongo v<?php h(ROCK_MONGO_VERSION);?></a></div>
	<div class="clear"></div>
</div>
