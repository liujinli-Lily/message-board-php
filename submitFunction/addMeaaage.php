<?php
header("Content-type:text/html;charset=utf-8");
    // if(!isset($_POST['submit'])){
    //     exit('非法访问！');
    // }
    // if(get_magic_quotes_gpc()){
        $nickname = trim($_POST['nickname']);
        $email = trim($_POST['email']);
        $content = $_POST['content'];
        $face = $_POST['face'];
    // }
    require("../conn.php");
    // require("./function.php");
    $createtime = time();
    // $ip = get_client_ip();
    // 数据写入库表
    $insert_sql = "INSERT INTO guestbook(nickname,email,face,content,createtime)VALUES";
    $insert_sql .= "('$nickname','$email','$face','$content',$createtime)";

    if(mysql_query($insert_sql)){
        echo json_encode(array('code' => 1, 'data' => '', 'msg' => '留言成功！'));
    } else {
        echo json_encode(array('code' => 2, 'data' => '', 'msg' => '留言失败！'));
        // echo '留言失败：',mysql_error(),'[ <a href="javascript:history.back()">返 回</a> ]';
    }
?>
