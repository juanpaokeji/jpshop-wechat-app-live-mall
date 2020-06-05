<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';
$function = require __DIR__ . '/function.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
        '@Tools' => '@vendor/Tools',
        '@Imagine' => '@vendor/Imagine',
        '@Psr/SimpleCache' => '@vendor/psr/simple-cache/src',
        '@PhpOffice' => '@vendor/phpoffice',
    ],
    'components' => [
        'request' => [
// !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'm80cj82AOO9hssjT-po-uk8yiYOT7mvI',
            'enableCookieValidation' => false, //禁用CSRF令牌验证
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false, //隐藏index.php
//            'enableStrictParsing' => false,
            'suffix' => '', //后缀
            'rules' => [
                'GET model' => 'model/list', //系统配置列表
                'GET model/<id>' => 'model/single', //系统配置单条
                'POST model' => 'model/add', //系统配置新增
                'PUT model/<id>' => 'model/update', //系统配置更新
                'DELETE model/<id>' => 'model/delete', //系统配置删除
//系统配置
                'GET configs' => 'admin/config/config/list', //系统配置列表
                'GET configs/<id>' => 'admin/config/config/single', //系统配置单条
                'POST configs' => 'admin/config/config/add', //系统配置新增
                'PUT configs/<id>' => 'admin/config/config/update', //系统配置更新
                'DELETE configs/<id>' => 'admin/config/config/delete', //系统配置删除
//系统配置类目表
                'GET configCategorys' => 'admin/config/category/list', //系统配置列表
                'GET configCategorys/<id>' => 'admin/config/category/single', //系统配置单条
                'POST configCategorys' => 'admin/config/category/add', //系统配置新增
                'PUT configCategorys/<id>' => 'admin/config/category/update', //系统配置更新
                'DELETE configCategorys/<id>' => 'admin/config/category/delete', //系统配置删除
//角色
                'GET groups' => 'admin/user/group/list', //角色列表
                'POST groups' => 'admin/user/group/add', //角色新增
                'GET groups/<id>' => 'admin/user/group/single', //角色单条
                'PUT groups/<id>' => 'admin/user/group/update', //角色更新
                'DELETE groups/<id>' => 'admin/user/group/delete', //角色删除
                'GET groups/rule/<id>' => 'admin/user/group/rule', //获取角色权限
                'GET groups/user/<id>' => 'admin/user/group/users', //获取角色用户信息
//权限
                'GET rules' => 'admin/user/rule/list', //权限列表
                'GET rules/<id>' => 'admin/user/rule/single', //权限单条
                'GET rules/menus/<id>' => 'admin/user/rule/menu', //权限菜单
                'POST rules' => 'admin/user/rule/add', //权限新增
                'PUT rules/<id>' => 'admin/user/rule/update', //权限更新
                'DELETE rules/<id>' => 'admin/user/rule/delete', //权限删除
//商户
                'GET merchants' => 'admin/user/merchant/list', //商户列表
                'POST merchants' => 'admin/user/merchant/add', //商户新增
                'PUT merchants/<id>' => 'admin/user/merchant/update', //商户更新
                'GET merchants/<id>' => 'admin/user/merchant/single', //商户单条
                'DELETE merchants/<id>' => 'admin/user/merchant/delete', //商户删除
                'GET merchants/wx/<id>' => 'admin/user/merchant/wx', //获取商户微信配置
                'GET merchants/ali/<id>' => 'admin/user/merchant/ali', //获取商户支付宝配置
                'PUT merchants/updatewx/<id>' => 'admin/user/merchant/updatewx', //商户微信更新
                'PUT merchants/updateali/<id>' => 'admin/user/merchant/updateali', //商户支付宝更新
                'PUT merchants/secret/<id>' => 'admin/user/merchant/secret', //商户密匙更新

//
//APP应用列表
                'GET apps' => 'admin/app/app/list', //APP应用列表
                'GET apps/<id>' => 'admin/app/app/single', //APP应用单条
                'POST apps' => 'admin/app/app/add', //APP应用新增
                'PUT apps/<id>' => 'admin/app/app/update', //APP应用更新
                'DELETE apps/<id>' => 'admin/app/app/delete', //APP应用删除
//
//APP应用类目
                'GET categorys' => 'admin/app/category/list', //APP应用类目列表
                'GET categorys/<id>' => 'admin/app/category/single', //APP应用类目单条
                'POST categorys' => 'admin/app/category/add', //APP应用类目新增
                'PUT categorys/<id>' => 'admin/app/category/update', //APP应用类目更新
                'DELETE categorys/<id>' => 'admin/app/category/delete', //APP应用类目删除
//APP应用套餐
                'GET combos' => 'admin/app/combo/list', //APP应用套餐列表
                'GET combos/<id>' => 'admin/app/combo/single', //APP应用套餐单条
                'POST combos' => 'admin/app/combo/add', //APP应用套餐新增
                'PUT combos/<id>' => 'admin/app/combo/update', //APP应用套餐更新
                'DELETE combos/<id>' => 'admin/app/combo/delete', //APP应用套餐删除
//抵用卷活动
                'GET vouacts' => 'admin/voucher/channel/list', //抵用卷类型列表
                'GET vouacts/<id>' => 'admin/voucher/channel/single', //抵用卷类型单条
                'POST vouacts' => 'admin/voucher/channel/add', //抵用卷类型新增
                'PUT vouacts/<id>' => 'admin/voucher/channel/update', //抵用卷类型更新
                'DELETE vouacts/<id>' => 'admin/voucher/channel/delete', //抵用卷类型删除
//抵用卷类型
                'GET voutypes' => 'admin/voucher/type/list', //抵用卷类型列表
                'GET voutypes/<id>' => 'admin/voucher/type/single', //抵用卷类型单条
                'POST voutypes' => 'admin/voucher/type/add', //抵用卷类型新增
                'PUT voutypes/<id>' => 'admin/voucher/type/update', //抵用卷类型更新
                'DELETE voutypes/<id>' => 'admin/voucher/type/delete', //抵用卷类型删除
//抵用卷
                'GET vouchers' => 'admin/voucher/voucher/list', //抵用卷列表
                'GET vouchers/<id>' => 'admin/voucher/voucher/single', //抵用卷单条
                'POST vouchers' => 'admin/voucher/voucher/add', //抵用卷新增
                'PUT vouchers/<id>' => 'admin/voucher/voucher/update', //抵用卷更新
                'DELETE vouchers/<id>' => 'admin/voucher/voucher/delete', //抵用卷删除
                //app应用版本
                'GET appVersion' => 'admin/system/version/list', //app应用版本列表
                'GET appVersion/<id>' => 'admin/system/version/single', //app应用版本单条
                'POST appVersion' => 'admin/system/version/add', //app应用版本新增
                'PUT appVersion/<id>' => 'admin/system/version/update', //app应用版本更新
                'DELETE appVersion/<id>' => 'admin/system/version/delete', //app应用版本删除
//
//登录
                'POST adminLogin' => 'admin/user/login',
                'GET loginCaptcha' => 'admin/user/login/captcha',
                //后台用户
                'POST users' => 'admin/user/user/add', //新增
                'DELETE users/<id>' => 'admin/user/user/delete', //删除
                'PUT users/<id>' => 'admin/user/user/update', //修改
                'GET users' => 'admin/user/user/finds', //查所有
                'GET users/<id>' => 'admin/user/user/find', //查单条
                'GET adminInfo' => 'admin/user/user/info', //获取当前登陆人员信息
                'PUT adminInfo' => 'admin/user/user/updateinfo', //修改当前登陆人员信息
                'PUT password' => 'admin/user/user/updatepassword', //修改当前登陆人员密码
//短信签名
                'POST signs' => 'admin/message/signature/add', //新增
                'DELETE signs/<id>' => 'admin/message/signature/delete', //删除
                'PUT signs/<id>' => 'admin/message/signature/update', //修改
                'PUT signs/status/<id>' => 'admin/message/signature/status', //只修改启用状态
                'GET signs' => 'admin/message/signature/finds', //查所有
                'GET signs/<id>' => 'admin/message/signature/find', //查单条
//短信模板
                'POST temps' => 'admin/message/template/add', //新增
                'DELETE temps/<id>' => 'admin/message/template/delete', //删除
                'PUT temps/<id>' => 'admin/message/template/update', //修改
                'PUT temps/status/<id>' => 'admin/message/template/status', //只修改启用状态
                'GET temps' => 'admin/message/template/finds', //查所有
                'GET temps/<id>' => 'admin/message/template/find', //查单条
//后台管理查询商户小程序版本上传信息
                'GET adminVersion' => 'admin/system/version/all', //查单条
                //自定义版权状态修改
                'PUT adminCopyright/<id>' => 'admin/system/version/upd',
                //微信公众号，小程序登陆
                'GET forumLogin' => 'forum/user/login', //来自微信公众号登陆授权
//店铺装修
                'GET decoration' => 'admin/system/decoration/list', //店铺装修列表
                'GET decoration/<id>' => 'admin/system/decoration/single', //店铺装修单条
                'POST decoration' => 'admin/system/decoration/add', //店铺装修新增
                'PUT decoration/<id>' => 'admin/system/decoration/update', //店铺装修更新
                'DELETE decoration/<id>' => 'admin/system/decoration/delete', //店铺装修删除
                //
//社区 《我的》部分配置
                'GET forumApp' => 'forum/user/app', //获取商户key
                'GET forumAppInfo' => 'forum/user/appinfo', //获取app 应用信息
                'GET forumLogin' => 'forum/user/login', //来自微信公众号登陆授权 或者小程序登陆
                'GET forumXcxLogin' => 'forum/user/user', //获取小程序用户信息  获取小程序传过来的加密信息解密并查询用户返回JWT
                'GET wxConfig' => 'forum/user/jssdk', //获取微信公众号个人信息
                'GET myforum' => 'forum/user/userinfo', //获取微信个人信息
                'GET mycollections' => 'forum/collection/list', //我的收藏
                'GET mycomments' => 'forum/comment/list', //我的回帖
                'GET myposts' => 'forum/post/posts', //我的贴子
                'GET mylikes' => 'forum/like/list', //我的点赞
                'GET topPost' => 'forum/post/posttop', //置顶帖子
                'GET posts' => 'forum/post/all', //登陆贴子列表
                'GET posts/<id>' => 'forum/post/single', //帖子查看
                'GET forums' => 'forum/post/list', //未登录贴子列表
                'GET forums/<id>' => 'forum/post/one', //未登录贴子查看
                'POST posts' => 'forum/post/add', //新增贴子
                'POST mycollections' => 'forum/collection/add', //新增收藏
                'POST comments' => 'forum/comment/add', //新增回帖
                'POST likes' => 'forum/like/add', //新增点赞
                'PUT likes/<id>' => 'forum/like/add', //取消点赞
                'POST postUpload' => 'forum/post/uploads', //上传帖子文件
                'GET comments/<id>' => 'forum/comment/all', //回帖子列表
                'GET keywords' => 'forum/keywords/list', //帖子话题列表
                'DELETE myposts/<id>' => 'forum/post/delete', //登陆贴子列表
                'POST wxFile/<id>' => 'forum/uploads/wxfile', ///上传帖子文件
                'GET info' => 'forum/user/info', //wx获取用户信息
                'GET forumUserLatLng' => 'forum/user/useraddress', //坐标转换
                //
//商户后台部分配置
                'GET merchantUsers' => 'merchant/user/user/all', //查所有商户
                'POST merchantSubLogin' => 'merchant/user/login/login',
                'POST merchantLogin' => 'merchant/user/login',
                'GET merchantWxPic' => 'wechat/officialAccount/official-account/wx-pic', //获取公众号二维码
                'POST merchantWacht' => 'wechat/officialAccount/official-account/index', //公众号事件处理
                'GET merchantCheck' => 'merchant/user/login/check', //检查用户是否关注公众号
                'POST merchantBind' => 'merchant/user/login/bind', //账户绑定
                'GET merchantApp' => 'merchant/app/app/list',
                'GET merchantAppOne' => 'merchant/app/app/one', //查询商户可以购买几个应用
                'GET merchantInfo' => 'merchant/user/merchants/single',
                'PUT merchantInfo' => 'merchant/user/merchants/update', //修改
                'GET merchantCombos/<id>' => 'merchant/app/combo/list',
                'GET merchantApps' => 'merchant/app/app/all',
                'GET merchantApps/<id>' => 'merchant/app/app/single',
                'POST merchantPay' => 'merchant/pay/pay/add',
                'GET alipay/<id>' => 'pay/alipay',
                'GET alipayApp/<id>' => 'pay/aliwappay', //app支付
                'GET wxpay/<id>' => 'pay/wechat',
                'POST aliReturnUrl' => 'pay/alipay/return_url',
                'GET wxQuery/<id>' => 'pay/wechat/query',
                //应用小程序版本信息
                'GET merchantVersion' => 'merchant/system/version/list', //
                //插件配置
                'POST merchantUnitPay' => 'merchant/system/unit/index', //模板订单列表
                'GET merchantUnitPay' => 'merchant/system/unit/query', //模板订单列表
                'GET merchantUnit' => 'merchant/system/unit/list', //模板订单列表
                'GET merchantUnits' => 'merchant/system/unit/single', //查询签到总开关
                'PUT merchantUnits/<id>' => 'merchant/system/unit/update', //模板订单更新
                //主题设置
                'GET merchantTheme' => 'merchant/system/theme/single', //主题单条
                'GET merchantThemeLink' => 'merchant/system/theme/link', //主题链接
                'PUT merchantTheme' => 'merchant/system/theme/update', //主题更新
                'GET merchantCopyright' => 'merchant/system/theme/copyright', //查询自定义版权状态
                //商户注册
                'GET merchantsSmsCode' => 'merchant/user/user/sms', //获取验证码
                'POST merchantRegister' => 'merchant/user/user/register', //注册
                'POST merchantPassword' => 'merchant/user/user/password', //修改密码
//    //商户配置
                'DELETE merchantConfig/<id>' => 'system/config/config/delete', //删除
                'PUT merchantConfig' => 'merchant/config/config/update', //修改
                'GET merchantConfig' => 'merchant/config/config/single', //查单条
                'GET merchantCon' => 'merchant/config/config/one', //查单条
//商户应用信息
                'GET merchantAppInfo/<id>' => 'merchant/app/access/single', //APP应用类目单条
                'GET merchantAppInfo' => 'merchant/app/access/one', //APP应用类目单条
                'PUT merchantAppInfo/<id>' => 'merchant/app/access/update', //APP应用类目更新
                'PUT merchantAppInfos/<id>' => 'merchant/app/access/updates', //满减活动更新

                'GET merchantPlugin'=>'merchant/app/access/plugin', //APP应用类目单条

                //商户帮助中心
                'GET merchantAppHelp' => 'merchant/system/app-help/list', //模板订单列表
                'GET merchantAppHelp/<id>' => 'merchant/system/app-help/single', //模板订单单条
                'POST merchantAppHelp' => 'merchant/system/app-help/add', //模板订单新增前台
                'PUT merchantAppHelp/<id>' => 'merchant/system/app-help/update', //模板订单更新前台
                'DELETE merchantAppHelp/<id>' => 'merchant/system/app-help/delete', //模板订单删除
                ////商户帮助分类
                'GET merchantAppHelpCategory' => 'merchant/system/app-help-category/list', //模板订单列表
                'GET merchantAppHelpCategory/<id>' => 'merchant/system/app-help-category/single', //模板订单单条
                'POST merchantAppHelpCategory' => 'merchant/system/app-help-category/add', //模板订单新增前台
                'PUT merchantAppHelpCategory/<id>' => 'merchant/system/app-help-category/update', //模板订单更新前台
                'DELETE merchantAppHelpCategory/<id>' => 'merchant/system/app-help-category/delete', //模板订单删除
//模板订单
                'GET designOrder' => 'merchant/design/order/list', //模板订单列表
                'GET designOrder/<id>' => 'merchant/design/order/single', //模板订单单条
                'POST designOrder' => 'merchant/design/order/add', //模板订单新增前台
                'PUT designOrder/<id>' => 'merchant/design/order/update', //模板订单更新前台
                'PUT designOrderup/<id>' => 'merchant/design/order/up', //模板订单更新后台
                'DELETE designOrder/<id>' => 'merchant/design/order/delete', //模板订单删除
//模板设计
                'GET designConfig' => 'merchant/design/config/single', //模板设计配置文件
                'PUT designConfig' => 'merchant/design/config/update', //模板设计配置文件
                'GET designMaterialAll' => 'merchant/design/material/all', //模板设计列表前台
                'GET designMaterialAlls' => 'merchant/design/material/alls', //模板设计列表前台
                'GET designMaterial' => 'merchant/design/material/list', //模板设计列表后台
                'GET designMaterial/<id>' => 'merchant/design/material/single', //模板设计单条
                'POST designMaterial' => 'merchant/design/material/add', //模板设计新增
                'POST designMaterial/<id>' => 'merchant/design/material/update', //模板设计更新
                'DELETE designMaterial/<id>' => 'merchant/design/material/delete', //模板设计删除
//
                'GET designInfo' => 'merchant/design/info/list', //模板订单列表
                'GET designInfoAll' => 'merchant/design/info/all', //模板订单列表前台
                'GET designInfo/<id>' => 'merchant/design/info/single', //模板订单单条
                'POST designInfo' => 'merchant/design/info/add', //模板订单新增前台
                'PUT designInfo/<id>' => 'merchant/design/info/update', //模板订单更新前台
                'DELETE designInfo/<id>' => 'merchant/design/info/delete', //模板订单删除
//帖子统计
                'GET merchantTotalWeek/<id>' => 'merchant/forum/total/week', //帖子概况
                'GET merchantTotalUser/<id>' => 'merchant/forum/total/user', //帖子概况
                'GET merchantTotalVisit/<id>' => 'merchant/forum/total/visit', //访问统计
                'GET merchantTotalPost/<id>' => 'merchant/forum/total/post', //帖子统计
                //贴子管理用户
                'GET merchantForumUser' => 'merchant/forum/user/single', //贴子管理用户查询
                'PUT merchantForumUser/<id>' => 'merchant/forum/user/update', //贴子管理用户更新
//商户帖子应用
                'POST merchantPost' => 'merchant/forum/post/add', //新增
                'DELETE merchantPost/<id>' => 'merchant/forum/post/delete', //删除
                'PUT merchantPost/<id>' => 'merchant/forum/post/update', //修改
                'PUT merchantPost' => 'merchant/forum/post/updatemore', //修改多条
                'GET merchantPost/<id>' => 'merchant/forum/post/single', //查单条
                'GET merchantPost' => 'merchant/forum/post/list', //查所有
                'PUT merchantPostForum' => 'merchant/forum/forum/config', //更新配置文件
                'GET merchantPostForum' => 'merchant/forum/forum/single', //查询强制话题状态
                'GET merchantIllegally' => 'merchant/forum/forum/illegally', //查询违禁词
//
//帖子积分规则
                'PUT merchantScoreRule' => 'merchant/score/rule/update', //修改
                'GET merchantScoreRule' => 'merchant/score/rule/list', //查所有
//回帖
                'POST merchantForumComment' => 'merchant/forum/comment/add', //新增
                'DELETE merchantForumComment/<id>' => 'merchant/forum/comment/delete', //删除
                'PUT merchantForumComment/<id>' => 'merchant/forum/comment/update', //修改
                'PUT merchantForumComment' => 'merchant/forum/comment/updatemore', //修改多条
                'GET merchantForumComment/<id>' => 'merchant/forum/comment/single', //查单条
                'GET merchantForumComment' => 'merchant/forum/comment/list', //查所有
//
//圈子话题
                'POST merchantKeyWords' => 'merchant/forum/keywords/add', //新增
                'DELETE merchantKeyWords/<id>' => 'merchant/forum/keywords/delete', //删除
                'PUT merchantKeyWords/<id>' => 'merchant/forum/keywords/update', //修改
                'GET merchantKeyWords' => 'merchant/forum/keywords/list', //查所有
                'GET merchantKeyWords/<id>' => 'merchant/forum/keywords/single', //查单条
//用户
                'PUT merchantUser/<id>' => 'merchant/user/user/update', //修改
                'GET merchantUser' => 'merchant/user/user/list', //查所有
                'GET merchantUser/<id>' => 'merchant/user/user/single', //查单条
                'DELETE merchantUser/<id>' => 'merchant/user/user/delete', //删除
//贴吧等级
                'POST merchantKeyLevel' => 'merchant/forum/level/add', //新增
                'DELETE merchantKeyLevel/<id>' => 'merchant/forum/level/delete', //删除
                'PUT merchantKeyLevel/<id>' => 'merchant/forum/level/update', //修改
                'GET merchantKeyLevel' => 'merchant/forum/level/list', //查所有
                'GET merchantKeyLevel/<id>' => 'merchant/forum/level/single', //查单条
                'GET test/<id>' => 'merchant/forum/total/test', //查单条
//管理员商城管理
                'GET adminShopCategory' => 'admin/shop/category/list', //商品类目管理
                'GET adminShopCategoryParent' => 'admin/shop/category/parent', //商品类目父类
                'GET adminShopCategory/<id>' => 'admin/shop/category/single', //商品类目管理单条
                'POST adminShopCategory' => 'admin/shop/category/add', //商品类目管理新增
                'PUT adminShopCategory/<id>' => 'admin/shop/category/update', //商品类目管理更新
                'DELETE adminShopCategory/<id>' => 'admin/shop/category/delete', //商品类目管理删除
                //新闻
                'GET adminNews' => 'admin/system/news/list', //商品类目管理单条
                'GET adminNews/<id>' => 'admin/system/news/single', //商品类目管理单条
                'POST adminNews' => 'admin/system/news/add', //商品类目管理新增
                'PUT adminNews/<id>' => 'admin/system/news/update', //商品类目管理更新
                'DELETE adminNews/<id>' => 'admin/system/news/delete', //商品类目管理删除
//帮助中心分类
                'GET adminHelpCategory' => 'admin/system/helps/list', //商品类目管理单条
                'GET adminHelpCategory/<id>' => 'admin/system/helps/single', //商品类目管理单条
                'POST adminHelpCategory' => 'admin/system/helps/add', //商品类目管理新增
                'PUT adminHelpCategory/<id>' => 'admin/system/helps/update', //商品类目管理更新
                'DELETE adminHelpCategory/<id>' => 'admin/system/helps/delete', //商品类目管理删除
                //帮助中心文章
                'GET adminHelp' => 'admin/system/help/list', //商品类目管理单条
                'GET adminHelp/<id>' => 'admin/system/help/single', //商品类目管理单条
                'POST adminHelp' => 'admin/system/help/add', //商品类目管理新增
                'PUT adminHelp/<id>' => 'admin/system/help/update', //商品类目管理更新
                'DELETE adminHelp/<id>' => 'admin/system/help/delete', //商品类目管理删除
                //新闻
                'GET news' => 'merchant/system/news/list', //商品类目管理单条
                'GET news/<id>' => 'merchant/system/news/single', //商品类目管理单条
                'GET helps' => 'merchant/system/help/list', //商品类目管理单条
                'GET helps/<id>' => 'merchant/system/help/single', //商品类目管理单条


                //腾讯云
                'GET adminCos' => 'admin/system/cos/list',
                'GET adminCos/<id>' => 'admin/system/cos/single',
                'POST adminCos' => 'admin/system/cos/add',
                'PUT adminCos/<id>' => 'admin/system/cos/update',
                'DELETE adminCos/<id>' => 'admin/system/cos/delete',

                'GET adminSms' => 'admin/system/sms/list',
                'GET adminSms/<id>' => 'admin/system/sms/single',
                'POST adminSms' => 'admin/system/sms/add',
                'PUT adminSms/<id>' => 'admin/system/sms/update',
                'DELETE adminSms/<id>' => 'admin/system/sms/delete',

                'GET adminVideo' => 'admin/system/video/list',
                'GET adminVideo/<id>' => 'admin/system/video/single',
                'POST adminVideo' => 'admin/system/video/add',
                'PUT adminVideo/<id>' => 'admin/system/video/update',
                'DELETE adminVideo/<id>' => 'admin/system/video/delete',
//
//商城统计
                'GET merchantShopTotal' => 'merchant/shop/total/total', //权限列表
//签到活动设置
                //'PUT merchantSignInStatus' => 'merchant/shop/signin/status', //用不打 签到插件状态更新
                'GET merchantSignUserAll/<id>' => 'merchant/shop/signin/users', //签到全部用户列表记录
                'GET merchantSignIn' => 'merchant/shop/signin/list', //签到活动
                'GET merchantSignInTime' => 'merchant/shop/signin/time', //签到活动
                'POST merchantSignIn' => 'merchant/shop/signin/add', //签到活动
                'GET merchantSignIn/<id>' => 'merchant/shop/signin/single', //签到活动
                'PUT merchantSignIn/<id>' => 'merchant/shop/signin/update', //签到活动
                'DELETE merchantSignIn/<id>' => 'merchant/shop/signin/delete', //签到活动
                'GET merchantSign/<id>' => 'merchant/shop/signin/sign', //签到列表记录
                'GET merchantSignUser/<id>' => 'merchant/shop/signin/user', //签到单人列表记录
                'GET merchantSignUserPrize' => 'merchant/shop/signin/prize', //签到领取记录
                'PUT merchantSignUserPrize/<id>' => 'merchant/shop/signin/updateprize', //更新签到领取状态备注
//商户
                'GET merchantRules' => 'merchant/system/group/all', //权限列表
                'GET merchantNewRules' => 'merchant/system/group/rules', //权限列表
//员工管理
                'GET merchantSubKefu' => 'merchant/system/user/kefu', //kefu列表
                'GET merchantSubUser' => 'merchant/system/user/list', //员工列表
                'GET merchantSubUser/<id>' => 'merchant/system/user/single', //员工单条
                'POST merchantSubUser' => 'merchant/system/user/add', //员工更新
                'PUT merchantSubUser/<id>' => 'merchant/system/user/update', //员工更新
                'DELETE merchantSubUser/<id>' => 'merchant/system/user/delete', //员工删除
                'PUT merchantYly/<id>' => 'merchant/system/user/ylyupdate', //门店易联云配置更新
                //应用菜单列表
                'GET merchantMenu' => 'merchant/system/menu/list', //菜单列表
                'GET merchantSubMenu' => 'merchant/system/menu/menu', //菜单列表
//角色

                'GET merchantSubGroup' => 'merchant/system/group/list', //角色列表
                'POST merchantSubGroup' => 'merchant/system/group/add', //角色新增
                'GET merchantSubGroup/<id>' => 'merchant/system/group/single', //角色单条
                'PUT merchantSubGroup/<id>' => 'merchant/system/group/update', //角色更新
                'DELETE merchantSubGroup/<id>' => 'merchant/system/group/delete', //角色删除
                'GET merchantSubGroupRule/<id>' => 'merchant/system/group/rule', //获取角色权限
                'GET merchantSubGroupUser<id>' => 'merchant/system/group/users', //获取角色用户信息
//                'GET merchantSubGroup' => 'merchant/system/group/list', //评论列表
//                'GET merchantSubGroup/<id>' => 'merchant/system/group/single', //评论单条
//                'POST merchantSubGroup' => 'merchant/system/group/add', //评论更新
//                'PUT merchantSubGroup/<id>' => 'merchant/system/group/update', //评论更新
//                'DELETE merchantSubGroup/<id>' => 'merchant/system/group/delete', //评论删除
//
//商城管理
                'GET merchantCategoryParent' => 'merchant/shop/category/parent', //商品类目父类
                'GET merchantShopCategory' => 'merchant/shop/category/category', //加载商品类目管理
                'GET merchantCategory' => 'merchant/shop/category/list', //商品类目管理
                'GET merchantCategorys' => 'merchant/shop/category/all', //商品类目管理
                'GET merchantCategory/<id>' => 'merchant/shop/category/single', //商品类目单条
                'POST merchantCategory' => 'merchant/shop/category/add', //商品类目增
                'PUT merchantCategory/<id>' => 'merchant/shop/category/update', //模商品类目更新
                'PUT merchantCategoryStatus/<id>' => 'merchant/shop/category/status', //模商品类目状态
                'DELETE merchantCategory/<id>' => 'merchant/shop/category/delete', //商品类目删除
                'GET merchantCategoryType' => 'merchant/shop/category/type', //商品类目整理后
                'GET merchantCategoryTypeMini' => 'merchant/shop/category/merchanttype', //商品类目父类
                'GET merchantCategoryTypeSub' => 'merchant/shop/category/sub', //商品类目子类
//商户商城商品管理
                'GET merchantGoods' => 'merchant/shop/goods/list', //商品列表
                'GET merchantGoodsQCode/<id>' => 'merchant/shop/goods/qcode', //商品列表
                'GET merchantGoods/<id>' => 'merchant/shop/goods/single', //商品单条
                'POST merchantGoods' => 'merchant/shop/goods/add', //商品新增
                'POST merchantGoodsImg' => 'merchant/shop/goods/uploads', //商品图片上传
                'POST merchantGoodsImgInfo' => 'merchant/shop/goods/uploadsinfo', //商品图片上传
                'POST merchantGoodsVideo' => 'merchant/shop/goods/upload-vod', //商品视频图片上传
                'PUT merchantGoods/<id>' => 'merchant/shop/goods/update', //商品更新
                'PUT merchantGood/<id>' => 'merchant/shop/goods/updates', //商品更新
                'DELETE merchantGoods/<id>' => 'merchant/shop/goods/delete', //商品删除
                'GET merchantGoodsRecycle' => 'merchant/shop/goods/recycle', //回收商品
                'PUT merchantGoodReduction/<id>' => 'merchant/shop/goods/reduction', //恢复商品
                'PUT merchantGoodAudit/<id>' => 'merchant/shop/goods/audit', //供应商商品审核，填写售价
                'GET merchantStock' => 'merchant/shop/goods/stock', //供应商商品审核，填写售价
//
//商城后台订单
                'GET merchantOrderSummary' => 'merchant/shop/order/summary', //订单概述
                'GET merchantOrder' => 'merchant/shop/order/list', //订单列表
                'PUT merchantOrderCancel/<id>' => 'merchant/shop/order/cancel', //取消订单
                'PUT merchantOrder/<id>' => 'merchant/shop/order/update', //订单更新
                'GET merchantSuborder' => 'merchant/shop/order/suborder', //子订单列表
                'PUT merchantSend' => 'merchant/shop/order/send', //订单发货
                'GET merchantOrder/<id>' => 'merchant/shop/order/single', //订单单条
                'POST merchantOrder' => 'merchant/shop/goods/add', //订单新增
                'DELETE merchantOrder/<id>' => 'merchant/shop/goods/delete', //订单删除
                'POST merchantOrderImg' => 'merchant/shop/order/uploads', //订单图片上传
                'PUT merchantOrderLeader' => 'merchant/shop/order/leader', //订单自提点修改
                'GET merchantOrderExpress/<id>' => 'merchant/shop/order/express', //订单快递详情

                'GET merchantLeaderGoods/<id>' => 'merchant/tuan/user/goods-list', //团长商品
                'PUT merchantLeaderGoods/<id>' => 'merchant/tuan/user/goods', //更新团长商品

                'PUT merchantOrderConfim' => 'merchant/tuan/user/goods', //更新团长商品


//订单状态个性化更新
//                'PUT merchantOrderRefuse/<id>' => 'merchant/shop/order/refuse', //确认拒绝操作
//                'PUT merchantOrderAgreeMoney/<id>' => 'merchant/shop/order/agreemoney', //同意只退款
//                'PUT merchantOrderAgreeGoods/<id>' => 'merchant/shop/order/agreegoods', //同意退款退货
                'PUT merchantOrderAfter/<id>' => 'merchant/shop/order/refund', //订单售后状态跟新
                'PUT merchantOrderRefund/<id>' => 'merchant/shop/order/refunds', //订单售后  一键退款
                'PUT merchantOrderRemark' => 'merchant/shop/order/remark', //更新备注
//维权列表
                'GET merchantSale' => 'merchant/shop/order/all', //订单列表
//商城后台
//运费模板
                'GET merchantShopExpress' => 'merchant/shop/express/list', //快递公司列表
                'GET merchantShopExpressCompany' => 'merchant/shop/express/all', //快递公司列表
                'POST merchantShopExpress' => 'merchant/shop/express/add', //快递公司新增
                'PUT merchantShopExpress/<id>' => 'merchant/shop/express/update', //快递公司新增
                'DELETE merchantShopExpress/<id>' => 'merchant/shop/express/delete', //快递公司新增
                'GET merchantShopExpressTemplate' => 'merchant/shop/template/list', //运费模板列表
                'GET merchantShopExpressTemplateAll' => 'merchant/shop/template/all', //运费模板列表
                'GET merchantShopExpressTemplate/<id>' => 'merchant/shop/template/single', //运费模板单条
                'POST merchantShopExpressTemplate' => 'merchant/shop/template/add', //运费模板新增
                'PUT merchantShopExpressTemplate/<id>' => 'merchant/shop/template/update', //运费模板更新
                'DELETE merchantShopExpressTemplate/<id>' => 'merchant/shop/template/delete', //运费模板更新
                'PUT merchantShopExpressTemplates/<id>' => 'merchant/shop/template/updates', //运费模板启用
//评论信息
                'GET merchantComment' => 'merchant/shop/comment/list', //评论列表
                'GET merchantComment/<id>' => 'merchant/shop/comment/single', //评论单条
                'PUT merchantComment/<id>' => 'merchant/shop/comment/update', //评论更新
                'DELETE merchantComment/<id>' => 'merchant/shop/comment/delete', //评论删除
                'GET shopComment/<id>' => 'shop/comment/all', //评论列表
                'GET shopComments/<id>' => 'merchant/shop/comment/list', //我的评论单条
                'GET shopComments/<id>' => 'merchant/shop/comment/single', //我的评论单条
                'PUT shopComments/<id>' => 'merchant/shop/comment/update', //我的评论更新
                'DELETE shopComments/<id>' => 'merchant/shop/comment/delete', //我的评论删除
//优惠卷
//优惠卷类型
                'GET shopVouTypes' => 'merchant/shop/vouchertype/list', //优惠卷类型列表
                'GET shopVouTypesAll' => 'merchant/shop/vouchertype/all', //优惠卷类型列表
                'GET shopVouTypes/<id>' => 'merchant/shop/vouchertype/single', //优惠卷类型单条
                'POST shopVouTypes' => 'merchant/shop/vouchertype/add', //优惠卷类型新增
                'PUT shopVouTypes/<id>' => 'merchant/shop/vouchertype/update', //优惠卷类型更新
                'DELETE shopVouTypes/<id>' => 'merchant/shop/vouchertype/delete', //优惠卷类型删除
//抵用卷
                'GET shopVouchers' => 'merchant/shop/voucher/list', //优惠卷列表
                'GET shopVouchers/<id>' => 'merchant/shop/voucher/single', //优惠卷单条
                'POST shopVouchers' => 'merchant/shop/voucher/add', //优惠卷新增
                'PUT shopVouchers/<id>' => 'merchant/shop/voucher/update', //优惠卷更新
                'DELETE shopVouchers/<id>' => 'merchant/shop/voucher/delete', //优惠卷删除
                //抵用卷配置
                'GET merchantShopConfig' => 'merchant/config/config/config', //优惠卷单条
                'PUT merchantShopConfig' => 'merchant/config/config/configup', //优惠卷更新

                'POST shopVouchersPack' => 'merchant/shop/voucher/pack', //优惠卷新增
//商城后台 banner
                'GET merchantBanner' => 'merchant/shop/banner/list', //banner列表
                'GET merchantBanner/<id>' => 'merchant/shop/banner/single', //banner单条
                'POST merchantBanner' => 'merchant/shop/banner/add', //banner新增
                'PUT merchantBanner/<id>' => 'merchant/shop/banner/update', //banner更新
                'DELETE merchantBanner/<id>' => 'merchant/shop/banner/delete', //banner删除
//商城后台 用户
                'GET merchantShopUsers' => 'merchant/shop/user/list', //会员列表
                'PUT merchantShopUsers/<id>' => 'merchant/shop/user/update', //会员更新
                'DELETE merchantShopUsers/<id>' => 'merchant/shop/user/delete', //会员更新
//售后信息
                'GET merchantAfterInfo' => 'merchant/shop/afterinfo/list', //售后列表
                'GET merchantAfterInfo/<id>' => 'merchant/shop/afterinfo/single', //售后单条
                'POST merchantAfterInfo' => 'merchant/shop/afterinfo/add', //售后新增
                'PUT merchantAfterInfo/<id>' => 'merchant/shop/afterinfo/update', //售后更新
                'DELETE merchantAfterInfo/<id>' => 'merchant/shop/afterinfo/delete', //售后删除

                'POST merchantPayMentCode' => 'merchant/shop/user/payment', //售后新增
//商城前台
                'GET ShopAppInfo' => 'shop/user/appinfo', //banner列表
                'POST shopUpload' => 'shop/order/uploads', //上传帖子文件
//获取banner
                'GET ShopBanner' => 'shop/banner/list', //banner列表
//获取红包
                'GET ShopRedEnvelope' => 'shop/voucher/vouchertype', //红包列表
                'GET ShopVoucher' => 'shop/voucher/list', //我的红包列表
                'GET ShopVoucherSupplier' => 'shop/voucher/voucher', //我的店铺红包
                'POST ShopRedEnvelope' => 'shop/voucher/add', //红包领取
                'POST ShopVoucherReceive' => 'shop/voucher/receive', //红包领取
//商城用户登陆
                'GET shopLogin' => 'shop/user/login', //来自微信公众号登陆授权 或者小程序登陆
                'GET shopXcxLogin' => 'shop/user/user', //获取小程序用户信息  获取小程序传过来的加密信息解密并查询用户返回JWT
                'GET shopConfig' => 'shop/user/jssdk', //获取微信js-sdk
                'GET shopUserInfo' => 'shop/user/info', //获取微信个人信息
                'GET shopUserPhone' => 'shop/user/phone', //获取微信个人信息
                'GET shopUserLatLng' => 'shop/user/useraddress', //坐标转换
                'GET shopUser' => 'shop/user/list', //用户 8个
                'GET shopUserCode' => 'shop/user/usercode', //用户二维码
                'POST shopUserPayment' => 'shop/user/payment',
                'GET shopUserPayment' => 'shop/user/payment-list', //用户付款记录

                //
//商城签到
                'GET shopSign/<id>' => 'shop/sign/list', //签到记录
                'GET shopSignToDay/<id>' => 'shop/sign/one', //当天签到记录
                'POST shopSign' => 'shop/sign/add', //新增签到记录
                'GET shopSignIn' => 'shop/sign/signin', //签到活动
                'GET shopSigns/<id>' => 'shop/sign/sign', //签到累计信息
                'GET shopSignsTotal/<id>' => 'shop/sign/total', //签到排行榜
                'POST shopSignsPrize/<id>' => 'shop/sign/prize', //签到奖品领取
                'POST shopSignsRepair/<id>' => 'shop/sign/index', //签到补签
//
//收货地址
                'GET shopContact' => 'shop/contact/list',
                'GET shopContact/<id>' => 'shop/contact/single',
                'POST shopContact' => 'shop/contact/add',
                'PUT shopContact/<id>' => 'shop/contact/update',
                'DELETE shopContact/<id>' => 'shop/contact/delete',
                //根据收货地址获取快递费
                'GET shopKdf/<id>' => 'shop/contact/kdf',
                //商品分类
                'GET shopCategory' => 'shop/category/list',
                'GET shopCategory/<id>' => 'shop/category/all',
                'GET shopCategory' => 'shop/category/alls', //
                'GET shopSubCategory' => 'shop/category/lists', //二级分类
                'GET shopAdminCategory' => 'shop/category/category', //
                //购物车
                'GET shopCart' => 'shop/cart/list',
                'GET shopCarts' => 'shop/cart/cart',
                'POST shopCart' => 'shop/cart/add',
                'DELETE shopCart' => 'shop/cart/delete',
                //商品
                'GET shopIsTopGoods' => 'shop/goods/istop', //推荐商品
                'GET shopGoods' => 'shop/goods/list',
                'GET shopGoodsAll' => 'shop/goods/all',
                'GET shopList' => 'shop/goods/goods',
                'GET shopGoods/<id>' => 'shop/goods/single',
                'GET shopGoodsinfo/<id>' => 'shop/goods/sinleinfo',
                'GET shopGoodsInfos' => 'shop/goods/cartinfo', //根据购物车选的商品返回详情
                'GET shopGoodsStock/<id>' => 'shop/goods/stock', //商品库存
                'GET shopGoodsStockProperty/<id>' => 'shop/goods/property', //商品库存属性

                'GET merchantGoodsStock/<id>' => 'shop/goods/stock', //商品库存
                'GET merchantGoodsStockProperty/<id>' => 'shop/goods/property', //商品库存属性

                'GET shopGoodsCode' => 'shop/goods/qcode', //小程序二维码
                'GET shopQcode' => 'shop/qcode/qcode', //小程序二维码不用验证TOKEN
//订单信息
                'GET shopOrder' => 'shop/order/list', //订单
                'GET shopRandomOrder' => 'shop/order/random', //随机 10 订单
                'GET shopOrder/<id>' => 'shop/order/single', //订单详情
                'DELETE shopOrder/<id>' => 'shop/order/delete', //订单详情
                'GET shopOrderExpress/<id>' => 'shop/order/express', //订单详情

//退款/退款 维权
                'GET shopOrderAfterList' => 'shop/order/afterlist',
                'PUT shopOrderAfter' => 'shop/order/after',
                'PUT shopOrderUnAfter/<id>' => 'shop/order/unmoney', //取消申请退款，取消申请退货退款
                'POST shopAfterUpload' => 'shop/order/uploads', //售后更新
                'PUT shopOrderGoods' => 'shop/order/goods', //确认收货
//评论
                'GET shopGoodsComment' => 'shop/comment/list',
                'GET shopGoodsComments/<id>' => 'shop/comment/all',
                'GET shopGoodsComment/<id>' => 'shop/comment/single',
                'POST shopGoodsComment' => 'shop/comment/add',
                'PUT shopGoodsComment/<id>' => 'shop/comment/update',
                'DELETE shopGoodsComment/<id>' => 'shop/comment/delete',
                'POST shopGoodsCommentUploads' => 'shop/comment/uploads',
//评分
//                'GET shopGoodsScore' => 'shop/comment/list',
                'GET shopGoodsScore/<id>' => 'shop/score/single',
                'POST shopGoodsScore' => 'shop/score/add',
                'PUT shopGoodsScore/<id>' => 'shop/score/update',
                'DELETE shopGoodsScore/<id>' => 'shop/score/delete',
                //省市级联
                'GET goodAddress' => 'shop/user/address',
                'POST shopOrderPay' => 'shop/order/add', //新增订单
                'POST shopOrderPay1' => 'shop/order/order', //新增订单
                'PUT shopUnOrder/<id>' => 'shop/order/unorder', //取消订单
                'POST shopGoPay/<id>' => 'shop/order/pay', //支付订单
                'POST shopGoPay1/<id>' => 'shop/order/pay1', //支付订单
                'GET shopGoodsBuyInfo/<id>' => 'shop/goods/buy-info',
                //好物圈
                'POST shopCircleGoods' => 'shop/good-circle/goods', //商品更新
                'GET shopCircleGoods/<id>' => 'shop/good-circle/single', //商品查询
//                'POST shopCircleOrder' => 'shop/good-circle/order', //订单导入
                'PUT shopCircleOrder/<id>' => 'shop/good-circle/update', //订单更新
                'POST shopHistoryOrder' => 'shop/good-circle/historyorder', //导入历史订单
                //商城主题设置
                'GET shopTheme' => 'shop/theme/single', //主题单条 版权信息
                'GET shopThemes' => 'shop/theme/one', //主题单条
                //
//公众号菜单配置
                'GET merchantWechatMenu' => 'wechat/officialAccount/menu',
                'POST merchantWechatMenu' => 'wechat/officialAccount/menu/create',
                'DELETE merchantWechatMenu' => 'wechat/officialAccount/menu/delete',
                //微信公众号素材管理
                'GET merchantWechatMedia' => 'wechat/officialAccount/media',
                'GET merchantWechatMedia/<id>' => 'shop/officialAccount/media/single',
                'POST merchantWechatMedia' => 'wechat/officialAccount/media/uploads',
                'DELETE merchantWechatMedia' => 'wechat/officialAccount/media/delete',
                //微信公众号关键词管理
                'GET merchantWechatWords' => 'wechat/officialAccount/words/list',
                'GET merchantWechatWords/<id>' => 'wechat/officialAccount/words/single',
                'POST merchantWechatWords' => 'wechat/officialAccount/words/add',
                'PUT merchantWechatWords/<id>' => 'wechat/officialAccount/words/update',
                'DELETE merchantWechatWords/<id>' => 'wechat/officialAccount/words/delete',
                'GET merchantWechatWord/<id>' => 'wechat/officialAccount/words/one', //非关键词与关注
                'PUT merchantWechatWord' => 'wechat/officialAccount/words/updates', //非关键词与关注
                'POST merchantWechatWord' => 'wechat/officialAccount/words/adds', //非关键词与关注
                //微信扫码授权 第三方平台
                'DELETE openPlatRemove' => 'wechat/officialAccount/openplat/remove',
                //小程序授权上传
                'GET miniProgram' => 'wechat/officialAccount/openplat/miniprogram',
                'POST miniProgramCommit' => 'wechat/officialAccount/openplat/commit',
                'GET miniProgramQrcode' => 'wechat/officialAccount/openplat/qrcode',
                'POST miniProgramAudit' => 'wechat/officialAccount/openplat/audit',
                'GET miniProgramAuditStatus' => 'wechat/officialAccount/openplat/auditstatus',
                'POST miniProgramrelease' => 'wechat/officialAccount/openplat/release',
                'GET miniProgramUndocodeaudit' => 'wechat/officialAccount/openplat/undocodeaudit',
                //获取小程序二维码
                'GET miniProgramQrcodes' => 'shop/user/qcode',
                //系统信息
                'GET adminSystemVip' => 'admin/system/vip/list', //vip列表
                'GET adminSystemVip/<id>' => 'admin/system/vip/single', //vip单挑
                'POST adminSystemVip' => 'admin/system/vip/add', //vip新增
                'PUT adminSystemVip/<id>' => 'admin/system/vip/update', //vip 更新
                'DELETE adminSystemVip/<id>' => 'admin/system/vip/delete', //vip 删除
                'GET adminSystemVipUser' => 'admin/system/user/list', //vip会员信息列表
                'GET adminSystemVipUser/<id>' => 'admin/system/user/single', //vip会员信息单挑
                'PUT adminSystemVipUser/<id>' => 'admin/system/user/update', //vip 会员信息审核
                'DELETE adminSystemVipUser/<id>' => 'admin/system/user/delete', //vip会员信息 删除
                //消息推送
                'GET adminSystemTemplate' => 'admin/system/template/list', //消息模板列表
                'GET adminSystemTemplate/<id>' => 'admin/system/template/single', //消息模板单挑
                'POST adminSystemTemplate' => 'admin/system/template/add', //消息模板新增
                'PUT adminSystemTemplate/<id>' => 'admin/system/template/update', //消息模板 更新
                'DELETE adminSystemTemplate/<id>' => 'admin/system/template/delete', //消息模板 删除
                'POST adminSystemTemplates' => 'admin/system/template/temp', //获取模板库某个模板标题下关键词库
                //系统菜单
                'GET adminSystemMenu' => 'admin/system/menu/list', //菜单列表
                'GET adminSystemMenu/<id>' => 'admin/system/menu/single', //菜单单挑
                'POST adminSystemMenu' => 'admin/system/menu/add', //菜单新增
                'PUT adminSystemMenu/<id>' => 'admin/system/menu/update', //菜单 更新
                'DELETE adminSystemMenu/<id>' => 'admin/system/menu/delete', //菜单 删除
                //团购
                //前台
                'GET shopTuanConfig' => 'tuan/config/single', //团购配置
                'GET shopTuanUser' => 'tuan/user/list', //团长列表
                'GET shopTuanMerbers' => 'shop/tuan/merbers', //团长列表
                'GET shopTuanUserInfo/<id>' => 'tuan/user/one', //团长列表
                'GET shopTuanLevel' => 'tuan/user/level', //团购等级权益
                'POST shopTuanUser' => 'tuan/user/add', //申请团长
                'GET shopTuanUserStatus' => 'tuan/user/tuan', //团长状态
                'POST shopTuanUserLeader' => 'tuan/user/leader', //绑定团长
                'GET shopTuanUserLast' => 'tuan/user/last', //最后一次团长
                'PUT shopTuanUserLast/<id>' => 'tuan/user/update', //最后一次团长更新
                'GET shopTuanSupplier' => 'tuan/user/supplier', //门店团长信息
                'GET shopTuanMiniprogramr' => 'shop/user/miniprogram', //门店团长信息
                //系统前台
                'POST form' => 'system/form/add', //添加表单
                //
                //后台
                'GET merchantIndexTotal' => 'merchant/shop/user/total', //
                'GET merchantTuanConfig' => 'merchant/tuan/config/single', //团购配置
                'POST merchantTuanConfig' => 'merchant/tuan/config/config', //团购配置修改
                'PUT merchantTuanUsers/<id>' => 'merchant/tuan/user/update', //团购开启，关闭
                'GET merchantTuanUser' => 'merchant/tuan/user/list', //团长列表
                'GET merchantTuanUser/<id>' => 'merchant/tuan/user/single', //团长列表
                'PUT merchantTuanUser/<id>' => 'merchant/tuan/user/audit', //审核团长
                'DELETE merchantTuanUserUntying/<id>' => 'merchant/tuan/user/delete', //审核团长
                //vip会员 信息
                'GET merchantTuanVip' => 'merchant/system/vip/list', //vip等级 信息详情
                'GET merchantTuanVipUser' => 'merchant/system/vip/single', //vip会员 信息详情
                'POST merchantTuanVipUser' => 'merchant/system/vip/add', //vip会员 信息修改
                // 'GET shopTuanUserStatus' => 'merchant/tuan/user/tuan', //团长状态
                //商品-城市组表
                'GET merchantGoodsCityGroup' => 'merchant/tuan/city/list', //城市组表列表
                'GET merchantGoodsCityGroup/<id>' => 'merchant/tuan/city/single', //城市组表单条
                'POST merchantGoodsCityGroup' => 'merchant/tuan/city/add', //城市组表新增
                'PUT merchantGoodsCityGroup/<id>' => 'merchant/tuan/city/update', //城市组表更新
                'DELETE merchantGoodsCityGroup/<id>' => 'merchant/tuan/city/delete', //城市组表删除
                //商户后台 购买VIP
                'GET merchantPayVip' => 'merchant/system/vip/pay',
                'POST merchantPayVipAli' => 'merchant/system/vip/alipay',
                'POST merchantPayVipWx' => 'merchant/system/vip/wxpay',
                //商户后台
                //快递信息配置
                'GET merchantElectronics' => 'merchant/shop/electronics/list', //快递信息配置列表
                'GET merchantElectronics/<id>' => 'merchant/shop/electronics/single', //快递信息配置单条
                'POST merchantElectronics' => 'merchant/shop/electronics/add', //快递信息配置新增
                'PUT merchantElectronics/<id>' => 'merchant/shop/electronics/update', //快递信息配置更新
                'DELETE merchantElectronics/<id>' => 'merchant/shop/electronics/delete', //快递信息配置删除
                //
                'GET merchantSystemTemplate' => 'merchant/system/template/list', //商户模板列表
                //    'GET merchantSystemTemplate/<id>' => 'merchant/system/template/single', //消息模板单挑
                'POST merchantSystemTemplate' => 'merchant/system/template/add', //消息模板新增
                'GET merchantSystemTemplateMessage' => 'merchant/system/template/all', //商户模板群发列表
                'GET merchantSystemTemplateMessageOne' => 'merchant/system/template/one', //商户模板群发列表
                'GET merchantSystemTemplateMessageOne/<id>' => 'merchant/system/template/single', //商户模板群发列表
                'POST merchantSystemTemplateMessage' => 'merchant/system/template/message', //商户模板群发列表
                'POST merchantSystemTemplateMessageSend' => 'merchant/system/template/send', //商户模板群发 发送
                'DELETE merchantSystemTemplate/<id>' => 'merchant/system/template/delete', //商户模板群发 删除
//                'PUT merchantSystemTemplate/<id>' => 'admin/system/template/update', //消息模板 更新
//                'DELETE merchantSystemTemplate/<id>' => 'admin/system/template/delete', //消息模板 删除
//
//                //面单打印订单
                'GET merchantPrintsOrders' => 'prints/order/list', //打印面单 列表
                'POST merchantPrintsOrders' => 'prints/order/prints', //打印面单
                'PUT merchantPrintsOrdersSend' => 'prints/order/send', //打印面单 发货
                //通用方法
                //前台文件上传
                'POST upload' => 'common/uploads/index',
                'GET express' => 'common/base/express',
                'POST base64' => 'common/uploads/base',
                'GET address' => 'common/base/address',
                'GET addr' => 'common/base/addr',
                'GET sms' => 'common/base/sms',
                'GET merchantSms' => 'common/sms/sms',
                //
                'GET printOrders' => 'prints/order/list', //快递信息配置列表
                'GET printOrders/<id>' => 'prints/order/single', //快递信息配置单条
                //商城前台店铺装修
                'GET shopDecorations' => 'shop/decoration/list', //店铺装修列表
//商城后台店铺装修
                'GET shopDecoration' => 'merchant/shop/decoration/list', //店铺装修列表
                'GET shopDecoration/<id>' => 'merchant/shop/decoration/single', //店铺装修单条
                'POST shopDecoration' => 'merchant/shop/decoration/add', //店铺装修新增
                'PUT shopDecoration/<id>' => 'merchant/shop/decoration/update', //店铺装修更新
                'DELETE shopDecoration/<id>' => 'merchant/shop/decoration/delete', //店铺装修删除
                'PUT shopDecorationIsEnable/<id>' => 'merchant/shop/decoration/isenable', //店铺装修更新
                'GET systemDecoration' => 'merchant/shop/decoration/systemlist', //商户后台系统模板库
                'POST systemDecoration' => 'merchant/shop/decoration/addsystem', //添加系统模板库到我的模板库中
                //提现
                'GET shopBalance' => 'shop/balance/balance', //团长体现
                'GET shopBalances' => 'shop/balance/list', //团长体现
                'GET shopBalancesAll' => 'shop/balance/all', //佣金流水
                'POST shopBalances' => 'shop/balance/add', //团长体现
                //商户后台
                'GET merchantShopBalance' => 'merchant/shop/balance/list', //团长体现 //佣金流水 佣金提现申请
                'PUT merchantShopBalance/<id>' => 'merchant/shop/balance/audit', //团长体现审核
                //后台商户短信，订单套餐
                'GET adminMerchantCombo' => 'admin/system/combo/list',
                'GET adminMerchantCombo/<id>' => 'admin/system/combo/single',
                'POST adminMerchantCombo' => 'admin/system/combo/add',
                'PUT adminMerchantCombo/<id>' => 'admin/system/combo/update',
                'DELETE adminMerchantCombo/<id>' => 'admin/system/combo/delete',
                'GET adminMerchantComboAccess' => 'admin/system/combo/all',
                'GET adminMerchantComboAlls' => 'admin/system/combo/alls',
                'POST adminMerchantComboInsert' => 'admin/system/combo/insert',
                //商户---短信，订单套餐
                'GET MerchantHeader' => 'merchant/system/combo/one',
                'GET MerchantCombo' => 'merchant/system/combo/list',
                'GET MerchantComboAll' => 'merchant/system/combo/all',
                //商户套餐购买
                'GET merchantPayCombo/<id>' => 'merchant/system/combo/pay',
                'POST merchantPayComboAli' => 'merchant/system/combo/alipay',
                'POST merchantPayComboWx' => 'merchant/system/combo/wxpay',
                'GET merchantPayComboWxQuery/<id>' => 'merchant/system/combo/query',
                //供应商商品
                'GET merchantSupplierGoods' => 'merchant/tuan/goods/list', //商品列表
                'GET merchantSupplierGoods/<id>' => 'merchant/tuan/goods/single', //商品单条
                'POST merchantSupplierGoods' => 'merchant/tuan/goods/add', //商品新增
                'POST merchantSupplierGoodsImg' => 'merchant/tuan/goods/uploads', //商品图片上传
                'POST merchantSupplierGoodsImgInfo' => 'merchant/tuan/goods/uploadsinfo', //商品图片上传
                'PUT merchantSupplierGoods/<id>' => 'merchant/tuan/goods/update', //商品更新
                'PUT merchantSupplierGood/<id>' => 'merchant/tuan/goods/updates', //商品更新
                'DELETE merchantSupplierGoods/<id>' => 'merchant/tuan/goods/delete', //商品删除
                'GET merchantSupplierGoodsRecycle' => 'merchant/tuan/goods/recycle', //回收商品
                'PUT merchantSupplierGoodReduction/<id>' => 'merchant/tuan/goods/reduction', //恢复商品
                'GET merchantSupplierOrder' => 'merchant/supplier/order/list', //商品列表
                //
                //----
                //供应商商品
                'GET supplierGoods' => 'supplier/goods/goods/list', //商品列表
                'GET supplierGoods/<id>' => 'supplier/goods/goods/single', //商品单条
                'POST supplierGoods' => 'supplier/goods/goods/add', //商品新增
                'POST supplierGoodsImg' => 'supplier/goods/goods/uploads', //商品图片上传
                'POST supplierGoodsImgInfo' => 'supplier/goods/goods/uploadsinfo', //商品图片上传
                'PUT supplierGoods/<id>' => 'supplier/goods/goods/update', //商品更新
                //'PUT supplierGoods/<id>' => 'supplier/goods/updates', //商品更新
                'DELETE supplierGoods/<id>' => 'supplier/goods/goods/delete', //商品删除
                //'GET supplierGoods' => 'supplier/goods/recycle', //回收商品
                'PUT supplierGoodsReduction/<id>' => 'supplier/goods/goods/reduction', //恢复商品
                'GET supplierCategoryTypeMini' => 'supplier/goods/category/merchanttype', //商品类目父类
                'GET supplierShopExpressTemplate' => 'supplier/goods/template/list', //运费模板列表
                'GET supplierGoodsCityGroup' => 'supplier/goods/city/list', //城市组表列表
                'GET supplierUserInfo' => 'supplier/goods/goods/info', //供应商信息
                'GET supplierOrder' => 'supplier/goods/order/list', //供应商订单
                'PUT supplierSend' => 'supplier/goods/order/send', //供应商订单
                'GET supplierElectronics' => 'supplier/goods/electronics/list', //快递信息配置列表
                'GET supplierYlyPrint' => 'supplier/goods/yly-print/single', //易联云小票机配置查询
                'PUT supplierYlyPrint/<id>' => 'supplier/goods/yly-print/update', //易联云小票机配置更新
                //指定购物返现
                'GET merchantCashback' => 'merchant/shop/cashback/list',
                'GET merchantCashback/<id>' => 'merchant/shop/cashback/single',
                'POST merchantCashback' => 'merchant/shop/cashback/add',
                'PUT merchantCashback/<id>' => 'merchant/shop/cashback/update',
                'DELETE merchantCashback/<id>' => 'merchant/shop/cashback/delete',
                //到店付款记录
                'GET merchantStorePayment' => 'merchant/shop/store-payment/list',
                'GET merchantStorePayment/<id>' => 'merchant/shop/store-payment/single',
                'POST merchantStorePayment' => 'merchant/shop/store-payment/add',
                'PUT merchantStorePayment/<id>' => 'merchant/shop/store-payment/update',
                'DELETE merchantStorePayment/<id>' => 'merchant/shop/store-payment/delete',

                'GET merchantStorePaymentConfig' => 'merchant/shop/store-payment/config',
                'PUT merchantStorePaymentConfig/<id>' => 'merchant/shop/store-payment/updateconfig',
                //门店付款记录
                'GET supplierStorePayment' => 'supplier/goods/order/store-list',
                'POST supplierStorePayment' => 'supplier/goods/order/payment',
                //  'GET supplierUserInfo' => 'supplier/goods/order/store-list',

                //商户秒杀活动
                'GET merchantFlashSale' => 'merchant/spike/flashsale/list',
                'GET merchantFlashSale/<id>' => 'merchant/spike/flashsale/single',
                'POST merchantFlashSale' => 'merchant/spike/flashsale/add',
                'PUT merchantFlashSale/<id>' => 'merchant/spike/flashsale/update',
                'DELETE merchantFlashSale/<id>' => 'merchant/spike/flashsale/delete',
                'PUT merchantFlashSaleGroup/<id>' => 'merchant/spike/flashsalegroup/updateshopflashsalegroup',
                'PUT merchantFlashSale' => 'merchant/spike/flashsale/updateshopflashsale',
                //前台秒杀
                'GET shopFlashSale' => 'shop/flash/group',
                'GET shopFlashSale/<id>' => 'shop/flash/single',

                //砍价
                'GET shopBargainGoods' => 'shop/bargain/goods',//砍价商品
                'POST shopBargain' => 'shop/bargain/add',//发起砍价
                'POST shopBargain/<id>' => 'shop/bargain/bargain',//砍价
                'GET shopBargain/<id>' => 'shop/bargain/single',//砍价详情
                'GET shopBargain' => 'shop/bargain/list',//砍价详情
                //test  banner 增删改查
                'POST systemBanner' => 'admin/system/banner/add', //banner 新增banner
                'GET systemBanners' => 'admin/system/banner/list', //banner 查询列表banner
                'PUT systemBanner/<id>' => 'admin/system/banner/update', //banner 更新banner
                'DELETE systemBanner/<id>' => 'admin/system/banner/delete', //banner 删除banner
                'GET systemBanner/<id>' => 'admin/system/banner/one', //banner 删除banner
                'POST systemBannersPay' => 'admin/system/pay/pay', //测试支付
                'POST systemBannersOpenid' => 'admin/system/pay/openid', //获取openid
                //供应商
                'POST supplierLogin' => 'supplier/user/login/login', //供应商登陆
                //团购团长信息
                'GET tuanExpress/<id>' => 'shop/tuan/express',
                'GET tuanLeader' => 'shop/tuan/leader',
                'GET tuanOrder/<id>' => 'shop/tuan/order', //团长订单
                'PUT tuanConfirm' => 'shop/tuan/confirm', //团长核销
                'PUT tuanReceiving' => 'shop/tuan/tuan-order-receiving', //团长确认收货
                'PUT updateTuanLeader' => 'shop/tuan/update-leader', //团长信息更新
                //团长等级
                'GET merchantLeaderLevel' => 'merchant/user/level/list',
                'GET merchantLeaderLevel/<id>' => 'merchant/user/level/single',
                'POST merchantLeaderLevel' => 'merchant/user/level/add',
                'PUT merchantLeaderLevel/<id>' => 'merchant/user/level/update',
                'DELETE merchantLeaderLevel/<id>' => 'merchant/user/level/delete',
                //团购前台统计
                'GET shopLeaderTotalToday' => 'tuan/user/today',
                'GET shopLeaderTotalOrder' => 'tuan/user/order',
                'GET shopLeaderTotalCensus' => 'tuan/user/total',
                'GET shopLeaderOrderStatistics' => 'tuan/user/order-statistics', // 团长订单统计（按商品）
                'GET shopLeaderOrderStatisticsUser' => 'tuan/user/order-statistics-user', // 团长订单统计（按用户）
                //订单二维码
                'GET shopOrderQrcode' => 'shop/order/qrcode',
                //核销员
                'GET merchantLeagueMember/<id>' => 'merchant/tuan/user/leaguememberlist', //团员列表
                'PUT merchantLeagueMember' => 'merchant/tuan/user/leaguememberupdata', //添加、取消核销员
                //商户后台短信签名
                'POST merchantSigns' => 'merchant/message/signature/add', //新增
                'DELETE merchantSigns/<id>' => 'merchant/message/signature/delete', //删除
                'PUT merchantSigns/<id>' => 'merchant/message/signature/update', //修改
                'PUT merchantSigns/status/<id>' => 'merchant/message/signature/status', //只修改启用状态
                'GET merchantSigns' => 'merchant/message/signature/finds', //查所有
                'GET merchantSigns/<id>' => 'merchant/message/signature/find', //查单条
                //商户后台短信模板
                'POST merchantTemps' => 'merchant/message/template/add', //新增
                'DELETE merchantTemps/<id>' => 'merchant/message/template/delete', //删除
                'PUT merchantTemps/<id>' => 'merchant/message/template/update', //修改
                'PUT merchantTemps/status/<id>' => 'merchant/message/template/status', //只修改启用状态
                'GET merchantTemps' => 'merchant/message/template/finds', //查所有
                'GET merchantTemps/<id>' => 'merchant/message/template/find', //查单条
                //商城配置
                'GET shopOpenconfig' => 'shop/config/list', //查所有
                //管理员后台插件配置
                'POST systemPlugin' => 'admin/system/plugin/add', //新增
                'GET systemPlugin' => 'admin/system/plugin/list', //查询列表
                'PUT systemPlugin/<id>' => 'admin/system/plugin/update', //更新
                'DELETE systemPlugin/<id>' => 'admin/system/plugin/delete', //删除
                'GET systemPlugin/<id>' => 'admin/system/plugin/one', //查询单条
                //商户后台秒杀总开关
                'GET merchantSpike' => 'merchant/spike/flashsale/configlist', //查询插件
                'PUT merchantSpike/<id>' => 'merchant/spike/flashsale/config', //更新
                //商户插件总开关
                'GET shopPlugin' => 'shop/config/list', //查询插件
                //管理员后台打印设置
                'POST adminPrinting' => 'admin/system/printing/add', //分组新增
                'GET adminPrinting' => 'admin/system/printing/list', //分组查询列表
                'PUT adminPrinting/<id>' => 'admin/system/printing/update', //分组更新
                'DELETE adminPrinting/<id>' => 'admin/system/printing/delete', //分组删除
                'GET adminPrinting/<id>' => 'admin/system/printing/one', //分组查询单条
                'POST adminPrintingkey' => 'admin/system/printing/keyadd', //分组字段新增
                'GET adminPrintingkey' => 'admin/system/printing/keylist', //分组字段查询列表
                'PUT adminPrintingkey/<id>' => 'admin/system/printing/keyupdate', //分组字段更新
                'DELETE adminPrintingkey/<id>' => 'admin/system/printing/keydelete', //分组字段删除
                'GET adminPrintingkey/<id>' => 'admin/system/printing/keyone', //分组字段查询单条
                'POST adminPrintingtemp' => 'admin/system/printing/tempadd', //模板新增
                'GET adminPrintingtemp' => 'admin/system/printing/templist', //模板查询列表
                'PUT adminPrintingtemp/<id>' => 'admin/system/printing/tempupdate', //模板更新
                'DELETE adminPrintingtemp/<id>' => 'admin/system/printing/tempdelete', //模板删除
                'GET adminPrintingtemp/<id>' => 'admin/system/printing/tempone', //模板查询单条
                'GET adminPrintingpulldown' => 'admin/system/printing/pulldownlist', //模板添加分组、字段信息
                //商户后台打印模板
                'GET merchantPrintingtemp' => 'merchant/shop/printing/list', //模板列表
                'GET merchantPrintingtemp/<id>' => 'merchant/shop/printing/one', //模板单条
                'POST merchantPrintingtemp' => 'merchant/shop/printing/add', //新增模板
                'PUT merchantPrintingtemp/<id>' => 'merchant/shop/printing/update', //模板更新
                'DELETE merchantPrintingtemp/<id>' => 'merchant/shop/printing/delete', //模板删除
                'GET adminPrinttemp' => 'merchant/shop/printing/adminlist', //管理员模板列表
                'GET merchantTuanordertemp' => 'merchant/shop/printing/tuanordertemp', //管理员模板列表
                //VIP会员卡配置
                'GET vipConfig' => 'merchant/vip/config/one', //vip配置
                'POST vipConfig' => 'merchant/vip/config/add', //新增vip配置
                'PUT vipConfig/<id>' => 'merchant/vip/config/update', //更新vip配置
                'DELETE vipConfig/<id>' => 'merchant/vip/config/delete', //删除vip配置
                // vip 会员卡
                'GET vips' => 'merchant/vip/vip/list', //会员卡列表
                'GET vips/<id>' => 'merchant/vip/vip/one', //会员卡单条信息
                'POST vips' => 'merchant/vip/vip/add', //新增会员卡
                'PUT vips/<id>' => 'merchant/vip/vip/update', //更新会员卡
                'DELETE vips/<id>' => 'merchant/vip/vip/delete', //删除会员卡
                // vip 积分会员卡
                'GET unpaidVips' => 'merchant/vip/vip/unpaidlist', //会员卡列表
                'GET unpaidVips/<id>' => 'merchant/vip/vip/unpaidone', //会员卡单条信息
                'POST unpaidVips' => 'merchant/vip/vip/unpaidadd', //新增会员卡
                'PUT unpaidVips/<id>' => 'merchant/vip/vip/unpaidupdate', //更新会员卡
                'DELETE unpaidVips/<id>' => 'merchant/vip/vip/unpaiddelete', //删除会员卡
                'GET merchantVipPlugin' => 'merchant/vip/vip/plugin', //会员卡插件开关
                'PUT merchantVipPlugin/<id>' => 'merchant/vip/vip/updateplugin', //会员卡插件开关
                // 前台 会员卡订单
                'GET vipAccess' => 'shop/vip-access/list', //会员卡订单列表
                'POST vipAccess' => 'shop/vip-access/add', //创建会员卡订单
                'POST vipAccess/<id>' => 'shop/vip-access/pay', //会员卡支付
                'GET vipList' => 'shop/vip-access/vip-list', //会员卡列表
                'GET vipIsVip' => 'shop/vip-access/is-vip', //检测是不是vip
                'GET unpaidVipAccess' => 'shop/vip-access/unpaid-vip', //积分会员信息
                // 商品图片库
                'GET pictureGroup' => 'merchant/shop/picture/list', //图片分组列表
                'GET pictureGroup/<id>' => 'merchant/shop/picture/one', //图片分组单个
                'POST pictureGroup' => 'merchant/shop/picture/add', //图片分组创建
                'PUT pictureGroup/<id>' => 'merchant/shop/picture/update', //图片分组更新
                'DELETE pictureGroup/<id>' => 'merchant/shop/picture/delete', //图片分组删除
                'GET picture/<id>' => 'merchant/shop/picture/picture-list', //图片列表
                'POST merchantGoodsPicture' => 'merchant/shop/goods/uploads-picture', //商品图片上传专用
                'DELETE merchantGoodsPicture/<id>' => 'merchant/shop/goods/delete-picture', //商品图片删除

                //供应商图片库
                'GET supplierPictureGroup' => 'supplier/shop/picture/list', //图片分组列表
                'GET supplierPictureGroup/<id>' => 'supplier/shop/picture/one', //图片分组单个
                'POST supplierPictureGroup' => 'supplier/shop/picture/add', //图片分组创建
                'PUT supplierPictureGroup/<id>' => 'supplier/shop/picture/update', //图片分组更新
                'DELETE supplierPictureGroup/<id>' => 'supplier/shop/picture/delete', //图片分组删除
                'GET supplierPicture/<id>' => 'supplier/shop/picture/picture-list', //图片列表
                'POST supplierGoodsPicture' => 'supplier/goods/goods/uploads-picture', //商品图片上传专用
                'DELETE supplierGoodsPicture/<id>' => 'supplier/goods/goods/delete-picture', //商品图片删除
                //积分商城
                //积分商品分组
                'GET merchantScoreCategory' => 'merchant/score/category/list', //积分商品分组列表
                'GET merchantScoreCategoryAll' => 'merchant/score/category/all', //积分商品分组列表
                'GET merchantScoreCategory/<id>' => 'merchant/score/category/single', //积分商品分组单个
                'POST merchantScoreCategory' => 'merchant/score/category/add', //积分商品分组创建
                'PUT merchantScoreCategory/<id>' => 'merchant/score/category/update', //积分商品分组更新
                'DELETE merchantScoreCategory/<id>' => 'merchant/score/category/delete', //积分商品分组删除
                //积分商品
                'GET merchantScoreGoods' => 'merchant/score/goods/list', //积分商品列表
                'GET merchantScoreGoods/<id>' => 'merchant/score/goods/single', //积分商品单个
                'POST merchantScoreGoods' => 'merchant/score/goods/add', //积分商品创建
                'PUT merchantScoreGoods/<id>' => 'merchant/score/goods/update', //积分商品更新
                'DELETE merchantScoreGoods/<id>' => 'merchant/score/goods/delete', //积分商品删除
                //
                'GET merchantScoreBanner' => 'merchant/score/banner/list', //积分banner列表
                'GET merchantScoreBanner/<id>' => 'merchant/score/banner/single', //积分banner单个
                'POST merchantScoreBanner' => 'merchant/score/banner/add', //积分banner创建
                'PUT merchantScoreBanner/<id>' => 'merchant/score/banner/update', //积分banner更新
                'DELETE merchantScoreBanner/<id>' => 'merchant/score/banner/delete', //积分banner删除
                //
                'GET merchantScoreOrder' => 'merchant/score/order/list', //积分订单列表
                'GET merchantScoreOrder/<id>' => 'merchant/score/order/single', //积分订单单个
                'PUT merchantScoreOrder/<id>' => 'merchant/score/order/update', //积分订单更新
                'DELETE merchantScoreOrder/<id>' => 'merchant/score/order/delete', //积分订单删除
                //前台积分商城
                'GET shopScoreBanner' => 'score/banner/list', //积分商品分组列表
                'GET shopScoreCategory' => 'score/category/list', //积分商品分组列表
                'GET shopScoreCategoryAll' => 'score/category/all', //积分商品分组列表
                'GET shopScoreGoods' => 'score/goods/list', //积分商品列表
                'GET shopScoreGoods/<id>' => 'score/goods/single', //积分商品单个
                'GET shopScoreTest' => 'score/goods/test', //测试
                //前台积分商城订单
                'GET shopScoreOrder' => 'score/order/list', //订单列表
                'GET shopScoreOrder/<id>' => 'score/order/single', //订单详情
                'POST shopScoreOrder' => 'score/order/add', //提交订单

                //前台 获取直播列表
                'GET shopLive' => 'shop/live/list', //直播列表
                //后台获取直播列表
                'GET merchantLive' => 'merchant/live/live/list', //直播列表
                'PUT merchantLive/<id>' => 'merchant/live/live/update', //直播列表
                //商户后台仓库
                'GET merchantWarehouse' => 'merchant/tuan/warehouse/list', //仓库列表
                'GET merchantWarehouse/<id>' => 'merchant/tuan/warehouse/one', //仓库单条
                'POST merchantWarehouse' => 'merchant/tuan/warehouse/add', //新增仓库
                'PUT merchantWarehouse/<id>' => 'merchant/tuan/warehouse/update', //仓库更新
                'DELETE merchantWarehouse/<id>' => 'merchant/tuan/warehouse/delete', //仓库删除
                'GET merchantWarehouseleader/<id>' => 'merchant/tuan/warehouse/leader', //仓库下团长
                //新人代金卷
                'GET shopNewUserVoucher' => 'shop/voucher/new', //新人代金卷
                //供应商资金明细
                'GET supplierBalance' => 'supplier/goods/balance/list', //供应商资金明细列表
                'GET supplierBalance/<id>' => 'supplier/goods/balance/single', //供应商资金明细单个
                'POST supplierBalance' => 'supplier/goods/balance/add', //积分商品创建
                'PUT supplierBalance/<id>' => 'supplier/goods/balance/update', //供应商资金明细更新
                'DELETE supplierBalance/<id>' => 'supplier/balance/banner/delete', //供应商资金明细删除
                'GET supplierCommission' => 'supplier/goods/balance/commission', //供应商佣金明细列表
                //供应商提现
                'GET merchantSupplierBalance' => 'merchant/supplier/balance/list', //供应商提现列表
                'GET merchantSupplierBalance/<id>' => 'merchant/supplier/balance/single', //供应商提现单个
                'PUT merchantSupplierBalance/<id>' => 'merchant/supplier/balance/update', //供应商提现审核
                //管理员后台
                'GET adminDiy' => 'admin/system/diy/list', //diy列表
                'GET adminDiy/<id>' => 'admin/system/diy/single', //diy单条
                'POST adminDiy' => 'admin/system/diy/add', //diy新增
                'PUT adminDiy/<id>' => 'admin/system/diy/update', //diy更新
                'DELETE adminDiy/<id>' => 'admin/system/diy/delete', //diy删除
                //商户diy
                'GET merchantAdminDiy' => 'merchant/system/diy/all', //系统div
                'GET merchantDiy' => 'merchant/system/diy/list', //diy列表
                'GET merchantDiy/<id>' => 'merchant/system/diy/single', //diy单条
                'POST merchantDiy' => 'merchant/system/diy/add', //diy新增
                'PUT merchantDiy/<id>' => 'merchant/system/diy/update', //diy更新
                'DELETE merchantDiy/<id>' => 'merchant/system/diy/delete', //diy删除
                //前台diy
                'GET shopDiy' => 'shop/diy/single', //系统diy
                //商户后台余额
                'GET balanceRatios' => 'merchant/balance/balance-config/list', //余额配置列表
                'GET balanceRatios/<id>' => 'merchant/balance/balance-config/one', //余额配置单条
                'POST balanceRatios' => 'merchant/balance/balance-config/add', //余额配置新增
                'PUT balanceRatios/<id>' => 'merchant/balance/balance-config/update', //余额配置更新
                'DELETE balanceRatios/<id>' => 'merchant/balance/balance-config/delete', //余额配置删除
                'GET balanceAccessLists' => 'merchant/balance/balance-config/order-list', //余额充值订单列表
                // 前台余额
                'GET balanceList' => 'shop/balance-access/config-list', //余额配置列表
                'GET balanceAccessList' => 'shop/balance-access/list', //余额充值订单列表
                'POST balanceAccess' => 'shop/balance-access/add', //余额充值创建订单
                'POST balanceAccess/<id>' => 'shop/balance-access/pay', //余额充值支付
                'POST luckyShare' => 'shop/voucher/share', //运气红包分享
                'POST luckyReceivePacket' => 'shop/voucher/receive-packet', //运气红包领取
                //供应商申请列表
                'GET merchantSuppliers' => 'merchant/shop/suppliers/list',
                'GET merchantSuppliers/<id>' => 'merchant/shop/suppliers/single',
                'POST shopSuppliers' => 'shop/suppliers/add',       //供应商申请前台
                'GET shopSuppliersImg' => 'shop/suppliers/img',//供应商申请海报
                'PUT merchantSuppliers/<id>' => 'merchant/shop/suppliers/update',
                'DELETE merchantSuppliers/<id>' => 'merchant/shop/suppliers/delete',
                //供应商banner
                'GET merchantSuppliersBanner' => 'merchant/shop/suppliers/all',
                'GET merchantSuppliersBanner/<id>' => 'merchant/shop/suppliers/one',
                'POST merchantSuppliersBanner' => 'merchant/shop/suppliers/insert',
                'PUT merchantSuppliersBanner/<id>' => 'merchant/shop/suppliers/renovate',
                'DELETE merchantSuppliersBanner/<id>' => 'merchant/shop/suppliers/del',
                //前台拼团
                'GET groupList/<id>' => 'shop/goods/group-list', //商品详情页显示拼团列表 3条
                'GET groupOrderList' => 'shop/order/group-order-list', //拼团订单用户
                //闪送
                'POST merchantFlashCalc' => 'merchant/system/flash-delivery/calc', //计算费用
                'POST merchantFlashSave' => 'merchant/system/flash-delivery/save', //下单
                'GET merchantFlashInfo' => 'merchant/system/flash-delivery/info', //查询订单
                'GET merchantFlashCancel' => 'merchant/system/flash-delivery/cancel', //取消订单
                'GET merchantFlashTrail' => 'merchant/system/flash-delivery/trail', //查询订单轨迹
                'GET merchantFlashAccount' => 'merchant/system/flash-delivery/account', //查询账户余额
                //商户后台拼团管理
                'GET merchantAssembleGoods' => 'merchant/shop/assemble/goods',//拼团商品列表
                'GET merchantAssembleOrder' => 'merchant/shop/assemble/order',//拼团订单列表
                'GET merchantAssembleAssemble' => 'merchant/shop/assemble/assemble',//拼团管理列表
                'GET merchantAssembleAssemble/<id>' => 'merchant/shop/assemble/assembleone',//拼团管理已参团信息
                //介入腾讯云商品
                'POST instance' => 'tencents/instance/instance', //校验token
                'PUT updatePhone' => 'merchant/user/user/bind-phone', //修改merchant_user 商户手机号
                'GET checkPhone' => 'merchant/user/user/check-phone', //校验登录账号是否是手机号
                'GET getInstanceLog/<id>' => 'admin/user/merchant/buy-t-c-instance', //获取商户在腾讯购买记录
                //易联云
                'GET merchantPrint' => 'merchant/system/yly-print/print',//打印
                'GET merchantPrints' => 'merchant/system/yly-print/list',//查询打印机列表
                'GET merchantPrints/<id>' => 'merchant/system/yly-print/one',//查询单条打印机
                'POST merchantPrints' => 'merchant/system/yly-print/add',//添加打印机
                'PUT merchantPrints/<id>' => 'merchant/system/yly-print/update',//更新打印机
                'DELETE merchantPrints/<id>' => 'merchant/system/yly-print/delete',//删除打印机
                'GET merchantAutoprint' => 'merchant/system/yly-print/auto-print',//自动推送打印
                //砍价活动
                'GET merchantBargainOrder' => 'merchant/shop/bargains/order',//活动列表
                'GET merchantBargainGoods' => 'merchant/shop/bargains/goods',//查询可以砍价的商品
                'GET merchantBargainGoods/<id>' => 'merchant/shop/bargains/single',//查询砍价的商品
                'PUT merchantBargainGoods/<id>' => 'merchant/shop/bargains/update',//更新砍价的商品
                'GET merchantBargain' => 'merchant/shop/bargain/list',//活动列表
                'PUT merchantBargain/<id>' => 'merchant/shop/bargain/update',//活动更新
                'GET merchantBargainInfo' => 'merchant/shop/bargains/list',//砍价详情
                //海报设置
                'GET posters' => 'merchant/shop/poster/list',//展示
                'POST posters' => 'merchant/shop/poster/update',//更新或者删除
                'GET shopPosters' => 'shop/poster/home-images',//生成首页海报
                'GET shopDetailPosters' => 'shop/poster/detail-images',//生成详情页海报
                //新人专享活动
                'GET merchantRecruits' => 'merchant/shop/recruits/list',//活动商品列表
                'DELETE merchantRecruits/<id>' => 'merchant/shop/recruits/delete',//删除活动商品
                'GET merchantRecruitsgoods' => 'merchant/shop/recruits/goodslist',//可参加活动商品列表
                'POST merchantRecruits' => 'merchant/shop/recruits/add',//添加活动商品
                //合伙人
                'GET partners' => 'merchant/partner/partner/list',//合伙人列表
                'GET partnerLists' => 'merchant/partner/partner/partner-list',//获取有效的合伙人列表
                'GET partners/<id>' => 'merchant/partner/partner/one',//合伙人单条
                'POST partners' => 'merchant/partner/partner/add',//合伙人新增
                'PUT partners' => 'merchant/partner/partner/rest-password',//合伙人新增
                'PUT partners/<id>' => 'merchant/partner/partner/update',//合伙人编辑
                'POST openPartners' => 'merchant/partner/partner/open-partner',//开启合伙人设置
                'POST partnersLogin' => 'partner/user/login/login',//合伙人登录
                'PUT partnersNumber/<id>' => 'admin/system/version/up-partner-number',//应用限制合伙人数量
                'GET searchPartner' => 'shop/config/search-partner',//查找登陆人属于哪个合伙人
                //合伙人商品
                'GET partnerGoods' => 'partner/goods/goods/list', //商品列表
                'GET partnerGoods/<id>' => 'partner/goods/goods/single', //商品单条
                'POST partnerGoods' => 'partner/goods/goods/add', //商品新增
                'POST partnerGoodsImg' => 'partner/goods/goods/uploads', //商品图片上传
                'POST partnerGoodsImgInfo' => 'partner/goods/goods/uploads-info', //商品图片上传
                'PUT partnerGoods/<id>' => 'partner/goods/goods/update', //商品更新
                'PUT partnerGood/<id>' => 'partner/goods/goods/updates', //商品更新
                'DELETE partnerGoods/<id>' => 'partner/goods/goods/delete', //商品删除
                'GET partnerRecycle' => 'partner/goods/goods/recycle', //回收商品
                'PUT partnerGoodReduction/<id>' => 'partner/goods/goods/reduction', //恢复商品
                'GET partnerCategoryTypeMini' => 'partner/goods/category/merchant-type', //分类
                // 合伙人商品图片库
                'GET partnerPictureGroup' => 'partner/goods/picture/list', //图片分组列表
                'GET partnerPictureGroup/<id>' => 'partner/goods/picture/one', //图片分组单个
                'POST partnerPictureGroup' => 'partner/goods/picture/add', //图片分组创建
                'PUT partnerPictureGroup/<id>' => 'partner/goods/picture/update', //图片分组更新
                'DELETE partnerPictureGroup/<id>' => 'partner/goods/picture/delete', //图片分组删除
                'GET partnerPicture/<id>' => 'partner/goods/picture/picture-list', //图片列表
                'POST partnerMerchantGoodsPicture' => 'partner/goods/goods/uploads-picture', //商品图片上传专用
                'DELETE partnerMerchantGoodsPicture/<id>' => 'partner/goods/goods/delete-picture', //商品图片删除
                //合伙人订单
                'GET partnerOrder' => 'partner/order/order/list', //合伙人订单列表
                'GET partnerOrderSummary' => 'partner/order/order/summary', //订单概述
                //合伙人团长
                'GET partnerLeader' => 'partner/leader/leader/list', //合伙人团长列表
                //合伙人提现申请
                'POST partnerWithdraws' => 'partner/withdraw/withdraw/add', //合伙人添加提现记录
                'GET partnerWithdraws' => 'partner/withdraw/withdraw/list', //合伙人提现申请记录
                'GET partnerMerchantWithdraws' => 'merchant/partner/partner/withdraw-list', //商户后台提现申请记录
                'PUT partnerMerchantWithdraws/<id>' => 'merchant/partner/partner/withdraw', //商户后台提现申请审核
                'GET openConfig' => 'partner/goods/goods/open-config', //是否开启团购
                'GET partnerBalance' => 'partner/withdraw/withdraw/balance', //获取balance
                //应用操作记录
                'GET Operation' => 'merchant/system/operation-record/list',//操作记录列表
                'GET Operation/<id>' => 'merchant/system/operation-record/one',//操作记录单条

                //仓库
                'POST storehouses' => 'merchant/storehouse/storehouse/add',//添加仓库
                'GET storehouses' => 'merchant/storehouse/storehouse/list',//仓库列表
                'GET storehouses/<id>' => 'merchant/storehouse/storehouse/one',//仓库单条
                'PUT storehouses/<id>' => 'merchant/storehouse/storehouse/update',//更新仓库
                'DELETE storehouses/<id>' => 'merchant/storehouse/storehouse/delete',//删除仓库
                'GET searchLeader' => 'merchant/storehouse/storehouse/search-leader',//查询团长
                'POST bindLeader' => 'merchant/storehouse/storehouse/bind-leader',//绑定团长
                'DELETE deleteLeader' => 'merchant/storehouse/storehouse/delete-leader',//解绑团长
                'GET storehouseDetail/<id>' => 'merchant/storehouse/storehouse/storehouse-detail',//仓库详情
                'GET searchGoods' => 'merchant/storehouse/storehouse/search-goods',//查询商品
                //入库
                'POST incomings' => 'merchant/storehouse/incoming/add',//入库插入
                'GET incomings' => 'merchant/storehouse/incoming/list',//入库列表
                'GET incomings/<id>' => 'merchant/storehouse/incoming/one',//入库单条
                'GET searchList' => 'merchant/storehouse/incoming/search-list',//入库查询
                'GET incomingExport' => 'merchant/storehouse/incoming/export',//入库导出
                'GET incomingExport/<id>' => 'merchant/storehouse/incoming/export-detail',//入库详情导出
                //出库
                'POST outbounds' => 'merchant/storehouse/outbound/add',//出库插入
                'GET outbounds' => 'merchant/storehouse/outbound/list',//出库列表
                'GET outbounds/<id>' => 'merchant/storehouse/outbound/one',//出库单条
                'GET outboundList' => 'merchant/storehouse/outbound/search-list',//出库查询
                'GET outboundExport' => 'merchant/storehouse/outbound/export',//出库导出
                'GET outboundExport/<id>' => 'merchant/storehouse/outbound/export-detail',//出库详情导出
                //盘点
                'POST inventories' => 'merchant/storehouse/inventory/add',//盘点插入
                'GET inventories' => 'merchant/storehouse/inventory/list',//盘点列表
                'GET inventories/<id>' => 'merchant/storehouse/inventory/one',//盘点单条
                'GET inventoryList' => 'merchant/storehouse/inventory/search-list',//盘点查询
                'GET inventoryExport' => 'merchant/storehouse/inventory/export',//盘点导出
                'GET inventoryExport/<id>' => 'merchant/storehouse/inventory/export-detail',//盘点详情导出
                'GET realStock' => 'merchant/storehouse/inventory/stock',//先有库存
                'GET realStockExport' => 'merchant/storehouse/inventory/real-stock-export',//先有库存导出
                'GET checkGoodsCode' => 'merchant/storehouse/storehouse/check-goods-code',//校验商品规格码唯一性

                //商户后台UU跑腿账号管理
                'GET merchantUuAccount' => 'merchant/system/uu/list',//账号查询
                'POST merchantUuAccount' => 'merchant/system/uu/add',//账号添加、更新
                //UU订单管理
                'GET merchantUuOrder' => 'merchant/system/uu/orderlist',//UU订单列表查询
                'POST merchantUuGetorderprice' => 'merchant/system/uu/getorderprice',//计算订单价格
                'POST merchantUuAddorder' => 'merchant/system/uu/addorder',//发布订单
                'PUT merchantUuCancelorder/<id>' => 'merchant/system/uu/cancelorder',//取消订单
                'POST merchantuucallback' => 'merchant/system/uu/callback',//回调
                //微信公众号
                'GET merchantTemplateMessage' => 'wechat/officialAccount/official-account/template-message', //商户新订单模板消息推送
                //统计
                'GET sales' => 'merchant/statistics/statistics/sales',//销售统计
                'GET salesExport' => 'merchant/statistics/statistics/sales-export',//销售统计导出
                'GET goodsSalesExport' => 'merchant/statistics/statistics/goods-sales-export',//商品销售统计导出
                'GET goodsSales' => 'merchant/statistics/statistics/goods-sales',//商品销售统计
                'GET leaderSales' => 'merchant/statistics/statistics/leader-sales',//团长销售统计
                'GET leaderSalesExport' => 'merchant/statistics/statistics/leader-sales-export',//团长销售统计导出
                'GET userSales' => 'merchant/statistics/statistics/user-sales',//用户销售
                'GET userSalesExport' => 'merchant/statistics/statistics/user-sales-export',//用户销售统计导出

                //多级分销
                'GET merchantDistribution' => 'merchant/app/access/distributions',//分销佣金比例设置
                'PUT merchantDistribution/<id>' => 'merchant/app/access/distribution',//分销佣金比例设置
                'POST uploadsImages' => 'merchant/distribution/super/uploads',//上传图片
                'POST superUsers' => 'merchant/distribution/super/add',//新增超级会员设置
                'GET superUsers' => 'merchant/distribution/super/one',//超级会员设置单条
                'PUT superUsers' => 'merchant/distribution/super/update',//超级会员设置跟新
                'POST agentUsers' => 'merchant/distribution/agent/add',//供应商添加
                'GET agentUsers/<id>' => 'merchant/distribution/agent/one',//供应商单条
                'PUT agentUsers/<id>' => 'merchant/distribution/agent/update',//供应商编辑
                'GET agentUsers' => 'merchant/distribution/agent/list',//供应商列表
                'POST operatorUsers' => 'merchant/distribution/operator/add',//运营商添加
                'GET operatorUsers/<id>' => 'merchant/distribution/operator/one',//运营商单条
                'PUT operatorUsers/<id>' => 'merchant/distribution/operator/update',//运营商编辑
                'GET operatorUsers' => 'merchant/distribution/operator/list',//运营商列表
                'GET distribution' => 'merchant/distribution/distribution/index',//计算分销各级佣金
                'GET StockRight' => 'merchant/distribution/distribution/stock-right',//计算运营商股权
                'GET distributionAccess' => 'merchant/distribution/distribution/list',//查询佣金记录
                'GET upUser' => 'merchant/distribution/distribution/upuser',//查询待审核会员
                'PUT upUser/<id>' => 'merchant/distribution/distribution/update',//审核会员等级
                //多级分销前台接口
                'GET distributionCenter' => 'shop/distribution/index',//分销中心首页
                'GET distributionOrder' => 'shop/distribution/order',//当前用户有关的订单信息
                'GET distributionUser' => 'shop/distribution/user',//我的团队、客户user
                //点我达
                'POST dianwodaCreate' => 'merchant/system/dian-wo-da/create',//下单
                'GET dianwoda' => 'merchant/system/dian-wo-da/one',//配置查询
                'POST dianwoda' => 'merchant/system/dian-wo-da/add',//配置添加、修改


                'GET miniConfig' => 'merchant/config/config/minione', //查单条
                'PUT miniConfig' => 'merchant/config/config/mini', //查单条

            ],
        ],
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => '127.0.0.1',
            'port' => 6379,
            'database' => 0,
        ],
        'imagine' => [
            'class' => 'yii\imagine\Image',
        ],
//        'cache' => [
////            'class' => 'yii\caching\FileCache',
//            'class' => 'yii\redis\Cache',
//        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
// 'useFileTransport' to false and configure a transport
// for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        /*
          'urlManager' => [
          'enablePrettyUrl' => true,
          'showScriptName' => false,
          'rules' => [
          ],
          ],
         */
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1   '],
    ];
}

return $config;
