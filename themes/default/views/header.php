<!DOCTYPE HTML>
<html lang = "en-US">
<head>
	<meta charset = "UTF-8">
	<title>RockMongo</title>
	<script src = "js/jquery-1.8.2.min.js"></script>
	<script src = "js/jquery.textarea.js"></script>
	<link href = 'http://fonts.googleapis.com/css?family=Ubuntu|Inconsolata' rel = 'stylesheet' type = 'text/css'>
	<link rel = "stylesheet" href = "<?php render_theme_path() ?>/css/style.css" type = "text/css" media = "all"/>
	<?php render_page_header(); ?>
	<script language = "javascript">
		$(function ()
		{
			$(document).click(window.parent.hideMenus);
			if ($("textarea").length > 0)
			{
				$("textarea").tabby();
			}
		});
	</script>