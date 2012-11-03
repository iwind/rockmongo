<h3><?php render_navigation($db); ?> &raquo; <?php hm("profile"); ?></h3>

<div class="operation">
	<a href="<?php h(url("db.profile", array("db"=>$db))); ?>" class="current"><?php hm("profile"); ?></a> | <a href="<?php h(url("db.profileLevel", array("db"=>$db))); ?>"><?php hm("change_level"); ?></a> |
	<a href="<?php h(url("db.clearProfile", array("db"=>$db))); ?>" onclick="return window.confirm('<?php hm("change_level"); ?> \'<?php h($db); ?>\'?')"><?php hm("clear"); ?></a> 
</div>

<p class="page"><?php h($page); ?></p>

<?php foreach ($rows as $row): ?>
<div style="border:2px #ccc solid;margin-bottom:5px;">
	<?php hm("date"); ?>: <?php h(date("Y-m-d H:i:s", $row["ts"]->sec));?><br/>
	<?php h($row["text"]);?>
</div>
<?php endforeach; ?>

<p class="page"><?php h($page); ?></p>