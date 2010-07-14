 
<frameset rows='25,*'> 
	<frame src='<?php echo $topUrl;?>' name='top' frameborder="0" bordercolor="#999999"> 
	<frameset cols='15%,*'> 
		<frame src='<?php echo $leftUrl;?>' name='left' frameborder="0" bordercolor="#999999" scrolling="auto"> 
		<frame src='<?php echo $rightUrl;?>' name='right' frameborder="0" bordercolor="#999999"> 
	</frameset> 
	<noframes> 
		<h2>frame alert</h2> 
		<p>this document is designed to be viewed using the frames feature.
		if you see this message, you are using a non-frame-capable web client.</p> 
	</noframes> 
</frameset> 
