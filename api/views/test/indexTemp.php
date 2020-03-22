<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
<!--    <link rel="stylesheet" href="https://cdn.bootcss.com/bootstrap/4.0.0-beta/css/bootstrap.min.css">-->
<!--    <script src="https://cdn.bootcss.com/jquery/1.12.4/jquery.min.js"></script>-->
    <script src="/js/jquery.min.js"></script>
    <script src="/js/ajax.js"></script>
</head>
<body>
<div class="container" style="padding: 15px 0;">
    <div>
        <ul>
            <p>短信模板</p>
            <li><button onclick="fun('list')">列表</button></li>
            <li><button onclick="fun('single')">查询单条</button></li>
            <li><button onclick="fun('add')">创建</button></li>
            <li><button onclick="fun('delete')">删除</button></li>
            <li><button onclick="fun('update')">更新</button></li>
        </ul>
    </div>
</div>
<script>
    var base = "http://192.168.188.236/admin/message/template";
    function fun(method){
        switch(method){
            case 'list'://列表
                var url = base + "/list";
                var data = {};
                _ajax(url,data,"GET");
                break;
            case 'single'://查询单条
                var url = base + "/single?id=103933";
                var data = {};
                _ajax(url,data,"GET");
                break;
            case 'add'://创建
                var url = base + "/add";
                var data = {title:"测试",remark:"模板备注",text:"请忽略。",type:0};
                //post方式请求
                _ajax(url,data,"POST");
                break;
            case 'delete'://删除单个
                var url = base + "/delete";
                var data = {id:105951};
                _ajax(url,data,"DELETE");
                break;
            case 'update'://更新
                var url = base + "/update";
                var data = {id:103933,title:"身份1",remark:"模板备1231",text:"您的手机验证码：请忽略111。",type:0};
                _ajax(url,data,"PUT");
                break;
            default:
                console.log('请求的method参数错误');
        }
    }
</script>
</body>
</html>
