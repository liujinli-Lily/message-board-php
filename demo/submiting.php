<?php
// ��ֹ�� POST ��ʽ����
if(!isset($_POST['submit'])){
    exit('�Ƿ�����!');
}
// ����Ϣ����
if(get_magic_quotes_gpc()){
	$nickname = htmlspecialchars(trim($_POST['nickname']));
	$email = htmlspecialchars(trim($_POST['email']));
	$content = htmlspecialchars(trim($_POST['content']));
} else {
	$nickname = addslashes(htmlspecialchars(trim($_POST['nickname'])));
	$email = addslashes(htmlspecialchars(trim($_POST['email'])));
	$content = addslashes(htmlspecialchars(trim($_POST['content'])));
}
if(strlen($nickname)>16){
	exit('�����ǳƲ��ó���16���ַ��� [ <a href="javascript:history.back()">�� ��</a> ]');
}
if(strlen($nickname)>60){
	exit('�������䲻�ó���60���ַ��� [ <a href="javascript:history.back()">�� ��</a> ]');
}

require("./conn.php");
require("./function.php");

$createtime = time();
$ip = get_client_ip();
// ����д����
$insert_sql = "INSERT INTO guestbook(nickname,email,face,content,createtime,clientip)VALUES";
$insert_sql .= "('$nickname','$email',$_POST[face],'$content',$createtime,'$ip')";

if(mysql_query($insert_sql)){
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312">
<meta http-equiv="Refresh" content="2;url=index.php">
<link rel="stylesheet" type="text/css" href="style/style.css" />
<title>���Գɹ�</title>
</head>
<body>
<div class="refresh">
<p>���Գɹ����ǳ���л�������ԡ�<br />���Ժ�ҳ�����ڷ���...</p>
</div>
</body>
</html>
<?php
} else {
	echo '����ʧ�ܣ�',mysql_error(),'[ <a href="javascript:history.back()">�� ��</a> ]';
}
?>