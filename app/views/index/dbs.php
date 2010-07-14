<div style="border-right:1px #ccc solid;height:95%;">

	<ul class="dbs">
		<?php foreach ($dbs as $db) : ?>
		<li><a href="<?php echo $baseUrl;?>&db=<?php h($db["name"]);?>" <?php if ($db["name"] == x("db")): ?>style="font-weight:bold"<?php endif;?>><?php echo $db["name"];?></a>
			<ul>
				<?php if (!empty($tables) && $db["name"] == x("db")): ?>
					<?php foreach ($tables as $table) :?>
					<li><a href="<?php h(url("collection", array( "db" => $db["name"], "collection" => $table ))); ?>" target="right"><?php h($table);?></a></li>
					<?php endforeach; ?>
				<?php endif; ?>
				<?php if ($db["name"] == x("db")):?>
				<li><a href="<?php h(url("newCollection", array( "db" => $db["name"] ))); ?>" target="right">New &raquo;</a></li>
				<?php endif;?>
			</ul>
		</li>
		<?php endforeach; ?>
	</ul>
	
</div>