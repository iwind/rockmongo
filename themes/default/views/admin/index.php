<html>
<head>
	<title>RockMongo</title>
	<!-- Base Jquery -->
	<script language = "javascript" type = "text/javascript" src = "js/jquery-1.8.2.min.js"></script>
	<script language = "javascript" type = "text/javascript" src = "js/jquery-ui-1.8.4.custom.min.js"></script>

	<!--JQuery Layout -->
	<script language = "javascript" type = "text/javascript" src = "js/jquery.layout.min-1.3.0.js"></script>
	<link type = "text/css" href = "<?php render_theme_path() ?>/css/layout-default-1.3.0.css" rel = "stylesheet"/>
</head>
<body>
<!-- top bar -->
<header class="row">
	<aside class="logo threecol">
		<a href = "/">RokMongo</a>
	</aside>
	<nav class="top-pane ninecol last">
		<select name = "host" onchange = "window.parent.location='<?php h(url("admin.changeHost")); ?>&index='+this.value" title = "<?php hm("switch_hosts"); ?>">
			<?php foreach ($servers as $index => $server): ?>
			<option value = "<?php h($index);?>" <?php if ($index == $serverIndex): ?>selected="selected"<?php endif;?>><?php h(isset($server["mongo_name"]) ? $server["mongo_name"] : "");?></option>
			<?php endforeach; ?>
		</select>
		| <a href = "#" onclick = "showServerMenu(this);return false;"><?php hm("tools");?>
		<span style = "font-size:11px">▼</span></a> <?php if (!is_null($isMaster)): ?>| <?php if ($isMaster): ?>
		<a href = "<?php h(url("server.replication")); ?>" target = "right" title = "<?php hm("master"); ?>"><?php hm("master"); ?></a><?php else: ?>
		<a href = "<?php h(url("server.replication")); ?>" target = "right" title = "<?php hm("slave"); ?>"><?php hm("slave"); ?></a><?php endif; ?><?php endif;?>
		</div>
		<div class = "right"><?php h($admin);?> |
			<a href = "#" onclick = "showManuals(this);return false;"><?php hm("manuals");?> <span style = "font-size:11px">▼</span></a>
			| <a href = "<?php h($logoutUrl);?>" target = "_top"><?php hm("logout"); ?></a>
			| <?php render_select("language", rock_load_languages(), __LANG__, array("style" => "width:100px", "onchange" => "window.top.location='index.php?action=admin.changeLang&lang='+this.value")); ?>
			| <a href = "<?php h(url("admin.about")); ?>" target = "right">RockMongo v<?php h(ROCK_MONGO_VERSION);?></a>
	</nav>
</header>
<div class="row main">
	<!-- left bar -->
	<aside class="threecol sidebar box">
		<iframe src = "<?php echo $leftUrl; ?>" name = "left" width = "100%" height = "100%" frameborder = "0" scrolling = "auto" marginheight = "0"></iframe>
	</aside>

	<!--main bar-->
	<article role="main" class="sixcol">
		<!-- menu when "Tools" clicked -->
		<iframe src = "<?php echo $rightUrl; ?>" name = "right" width = "100%" height = "100%" frameborder = "0" marginheight = "0" scrolling = "auto"></iframe>
	</article>
	<!-- right bar -->
	<aside class="threecol last sidebar box">

		<nav class = "server-menu">
			<a href = "<?php h(url("server.index")); ?>" target = "right"><?php hm("server"); ?></a><br/>
			<a href = "<?php h(url("server.status")); ?>" target = "right"><?php hm("status"); ?></a> <br/>
			<a href = "<?php h(url("server.databases")); ?>" target = "right"><?php hm("databases"); ?></a>
			<a href = "<?php h(url("server.createDatabase")); ?>" target = "right" title = "Create new Database">[+]</a> <br/>
			<a href = "<?php h(url("server.processlist")); ?>" target = "right"><?php hm("processlist"); ?></a> <br/>
			<a href = "<?php h(url("server.command")); ?>" target = "right"><?php hm("command"); ?></a> <br/>
			<a href = "<?php h(url("server.execute")); ?>" target = "right"><?php hm("execute"); ?></a> <br/>
			<a href = "<?php h(url("server.replication")); ?>" target = "right"><?php hm("master_slave"); ?></a>
		</nav>
	</aside>
</div>

<footer class="row centred">
	<nav class = "manual">
	<?php render_manual_items() ?>
</nav>
</footer>
<!-- quick links -->




</body>
</html>