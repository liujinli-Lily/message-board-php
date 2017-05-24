<?php
/*****************************
* admin.php 后台管理主页面文件
*****************************/
session_start();
// 未登录则重定向到登陆页面
if(!isset($_SESSION['username'])){
	header("Location: http://".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\')."/login.php");
	exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312">
<link rel="stylesheet" type="text/css" href="style/style.css" />
<title>留言管理</title>
</head>
<body>
<div id="container">
<div id="guestbook"><!--留言列表-->
<h3>留言列表</h3>
<?php
// 引用相关文件
require("./conn.php");
require("./config.php");

// 确定当前页数 $p 参数
$p = $_GET['p']?$_GET['p']:1;
// 数据指针
$offset = ($p-1)*$pagesize;

$query_sql = "SELECT * FROM guestbook ORDER BY id DESC LIMIT  $offset , $pagesize";
$result = mysql_query($query_sql);
// 如果出现错误并退出
if(!$result) exit('查询数据错误：'.mysql_error());
// 循环输出
while($gb_array = mysql_fetch_array($result)){
?>
<div class="guestbook-list">
<p class="guestbook-head">
<img src="images/<?=$gb_array['face']?>.gif" />
<span class="bold"><?=$gb_array['nickname']?></span> <span class="guestbook-time">[<?=date("Y-m-d H:i:s", $gb_array['createtime'])?>]</span><span> ID号：<?=$gb_array['id']?> 留言者IP：<?=$gb_array['clientip']?> <a href="reply.php?action=delete&id=<?=$gb_array['id']?>">删除留言</a> <a href="login.php?action=logout">注销登录</a></span></p>
<p class="guestbook-content"><?=nl2br($gb_array['content'])?></p>
<form id="form1" name="form1" method="post" action="reply.php">
<p><label for="reply">回复本条留言:</label></p>
<textarea id="reply" name="reply" cols="40" rows="5"><?=$gb_array['reply']?></textarea>
<p>
<input name="id" type="hidden" value="<?=$gb_array['id']?>" />
<input type="submit" name="submit" value="回复留言" />
</p>
</form>
</div>
<?php
}	//while循环结束
?>
<div class="guestbook-list guestbook-page">
<p>
<?php
//计算留言页数
$count_result = mysql_query("SELECT count(*) FROM guestbook");
$count_array = mysql_fetch_array($count_result);
$pagenum = ceil($count_array['count(*)']/$pagesize);
echo '共 ',$count_array['count(*)'],' 条留言';
if ($pagenum > 1) {
	for($i=1;$i<=$pagenum;$i++) {
		if($i==$p) {
			echo '&nbsp;[',$i,']';
		} else {
			echo '&nbsp;<a href="admin.php?p=',$i,'">'.$i.'</a>';
		}
	}
}
?>
</p>
</div>
</div><!--留言列表结束-->


</div><!--container-->
</body>
</html>