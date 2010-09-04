<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
<title>RockMongo</title>
<script language="javascript" src="js/jquery-1.4.2.min.js"></script>

<style type="text/css">
* {font-size:11px; font-family:'Courier New', Arial}
body {margin:0; padding:0}
a { text-decoration:none }

/** common **/
.clear { clear:both}
.page span a { font-weight:bold; color:red}
blockquote { padding:0px; margin:0; border:1px #ccc solid; background-color:#eee }
.error { padding:0px; margin:10px 0; border:1px #ccc solid; background-color:#eee;color:red }
.message { padding:0px; margin:10px 0; border:1px #ccc solid; background-color:#eee;color:green }
.operation {padding:3px;border-bottom:1px #999 solid;margin-bottom:5px;}
.operation a {font-size:11px;}
.operation a.current { font-weight:bold;text-decoration:underline }
.gap {height:20px}
.big {font-size:14px}
h3 {padding-bottom:0;margin-top:0;padding-bottom:3px;border-bottom:1px #cc9 solid;margin-bottom:7px; font-size:12px }
h3 a {font-size:12px}
ul.list { list-style:none; width:600px; margin:0; padding:0; }
ul.list li {float:left; width:200px;}

/** left **/
.dbs { margin:0; padding:0; list-style:none; }
.dbs li { background-color:#eeefff; padding-left:20px; border-bottom:1px #ccc solid }
.dbs ul {padding:0;margin:0;list-style:none;}
.dbs ul li {padding-left:20;border-bottom:0}


/** collection **/
.query {background-color:#eeefff}
.field_orders p { height:14px }

/** top **/
.top {border-bottom:1px #666 solid; background-color:#ccc; }
.top select { height:18px; background-color:#ccc; border:0 }
.top .left {float:left}
.top .right {float:right}


.menu {
  float:right;
  margin-right:100px;
  margin-top:0px;
  background-color:#eee;
  border-left:1px #ccc solid;
  border-top:1px #ccc solid;
  border-right:2px #ccc solid;
  border-bottom:2px #ccc solid;
  padding-left:3px;
  position:absolute;
  display:none;
  width:100px;
}
</style>

<script language="javascript">
$(function () {
	$(document).click(window.parent.hideMenus);
});
</script>
</head>
<body>