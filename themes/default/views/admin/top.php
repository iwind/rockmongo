<script language="javascript">
//show manual links
function showManuals(link) {
	var obj = $(link);
	window.parent.setManualPosition(".manual", obj.position().left, obj.position().top + obj.height() + 3);
}

function showServerMenu(link) {
	var obj = $(link);
	window.parent.setManualPosition(".server-menu", obj.position().left, obj.position().top + obj.height() + 3);
}
</script>

<div class="top top-page">
	<div class="left">
		<select name="host" onchange="window.parent.location='<?php h(url("admin.changeHost")); ?>&index='+this.value" title="<?php hm("switch_hosts"); ?>">
		<?php foreach ($servers as $index => $server):?>
		<option value="<?php h($index);?>" <?php if($index == $serverIndex): ?>selected="selected"<?php endif;?>><?php h(isset($server["mongo_name"]) ? $server["mongo_name"] : "");?></option>
		<?php endforeach; ?>
		</select>
		| <a href="#" onclick="showServerMenu(this);return false;"><?php hm("tools");?> <span style="font-size:11px">▼</span></a> <?php if(!is_null($isMaster)): ?>| <?php if($isMaster):?><a href="<?php h(url("server.replication")); ?>" target="right" title="<?php hm("master"); ?>"><?php hm("master"); ?></a><?php else:?><a href="<?php h(url("server.replication")); ?>" target="right" title="<?php hm("slave"); ?>"><?php hm("slave"); ?></a><?php endif;?><?php endif;?>
	</div>
	<div class="right"><?php h($admin);?> | <a href="#" onclick="showManuals(this);return false;"><?php hm("manuals");?> <span style="font-size:11px">▼</span></a> | <a href="<?php h(url("plugins.index")) ?>" target="right">Plugins</a> |  <a href="<?php h($logoutUrl);?>" target="_top"><?php hm("logout"); ?></a> | <?php render_select("language", rock_load_languages(), __LANG__, array( "style" => "width:100px", "onchange" => "window.top.location='index.php?action=admin.changeLang&lang='+this.value" )); ?>  | <a href="<?php h(url("admin.about")); ?>" target="right">RockMongo v<?php h(ROCK_MONGO_VERSION);?></a></div>
	<div class="clear"></div>
</div>