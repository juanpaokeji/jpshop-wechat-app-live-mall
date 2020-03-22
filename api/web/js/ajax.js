// 通用 ajax 方式请求
var token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJTSEEyNTYifQ__.eyJpYXQiOjE1MjQxOTk5NTcsImV4cCI6MTUyNDI4NjM1NywidWlkIjoiMSJ9.62078d201d05b2f1685c720aaec4fd4577b4e4ad97f5c1cf0270ab730c32d4c5';
function _ajax(url,data,type){
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
        type: type,// POST GET
        // dataType: 'json',
        // async:false,//当值为false时放开会有警告：主线程中同步的 XMLHttpRequest 已不推荐使用，因其对终端用户的用户体验存在负面影响
        error: function(result){
            alert("error!"+result.responseText);
        },
        success: function(res){
            console.log(res);//自动格式化输出所有 响应 信息
        }
    });
}