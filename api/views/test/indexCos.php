<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
<!--    <link rel="stylesheet" href="https://cdn.bootcss.com/bootstrap/4.0.0-beta/css/bootstrap.min.css">-->
    <script src="/js/jquery.min.js"></script>
    <script src="/js/ajax.js"></script>
<!--    <script src="https://cdn.bootcss.com/jquery/1.12.4/jquery.min.js"></script>-->
</head>
<body>
<div class="container" style="padding: 15px 0;">
    <form id="form" action="http://192.168.188.236/admin/cos/cos/uploads" method="POST" enctype="multipart/form-data">
        <ul>
            <li><input type="file" name="img" id="img"/></li>
            <li><button onclick="fun('cos')">登录</button></li>
        </ul>
    </form>
</div>
<script>
    var base = "http://192.168.188.236/admin/user/user";
    function fun(method){
        switch(method){
            case 'cos'://cos
                var data = $("#form").serializeArray();
                $.ajax(
                    {
                        data: data,
                        type: 'POST',// POST GET
                        error: function(result){
                            alert("error!"+result.responseText);
                        },
                        success: function(res){
                            console.log(res);//自动格式化输出所有 响应 信息
                        }
                    });
                break;
            default:
                console.log('请求的method参数错误');
        }
    }
</script>
</body>
</html>
