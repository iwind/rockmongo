<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
<title>RockMongo</title>
<script language="javascript" src="<?php echo dirname($_SERVER['PHP_SELF']) ?>/js/jquery-1.4.2.min.js"></script>

<style type="text/css">
* {font-size:11px; font-family:'Courier New', Arial}
body {margin:0; padding:0}
a { text-decoration:none }

/** common **/
.clear { clear:both}
.page span a { font-weight:bold; color:red}
blockquote { padding:0px; margin:0; border:1px #ccc solid; background-color:#eee }
.error { padding:0px; margin:10px 0; border:1px #ccc solid; background-color:#eee;color:red }
.operation {padding:3px;border-bottom:1px #999 solid;margin-bottom:5px;}
.operation a {font-size:11px;}

/** left **/
.dbs ul {margin-left:20px;padding-left:2px;}

/** collection **/
.query {background-color:#eeefff}
.field_orders p { height:14px }

/** top **/
.top {border-bottom:1px #ccc solid; background-color:#ffe; padding:3px}
.top .left {float:left}
.top .right {float:right}
</style>
</head>
<body>