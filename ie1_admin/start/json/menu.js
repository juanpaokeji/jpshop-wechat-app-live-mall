{
    "status"
:
    200
        , "message"
:
    ""
        , "data"
:
    [ {
        "name": "users"
        , "title": "用户"
        , "icon": "layui-icon-user"
        , "list": [{
            "name": "staff"
            , "title": "管理员管理"
            , "list": [{
                "name": "list"
                , "title": "员工管理"
            }]
        }]
    }, {
        "name": "set"
        , "title": "系统"
        , "icon": "layui-icon-set"
        , "list": [{
            "name": "system"
            , "title": "基础"
            , "spread": true
            , "list": [{
                "name": "group"
                , "title": "角色管理"
            }, {
                "name": "jurisdiction"
                , "title": "菜单权限管理"
            }, {
                "name": "appJurisdiction"
                , "title": "商城应用权限管理"
            }]
        }, {
            "name": "printing"
            , "title": "打印设置"
            , "list": [{
                "name": "printing_group"
                , "title": "分组管理"
            }, {
                "name": "printing_key"
                , "title": "字段管理"
            }, {
                "name": "printing_temp"
                , "title": "打印模板"
            }]
        }, {
            "jump": "set/decoration/list"
            , "title": "店铺装修模板"
        }]
    }, {
        "name": "cos"
        , "title": "腾讯云"
        , "icon": "layui-icon-video"
        , "list": [{
            "jump": "cos/adminCos"
            , "title": "COS"
        }, {
            "jump": "cos/adminVideo"
            , "title": "视频"
        }, {
            "jump": "cos/adminSms"
            , "title": "SMS设置"
        }]
    }, {
        "name": "op/update"
        , "title": "一键更新"
        , "icon": "layui-icon-download-circle"
    }, {
        "name": "op/intoMini"
        , "title": "进入小程序"
        , "icon": "layui-icon-senior"
    }]
}