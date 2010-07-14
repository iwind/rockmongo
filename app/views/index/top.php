	<div class="top">
		<div class="left">mongodb://<?php echo $server["host"];?>:<?php echo $server["port"];?>  <a href="<?php echo $logoutUrl;?>" target="_top">Logout</a></div>
		<div class="right"><a href="<?php h(url("about")); ?>" target="right">v<?php h(ROCK_MONGO_VERSION);?></a></div>
		<div class="clear"></div>
	</div>
