<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>留言板管理员登录</title>
</head>
<link rel="stylesheet" type="text/css" href="./css/bootstrap.css">
<link href="./css/sweetalert.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="./css/login.css">
<body class="signin">
<div class="signinpanel">
    <div class="row">
        <div class="col-sm-7">
            <div class="signin-info">
                <div class="logopanel m-b">
                </div>
                <div class="m-b"></div>
                <h4>欢迎使用 <strong>留言板管理</strong></h4>
                <ul class="m-b">
                </ul>
            </div>
        </div>
        <div class="col-sm-5">
            <form method="post">
                <p class="m-t-md" id="err_msg">登录到留言板管理</p>
                <input type="text" class="form-control uname" placeholder="用户名" id="username">
                <input type="password" class="form-control pword m-b" placeholder="密码" id="password" required="" aria-required="true">
                <input class="btn btn-success btn-block" id="login_btn" value="登录">
                <a href="index.php">返回留言板</a>
            </form>
        </div>
    </div>

</div>

</body>
<script type="text/javascript" src="./js/jquery-2.1.4.min.js"></script>
<script type="text/javascript" src="./js/bootstrap.js"></script>
<script src="./js/jquery.validate.min.js"></script>
<script src="./js/messages_zh.min.js"></script>
<script type="text/javascript" src="./js/sweetalert.min.js"></script>
<script type="text/javascript">
    document.onkeydown=function(event){
        var e = event || window.event || arguments.callee.caller.arguments[0];
        if(e && e.keyCode==13){ // enter 键
            $('#login_btn').click();
        }
    };
    var lock = false;
    $('#login_btn').click(function(){
        var username = $('#username').val(),
            password = $('#password').val();
        if(username==""){
            swal("登录名不能为空", "", "error");
            return false;
        }
        if(password==""){
            swal("密码不能为空", "", "error");
            return false;
        }
        $('#login_btn').removeClass('btn-success').addClass('btn-danger').val('登陆中...');
        $.ajax({
            url: './submitFunction/login.php/index',
            type: 'POST',
            dataType: 'json',
            data: {'username':username, 'password':password},
            beforeSend:function(){
                $('#login_btn').val('登录').removeClass('btn-danger').addClass('btn-success');
            },
        })
        .done(function(data) {
            if(data.code==1){
                // window.location.href=data.data;
                sweetAlert({
                    title:"",
                    text: data.msg,
                    type: "success",
                }, function(){
                    window.location.href='./admin.php';
                });
            }else{
                $('#err_msg').show().html("<span style='color:red'>"+data.msg+"</span>");
                return;
            }
        })
        .fail(function() {
            console.log("error");
        })
        .always(function() {
            console.log("complete");
        });
    });
</script>
</html>