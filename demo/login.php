<?php
/*****************************
* login.php ��¼��֤�����ļ�
*****************************/
session_start();

// ע����¼
if($_GET['action'] == 'logout'){
	session_unregister("username");
	exit('<script language="javascript">alert("�˳��ɹ���");self.location = "login.php";</script>');
}

// �����⵽�ѵ�¼��ֱ����ת������ҳ��
if(isset($_SESSION['username'])){
	header("Location: http://".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\')."/admin.php");
	exit;
}

if($_POST){
	require("./conn.php");
	$username = $_POST['username'];
	$password = MD5(trim($_POST['password']));
	$check_result = mysql_query("SELECT uid FROM user WHERE username = '$username' AND password = '$password'");
	if(mysql_fetch_array($check_result)){
		session_register("username");
		header("Location: http://".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\')."/admin.php");
		exit;
	} else {
		echo '<script language="javascript">alert("�������");self.location = "login.php";</script>';
	}
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<link rel="stylesheet" type="text/css" href="style/style.css" />
<title>���Թ����¼</title>
<script language="JavaScript">
<!--

function InputCheck(form1)
{
  if (form1.password.value == "")
  {
    alert("���������롣");
    form1.password.focus();
    return (false);
  }
}
//--!>
</script>
</head>
<body>
<div class="login-form">
<form id="form1" name="form1" method="post" action="login.php" onSubmit="return InputCheck(this)">
<p>
<input type="hidden" name="username" value="admin" />
<label for="password">��������:</label>
<input id="password" name="password" type="password" />
<input type="submit" name="submit" value=" ȷ �� " />
&nbsp;<a href="index.php">�������԰�</a>
</p>
</form>
</div>
</body>
</html>