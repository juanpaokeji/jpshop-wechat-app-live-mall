<?php

namespace app\controllers\shop;

use app\models\merchant\system\ShopSolitaireModel;
use app\models\shop\GroupOrderModel;
use app\models\shop\ShopGoodsModel;
use app\models\shop\SubOrdersModel;
use app\models\shop\UserModel;
use yii;
use yii\web\ShopController;

class SolitaireController extends ShopController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors() {
        return [
            'token' => [
                'class' => 'yii\filters\ShopFilter', //调用过滤器
//                'only' => ['single'],//指定控制器应用到哪些动作
                'except' => ['order-share','solitaire-share'],//指定控制器不应用到哪些动作
            ]
        ];
    }

    /**
     * 接龙
     */
    public function actionOne($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $model = new ShopSolitaireModel();
            $where['id'] = $id;
            $array = $model->do_one($where);
            if ($array['status'] == 200){
                $array['data']['end_time'] = date('Y-m-d H:i:s',$array['data']['end_time']);
                $array['data']['take_delivery_time'] = date('Y-m-d H:i:s',$array['data']['take_delivery_time']);
                $array['data']['pic_urls'] = explode(';',$array['data']['pic_urls']);
                $goodsIds = json_decode($array['data']['goods_ids'],true);
                $goodsModel = new ShopGoodsModel();
                $goodsWhere['field'] = "shop_goods.id,shop_goods.name,shop_goods.type,shop_goods.weight,shop_goods.pic_urls,shop_goods.supplier_id,shop_goods.service_goods_is_ship,shop_goods.stocks,system_sub_admin.leader";
                $goodsWhere['in'] = ['shop_goods.id',$goodsIds];
                $goodsWhere['join'][] = ['left join','system_sub_admin','shop_goods.supplier_id = system_sub_admin.id'];
                $goodsWhere['limit'] = false;
                $goodsInfo = $goodsModel->do_select($goodsWhere);
                $stockWhere['field'] = "shop_stock.id as stock_id,shop_stock.goods_id,shop_stock.property1_name,shop_stock.property2_name,shop_stock.price";
                $stockWhere['in'] = ['shop_goods.id',$goodsIds];
                $stockWhere['shop_stock.delete_time'] = null;
                $stockWhere['limit'] = false;
                $stockWhere['join'][] = ['right join','shop_stock','shop_stock.goods_id = shop_goods.id'];
                $stockInfo = $goodsModel->do_select($stockWhere);
                if ($goodsInfo['status'] != 200 || $stockInfo['status'] != 200){
                    return result(500, "商品信息有误");
                }
                $goodsStocks = 0;
                foreach ($goodsInfo['data'] as $k=>$v){
                    $pic = explode(',',$v['pic_urls']);
                    $goodsInfo['data'][$k]['pic_urls'] = $pic[0];
                    $goodsStocks += $v['stocks'];
                    if ($v['leader'] != null){
                        $goodsInfo['data'][$k]['supplier_name'] = json_decode($v['leader'],true)['realname'];
                    }
                    foreach ($stockInfo['data'] as $sk=>$sv){
                        if ($sv['goods_id'] == $v['id']){
                            $goodsInfo['data'][$k]['stock'][] = $sv;
                        }
                    }
                }
                $orderModel = new GroupOrderModel();
                $orderWhere['shop_order_group.solitaire_id'] = $id;
                $orderWhere['or'] = ['and',['>','shop_order_group.status', 0],['!=','shop_order_group.status', 2]];
                $orderWhere['field'] = "shop_user.avatar";
                $orderWhere['join'][] = ['left join','shop_user','shop_user.id = shop_order_group.user_id'];
                $orderWhere['groupBy'] = "user_id";
                $avatarInfo = $orderModel->do_select($orderWhere);
                $avatar = [];
                $num = 0;
                if ($avatarInfo['status'] == 200){
                    foreach ($avatarInfo['data'] as $k=>$v){
                        $avatar[] = $v['avatar'];
                        if ($k >= 6){
                            break;
                        }
                    }
                    $num = $avatarInfo['count'];
                }
                //商品详情
                $array['data']['goods'] = $goodsInfo['data'];
                //接龙商品数量、人数、头像
                $array['data']['statistics']['stocks'] = $goodsStocks;
                $array['data']['statistics']['num'] = $num;
                $array['data']['statistics']['avatar'] = $avatar;
                //我的订单
                $subOrderModel = new SubOrdersModel();
                $orderWhere = [];
                $orderWhere['shop_order_group.solitaire_id'] = $id;
                $orderWhere['shop_order_group.user_id'] = yii::$app->session['user_id'];
                $orderWhere['field'] = "shop_user.nickname,shop_order_group.status,shop_order_group.create_time,shop_order.name,shop_order.property1_name,shop_order.property2_name,sum(shop_order.number) as number";
                $orderWhere['or'] = ['and',['>','shop_order_group.status', 0],['!=','shop_order_group.status', 2]];
                $orderWhere['groupBy'] = "stock_id";
                $orderWhere['limit'] = false;
                $orderWhere['join'][] = ['left join','shop_order_group','shop_order.order_group_sn = shop_order_group.order_sn'];
                $orderWhere['join'][] = ['left join','shop_user','shop_user.id = shop_order_group.user_id'];
                $myOrder = $subOrderModel->do_select($orderWhere);
                if ($myOrder['status'] == 200){
                    foreach ($myOrder['data'] as $k=>$v){
                        $array['data']['my_order']['nickname'] = $v['nickname'];
                        $array['data']['my_order']['format_create_time'] = $v['format_create_time'];
                        $array['data']['my_order']['status'] = $v['status'];
                        $myOrderGoods['name'] = $v['name'];
                        $myOrderGoods['number'] = $v['number'];
                        $myOrderGoods['property1_name'] = $v['property1_name'];
                        $myOrderGoods['property2_name'] = $v['property2_name'];
                        $array['data']['my_order']['goods'][] = $myOrderGoods;
                    }
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    //接龙订单列表
    public function actionList($id){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $orderModel = new GroupOrderModel();
            $orderWhere['field'] = "shop_user.nickname,shop_user.avatar,shop_order_group.order_sn,shop_order_group.create_time";
            $orderWhere['shop_order_group.solitaire_id'] = $id;
            $orderWhere['or'] = ['and',['>','shop_order_group.status', 0],['!=','shop_order_group.status', 2]];
            if (isset($params['page'])){
                $orderWhere['limit'] = $params['limit'];
                $orderWhere['page'] = $params['page'];
            }else{
                $orderWhere['limit'] = false;
            }
            $orderWhere['join'][] = ['left join','shop_user','shop_user.id = shop_order_group.user_id'];
            $array = $orderModel->do_select($orderWhere);

            $subOrderModel = new SubOrdersModel();
            $subOrderWhere['shop_order_group.solitaire_id'] = $id;
            $subOrderWhere['or'] = ['and',['>','shop_order_group.status', 0],['!=','shop_order_group.status', 2]];
            $subOrderWhere['field'] = "shop_order.order_group_sn,shop_order.name,shop_order.property1_name,shop_order.property2_name,shop_order.number";
            $subOrderWhere['join'][] = ['left join','shop_order_group','shop_order.order_group_sn = shop_order_group.order_sn'];
            $info = $subOrderModel->do_select($subOrderWhere);

            if ($array['status'] == 200 && $info['status'] == 200){
                foreach ($array['data'] as $k=>$v){
                    foreach ($info['data'] as $key=>$val){
                        if ($v['order_sn'] == $val['order_group_sn']){
                            $array['data'][$k]['goods'][] = $val;
                        }
                    }
                }
            }

            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    //接龙转发
    public function actionOrderShare(){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            //设置类目 参数
            $must = ['solitaire_id','share_uid'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $model = new ShopSolitaireModel();
            $where['field'] = "name,goods_ids";
            $where['id'] = $params['solitaire_id'];
            $array = $model->do_one($where);
            if ($array['status'] == 200){
                $goodsIds = json_decode($array['data']['goods_ids'],true);
                unset($array['data']['goods_ids']);
                $goodsModel = new ShopGoodsModel();
                $goodsWhere['field'] = "id,pic_urls,price";
                $goodsWhere['in'] = ['id',$goodsIds];
                $goodsWhere['limit'] = 3;
                $goodsWhere['orderby'] = 'price';
                $goodsInfo = $goodsModel->do_select($goodsWhere);
                if ($goodsInfo['status'] != 200){
                    return result(500, "商品信息有误");
                }
                foreach ($goodsInfo['data'] as $k=>$v){
                    $pic = explode(',',$v['pic_urls']);
                    $goodsInfo['data'][$k]['pic_urls'] = $pic[0];
                }

                $userModel = new UserModel();
                $userWhere['fields'] = 'sum(shop_order_group.payment_money) as payment_money,shop_user.nickname,shop_user.avatar';
                $userWhere['shop_order_group.solitaire_id'] = $params['solitaire_id'];
                $userWhere['shop_user.id'] = $params['share_uid'];
                $userWhere['(shop_order_group.status > 0 and shop_order_group.status != 2)'] = null;
                $userWhere['join'] = ' left join shop_order_group on shop_user.id = shop_order_group.user_id';
                $userInfo = $userModel->findall($userWhere);
                if ($userInfo['status'] != 200){
                    return result(500, "会员信息有误");
                }
                if ($userInfo['data'][0]['payment_money'] == null){
                    $userInfo['data'][0]['payment_money'] = 0;
                }
                $array['data']['payment_money'] = $userInfo['data'][0]['payment_money'];
                $array['data']['nickname'] = $userInfo['data'][0]['nickname'];
                $array['data']['avatar'] = $userInfo['data'][0]['avatar'];
                $array['data']['price'] = $goodsInfo['data'][0]['price'];
                $array['data']['goods'] = $goodsInfo['data'];

            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    //接龙分享
    public function actionSolitaireShare(){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            //设置类目 参数
            $must = ['solitaire_id','share_uid'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $model = new ShopSolitaireModel();
            $where['field'] = "name,goods_ids";
            $where['id'] = $params['solitaire_id'];
            $array = $model->do_one($where);
            if ($array['status'] == 200){
                $goodsIds = json_decode($array['data']['goods_ids'],true);
                unset($array['data']['goods_ids']);
                $goodsModel = new ShopGoodsModel();
                $goodsWhere['field'] = "price";
                $goodsWhere['in'] = ['id',$goodsIds];
                $goodsWhere['limit'] = 1;
                $goodsWhere['orderby'] = 'price';
                $goodsInfo = $goodsModel->do_select($goodsWhere);
                if ($goodsInfo['status'] != 200){
                    return result(500, "商品信息有误");
                }

                $userModel = new UserModel();
                $userWhere['fields'] = 'count(shop_order_group.id) as num,shop_user.nickname,shop_user.avatar';
                $userWhere['shop_order_group.solitaire_id'] = $params['solitaire_id'];
                $userWhere['shop_user.id'] = $params['share_uid'];
                $userWhere['(shop_order_group.status > 0 and shop_order_group.status != 2)'] = null;
                $userWhere['join'] = ' left join shop_order_group on shop_user.id = shop_order_group.user_id';
                $userInfo = $userModel->findall($userWhere);
                if ($userInfo['status'] != 200){
                    return result(500, "会员信息有误");
                }
                if ($userInfo['data'][0]['num'] == null){
                    $userInfo['data'][0]['num'] = 0;
                }
                $array['data']['my_num'] = $userInfo['data'][0]['num'];
                $array['data']['my_nickname'] = $userInfo['data'][0]['nickname'];
                $array['data']['my_avatar'] = $userInfo['data'][0]['avatar'];
                $array['data']['price'] = $goodsInfo['data'][0]['price'];

                $orderModel = new GroupOrderModel();
                $orderWhere['shop_order_group.solitaire_id'] = $params['solitaire_id'];
                $orderWhere['or'] = ['and',['>','shop_order_group.status', 0],['!=','shop_order_group.status', 2]];
                $orderWhere['field'] = "count(shop_order_group.id) as num,shop_order_group.create_time,shop_user.nickname,shop_user.avatar";
                $orderWhere['join'][] = ['left join','shop_user','shop_user.id = shop_order_group.user_id'];
                $orderWhere['groupBy'] = "user_id";
                $avatarInfo = $orderModel->do_select($orderWhere);
                if ($avatarInfo['status'] == 200){
                    $array['data']['list'] = $avatarInfo['data'];
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
