<?php
if(!session_id()) session_start();
    // 注销登录
    if($_GET['action'] == 'logout'){
        // session_unregister("username");
        session_unset();//free all session variable
        session_destroy();//销毁一个会话中的全部数据
        setcookie(session_name(),'',time()-3600);//销毁与客户端的卡号
        echo json_encode(array('code' => 1, 'data' => '', 'msg' => '退出成功！','url' => './login.php'));

        // exit('<script language="javascript">alert("退出成功！");self.location = "login.php";</script>');
    }
?>