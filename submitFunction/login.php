<?php
if(!session_id()) session_start();

if($_POST){
    require("../conn.php");
    $username = $_POST['username'];
    $password = $_POST['password'];
    /*mysql_fetch_array() 函数从结果集中取得一行作为关联数组，或数字数组，或二者兼有
        返回根据从结果集取得的行生成的数组，如果没有更多行则返回 false。*/
    $check_reslut = mysql_query("select id from adminuser where username = '$username' and password ='$password'");
    if(mysql_fetch_array($check_reslut)){
        // session_register("username");
        $_SESSION['username']=$username;
        // header("Location: http://".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\')."/admin.php");
        echo json_encode(array('code' => 1, 'data' => '', 'msg' => '登录成功！','url' => './message/admin.php'));
        exit;
    } else {
        // echo '<script language="javascript">alert("密码错误！");self.location = "login.php";</script>';
         echo json_encode(array('code' => 101, 'data' => '', 'msg' => '用户名或密码错误'));

    }
}

?>