<?php
header("Content-type:text/html;charset=utf-8");
session_start();

class login{
    // 如果检测到已登录，直接跳转至管理页面
    /*if(isset($_SESSION['username'])){
        exit('<script language="javascript">self.location = "login.php";</script>');
        // header("Location: http://".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\')."/admin.php");
        // exit;
    }*/
    public function index()
    {
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
        # code...
    }

    public function logout(){
        // 注销登录
        if($_GET['action'] == 'logout'){
            // session_unregister("username");
            session_unset();//free all session variable
            session_destroy();//销毁一个会话中的全部数据
            setcookie(session_name(),'',time()-3600);//销毁与客户端的卡号
            echo json_encode(array('code' => 1, 'data' => '', 'msg' => '退出成功！','url' => './login.php'));

            exit('<script language="javascript">alert("退出成功！");self.location = "login.php";</script>');
        }
    }

    public function testdelete(){
        if ($_POST) {
            $id = $_POST['id'];
            $reslut=msql_query("delete from guestbook where id='$id'");
             if(mysql_query($reslut)){
                echo json_encode(array('code' => 1, 'data' => '', 'msg' => '删除成功！'));
            } else {
                echo json_encode(array('code' => 2, 'data' => '', 'msg' => '删除失败！'));
            }
        }
    }
}
$my_done = new login;
$login = $my_done ->index();
$testdelete = $my_done ->testdelete();

?>