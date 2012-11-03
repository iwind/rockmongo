<h3><?php render_navigation($db); ?> &raquo; <?php hm("repair_database"); ?></h3>

<div style="border:2px #ccc solid;margin-bottom:5px;background-color:#eeefff">
<?php hm("response_from_server"); ?>:
	<div style="margin-top:5px">
		<?php h($ret);?>
	</div>
</div>