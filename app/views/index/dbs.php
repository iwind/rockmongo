<script language="javascript">
function highlightCollection(name) {
	var collections = $(".collections");
	collections.find("li").each(function () {
		var a = $(this).find("a");
		if (a.text() == name) {
			a.css("font-weight", "bold");
			a.css("color", "green");
		}
		else {
			a.css("font-weight", "normal");
			a.css("color", "");
		}
	});
}

</script>

<div style="background-color:#eeefff;height:100%">
	<div style="margin-left:20px;margin-bottom:7px"><strong><a href="<?php h(url("server"));?>" target="right"><?php hm("server"); ?></a></strong></div>
	<div style="margin-left:20px;margin-bottom:2px"><strong><a href="<?php h(url("databases"));?>" target="right"><?php hm("databases"); ?></a>:</strong></div>
	<ul class="dbs">
		<?php foreach ($dbs as $db) : ?>
		<li><a href="<?php echo $baseUrl;?>&db=<?php h($db["name"]);?>" <?php if ($db["name"] == x("db")): ?>style="font-weight:bold"<?php endif;?> onclick="window.parent.frames['right'].location='<?php h(url("db",array("db"=>$db["name"])));?>'"><?php echo $db["name"];?></a><?php if($db["collectionCount"]>0):?> (<?php h($db["collectionCount"]); ?>)<?php endif;?>
			<ul class="collections">
				<?php if($db["name"] == x("db")): ?>
					<?php if (!empty($tables)):?>
						<?php foreach ($tables as $table => $count) :?>
						<li><a href="<?php h(url("collection", array( "db" => $db["name"], "collection" => $table ))); ?>" target="right"><?php h($table);?></a> (<?php h($count);?>)</li>
						<?php endforeach; ?>
					<?php else:?>
						<li>No collections yet</li>
					<?php endif;?>
				<?php endif; ?>
				<?php if ($db["name"] == x("db")):?>
				<li><a href="<?php h(url("newCollection", array( "db" => $db["name"] ))); ?>" target="right">Create &raquo;</a></li>
				<?php endif;?>
			</ul>
		</li>
		<?php endforeach; ?>
	</ul>
	
</div>