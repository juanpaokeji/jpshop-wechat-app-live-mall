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
    <div>
        <ul>
            <p>后台用户</p>
            <img src="http://juanpao999-1255754174.cos.cn-south.myqcloud.com/180413%2F1523611000.png" />
            <li><button onclick="fun('login')">登录</button></li>
            <li><button onclick="fun('list')">列表</button></li>
            <li><button onclick="fun('single')">查询单条</button></li>
            <li><button onclick="fun('add')">创建</button></li>
            <li><button onclick="fun('delete')">删除</button></li>
            <li><button onclick="fun('update')">更新</button></li>
            <li><button onclick="fun('userList')">角色成员</button></li>
        </ul>
    </div>
</div>
<script>
    var base = "http://192.168.188.236/admin/user";
    function fun(method){
        switch(method){
            case 'login'://登录
                var url = "http://192.168.188.236/admin/user/login";
                var data = {'username':'jys', 'password':'123456', 'code':'twha'};
                $.ajax(
                    {
                        url: url,
                        data: data,
                        // headers: {
                        //     'Access-Token':token
                        // },
                        beforeSend:function(xhr){
                            xhr.setRequestHeader("Access-Token", token);
                        },
                        type: 'POST',// POST GET
                        // dataType: 'json',
                        // async:false,//当值为false时放开会有警告：主线程中同步的 XMLHttpRequest 已不推荐使用，因其对终端用户的用户体验存在负面影响
                        error: function(result){
                            alert("error!"+result.responseText);
                        },
                        success: function(res){
                            console.log(res);//自动格式化输出所有 响应 信息
                        }
                    });
                break;
            case 'list'://列表
                var url = base + '?page=1&limit=10';
                var data = {};
                _ajax(url,data,"GET");
                break;
            case 'single'://查询单条
                var url = base + "/single?id=12";
                var data = {};
                _ajax(url,data,"GET");
                break;
            case 'add'://创建
                var url = base + "/add";
                var data = {username:"sky", password:'skyaini', group_id:0};
                //post方式请求
                _ajax(url,data,"POST");
                break;
            case 'delete'://删除单个
                var url = base + "/delete";
                var data = {id:4};
                _ajax(url,data,"DELETE");
                break;
            case 'update'://更新
                var url = base + "/update";
                var data = {id:2, username:"cs", password:'123456', group_id:0};
                _ajax(url,data,"PUT");
                break;
            case 'userList'://角色成员列表
                var url = base + '/users?group_id=1&page=1&limit=10';
                var data = {};
                _ajax(url,data,"GET");
                break;
            default:
                console.log('请求的method参数错误');
        }
    }
</script>
</body>
</html>
