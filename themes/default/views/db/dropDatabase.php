<h3><?php render_navigation($db); ?> &raquo; <?php hm("dropdatabase"); ?></h3>

<p style="font-size:14px">
	<?php hm("dropwarning"); ?> <?php h($db);?>?
</p>
<p style="font-size:14px">
	<?php hm("dropwarning2"); ?>
</p>
<input type="button" value="<?php hm("yes"); ?>" onclick="window.location='<?php h(url("db.dropDatabase", array("db"=>$db,"confirm"=>1))); ?>'"/> <input type="button" value="<?php hm("back"); ?>" onclick="window.location='<?php h(url("db.index", array("db"=>$db))); ?>'"/>