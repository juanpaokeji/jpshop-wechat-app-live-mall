<?php

namespace app\controllers\merchant\system;

use app\models\merchant\system\GroupModel;
use app\models\shop\ShopAuthGroupAccessModel;
use app\models\system\SystemMenuModel;
use yii\web\MerchantController;
use yii;

class MenuController extends MerchantController
{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors()
    {
        return [
            'token' => [
                'class' => 'yii\filters\MerchantFilter', //调用过滤器
               // 'only' => ['single'], //指定控制器应用到哪些动作
                //'except' => ['test'], //指定控制器不应用到哪些动作
            ]
        ];
    }

//    public function actionTest()
//    {
//        $str = '[{"path":"/login","hidden":true},{"path":"/404","hidden":true},{"path":"/","hidden":true,"children":[{"path":"/","name":"应用列表","hidden":false},{"path":"/appinfo","name":"应用信息","hidden":false}]},{"path":"/info/updatePW","hidden":true,"children":[{"path":"/","name":"修改密码","hidden":false}]},{"path":"/dashboard","name":"总览","hidden":false,"children":[{"path":"/dashboard","name":"数据概览","hidden":false},{"path":"/upgrade","name":"升级日志","hidden":false}]},{"path":"/goods/list","name":"商品","hidden":false,"children":[{"path":"/goods/list","name":"商品列表","hidden":false},{"path":"/goods/group","name":"商品分组","hidden":false},{"path":"/goods/recyclebin","name":"回收站","hidden":false},{"path":"/goods/add","name":"商品添加","hidden":true}]},{"path":"/order/manage","name":"订单","hidden":false,"children":[{"path":"/order/manage","name":"订单管理","hidden":false},{"path":"/order/summary","name":"订单概述","hidden":false},{"path":"/order/comment","name":"评价管理","hidden":false},{"path":"/order/print","name":"订单配送","hidden":false}]},{"path":"/vip/list","name":"会员","hidden":false,"children":[{"path":"/vip/list","name":"会员列表","hidden":false},{"path":"/vip/unpay","name":"会员等级","hidden":false}]},{"path":"/customers/head","name":"团长","hidden":false,"children":[{"path":"/customers/head","name":"团长","hidden":false},{"path":"/customers/audit","name":"团长审核","hidden":false},{"path":"/customers/leave","name":"团长等级","hidden":false},{"path":"/customers/warehouse","name":"路线","hidden":false},{"path":"/setting/tuanconfig","name":"设置","hidden":false},{"path":"/finance/record","name":"佣金流水","hidden":false}]},{"path":"/subCommission/setting","name":"分销","hidden":false,"children":[{"path":"/subCommission/setting","name":"超级会员","hidden":false},{"path":"/subCommission/agent","name":"代理商","hidden":false},{"path":"/subCommission/operator","name":"运营商","hidden":false},{"path":"/subCommission/record","name":"佣金记录","hidden":false},{"path":"/subCommission/upUser","name":"会员审核","hidden":false},{"path":"/subCommission/distribution","name":"设置","hidden":false},{"path":"/subCommission/distributors","name":"分销商","hidden":false}]},{"path":"/marketing/index","name":"应用","hidden":false,"children":[{"path":"/marketing/index","name":"应用管理","hidden":false},{"path":"/marketing/coupon","name":"优惠券","hidden":true},{"path":"/marketing/buyCashback","name":"购物返现","hidden":true},{"path":"/marketing/seckill","name":"秒杀","hidden":true},{"path":"/marketing/singin","name":"签到","hidden":true},{"path":"/marketing/apptamplate","name":"模板信息","hidden":true},{"path":"/marketing/assemble","name":"拼团","hidden":true},{"path":"/marketing/pay","name":"充值","hidden":true},{"path":"/marketing/bargain","name":"砍价","hidden":true},{"path":"/marketing/reduction","name":"满减","hidden":true},{"path":"/marketing/recuits","name":"新人专享","hidden":true},{"path":"/marketing/score","name":"积分商城","hidden":true},{"path":"/marketing/estimated","name":"预约送达","hidden":true},{"path":"/vip/pay","name":"会员PLUS","hidden":true},{"path":"/marketing/copyright","name":"自定义版权","hidden":true}]},{"path":"/finance/apply","name":"数据","hidden":false,"children":[{"path":"/finance/apply","name":"提现申请","hidden":false},{"path":"/statistics/sales","name":"销售统计","hidden":false},{"path":"/statistics/goodsSales","name":"商品销售统计","hidden":false},{"path":"/statistics/leaderSales","name":"团长销售统计","hidden":false},{"path":"/statistics/userSales","name":"用户排行统计","hidden":false}]},{"path":"/warehouse/list","name":"仓库","hidden":false,"children":[{"path":"/warehouse/list","name":"仓库管理","hidden":false},{"path":"/warehouse/warehouseLeader","name":"配送小区","hidden":false},{"path":"/warehouse/inComings","name":"入库管理","hidden":false},{"path":"/warehouse/inComings/add","name":"入库新增","hidden":true},{"path":"/warehouse/inComings/info","name":"入库详情","hidden":true},{"path":"/warehouse/inComingsSearch","name":"入库查询","hidden":false},{"path":"/warehouse/outbounds","name":"出库管理","hidden":false},{"path":"/warehouse/outbounds/add","name":"出库新增","hidden":true},{"path":"/warehouse/outbounds/info","name":"出库详情","hidden":true},{"path":"/warehouse/outboundsSearch","name":"出库查询","hidden":false},{"path":"/warehouse/inventories","name":"盘点管理","hidden":false},{"path":"/warehouse/inventories/add","name":"盘点新增","hidden":true},{"path":"/warehouse/inventories/info","name":"盘点详情","hidden":true},{"path":"/warehouse/realStock","name":"现有库存","hidden":false}]},{"path":"/supplier/apply","name":"门店","hidden":false,"children":[{"path":"/supplier/apply","name":"申请列表","hidden":false},{"path":"/supplier/supplierConfig","name":"配置","hidden":false},{"path":"/supplier/list","name":"门店","hidden":false},{"path":"/supplier/goods","name":"商品","hidden":false},{"path":"/supplier/order","name":"订单","hidden":false},{"path":"/supplier/withdrawal","name":"提现","hidden":false},{"path":"/marketing/PaymentInStore","name":"到店付款","hidden":false}]},{"path":"/applet/baseconfig","name":"小程序","hidden":false,"children":[{"path":"/applet/baseconfig","name":"支付","hidden":false}]},{"path":"/employee/manage","name":"权限","hidden":false,"children":[{"path":"/employee/manage","name":"员工管理","hidden":false},{"path":"/employee/role","name":"角色管理","hidden":false},{"path":"/employee/kefu","name":"客服管理","hidden":false}]},{"path":"/setting/appsetting","name":"设置","hidden":false,"children":[{"path":"/setting/appsetting","name":"基础设置","hidden":false},{"path":"/setting/express","name":"运费模板","hidden":false},{"path":"/setting/takegoods","name":"收货信息","hidden":false},{"path":"/setting/expressdoc","name":"电子面单","hidden":false},{"path":"/setting/viewconfig","name":"页面配置","hidden":false},{"path":"/setting/uu","name":"UU跑腿","hidden":false},{"path":"/setting/dianwoda","name":"点我达","hidden":false},{"path":"/setting/yly","name":"易联云","hidden":false},{"path":"/setting/posters","name":"分享海报","hidden":false},{"path":"/setting/help","name":"常见问题","hidden":false},{"path":"/setting/enclosure","name":"附件","hidden":false},{"path":"/setting/sms","name":"短信","hidden":false},{"path":"/setting/logistics","name":"物流查询","hidden":false}]},{"path":"/operationlog","name":"日志","hidden":false,"children":[{"path":"/operationlog","name":"操作记录","hidden":false}]},{"path":"/goToOld","name":"装修","hidden":false,"children":[{"path":"/goToOld","name":"装修","hidden":false}]},{"path":"/liveBroadcast","name":"直播","hidden":true,"children":[{"path":"/liveBroadcast","name":"直播","hidden":false}]},{"path":"/score/list/add","name":"积分商城","hidden":true,"children":[{"path":"/score/list/add","name":"积分商品添加","hidden":false}]},{"path":"/didopen","name":"未授权","hidden":true,"children":[{"path":"/didopen","name":"未授权","hidden":false}]},{"path":"*","hidden":true}]';
//        $list = json_decode($str, true);
//
//        for ($i = 0; $i < count($list); $i++) {
//
//            if (isset($list[$i]['children'])) {
//                $model = new SystemMenuModel();
//                $children = $list[$i]['children'];
//                unset($list[$i]['children']);
//                $list[$i]['pid'] = 0;
//                $res = $model->do_add($list[$i]);
//                for ($j = 0; $j < count($children); $j++) {
//                    $model = new SystemMenuModel();
//                    $children[$j]['pid'] = $res['data'];
//                    $model->do_add($children[$j]);
//                }
//            } else {
//                $model = new SystemMenuModel();
//                $model->do_add($list[$i]);
//            }
//
//        }
//    }

    public function actionList()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new SystemMenuModel();
            unset($params['key']);
            $params['pid'] = 0;
            $params['limit'] = 100;
            $array = $model->do_select($params);
            if ($array['status'] == 200) {
                for ($i = 0; $i < count($array['data']); $i++) {
                    $params['pid'] = $array['data'][$i]['id'];
                    $sub = $model->do_select($params);
                    if ($sub['status'] == 200) {
                        $array['data'][$i]['children'] = $sub['data'];
                    }
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionMenu()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new SystemMenuModel();


            if (yii::$app->session['sid'] != null) {

                $groupAccess = new ShopAuthGroupAccessModel();
                $subUser = $groupAccess->do_one(['uid' => yii::$app->session['sid'], 'orderby' => 'uid asc']);

                if ($subUser['status'] != 200) {
                    return result(500, "请求错误");
                }

                $groupModel = new GroupModel();
                $group = $groupModel->find(['id' => $subUser['data']['group_ids']]);
                unset($params['key']);
                $params['limit'] = false;
                $params['in'] = ['id', explode(",", $group['data']['rules'])];
                $params['orderby'] = "id asc";
                $params['pid'] = 0;
                $array = $model->do_select($params);
                if ($array['status'] == 200) {
                    for ($i = 0; $i < count($array['data']); $i++) {
                        $params['pid'] = $array['data'][$i]['id'];
                        $sub = $model->do_select($params);
                        if ($sub['status'] == 200) {
                            $array['data'][$i]['children'] = $sub['data'];
                        } else {
                            $array['data'][$i]['children'] = array();
                        }
                    }
                }
            } else {
                return result(500, "请求错误");
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
