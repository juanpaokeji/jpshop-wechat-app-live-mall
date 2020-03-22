{
    "status"
:
    200
        , "message"
:
    ""
        , "data"
:
    [{
        "title": "主页"
        , "icon": "layui-icon-home"
        , "list": [{
            "title": "控制台"
            , "jump": "/"
        }]
    }, {
        "name": "users"
        , "title": "用户"
        , "icon": "layui-icon-user"
        , "list": [{
            "name": "merchant"
            , "title": "商户管理"
            , "spread": true
            , "list": [{
                "name": "BusinessManagement_Staff_Management"
                , "title": "角色管理"
            }, {
                "name": "list"
                , "title": "商户列表"
            }, {
                "name": "jurisdiction"
                , "title": "商户权限设置"
            }]
        }, {
            "name": "staff"
            , "title": "管理员管理"
            , "list": [{
                "name": "group"
                , "title": "角色管理"
            }, {
                "name": "list"
                , "title": "员工管理"
            }, {
                "name": "jurisdiction"
                , "title": "管理员权限管理"
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
                "name": "website"
                , "title": "网站设置"
            }, {
                "name": "email"
                , "title": "资料管理"
            }, {
                "name": "permissions"
                , "title": "权限管理"
            }]
        }, {
            "name": "user"
            , "title": "我的设置"
            , "list": [{
                "name": "info"
                , "title": "基本资料"
            }, {
                "name": "password"
                , "title": "修改密码"
            }]
        }, {
            "name": "sms"
            , "title": "短信"
            , "list": [{
                "name": "tempList"
                , "title": "短信模板"
            }, {
                "name": "signList"
                , "title": "短信签名"
            }, {
                "name": "Message_record"
                , "title": "短信列表"
            }]
        }, {
            "jump": "set/config"
            , "title": "全局配置"
        }, {
            "jump": "set/configCategory"
            , "title": "全局配置类目"
        }]
    }, {
        "name": "c"
        , "title": "订单"
        , "icon": "layui-icon-zzfile-text-o"
        , "list": [{
            "name": ""
            , "title": "订单列表"
        }]
    }, {
        "name": "application"
        , "title": "应用管理"
        , "icon": "layui-icon-app"
        , "list": [{
            "jump": "application/category"
            , "title": "应用类目"
        }, {
            "jump": "application/list"
            , "title": "应用列表"
        }, {
            "jump": "application/combo"
            , "title": "套餐管理"
        }, {
            "name": ""
            , "title": "商户应用列表"
        }]
    }, {
        "name": "voucher"
        , "title": "活动"
        , "icon": "layui-icon-zzgift"
        , "list": [{
            "jump": "voucher/type"
            , "title": "抵用券类型"
        }, {
            "jump": "voucher/list"
            , "title": "抵用券"
        }, {
            "jump": "voucher/activity"
            , "title": "抵用券活动"
        }, {
            "name": ""
            , "title": "红包"
        }, {
            "name": ""
            , "title": "组合优惠"
        }, {
            "name": ""
            , "title": "满减优惠"
        }]
    }, {
        "name": "b"
        , "title": "财务"
        , "icon": "layui-icon-rmb"
        , "list": [{
            "name": "vouchersType"
            , "title": "交易明细"
        }, {
            "name": "vouchersList"
            , "title": "提现列表"
        }]
    }, {
        "name": "a"
        , "title": "通知"
        , "icon": "layui-icon-speaker"
        , "list": [{
            "name": ""
            , "title": "订单通知"
        }, {
            "name": ""
            , "title": "服务通知"
        }, {
            "name": ""
            , "title": "公告"
        }]
    }, {
        "name": "wechat"
        , "title": "公众号&小程序"
        , "icon": "layui-icon-login-wechat"
        , "list": [{
            "name": "ApplicationGroup"
            , "title": "商户配置信息查看"
        }]
    }, {
        "name": "model"
        , "title": "模板"
        , "icon": "layui-icon-ok-circle"
    }]
}