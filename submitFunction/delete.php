<?php
if(!session_id()) session_start();
    if ($_POST) {
        require("../conn.php");
        $id = $_POST['id'];
        $delete_sql = "DELETE FROM guestbook WHERE id = '$id'";
        if(mysql_query($delete_sql)){
            echo json_encode(array('code' => 1, 'data' => '', 'msg' => '删除成功！'));
        } else {
            echo json_encode(array('code' => 2, 'data' => '', 'msg' => '删除失败！'));
        }
    }
?>