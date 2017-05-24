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
<!DOCTYPE >
<html >
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="css/style.css">
<link rel="stylesheet" type="text/css" href="./css/bootstrap.css">
<link href="./css/sweetalert.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="./css/layer.css">
<link rel="stylesheet" type="text/css" href="style/style.css" />
<title>留言管理</title>
</head>
<style type="text/css">
    #guestbook{
        width: 80%;
        margin: 0 auto;
    }
</style>
<body>
<div id="container">
<div id="guestbook"><!--留言列表-->
<h3>留言列表</h3>
<!-- <span>登录名：<?php echo $_SESSION['nickname'] ?></span> -->
<a href="./submitFunction/login.php/logout?action=logout">注销登录</a>
    <ul class="post-list np-comment-list">
        <!-- <li class="np-post topAll ">
            <div class="np-tip-newpost"></div>
            <img class="np-avatar popClick" src="" alt="头像" >
            <div class="np-post-body">
              <div class="np-post-header">
                <a href="javascript:void(0)" class="np-btn np-btn-report report" style="display: none;">举报</a>
                <span class=""><a href="javascript:void(0)" title="电闪雷鸣" class="np-user popClick " post_uid="26445173">电闪雷鸣</a></span>
                <a href="javascript:void(0)" class="replywho np-icon-reply-weak np-user" style="display:none"></a>
                <span class="np-time" data="1495525707">44分钟前</span>
              </div>
              <div class="np-post-content" data-height="5">
                <p>怎么这么多人说不应该帮，还说她父母的坏话啊？男女平等是对的！可他们必定是父母啊！三十多岁了，应该多少有些钱，多少帮一点，是你的心意！他们那时可能有点偏心，可他们也肯定有难处！可那么难，还让你上了大学，其实他们还是爱你的！要学会感恩！！！</p>
              </div>
              <div class="np-post-footer">
                <a href="javascript:void(0)" class="np-btn np-btn-upvote upvote" id="upvote_6272689458590063243">(<em>11</em>)</a>
                <a href="javascript:void(0)" class="np-btn np-btn-cvote cpvote np-tip-newpost1" id="cpvote_6272689458590063243">(<em>0</em>)</a>
                <a href="javascript:void(0)" class="np-btn np-btn-reply reply">回复</a>
                <a class="np-postlink np-btn" targetid="1950568924" commentid="6272689458590063243" parentid="0" href="javascript:void(0)"><img src="http://mat1.gtimg.com/www/niuping2013/postframe/transparent.gif">查看回复(5)</a>
                <a href="javascript:void(0)" class="np-btn np-btn-newreply"></a>
              </div>
            </div>
            <ul class="children"></ul>
        </li> -->

        <?php
        // 引用相关文件
        require("./conn.php");
        require("./config.php");
        require('./submitFunction/login.php');

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
        <li class="np-post topAll ">
            <div class="np-tip-newpost"></div>
            <img class="np-avatar popClick" src="./images/<?php echo $gb_array['face'] ?>.gif" alt="头像" >
            <div class="np-post-body">
              <div class="np-post-header">
                <span class="">
                    <a href="javascript:void(0)" title="<?php echo $gb_array['nickname'] ?>" class="np-user popClick "><?php echo $gb_array['nickname'] ?></a>
                </span>
                <span class="np-time" ><?php echo date("Y-m-d H:i:s",$gb_array['createtime'])  ?></span>
                <a class="j-delete" data-id="<?php echo $gb_array['id']?>" >删除留言</a>
              </div>
              <div class="np-post-content" data-height="5">
                <p><?php echo $gb_array['content'] ?></p>
              </div>
              <div class="np-post-footer">
                <a href="javascript:void(0)" class="np-btn np-btn-reply j-reply">回复</a>
              </div>
            </div>
            <ul class="children"></ul>
        </li>
        <?php
        }   //while循环结束
        ?>
    </ul>
 <!-- <form id="form1" name="form1" method="post" action="reply.php">
        <p><label for="reply">回复本条留言:</label></p>
        <textarea id="reply" name="reply" cols="40" rows="5"></textarea>
        <p>
            <input name="id" type="hidden" value="" />
            <input type="submit" name="submit" value="回复留言" />
        </p>
    </form> -->
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
<script type="text/javascript" src="./js/jquery-2.1.4.min.js"></script>
<script type="text/javascript" src="./js/bootstrap.js"></script>
<script type="text/javascript" src="./js/sweetalert.min.js"></script>
<script type="text/javascript" src="./js/layer.min.js"></script>
<script src="./js/jquery.validate.min.js"></script>
<script src="./js/messages_zh.min.js"></script>
<script type="text/javascript">
    $(".j-reply").click(function(event) {
        /* Act on the event */
    });

    $(".j-delete").click(function(event) {
        /* Act on the event */
        var id = $(this).data('id'),
            $parents=$(this).parents("li");
        layer.confirm('确认删除此条信息?', {icon: 3, title:'提示'}, function(index){
            //do something
            $.ajax({
                url: './submitFunction/delete.php',
                type: 'POST',
                dataType: 'json',
                data: {id: id},
                success :function(data){
                    if(data.code==1){
                        sweetAlert({
                            title:"",
                            text: data.msg,
                            type: "success",
                        }, function(){
                            $parents.remove();
                        });
                    }else{
                        swal(data.msg, "", "error");
                    }
                }
            })
            layer.close(index);
        })


    });
</script>
</html>