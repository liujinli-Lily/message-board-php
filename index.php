<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Document</title>
</head>
<link rel="stylesheet" type="text/css" href="css/style.css">
<link rel="stylesheet" type="text/css" href="./css/bootstrap.css">
<link href="./css/sweetalert.css" rel="stylesheet">
<body>
<div id="guestbook-form">
  <h3 class="m-title">发表留言</h3>
  <form class="form-horizontal m-t" id="commentForm" method="post" target="nm_iframe" onsubmit="return toVaild()">
    <div class="form-group">
        <label class="col-sm-3 control-label">昵&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;称:</label>
        <div class="input-group col-sm-8">
            <input id="nickname" type="text" class="form-control" name="nickname" required="" aria-required="true">
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label">电子邮件：</label>
        <div class="input-group col-sm-8">
            <input id="email" type="text" class="form-control" name="email" required="" aria-required="true">
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label">头&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;像:</label>
        <div class="input-group col-sm-8">
            <input type="radio" name="face" value="1" checked>
            <img src="images/1.gif" />
            <input type="radio" name="face" value="2">
            <img src="images/2.gif" />
            <input type="radio" name="face" value="3">
            <img src="images/3.gif" />
            <input type="radio" name="face" value="4">
            <img src="images/4.gif" />
            <input type="radio" name="face" value="5">
            <img src="images/5.gif" />
            <input type="radio" name="face" value="6">
            <img src="images/6.gif" />
            <input type="radio" name="face" value="7">
            <img src="images/7.gif" />
            <input type="radio" name="face" value="8">
            <img src="images/8.gif" />
            <input type="radio" name="face" value="9">
            <img src="images/9.gif" />
            <input type="radio" name="face" value="10">
            <img src="images/10.gif" />
            <input type="radio" name="face" value="11">
            <img src="images/11.gif" />
            <input type="radio" name="face" value="12">
            <img src="images/12.gif" />
            <input type="radio" name="face" value="13">
            <img src="images/13.gif" />
            <input type="radio" name="face" value="14">
            <img src="images/14.gif" />
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label">留言内容</label>
        <div class="input-group col-sm-8">
            <textarea class="form-control" name="content" id="content" required="" aria-required="true"></textarea>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-8 col-sm-offset-3">
            <button class="btn btn-primary" type="submit">提交</button>
            <span>(请自觉遵守互联网相关政策法规，严禁发布色情、暴力、反动言论) </span>
        </div>
    </div>
  </form>
  <iframe id="id_iframe" name="nm_iframe" style="display:none;"></iframe>
</div><!--container-->


<div class="guestbook-list">
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

        require('./conn.php');
        require('./config.php');

        $p = $_GET['p'] ? $_GET['p']:1;
        $offset = ($p-1)*$pagesize;

        $query_sql = "select * from guestbook ORDER BY id DESC LIMIT  $offset , $pagesize";
        $result = mysql_query($query_sql);
        if(!$result) exit ('查村数据错误'. mysql_error());

        while($volist = mysql_fetch_array($result)){
        ?>
            <li class="np-post topAll">
                <div class="np-tip-newpost"></div>
                <img class="np-avatar popClick" src="images/<?=$volist['face']?>.gif" alt="头像" >
                <div class="np-post-body">
                  <div class="np-post-header">
                    <span class=""><a href="javascript:void(0)" title="<?=$volist['nickname']?>" class="np-user popClick " post_uid="26445173"><?=$volist['nickname']?></a></span>
                    <a href="javascript:void(0)" class="replywho np-icon-reply-weak np-user" style="display:none"></a>
                    <span class="np-time" data="1495525707"><?php echo date("Y-m-d H:i:s",$volist['createtime'])?></span>
                  </div>
                  <div class="np-post-content" data-height="5">
                    <p><?=$volist['content']?></p>
                  </div>
                </div>
            </li>
        <?php
          }
        ?>

    </ul>
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
              echo '&nbsp;<a href="index.php?p=',$i,'">'.$i.'</a>';
            }
          }
        }
        ?>
        </p>
    </div>
</div>


</body>
<script type="text/javascript" src="./js/jquery-2.1.4.min.js"></script>
<script type="text/javascript" src="./js/bootstrap.js"></script>
<script type="text/javascript" src="./js/sweetalert.min.js"></script>
<script src="./js/jquery.validate.min.js"></script>
<script src="./js/messages_zh.min.js"></script>
<script type="text/javascript">
  //表单提交
    function toVaild(){
        var url = "./submitFunction/addMeaaage.php",
            nickname = $('#nickname').val(),
            email = $('#email').val(),
            face = $('input[name="face"]:checked').val(),
            content = $('#content').val();
            $.ajax({
                type:"POST",
                url:url,
                data:{
                  nickname:nickname,
                  email:email,
                  face:face,
                  content:content
                },// 你的formid
                dataType: "json",
                async: false,
                success: function(data) {
                    //关闭加载层
                    console.log(data);
                    if(data.code == 1){
                        sweetAlert({
                            title:"",
                            text: data.msg,
                            type: "success",
                        }, function(){
                            window.location.reload();
                        });
                    }else{
                        swal(data.msg, "", "error");
                    }

                }
            });
        return false;
    }
</script>
</html>