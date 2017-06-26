<?php
if($_POST){
    require("../conn.php");
    $replytime = time();
    $reply = $_POST['reply'];
    $id = $_POST['id'];
    /*mysql_fetch_array() 函数从结果集中取得一行作为关联数组，或数字数组，或二者兼有
        返回根据从结果集取得的行生成的数组，如果没有更多行则返回 false。*/
    $update_reslut = " ";
    if(mysql_query($update_reslut)){
        echo json_encode(array('code' => 1, 'data' => '', 'msg' => '回复成功',));
        exit;
    } else {
        // echo '<script language="javascript">alert("密码错误！");self.location = "login.php";</script>';
         echo json_encode(array('code' => 101, 'data' => '', 'msg' => '回复失败'));
    }
}
?>