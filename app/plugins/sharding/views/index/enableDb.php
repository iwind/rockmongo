<h3><?php render_navigation($db); ?> &raquo; Enable Sharding</h3>

<?php if(isset($ret)):?>
<?php render_server_response($ret) ?>
<?php endif; ?>

<input type="button" value="Back to Database" onclick="window.location='<?php render_url("db.index", array( "db" => $db )) ?>'"/>