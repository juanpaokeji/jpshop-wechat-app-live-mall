<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
<!--    <link rel="stylesheet" href="https://cdn.bootcss.com/bootstrap/4.0.0-beta/css/bootstrap.min.css">-->
<!--    <script src="/js/jquery.min.js"></script>-->
    <script src="https://cdn.bootcss.com/jquery/1.12.4/jquery.min.js"></script>
    <script src="/js/ajax.js"></script>
</head>
<body>
<div class="container" style="padding: 15px 0;">
    <div>
        <ul>
            <p>短信签名</p>
            <li><input type="file" name="img" id="img"/></li>
            <li><button onclick="fun('list')">列表</button></li>
            <li><button onclick="fun('single')">查询单条</button></li>
            <li><button onclick="fun('add')">创建</button></li>
            <li><button onclick="fun('delete')">删除</button></li>
            <li><button onclick="fun('update')">更新</button></li>
            <li><button onclick="fun('send')">短信测试</button></li>
        </ul>
    </div>
</div>
<script>
    var base = "http://api.juanpao.com/admin/message/signature";
    function fun(method){
        switch(method){
            case 'list'://列表
                var url = base;
                var data = {};
                _ajax(url,data,"GET");
                break;
            case 'single'://查询单条
                var url = base + "/single?id=138495";
                var data = {};
                _ajax(url,data,"GET");
                break;
            case 'add'://创建
                var formData = new FormData();
                formData.append('text', "卷泡");
                formData.append('remark', "卷泡网络科技有限123123");
                formData.append('fileName', 'img');
                formData.append('img', $('#img')[0].files[0]);
                var url = base;
                //post方式请求
                $.ajax(
                    {
                        url: url,
                        data: formData,
                        headers: {
                            'Access-Token':token
                        },
                        // beforeSend:function(xhr){
                        //     xhr.setRequestHeader("Access-Token", token);
                        // },
                        processData: false,// 告诉jQuery不要去处理发送的数据
                        contentType: false,// 告诉jQuery不要去设置Content-Type请求头
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
            case 'delete'://删除单个
                var url = base + "/delete";
                var data = {id:138498};
                _ajax(url,data,"DELETE");
                break;
            case 'update'://更新
                var formData = new FormData();
                formData.append('id', "139224");
                formData.append('text', "卷泡123");
                formData.append('remark', "公司需要");
                //img两个参数在未上传新图片时不需要append
                formData.append('fileName', 'img');
                formData.append('img', $('#img')[0].files[0]);
                var url = base + "/update";
                //post方式请求
                $.ajax(
                    {
                        url: url,
                        data: formData,
                        headers: {
                            'Content-Type':'application/x-www-form-urlencoded'
                        },
                        processData: false,
                        contentType: false,
                        type: 'PUT',// POST GET
                        error: function(result){
                            alert("error!"+result.responseText);
                        },
                        success: function(res){
                            console.log(res);//自动格式化输出所有 响应 信息
                        }
                    });
                break;
            case 'send'://短信测试
                var url = base + "/test";
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
