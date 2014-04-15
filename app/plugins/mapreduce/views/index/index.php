<div class="operation">
	<?php render_navigation($db_name, $collection_name); ?> &raquo; MapReduce <a href="<?php url("@mapreduce.index.index", array( "db" => $db_name, "collection" => $collection_name )) ?>">[New]</a>
</div>

<a href="http://www.mongodb.org/display/DOCS/MapReduce" target="_blank">MapReduce Document</a>

<br/>
<?php if(!empty($command)): ?>
<strong>Command</strong>
<blockquote><?php h($command) ?></blockquote>
<?php endif; ?>

<br/>
<?php if(!empty($message)): ?>
<strong>Result</strong>
<p class="message"><?php h($message) ?></p>
<?php endif; ?>
<?php if(!empty($error)): ?>
<strong>Result</strong>
<p class="error"><?php h($error) ?></p>
<?php endif; ?>

<form method="post">
<input type="submit" value="Execute"/>

<table bgcolor="#cccccc" cellpadding="2" cellspacing="1">
	<tr bgcolor="#fffeee">
		<td valign="top">Collection <font color="blue">*</font>:</td>
		<td><input type="text" name="collection" value="<?php h($collection_name); ?>"/></textarea></td>
	</tr>
	<tr bgcolor="#fffeee">
		<td valign="top">Map Function <font color="blue">*</font>:</td>
		<td><textarea name="map_function" rows="5" cols="60"><?php h($map_function) ?></textarea></td>
	</tr>
	<tr bgcolor="#fffeee">
		<td valign="top">Reduce Function <font color="blue">*</font>:</td>
		<td><textarea name="reduce_function" rows="5" cols="60"><?php h($reduce_function) ?></textarea></td>
	</tr>
	<tr bgcolor="#ffffff">
		<td valign="top">Query Filter:</td>
		<td><textarea name="query_filter" rows="5" cols="60"><?php h($query_filter) ?></textarea></td>
	</tr>
	<tr bgcolor="#ffffff">
		<td valign="top">Sort:</td>
		<td><textarea name="sort" rows="5" cols="60"><?php h($sort) ?></textarea></td>
	</tr>
	<tr bgcolor="#ffffff">
		<td valign="top">Limit:</td>
		<td><input type="text" name="limit" value="<?php h($limit) ?>"/></td>
	</tr>
	<tr bgcolor="#ffffff">
		<td valign="top">Out Options:</td>
		<td><textarea name="out_options" rows="5" cols="60"><?php h($out_options) ?></textarea></td>
	</tr>
	<tr bgcolor="#ffffff">
		<td>Keeptemp:</td>
		<td><select name="keeptemp"><option value="false" <?php if($keeptemp=="false"): ?>selected="selected"<?php endif; ?>>FALSE<option value="true" <?php if($keeptemp=="true"): ?>selected="selected"<?php endif; ?>>TRUE</option></select></td>
	</tr>
	<tr bgcolor="#ffffff">
		<td valign="top">Finalize Function:</td>
		<td><textarea name="finalize_function" rows="5" cols="60"><?php h($finalize_function) ?></textarea></td>
	</tr>
	<tr bgcolor="#ffffff">
		<td valign="top">Scope Variables:</td>
		<td><textarea name="scope_vars" rows="5" cols="60"><?php h($scope_vars) ?></textarea></td>
	</tr>
	<tr bgcolor="#ffffff">
		<td>Js Mode:</td>
		<td><select name="jsmode"><option value="false" <?php if($jsmode=="false"): ?>selected="selected"<?php endif; ?>>FALSE<option value="true" <?php if($jsmode=="true"): ?>selected="selected"<?php endif; ?>>TRUE</option></select></td>
	</tr>
	<tr bgcolor="#ffffff">
		<td>Verbose:</td>
		<td><select name="verbose"><option value="false" <?php if($verbose=="false"): ?>selected="selected"<?php endif; ?>>FALSE<option value="true" <?php if($verbose=="true"): ?>selected="selected"<?php endif; ?>>TRUE</option></select></td>
	</tr>
</table>

<input type="submit" value="Execute"/>
</form>

<hr/>
<strong>Usage</strong>
<pre class="javascript" style="font-family:monospace;margin:0px;padding:0px">&nbsp;
db.<span style="color: #660066;">runCommand</span><span style="color: #009900;">(</span>
 <span style="color: #009900;">{</span> mapreduce <span style="color: #339933;">:</span> <span style="color: #339933;">&lt;</span>collection<span style="color: #339933;">&gt;,</span>
   map <span style="color: #339933;">:</span> <span style="color: #339933;">&lt;</span>mapfunction<span style="color: #339933;">&gt;,</span>
   reduce <span style="color: #339933;">:</span> <span style="color: #339933;">&lt;</span>reducefunction<span style="color: #339933;">&gt;</span>
   <span style="color: #009900;">[</span><span style="color: #339933;">,</span> query <span style="color: #339933;">:</span> <span style="color: #339933;">&lt;</span>query filter object<span style="color: #339933;">&gt;</span><span style="color: #009900;">]</span>
   <span style="color: #009900;">[</span><span style="color: #339933;">,</span> sort <span style="color: #339933;">:</span> <span style="color: #339933;">&lt;</span>sorts the input objects using <span style="color: #000066; font-weight: bold;">this</span> key. <span style="color: #660066;">Useful</span> <span style="color: #000066; font-weight: bold;">for</span> optimization<span style="color: #339933;">,</span> like sorting by the emit key <span style="color: #000066; font-weight: bold;">for</span> fewer reduces<span style="color: #339933;">&gt;</span><span style="color: #009900;">]</span>
   <span style="color: #009900;">[</span><span style="color: #339933;">,</span> limit <span style="color: #339933;">:</span> <span style="color: #339933;">&lt;</span>number of objects to <span style="color: #000066; font-weight: bold;">return</span> from collection<span style="color: #339933;">,</span> not supported <span style="color: #000066; font-weight: bold;">with</span> sharding<span style="color: #339933;">&gt;</span><span style="color: #009900;">]</span>
   <span style="color: #009900;">[</span><span style="color: #339933;">,</span> out <span style="color: #339933;">:</span> <span style="color: #339933;">&lt;</span>see output options below<span style="color: #339933;">&gt;</span><span style="color: #009900;">]</span>
   <span style="color: #009900;">[</span><span style="color: #339933;">,</span> keeptemp<span style="color: #339933;">:</span> <span style="color: #339933;">&lt;</span><span style="color: #003366; font-weight: bold;">true</span><span style="color: #339933;">|</span>false<span style="color: #339933;">&gt;</span><span style="color: #009900;">]</span>
   <span style="color: #009900;">[</span><span style="color: #339933;">,</span> finalize <span style="color: #339933;">:</span> <span style="color: #339933;">&lt;</span>finalizefunction<span style="color: #339933;">&gt;</span><span style="color: #009900;">]</span>
   <span style="color: #009900;">[</span><span style="color: #339933;">,</span> scope <span style="color: #339933;">:</span> <span style="color: #339933;">&lt;</span>object where fields go into javascript global scope <span style="color: #339933;">&gt;</span><span style="color: #009900;">]</span>
   <span style="color: #009900;">[</span><span style="color: #339933;">,</span> jsMode <span style="color: #339933;">:</span> <span style="color: #003366; font-weight: bold;">true</span><span style="color: #009900;">]</span>
   <span style="color: #009900;">[</span><span style="color: #339933;">,</span> verbose <span style="color: #339933;">:</span> <span style="color: #003366; font-weight: bold;">true</span><span style="color: #009900;">]</span>
 <span style="color: #009900;">}</span>
<span style="color: #009900;">)</span><span style="color: #339933;">;</span>
&nbsp;</pre>