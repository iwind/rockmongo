<h3><a href="<?php h(url("databases"));?>"><?php hm("databases"); ?></a> &raquo; <a href="<?php h(url("db", array("db"=>$db))); ?>"><?php h($db);?></a> &raquo; Repair Database</h3>

<div style="border:2px #ccc solid;margin-bottom:5px;background-color:#eeefff">
Response from server:
	<div style="margin-top:5px">
		<?php h($ret);?>
	</div>
</div>